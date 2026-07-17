<footer class="site-footer container-fluid bg-brand-main p-0">

    <div class="container-fluid pt-5 px-0">







        <?php 
        /* -------------------------------------------------------------------------- */
        /*                                 Main block                                 */
        /* -------------------------------------------------------------------------- */
        ?>
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-12">



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
                                        <img src="<?php echo get_template_directory_uri() ?>/img/logo-light-bystra.png" class="logo-constraint img-fluid" alt="<?php echo get_bloginfo('name'); ?> logo">
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







                            </div>


                            <div class="col-md-12 col-lg-8 col-xl-9 col-xxl-10">

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
        <div class="container-fluid py-5 px-0">
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
                                    &copy; <?php echo date('Y'); ?> | <?php echo get_bloginfo('name'); ?>. | <a href="#" class="text-white">Privacy Policy</a>
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