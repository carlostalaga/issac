<?php
/*

███████ ██      ██ ██████  ███████ ██████
██      ██      ██ ██   ██ ██      ██   ██
███████ ██      ██ ██   ██ █████   ██████
     ██ ██      ██ ██   ██ ██      ██   ██
███████ ███████ ██ ██████  ███████ ██   ██


 ██████  █████  ██████  ██████  ███████
██      ██   ██ ██   ██ ██   ██ ██
██      ███████ ██████  ██   ██ ███████
██      ██   ██ ██   ██ ██   ██      ██
 ██████ ██   ██ ██   ██ ██████  ███████

/* Slider Cards Block */
?>


<div id="block-slider-cards-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0 d-flex justify-content-center bg-light">
    <div class="container m-0 pb-5" style="z-index: 20;">
        <div class="slider-cards slick-slider">
            <?php
            if( have_rows('slider_cards_repeater') ):
                while ( have_rows('slider_cards_repeater') ) : the_row();
                    $image = get_sub_field('image');
                    if ($image):
                        $image_url = $image ? $image['sizes']['576sm'] : '';
                        $image_alt = $image ? $image['alt'] : '';
                    endif;
                    $headline = get_sub_field('headline');
                    $tagline = get_sub_field('tagline');
                    $description = get_sub_field('description');
                    $link = get_sub_field('link'); // ACF link array
                    if ($link):
                        $link_url = $link['url'];
                        $link_title = $link['title'];
                        $link_target = $link['target'] ? $link['target'] : '_self';
                    endif;
            ?>

            <?php if($link): ?>
            <a href="<?php echo $link_url ?>" class="text-posidonia">
                <?php endif ?>
                <div class="magnify text-center bg-white p-3">
                    <div class="headline mb-3"><?php echo $headline ?></div>
                    <img src="<?php echo $image_url ?>" class="img-fluid mb-3" alt="<?php echo $image_alt ?>">
                    <div class="tagline mb-3"><?php echo $tagline ?></div>
                    <div class="description"><?php echo $description ?></div>
                </div>
                <?php if($link): ?>
            </a>
            <?php endif ?>


            <?php
                endwhile;
            endif;
            ?>
        </div>
    </div>
</div>

<!-- Slider styles moved to style.scss -->