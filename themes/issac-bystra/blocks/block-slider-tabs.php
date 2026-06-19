<?php
/*
███████ ██      ██ ██████  ███████ ██████      ████████  █████  ██████  ███████
██      ██      ██ ██   ██ ██      ██   ██        ██    ██   ██ ██   ██ ██
███████ ██      ██ ██   ██ █████   ██████         ██    ███████ ██████  ███████
     ██ ██      ██ ██   ██ ██      ██   ██        ██    ██   ██ ██   ██      ██
███████ ███████ ██ ██████  ███████ ██   ██        ██    ██   ██ ██████  ███████

Swiper Thumbs – pill nav controls a content slider.
Accessible, GSAP-ready, mobile-swipeable.
*/

$slider_tabs_id = 'slider-tabs' . $iBlock;
?>

<?php if (have_rows('slider_tabs_repeater')): $tabIndex = 0; ?>
<section id="<?php echo esc_attr($slider_tabs_id); ?>" class="slider-tabs" aria-label="Tabbed content">

    <!-- Nav Swiper: pill navigation -->
    <div class="slider-tabs__nav-wrapper">
        <div class="swiper swiper-slider-tabs-nav swiper-slider-tabs-nav--<?php echo $iBlock; ?>">
            <div class="swiper-wrapper" role="tablist">
                <?php while (have_rows('slider_tabs_repeater')): the_row();
                    $slider_tab_label = get_sub_field('slider_tab_label');
                    $is_active = ($tabIndex === 0);
                ?>
                <div class="swiper-slide slider-tabs__pill-slide">
                    <button class="slider-tabs__pill<?php echo $is_active ? ' slider-tabs__pill--active' : ''; ?>" type="button" role="tab" id="<?php echo esc_attr($slider_tabs_id); ?>-tab-<?php echo $tabIndex; ?>" aria-controls="<?php echo esc_attr($slider_tabs_id); ?>-panel-<?php echo $tabIndex; ?>" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>" tabindex="<?php echo $is_active ? '0' : '-1'; ?>">
                        <?php echo esc_html($slider_tab_label); ?>
                    </button>
                </div>
                <?php $tabIndex++; endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Content Swiper: tab panels -->
    <div class="slider-tabs__content-wrapper">
        <div class="swiper swiper-slider-tabs-content swiper-slider-tabs-content--<?php echo $iBlock; ?>">
            <div class="swiper-wrapper">
                <?php
                $tabIndex = 0;
                while (have_rows('slider_tabs_repeater')): the_row();
                    $slider_tab_content = get_sub_field('slider_tab_content');
                    $is_active = ($tabIndex === 0);
                ?>
                <div class="swiper-slide slider-tabs__panel" role="tabpanel" id="<?php echo esc_attr($slider_tabs_id); ?>-panel-<?php echo $tabIndex; ?>" aria-labelledby="<?php echo esc_attr($slider_tabs_id); ?>-tab-<?php echo $tabIndex; ?>" <?php echo !$is_active ? 'hidden' : ''; ?>>
                    <?php if ($slider_tab_content): ?>
                    <div class="slider-tabs__panel-inner" data-anim>
                        <?php echo $slider_tab_content; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php $tabIndex++; endwhile; ?>
            </div>
        </div>
    </div>

</section>
<?php endif; ?>