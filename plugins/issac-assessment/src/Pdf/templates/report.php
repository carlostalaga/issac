<?php
/**
 * PDF report HTML template for mPDF.
 *
 * Variables in scope:
 * @var array                      $summary
 * @var \Issac\Domain\DomainNode[] $tree
 * @var array<string, int>         $responses
 * @var object                     $assessment
 * @var \WP_User                   $user
 */

use Issac\Domain\ScoringService;

defined('ABSPATH') || exit;

$overall         = $summary['overall'];
$generatedDate   = wp_date('j F Y');
$statusFormatted = esc_html(ucwords(str_replace('_', ' ', (string) $assessment->status)));

/**
 * Collect inactive items that still have stored responses.
 *
 * @return \Issac\Domain\ItemNode[]
 */
$retiredAnsweredItems = static function (\Issac\Domain\DomainNode $domain) use ($responses): array {
    $items = [];

    foreach ($domain->subsections as $subsection) {
        foreach ($subsection->items as $item) {
            if (!$item->isActive && isset($responses[$item->itemCode])) {
                $items[] = $item;
            }
        }
    }

    return $items;
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ISSAC Assessment Report</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10pt;
            color: #212529;
            line-height: 1.4;
        }
        h1 {
            font-size: 18pt;
            color: #0d6efd;
            margin: 0 0 8px 0;
        }
        h2 {
            font-size: 13pt;
            color: #212529;
            margin: 24px 0 8px 0;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
        }
        h3 {
            font-size: 11pt;
            color: #495057;
            margin: 16px 0 6px 0;
        }
        .cover-table {
            width: 100%;
            margin-bottom: 24px;
        }
        .cover-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .cover-label {
            width: 35%;
            font-weight: bold;
            color: #495057;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .data-table tr.overall-row td {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .progress-row {
            margin-bottom: 10px;
        }
        .progress-label {
            font-size: 9pt;
            margin-bottom: 3px;
        }
        .progress-track {
            width: 100%;
            height: 12px;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
        }
        .progress-fill {
            height: 12px;
            background-color: #0d6efd;
        }
        .retired-row td {
            color: #6c757d;
            background-color: #f8f9fa;
        }
        .retired-note {
            font-size: 8pt;
            color: #6c757d;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <h1>ISSAC Assessment Report</h1>

    <table class="cover-table">
        <tr>
            <td class="cover-label">Participant</td>
            <td><?= esc_html($user->display_name) ?></td>
        </tr>
        <tr>
            <td class="cover-label">Date generated</td>
            <td><?= esc_html($generatedDate) ?></td>
        </tr>
        <tr>
            <td class="cover-label">Overall completion</td>
            <td><?= esc_html((string) $overall['answered']) ?>/<?= esc_html((string) $overall['total']) ?> items &middot; <?= esc_html((string) $overall['completion']) ?>%</td>
        </tr>
        <tr>
            <td class="cover-label">ISSAC instrument version</td>
            <td><?= esc_html((string) $assessment->instrument_version) ?></td>
        </tr>
        <tr>
            <td class="cover-label">Assessment status</td>
            <td><?= $statusFormatted ?></td>
        </tr>
    </table>

    <h2>Summary</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Domain</th>
                <th>Items answered</th>
                <th>Average</th>
                <th>Band</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['domains'] as $domainSummary) : ?>
            <tr>
                <td><?= esc_html($domainSummary['code'] . '. ' . $domainSummary['title']) ?></td>
                <td><?= esc_html((string) $domainSummary['answered']) ?>/<?= esc_html((string) $domainSummary['total']) ?></td>
                <td><?= (int) $domainSummary['answered'] > 0 ? esc_html(number_format((float) $domainSummary['average'], 1)) : '&mdash;' ?></td>
                <td><?= (int) $domainSummary['answered'] > 0 ? esc_html($domainSummary['band']) : '&mdash;' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="overall-row">
                <td>Overall</td>
                <td><?= esc_html((string) $overall['answered']) ?>/<?= esc_html((string) $overall['total']) ?></td>
                <td><?= (int) $overall['answered'] > 0 ? esc_html(number_format((float) $overall['average'], 1)) : '&mdash;' ?></td>
                <td><?= (int) $overall['answered'] > 0 ? esc_html($overall['band']) : '&mdash;' ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Domain Progress</h2>

    <?php foreach ($summary['domains'] as $domainSummary) :
        $barWidth = max(0, min(100, (float) $domainSummary['completion']));
    ?>
    <div class="progress-row">
        <div class="progress-label">
            <?= esc_html($domainSummary['code'] . '. ' . $domainSummary['title']) ?>
            &mdash; <?= esc_html((string) $domainSummary['completion']) ?>%
            (<?= esc_html((string) $domainSummary['answered']) ?>/<?= esc_html((string) $domainSummary['total']) ?>)
        </div>
        <div class="progress-track">
            <div class="progress-fill" style="width: <?= esc_attr((string) $barWidth) ?>%;"></div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="page-break"></div>

    <h2>Detailed Results</h2>

    <?php foreach ($tree as $i => $domain) :
        $domainSummary = $summary['domains'][$i];
    ?>
    <h2><?= esc_html($domain->code . '. ' . $domain->title) ?></h2>

    <?php foreach ($domain->subsections as $j => $subsection) :
        $subsectionSummary = $domainSummary['subsections'][$j];
    ?>
    <h3><?= esc_html($subsection->title) ?></h3>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Code</th>
                <th style="width: 62%;">Item prompt</th>
                <th style="width: 10%;">Score</th>
                <th style="width: 20%;">Band</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subsection->items as $item) :
                if (!$item->isActive) {
                    continue;
                }
                $score = $responses[$item->itemCode] ?? null;
                $itemBand = $score !== null
                    ? ScoringService::band((float) $score)
                    : ScoringService::BAND_NOT_RATED;
            ?>
            <tr>
                <td><?= esc_html($item->itemCode) ?></td>
                <td><?= esc_html($item->prompt) ?></td>
                <td><?= $score !== null ? esc_html((string) $score) : '&mdash;' ?></td>
                <td><?= $score !== null ? esc_html($itemBand) : '&mdash;' ?></td>
            </tr>
            <?php endforeach; ?>

            <?php foreach ($retiredAnsweredItems($domain) as $retiredItem) :
                if ($retiredItem->subsectionId !== $subsection->id) {
                    continue;
                }
                $score = $responses[$retiredItem->itemCode];
                $itemBand = ScoringService::band((float) $score);
            ?>
            <tr class="retired-row">
                <td><?= esc_html($retiredItem->itemCode) ?></td>
                <td>
                    <?= esc_html($retiredItem->prompt) ?>
                    <span class="retired-note">(item retired)</span>
                </td>
                <td><?= esc_html((string) $score) ?></td>
                <td><?= esc_html($itemBand) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>
    <?php endforeach; ?>

</body>

</html>