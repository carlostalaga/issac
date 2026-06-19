<?php
/**
 * blog-loop.php
 * 
 * Common news/events loop. 
 * This file is included from other templates (like front-page.php).
 * 
 * NOTE: On a sub-site where we do switch_to_blog(1), get_current_blog_id()
 *       will always return 1 here. Instead, we rely on $is_subsite_for_includes
 *       to determine if we are "physically" on a sub-site vs. main site.
 */

/*******************************************************
 * 1) Decide how many posts & styling based on sub-site or main site
 *******************************************************/
if ( isset($is_subsite_for_includes) && $is_subsite_for_includes ) {
    // Sub-site logic
    $posts_per_page    = 4;
    $container_bg      = 'bg-hueso';
    $header_text_class = 'text-white';
    $show_pagination   = false; // No pagination for sub-sites
} else {
    // Main site logic
    $posts_per_page    = 8;
    $container_bg      = 'bg-hueso';
    $header_text_class = 'text-posidonia';
    $show_pagination   = true;  // Show pagination for main site
}

/*******************************************************
 * 2) Prepare and run the WP_Query 
 *******************************************************/
// Handle pagination only if we are on the main site
$paged = $show_pagination ? ( get_query_var('paged') ? get_query_var('paged') : 1 ) : 1;

$args = array(
    'post_type'      => 'post',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
);

$the_query = new WP_Query($args);
?>

<!-- Container with dynamic background class -->
<div class="container-fluid py-5 px-5 px-md-0 <?php echo esc_attr($container_bg); ?>">
    <div class="container my-5">

        <!-- Cards -->
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-xl-9">


                <div class="row row-cols-1 row-cols-md-2 g-5 d-flex justify-content-center">
                    <?php
                if ( $the_query->have_posts() ) :
                    while ( $the_query->have_posts() ) :
                        $the_query->the_post();

                        // Attempt to get the '576sm' sized thumbnail
                        $postImg = wp_get_attachment_image_src(
                            get_post_thumbnail_id($post->ID),
                            '8-5r576'
                        );

                        // Fallback to 'full' or placeholder
                        if ( !$postImg ) {
                            $postImg = wp_get_attachment_image_src(
                                get_post_thumbnail_id($post->ID),
                                'full'
                            ) ?: array(get_template_directory_uri() . '/img/fallback-8-5r576.png','');
                        }

                        // Get the alt text for accessibility
                        $postImg_alt = get_post_meta(
                            get_post_thumbnail_id($post->ID),
                            '_wp_attachment_image_alt',
                            true
                        );
                        ?>

                    <!-- Card Column -->
                    <div class="col card-fx-container wow animate__animated animate__fadeIn">
                        <div class="card-fxs mb-5 mb-md-2 shadow-sm">
                            <!-- Image Section -->
                            <div>
                                <img src="<?php echo esc_url($postImg[0]); ?>" alt="<?php echo esc_attr($postImg_alt); ?>" class="img-fluid">
                            </div>


                            <!-- Text Content -->
                            <div class="p-5 bg-white fosforos">
                                <div class="text-start">
                                    <div>
                                        <h4><?php the_title(); ?></h4>
                                    </div>
                                    <div class="text-posidonia mb-4">
                                        <?php 
                                            // If you have a custom function get_excerpt($length)
                                            // otherwise, default to the_excerpt()
                                            if ( function_exists('get_excerpt') ) {
                                                echo get_excerpt(90);
                                            } else {
                                                the_excerpt();
                                            }
                                            ?>
                                    </div>
                                    <div class="text-center">
                                        <a href="<?php the_permalink(); ?>">
                                            <button class="btn btn-posidonia">LEARN MORE</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .card-fx -->
                    </div><!-- .col -->
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No posts found.</p>';
                endif;
                ?>
                </div>

            </div>
        </div>
        <!-- Cards -->

        <!-- Pagination -->
        <?php if ( $show_pagination && $the_query->max_num_pages > 1 ) : ?>
        <div class="row border-top mt-5">
            <div class="container mt-5 py-5">
                <div class="pagination d-flex justify-content-center">
                    <?php
                        echo paginate_links(array(
                            'total'     => $the_query->max_num_pages,
                            'current'   => $paged,
                            'format'    => '?paged=%#%',
                            'show_all'  => false,
                            'type'      => 'plain',
                            'end_size'  => 2,
                            'mid_size'  => 2,
                            'prev_next' => true,
                            'prev_text' => __('<i class="fas fa-chevron-left text-posidonia"></i>'),
                            'next_text' => __('<i class="fas fa-chevron-right text-posidonia"></i>'),
                        ));
                        ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- End of Pagination -->

    </div><!-- .container -->
</div><!-- .container-fluid -->