<?php
namespace Issac\Cli;

use Issac\Content\Importer;

defined('ABSPATH') || exit;

/**
 * WP-CLI command: wp issac import
 *
 * Loads data/instrument-2023.06.json into the instrument CPTs.
 * Idempotent — safe to re-run; only creates posts that don't already exist.
 */
final class ImportCommand
{
    /**
     * Import the ISSAC instrument from the bundled JSON file.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Show what would happen without writing to the database.
     *
     * [--update-text]
     * : Also refresh wording on already-existing posts (title, description,
     *   prompt, descriptors). Without this flag, matched posts keep their
     *   current text and only menu_order is updated.
     *
     * ## EXAMPLES
     *
     *     wp issac import
     *     wp issac import --dry-run
     *     wp issac import --update-text
     *
     * @when after_wp_load
     */
    public function import(array $args, array $assocArgs): void
    {
        $dryRun    = (bool) \WP_CLI\Utils\get_flag_value($assocArgs, 'dry-run', false);
        $updateText = (bool) \WP_CLI\Utils\get_flag_value($assocArgs, 'update-text', false);

        if ($dryRun) {
            \WP_CLI::log('--- DRY RUN (no changes will be written) ---');
        }

        if ($updateText) {
            \WP_CLI::log('Text updates enabled — matched posts will have their wording refreshed.');
        }

        try {
            $importer = new Importer(dryRun: $dryRun, updateText: $updateText);
            $tally    = $importer->run();
        } catch (\Throwable $e) {
            \WP_CLI::error($e->getMessage());
            return;
        }

        $this->report($tally, $dryRun);
    }

    private function report(array $tally, bool $dryRun): void
    {
        $prefix = $dryRun ? '[dry-run] ' : '';

        foreach (['domains', 'subsections', 'items'] as $type) {
            $c = $tally[$type]['created'];
            $m = $tally[$type]['matched'];
            $u = $tally[$type]['updated'];
            \WP_CLI::log(sprintf(
                '%s%s: %d created, %d matched, %d updated',
                $prefix,
                ucfirst($type),
                $c,
                $m,
                $u
            ));
        }

        $perDomain = $tally['per_domain'] ?? [];
        $totalDomains     = ($tally['domains']['created'] + $tally['domains']['matched']);
        $totalSubsections = ($tally['subsections']['created'] + $tally['subsections']['matched']);
        $totalItems       = ($tally['items']['created'] + $tally['items']['matched']);

        $breakdown = [];
        foreach ($perDomain as $code => $count) {
            $breakdown[] = "D{$code}:{$count}";
        }

        \WP_CLI::success(sprintf(
            '%s%d domains / %d subsections / %d items (%s)',
            $prefix,
            $totalDomains,
            $totalSubsections,
            $totalItems,
            implode(', ', $breakdown)
        ));
    }

    public static function register(): void
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        \WP_CLI::add_command('issac import', [new self(), 'import']);
    }
}
