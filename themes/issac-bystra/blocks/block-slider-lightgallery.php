<?php
/*
;
███████ ██      ██ ██████  ███████ ██████
██      ██      ██ ██   ██ ██      ██   ██
███████ ██      ██ ██   ██ █████   ██████
     ██ ██      ██ ██   ██ ██      ██   ██
███████ ███████ ██ ██████  ███████ ██   ██


██      ██  ██████  ██   ██ ████████  ██████   █████  ██      ██      ███████ ██████  ██    ██
██      ██ ██       ██   ██    ██    ██       ██   ██ ██      ██      ██      ██   ██  ██  ██
██      ██ ██   ███ ███████    ██    ██   ███ ███████ ██      ██      █████   ██████    ████
██      ██ ██    ██ ██   ██    ██    ██    ██ ██   ██ ██      ██      ██      ██   ██    ██
███████ ██  ██████  ██   ██    ██     ██████  ██   ██ ███████ ███████ ███████ ██   ██    ██

*/
$slider_lightgallery_title = get_sub_field('slider_lightgallery_title');
$slider_lightgallery_images = get_sub_field('slider_lightgallery_gallery'); // This is assuming the gallery field name is 'slider_lightgallery_gallery'
?>

<style>
/*
███████ ██      ██  ██████ ██   ██
██      ██      ██ ██      ██  ██
███████ ██      ██ ██      █████
     ██ ██      ██ ██      ██  ██
███████ ███████ ██  ██████ ██   ██


*/

.slick-slide {
    margin: 0 6px !important;
    width: 100%;
    height: auto;
}

.slick-list {
    margin: 0 -5px;
}

.magnify {
    overflow: hidden;
}

.magnify img {
    transition: transform .5s ease;
}

.magnify:hover img {
    transform: scale(0.94);
    filter: saturate(1.2);
    cursor: zoom-in;
}



/*
██      ██  ██████  ██   ██ ████████  ██████   █████  ██      ██      ███████ ██████  ██    ██
██      ██ ██       ██   ██    ██    ██       ██   ██ ██      ██      ██      ██   ██  ██  ██
██      ██ ██   ███ ███████    ██    ██   ███ ███████ ██      ██      █████   ██████    ████
██      ██ ██    ██ ██   ██    ██    ██    ██ ██   ██ ██      ██      ██      ██   ██    ██
███████ ██  ██████  ██   ██    ██     ██████  ██   ██ ███████ ███████ ███████ ██   ██    ██


*/
.gallery-container>div {
    width: 200px;
    padding: 12px;
    display: inline-block;
}

/* LightGallery backdrop styles moved to _fx.scss */

</style>


<div id="block-slider-lightgallery-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0 d-flex justify-content-center">


    <div class="container m-0 p-0" style="z-index: 20;">


        <?php if($slider_lightgallery_title): ?>
        <div class="bg-danger m-0 pt-4 pb-3 px-5 wow animate__animated animate__fadeInUp">
            <h4 class="text-white"><?php echo $slider_lightgallery_title; ?></h4>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="36px" height="20px" viewBox="0 0 52.416 60.005" style="padding: 0; margin-top: -30px; margin-left: 90px;">
            <polygon fill="#db2b27" transform="scale(1, -1) translate(0, -60.005)" points="0 29.005 52.416 29.005 26.208 0 0 29.005" />
        </svg>
        <?php endif; ?>




        <!-- Slick & Lightgallery -->
        <div class="slickGallery mt-4 px-2" id="gallery-container-slider-<?php echo get_row_index(); ?>">
            <?php if($slider_lightgallery_images): ?>
            <?php foreach($slider_lightgallery_images as $image): ?>
            <div class="magnify" data-src="<?php echo $image['sizes']['1080p'] ?? $image['url']; ?>">
                <img class="img-fluid slick-slide" src="<?php echo $image['sizes']['720p'] ?? $image['url']; ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" />
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>No images found in the gallery.</p>
            <?php endif; ?>
        </div>

    </div>
</div>