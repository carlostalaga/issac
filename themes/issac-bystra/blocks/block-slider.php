<?php
$bg = get_block_background();

if (have_rows('slider_repeater')): ?>

<div id="slider-wrap-<?php echo esc_attr($iBlock); ?>" class="block-slider-wrap corner-fill" <?php echo $bg['style_attr']; ?>>
    <div id="slider-<?php echo esc_attr($iBlock); ?>" class="swiper block-slider block-slider--hero img-rounded-top">
        <div class="swiper-wrapper">
            <?php while (have_rows('slider_repeater')): the_row();
            $video = get_sub_field('video');
            $image = get_sub_field('image');

            if ($image):
                $image_url_mobile = $image['sizes']['16-9r720'] ?? ($image['url'] ?? '');
                $image_url_tablet = $image['sizes']['720p'] ?? ($image['url'] ?? '');
                $image_url = $image['sizes']['1080p'] ?? ($image['url'] ?? '');
            endif;

            $headline = get_sub_field('headline');
            $content = get_sub_field('content');
            $link = get_sub_field('link');
        ?>

            <div class="swiper-slide">
                <div class="container-fluid block-slider__bg">





                    <?php if ($video): ?>
                    <video playsinline autoplay muted loop>
                        <source src="<?php echo esc_url($video['url']); ?>" type="video/mp4">
                    </video>
                    <?php elseif ($image): ?>
                    <picture>
                        <source media="(max-width: 576px)" srcset="<?php echo esc_url($image_url_mobile); ?>">
                        <source media="(min-width: 577px) and (max-width: 1199px)" srcset="<?php echo esc_url($image_url_tablet); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                    </picture>
                    <?php else: ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/img/failover.jpg'); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                    <?php endif; ?>

                    <?php if ($headline || $content): ?>
                    <div class="container">
                        <div class="row justify-content-end align-items-center h-100">

                            <div class="col-3 col-md-5 col-xl-5 col-xxl-6">
                                &nbsp;
                            </div>


                            <div class="col-9 col-md-7 col-xl-7 col-xxl-6 pe-5 pe-md-0 block-slider__card-inner-wrapper">
                                <div class="block-slider__card-inner">
                                    <?php if ($headline): ?>
                                    <div class="headline-1"><?php echo wp_kses_post($headline); ?></div>
                                    <?php endif; ?>
                                    <?php if ($content): ?>
                                    <div class="text-slider-content mb-3 d-none d-xl-block">
                                        <?php echo wp_kses_post($content); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($link && is_array($link)): ?>
                                    <div class="pt-3">
                                        <a href="<?php echo esc_url($link['url']); ?>" target="<?php echo esc_attr($link['target']); ?>" class="btn btn-brand-main" title="<?php echo esc_attr($link['title']); ?>" aria-label="Learn more about <?php echo esc_attr($link['title']); ?>">Learn More</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>


                        </div>
                    </div>
                    <?php endif; ?>







                </div>
            </div>

            <?php endwhile; ?>
        </div>

        <img class="block-slider__bottom-shape" src="<?php echo esc_url(get_template_directory_uri() . '/img/shape-slider-bottom-block-slider.svg'); ?>" alt="" aria-hidden="true">

        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

    </div>
</div>

<?php else: ?>
<!-- No slides found -->
<?php endif; ?>