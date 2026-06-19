<?php
/*
 ██████  ██████  ███    ██ ████████  █████   ██████ ████████
██      ██    ██ ████   ██    ██    ██   ██ ██         ██
██      ██    ██ ██ ██  ██    ██    ███████ ██         ██
██      ██    ██ ██  ██ ██    ██    ██   ██ ██         ██
 ██████  ██████  ██   ████    ██    ██   ██  ██████    ██

*/


$bg = get_block_background();
$contact_section_backgrounds = ['bg-3', 'bg-2', 'bg-2a', 'bg-hueso', 'bg-white', 'bg-hero', 'bg-1', 'bg-custom'];
$contact_is_section_bg = in_array($bg['value'], $contact_section_backgrounds, true);
$contact_section_class = ($contact_is_section_bg && !empty($bg['class'])) ? $bg['class'] : 'bg-3';
$contact_section_style_attr = !empty($bg['style_attr']) ? $bg['style_attr'] : ' style="--corner-fill-colour:#A48C50;"';
$contact_headline = get_sub_field('contact_headline');
$contact_content = get_sub_field('contact_content');
$contact_form_shortcode = get_sub_field('contact_form_shortcode');
?>

<div id="contact-<?php echo $iBlock; ?>" class="container-fluid py-5 card-rounded-top corner-fill <?php echo esc_attr($contact_section_class); ?>" <?php echo $contact_section_style_attr; ?>>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">


                <div class="container bg-brand-main contact-rounded p-5" style="margin-bottom: 270px;">
                    <div class="row">

                        <div class="col-12 p-5">
                            <h4 class="text-hueso mb-5"><?php echo $contact_headline; ?></h4>
                            <?php if( $contact_form_shortcode ): ?>
                            <?php echo do_shortcode($contact_form_shortcode); ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>

</div>