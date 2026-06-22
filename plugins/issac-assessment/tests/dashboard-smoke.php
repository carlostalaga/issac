<?php
/**
 * Dashboard smoke test for ISSAC (Milestone 6).
 *
 * Run: wp eval-file wp-content/plugins/issac-assessment/tests/dashboard-smoke.php --user=1
 */

use Issac\Domain\InstrumentRepository;
use Issac\Domain\AssessmentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;

$GLOBALS['issac_results'] = ['passed' => 0, 'failed' => 0];

function issac_dash_assert(bool $condition, string $label): void
{
    if ($condition) {
        $GLOBALS['issac_results']['passed']++;
        echo "  ✓ PASS: {$label}\n";
    } else {
        $GLOBALS['issac_results']['failed']++;
        echo "  ✗ FAIL: {$label}\n";
    }
}

// ── Setup: ensure current user has the capability ────────────────────
$user = wp_get_current_user();
$hadCap = $user->has_cap('issac_take_assessment');
if (!$hadCap) {
    $user->add_cap('issac_take_assessment');
}

// Clean any pre-existing assessment for a guaranteed fresh start.
global $wpdb;
$existingAssessment = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}issac_assessments WHERE user_id = %d",
    $user->ID
));
if ($existingAssessment) {
    $wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $existingAssessment]);
    $wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => (int) $existingAssessment]);
    echo "  Cleared pre-existing assessment #{$existingAssessment}\n";
}

// ── 1. Fresh assessment render ───────────────────────────────────────
echo "\n1. Fresh assessment — dashboard structure\n";

$output = do_shortcode('[issac_dashboard]');

issac_dash_assert(str_contains($output, 'issac-dashboard'), 'Output contains .issac-dashboard wrapper');
issac_dash_assert(str_contains($output, '<svg'), 'Output contains SVG ring');
issac_dash_assert(substr_count($output, 'issac-dashboard__card') >= 5, 'Five domain cards present');
issac_dash_assert(substr_count($output, 'progress-bar') >= 5, 'Five progress bars present');

// ── 2. Fresh assessment: all CTAs = "Start", download disabled ───────
echo "\n2. Fresh assessment — CTA labels and download state\n";

issac_dash_assert(substr_count($output, '>Start</a>') === 5, 'All five CTAs show "Start"');
issac_dash_assert(str_contains($output, 'disabled'), 'Download button is disabled');
issac_dash_assert(!str_contains($output, 'issac-dashboard__card-avg'), 'No average/band text shown yet');

// ── 3. One response in domain 1 → "Resume" ──────────────────────────
echo "\n3. Partial domain 1 — Resume CTA + average/band\n";

$assessment = AssessmentRepository::findOrCreate(get_current_user_id());
$tree = InstrumentRepository::tree();

// Find first active item in domain 1
$firstItemCode = null;
foreach ($tree as $domain) {
    if ($domain->code !== '1') continue;
    foreach ($domain->subsections as $sub) {
        foreach ($sub->items as $item) {
            if ($item->isActive) {
                $firstItemCode = $item->itemCode;
                break 3;
            }
        }
    }
}

ResponseRepository::upsert((int) $assessment->id, $firstItemCode, 4);
$output = do_shortcode('[issac_dashboard]');

issac_dash_assert(str_contains($output, '>Resume</a>'), 'Domain 1 shows "Resume"');
issac_dash_assert(substr_count($output, '>Start</a>') === 4, 'Other four domains still show "Start"');
issac_dash_assert(str_contains($output, 'issac-dashboard__card-avg'), 'Domain 1 shows average/band');
issac_dash_assert(str_contains($output, 'answered'), 'Domain 1 shows answered count');

// ── 4. Complete domain 4 → "Review" ─────────────────────────────────
echo "\n4. Complete domain 4 — Review CTA\n";

$domain4Items = [];
foreach ($tree as $domain) {
    if ($domain->code !== '4') continue;
    foreach ($domain->subsections as $sub) {
        foreach ($sub->items as $item) {
            if ($item->isActive) {
                $domain4Items[] = $item->itemCode;
            }
        }
    }
}

foreach ($domain4Items as $code) {
    ResponseRepository::upsert((int) $assessment->id, $code, 3);
}

$output = do_shortcode('[issac_dashboard]');
issac_dash_assert(str_contains($output, '>Review</a>'), 'Domain 4 shows "Review"');

// ── 5. CTA links include correct ?d={code} ──────────────────────────
echo "\n5. CTA links include ?d={code}\n";

foreach (['1', '2', '3', '4', '5'] as $code) {
    issac_dash_assert(
        str_contains($output, 'd=' . $code),
        "CTA link for domain {$code} includes d={$code}"
    );
}

// ── 6. Download button stays disabled (M7 placeholder) ───────────────
echo "\n6. Download button disabled (no PDF route yet)\n";

issac_dash_assert(str_contains($output, 'disabled'), 'Download button remains disabled');
issac_dash_assert(!str_contains($output, '/issac/v1/report'), 'No link to missing PDF endpoint');

// ── 7. Logged-out → login prompt ─────────────────────────────────────
echo "\n7. Access gate — logged out\n";

wp_set_current_user(0);
$output = do_shortcode('[issac_dashboard]');
issac_dash_assert(str_contains($output, 'issac-login-prompt'), 'Logged-out shows login prompt');
issac_dash_assert(!str_contains($output, 'issac-dashboard'), 'No dashboard markup for logged-out');

// ── 8. Logged-in without capability → login prompt ───────────────────
echo "\n8. Access gate — no capability\n";

$subscriber = get_users(['role' => 'subscriber', 'number' => 1]);
if (!empty($subscriber)) {
    $subUser = $subscriber[0];
    $subUser->remove_cap('issac_take_assessment');
    wp_set_current_user($subUser->ID);
    $output = do_shortcode('[issac_dashboard]');
    issac_dash_assert(str_contains($output, 'issac-login-prompt'), 'User without cap sees login prompt');
} else {
    echo "  – SKIP: No subscriber user to test capability gate\n";
}

// ── Cleanup ──────────────────────────────────────────────────────────
echo "\n9. Cleanup\n";

wp_set_current_user($user->ID);

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
