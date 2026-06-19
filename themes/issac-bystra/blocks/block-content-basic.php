<?php
/**
 * Block: Content Basic
 *
 * A flexible content block that supports various layouts with headline, content, and optional image.
 * Supports different column arrangements and image alignments.
 */

// Get field values from ACF
$content_basic_hero = get_sub_field('content_basic_hero');
if ($content_basic_hero):
    $content_basic_hero_class = ' content-basic-hero'; //  spacing is needed to prevent merge into the background class.
else:
    $content_basic_hero_class = ' content-basic-regular';
endif;
$bg = get_block_background();
$content_basic_columns = get_sub_field('content_basic_columns');
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

$content_basic_mask_paths = array(
    'M876.9,113.3c-56,7.3-118.7,21.4-188.2,43.5-61,19.4-127.2,45.1-198.6,78.1-3.8,1.8-7.7,3.6-11.6,5.4-16.3,7.7-32.5,15.5-48.5,23.3-43.4,21.5-85.4,43.7-125.7,66.3-12.3,6.9-24.4,13.8-36.3,20.7-32.5,18.9-63.9,37.9-94,56.9-33.5,21.1-65.4,42.2-95.7,62.9.5.8,1,1.6,1.6,2.5,37,57.3,84.4,105.7,140.9,143.9,2.8,1.9,5.6,3.7,8.4,5.6,17.3-18.4,38.4-39.9,63.2-63.7,28.7-27.5,62.3-58,100.5-89.9,12-10,24.3-20.1,37.2-30.2,38.5-30.6,81-61.8,127.3-92.6,18.1-12,36.8-24,56-35.8,1.1-.7,2.2-1.4,3.3-2.1,69.7-42.5,134-74.4,193.1-97.4,71.5-27.7,135.3-42.3,191.9-46.8v-56.4c-38-1.9-79.5-.2-124.7,5.7Z',
    'M538.7,424.7c-94.3,75.3-178.8,153.6-252.9,229.3,46.1,22,95,36.6,145.8,43.7,3.9.5,7.5,1,11.1,1.4,13.3,1.5,26.8,2.5,40.2,3,52.8-74.3,121.3-159.4,205.8-243.6,115.4-115,219.7-183.6,311.4-219.5.4-5.7.7-11.5,1-17.2v-.8c.2-2.3.2-4.7.3-7.1v-.3c0-3.8.2-7.9.2-12v-25.1c-33.1,7.1-68.1,17.5-105.1,31.7-101.6,39-221.4,107.7-357.8,216.5Z',
    'M615.1,607.7c-23.5,30.9-46,61.8-67.4,92.6,25-2.3,49.7-6.5,73.9-12.5,15.4-3.8,30.7-8.4,45.6-13.6l.5-.2,1.6-.6c28.1-10,55-22.4,80.6-37.1,6.1-10.8,12.5-21.6,19-32.4,71.5-118.7,143.2-202.9,212.4-260.7,1.8-6.1,3.5-12.2,5-18.3,4.5-17.6,8-35.7,10.5-53.7.6-4.8,1.3-9.5,1.8-14.3-28.8,14.5-58.5,32.3-89.1,53.7-89.1,62.5-188.6,158.3-294.4,297Z',
    'M463.6,220.3c1.5-.7,3-1.4,4.5-2.1l2.9-1.4c3-1.4,5.9-2.7,8.8-4.1,59.9-27.6,118.4-51.2,174.2-70.2-58.2-23.5-119.3-46-182.8-66.5C305.3,22.4,168.9.5,60.1,0,39.2,0,19.1.6,0,2v64.3c93.7,5.3,207.1,33.4,342.8,94.3,42.6,19.1,82.9,39.2,120.8,59.7Z',
    'M292.3,308.6c37.3-20.9,75.2-41,112.8-59.8C268.8,168.7,151.1,120.8,52.5,96.6c-18-4.4-35.5-8.1-52.5-11v68.3c83.1,28.2,177.5,79,283.4,159.8,2.9-1.7,5.9-3.4,8.9-5.1Z',
    'M157.3,389.1c1.2-.8,2.4-1.5,3.7-2.3,28.8-18.2,58.2-36,87.2-52.9-70.3-58.6-135.8-102.4-195.6-134-17.9-9.5-35.4-17.9-52.5-25.3v27c0,1.6,0,3.2,0,4.8.2,15.5,1,30.9,2.6,46.2,49.4,33.8,101.1,78.5,154.6,136.5Z',
);


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
if ($content_basic_headline):
    if ($content_basic_is_first): ?>
<!-- First headline on page - use H1 for SEO -->
<h1 class="mb-5"><?php echo esc_html($content_basic_headline); ?></h1>
<?php else: ?>
<!-- Subsequent headlines - use H2 for proper heading hierarchy -->
<h2 class="mb-5"><?php echo esc_html($content_basic_headline); ?></h2>
<?php endif;
endif;
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
    <?php echo display_resources('content_basic_resource_repeater', true, false, $bg['use_light_buttons']); ?>
</div>
<?php
    $resources_html = ob_get_clean();
endif;

?>

<!-- Content Basic Block -->
<div id="<?php echo esc_attr('content-' . $iBlock); ?>" class="container-fluid px-5 px-md-0 card-rounded-top corner-fill <?php echo esc_attr($bg['class']); echo esc_attr($content_basic_hero_class); ?>" <?php echo $bg['style_attr']; ?>>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">


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
                            <div<?php if (!$content_basic_image_is_a_logo): ?> class="content-basic__image-mask" <?php endif; ?>>
                                <?php if ($content_basic_image_is_a_logo): ?>
                                <img src="<?php echo esc_url($content_basic_image_url_square); ?>" class="img-fluid" alt="<?php echo esc_attr($content_basic_image_alt); ?>">
                                <?php else:
                        $content_basic_clip_id = 'content-basic-mask-' . $iBlock;
                        $content_basic_title_id = 'content-basic-mask-title-' . $iBlock;
                    ?>
                                <svg class="content-basic__masked-svg js-content-basic-mask" viewBox="0 0 1001.6 702.1" preserveAspectRatio="xMidYMid meet" xmlns:xlink="http://www.w3.org/1999/xlink" <?php if ($content_basic_image_alt): ?>role="img" aria-labelledby="<?php echo esc_attr($content_basic_title_id); ?>" <?php else: ?>aria-hidden="true" <?php endif; ?>>
                                    <?php if ($content_basic_image_alt): ?>
                                    <title id="<?php echo esc_attr($content_basic_title_id); ?>"><?php echo esc_html($content_basic_image_alt); ?></title>
                                    <?php endif; ?>
                                    <defs>
                                        <clipPath id="<?php echo esc_attr($content_basic_clip_id); ?>" clipPathUnits="userSpaceOnUse">
                                            <?php foreach ($content_basic_mask_paths as $path_index => $path_d): ?>
                                            <path class="content-basic__mask-path content-basic__mask-path--<?php echo esc_attr($path_index + 1); ?>" d="<?php echo esc_attr($path_d); ?>" />
                                            <?php endforeach; ?>
                                        </clipPath>
                                    </defs>
                                    <image class="content-basic__masked-svg-image" href="<?php echo esc_url($content_basic_image_url_square); ?>" xlink:href="<?php echo esc_url($content_basic_image_url_square); ?>" x="0" y="0" width="1001.6" height="702.1" preserveAspectRatio="xMidYMid slice" clip-path="url(#<?php echo esc_attr($content_basic_clip_id); ?>)" />
                                </svg>
                                <?php endif; ?>
                        </div>
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