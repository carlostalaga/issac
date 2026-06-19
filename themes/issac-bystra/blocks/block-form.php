<?php
/*
███████  ██████  ██████  ███    ███
██      ██    ██ ██   ██ ████  ████
█████   ██    ██ ██████  ██ ████ ██
██      ██    ██ ██   ██ ██  ██  ██
██       ██████  ██   ██ ██      ██


*/
$bg = get_block_background();
$form_headline = get_sub_field('form_headline');
$form_shortcode = get_sub_field('form_shortcode');
?>

<div id="contact-<?php echo $iBlock; ?>" class="container-fluid py-5 <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
    <div class="container bg-white img-contact p-5">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-10 pt-5">


                <?php if( $form_headline ): ?>
                <div class="mb-5">
                    <h6><?php echo esc_html($form_headline); ?></h6>
                </div>
                <?php endif; ?>

                <div>
                    <?php echo do_shortcode($form_shortcode); ?>
                </div>


            </div>
        </div>
    </div>
</div>