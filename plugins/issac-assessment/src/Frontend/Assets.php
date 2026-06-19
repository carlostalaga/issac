<?php

declare(strict_types=1);

namespace Issac\Frontend;

defined('ABSPATH') || exit;

final class Assets
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        $post = get_post();
        if (!$post || !has_shortcode($post->post_content ?? '', 'issac_domain')) {
            return;
        }

        wp_enqueue_style(
            'issac-css',
            ISSAC_URL . 'assets/css/issac.css',
            [],
            ISSAC_VERSION
        );

        wp_enqueue_script(
            'issac-js',
            ISSAC_URL . 'assets/js/issac.js',
            [],
            ISSAC_VERSION,
            true
        );

        $domainCode = sanitize_text_field($_GET['d'] ?? '');

        wp_localize_script('issac-js', 'issacData', [
            'restUrl'    => esc_url_raw(rest_url('issac/v1/')),
            'nonce'      => wp_create_nonce('wp_rest'),
            'domainCode' => $domainCode ?: null,
        ]);
    }
}
