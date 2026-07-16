<?php
/*
███████ ██████   █████   ██████ ███████ ██████
██      ██   ██ ██   ██ ██      ██      ██   ██
███████ ██████  ███████ ██      █████   ██████
     ██ ██      ██   ██ ██      ██      ██   ██
███████ ██      ██   ██  ██████ ███████ ██   ██


*/

$spacer_height = get_sub_field('spacer_height');
$spacer_height_value = $spacer_height ?: '0px';

$spacer_style = 'height:' . esc_attr($spacer_height_value) . ';';
?>
<div id="spacer-<?php echo $iBlock; ?>" class="container-fluid border border-0 border-danger" style="<?php echo esc_attr($spacer_style); ?>">
</div>