<?php

namespace Issac\Admin;

defined('ABSPATH') || exit;

/**
 * Renders the ISSAC Users admin page wrapping UsersListTable.
 */
final class UsersPage
{
    public static function render(): void
    {
        $table = new UsersListTable();
        $table->prepare_items();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('ISSAC Users', 'issac-assessment') . '</h1>';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="issac-users">';
        $table->search_box(__('Search users', 'issac-assessment'), 'issac-user-search');
        $table->display();
        echo '</form>';
        echo '</div>';
    }
}
