<?php
/**
 * Render smoke test for ISSAC domain shortcode (Milestone 5A).
 *
 * Run: wp eval-file wp-content/plugins/issac-assessment/tests/render-smoke.php --user=1
 */

use Issac\Domain\InstrumentRepository;
use Issac\Domain\AssessmentRepository;
use Issac\Domain\ResponseRepository;

$GLOBALS['issac_results'] = ['passed' => 0, 'failed' => 0];

function issac_render_assert(bool $condition, string $label): void
{
    if ($condition) {
        $GLOBALS['issac_results']['passed']++;
        echo "  ✓ PASS: {$label}\n";
    } else {
        $GLOBALS['issac_results']['failed']++;
        echo "  ✗ FAIL: {$label}\n";
    }
}

// Ensure current user has the capability
$user = wp_get_current_user();
if (!$user->has_cap('issac_take_assessment')) {
    $user->add_cap('issac_take_assessment');
}

// --- 1. Valid domain renders expected markup ---
echo "\n1. Valid domain render\n";

$_GET['d'] = '1';
$output = do_shortcode('[issac_domain]');

issac_render_assert(str_contains($output, 'issac-domain'), 'Output contains .issac-domain');
issac_render_assert(str_contains($output, 'issac-item'), 'Output contains .issac-item');
issac_render_assert(str_contains($output, 'type="radio"'), 'Output contains radio inputs');
issac_render_assert(str_contains($output, 'issac-descriptor'), 'Output contains descriptor divs');
issac_render_assert(str_contains($output, 'data-domain-code="1"'), 'data-domain-code="1" present');

// --- 2. Pre-selected scores show checked ---
echo "\n2. Pre-selected scores\n";

$assessment = AssessmentRepository::findOrCreate(get_current_user_id());
ResponseRepository::upsert((int) $assessment->id, '1.1', 4);

$output = do_shortcode('[issac_domain]');
issac_render_assert(
    (bool) preg_match('/name="score_1\.1"[^>]*value="4"[^>]*checked/s', $output),
    'Score 1.1=4 radio is checked'
);

// Also verify the correct descriptor is active (score 3-4 → descriptor--3 active)
issac_render_assert(
    (bool) preg_match('/data-item-code="1\.1".*?issac-descriptor--3 issac-descriptor--active/s', $output),
    'Descriptor--3 active for score=4'
);

// --- 3. Inactive items excluded ---
echo "\n3. Inactive items excluded\n";

$tree = InstrumentRepository::tree();
$inactiveCode = null;
foreach ($tree as $domain) {
    if ($domain->code !== '1') continue;
    foreach ($domain->subsections as $sub) {
        foreach ($sub->items as $item) {
            if (!$item->isActive) {
                $inactiveCode = $item->itemCode;
                break 3;
            }
        }
    }
}

if ($inactiveCode) {
    issac_render_assert(
        !str_contains($output, 'data-item-code="' . $inactiveCode . '"'),
        "Inactive item {$inactiveCode} is absent from output"
    );
} else {
    echo "  – SKIP: No inactive items in domain 1 to test\n";
}

// --- 4. Invalid domain shows error, no fatal ---
echo "\n4. Invalid domain\n";

$_GET['d'] = '99';
$output = do_shortcode('[issac_domain]');
issac_render_assert(str_contains($output, 'issac-error'), 'Invalid domain shows error message');
issac_render_assert(!str_contains($output, 'issac-domain'), 'No domain markup for invalid code');

// --- 5. Escaping ---
echo "\n5. Escaping verification\n";

$_GET['d'] = '1';
$output = do_shortcode('[issac_domain]');
issac_render_assert(!str_contains($output, '<script>'), 'No raw <script> tags in output');

// --- 6. Cleanup ---
echo "\n6. Cleanup\n";

global $wpdb;
$wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $assessment->id, 'item_code' => '1.1']);
$wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => (int) $assessment->id]);
echo "  Cleaned up assessment #{$assessment->id}\n";

unset($_GET['d']);

// --- Summary ---
$r = $GLOBALS['issac_results'];
echo "\n" . str_repeat('─', 40) . "\n";
echo "Results: {$r['passed']} passed, {$r['failed']} failed\n";

if ($r['failed'] > 0) {
    echo "⚠ Some tests failed.\n";
} else {
    echo "All checks passed.\n";
}
