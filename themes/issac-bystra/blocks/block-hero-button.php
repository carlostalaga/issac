<?php
/*
██   ██ ███████ ██████   ██████
██   ██ ██      ██   ██ ██    ██
███████ █████   ██████  ██    ██
██   ██ ██      ██   ██ ██    ██
██   ██ ███████ ██   ██  ██████


██████  ██    ██ ████████ ████████  ██████  ███    ██
██   ██ ██    ██    ██       ██    ██    ██ ████   ██
██████  ██    ██    ██       ██    ██    ██ ██ ██  ██
██   ██ ██    ██    ██       ██    ██    ██ ██  ██ ██
██████   ██████     ██       ██     ██████  ██   ████
*/

$hero_button_colour = get_sub_field('hero_button_colour');
$hero_button_link = get_sub_field('hero_button_link');
$hero_button_reduce_gap = get_sub_field('hero_button_reduce_gap');

if ($hero_button_link) { 
    $hero_button_link_url = $hero_button_link['url'];
    $hero_button_link_title = !empty($hero_button_link['title']) ? $hero_button_link['title'] : 'Learn More';
    $hero_button_link_target = $hero_button_link['target'] ? $hero_button_link['target'] : '_self';
}

// Default button class
$hero_button_class = 'btn-aguamarina';

// Assign button class based on the direct value from ACF
switch ($hero_button_colour) {
    case '#db2b27':
        $hero_button_class = 'btn-aguamarina';
        break;
    case '#262661':
        $hero_button_class = 'btn-posidonia';
        break;
    case '#1a416f':
        $hero_button_class = 'btn-posidonia';
        break;
    case '#3d5c92':
        $hero_button_class = 'btn-cielo';
        break;
    case '#00664a':
        $hero_button_class = 'btn-aguamarina';
        break;
    case '#008f88':
        $hero_button_class = 'btn-aguamarina';
        break;
}
?>

<div id="hero_button-<?php echo $iBlock; ?>" class="container-fluid <?php echo $hero_button_reduce_gap ? 'pt-3 pb-0' : 'pt-5 pb-4'; ?> border border-0 border-success">

    <?php if ($hero_button_link): ?>
    <div class="container">
        <a href="<?php echo esc_url($hero_button_link_url); ?>" target="<?php echo esc_attr($hero_button_link_target); ?>">
            <button class="btn <?php echo esc_attr($hero_button_class); ?> text-center w-100">
                <?php echo esc_html($hero_button_link_title); ?>
            </button>
        </a>
    </div>
    <?php endif; ?>

</div>