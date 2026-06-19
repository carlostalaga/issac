<?php
/*
██████  ██████  ███████  █████  ██████   ██████ ██████  ██    ██ ███    ███ ██████  ███████
██   ██ ██   ██ ██      ██   ██ ██   ██ ██      ██   ██ ██    ██ ████  ████ ██   ██ ██
██████  ██████  █████   ███████ ██   ██ ██      ██████  ██    ██ ██ ████ ██ ██████  ███████
██   ██ ██   ██ ██      ██   ██ ██   ██ ██      ██   ██ ██    ██ ██  ██  ██ ██   ██      ██
██████  ██   ██ ███████ ██   ██ ██████   ██████ ██   ██  ██████  ██      ██ ██████  ███████


*/

$bg = get_block_background();
?>

<div id="breadcrumbs-<?php echo $iBlock; ?>" class="container-fluid border border-0 border-danger <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>

    <div class="container">


        <div>
            <?php 
            $current_post_type = get_post_type();
            
            // Services post type - check segment taxonomy
            if ($current_post_type === 'services') :
                if (has_term('children', 'segment')) : ?>
            <a href="<?php echo esc_url(home_url('/children/')); ?>" class="btn btn-sm btn-aguamarina">FOR CHILDREN <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <?php elseif (has_term('adults', 'segment')) : ?>
            <a href="<?php echo esc_url(home_url('/adults/')); ?>" class="btn btn-sm btn-brand-main">FOR ADULTS <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <?php endif;
            
            // Doctors and Staff post type
            elseif ($current_post_type === 'doctors_staff') : ?>
            <a href="<?php echo esc_url(home_url('/doctors-and-staff/')); ?>" class="btn btn-sm btn-brand-main">OUR TEAM <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </div>



    </div>
</div>