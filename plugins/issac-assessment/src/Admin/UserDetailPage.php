<?php

namespace Issac\Admin;

use Issac\Domain\AssessmentRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;
use Issac\Install\Capabilities;
use Issac\Pdf\ReportGenerator;

defined('ABSPATH') || exit;

/**
 * Read-only view of a single user's assessment, with admin PDF download.
 */
final class UserDetailPage
{
    public static function render(): void
    {
        if (!current_user_can(Capabilities::VIEW_ADMIN)) {
            wp_die(
                esc_html__('You do not have permission to view this page.', 'issac-assessment'),
                403
            );
        }

        $assessmentId = isset($_GET['assessment_id']) ? absint($_GET['assessment_id']) : 0;
        if ($assessmentId === 0) {
            self::renderError(__('No assessment specified.', 'issac-assessment'));
            return;
        }

        $assessment = AssessmentRepository::getById($assessmentId);
        if ($assessment === null) {
            self::renderError(__('Assessment not found.', 'issac-assessment'));
            return;
        }

        $user = get_userdata((int) $assessment->user_id);
        if ($user === false) {
            self::renderError(__('User not found for this assessment.', 'issac-assessment'));
            return;
        }

        $tree      = InstrumentRepository::tree();
        $responses = ResponseRepository::forAssessment($assessmentId);
        $summary   = ScoringService::summary($tree, $responses);

        $downloadUrl = wp_nonce_url(
            admin_url('admin-post.php?action=issac_download_user_pdf&assessment_id=' . $assessmentId),
            'issac_download_pdf_' . $assessmentId
        );

        $backUrl = admin_url('admin.php?page=issac-users');
        $statusLabel = $assessment->status === 'completed'
            ? __('Completed', 'issac-assessment')
            : __('In Progress', 'issac-assessment');
        ?>
        <div class="wrap">
            <p><a href="<?php echo esc_url($backUrl); ?>">&larr; <?php esc_html_e('Back to Users', 'issac-assessment'); ?></a></p>
            <h1><?php echo esc_html($user->display_name); ?></h1>

            <style>
                .issac-detail-meta { margin: 12px 0 24px; }
                .issac-detail-meta span { margin-right: 24px; color: #646970; }
                .issac-overall-card {
                    background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;
                    padding: 20px 24px; margin-bottom: 24px; display: inline-block;
                }
                .issac-overall-card h2 { margin: 0 0 4px; font-size: 28px; }
                .issac-overall-card p { margin: 4px 0; color: #646970; }
                .issac-domains-table { border-collapse: collapse; width: 100%; margin-bottom: 24px; }
                .issac-domains-table th,
                .issac-domains-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #c3c4c7; }
                .issac-domains-table th { background: #f0f0f1; }
                .issac-domains-table td.num { text-align: right; }
                .issac-progress { background: #dcdcde; border-radius: 3px; height: 12px; width: 120px; display: inline-block; vertical-align: middle; }
                .issac-progress-bar { background: #2271b1; border-radius: 3px; height: 100%; }
            </style>

            <div class="issac-detail-meta">
                <span><strong><?php esc_html_e('Email:', 'issac-assessment'); ?></strong> <?php echo esc_html($user->user_email); ?></span>
                <span><strong><?php esc_html_e('Status:', 'issac-assessment'); ?></strong> <?php echo esc_html($statusLabel); ?></span>
                <span><strong><?php esc_html_e('Started:', 'issac-assessment'); ?></strong> <?php echo esc_html(wp_date(get_option('date_format'), strtotime($assessment->started_at))); ?></span>
                <span><strong><?php esc_html_e('Last Activity:', 'issac-assessment'); ?></strong> <?php echo esc_html(wp_date(get_option('date_format'), strtotime($assessment->updated_at))); ?></span>
                <?php if (!empty($assessment->instrument_version)): ?>
                    <span><strong><?php esc_html_e('Instrument:', 'issac-assessment'); ?></strong> v<?php echo esc_html($assessment->instrument_version); ?></span>
                <?php endif; ?>
            </div>

            <div class="issac-overall-card">
                <h2><?php echo esc_html(number_format($summary['overall']['completion'], 1)); ?>% <?php esc_html_e('complete', 'issac-assessment'); ?></h2>
                <p><?php
                    printf(
                        /* translators: 1: answered count, 2: total count */
                        esc_html__('%1$d of %2$d items answered', 'issac-assessment'),
                        $summary['overall']['answered'],
                        $summary['overall']['total']
                    );
                ?></p>
                <p><?php
                    $avg = $summary['overall']['average'];
                    printf(
                        /* translators: 1: average score, 2: band label */
                        esc_html__('Average: %1$s · Band: %2$s', 'issac-assessment'),
                        $avg !== null ? number_format($avg, 1) : '—',
                        $summary['overall']['band']
                    );
                ?></p>
            </div>

            <h2><?php esc_html_e('Domain Breakdown', 'issac-assessment'); ?></h2>

            <table class="issac-domains-table widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Domain', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Items Answered', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Progress', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Average', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Band', 'issac-assessment'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary['domains'] as $domain): ?>
                        <tr>
                            <td><?php echo esc_html($domain['code'] . '. ' . $domain['title']); ?></td>
                            <td class="num"><?php echo esc_html($domain['answered'] . ' / ' . $domain['total']); ?></td>
                            <td>
                                <span class="issac-progress">
                                    <span class="issac-progress-bar" style="width: <?php echo esc_attr(min(100, $domain['completion'])); ?>%"></span>
                                </span>
                                <?php echo esc_html(number_format($domain['completion'], 1)); ?>%
                            </td>
                            <td class="num"><?php echo esc_html($domain['average'] !== null ? number_format($domain['average'], 1) : '—'); ?></td>
                            <td><?php echo esc_html($domain['band']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <a href="<?php echo esc_url($downloadUrl); ?>" class="button button-primary">
                    <?php esc_html_e('Download their PDF', 'issac-assessment'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * admin-post.php handler: generates and streams the PDF for an assessment
     * without recording the pdf_generated event on the participant's history.
     */
    public static function downloadPdf(): void
    {
        if (!current_user_can(Capabilities::VIEW_ADMIN)) {
            wp_die(esc_html__('Permission denied.', 'issac-assessment'), 403);
        }

        $assessmentId = isset($_GET['assessment_id']) ? absint($_GET['assessment_id']) : 0;
        check_admin_referer('issac_download_pdf_' . $assessmentId);

        $assessment = AssessmentRepository::getById($assessmentId);
        if ($assessment === null) {
            wp_die(esc_html__('Assessment not found.', 'issac-assessment'), 404);
        }

        // ReportGenerator::generate('save') records a pdf_generated event on the
        // participant's assessment. An admin download must not alter the participant's
        // event history, so we snapshot the existing event (if any) before generating
        // and restore/remove it afterward.
        global $wpdb;
        $eventsTable  = $wpdb->prefix . 'issac_events';
        $aid          = absint($assessment->id);
        $oldTimestamp  = $wpdb->get_var($wpdb->prepare(
            "SELECT created_at FROM {$eventsTable} WHERE assessment_id = %d AND event_key = %s",
            $aid,
            'pdf_generated'
        ));

        $path = ReportGenerator::generate($assessment, 'save');

        if ($path === null || !file_exists($path)) {
            wp_die(esc_html__('Failed to generate PDF.', 'issac-assessment'), 500);
        }

        // Undo the event side-effect from ReportGenerator.
        if ($oldTimestamp !== null) {
            $wpdb->update(
                $eventsTable,
                ['created_at' => $oldTimestamp],
                ['assessment_id' => $aid, 'event_key' => 'pdf_generated'],
                ['%s'],
                ['%d', '%s']
            );
        } else {
            $wpdb->delete(
                $eventsTable,
                ['assessment_id' => $aid, 'event_key' => 'pdf_generated'],
                ['%d', '%s']
            );
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        $user = get_userdata((int) $assessment->user_id);
        $name = $user ? sanitize_file_name($user->display_name) : 'user';
        $filename = 'ISSAC-Report-' . $name . '-' . gmdate('Y-m-d') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        unlink($path);
        exit;
    }

    private static function renderError(string $message): void
    {
        echo '<div class="wrap">';
        echo '<p><a href="' . esc_url(admin_url('admin.php?page=issac-users')) . '">&larr; ';
        esc_html_e('Back to Users', 'issac-assessment');
        echo '</a></p>';
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
        echo '</div>';
    }
}
