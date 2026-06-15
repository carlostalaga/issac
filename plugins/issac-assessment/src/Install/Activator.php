<?php
namespace Issac\Install;

defined('ABSPATH') || exit;

/**
 * Plugin activation handler.
 *
 * Creates the three custom tables (assessments, responses, events) via
 * dbDelta and delegates role/capability creation to Capabilities.
 */
class Activator
{
    /** Bump this string whenever the schema changes (the Migrator will use it later). */
    public const DB_VERSION = '1.0.0';

    public static function activate(): void
    {
        self::createTables();
        Capabilities::install();
        update_option('issac_db_version', self::DB_VERSION);
    }

    private static function createTables(): void
    {
        global $wpdb;

        // dbDelta() lives in this file, which isn't loaded by default.
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $prefix  = $wpdb->prefix;            // usually "wp_"
        $charset = $wpdb->get_charset_collate();

        $assessments = "CREATE TABLE {$prefix}issac_assessments (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            instrument_version varchar(20) NOT NULL DEFAULT '2023.06',
            status varchar(20) NOT NULL DEFAULT 'in_progress',
            started_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            completed_at datetime NULL,
            team_id bigint unsigned NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) {$charset};";

        $responses = "CREATE TABLE {$prefix}issac_responses (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            assessment_id bigint unsigned NOT NULL,
            item_code varchar(10) NOT NULL,
            score tinyint unsigned NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY assessment_item (assessment_id,item_code),
            KEY item_code (item_code)
        ) {$charset};";

        $events = "CREATE TABLE {$prefix}issac_events (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            assessment_id bigint unsigned NOT NULL,
            event_key varchar(50) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY assessment_event (assessment_id,event_key)
        ) {$charset};";

        dbDelta($assessments);
        dbDelta($responses);
        dbDelta($events);
    }
}