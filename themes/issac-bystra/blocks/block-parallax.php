<?php
/*
██████   █████  ██████   █████  ██      ██       █████  ██   ██
██   ██ ██   ██ ██   ██ ██   ██ ██      ██      ██   ██  ██ ██
██████  ███████ ██████  ███████ ██      ██      ███████   ███
██      ██   ██ ██   ██ ██   ██ ██      ██      ██   ██  ██ ██
██      ██   ██ ██   ██ ██   ██ ███████ ███████ ██   ██ ██   ██

*/

$parallax_image = get_sub_field('parallax_image');
if($parallax_image):
    $parallax_image_alt = $parallax_image['alt'];
    $parallax_image_mobile = $parallax_image['sizes']['16-9r720'];
    $parallax_image_tablet = $parallax_image['sizes']['720p'];
    $parallax_image_desktop = $parallax_image['sizes']['1080p'];
endif;

$parallax_headline = get_sub_field('parallax_headline');
$parallax_tagline = get_sub_field('parallax_tagline');

$parallax_link = get_sub_field('parallax_link');
if($parallax_link):
    $parallax_link_url = $parallax_link['url'];
    $parallax_link_title = $parallax_link['title'];
    $parallax_link_target = $parallax_link['target'] ? $parallax_link['target'] : '_self';
endif;
?>

<div class="parallax-container bg-danger">
    <div class="parallax-image-wrapper">
        <picture>
            <!-- Mobile image for screens <= 576px -->
            <source media="(max-width: 576px)" srcset="<?php echo esc_url($parallax_image_mobile); ?>">
            <!-- Tablet image for screens between 577px and 1199px -->
            <source media="(min-width: 577px) and (max-width: 1199px)" srcset="<?php echo esc_url($parallax_image_tablet); ?>">
            <!-- Desktop image for screens > 1200px -->
            <source media="(min-width: 1200px)" srcset="<?php echo esc_url($parallax_image_desktop); ?>">
            <!-- Failover image if other sources fail to load -->
            <img src="<?php echo esc_url(get_template_directory_uri() . '/img/failover.jpg'); ?>" alt="<?php echo esc_attr($parallax_image_alt); ?>" class="img-fluid parallax-image">
        </picture>
    </div>

    <div class="parallax-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content-box">
                        <?php if ($parallax_headline): ?>
                        <div class="mb-3">
                            <h2 class="text-posidonia"><?php echo $parallax_headline ?></h2>
                        </div>
                        <?php endif; ?>
                        <?php if ($parallax_tagline): ?>
                        <div class="mb-3">
                            <h6 class="text-posidonia"><?php echo $parallax_tagline ?></h6>
                        </div class="mb-3">
                        <?php endif; ?>
                        <?php if($parallax_link): ?>
                        <div>
                            <a href="<?php echo esc_url($parallax_link_url); ?>" target="<?php echo esc_attr($parallax_link_target); ?>" class="btn btn-aguamarina"><?php echo $parallax_link_title; ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>