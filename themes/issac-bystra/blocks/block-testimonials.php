<?php
/*
████████ ███████ ███████ ████████ ██ ███    ███  ██████  ███    ██ ██  █████  ██      ███████
   ██    ██      ██         ██    ██ ████  ████ ██    ██ ████   ██ ██ ██   ██ ██      ██
   ██    █████   ███████    ██    ██ ██ ████ ██ ██    ██ ██ ██  ██ ██ ███████ ██      ███████
   ██    ██           ██    ██    ██ ██  ██  ██ ██    ██ ██  ██ ██ ██ ██   ██ ██           ██
   ██    ███████ ███████    ██    ██ ██      ██  ██████  ██   ████ ██ ██   ██ ███████ ███████
*/

$bg = get_block_background();
$testimonials_headline = get_sub_field('testimonials_headline');
$testimonials_segment = get_sub_field('testimonials_segment');

// Prepare WP_Query arguments for retrieving 'testimonials' posts
$testimonials_args = [
    'post_type'      => 'testimonials', // Custom post type
    'posts_per_page' => 256,             // Limit the number of posts
    'orderby'        => 'rand',         // Random order for posts
];

// Add taxonomy filter if a segment is selected
if ($testimonials_segment) {
    $testimonials_args['tax_query'] = [
        [
            'taxonomy' => 'segment',        // Custom taxonomy name
            'field'    => 'term_id',        // Filter by term ID
            'terms'    => $testimonials_segment, // The selected segment ID
        ],
    ];
}

// Execute WP_Query with the arguments
$testimonials_query = new WP_Query($testimonials_args);
$testimonials_block_id = 'testimonialsBlock-' . $iBlock;
$testimonials_swiper_id = 'testimonialsSwiper-' . $iBlock;
$testimonial_word_limit = 39;
$quote_shape_url = get_template_directory_uri() . '/img/shape-quotes.svg';

// Begin HTML output
?>
<div id="<?php echo esc_attr($testimonials_block_id); ?>" class="container-fluid py-5 card-rounded-top corner-fill <?php echo esc_attr($bg['class']); ?>" <?php echo $bg['style_attr']; ?>>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">


                <div class="container" style="padding-bottom: 90px;">
                    <section class="testimonials-slider-panel" aria-labelledby="<?php echo esc_attr($testimonials_block_id); ?>-heading">


                        <div class="row justify-content-center">
                            <div class="col-12 my-5 py-5">
                                <h3><?php echo esc_html($testimonials_headline); ?></h3>
                            </div>
                        </div>

                        <?php if ($testimonials_query->have_posts()) : ?>
                        <div class="testimonials-slider-wrap">
                            <div id="<?php echo esc_attr($testimonials_swiper_id); ?>" class="swiper swiper-testimonials">
                                <div class="swiper-wrapper">
                                    <?php
                        $testimonial_index = 0;
                        while ($testimonials_query->have_posts()) : $testimonials_query->the_post();
                            $testimonial = get_field('testimonial');
                            $author = get_field('author');
                            $about_author = get_field('about_author');
                            $testimonial_text = trim(wp_strip_all_tags($testimonial));
                            $testimonial_excerpt = wp_trim_words($testimonial_text, $testimonial_word_limit, '...');
                            $is_truncated = (str_word_count($testimonial_text) > $testimonial_word_limit);
                            $modal_id = 'testimonialModal-' . $iBlock . '-' . $testimonial_index;
                        ?>
                                    <article class="swiper-slide testimonial-slide">
                                        <div class="testimonial-card  bg-hueso testimonial-slider-card">
                                            <div class="text-center mt-4 mb-5 pb-3">
                                                <img src="<?php echo esc_url($quote_shape_url); ?>" alt="" aria-hidden="true" style="max-width: 114px;">
                                            </div>

                                            <div class="fosforos mb-4">
                                                <?php if ($testimonial_excerpt) : ?>
                                                <div class="text-negro mb-5">
                                                    <?php echo esc_html($testimonial_excerpt); ?><?php if ($is_truncated) : ?><a href="#" class="testimonial-read-more swiper-no-swiping" data-bs-toggle="modal" data-bs-target="#<?php echo esc_attr($modal_id); ?>"> READ MORE</a><?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="cerillas">
                                                <?php if ($author) : ?>
                                                <span class="text-negro"> <strong><?php echo esc_html($author); ?></strong></span>
                                                <?php endif; ?>

                                                <?php if ($about_author) : ?>
                                                <span class="text-negro"> — <strong><?php echo esc_html($about_author); ?></strong></span>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                    </article>
                                    <?php $testimonial_index++; endwhile; ?>
                                </div>
                            </div>

                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>

                        <?php else: ?>
                        <p class="testimonials-slider-panel__empty"><?php _e('Sorry, no testimonials matched your criteria.', 'textdomain'); ?></p>
                        <?php endif; ?>

                        <?php wp_reset_postdata(); ?>
                    </section>
                </div>


            </div>
        </div>
    </div>



</div>

<?php if ($testimonials_query->post_count > 0) :
    $testimonials_query->rewind_posts();
    $testimonial_index = 0;
    while ($testimonials_query->have_posts()) : $testimonials_query->the_post();
        $testimonial = get_field('testimonial');
        $author = get_field('author');
        $about_author = get_field('about_author');
        $testimonial_text = trim(wp_strip_all_tags($testimonial));
        $is_truncated = (str_word_count($testimonial_text) > $testimonial_word_limit);
        $modal_id = 'testimonialModal-' . $iBlock . '-' . $testimonial_index;

        if ($is_truncated) : ?>
<div class="modal fade testimonial-modal" id="<?php echo esc_attr($modal_id); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-4 p-md-5">
                <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="mt-3">
                    <?php echo esc_html($testimonial_text); ?>
                </div>
                <?php if ($author) : ?>
                <p class="mt-4 mb-0"><strong><?php echo esc_html($author); ?></strong></p>
                <?php endif; ?>
                <?php if ($about_author) : ?>
                <span class="text-muted"><?php echo esc_html($about_author); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif;
        $testimonial_index++;
    endwhile;
    wp_reset_postdata();
endif; ?>