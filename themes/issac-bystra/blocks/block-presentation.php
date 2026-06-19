<?php
/*
██████  ██████  ███████ ███████ ███████ ███    ██ ████████  █████  ████████ ██  ██████  ███    ██
██   ██ ██   ██ ██      ██      ██      ████   ██    ██    ██   ██    ██    ██ ██    ██ ████   ██
██████  ██████  █████   ███████ █████   ██ ██  ██    ██    ███████    ██    ██ ██    ██ ██ ██  ██
██      ██   ██ ██           ██ ██      ██  ██ ██    ██    ██   ██    ██    ██ ██    ██ ██  ██ ██
██      ██   ██ ███████ ███████ ███████ ██   ████    ██    ██   ██    ██    ██  ██████  ██   ████


*/

$bg = get_block_background();

$presentation_image = get_sub_field('presentation_image');
if($presentation_image):
    $presentation_image_alt = $presentation_image['alt'];
    $presentation_image_url = $presentation_image['sizes']['1080p'];
endif;

$presentation_headline_column_a = get_sub_field('presentation_headline_column_a');
$presentation_headline_column_b = get_sub_field('presentation_headline_column_b');
$presentation_content_column_a = get_sub_field('presentation_content_column_a');
$presentation_content_column_b = get_sub_field('presentation_content_column_b');
$presentation_ticker = get_sub_field('presentation_ticker');

?>

<div class="container-fluid presentation-block <?php echo esc_attr($bg['class']); ?> p-0" <?php echo $bg['style_attr']; ?>>

    <?php
        /* -------------------------------------------------------------------------- */
        /*                                 Top section                                */
        /* -------------------------------------------------------------------------- */
        ?>
    <div class="row">
        <div class="col-12">

            <?php if ($presentation_image): ?>
            <div class="presentation-block__image">
                <img src="<?php echo esc_url($presentation_image_url); ?>" class="img-rounded-top" alt="<?php echo esc_attr($presentation_image_alt); ?>">
            </div>
            <?php endif; ?>

        </div>

    </div>


    <?php
    /* -------------------------------------------------------------------------- */
    /*                               Content section                              */
    /* -------------------------------------------------------------------------- */
    ?>
    <div class="container-fluid bg-brand-accent presentation-content-wrapper">

        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-11 col-lg-10">


                    <div class="container presentation-content-container">

                        <div class="row g-5 presentation-theme-content">

                            <div class="col-md-6">
                                <div class="block-presentation__card-inner">
                                    <div class="mb-3 ">
                                        <h4><?php echo $presentation_headline_column_a; ?></h4>
                                    </div>
                                    <div>
                                        <?php echo $presentation_content_column_a; ?>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="block-presentation__card-inner">
                                    <div class="mb-3">
                                        <h4><?php echo $presentation_headline_column_b; ?></h4>
                                    </div>
                                    <div>
                                        <?php echo $presentation_content_column_b; ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>



                </div>
            </div>
        </div>

    </div>


    <?php 
    /* -------------------------------------------------------------------------- */
    /*                           Marquee Ticker Section                           */
    /* -------------------------------------------------------------------------- */
    if($presentation_ticker): 
    ?>
    <div class="col-12 block-presentation__marquee-wrapper">
        <div class="presentation-marquee headline-2" aria-label="<?php echo esc_attr($presentation_ticker); ?>">
            <div class="presentation-marquee__track">
                <?php for($group = 0; $group < 2; $group++): ?>
                <div class="presentation-marquee__group" aria-hidden="true">
                    <?php for($item = 0; $item < 4; $item++): ?>
                    <span class="presentation-marquee__item text-hueso headline-1"><?php echo esc_html($presentation_ticker); ?></span>
                    <?php endfor; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>