<?php
namespace Issac\Rest;

use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;

defined('ABSPATH') || exit;

/**
 * POST /responses — upserts a single item score and returns updated progress.
 */
final class ResponseEndpoint
{
    public static function register(): void
    {
        register_rest_route(RoutesController::NAMESPACE, '/responses', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'upsert'],
            'permission_callback' => [RoutesController::class, 'permissionCallback'],
            'args'                => [
                'item_code' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => fn ($v) => is_string($v) && $v !== '',
                ],
                'score' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => fn ($v) => is_numeric($v) && (int) $v >= 1 && (int) $v <= 5,
                ],
            ],
        ]);
    }

    public static function upsert(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $assessment = RoutesController::resolveAssessment();

        try {
            ResponseRepository::upsert(
                (int) $assessment->id,
                $request['item_code'],
                (int) $request['score']
            );
        } catch (\InvalidArgumentException $e) {
            return new \WP_Error(
                'invalid_response',
                $e->getMessage(),
                ['status' => 400]
            );
        }

        $tree      = InstrumentRepository::tree();
        $responses = ResponseRepository::forAssessment((int) $assessment->id);
        $summary   = ScoringService::summary($tree, $responses);

        return rest_ensure_response([
            'responses' => (object) $responses,
            'summary'   => $summary,
        ]);
    }
}
