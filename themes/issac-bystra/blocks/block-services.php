<?php
/*
███████ ███████ ██████  ██    ██ ██  ██████ ███████ ███████
██      ██      ██   ██ ██    ██ ██ ██      ██      ██
███████ █████   ██████  ██    ██ ██ ██      █████   ███████
     ██ ██      ██   ██  ██  ██  ██ ██      ██           ██
███████ ███████ ██   ██   ████   ██  ██████ ███████ ███████


*/

$services_columns = get_sub_field('services_columns');
$services_image_border = get_sub_field('services_image_border');
$segment = get_sub_field('segment');
?>

<div id="servicesBlock-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0">

    <div class="container py-5">

        <!-- Services Loop -->
        <?php
        // Build query args for Services CPT filtered by segment taxonomy
        $args = array(
            'post_type'      => 'services',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );

        // Filter by segment taxonomy if selected
        if( $segment && !empty($segment) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'segment',
                    'field'    => 'term_id',
                    'terms'    => $segment,
                ),
            );
        }

        $services_query = new WP_Query( $args );

        if( $services_query->have_posts() ): $iServices = 0;
        ?>

        <div class="row row-cols-md-2 
        <?php 
        if( $services_columns == '2_col' ) : 
            echo ''; 
        elseif( $services_columns == '3_col' ) :  
            echo 'row-cols-lg-3'; 
        elseif( $services_columns == '4_col' ) :  
            echo 'row-cols-lg-4'; 
        elseif( $services_columns == '5_col' ) :  
            echo 'row-cols-lg-4 row-cols-lg-5'; 
        elseif( $services_columns == '6_col' ) :  
            echo 'row-cols-lg-4 row-cols-xl-6'; 
        endif;
        ?> g-5 d-flex justify-content-center">

            <?php
            while ( $services_query->have_posts() ) : $services_query->the_post();
            
            // Get ACF fields from the service post
            $service_image = get_field('service_image');
            if($service_image):
                // Set thumbnail size based on number of columns
                if( $services_columns == '2_col' ) :
                    $service_image_url = $service_image['sizes']['4-3r576'];
                elseif( $services_columns == '3_col' ) :
                    $service_image_url = $service_image['sizes']['4-3r576'];
                elseif( $services_columns == '4_col' ) :
                    $service_image_url = $service_image['sizes']['4-3r576'];
                elseif( $services_columns == '5_col' ) :
                    $service_image_url = $service_image['sizes']['4-3r576'];
                elseif( $services_columns == '6_col' ) :
                    $service_image_url = $service_image['sizes']['4-3r576'];
                else:
                    $service_image_url = $service_image['sizes']['4-3r576'];
                endif;
            endif;

            $service_title = get_the_title();
            $service_description = get_field('description');
            $service_link = get_permalink();
            ?>

            <div class="col wow animate__animated animate__fadeInUp">

                <!-- service-card-wrapper: enables GSAP hover effect on image -->
                <div class="service-card-wrapper">

                    <!-- service-card-body: card container with hover lift effect -->
                    <div class="border border-light shadow-lg cards-shape-top bg-white service-card-body">

                        <!-- service-image-container: wraps the image for hover animation -->
                        <div class="service-image-container <?php if( $services_image_border ) : echo 'p-5'; else: echo 'p-0'; endif; ?>">
                            <?php if( $service_image ): ?>
                            <img src="<?php echo esc_url($service_image_url); ?>" class="img-cards img-fluid" alt="<?php echo esc_attr($service_title); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="pb-5 px-5 bg-white text-center cards-shape-bottom">

                            <div class="fosforos pb-5 <?php if( ! $services_image_border ) : echo 'pt-5'; endif; ?>">
                                <?php if( $service_title ):  ?>
                                <div class="mb-5">
                                    <h6 class="text-posidonia highlight"><?php echo esc_html($service_title); ?></h6>
                                </div>
                                <?php endif; ?>

                                <?php if( $service_description ):  ?>
                                <div class="text-posidonia mb-3">
                                    <?php echo esc_html($service_description); ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-center">
                                <a href="<?php echo esc_url($service_link); ?>" class="btn btn-sm btn-aguamarina">LEARN&nbsp;MORE <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <?php $iServices++; 
            endwhile; 
            wp_reset_postdata();
            ?>

        </div>

        <?php
        else :
            echo '<!-- No services found -->';
        endif;
        ?>
        <!-- //end Services Loop -->

    </div>
</div>