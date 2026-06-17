<?php
namespace Issac\Rest;

use Issac\Domain\EventRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;

defined('ABSPATH') || exit;

/**
 * POST /events/check — evaluates milestone conditions, records new events,
 * returns toast labels for first-time milestones only.
 */
final class EventsEndpoint
{
    private const MILESTONES = [
        'halfway'      => 'Halfway there',
        'all_completed' => 'All items reviewed',
    ];

    public static function register(): void
    {
        register_rest_route(RoutesController::NAMESPACE, '/events/check', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'check'],
            'permission_callback' => [RoutesController::class, 'permissionCallback'],
            'args'                => [
                'domain_code' => [
                    'required' => false,
                    'type'     => 'string',
                ],
            ],
        ]);
    }

    public static function check(\WP_REST_Request $request): \WP_REST_Response
    {
        $assessment = RoutesController::resolveAssessment();
        $tree       = InstrumentRepository::tree();
        $responses  = ResponseRepository::forAssessment((int) $assessment->id);
        $summary    = ScoringService::summary($tree, $responses);

        $newEvents = [];

        // 1. Check each domain for 100% completion.
        foreach ($summary['domains'] as $domain) {
            if ($domain['completion'] === 100.0) {
                $key = "domain_completed:{$domain['code']}";
                if (EventRepository::record((int) $assessment->id, $key)) {
                    $newEvents[] = ['key' => $key, 'toast' => 'Domain completed'];
                }
            }
        }

        // 2. Overall completion >= 50%.
        if ($summary['overall']['completion'] >= 50.0) {
            $key = 'halfway';
            if (EventRepository::record((int) $assessment->id, $key)) {
                $newEvents[] = ['key' => $key, 'toast' => self::MILESTONES[$key]];
            }
        }

        // 3. Overall completion = 100%.
        if ($summary['overall']['completion'] === 100.0) {
            $key = 'all_completed';
            if (EventRepository::record((int) $assessment->id, $key)) {
                $newEvents[] = ['key' => $key, 'toast' => self::MILESTONES[$key]];
            }
        }

        return rest_ensure_response(['new_events' => $newEvents]);
    }
}
