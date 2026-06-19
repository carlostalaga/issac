<?php
/*
 ██████ ████████  █████      ██████  ██    ██ ████████ ████████  ██████  ███    ██ ███████
██         ██    ██   ██     ██   ██ ██    ██    ██       ██    ██    ██ ████   ██ ██
██         ██    ███████     ██████  ██    ██    ██       ██    ██    ██ ██ ██  ██ ███████
██         ██    ██   ██     ██   ██ ██    ██    ██       ██    ██    ██ ██  ██ ██      ██
 ██████    ██    ██   ██     ██████   ██████     ██       ██     ██████  ██   ████ ███████
*/

$bg = get_block_background();
$cta_buttons_repeater = get_sub_field('cta_buttons_repeater');
if (empty($cta_buttons_repeater) || !is_array($cta_buttons_repeater)):
    return;
endif;
?>
<div class="container-fluid <?php echo esc_attr($bg['class']); ?>"<?php echo $bg['style_attr']; ?>>
    <div class="container cta-buttons-container">
        <div class="row nav-hero-tabs d-flex justify-content-center pb-4 pb-lg-0">
            <?php foreach ($cta_buttons_repeater as $cta_buttons_row): ?>
            <?php
                $cta_buttons_link = $cta_buttons_row['link'] ?? [];
                if (!is_array($cta_buttons_link)):
                    continue;
                endif;

                $cta_buttons_link_url = $cta_buttons_link['url'] ?? '';
                if (empty($cta_buttons_link_url)):
                    continue;
                endif;

                $cta_buttons_link_title = $cta_buttons_link['title'] ?? '';
                $cta_buttons_link_target = $cta_buttons_link['target'] ?? '';
                $cta_buttons_link_rel = ($cta_buttons_link_target === '_blank') ? 'noopener noreferrer' : '';
                ?>
            <div class="col-6 col-sm-6 col-md-4 col-lg-3 pt-4 py-lg-0">
                <a class="btn btn-hero w-100 nav-hero-tab" href="<?php echo esc_url($cta_buttons_link_url); ?>" <?php if (!empty($cta_buttons_link_target)): ?>target="<?php echo esc_attr($cta_buttons_link_target); ?>" <?php endif; ?><?php if (!empty($cta_buttons_link_rel)): ?> rel="<?php echo esc_attr($cta_buttons_link_rel); ?>" <?php endif; ?>>
                    <?php echo esc_html($cta_buttons_link_title ?: __('Learn more', 'eyemedics')); ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>