<?php
namespace Issac\Content;

defined('ABSPATH') || exit;

/**
 * Enforces the instrument numbering scheme at save time via acf/validate_value.
 *
 *  - domain_code: required; a single digit 1\u20135; unique across all domains.
 *  - item_code:   required; matches ^\d+\.\d+$ (e.g. "1.1", "5.18");
 *                 unique across all items.
 *
 * The integer before the dot in an item code is its domain number; the integer
 * after restarts at 1 for each domain. Uniqueness is what guarantees responses
 * (which key off item_code) always resolve to exactly one item.
 */
final class Validation
{
    public static function register(): void
    {
        add_filter('acf/validate_value/key=field_issac_domain_code', [self::class, 'validateDomainCode'], 10, 4);
        add_filter('acf/validate_value/key=field_issac_item_code', [self::class, 'validateItemCode'], 10, 4);
    }

    /**
     * @param bool|string $valid
     * @param mixed       $value
     * @return bool|string  true when valid, otherwise an error message.
     */
    public static function validateDomainCode($valid, $value, $field, $input)
    {
        if ($valid !== true) {
            return $valid;
        }

        $code = is_string($value) ? trim($value) : '';

        if ($code === '') {
            return __('Domain code is required.', 'issac');
        }

        if (!preg_match('/^[1-5]$/', $code)) {
            return __('Domain code must be a single digit from 1 to 5.', 'issac');
        }

        if (self::codeInUse(PostTypes::DOMAIN, 'domain_code', $code)) {
            return sprintf(
                /* translators: %s: domain code */
                __('Domain code "%s" is already used by another domain.', 'issac'),
                $code
            );
        }

        return true;
    }

    /**
     * @param bool|string $valid
     * @param mixed       $value
     * @return bool|string  true when valid, otherwise an error message.
     */
    public static function validateItemCode($valid, $value, $field, $input)
    {
        if ($valid !== true) {
            return $valid;
        }

        $code = is_string($value) ? trim($value) : '';

        if ($code === '') {
            return __('Item code is required.', 'issac');
        }

        if (!preg_match('/^\d+\.\d+$/', $code)) {
            return __('Item code must look like "1.1" or "5.18" (digits, a dot, digits).', 'issac');
        }

        if (self::codeInUse(PostTypes::ITEM, 'item_code', $code)) {
            return sprintf(
                /* translators: %s: item code */
                __('Item code "%s" is already used by another item.', 'issac'),
                $code
            );
        }

        return true;
    }

    /**
     * Is $value already stored in $metaKey on another post of $postType?
     * The post currently being saved is excluded so re-saving it is allowed.
     */
    private static function codeInUse(string $postType, string $metaKey, string $value): bool
    {
        $exclude = self::currentPostId();

        $query = new \WP_Query([
            'post_type'              => $postType,
            'post_status'            => ['publish', 'pending', 'draft', 'future', 'private'],
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'post__not_in'           => $exclude > 0 ? [$exclude] : [],
            'meta_query'             => [
                [
                    'key'     => $metaKey,
                    'value'   => $value,
                    'compare' => '=',
                ],
            ],
        ]);

        return !empty($query->posts);
    }

    /**
     * Resolve the post being saved. ACF's AJAX validation posts `post_id`;
     * a classic (no-JS) save exposes `post_ID`.
     */
    private static function currentPostId(): int
    {
        foreach (['post_id', 'post_ID'] as $key) {
            if (isset($_POST[$key])) {
                return absint($_POST[$key]);
            }
        }

        return 0;
    }
}
