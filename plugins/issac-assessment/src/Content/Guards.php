<?php
namespace Issac\Content;

defined('ABSPATH') || exit;

/**
 * Protects instrument content that has recorded user responses.
 *
 * Trashing or deleting an issac_item, issac_subsection or issac_domain is
 * blocked when a row in wp_issac_responses references it — an item directly
 * (by item_code), or a domain/subsection via the item_codes of its descendants.
 *
 * Editors are steered toward the is_active toggle instead, so historical
 * responses are never orphaned.
 */
final class Guards
{
    public static function register(): void
    {
        add_filter('pre_trash_post', [self::class, 'blockIfReferenced'], 10, 2);
        add_filter('pre_delete_post', [self::class, 'blockIfReferenced'], 10, 2);
        add_action('admin_notices', [self::class, 'renderNotice']);
    }

    /**
     * Short-circuit wp_trash_post()/wp_delete_post() when the post (or its
     * descendants) has responses. Returning a non-null value aborts the action.
     *
     * @param  mixed    $check  Filter short-circuit value (null = proceed).
     * @param  \WP_Post $post   The post about to be trashed/deleted.
     * @return mixed            false to block, otherwise the original $check.
     */
    public static function blockIfReferenced($check, $post)
    {
        if (!($post instanceof \WP_Post) || !in_array($post->post_type, PostTypes::all(), true)) {
            return $check;
        }

        $codes = self::itemCodesFor($post);

        if ($codes !== [] && self::responsesExist($codes)) {
            self::flagNotice();
            return false;
        }

        return $check;
    }

    public static function renderNotice(): void
    {
        $key = self::noticeKey();
        if ($key === '' || !get_transient($key)) {
            return;
        }

        delete_transient($key);

        printf(
            '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
            esc_html__("This content has recorded responses — set 'Active' to off instead of deleting.", 'issac')
        );
    }

    /**
     * Collect every item_code reachable from $post.
     *
     * @return string[]
     */
    private static function itemCodesFor(\WP_Post $post): array
    {
        switch ($post->post_type) {
            case PostTypes::ITEM:
                $code = get_post_meta($post->ID, 'item_code', true);
                return is_string($code) && $code !== '' ? [$code] : [];

            case PostTypes::SUBSECTION:
                return self::codesForSubsections([$post->ID]);

            case PostTypes::DOMAIN:
                $subsectionIds = self::childIds(PostTypes::SUBSECTION, 'domain', $post->ID);
                return self::codesForSubsections($subsectionIds);
        }

        return [];
    }

    /**
     * @param  int[] $subsectionIds
     * @return string[]
     */
    private static function codesForSubsections(array $subsectionIds): array
    {
        $codes = [];

        foreach ($subsectionIds as $subsectionId) {
            $itemIds = self::childIds(PostTypes::ITEM, 'subsection', (int) $subsectionId);
            foreach ($itemIds as $itemId) {
                $code = get_post_meta($itemId, 'item_code', true);
                if (is_string($code) && $code !== '') {
                    $codes[] = $code;
                }
            }
        }

        return array_values(array_unique($codes));
    }

    /**
     * Post IDs of $postType whose $metaKey (a post_object reference) points at $parentId.
     *
     * @return int[]
     */
    private static function childIds(string $postType, string $metaKey, int $parentId): array
    {
        $query = new \WP_Query([
            'post_type'              => $postType,
            'post_status'            => ['publish', 'pending', 'draft', 'future', 'private'],
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query'             => [
                [
                    'key'     => $metaKey,
                    'value'   => $parentId,
                    'compare' => '=',
                ],
            ],
        ]);

        return array_map('intval', $query->posts);
    }

    /**
     * @param string[] $codes
     */
    private static function responsesExist(array $codes): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'issac_responses';

        // Avoid a SQL error if the responses table has not been created yet.
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($codes), '%s'));

        $sql = $wpdb->prepare(
            "SELECT 1 FROM {$table} WHERE item_code IN ({$placeholders}) LIMIT 1",
            $codes
        );

        return (bool) $wpdb->get_var($sql);
    }

    private static function flagNotice(): void
    {
        $key = self::noticeKey();
        if ($key !== '') {
            set_transient($key, 1, MINUTE_IN_SECONDS);
        }
    }

    private static function noticeKey(): string
    {
        $userId = get_current_user_id();
        return $userId > 0 ? 'issac_guard_notice_' . $userId : '';
    }
}
