<?php

declare(strict_types=1);

namespace Issac\Frontend;

use Issac\Domain\AssessmentRepository;
use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;
use Issac\Install\Capabilities;

defined('ABSPATH') || exit;

final class Shortcodes
{
    public static function register(): void
    {
        add_shortcode('issac_domain', [self::class, 'renderDomain']);
    }

    public static function renderDomain(): string
    {
        if (!is_user_logged_in() || !current_user_can(Capabilities::TAKE_ASSESSMENT)) {
            $loginUrl = wp_login_url(get_permalink());
            return '<p class="issac-login-prompt">Please log in to take the assessment. '
                . '<a href="' . esc_url($loginUrl) . '">Log in</a></p>';
        }

        $domainCode = sanitize_text_field($_GET['d'] ?? '');
        $tree = InstrumentRepository::tree();

        $domain = null;
        foreach ($tree as $node) {
            if ($node->code === $domainCode) {
                $domain = $node;
                break;
            }
        }

        if ($domain === null) {
            return '<p class="issac-error">Invalid or missing domain. Please select a valid domain.</p>';
        }

        $assessment = AssessmentRepository::findOrCreate(get_current_user_id());
        $responses = ResponseRepository::forAssessment((int) $assessment->id);
        $summary = ScoringService::summary($tree, $responses);

        $domainSummary = null;
        foreach ($summary['domains'] as $row) {
            if ($row['code'] === $domain->code) {
                $domainSummary = $row;
                break;
            }
        }

        ob_start();
        include __DIR__ . '/templates/domain.php';
        return ob_get_clean();
    }
}
