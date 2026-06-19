<?php
/*
███████ ██████   █████   ██████ ███████ ██████
██      ██   ██ ██   ██ ██      ██      ██   ██
███████ ██████  ███████ ██      █████   ██████
     ██ ██      ██   ██ ██      ██      ██   ██
███████ ██      ██   ██  ██████ ███████ ██   ██


*/

$bg = get_block_background();
$spacer_height = get_sub_field('spacer_height');
$spacer_height_value = $spacer_height ?: '0px';

$spacer_style = 'height:' . esc_attr($spacer_height_value) . ';';
if (!empty($bg['styles'])):
    $spacer_style .= $bg['styles'];
endif;
?>
<!-- no round top to use the block as comodin -->
<div id="spacer-<?php echo $iBlock; ?>" class="container-fluid border border-0 border-danger  corner-fill <?php echo esc_attr($bg['class']); ?>" style="<?php echo esc_attr($spacer_style); ?>">
</div>