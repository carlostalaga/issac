<?php
/*
████████ ███████  █████  ███    ███
   ██    ██      ██   ██ ████  ████
   ██    █████   ███████ ██ ████ ██
   ██    ██      ██   ██ ██  ██  ██
   ██    ███████ ██   ██ ██      ██


*/
$team_columns = get_sub_field('team_columns');
$team_image_border = get_sub_field('team_image_border');
?>

<div id="teamBlock-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0">




    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">




                <div class="container py-5" style="margin-bottom: 120px;">

                    <!-- Team Loop -->
                    <?php
        // Build query args for Team CPT
        $args = array(
            'post_type'      => 'team',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );

        $team_query = new WP_Query( $args );

        if( $team_query->have_posts() ): $iTeam = 0;
        ?>

                    <div class="row row-cols-md-2 
        <?php 
        if( $team_columns == '2_col' ) : 
            echo ''; 
        elseif( $team_columns == '3_col' ) :  
            echo 'row-cols-lg-3'; 
        elseif( $team_columns == '4_col' ) :  
            echo 'row-cols-lg-4'; 
        elseif( $team_columns == '5_col' ) :  
            echo 'row-cols-lg-4 row-cols-lg-5'; 
        elseif( $team_columns == '6_col' ) :  
            echo 'row-cols-lg-4 row-cols-xl-6'; 
        endif;
        ?> g-5 d-flex justify-content-center">

                        <?php
            while ( $team_query->have_posts() ) : $team_query->the_post();
            
            // Get ACF fields from the team post
            $profile_image = get_field('profile_image');
            if($profile_image):
                // Set thumbnail size based on number of columns
                if( $team_columns == '2_col' ) :
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                elseif( $team_columns == '3_col' ) :
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                elseif( $team_columns == '4_col' ) :
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                elseif( $team_columns == '5_col' ) :
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                elseif( $team_columns == '6_col' ) :
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                else:
                    $profile_image_url = $profile_image['sizes']['3-4r576'];
                endif;
            endif;

            $team_nickname = get_field('team_nickname');
            $team_title = get_the_title();
            $team_role = get_field('role');
            $team_link = get_permalink();
            ?>

                        <div class="col p-5">

                            <!-- team-card-wrapper: provides padding-top space for the image overflow -->
                            <div class="team-card-wrapper">

                                <!-- team-card-body: enables overflow:visible so image can escape container -->
                                <div class="border border-light shadow-lg cards-shape-top bg-white team-card-body">

                                    <!-- team-image-overflow: negative margin pulls image above the card -->
                                    <div class="team-image-overflow text-center <?php if( $team_image_border ) : echo 'px-5'; endif; ?>">
                                        <?php if( $profile_image ): ?>
                                        <div class="px-3">
                                            <img src="<?php echo esc_url($profile_image_url); ?>" class="img-cards img-fluid img-rounded-max" alt="<?php echo esc_attr($team_title); ?>">
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="pb-5 px-5 bg-white text-center cards-shape-bottom">

                                        <div class="fosforos pb-5 pt-4">

                                            <div class="my-5">
                                                <h3><?php echo esc_html($team_nickname); ?></h3>
                                            </div>

                                            <?php if( $team_title ):  ?>
                                            <div class="mb-3">
                                                <h5><?php echo esc_html($team_title); ?></h5>
                                            </div>
                                            <?php endif; ?>

                                            <?php if( $team_role ):  ?>
                                            <div class="text-posidonia mb-3">
                                                <h6 class="text-brand-accent"><?php echo esc_html($team_role); ?></h6>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="text-center pb-4">
                                            <a href="<?php echo esc_url($team_link); ?>" class="btn btn-sm btn-brand-main">Learn&nbsp;More</a>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <?php $iTeam++; 
            endwhile; 
            wp_reset_postdata();
            ?>

                    </div>

                    <?php
        else :
            echo '<!-- No team members found -->';
        endif;
        ?>
                    <!-- //end Team Loop -->

                </div>




            </div>
        </div>
    </div>




</div>