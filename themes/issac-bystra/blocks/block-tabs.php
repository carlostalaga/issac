<?php 
/*
████████  █████  ██████  ███████
   ██    ██   ██ ██   ██ ██
   ██    ███████ ██████  ███████
   ██    ██   ██ ██   ██      ██
   ██    ██   ██ ██████  ███████

*/

$bg = get_block_background();
?>
<div id="tabs-<?php echo $iBlock; ?>" class="container-fluid tabs-block py-0 px-0 <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
    <div class="container tabsBox py-0">

        <?php if (have_rows('tabs_repeater')): $tabIndex = 0; ?>

        <!-- Nav tabs -->
        <ul class="nav tabs-icon-nav justify-content-center flex-wrap" id="myTab-<?php echo $iBlock; ?>" role="tablist">
            <?php while (have_rows('tabs_repeater')): the_row();
                $tab_label = get_sub_field('tab_label');
                $is_active = ($tabIndex === 0);
            ?>
            <li class="nav-item" role="presentation">
                <button class="tabs-icon-btn <?php echo $is_active ? 'active' : ''; ?>" id="tab-<?php echo $iBlock; ?>-<?php echo $tabIndex; ?>-tab" data-bs-toggle="tab" data-bs-target="#tab-<?php echo $iBlock; ?>-<?php echo $tabIndex; ?>" type="button" role="tab" aria-controls="tab-<?php echo $iBlock; ?>-<?php echo $tabIndex; ?>" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                    <span class="tabs-icon-label"><?php echo esc_html($tab_label); ?></span>
                </button>
            </li>
            <?php $tabIndex++; endwhile; ?>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content p-0 bg-danger" id="myTabContent-<?php echo $iBlock; ?>">
            <?php 
            // Reset the repeater to loop again for tab content
            $tabIndex = 0;
            while (have_rows('tabs_repeater')): the_row();
                $tab_content = get_sub_field('tab_content');
                $is_active = ($tabIndex === 0);
            ?>
            <div class="tab-pane fade <?php echo $is_active ? 'show active' : ''; ?>" id="tab-<?php echo $iBlock; ?>-<?php echo $tabIndex; ?>" role="tabpanel" aria-labelledby="tab-<?php echo $iBlock; ?>-<?php echo $tabIndex; ?>-tab">

                <?php if ($tab_content): ?>
                <div class="tabs-content-area mt-4">
                    <?php echo $tab_content; ?>
                </div>
                <?php endif; ?>

            </div>
            <?php $tabIndex++; endwhile; ?>
        </div>

        <?php else: ?>
        <!-- No tabs configured -->
        <?php endif; ?>

    </div>
</div>