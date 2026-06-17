<?php
namespace Issac\Domain;

defined('ABSPATH') || exit;

/**
 * Manages the wp_issac_events table.
 *
 * Events use INSERT IGNORE on the (assessment_id, event_key) unique key
 * to guarantee each milestone fires exactly once with no race conditions.
 */
final class EventRepository
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'issac_events';
    }

    /**
     * Records an event. Returns true if the event was newly inserted,
     * false if it already existed.
     */
    public static function record(int $assessmentId, string $eventKey): bool
    {
        global $wpdb;
        $assessmentId = absint($assessmentId);

        $now = gmdate('Y-m-d H:i:s');

        $result = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO %i (assessment_id, event_key, created_at)
             VALUES (%d, %s, %s)",
            self::table(),
            $assessmentId,
            $eventKey,
            $now,
        ));

        // $wpdb->query returns the number of affected rows.
        // INSERT IGNORE returns 1 for a new row, 0 for a duplicate.
        return $result === 1;
    }

    /**
     * Returns all event keys recorded for an assessment as a flat array.
     *
     * @return string[] e.g. ['domain_completed:1', 'halfway', …]
     */
    public static function firedForAssessment(int $assessmentId): array
    {
        global $wpdb;
        $assessmentId = absint($assessmentId);

        return $wpdb->get_col($wpdb->prepare(
            "SELECT event_key FROM %i WHERE assessment_id = %d",
            self::table(),
            $assessmentId,
        ));
    }
}
