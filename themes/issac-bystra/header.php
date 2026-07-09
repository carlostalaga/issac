<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo( 'name' ); ?></title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header bg-brand-accent py-5 px-0 px-md-0">

        <!-- Super Menu -->
        <!-- d-none for now, to be removed -->
        <div class="container-fluid bg-brand-main-light p-0 d-none">
            <nav aria-label="Secondary navigation: Shortcuts" id="super-menu" class="d-flex flex-wrap justify-content-end">
                <?php /* Super Menu */
            wp_nav_menu( array(
                'menu' => 'super-menu',
                'theme_location' => 'super-menu',
                'fallback_cb'    => false
            ) );
            ?>
            </nav>
        </div>

        <!-- 
            Bootstrap Navigation Structure:
            - The white pill container holds: logo + hamburger button + DESKTOP menu
            - The MOBILE menu is placed OUTSIDE the pill so it doesn't stretch it
            - Desktop (xl+): Menu displays inline inside the pill, hamburger hidden
            - Mobile (<xl): Hamburger visible, menu collapses outside the pill
            
            Note: We use a separate nav for mobile to avoid Bootstrap's navbar-expand 
            overriding our visibility classes on the collapse element.
        -->

        <!-- Desktop navbar (xl+) +++ menu collapse need sync between navbar-expand-xl and javascript breakpoint for the effect === var breakpoint = 1200; // Bootstrap xl breakpoint (matches navbar-expand-xl) -->
        <nav aria-label="Primary navigation" id="navbar-fx" class="navbar sticky-bottom navbar-expand-xl justify-content-center" data-bs-theme="dark">

            <div class="container d-flex justify-content-center px-0">
                <div class="row  d-flex justify-content-center w-100">
                    <div class="col-12">


                        <!-- White pill container -->
                        <div class="container py-3">

                            <div class="row w-100 m-0 align-items-center">

                                <!-- Logo column -->
                                <div class="col-8 col-md-4 col-xl-3">
                                    <a id="navbar-brand" href="<?php echo get_option('siteurl'); ?>" aria-label="Go to homepage">
                                        <img id="logo-change" src="<?php echo get_template_directory_uri() ?>/img/logo-light-bystra.png" class="logo-constraint img-fluid my-3" alt="<?php echo get_bloginfo('name'); ?> logo">
                                    </a>
                                </div>

                                <!-- Hamburger button: Only visible on mobile (<xl) -->
                                <div class="col d-flex d-xl-none justify-content-end">
                                    <button class="navbar-toggler first-button" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu-mobile" aria-controls="main-menu-mobile" aria-expanded="false" aria-label="Toggle navigation">
                                        <div class="animated-icon1"><span></span><span></span><span></span></div>
                                    </button>
                                </div>

                                <!-- Desktop menu: Always visible on xl+, hidden on mobile -->
                                <!-- This stays INSIDE the pill container -->
                                <div class="col-4 col-md-8 col-xl-9 d-none d-xl-flex justify-content-end">
                                    <?php
                        wp_nav_menu(array(
                            'theme_location' => 'main-menu',
                            'container' => false,
                            'menu_class' => '',
                            'fallback_cb' => '__return_false',
                            'items_wrap' => '<ul id="menu-main-menu-desktop" class="navbar-nav ms-auto mb-0 %2$s">%3$s</ul>',
                            'depth' => 2,
                            'walker' => new bootstrap_533_wp_nav_menu_walker()
                        ));
                        ?>
                                </div>

                            </div>

                        </div>
                        <!-- End white pill container -->



                    </div>
                </div>
            </div>

        </nav>

        <!-- Mobile menu: OUTSIDE the pill, OUTSIDE the navbar-expand system -->
        <!-- Uses Bootstrap collapse but not navbar-collapse to avoid conflicts -->
        <div class="collapse container mt-3 d-xl-none" id="main-menu-mobile">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'main-menu',
                'container' => false,
                'menu_class' => '',
                'fallback_cb' => '__return_false',
                'items_wrap' => '<ul id="menu-main-menu-mobile" class="navbar-nav text-end %2$s">%3$s</ul>',
                'depth' => 2,
                'walker' => new bootstrap_533_wp_nav_menu_walker()
            ));
            ?>
        </div>
        <!-- End mobile menu -->
    </header>