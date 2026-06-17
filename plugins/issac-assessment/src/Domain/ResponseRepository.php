<?php
namespace Issac\Domain;

defined('ABSPATH') || exit;

/**
 * Manages the wp_issac_responses table.
 *
 * All writes use INSERT … ON DUPLICATE KEY UPDATE on the (assessment_id,
 * item_code) unique key — true upserts with no select-then-insert races.
 */
final class ResponseRepository
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'issac_responses';
    }

    private static function assessmentTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'issac_assessments';
    }

    /**
     * Upserts a score for the given item_code within an assessment.
     *
     * Validates the score (1–5) and that the item_code exists among active
     * items before writing. Also bumps assessments.updated_at.
     *
     * @throws \InvalidArgumentException On invalid score or unknown/inactive item_code.
     */
    public static function upsert(int $assessmentId, string $itemCode, int $score): void
    {
        global $wpdb;

        $assessmentId = absint($assessmentId);

        if ($score < 1 || $score > 5) {
            throw new \InvalidArgumentException(
                "Score must be 1–5, got {$score}."
            );
        }

        self::validateItemCode($itemCode);

        $now = gmdate('Y-m-d H:i:s');

        $wpdb->query($wpdb->prepare(
            "INSERT INTO %i (assessment_id, item_code, score, updated_at)
             VALUES (%d, %s, %d, %s)
             ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = VALUES(updated_at)",
            self::table(),
            $assessmentId,
            $itemCode,
            $score,
            $now,
        ));

        // Bump the assessment's updated_at.
        $wpdb->update(
            self::assessmentTable(),
            ['updated_at' => $now],
            ['id' => $assessmentId],
            ['%s'],
            ['%d'],
        );
    }

    /**
     * Returns all responses for an assessment as item_code => score.
     *
     * Includes responses to items that have since been deactivated — never
     * filters or deletes existing responses.
     */
    public static function forAssessment(int $assessmentId): array
    {
        global $wpdb;
        $assessmentId = absint($assessmentId);

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT item_code, score FROM %i WHERE assessment_id = %d",
            self::table(),
            $assessmentId,
        ));

        $responses = [];
        foreach ($rows as $row) {
            $responses[$row->item_code] = (int) $row->score;
        }

        return $responses;
    }

    /**
     * Validates that the item_code exists among active items.
     *
     * @throws \InvalidArgumentException If the item_code is unknown or inactive.
     */
    private static function validateItemCode(string $itemCode): void
    {
        $tree = InstrumentRepository::tree();

        foreach ($tree as $domain) {
            foreach ($domain->subsections as $subsection) {
                foreach ($subsection->items as $item) {
                    if ($item->itemCode === $itemCode && $item->isActive) {
                        return;
                    }
                }
            }
        }

        throw new \InvalidArgumentException(
            "Item code '{$itemCode}' does not exist or is not active."
        );
    }
}
