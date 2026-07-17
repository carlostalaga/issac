<?php
/*
 ██████  ██    ██  ██████  ████████ ███████
██    ██ ██    ██ ██    ██    ██    ██
██    ██ ██    ██ ██    ██    ██    █████
██ ▄▄ ██ ██    ██ ██    ██    ██    ██
 ██████   ██████   ██████     ██    ███████
    ▀▀

*/

$quote_label = get_sub_field('quote_label');
$quote_content = get_sub_field('quote_content');
?>

<div class="container-fluid py-5">
    <div class="container quote-shape bg-hueso px-5 pt-5 pb-3">
        <div class="row d-flex justify-content-center">
            <div class="col-12">
                <div class="mb-3">
                    <h6 class="text-uppercase"><?php echo $quote_label; ?></h6>
                </div>
                <div>
                    <?php echo $quote_content; ?>
                </div>
            </div>
        </div>
    </div>
</div>