<?php
namespace Issac\Content;

defined('ABSPATH') || exit;

/**
 * Registers the three instrument CPTs and groups them under one "ISSAC"
 * admin menu.
 *
 * All three are PRIVATE (public => false, show_ui => true): they are editorial
 * content managed in wp-admin only, never exposed on the front end as queryable
 * URLs. Each supports revisions (a full edit history of every question) and
 * page-attributes (the menu_order field used for display ordering).
 *
 * Every capability is mapped to the single `issac_edit_instrument` cap, so a
 * non-admin team member can be granted instrument-editing rights without full
 * site admin (administrators already hold the cap, added on activation).
 */
final class PostTypes
{
    public const DOMAIN     = 'issac_domain';
    public const SUBSECTION = 'issac_subsection';
    public const ITEM       = 'issac_item';

    /** The single capability that gates all instrument editing. */
    private const CAP = 'issac_edit_instrument';

    /** Top-level admin menu slug the CPTs are nested under. */
    public const MENU_SLUG = 'issac';

    public static function register(): void
    {
        add_action('init', [self::class, 'registerPostTypes']);
        add_action('admin_menu', [self::class, 'registerMenu']);
    }

    /** @return string[] The three instrument post types. */
    public static function all(): array
    {
        return [self::DOMAIN, self::SUBSECTION, self::ITEM];
    }

    public static function registerPostTypes(): void
    {
        register_post_type(self::DOMAIN, self::args(
            single: 'Domain',
            plural: 'Domains',
            menuName: 'Domains'
        ));

        register_post_type(self::SUBSECTION, self::args(
            single: 'Subsection',
            plural: 'Subsections',
            menuName: 'Subsections'
        ));

        register_post_type(self::ITEM, self::args(
            single: 'Item',
            plural: 'Items',
            menuName: 'Items'
        ));
    }

    /**
     * Top-level "ISSAC" menu. The CPTs attach themselves to it via
     * show_in_menu, so this only needs to provide the parent landing page.
     */
    public static function registerMenu(): void
    {
        add_menu_page(
            'ISSAC',
            'ISSAC',
            self::CAP,
            self::MENU_SLUG,
            [self::class, 'renderLanding'],
            'dashicons-yes-alt',
            30
        );
    }

    public static function renderLanding(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('ISSAC', 'issac') . '</h1>';
        echo '<p>' . esc_html__(
            'Inclusive Schools Self-Assessment Checklist. Edit the instrument content using the Domains, Subsections and Items sections in this menu.',
            'issac'
        ) . '</p>';
        echo '</div>';
    }

    /**
     * Build the register_post_type() arguments shared by all three CPTs.
     */
    private static function args(string $single, string $plural, string $menuName): array
    {
        return [
            'labels'              => self::labels($single, $plural, $menuName),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => self::MENU_SLUG,
            'show_in_rest'        => false,
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'hierarchical'        => false,
            'supports'            => ['title', 'revisions', 'page-attributes'],
            'capability_type'     => self::CAP,
            'map_meta_cap'        => true,
            'capabilities'        => self::capabilities(),
        ];
    }

    /**
     * Map every PRIMITIVE capability to the single `issac_edit_instrument` cap.
     * Hold that one cap and you can fully manage instrument content; lack it and
     * the CPTs (and the ISSAC menu) are invisible.
     *
     * The meta caps `edit_post`/`read_post`/`delete_post` are deliberately NOT
     * listed. With map_meta_cap => true, WordPress derives them from the
     * primitives above based on ownership. Mapping a meta cap to the bare gate
     * string would register `issac_edit_instrument` itself as a meta capability,
     * which map_meta_cap then resolves to `do_not_allow` whenever it is checked
     * without a specific post (e.g. the admin menu) — silently hiding everything.
     */
    private static function capabilities(): array
    {
        $cap = self::CAP;

        return [
            'create_posts'           => $cap,
            'edit_posts'             => $cap,
            'edit_others_posts'      => $cap,
            'edit_private_posts'     => $cap,
            'edit_published_posts'   => $cap,
            'publish_posts'          => $cap,
            'read_private_posts'     => $cap,
            'delete_posts'           => $cap,
            'delete_private_posts'   => $cap,
            'delete_published_posts' => $cap,
            'delete_others_posts'    => $cap,
        ];
    }

    private static function labels(string $single, string $plural, string $menuName): array
    {
        return [
            'name'               => $plural,
            'singular_name'      => $single,
            'menu_name'          => $menuName,
            'add_new'            => __('Add New', 'issac'),
            'add_new_item'       => sprintf(__('Add New %s', 'issac'), $single),
            'edit_item'          => sprintf(__('Edit %s', 'issac'), $single),
            'new_item'           => sprintf(__('New %s', 'issac'), $single),
            'view_item'          => sprintf(__('View %s', 'issac'), $single),
            'search_items'       => sprintf(__('Search %s', 'issac'), $plural),
            'not_found'          => sprintf(__('No %s found', 'issac'), strtolower($plural)),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'issac'), strtolower($plural)),
            'all_items'          => $plural,
        ];
    }
}
