<?php
/*
 ██████  ██    ██  ██████  ████████ ███████
██    ██ ██    ██ ██    ██    ██    ██
██    ██ ██    ██ ██    ██    ██    █████
██ ▄▄ ██ ██    ██ ██    ██    ██    ██
 ██████   ██████   ██████     ██    ███████
    ▀▀

*/

$bg = get_block_background();

$quote_image = get_sub_field('quote_image');
if($quote_image):
    $quote_image_url = $quote_image['sizes']['3-4r576'];
    $quote_image_alt = $quote_image['alt'];
    $quote_image_title = $quote_image['title'];
endif;

$quote_content = get_sub_field('quote_content');
?>

<div class="container-fluid py-5 <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="row">
                    <div class="col-12 col-md-4 pe-md-5 pb-5 pb-md-0">
                        <?php if($quote_image): ?>
                        <img src="<?php echo esc_url($quote_image_url); ?>" alt="<?php echo esc_attr($quote_image_alt); ?>" class="img-fluid img-rounded-max">
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-8 d-flex align-items-center">
                        <div>
                            <strong><?php echo $quote_content; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>