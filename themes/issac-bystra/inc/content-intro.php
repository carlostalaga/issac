<?php
/**
 * Content Intro (Reusable)
 *
 * Simplified variant of the Content Basic block.
 * Uses only:
 * - content_columns
 * - content_content
 *
 * Usage example:
 * get_template_part('inc/content-intro');
 */

/* =========================================
 * ACF values
 * =======================================*/
$content_columns = get_sub_field('content_columns');
$content_content = get_sub_field('content_content');
$content_intro_post_title = get_the_title();

// Fallback to get_field() when this template is used outside flexible content loops.
if (!$content_columns):
    $content_columns = get_field('content_columns');
endif;

if (!$content_content):
    $content_content = get_field('content_content');
endif;

/* =========================================
 * Layout modifiers
 * =======================================*/
$content_newspaper_class = ($content_columns === '2_col') ? 'newspaper' : '';

/* =========================================
 * Guard clause
 * =======================================*/
if (!$content_intro_post_title && !$content_content):
    return;
endif;
?>

<!-- =========================================
     Content Intro markup
========================================= -->
<section class="container-fluid py-5 px-5 px-md-0 bg-brand-main">
    <div class="container my-5 py-5">
        <div class="row">
            <div class="col-12 contentBox bg-transparent">
                <!-- Post title -->
                <?php if ($content_intro_post_title): ?>
                <h1 class="mb-5">
                    <?php the_title(); ?>
                </h1>
                <?php endif; ?>

                <!-- ACF content -->
                <?php if ($content_content): ?>
                <div class="<?php echo esc_attr($content_newspaper_class); ?>">
                    <?php echo wp_kses_post($content_content); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>