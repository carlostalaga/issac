<?php
/**
 * Block Background Colour Helper
 *
 * Centralises the background colour system for all blocks.
 * Supports the ACF "backgrounds" group field with preset classes,
 * custom colours, text colour overrides via CSS custom properties,
 * and dark-background detection for child element variants.
 *
 * Usage in any block:
 *   $bg = get_block_background();
 *   Then in HTML:
 *   <div class="container-fluid <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
 */

if (!function_exists('get_corner_fill_colour_value')):

/**
 * Resolves a corner-fill preset (bg-brand-main, etc.) or custom picker value to a CSS colour.
 * Values mirror scss/_colours.scss.
 */
function get_corner_fill_colour_value($choice, $custom_colour = '') {
    $preset_colours = [
        'bg-brand-main'    => '#646B47',
        'bg-brand-accent'  => '#A48C50',
        'bg-brand-support' => '#F58220',
        'bg-hueso'         => '#FAF7EE',
        'bg-white'         => '#ffffff',
        'bg-2'             => '#ffffff',
        'bg-2a'            => '#ffffff',
        'bg-3'             => '#A48C50',
    ];

    if ($choice === 'bg-custom' && !empty($custom_colour)):
        return $custom_colour;
    endif;

    return $preset_colours[$choice] ?? '';
}

endif;

if (!function_exists('get_block_background')):

function get_block_background($field_name = 'backgrounds') {
    $backgrounds = get_sub_field($field_name) ?: [];

    // Seamless ACF clones flatten the backgrounds group at layout level.
    if (empty($backgrounds)):
        $flat_background_colour = get_sub_field('background_colour');
        if ($flat_background_colour !== null && $flat_background_colour !== ''):
            $backgrounds = [
                'background_colour'            => $flat_background_colour,
                'custom_background_colour'     => get_sub_field('custom_background_colour'),
                'custom_text_colour'           => get_sub_field('custom_text_colour'),
                'corner_fill_colour'           => get_sub_field('corner_fill_colour'),
                'corner_fill_custom_colour'    => get_sub_field('corner_fill_custom_colour'),
            ];
        endif;
    endif;

    $background_colour = $backgrounds['background_colour'] ?? '';
    $custom_background_colour = $backgrounds['custom_background_colour'] ?? '';
    $custom_text_colour = $backgrounds['custom_text_colour'] ?? '';
    $corner_fill_colour = $backgrounds['corner_fill_colour'] ?? '';
    $corner_fill_custom_colour = $backgrounds['corner_fill_custom_colour'] ?? '';

    $background_colour_field = null;

    if (function_exists('get_sub_field_object')):
        $backgrounds_field = get_sub_field_object($field_name);
        if (!empty($backgrounds_field['sub_fields']) && is_array($backgrounds_field['sub_fields'])):
            foreach ($backgrounds_field['sub_fields'] as $sub_field):
                if (($sub_field['name'] ?? '') === 'background_colour'):
                    $background_colour_field = $sub_field;
                    break;
                endif;
            endforeach;
        endif;

        if (empty($background_colour_field['choices'])):
            $background_colour_field = get_sub_field_object('background_colour');
        endif;
    endif;

    $allowed = array_keys($background_colour_field['choices'] ?? []);
    $class = in_array($background_colour, $allowed, true) ? $background_colour : '';

    if (empty($class) && !empty($background_colour) && preg_match('/^bg-/', $background_colour)):
        $class = $background_colour;
    endif;
    $styles = '';

    if ($background_colour === 'bg-custom'):
        if (!empty($custom_background_colour)):
            $styles .= 'background-color:' . esc_attr($custom_background_colour) . ';';
        endif;
        if (!empty($custom_text_colour)):
            $styles .= 'color:' . esc_attr($custom_text_colour) . ';';
            $styles .= '--heading-colour:' . esc_attr($custom_text_colour) . ';';
            $styles .= '--text-colour:' . esc_attr($custom_text_colour) . ';';
            $styles .= '--link-colour:' . esc_attr($custom_text_colour) . ';';
        endif;
    endif;

    // Corner fill: explicit ACF choice takes precedence, otherwise solid preset backgrounds.
    $corner_fill_css_colour = get_corner_fill_colour_value($corner_fill_colour, $corner_fill_custom_colour);
    if (empty($corner_fill_css_colour)):
        $corner_fill_css_colour = get_corner_fill_colour_value($background_colour, $custom_background_colour);
    endif;
    if (!empty($corner_fill_css_colour)):
        $styles .= '--corner-fill-colour:' . esc_attr($corner_fill_css_colour) . ';';
    endif;

    $dark_backgrounds = ['bg-brand-main', 'bg-brand-accent'];
    $is_dark = in_array($background_colour, $dark_backgrounds, true);

    $style_attr = '';
    if (!empty($styles)):
        $style_attr = ' style="' . esc_attr($styles) . '"';
    endif;

    return [
        'value'             => $background_colour,
        'class'             => $class,
        'styles'            => $styles,
        'style_attr'        => $style_attr,
        'is_dark'           => $is_dark,
        'use_light_buttons' => $is_dark,
    ];
}

endif;
