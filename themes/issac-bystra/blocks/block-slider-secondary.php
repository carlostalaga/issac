<?php
if (have_rows('slider_repeater')): ?>

<div id="slider-wrap-<?php echo esc_attr($iBlock); ?>" class="block-slider-wrap">
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
                        <div class="row w-100 h-100 d-flex justify-content-end align-items-center">

                            <div class="col-10 col-md-9 col-lg-8 col-xl-7 col-xxl-6 ps-5 block-slider-secondary__card-inner-wrapper">
                                <div class="block-slider__card-inner">
                                    <?php if ($headline): ?>
                                    <div class="headline-2"><?php echo wp_kses_post($headline); ?></div>
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

                            <div class="col-2 col-md-3 col-lg-4 col-xl-5 col-xxl-6">
                                &nbsp;
                            </div>

                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php endwhile; ?>
        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

    </div>
</div>

<?php else: ?>
<!-- No slides found -->
<?php endif; ?>