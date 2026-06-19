<?php /*
███████╗███╗   ███╗██████╗ ███████╗██████╗ 
██╔════╝████╗ ████║██╔══██╗██╔════╝██╔══██╗
█████╗  ██╔████╔██║██████╔╝█████╗  ██║  ██║
██╔══╝  ██║╚██╔╝██║██╔══██╗██╔══╝  ██║  ██║
███████╗██║ ╚═╝ ██║██████╔╝███████╗██████╔╝
╚══════╝╚═╝     ╚═╝╚═════╝ ╚══════╝╚═════╝ 
                                           
███╗   ███╗███████╗██████╗ ██╗ █████╗      
████╗ ████║██╔════╝██╔══██╗██║██╔══██╗     
██╔████╔██║█████╗  ██║  ██║██║███████║     
██║╚██╔╝██║██╔══╝  ██║  ██║██║██╔══██║     
██║ ╚═╝ ██║███████╗██████╔╝██║██║  ██║     
╚═╝     ╚═╝╚══════╝╚═════╝ ╚═╝╚═╝  ╚═╝     
*/ 

$bg = get_block_background();
$format = get_sub_field('format');
$video_width = get_sub_field('video_width');
$iframe = get_sub_field('media');
$video = get_sub_field('video');
$posterimage = get_sub_field('posterimage');
if($posterimage):
    $posterimage_url = $posterimage['sizes']['11-5r720'];
    $posterimage_alt = $posterimage['alt'];
endif;
?>



<div id="block-embed-media">
    <div class="container-fluid py-5 px-5 px-md-0 <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
        <div class="container">
            <div class="row px-2 spacer-inner-block-redux d-flex justify-content-center">


                <!-- content -->
                <div class="col-12  
                    <?php                             
                    switch ($video_width) {
                        case '12':
                            echo 'col-md-12 col-xl-12';
                            break;
                        case '11':
                            echo 'col-md-11 col-xl-11';
                            break;
                        case '10':
                            echo 'col-md-10 col-xl-10';
                            break;
                        case '9':
                            echo 'col-md-9 col-xl-9';
                            break;
                        case '8':
                            echo 'col-md-8 col-xl-8';
                            break;
                        case '6':
                            echo 'col-md-8 col-xl-6';
                            break;
                        case '5':
                            echo 'col-md-8 col-xl-5';
                            break;
                        case '4':
                            echo 'col-md-8 col-xl-4';
                            break;
                        default:
                            // Handle the case when $video_width does not match any of the specified conditions
                            echo 'col-xxl-12';
                            break;
                    }
                    ?>
                    text-center">


                    <?php if( $format == 'external' ) { ?>

                    <!-- Video embedding -->

                    <style>
                    .embed-container {
                        position: relative;
                        padding-bottom: 56.25%;
                        overflow: hidden;
                        max-width: 100%;
                        height: auto;
                    }

                    .embed-container iframe,
                    .embed-container object,
                    .embed-container embed {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    }
                    </style>

                    <div class="embed-container mt-5 d-flex flex-column justify-content-center align-items-center">

                        <?php
                        // Load value.
                        //$iframe = get_field('project_embeded_media');

                        // Use preg_match to find iframe src.
                        preg_match('/src="(.+?)"/', $iframe, $matches);
                        $src = $matches[1];

                        // Add extra parameters to src and replace HTML.
                        $params = array(
                            'controls'  => 1,
                            'hd'        => 1,
                            'autoplay'  => 0,
                            'autohide'  => 1
                        );
                        $new_src = add_query_arg($params, $src);
                        $iframe = str_replace($src, $new_src, $iframe);

                        // Add extra attributes to iframe HTML.
                        $attributes = '';
                        $iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);

                        // Display customized HTML.
                        echo $iframe;
                        ?>

                    </div>
                    <!-- //end Video embedding -->

                    <?php } elseif ( $format == 'internal' ) { ?>

                    <!-- video mp4 -->
                    <div class="">

                        <video playsinline controls style="width: 100%; height: auto;" poster="<?php echo $posterimage_url; ?>">
                            <source src="<?php echo $video['url']; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>

                    </div>
                    <!-- //end video mp4 -->

                    <?php } ?>


                </div>
                <!-- //end content -->


            </div>
        </div>
    </div>
</div>