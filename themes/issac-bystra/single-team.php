<?php get_header(); ?>
<main id="main-content" role="main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>




        <?php

        $team_title = get_the_title();        
        // ACF fields (group_697aeb400e827 — Team)
        $team_image = get_field('team_image'); // array
        if ($team_image):
            $team_image_url = $team_image['sizes']['7-5r960'];
            $team_image_alt = $team_image['alt'];
        endif;
        $team_nickname       = get_field('team_nickname');       // text
        $role                = get_field('role');                // text
        $email               = get_field('email');               // email
        $team_linkedin       = get_field('team_linkedin');       // array (link)
        $team_phone          = get_field('team_phone');          // text
        $credit_rep_number   = get_field('credit_rep_number');   // text
        $team_intro_headline = get_field('team_intro_headline'); // text
        $team_intro_content  = get_field('team_intro_content');  // textarea
        $team_content        = get_field('team_content');        // wysiwyg

        ?>




        <div class="container-fluid bg-hero p-5 corner-fill" style="--corner-fill-colour: #A48C50;">

            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-11 col-lg-10">


                        <?php
                        /* -------------------------------------------------------------------------- */
                        /*                                 Top section                                */
                        /* -------------------------------------------------------------------------- */
                        ?>
                        <div class="container hero-overlap<?php echo !$team_image ? ' hero-overlap--solo' : ''; ?>">

                            <?php if ($team_image): ?>
                            <div class="hero-overlap__media">
                                <img src="<?php echo esc_url($team_image_url); ?>" alt="<?php echo esc_attr($team_image_alt); ?>" class="hero-overlap__image img-fluid img-rounded">
                            </div>
                            <?php endif; ?>

                            <div class="hero-overlap__card-wrap">
                                <div class="hero-overlap__card bg-hueso card-rounded">
                                    <div class="row w-100 g-4">

                                        <div class="col">
                                            <div class="pe-5">
                                                <?php if ($team_nickname): ?>
                                                <div class="hero-overlap__headline headline-1 text-brand-accent mb-4">
                                                    <?php echo $team_nickname; ?>
                                                </div>
                                                <?php endif; ?>

                                                <?php if ($team_title): ?>
                                                <div>
                                                    <h5 class="text-prose-400"><?php echo $team_title; ?></h5>
                                                </div>
                                                <?php endif; ?>

                                                <?php if ($role): ?>
                                                <div>
                                                    <h5 class="text-brand-accent text-prose-400"><?php echo $role; ?></h5>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="ps-5" style="border-left: 3px solid var(--colour-brand-accent);">

                                                <?php if ($team_linkedin): ?>
                                                <div class="mb-4">
                                                    <a href="<?php echo esc_url($team_linkedin['url']); ?>" target="_blank" aria-label="LinkedIn">
                                                        <i class="fab fa-linkedin text-brand-main" style="font-size: 3.6rem;" aria-hidden="true"></i>
                                                    </a>
                                                </div>
                                                <?php endif; ?>

                                                <?php if ($team_phone): ?>
                                                <div class="mb-1">
                                                    P: <a href="tel:<?php echo esc_attr($team_phone); ?>" class=""> <?php echo $team_phone; ?></a>
                                                </div>
                                                <?php endif; ?>

                                                <?php if ($credit_rep_number): ?>
                                                <div class="mb-4">
                                                    <span class="text-brand-main">Credit Rep Number: <?php echo $credit_rep_number; ?></span>
                                                </div>
                                                <?php endif; ?>

                                                <?php if ($email): ?>
                                                <div class="mb-3">
                                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-brand-main">Email <?php echo $team_nickname; ?></a>
                                                </div>
                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </div>

        </div>






        </div>





        <div class="container-fluid bg-hueso p-5 card-rounded-top">

            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-11 col-lg-10">


                        <div class="container" style="padding-top: 120px; padding-bottom: 270px;">

                            <?php
                /* -------------------------------------------------------------------------- */
                /*                               Content section                              */
                /* -------------------------------------------------------------------------- */
                ?>
                            <div class="row g-5 hero-theme-content">
                                <div class="col-md-6 col-lg-5">
                                    <div>
                                        <h2><?php echo $team_intro_headline; ?></h2>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-lg-1">
                                    <div class="ps-5" style="border-left: 3px solid var(--colour-text);">
                                        <?php echo $team_intro_content; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="pt-5">
                                        <?php echo $team_content; ?>
                                    </div>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </div>

        </div>





    </article>


    <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>