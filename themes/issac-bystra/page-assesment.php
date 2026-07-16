<?php
/*
Template Name: My Assessment
*/
?>
<?php get_header(); ?>
<main id="main-content" role="main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>


        <div class="container-fluid py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div>
                            <h1>My Responses</h1>
                        </div>

                        <div>
                            <?php echo do_shortcode('[issac_dashboard]'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <?php 
        /* Flexible Content */
        /* include get_theme_file_path('/blocks/flexible-content.php'); */ 
        ?>




    </article>


    <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>