<?php
/*
 ██████  ██████  ███    ██ ████████  █████   ██████ ████████
██      ██    ██ ████   ██    ██    ██   ██ ██         ██
██      ██    ██ ██ ██  ██    ██    ███████ ██         ██
██      ██    ██ ██  ██ ██    ██    ██   ██ ██         ██
 ██████  ██████  ██   ████    ██    ██   ██  ██████    ██

*/


$contact_headline = get_sub_field('contact_headline');
$contact_content = get_sub_field('contact_content');
$contact_form_shortcode = get_sub_field('contact_form_shortcode');
?>

<div id="contact-<?php echo $iBlock; ?>" class="container-fluid py-5">

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