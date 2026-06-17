<?php
namespace Issac\Rest;

use Issac\Domain\AssessmentRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;
use Issac\Install\Capabilities;

defined('ABSPATH') || exit;

/**
 * Registers all REST routes under the issac/v1 namespace and provides
 * shared helpers consumed by the endpoint classes.
 */
final class RoutesController
{
    public const NAMESPACE = 'issac/v1';

    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        AssessmentEndpoint::register();
        ResponseEndpoint::register();
        EventsEndpoint::register();
    }

    /**
     * Shared permission callback for all issac/v1 routes.
     *
     * Returns WP_Error with 401 for anonymous users, 403 for users lacking
     * the capability — never bare `false` (which always yields 403).
     */
    public static function permissionCallback(): bool|\WP_Error
    {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_not_logged_in',
                'Authentication required.',
                ['status' => 401]
            );
        }

        if (!current_user_can(Capabilities::TAKE_ASSESSMENT)) {
            return new \WP_Error(
                'rest_forbidden',
                'Insufficient permissions.',
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Returns the current user's in_progress assessment, creating one if needed.
     * Never accepts assessment_id or user_id from request input.
     */
    public static function resolveAssessment(): object
    {
        return AssessmentRepository::findOrCreate(get_current_user_id());
    }

    /**
     * Builds the standard API response payload for an assessment.
     */
    public static function buildPayload(object $assessment): array
    {
        $tree      = InstrumentRepository::tree();
        $responses = ResponseRepository::forAssessment((int) $assessment->id);
        $summary   = ScoringService::summary($tree, $responses);

        return [
            'assessment' => [
                'id'                 => (int) $assessment->id,
                'status'             => $assessment->status,
                'instrument_version' => $assessment->instrument_version,
                'started_at'         => self::formatDatetime($assessment->started_at),
                'updated_at'         => self::formatDatetime($assessment->updated_at),
                'completed_at'       => self::formatDatetime($assessment->completed_at),
            ],
            'responses' => (object) $responses,
            'summary'   => $summary,
        ];
    }

    /**
     * Converts DB datetime (UTC, Y-m-d H:i:s) to ISO 8601 with +00:00 suffix.
     */
    public static function formatDatetime(?string $dbDatetime): ?string
    {
        if ($dbDatetime === null || $dbDatetime === '') {
            return null;
        }

        return (new \DateTimeImmutable($dbDatetime, new \DateTimeZone('UTC')))
            ->format('c');
    }
}
