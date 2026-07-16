<?php
/*
██   ██ ███████ ██████   ██████
██   ██ ██      ██   ██ ██    ██
███████ █████   ██████  ██    ██
██   ██ ██      ██   ██ ██    ██
██   ██ ███████ ██   ██  ██████

*/

$hero_image = get_sub_field('hero_image');
if($hero_image):
    $hero_image_alt = $hero_image['alt'];
    $hero_image_url = $hero_image['sizes']['7-5r960'];
endif;

$hero_headline = get_sub_field('hero_headline');
$hero_tagline = get_sub_field('hero_tagline');
$hero_content = get_sub_field('hero_content');
$hero_content_reversed = get_sub_field('hero_content_reversed');

if ($hero_content_reversed) {
    $hero_tagline_col = 'col-md-6 col-lg-5 pe-5';
    $hero_content_col = 'col-md-6 offset-lg-1';
} else {
    $hero_tagline_col = 'col-md-6';
    $hero_content_col = 'col-md-6 col-lg-5 offset-lg-1 ps-5';
}
?>

<div class="container-fluid pt-0 pb-5 px-0">

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">

                <?php
        /* -------------------------------------------------------------------------- */
        /*                                 Top section                                */
        /* -------------------------------------------------------------------------- */
        ?>
                <div class="container hero-overlap<?php echo !$hero_image ? ' hero-overlap--solo' : ''; ?>">

                    <?php if ($hero_image): ?>
                    <div class="hero-overlap__media">
                        <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>" class="hero-overlap__image img-fluid img-rounded">
                    </div>
                    <?php endif; ?>

                    <div class="hero-overlap__card-wrap">
                        <div class="hero-overlap__card bg-hueso card-rounded">
                            <div class="hero-overlap__headline headline-1">
                                <?php echo $hero_headline; ?>
                            </div>
                        </div>
                    </div>

                </div>




                <div class="container" style="padding-top: 0px; padding-bottom: 120px;">

                    <?php
        /* -------------------------------------------------------------------------- */
        /*                               Content section                              */
        /* -------------------------------------------------------------------------- */
        ?>
                    <div class="row g-5 px-5 px-md-0 hero-theme-content">
                        <div class="<?php echo esc_attr($hero_tagline_col); ?>">
                            <div>
                                <h2><?php echo $hero_tagline; ?></h2>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr($hero_content_col); ?> align-self-start" style="border-left: 3px solid var(--colour-text);">
                            <div>
                                <?php echo $hero_content; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>