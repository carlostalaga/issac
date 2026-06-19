<!-- new design footer -->

<footer class="site-footer container-fluid bg-brand-main p-0" style="position: relative; margin-top: -84px;">

    <span class="site-footer__texture" aria-hidden="true"></span>

    <img class="site-footer__top-shape" src="<?php echo esc_url(get_template_directory_uri() . '/img/shape-slider-bottom.svg'); ?>" alt="" aria-hidden="true">


    <div class="container-fluid pt-5 px-0">







        <?php 
        /* -------------------------------------------------------------------------- */
        /*                                 Main block                                 */
        /* -------------------------------------------------------------------------- */
        ?>
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-11 col-lg-10">



                    <div class="container px-5 px-md-0">
                        <div class="row g-5 mb-5">

                            <div class="col-md-12 col-lg-4 col-xl-3 col-xxl-2 text-center text-lg-start">
                                <?php
                                /* -------------------------------------------------------------------------- */
                                /*                                    logo                                    */
                                /* -------------------------------------------------------------------------- */
                                ?>
                                <div class="mb-5">
                                    <a id="navbar-brand" href="<?php echo get_option('siteurl'); ?>" aria-label="Go to homepage">
                                        <img src="<?php echo get_template_directory_uri() ?>/img/logo-farmscape-finance-vertical.png" class="img-fluid" alt="<?php echo get_bloginfo('name'); ?> logo">
                                    </a>
                                </div>
                                <?php
                                /* -------------------------------------------------------------------------- */
                                /*                                Social Icons                                */
                                /* -------------------------------------------------------------------------- */
                                ?>
                                <div id="social-icons-lightmode" class="mb-5 text-center">
                                    <?php if( get_field('mail', 'option') ): ?>
                                    <a href="<?php echo esc_url( 'mailto:' . antispambot( get_field('mail', 'option' ) ) . '?subject=' . rawurlencode( 'Message from ' . get_bloginfo('name') ) ); ?>">
                                        <i class="fa fa-envelope ms-2 fs-1" aria-hidden="true"></i>
                                    </a>&nbsp;
                                    <?php endif; ?>

                                    <?php $facebook = get_field('facebook', 'option'); ?>
                                    <?php if( $facebook ): ?>
                                    <a target="_blank" href="<?php echo esc_url( $facebook['url'] ); ?>"><i class="fab fa-facebook-f ms-2 fs-1" aria-hidden="true"></i></a>&nbsp;
                                    <?php endif; ?>

                                    <?php $twitter = get_field('twitter', 'option'); ?>
                                    <?php if( $twitter ): ?>
                                    <a target="_blank" href="<?php echo esc_url( $twitter['url'] ); ?>"><i class="fab fa-twitter ms-2 fs-1" aria-hidden="true"></i></a>&nbsp;
                                    <?php endif; ?>

                                    <?php $tiktok = get_field('tiktok', 'option'); ?>
                                    <?php if( $tiktok ): ?>
                                    <a target="_blank" href="<?php echo esc_url( $tiktok['url'] ); ?>"><i class="fab fa-tiktok ms-2 fs-1" aria-hidden="true"></i></a>&nbsp;
                                    <?php endif; ?>

                                    <?php $instagram = get_field('instagram', 'option'); ?>
                                    <?php if( $instagram ): ?>
                                    <a target="_blank" href="<?php echo esc_url( $instagram['url'] ); ?>"><i class="fab fa-instagram ms-2 fs-1" aria-hidden="true"></i></a>&nbsp;
                                    <?php endif; ?>

                                    <?php $linkedin = get_field('linkedin', 'option'); ?>
                                    <?php if( $linkedin ): ?>
                                    <a target="_blank" href="<?php echo esc_url( $linkedin['url'] ); ?>"><i class="fab fa-linkedin-in ms-2 fs-1" aria-hidden="true"></i></a>&nbsp;
                                    <?php endif; ?>
                                </div>


                                <?php
                                /* -------------------------------------------------------------------------- */
                                /*                                    Phone                                   */
                                /* -------------------------------------------------------------------------- */
                                ?>
                                <div class="mb-5 text-center">
                                    <span class="text-hueso">P: 08 8562 2707</span>
                                </div>



                                <?php
                                /* -------------------------------------------------------------------------- */
                                /*                                    Logo                                    */
                                /* -------------------------------------------------------------------------- */
                                ?>
                                <div class="mb-5 text-center">
                                    <img src="<?php echo get_template_directory_uri() ?>/img/logo-mfaa.png" class="img-fluid" alt="MFAA Member" style="max-width: 150px;">
                                </div>


                            </div>


                            <div class="col-md-12 col-lg-8 col-xl-9 col-xxl-10">
                                <div class="row g-5">


                                    <div class="col-12 text-lg-end" style="margin-top: 90px; margin-bottom: 0px;">
                                        &nbsp;
                                    </div>



                                    <?php
                                    /* -------------------------------------------------------------------------- */
                                    /*                                 Quick Links                                */
                                    /* -------------------------------------------------------------------------- */
                                    ?>
                                    <div class="col-6 col-lg-6 col-xl-3 col-xxl-2 offset-xxl-1">

                                        <div class="titles-footer mb-4">
                                            Quick Links
                                        </div>
                                        <nav aria-label="Footer primary navigation" id="footer-menu" class="mb-5">
                                            <?php /* Footer Menu */
                                    wp_nav_menu( array(
                                        'menu' => 'footer-menu',
                                        'theme_location' => 'footer-menu',
                                        'fallback_cb'    => false
                                    ) );
                                ?>
                                        </nav>

                                    </div>


                                    <?php
                                    /* -------------------------------------------------------------------------- */
                                    /*                              Office Locations                              */
                                    /* -------------------------------------------------------------------------- */
                                    ?>
                                    <div class="col-6 col-lg-6 col-xl-3 col-xxl-3">

                                        <div class="titles-footer mb-4">
                                            Office Locations
                                        </div>
                                        <div class="text-white">
                                            <?php $footer_locations = get_field('locations', 'option'); ?>
                                            <?php if ($footer_locations) : ?>
                                            <?php foreach ($footer_locations as $footer_location) : ?>
                                            <?php
                                            $footer_location_name = $footer_location['location'] ?? '';
                                            $footer_location_address = $footer_location['address'] ?? '';
                                            $footer_location_phone = $footer_location['phone'] ?? '';
                                            $footer_location_phone_link = $footer_location_phone ? preg_replace('/[^0-9+]/', '', $footer_location_phone) : '';
                                            ?>
                                            <div class="mb-4">
                                                <?php if ($footer_location_name) : ?>
                                                <div class="text-drama-700"><?php echo esc_html($footer_location_name); ?></div>
                                                <?php endif; ?>
                                                <?php if ($footer_location_address) : ?>
                                                <div class="text-prose-400"><?php echo wp_kses_post($footer_location_address); ?></div>
                                                <?php endif; ?>
                                                <?php if ($footer_location_phone) : ?>
                                                <div class="text-prose-400">P: <a href="tel:<?php echo esc_attr($footer_location_phone_link); ?>" class="text-white"><?php echo esc_html($footer_location_phone); ?></a></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>


                                    <?php
                                    /* -------------------------------------------------------------------------- */
                                    /*                                Get in Touch                                */
                                    /* -------------------------------------------------------------------------- */
                                    ?>
                                    <div class="col-12  col-xl-6 col-xxl-5 offset-xxl-1">

                                        <div class="titles-footer mb-4">
                                            Get in Touch
                                        </div>
                                        <div>
                                            <?php echo do_shortcode('[contact-form-7 id="21764f5" title="Contact form 1"]'); ?>
                                        </div>
                                    </div>



                                </div>
                            </div>


                        </div>
                    </div>



                </div>
            </div>
        </div>


        <?php 
        /* -------------------------------------------------------------------------- */
        /*                               Secondary block                              */
        /* -------------------------------------------------------------------------- */
        ?>
        <div class="container-fluid bg-brand-main-light py-5 px-0">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-11 col-lg-10">


                        <div class="container">
                            <div class="row text-small">
                                <?php if( get_field('terms_conditions', 'option') ): ?>
                                <div class="col-12 text-white mb-5">
                                    <?php 
                                        $terms_conditions = get_field('terms_conditions', 'option');
                                        if( $terms_conditions ):
                                            echo $terms_conditions;
                                        endif;
                                    ?>
                                </div>
                                <?php endif; ?>
                                <div class="col-12 text-center text-white mb-md-0" style="z-index: 2; font-size: 1.2rem;">
                                    &copy; <?php echo date('Y'); ?> | Barossa Lending Services Pty Ltd T/A <?php echo get_bloginfo('name'); ?>. ABN 69 617 250 861 | Australian Credit Licence 391835 | <a href="#" class="text-white">Privacy Policy</a> | <a href="#" class="text-white">Complaints</a>
                                </div>
                                <div class="col-12 text-center" style="font-size: 1.2rem;">
                                    <a href="https://envyus.com.au" target="_blank" rel="noopener noreferrer" class="text-white">Site by EnvyUs Creative</a>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>






    </div>




</footer>
<?php wp_footer(); ?>
</body>

</html>