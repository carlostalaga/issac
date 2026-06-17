<?php
namespace Issac;

use Issac\Cli\ImportCommand;
use Issac\Content\Guards;
use Issac\Content\PostTypes;
use Issac\Content\Validation;
use Issac\Domain\InstrumentRepository;
use Issac\Install\Capabilities;
use Issac\Rest\RoutesController;

defined('ABSPATH') || exit;

/**
 * Central wiring point: registers all hooks for the plugin.
 */
final class Plugin
{
    public static function boot(): void
    {
        self::registerAcfJsonPaths();

        Capabilities::register();
        PostTypes::register();
        Validation::register();
        Guards::register();
        InstrumentRepository::register();
        ImportCommand::register();
        RoutesController::register();
    }

    /**
     * Point ACF Local JSON at the plugin's acf-json/ folder so field groups
     * load from (and save to) Git-versioned files rather than the active theme.
     */
    private static function registerAcfJsonPaths(): void
    {
        $dir = rtrim(ISSAC_PATH, '/\\') . '/acf-json';

        add_filter('acf/settings/save_json', static fn (): string => $dir);

        add_filter('acf/settings/load_json', static function (array $paths) use ($dir): array {
            $paths[] = $dir;
            return $paths;
        });
    }
}
