<?php
/**
 * Integration smoke test for ISSAC REST API (Milestone 4).
 *
 * Run: wp eval-file wp-content/plugins/issac-assessment/tests/rest-smoke.php --user=1
 *
 * Uses rest_do_request() for internal dispatch (bypasses nonce/HTTP).
 */

use Issac\Domain\InstrumentRepository;

$GLOBALS['issac_results'] = ['passed' => 0, 'failed' => 0];
$testAssessmentId = null;

function issac_assert(bool $condition, string $label): void
{
    if ($condition) {
        $GLOBALS['issac_results']['passed']++;
        echo "  ✓ PASS: {$label}\n";
    } else {
        $GLOBALS['issac_results']['failed']++;
        echo "  ✗ FAIL: {$label}\n";
    }
}

function issac_request(string $method, string $route, array $body = []): WP_REST_Response
{
    $request = new WP_REST_Request($method, '/issac/v1' . $route);
    if (!empty($body)) {
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode($body));
    }
    return rest_do_request($request);
}

// --- 1. GET /assessment ---
echo "\n1. GET /assessment\n";

$response = issac_request('GET', '/assessment');
$data = $response->get_data();

issac_assert($response->get_status() === 200, 'Returns 200');
issac_assert(isset($data['assessment']['id']), 'Assessment created lazily');

$testAssessmentId = $data['assessment']['id'] ?? null;
$tree = InstrumentRepository::tree();
$totalActive = 0;
foreach ($tree as $domain) {
    foreach ($domain->subsections as $sub) {
        foreach ($sub->items as $item) {
            if ($item->isActive) {
                $totalActive++;
            }
        }
    }
}

issac_assert($data['summary']['overall']['total'] === $totalActive, "summary.overall.total = {$totalActive} (live tree count)");
issac_assert(empty((array) $data['responses']), 'Responses initially empty');
issac_assert($data['assessment']['status'] === 'in_progress', 'Status is in_progress');

// --- 2. POST /responses ---
echo "\n2. POST /responses\n";

$response = issac_request('POST', '/responses', ['item_code' => '1.1', 'score' => 3]);
issac_assert($response->get_status() === 200, 'Upsert 1.1=3 returns 200');

$response = issac_request('POST', '/responses', ['item_code' => '1.2', 'score' => 5]);
issac_assert($response->get_status() === 200, 'Upsert 1.2=5 returns 200');

$response = issac_request('POST', '/responses', ['item_code' => '2.1', 'score' => 4]);
$data = $response->get_data();
issac_assert($response->get_status() === 200, 'Upsert 2.1=4 returns 200');

// D1 average = (3+5)/2 = 4.0
$d1 = null;
foreach ($data['summary']['domains'] as $d) {
    if ($d['code'] === '1') {
        $d1 = $d;
        break;
    }
}
issac_assert($d1 !== null && $d1['average'] === 4.0, 'D1 average = 4.0');

// --- 3. POST /responses validation ---
echo "\n3. POST /responses validation\n";

$response = issac_request('POST', '/responses', ['item_code' => '1.1', 'score' => 0]);
issac_assert($response->get_status() === 400, 'score=0 → 400');

$response = issac_request('POST', '/responses', ['item_code' => '1.1', 'score' => 6]);
issac_assert($response->get_status() === 400, 'score=6 → 400');

$response = issac_request('POST', '/responses', ['item_code' => '99.99', 'score' => 3]);
issac_assert($response->get_status() === 400, 'item_code=99.99 → 400');

// --- 4. POST /events/check (domain completion) ---
echo "\n4. POST /events/check\n";

// Score all remaining D1 items to reach 100% completion.
$d1Items = [];
foreach ($tree as $domain) {
    if ($domain->code === '1') {
        foreach ($domain->subsections as $sub) {
            foreach ($sub->items as $item) {
                if ($item->isActive) {
                    $d1Items[] = $item->itemCode;
                }
            }
        }
        break;
    }
}

foreach ($d1Items as $code) {
    // 1.1 and 1.2 already scored; score the rest.
    if (!in_array($code, ['1.1', '1.2'], true)) {
        issac_request('POST', '/responses', ['item_code' => $code, 'score' => 4]);
    }
}

$response = issac_request('POST', '/events/check');
$data = $response->get_data();
$keys = array_column($data['new_events'], 'key');
issac_assert(in_array('domain_completed:1', $keys, true), 'domain_completed:1 fires on first check');

// Second call — should be empty.
$response = issac_request('POST', '/events/check');
$data = $response->get_data();
$keys = array_column($data['new_events'], 'key');
issac_assert(!in_array('domain_completed:1', $keys, true), 'domain_completed:1 does NOT fire again');

// --- 5. POST /complete ---
echo "\n5. POST /complete\n";

$response = issac_request('POST', '/complete');
$data = $response->get_data();
issac_assert($response->get_status() === 200, 'Complete returns 200');
issac_assert($data['assessment']['status'] === 'completed', 'Status is completed');
issac_assert($data['assessment']['completed_at'] !== null, 'completed_at is set');

// Second complete — should error.
$response = issac_request('POST', '/complete');
issac_assert($response->get_status() === 409, 'Second complete returns 409');

// --- 6. Permission (unauthenticated) ---
echo "\n6. Permission\n";

$originalUser = get_current_user_id();
wp_set_current_user(0);

$response = issac_request('GET', '/assessment');
issac_assert($response->get_status() === 401, 'Unauthenticated → 401');

wp_set_current_user($originalUser);

// --- 7. Cleanup ---
echo "\n7. Cleanup\n";

if ($testAssessmentId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'issac_events', ['assessment_id' => $testAssessmentId]);
    $wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => $testAssessmentId]);
    $wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => $testAssessmentId]);
    echo "  Cleaned up assessment #{$testAssessmentId}\n";
}

// --- Summary ---
$r = $GLOBALS['issac_results'];
echo "\n" . str_repeat('─', 40) . "\n";
echo "Results: {$r['passed']} passed, {$r['failed']} failed\n";

if ($r['failed'] > 0) {
    echo "⚠ Some tests failed.\n";
} else {
    echo "All checks passed.\n";
}
