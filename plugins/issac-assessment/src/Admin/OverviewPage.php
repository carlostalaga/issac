<?php

namespace Issac\Admin;

use Issac\Domain\InstrumentRepository;

defined('ABSPATH') || exit;

/**
 * Admin Overview page: headline stats and per-domain breakdown.
 */
final class OverviewPage
{
    public static function render(): void
    {
        $stats = self::gatherStats();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ISSAC Overview', 'issac-assessment'); ?></h1>

            <style>
                .issac-stats { display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap; }
                .issac-stat-card {
                    background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;
                    padding: 20px 24px; min-width: 160px; flex: 1;
                }
                .issac-stat-card h2 { margin: 0 0 4px; font-size: 32px; line-height: 1.2; }
                .issac-stat-card p { margin: 0; color: #646970; }
                .issac-domain-table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                .issac-domain-table th,
                .issac-domain-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #c3c4c7; }
                .issac-domain-table th { background: #f0f0f1; }
                .issac-domain-table td.num { text-align: right; }
                .issac-item-table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                .issac-item-table th,
                .issac-item-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #c3c4c7; }
                .issac-item-table th { background: #f0f0f1; }
                .issac-item-table td.num { text-align: right; }
                .issac-item-table .issac-group-header th { background: #e5e5e5; font-size: 14px; }
            </style>

            <div class="issac-stats">
                <?php foreach ($stats['counters'] as $counter): ?>
                    <div class="issac-stat-card">
                        <h2><?php echo esc_html((string) $counter['value']); ?></h2>
                        <p><?php echo esc_html($counter['label']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2><?php esc_html_e('Per-Domain Statistics', 'issac-assessment'); ?></h2>

            <table class="issac-domain-table widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Domain', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Avg Completion %', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Avg Score', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Participants Answered', 'issac-assessment'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['domains'])): ?>
                        <tr>
                            <td colspan="4"><?php esc_html_e('No assessment data yet.', 'issac-assessment'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats['domains'] as $domain): ?>
                            <tr>
                                <td><?php echo esc_html($domain['title']); ?></td>
                                <td class="num"><?php echo esc_html($domain['avg_completion']); ?>%</td>
                                <td class="num"><?php echo esc_html($domain['avg_score']); ?></td>
                                <td class="num"><?php echo esc_html((string) $domain['participants']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2><?php esc_html_e('Per-Item Statistics', 'issac-assessment'); ?></h2>

            <table class="issac-item-table widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Item', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Avg Score', 'issac-assessment'); ?></th>
                        <th><?php esc_html_e('Answered', 'issac-assessment'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['items'])): ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e('No items found.', 'issac-assessment'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats['items'] as $domain): ?>
                            <tr class="issac-group-header">
                                <th colspan="3"><?php echo esc_html($domain['title']); ?></th>
                            </tr>
                            <?php foreach ($domain['items'] as $item): ?>
                                <tr>
                                    <td><?php echo esc_html($item['label']); ?></td>
                                    <td class="num"><?php echo esc_html($item['avg_score']); ?></td>
                                    <td class="num"><?php echo esc_html((string) $item['answered']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Gather headline counters and per-domain / per-item aggregate stats.
     *
     * @return array{counters: list<array{label: string, value: int}>, domains: list<array>, items: list<array>}
     */
    private static function gatherStats(): array
    {
        global $wpdb;
        $assessments = $wpdb->prefix . 'issac_assessments';
        $responses   = $wpdb->prefix . 'issac_responses';
        $events      = $wpdb->prefix . 'issac_events';

        // Headline counters
        $totalParticipants = (int) $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$assessments}");
        $inProgress        = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$assessments} WHERE status = %s",
            'in_progress'
        ));
        $completed         = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$assessments} WHERE status = %s",
            'completed'
        ));
        $pdfsGenerated     = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$events} WHERE event_key = %s",
            'pdf_generated'
        ));

        $counters = [
            ['label' => __('Total Participants', 'issac-assessment'), 'value' => $totalParticipants],
            ['label' => __('In Progress', 'issac-assessment'),        'value' => $inProgress],
            ['label' => __('Completed', 'issac-assessment'),          'value' => $completed],
            ['label' => __('PDFs Generated', 'issac-assessment'),     'value' => $pdfsGenerated],
        ];

        // Per-domain stats
        $tree = InstrumentRepository::tree();
        $domainStats = [];

        foreach ($tree as $domain) {
            $itemCodes = self::domainItemCodes($domain);
            $totalItems = count($itemCodes);

            if ($totalItems === 0) {
                $domainStats[] = [
                    'title'          => $domain->code . '. ' . $domain->title,
                    'avg_completion' => '0.0',
                    'avg_score'      => '—',
                    'participants'   => 0,
                ];
                continue;
            }

            $placeholders = implode(',', array_fill(0, $totalItems, '%s'));

            // Participants answered: distinct assessments with ≥1 response in this domain
            $participantsAnswered = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT assessment_id) FROM {$responses} WHERE item_code IN ({$placeholders})",
                ...$itemCodes
            ));

            // Avg score across all responses in this domain
            $avgScore = $wpdb->get_var($wpdb->prepare(
                "SELECT ROUND(AVG(score), 1) FROM {$responses} WHERE item_code IN ({$placeholders})",
                ...$itemCodes
            ));

            // Avg completion %: per-assessment answered/total, averaged across assessments
            // that have at least one response in this domain
            $avgCompletion = 0.0;
            if ($participantsAnswered > 0) {
                $avgCompletion = (float) $wpdb->get_var($wpdb->prepare(
                    "SELECT ROUND(AVG(answered / %d * 100), 1)
                     FROM (
                         SELECT assessment_id, COUNT(*) AS answered
                         FROM {$responses}
                         WHERE item_code IN ({$placeholders})
                         GROUP BY assessment_id
                     ) AS per_assessment",
                    $totalItems,
                    ...$itemCodes
                ));
            }

            $domainStats[] = [
                'title'          => $domain->code . '. ' . $domain->title,
                'avg_completion' => number_format($avgCompletion, 1),
                'avg_score'      => $avgScore !== null ? number_format((float) $avgScore, 1) : '—',
                'participants'   => $participantsAnswered,
            ];
        }

        // Per-item stats: one aggregate query, then a PHP lookup against the tree.
        $rows = $wpdb->get_results(
            "SELECT item_code, ROUND(AVG(score), 1) AS avg_score, COUNT(*) AS answered
             FROM {$responses}
             GROUP BY item_code",
            OBJECT_K
        );

        $itemStats = [];
        foreach ($tree as $domain) {
            $domainItems = [];
            foreach ($domain->subsections as $sub) {
                foreach ($sub->items as $item) {
                    if (!$item->isActive) {
                        continue;
                    }
                    $row = $rows[$item->itemCode] ?? null;
                    $domainItems[] = [
                        'label'     => $item->itemCode . '. ' . $item->label,
                        'avg_score' => $row !== null && $row->avg_score !== null
                            ? number_format((float) $row->avg_score, 1)
                            : '—',
                        'answered'  => $row !== null ? (int) $row->answered : 0,
                    ];
                }
            }
            $itemStats[] = [
                'title' => $domain->code . '. ' . $domain->title,
                'items' => $domainItems,
            ];
        }

        return ['counters' => $counters, 'domains' => $domainStats, 'items' => $itemStats];
    }

    /**
     * Collect all active item codes for a domain.
     *
     * @return string[]
     */
    private static function domainItemCodes(\Issac\Domain\DomainNode $domain): array
    {
        $codes = [];
        foreach ($domain->subsections as $sub) {
            foreach ($sub->items as $item) {
                if ($item->isActive) {
                    $codes[] = $item->itemCode;
                }
            }
        }
        return $codes;
    }
}
