<?php
namespace Issac\Rest;

use Issac\Domain\AssessmentRepository;

defined('ABSPATH') || exit;

/**
 * GET /assessment — returns (or lazily creates) the current user's assessment.
 * POST /complete  — marks the in_progress assessment as completed.
 */
final class AssessmentEndpoint
{
    public static function register(): void
    {
        register_rest_route(RoutesController::NAMESPACE, '/assessment', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get'],
            'permission_callback' => [RoutesController::class, 'permissionCallback'],
        ]);

        register_rest_route(RoutesController::NAMESPACE, '/complete', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'complete'],
            'permission_callback' => [RoutesController::class, 'permissionCallback'],
        ]);
    }

    public static function get(\WP_REST_Request $request): \WP_REST_Response
    {
        $assessment = RoutesController::resolveAssessment();

        return rest_ensure_response(RoutesController::buildPayload($assessment));
    }

    /**
     * Marks the current user's in_progress assessment as completed.
     *
     * Uses currentFor() (not findOrCreate) to avoid creating a new assessment
     * when one is already completed.
     */
    public static function complete(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $userId     = get_current_user_id();
        $assessment = AssessmentRepository::currentFor($userId);

        if ($assessment === null) {
            return new \WP_Error(
                'no_assessment',
                'No in-progress assessment found.',
                ['status' => 409]
            );
        }

        try {
            AssessmentRepository::complete((int) $assessment->id, $userId);
        } catch (\InvalidArgumentException $e) {
            return new \WP_Error(
                'already_completed',
                $e->getMessage(),
                ['status' => 409]
            );
        }

        $completed = AssessmentRepository::getById((int) $assessment->id);

        return rest_ensure_response(RoutesController::buildPayload($completed));
    }
}
