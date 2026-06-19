<?php

/*
██████   █████  ███    ██ ███    ██ ███████ ██████
██   ██ ██   ██ ████   ██ ████   ██ ██      ██   ██
██████  ███████ ██ ██  ██ ██ ██  ██ █████   ██████
██   ██ ██   ██ ██  ██ ██ ██  ██ ██ ██      ██   ██
██████  ██   ██ ██   ████ ██   ████ ███████ ██   ██


*/
$bg = get_block_background();
$banner_section_backgrounds = ['bg-3', 'bg-2', 'bg-2a', 'bg-hueso', 'bg-white', 'bg-hero', 'bg-1', 'bg-custom'];
$banner_is_section_bg = in_array($bg['value'], $banner_section_backgrounds, true);
$banner_section_class = ($banner_is_section_bg && !empty($bg['class'])) ? $bg['class'] : 'bg-3';
$banner_card_class = $banner_is_section_bg ? 'bg-brand-main' : (!empty($bg['class']) ? $bg['class'] : 'bg-brand-main');
$banner_card_is_dark = $banner_is_section_bg ? true : $bg['is_dark'];
$banner_section_style_attr = !empty($bg['style_attr']) ? $bg['style_attr'] : ' style="--corner-fill-colour:#A48C50;"';
$banner_headline = get_sub_field('banner_headline');
$banner_content = get_sub_field('banner_content');
$banner_link = get_sub_field('banner_link');
if($banner_link):
    $banner_link_url = $banner_link['url'];
    $banner_link_title = $banner_link['title'];
    $banner_link_target = $banner_link['target'];
endif;
?>


<div id="banner-<?php echo $iBlock; ?>" class="container-fluid banner-block-wrap card-rounded-top corner-fill <?php echo esc_attr($banner_section_class); ?>" <?php echo $banner_section_style_attr; ?>>

    <span class="site-footer__texture site-footer__texture--banner" aria-hidden="true"></span>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">






                <div class="container my-5 py-5 d-flex justify-content-center">

                    <?php
        /* banner body */
        ?>
                    <div class="col-xxl-10" style="padding-bottom: 100px;">


                        <div class="row banner-block <?php echo esc_attr($banner_card_class); ?> my-5 block-banner__card-inner">

                            <div class="col-12 mb-5">
                                <?php if($banner_headline): ?>
                                <h4><?php echo $banner_headline; ?></h4>
                                <?php endif; ?>

                                <?php if($banner_content): ?>
                                <div class="mb-5 p-3">
                                    <?php echo $banner_content; ?>
                                </div>
                                <?php endif; ?>

                                <?php if($banner_link): ?>
                                <div class="mb-5 p-3">
                                    <a href="<?php echo esc_url($banner_link_url); ?>" class="btn <?php echo $banner_card_is_dark ? 'btn-hueso' : 'btn-brand-main'; ?>" target="<?php echo esc_attr($banner_link_target); ?>"><?php echo esc_html($banner_link_title); ?></a>
                                </div>
                                <?php endif; ?>

                            </div>


                            <div class="col-12 newspaper">

                                <?php
                if( have_rows('banner_repeater') ):
                    while( have_rows('banner_repeater') ) : the_row();

                        $banner_repeater_image = get_sub_field('banner_repeater_image');
                        if($banner_repeater_image):
                            $banner_repeater_image_url = $banner_repeater_image['sizes']['576sm'];
                        endif;
                        $banner_repeater_title = get_sub_field('banner_repeater_title');
                ?>


                                <div class="row g-5 p-3">
                                    <?php if( $banner_repeater_image ): ?>
                                    <div class="col-3">
                                        <div class="banner-repeater-icon">
                                            <img src="<?php echo esc_url($banner_repeater_image_url); ?>" class="img-fluid" alt="">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-9 d-flex align-items-center">
                                        <h6><?php echo $banner_repeater_title; ?></h6>
                                    </div>
                                </div>

                                <?php      
                    endwhile;
                endif;
                ?>

                            </div>



                        </div>

                    </div>

                </div>



            </div>
        </div>
    </div>

</div>