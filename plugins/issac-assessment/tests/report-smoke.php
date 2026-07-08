<?php
/**
 * PDF report smoke test for ISSAC (Milestone 7).
 *
 * Run: wp eval-file wp-content/plugins/issac-assessment/tests/report-smoke.php --user=1
 */

use Issac\Domain\AssessmentRepository;
use Issac\Domain\EventRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Pdf\ReportGenerator;
use Issac\Rest\RoutesController;

$GLOBALS['issac_results'] = ['passed' => 0, 'failed' => 0];

function issac_report_assert(bool $condition, string $label): void
{
    if ($condition) {
        $GLOBALS['issac_results']['passed']++;
        echo "  ✓ PASS: {$label}\n";
    } else {
        $GLOBALS['issac_results']['failed']++;
        echo "  ✗ FAIL: {$label}\n";
    }
}

// ── Setup ───────────────────────────────────────────────────────────
$user = wp_get_current_user();
$hadCap = $user->has_cap('issac_take_assessment');
if (!$hadCap) {
    $user->add_cap('issac_take_assessment');
}

global $wpdb;

$existingAssessment = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}issac_assessments WHERE user_id = %d",
    $user->ID
));
if ($existingAssessment) {
    $wpdb->delete($wpdb->prefix . 'issac_events', ['assessment_id' => (int) $existingAssessment]);
    $wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $existingAssessment]);
    $wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => (int) $existingAssessment]);
    echo "  Cleared pre-existing assessment #{$existingAssessment}\n";
}

wp_set_current_user($user->ID);

$assessment = AssessmentRepository::findOrCreate(get_current_user_id());
$tree       = InstrumentRepository::tree();
$savedPaths = [];

// Find first active items in domains 1 and 2
$itemCodes = [];
foreach (['1', '2'] as $domainCode) {
    foreach ($tree as $domain) {
        if ($domain->code !== $domainCode) {
            continue;
        }
        foreach ($domain->subsections as $sub) {
            foreach ($sub->items as $item) {
                if ($item->isActive) {
                    $itemCodes[] = $item->itemCode;
                    break 2;
                }
            }
        }
    }
}

foreach ($itemCodes as $code) {
    ResponseRepository::upsert((int) $assessment->id, $code, 4);
}

// ── 1. Save-mode PDF generation ─────────────────────────────────────
echo "\n1. ReportGenerator save mode\n";

$path = ReportGenerator::generate($assessment, 'save');
$savedPaths[] = $path;

issac_report_assert(is_string($path) && $path !== '', 'Returns a file path');
issac_report_assert(file_exists($path), 'PDF file exists on disk');
issac_report_assert(str_starts_with((string) file_get_contents($path, false, null, 0, 4), '%PDF'), 'File is a PDF');
issac_report_assert(filesize($path) > 1024, 'PDF is larger than 1 KB');
issac_report_assert(str_contains(basename($path), gmdate('Y-m-d')), 'Filename contains today\'s date');

// ── 2. pdf_generated event recorded ─────────────────────────────────
echo "\n2. pdf_generated event\n";

$firstTimestamp = EventRepository::lastFired((int) $assessment->id, 'pdf_generated');
issac_report_assert($firstTimestamp !== null, 'lastFired returns a datetime after generation');

// ── 3. recordOrTouch updates timestamp on second generation ─────────
echo "\n3. recordOrTouch updates timestamp\n";

sleep(1);
$path2 = ReportGenerator::generate($assessment, 'save');
$savedPaths[] = $path2;

$secondTimestamp = EventRepository::lastFired((int) $assessment->id, 'pdf_generated');
issac_report_assert(
    $secondTimestamp !== null && strtotime($secondTimestamp) >= strtotime((string) $firstTimestamp),
    'Second generation updates lastFired timestamp'
);

$eventCount = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}issac_events
     WHERE assessment_id = %d AND event_key = %s",
    (int) $assessment->id,
    'pdf_generated'
));
issac_report_assert($eventCount === 1, 'Exactly one pdf_generated row exists');

// ── 4. REST route registration ────────────────────────────────────────
echo "\n4. REST route registration\n";

do_action('rest_api_init');
RoutesController::registerRoutes();

$routes = rest_get_server()->get_routes();
issac_report_assert(isset($routes['/issac/v1/report']), 'GET /issac/v1/report route exists');

// ── 5. Empty responses rejected by endpoint ───────────────────────────
echo "\n5. REST endpoint — no responses\n";

$emptyAssessment = AssessmentRepository::findOrCreate(get_current_user_id());
$wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $emptyAssessment->id]);

$request = new WP_REST_Request('GET', '/issac/v1/report');
$response = rest_get_server()->dispatch($request);
issac_report_assert($response->get_status() === 400, 'Empty assessment returns 400');

// Restore responses for remaining tests
foreach ($itemCodes as $code) {
    ResponseRepository::upsert((int) $assessment->id, $code, 4);
}

// ── 6. Access gate — logged out ─────────────────────────────────────
echo "\n6. Access gate — logged out\n";

wp_set_current_user(0);
$request = new WP_REST_Request('GET', '/issac/v1/report');
$response = rest_get_server()->dispatch($request);
issac_report_assert($response->get_status() === 401, 'Logged-out request returns 401');

// ── 7. Dashboard integration — last report date ───────────────────────
echo "\n7. Dashboard integration\n";

wp_set_current_user($user->ID);
$output = do_shortcode('[issac_dashboard]');

issac_report_assert(str_contains($output, '/issac/v1/report'), 'Dashboard contains report download link');
issac_report_assert(str_contains($output, 'Last report:'), 'Dashboard shows last report date');
issac_report_assert(str_contains($output, 'issac-dashboard__download'), 'Dashboard contains download control');

// ── 8. Fresh user — download disabled ─────────────────────────────────
echo "\n8. Fresh user — download disabled\n";

$wpdb->delete($wpdb->prefix . 'issac_events', ['assessment_id' => (int) $assessment->id]);
$wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $assessment->id]);

$output = do_shortcode('[issac_dashboard]');
issac_report_assert(str_contains($output, 'disabled'), 'Download button disabled with zero responses');
issac_report_assert(!str_contains($output, 'Last report:'), 'No last report line when never generated');

// ── Cleanup ──────────────────────────────────────────────────────────
echo "\n9. Cleanup\n";

wp_set_current_user($user->ID);

foreach ($savedPaths as $savedPath) {
    if (is_string($savedPath) && file_exists($savedPath)) {
        unlink($savedPath);
    }
}

$wpdb->delete($wpdb->prefix . 'issac_events', ['assessment_id' => (int) $assessment->id]);
$wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $assessment->id]);
$wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => (int) $assessment->id]);

if (!$hadCap) {
    $user->remove_cap('issac_take_assessment');
}

echo "  Cleaned up assessment #{$assessment->id}\n";

// ── Summary ──────────────────────────────────────────────────────────
$r = $GLOBALS['issac_results'];
echo "\n" . str_repeat('─', 40) . "\n";
echo "Results: {$r['passed']} passed, {$r['failed']} failed\n";

if ($r['failed'] > 0) {
    echo "⚠ Some tests failed.\n";
} else {
    echo "All checks passed.\n";
}
