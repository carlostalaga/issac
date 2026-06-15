<?php
namespace Issac\Domain;

use Issac\Content\PostTypes;

defined('ABSPATH') || exit;

/**
 * The single read path for instrument content.
 *
 * Builds the full Domain → Subsection → Item tree in one pass (one WP_Query per
 * CPT, each priming its own meta cache), returns plain PHP value objects, and
 * caches the assembled tree so no other code ever scatters get_field() calls.
 *
 * Totals/percentages must always be computed live from this tree — never
 * hard-coded. The reference counts (5 domains / 18 subsections / 70 items) are
 * for Milestone 2 import validation only.
 */
final class InstrumentRepository
{
    private const CACHE_KEY = 'issac_instrument_tree';

    /** Per-request memo so repeated calls within one request are free. */
    private static ?array $memo = null;

    public static function register(): void
    {
        foreach (PostTypes::all() as $postType) {
            add_action("save_post_{$postType}", [self::class, 'flush']);
        }

        add_action('acf/save_post', [self::class, 'flush'], 20);
        add_action('deleted_post', [self::class, 'flush']);
        add_action('trashed_post', [self::class, 'flush']);
        add_action('untrashed_post', [self::class, 'flush']);
    }

    /**
     * The full instrument tree.
     *
     * @return DomainNode[] Ordered by menu_order.
     */
    public static function tree(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return self::$memo = $cached;
        }

        $tree = self::build();
        set_transient(self::CACHE_KEY, $tree, DAY_IN_SECONDS);

        return self::$memo = $tree;
    }

    /** Drop both the persistent and in-request caches. */
    public static function flush(): void
    {
        self::$memo = null;
        delete_transient(self::CACHE_KEY);
    }

    /**
     * Assemble the tree from three queries. Each get_posts() primes the meta
     * cache for its result set, so the subsequent field reads hit cache only.
     *
     * @return DomainNode[]
     */
    private static function build(): array
    {
        $domainPosts     = self::fetch(PostTypes::DOMAIN);
        $subsectionPosts = self::fetch(PostTypes::SUBSECTION);
        $itemPosts       = self::fetch(PostTypes::ITEM);

        // Group items under their subsection, preserving menu_order.
        $itemsBySubsection = [];
        foreach ($itemPosts as $post) {
            $item = self::toItem($post);
            $itemsBySubsection[$item->subsectionId][] = $item;
        }

        // Group subsections under their domain, attaching their items.
        $subsectionsByDomain = [];
        foreach ($subsectionPosts as $post) {
            $domainId = (int) self::field($post->ID, 'domain');
            $subsectionsByDomain[$domainId][] = new SubsectionNode(
                id: (int) $post->ID,
                title: (string) $post->post_title,
                menuOrder: (int) $post->menu_order,
                domainId: $domainId,
                items: $itemsBySubsection[$post->ID] ?? []
            );
        }

        // Assemble domains.
        $tree = [];
        foreach ($domainPosts as $post) {
            $tree[] = new DomainNode(
                id: (int) $post->ID,
                code: (string) self::field($post->ID, 'domain_code'),
                title: (string) $post->post_title,
                description: (string) self::field($post->ID, 'description'),
                menuOrder: (int) $post->menu_order,
                subsections: $subsectionsByDomain[$post->ID] ?? []
            );
        }

        return $tree;
    }

    /**
     * @return \WP_Post[] Published posts of $postType ordered by menu_order.
     */
    private static function fetch(string $postType): array
    {
        return get_posts([
            'post_type'              => $postType,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'menu_order',
            'order'                  => 'ASC',
            'suppress_filters'       => false,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ]);
    }

    private static function toItem(\WP_Post $post): ItemNode
    {
        $rawActive = self::field($post->ID, 'is_active');
        $isActive  = ($rawActive === '' || $rawActive === null) ? true : (bool) $rawActive;

        return new ItemNode(
            id: (int) $post->ID,
            itemCode: (string) self::field($post->ID, 'item_code'),
            label: (string) $post->post_title,
            prompt: (string) self::field($post->ID, 'prompt'),
            descriptor1: (string) self::field($post->ID, 'descriptor_1'),
            descriptor3: (string) self::field($post->ID, 'descriptor_3'),
            descriptor5: (string) self::field($post->ID, 'descriptor_5'),
            isActive: $isActive,
            menuOrder: (int) $post->menu_order,
            subsectionId: (int) self::field($post->ID, 'subsection')
        );
    }

    /**
     * Read one ACF field, falling back to raw meta if ACF is unavailable.
     * This is the only place instrument fields are read directly.
     *
     * @return mixed
     */
    private static function field(int $postId, string $name)
    {
        if (function_exists('get_field')) {
            return get_field($name, $postId);
        }

        return get_post_meta($postId, $name, true);
    }
}
