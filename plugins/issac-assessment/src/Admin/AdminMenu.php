<?php

namespace Issac\Admin;

use Issac\Content\PostTypes;
use Issac\Install\Capabilities;

defined('ABSPATH') || exit;

/**
 * Registers admin submenu pages under the existing ISSAC menu.
 */
final class AdminMenu
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addSubmenus']);
        add_action('admin_post_issac_download_user_pdf', [UserDetailPage::class, 'downloadPdf']);
    }

    public static function addSubmenus(): void
    {
        // Overview — same slug as parent so it replaces the default landing page.
        add_submenu_page(
            PostTypes::MENU_SLUG,
            'ISSAC Overview',
            'Overview',
            Capabilities::VIEW_ADMIN,
            PostTypes::MENU_SLUG,
            [OverviewPage::class, 'render'],
        );

        // Users list
        add_submenu_page(
            PostTypes::MENU_SLUG,
            'ISSAC Users',
            'Users',
            Capabilities::VIEW_ADMIN,
            'issac-users',
            [UsersPage::class, 'render'],
        );

        // User Detail — hidden page (null parent)
        add_submenu_page(
            null,
            'User Assessment Detail',
            'User Detail',
            Capabilities::VIEW_ADMIN,
            'issac-user-detail',
            [UserDetailPage::class, 'render'],
        );
    }
}
