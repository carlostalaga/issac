<?php
/**
 * Admin dashboard smoke test for ISSAC (Milestone 8).
 *
 * Run: wp eval-file wp-content/plugins/issac-assessment/tests/admin-smoke.php --user=1
 */

use Issac\Admin\AdminMenu;
use Issac\Admin\OverviewPage;
use Issac\Admin\UserDetailPage;
use Issac\Admin\UsersListTable;
use Issac\Domain\AssessmentRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Install\Capabilities;

$GLOBALS['issac_results'] = ['passed' => 0, 'failed' => 0];

function issac_admin_assert(bool $condition, string $label): void
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
global $wpdb;

// Ensure caps are synced
Capabilities::install();

// Clean any pre-existing test assessment for this user
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

// ── 1. issac_manager role ───────────────────────────────────────────
echo "\n1. issac_manager role\n";

$managerRole = get_role('issac_manager');
issac_admin_assert($managerRole !== null, 'issac_manager role exists');

if ($managerRole) {
    issac_admin_assert($managerRole->has_cap('read'), 'Manager has "read"');
    issac_admin_assert($managerRole->has_cap('issac_take_assessment'), 'Manager has "issac_take_assessment"');
    issac_admin_assert($managerRole->has_cap('issac_edit_instrument'), 'Manager has "issac_edit_instrument"');
    issac_admin_assert($managerRole->has_cap('issac_view_admin'), 'Manager has "issac_view_admin"');
}

// ── 2. Administrator has issac_view_admin ────────────────────────────
echo "\n2. Administrator capabilities\n";

issac_admin_assert($user->has_cap('issac_view_admin'), 'Administrator has issac_view_admin');
issac_admin_assert($user->has_cap('issac_edit_instrument'), 'Administrator has issac_edit_instrument');

// ── 3. Submenu registration ─────────────────────────────────────────
echo "\n3. Admin submenu registration\n";

// Force admin_menu to fire so submenus are registered
set_current_screen('dashboard');
do_action('admin_menu');

global $submenu;
$issacSubmenu = $submenu['issac'] ?? [];

$slugs = array_column($issacSubmenu, 2);
issac_admin_assert(in_array('issac', $slugs, true), 'Overview submenu registered (slug "issac")');
issac_admin_assert(in_array('issac-users', $slugs, true), 'Users submenu registered');

// ── 4. Overview page renders with test data ─────────────────────────
echo "\n4. Overview page rendering\n";

// Create a test assessment with some responses
$assessment = AssessmentRepository::findOrCreate($user->ID);
$tree       = InstrumentRepository::tree();

$testCodes = [];
foreach ($tree as $domain) {
    foreach ($domain->subsections as $sub) {
        foreach ($sub->items as $item) {
            if ($item->isActive) {
                $testCodes[] = $item->itemCode;
                break 2;
            }
        }
    }
}

foreach ($testCodes as $code) {
    ResponseRepository::upsert((int) $assessment->id, $code, 3);
}

ob_start();
OverviewPage::render();
$overviewHtml = ob_get_clean();

issac_admin_assert(str_contains($overviewHtml, 'issac-stat-card'), 'Overview contains stat cards');
issac_admin_assert(str_contains($overviewHtml, 'issac-domain-table'), 'Overview contains domain table');
issac_admin_assert(str_contains($overviewHtml, 'Total Participants') || str_contains($overviewHtml, 'ISSAC Overview'), 'Overview contains expected content');

// ── 5. UsersListTable ───────────────────────────────────────────────
echo "\n5. UsersListTable\n";

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$_REQUEST = [];
$table = new UsersListTable();
$table->prepare_items();

issac_admin_assert(!empty($table->items), 'UsersListTable has items');

$found = false;
foreach ($table->items as $item) {
    if ((int) $item->user_id === $user->ID) {
        $found = true;
        break;
    }
}
issac_admin_assert($found, 'Test user appears in UsersListTable');

// ── 6. UserDetailPage ───────────────────────────────────────────────
echo "\n6. UserDetailPage rendering\n";

$_GET['assessment_id'] = (string) $assessment->id;

ob_start();
UserDetailPage::render();
$detailHtml = ob_get_clean();

issac_admin_assert(str_contains($detailHtml, esc_html($user->display_name)), 'Detail page shows user display name');
issac_admin_assert(str_contains($detailHtml, 'Download their PDF'), 'Detail page has download button');
issac_admin_assert(str_contains($detailHtml, 'issac-overall-card'), 'Detail page shows overall card');
issac_admin_assert(str_contains($detailHtml, 'issac-domains-table'), 'Detail page shows domain breakdown');
issac_admin_assert(str_contains($detailHtml, 'Back to Users'), 'Detail page has back link');

unset($_GET['assessment_id']);

// ── 7. UserDetailPage — invalid assessment ──────────────────────────
echo "\n7. UserDetailPage — invalid assessment\n";

$_GET['assessment_id'] = '999999';

ob_start();
UserDetailPage::render();
$errorHtml = ob_get_clean();

issac_admin_assert(str_contains($errorHtml, 'not found') || str_contains($errorHtml, 'Assessment not found'), 'Invalid assessment shows error');
issac_admin_assert(str_contains($errorHtml, 'Back to Users'), 'Error page has back link');

unset($_GET['assessment_id']);

// ── 8. Access gate — user without issac_view_admin ──────────────────
echo "\n8. Access gate\n";

// issac_participant should NOT have view_admin
$participantRole = get_role('issac_participant');
issac_admin_assert(
    $participantRole !== null && !$participantRole->has_cap('issac_view_admin'),
    'issac_participant does NOT have issac_view_admin'
);

// ── Cleanup ──────────────────────────────────────────────────────────
echo "\n9. Cleanup\n";

$wpdb->delete($wpdb->prefix . 'issac_events', ['assessment_id' => (int) $assessment->id]);
$wpdb->delete($wpdb->prefix . 'issac_responses', ['assessment_id' => (int) $assessment->id]);
$wpdb->delete($wpdb->prefix . 'issac_assessments', ['id' => (int) $assessment->id]);

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
