<?php
/**
 * Block: Content Basic
 *
 * A flexible content block that supports various layouts with headline, content, and optional image.
 * Supports different column arrangements and image alignments.
 */

// Get field values from ACF
$content_basic_columns = get_sub_field('content_basic_columns');
$content_basic_label = get_sub_field('content_basic_label');
$content_basic_headline = get_sub_field('content_basic_headline');
$content_basic_content = get_sub_field('content_basic_content');
$content_basic_optional_image = get_sub_field('content_basic_optional_image');
$content_basic_image = get_sub_field('content_basic_image');

// Square (side-by-side) and wide (content_wide) image URLs; alt text
$content_basic_image_url_square = '';
$content_basic_image_url_wide = '';
$content_basic_image_alt = '';

if ($content_basic_image):
    $content_basic_image_url_square = $content_basic_image['sizes']['5-7r686'];
    $content_basic_image_url_wide = $content_basic_image['sizes']['1080p'];
    $content_basic_image_alt = $content_basic_image['alt'];
endif;

$content_basic_image_alignment = get_sub_field('content_basic_image_alignment');
$content_basic_image_is_a_logo = get_sub_field('content_basic_image_is_a_logo');

$newspaper_class = ($content_basic_columns == '2_col') ? 'newspaper content-basic__columns' : '';

if ($content_basic_optional_image):
    $content_column_class = $content_basic_image_is_a_logo ? 'col-12 col-sm-9 col-xxl-10' : 'col-md-5 col-12';
    $image_column_class = $content_basic_image_is_a_logo ? 'd-none d-sm-block col-sm-3 col-xxl-2' : 'col-md-7';
else:
    $content_column_class = 'col-12';
    $image_column_class = 'col-md-2';
endif;
$image_order_class = ($content_basic_image_alignment == 'left') ? 'order-0 pe-5' : 'order-1 ps-5';
// Pre-render shared content sections to avoid duplication
ob_start();
if ($content_basic_label): ?>
<div class="mb-4">
    <h6 class="text-uppercase"><?php echo $content_basic_label; ?></h6>
</div>
<?php endif;
if ($content_basic_headline): ?>
<h2 class="mb-5 brand-accent"><?php echo esc_html($content_basic_headline); ?></h2>
<?php endif;
$headline_html = ob_get_clean();

ob_start();
if ($content_basic_content): ?>
<div class="<?php echo esc_attr($newspaper_class); ?>">
    <div class="content-basic-content"><?php echo $content_basic_content; ?></div>
</div>
<?php endif;
$content_html = ob_get_clean();

$resources_html = '';
if (have_rows('content_basic_resource_repeater')):
    ob_start(); ?>
<div class="mt-5">
    <?php echo display_resources('content_basic_resource_repeater', true); ?>
</div>
<?php
    $resources_html = ob_get_clean();
endif;

?>

<!-- Content Basic Block -->
<div id="<?php echo esc_attr('content-' . $iBlock); ?>" class="container-fluid py-5 px-5 px-md-0">

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-12">


                <div class="container">

                    <?php if ($content_basic_image_alignment == 'content_wide'): ?>

                    <!-- "content_wide" layout: headline, content, resources, then image -->
                    <div class="row g-5">
                        <div class="col-12 contentBox bg-transparent">
                            <?php echo $headline_html; ?>
                            <?php echo $content_html; ?>
                            <?php echo $resources_html; ?>
                        </div>

                        <?php if ($content_basic_optional_image): ?>
                        <div class="col-12">
                            <img src="<?php echo esc_url($content_basic_image_url_wide); ?>" class="img-fluid" alt="<?php echo esc_attr($content_basic_image_alt); ?>">
                        </div>
                        <?php endif; ?>

                    </div>

                    <?php else: ?>
                    <!-- "left" or "right" layout: image and content side by side -->
                    <div class="row g-5 py-0">

                        <?php if ($content_basic_optional_image): ?>
                        <div class="mb-5 <?php echo esc_attr(trim($image_column_class . ' ' . $image_order_class)); ?>">
                            <img src="<?php echo esc_url($content_basic_image_url_square); ?>" class="img-fluid" alt="<?php echo esc_attr($content_basic_image_alt); ?>">
                        </div>
                        <?php endif; ?>

                        <?php if ($content_html): ?>
                        <div class="<?php echo esc_attr($content_column_class); ?> contentBox p-0 d-flex align-items-center">
                            <div>
                                <div class="col-12">
                                    <?php echo $headline_html; ?>
                                </div>

                                <div>
                                    <?php echo $content_html; ?>
                                </div>

                                <?php if ($resources_html): ?>
                                <div class="col-12 order-3">
                                    <?php echo $resources_html; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>



                    </div>
                    <?php endif; ?>


                </div>
            </div>
        </div>


    </div>
</div>