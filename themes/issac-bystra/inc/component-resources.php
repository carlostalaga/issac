<?php
/**
 * Display Resources Component
 * 
 * Renders a list of file downloads and/or links from an ACF repeater field.
 * 
 * @param string $repeater_field_name  The ACF repeater field name containing resources.
 * @param bool   $alignment            Text alignment for Original Style only. false = center (default), true = left-aligned.
 *                                     Ignored when using Alternate Style.
 * @param bool   $alternate_style      Design style toggle:
 *                                     - false (default): Title inside button with icon on right
 *                                     - true: Title separate from button, button shows "Download" or "Learn more"
 * @param bool   $use_light_buttons    When true, swap button colors to btn-blanco (files) / btn-hueso (links).
 * 
 * Usage Examples:
 * 
 *   Original Style (title inside button):
 *   <?php display_resources('resource_entries'); ?> // centered
* <?php display_resources('resource_entries', true); ?> // left-aligned
*
* Alternate Style (title separate, buttons say "Download" / "Learn more"):
* <?php display_resources('resource_entries', false, true); ?>
*
* Button Colors:
* - Files: btn-brand-accent (teal)
* - Links: btn-brand-main (green)
*/

function display_resources($repeater_field_name, $alignment = false, $alternate_style = false, $use_light_buttons = false) {
$file_button_class = $use_light_buttons ? 'btn btn-hueso' : 'btn btn-brand-accent';
$link_button_class = $use_light_buttons ? 'btn btn-blanco' : 'btn btn-brand-main';
?>
<div class="<?php if (!$alternate_style && $alignment): echo 'text-start'; elseif (!$alternate_style): echo 'text-center'; endif; ?>">

    <?php
        if (have_rows($repeater_field_name)): $iDoc = 0; // Set the increment variable
            while (have_rows($repeater_field_name)): the_row();
                $type_of_resource = get_sub_field('type_of_resource');
                
                // Initialize variables
                $url = '';
                $title = '';
                $caption = '';
                $icon = '';
                $link_url = '';
                $link_title = '';
                $link_target = '_self';
                
                $file = get_sub_field('file');
                if ($file):
                    $url = $file['url'];
                    $title = $file['title'];
                    $caption = $file['caption'];
                    $icon = $file['icon'];
                endif;
                $link = get_sub_field('link');
                if ($link):
                    $link_url = $link['url'];
                    $link_title = $link['title'];
                    $link_target = $link['target'] ? $link['target'] : '_self';
                endif;                
    ?>


    <?php if ($alternate_style): ?>
    <!-- Alternate Style: Title separate from button -->
    <?php if ($type_of_resource == 'file'): ?>
    <div class="d-flex justify-content-between align-items-center mb-3 py-4 border-bottom border-dark">
        <h6 class="fw-normal d-flex align-items-center" style="overflow-wrap: break-word; text-wrap: wrap; flex: 1;">
            <?php echo esc_html($title); ?>
        </h6>
        <a class="<?php echo esc_attr($file_button_class); ?> d-inline-flex align-items-center justify-content-center gap-2 ps-5 text-nowrap" href="<?php echo esc_attr($url); ?>" target="_blank" title="<?php echo esc_attr($title); ?>">
            <span style="overflow-wrap: break-word; text-wrap: wrap;">Download</span>
            <!-- <i class="fas fa-file" aria-hidden="true" style="font-size: 1.4rem; color: inherit; transition: none;"></i> -->
        </a>
    </div>
    <?php elseif ($type_of_resource == 'link'): ?>
    <div class="d-flex justify-content-between align-items-center mb-3 py-4 border-bottom border-dark">
        <h6 class="fw-normal d-flex align-items-center" style="overflow-wrap: break-word; text-wrap: wrap; flex: 1;">
            <?php echo esc_html($link_title); ?>
        </h6>
        <a class="<?php echo esc_attr($link_button_class); ?> d-inline-flex align-items-center justify-content-center gap-2 ps-5 text-nowrap" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" title="<?php echo esc_attr($link_title); ?>" aria-label="Learn more about <?php echo esc_attr($link_title); ?>">
            <span style="overflow-wrap: break-word; text-wrap: wrap;">Learn more</span>
            <!-- <i class="fas fa-link" aria-hidden="true" style="font-size: 1.4rem; color: inherit; transition: none;"></i> -->
        </a>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <!-- Original Style: Title inside button -->
    <?php if ($type_of_resource == 'file'): ?>
    <a class="<?php echo esc_attr($file_button_class); ?> text-start fit-content mb-5" href="<?php echo esc_attr($url); ?>" target="_blank" title="<?php echo esc_attr($title); ?>">
        <span style="overflow-wrap: break-word; text-wrap: wrap;">
            <?php if($title): echo esc_html($title); else: ?>Learn&nbsp;more<?php endif; ?>
        </span>
        <!--  <i class="fas fa-file" aria-hidden="true" style="font-size: 1.4rem; color: inherit; transition: none;"></i> -->
    </a>
    <?php elseif ($type_of_resource == 'link'): ?>
    <a class="<?php echo esc_attr($link_button_class); ?> text-start fit-content mb-5" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" title="<?php echo esc_attr($link_title); ?>" aria-label="Learn more about <?php echo esc_attr($link_title); ?>">
        <span style="overflow-wrap: break-word; text-wrap: wrap;">
            <?php if($link_title): echo esc_html($link_title); else: ?>Learn&nbsp;more<?php endif; ?>
        </span>
        <!-- <i class="fas fa-arrow-right" aria-hidden="true" style="font-size: 1.4rem; color: inherit; transition: none;"></i> -->
    </a>
    <?php endif; ?>
    <?php endif; ?>
    <?php $iDoc++; endwhile;
        else :
            // No value.
            // Do something...
        endif;
        ?>
</div>

<?php
}
?>