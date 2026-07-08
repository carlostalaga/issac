<?php

declare(strict_types=1);

namespace Issac\Rest;

use Issac\Domain\ResponseRepository;
use Issac\Pdf\ReportGenerator;

defined('ABSPATH') || exit;

/**
 * GET /report — streams the current user's assessment PDF.
 */
final class ReportEndpoint
{
    public static function register(): void
    {
        register_rest_route(RoutesController::NAMESPACE, '/report', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'download'],
            'permission_callback' => [RoutesController::class, 'permissionCallback'],
        ]);
    }

    /**
     * @return null mPDF Output() sends headers and exits; this callback never returns on success.
     */
    public static function download(\WP_REST_Request $request): \WP_REST_Response|\WP_Error|null
    {
        $assessment = RoutesController::resolveAssessment();
        $responses  = ResponseRepository::forAssessment((int) $assessment->id);

        if ($responses === []) {
            return new \WP_Error(
                'no_responses',
                'No responses recorded yet. Answer at least one item before downloading a report.',
                ['status' => 400]
            );
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        ReportGenerator::generate($assessment, 'stream');

        return null;
    }
}
