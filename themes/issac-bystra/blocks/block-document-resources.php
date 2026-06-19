<?php 

/*
██████   ██████   ██████ ██    ██ ███    ███ ███████ ███    ██ ████████
██   ██ ██    ██ ██      ██    ██ ████  ████ ██      ████   ██    ██
██   ██ ██    ██ ██      ██    ██ ██ ████ ██ █████   ██ ██  ██    ██
██   ██ ██    ██ ██      ██    ██ ██  ██  ██ ██      ██  ██ ██    ██
██████   ██████   ██████  ██████  ██      ██ ███████ ██   ████    ██


██████  ███████ ███████  ██████  ██    ██ ██████   ██████ ███████ ███████
██   ██ ██      ██      ██    ██ ██    ██ ██   ██ ██      ██      ██
██████  █████   ███████ ██    ██ ██    ██ ██████  ██      █████   ███████
██   ██ ██           ██ ██    ██ ██    ██ ██   ██ ██      ██           ██
██   ██ ███████ ███████  ██████   ██████  ██   ██  ██████ ███████ ███████


*/

$bg = get_block_background();
$document_resources_title = get_sub_field('document_resources_title');
$document_resources_columns = get_sub_field('document_resources_columns');
?>


<style>
.newspaper-1 {
    column-count: 1;
    column-gap: 40px;
}
.newspaper-2 {
    column-count: 2;
    column-gap: 40px;
}

@media screen and (max-width: 992px) {
    .newspaper-2 {
        column-count: 1;
    }
}

/* List separator styles moved to style.scss */
</style>

<div id="documentResources-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0 <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
    <div class="container">

        <?php if($document_resources_title): ?>
        <h2 class="pb-5"><?php echo $document_resources_title ?></h2>
        <?php endif; ?>

        <div class="<?php if($document_resources_columns == '1_col'): echo 'newspaper-1'; elseif($document_resources_columns == '2_col'): echo 'newspaper-2'; endif; ?>">


            <?php
                echo display_resources('document_resource_repeater', true, true, $bg['use_light_buttons']);
            ?>


        </div>
    </div>
</div>