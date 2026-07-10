<?php
/**
 * Domain assessment template.
 *
 * Variables in scope:
 * @var \Issac\Domain\DomainNode $domain
 * @var array<string, int>       $responses   item_code => score
 * @var array                    $domainSummary
 */

defined('ABSPATH') || exit;
?>
<div class="issac-domain" data-domain-code="<?= esc_attr($domain->code) ?>">

    <header class="issac-domain__header">
        <h1><?= esc_html($domain->title) ?></h1>
        <div class="issac-domain__description"><?= wp_kses_post($domain->description) ?></div>
    </header>

    <div class="issac-domain__progress-sticky my-5">
        <div class="issac-domain__progress progress" role="progressbar" aria-valuenow="<?= (int) round($domainSummary['completion']) ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="issac-domain__progress-bar progress-bar" style="width: <?= esc_attr($domainSummary['completion']) ?>%"></div>
        </div>
        <span class="issac-domain__progress-text">
            <?= (int) $domainSummary['answered'] ?>/<?= (int) $domainSummary['total'] ?>
            items &middot; <?= esc_html($domainSummary['completion']) ?>%
        </span>
    </div>

    <?php foreach ($domain->subsections as $subsection) : ?>
    <section class="issac-subsection">
        <h4 class="issac-subsection__title">— <?= esc_html($subsection->title) ?></h4>

        <?php foreach ($subsection->items as $item) : ?>
        <?php if (!$item->isActive) continue; ?>
        <?php
                $currentScore = $responses[$item->itemCode] ?? 0;
                $activeDescriptor = match (true) {
                    $currentScore >= 5 => 5,
                    $currentScore >= 3 => 3,
                    $currentScore >= 1 => 1,
                    default            => 0,
                };
            ?>
        <article class="issac-item p-5 my-5" data-item-code="<?= esc_attr($item->itemCode) ?>">
            <div class="issac-item__prompt mb-2">
                <span class="issac-item__code"><?= esc_html($item->itemCode) ?></span>
                <span class="issac-item__prompt"><?= esc_html($item->prompt) ?></span>
            </div>

            <fieldset class="issac-item__scores my-5">
                <legend class="visually-hidden">Score for item <?= esc_attr($item->itemCode) ?></legend>
                <?php for ($score = 1; $score <= 5; $score++) : ?>
                <input type="radio" class="btn-check" name="score_<?= esc_attr($item->itemCode) ?>" id="score_<?= esc_attr($item->itemCode) ?>_<?= $score ?>" value="<?= $score ?>" autocomplete="off" <?php checked($currentScore, $score); ?>>
                <label class="btn btn-brand-accent issac-score__btn" for="score_<?= esc_attr($item->itemCode) ?>_<?= $score ?>"><?= $score ?></label>
                <?php endfor; ?>
            </fieldset>

            <div class="issac-item__descriptors row">
                <div class="col-md-4 issac-descriptor issac-descriptor--1<?= $activeDescriptor === 1 ? ' issac-descriptor--active' : '' ?>">
                    <strong class="issac-descriptor__label">Exploring</strong>
                    <div class="issac-descriptor__text"><?= wp_kses_post($item->descriptor1) ?></div>
                </div>
                <div class="col-md-4 issac-descriptor issac-descriptor--3<?= $activeDescriptor === 3 ? ' issac-descriptor--active' : '' ?>">
                    <strong class="issac-descriptor__label">Implementing</strong>
                    <div class="issac-descriptor__text"><?= wp_kses_post($item->descriptor3) ?></div>
                </div>
                <div class="col-md-4 issac-descriptor issac-descriptor--5<?= $activeDescriptor === 5 ? ' issac-descriptor--active' : '' ?>">
                    <strong class="issac-descriptor__label">Sustained Action</strong>
                    <div class="issac-descriptor__text"><?= wp_kses_post($item->descriptor5) ?></div>
                </div>
            </div>

            <div class="issac-item__status" aria-live="polite"></div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endforeach; ?>

</div>

<div id="issac-toast-template" class="toast d-none" role="status" aria-live="polite" aria-atomic="true">
    <div class="toast-body"></div>
</div>