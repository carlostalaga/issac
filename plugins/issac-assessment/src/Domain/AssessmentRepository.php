<?php
namespace Issac\Domain;

defined('ABSPATH') || exit;

/**
 * Manages the wp_issac_assessments table.
 *
 * All dates are stored in UTC. The caller (REST layer) is responsible for
 * passing the authenticated user ID via get_current_user_id() — this class
 * never resolves the user itself.
 */
final class AssessmentRepository
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'issac_assessments';
    }

    private static function utcNow(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * Returns the single in_progress assessment for a user, or null.
     */
    public static function currentFor(int $userId): ?object
    {
        global $wpdb;
        $userId = absint($userId);

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE user_id = %d AND status = 'in_progress' LIMIT 1",
            self::table(),
            $userId,
        ));

        return $row ?: null;
    }

    /**
     * Returns the existing in_progress assessment or atomically creates one.
     *
     * Uses INSERT … SELECT WHERE NOT EXISTS to guard against concurrent
     * requests creating duplicate rows. If a race causes the INSERT to be
     * a no-op (row already exists), we simply re-query.
     */
    public static function findOrCreate(int $userId): object
    {
        global $wpdb;
        $userId = absint($userId);

        $existing = self::currentFor($userId);
        if ($existing !== null) {
            return $existing;
        }

        $table = self::table();
        $now   = self::utcNow();

        // Atomic check-and-insert: only inserts if no in_progress row exists.
        $wpdb->query($wpdb->prepare(
            "INSERT INTO %i (user_id, instrument_version, status, started_at, updated_at)
             SELECT %d, %s, 'in_progress', %s, %s
             FROM DUAL
             WHERE NOT EXISTS (
                 SELECT 1 FROM %i WHERE user_id = %d AND status = 'in_progress'
             )",
            $table,
            $userId,
            '2023.06',
            $now,
            $now,
            $table,
            $userId,
        ));

        // Whether we just inserted or a concurrent request beat us, the row exists now.
        $row = self::currentFor($userId);
        if ($row === null) {
            throw new \RuntimeException("Failed to create assessment for user {$userId}.");
        }

        return $row;
    }

    /**
     * Marks an assessment as completed.
     *
     * @throws \InvalidArgumentException If the assessment doesn't exist or doesn't belong to the user.
     */
    public static function complete(int $assessmentId, int $userId): void
    {
        global $wpdb;
        $assessmentId = absint($assessmentId);
        $userId       = absint($userId);

        $updated = $wpdb->update(
            self::table(),
            [
                'status'       => 'completed',
                'completed_at' => self::utcNow(),
                'updated_at'   => self::utcNow(),
            ],
            [
                'id'      => $assessmentId,
                'user_id' => $userId,
                'status'  => 'in_progress',
            ],
            ['%s', '%s', '%s'],
            ['%d', '%d', '%s'],
        );

        if ($updated === 0) {
            throw new \InvalidArgumentException(
                "No in-progress assessment #{$assessmentId} found for user #{$userId}."
            );
        }
    }

    /**
     * Fetch a single assessment by ID.
     */
    public static function getById(int $id): ?object
    {
        global $wpdb;
        $id = absint($id);

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            self::table(),
            $id,
        ));

        return $row ?: null;
    }
}
