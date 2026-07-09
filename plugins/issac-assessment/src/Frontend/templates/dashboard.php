<?php
/**
 * Participant dashboard template.
 *
 * Variables in scope:
 * @var \Issac\Domain\DomainNode[] $tree
 * @var array                      $summary
 * @var object                     $assessment
 */

use Issac\Frontend\Shortcodes;

defined('ABSPATH') || exit;

$overall    = $summary['overall'];
$completion = $overall['completion'];
$answered   = (int) $overall['answered'];
$total      = (int) $overall['total'];
$percent    = (int) round($completion);

// SVG ring geometry
$ringSize   = 120;
$strokeWidth = 10;
$radius     = ($ringSize - $strokeWidth) / 2;
$circumference = 2 * M_PI * $radius;
$offset     = $circumference - ($circumference * $completion / 100);
?>
<div class="issac-dashboard container">

    <section class="issac-dashboard__overview text-center mb-4">
        <div class="issac-dashboard__ring-wrap" role="img" aria-label="<?= esc_attr("Overall progress: {$percent}%") ?>">
            <svg class="issac-dashboard__ring" width="<?= $ringSize ?>" height="<?= $ringSize ?>" viewBox="0 0 <?= $ringSize ?> <?= $ringSize ?>">
                <circle class="issac-dashboard__ring-bg" cx="<?= $ringSize / 2 ?>" cy="<?= $ringSize / 2 ?>" r="<?= $radius ?>" fill="none" stroke-width="<?= $strokeWidth ?>" />
                <circle class="issac-dashboard__ring-fill" cx="<?= $ringSize / 2 ?>" cy="<?= $ringSize / 2 ?>" r="<?= $radius ?>" fill="none" stroke-width="<?= $strokeWidth ?>" stroke-dasharray="<?= esc_attr((string) $circumference) ?>" stroke-dashoffset="<?= esc_attr((string) $offset) ?>" transform="rotate(-90 <?= $ringSize / 2 ?> <?= $ringSize / 2 ?>)" />
            </svg>
            <div class="issac-dashboard__ring-label">
                <span class="issac-dashboard__ring-percent"><?= $percent ?>%</span>
                <span class="issac-dashboard__ring-caption"><?= $answered ?>/<?= $total ?> items</span>
            </div>
        </div>
        <span class="visually-hidden">Overall progress: <?= $percent ?>%, <?= $answered ?> of <?= $total ?> items answered.</span>
    </section>

    <section class="issac-dashboard__domains">
        <div class="row g-4">
            <?php foreach ($summary['domains'] as $i => $domainSummary) :
                $domainNode  = $tree[$i];
                $dCompletion = $domainSummary['completion'];
                $dAnswered   = (int) $domainSummary['answered'];
                $dTotal      = (int) $domainSummary['total'];
                $dCode       = $domainSummary['code'];
                $ctaLabel    = Shortcodes::domainCtaLabel($dCompletion);
                $domainUrl   = add_query_arg('d', $dCode, trailingslashit(get_permalink()) . 'domain/');
            ?>
            <div class="col-12 col-md-6">
                <div class="card issac-dashboard__card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2 fosforos">
                            <h6 class="text-brand-accent"><?= esc_html($domainSummary['title']) ?></h6>
                        </div>
                        <div class="issac-dashboard__card-desc mb-3"><?= wp_kses_post($domainNode->description) ?></div>

                        <div class="progress mb-2" role="progressbar" aria-valuenow="<?= (int) round($dCompletion) ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= esc_attr($domainSummary['title'] . ' progress') ?>">
                            <div class="progress-bar" style="width: <?= esc_attr($dCompletion) ?>%"></div>
                        </div>

                        <div class="issac-dashboard__card-stats small text-body-secondary mb-3">
                            <?php if ($dAnswered >= 1) : ?>
                            <span class="issac-dashboard__card-avg">Avg <?= esc_html(number_format($domainSummary['average'], 1)) ?> &middot; <?= esc_html($domainSummary['band']) ?></span>
                            <span class="issac-dashboard__card-count"><?= $dAnswered ?>/<?= $dTotal ?> answered</span>
                            <?php else : ?>
                            <span class="issac-dashboard__card-count"><?= $dAnswered ?>/<?= $dTotal ?> answered</span>
                            <?php endif; ?>
                        </div>

                        <?php
                        $ctaClass = $dCompletion >= 100.0
                            ? 'btn btn-brand-accent-outline'
                            : 'btn btn-brand-accent';
                        ?>
                        <a href="<?= esc_url($domainUrl) ?>" class="<?= esc_attr($ctaClass) ?> mt-auto issac-dashboard__cta"><?= esc_html($ctaLabel) ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="issac-dashboard__actions text-center mt-4">
        <?php
        $downloadLabel = $completion >= 100.0 ? 'Download report' : 'Download progress report';
        $reportUrl     = wp_nonce_url(rest_url('issac/v1/report'), 'wp_rest');
        ?>
        <?php if ($answered >= 1) : ?>
        <a href="<?= esc_url($reportUrl) ?>" class="btn btn-brand-accent-outline issac-dashboard__download">
            <?= esc_html($downloadLabel) ?>
        </a>
        <?php else : ?>
        <button type="button" class="btn btn-brand-accent-outline issac-dashboard__download" disabled aria-disabled="true">
            <?= esc_html($downloadLabel) ?>
        </button>
        <?php endif; ?>
        <?php if ($lastReportDate !== null) : ?>
        <small class="d-block text-body-secondary mt-2">
            Last report: <?= esc_html(wp_date('j M Y, g:i a', strtotime($lastReportDate . ' UTC'))) ?>
        </small>
        <?php endif; ?>
    </section>

</div>