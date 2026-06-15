<?php
namespace Issac\Install;

defined('ABSPATH') || exit;

/**
 * Single source of truth for the plugin's roles and capabilities.
 *
 * WordPress never grants custom capabilities to administrators automatically,
 * so they must be added explicitly. Activation handles the first install, and
 * a version-guarded sync on admin_init self-heals sites where the plugin was
 * activated before (or without) these caps being applied.
 */
final class Capabilities
{
    /** Gates instrument content editing (the three CPTs + the ISSAC menu). */
    public const EDIT_INSTRUMENT = 'issac_edit_instrument';

    /** Gates the admin Overview/Users screens (added later milestones). */
    public const VIEW_ADMIN = 'issac_view_admin';

    /** Lets a user take the assessment. */
    public const TAKE_ASSESSMENT = 'issac_take_assessment';

    /** Bump when the cap set changes to force a re-sync on existing sites. */
    private const VERSION = '1';
    private const OPTION  = 'issac_caps_version';

    /** @return string[] Capabilities granted to administrators. */
    public static function adminCaps(): array
    {
        return [self::EDIT_INSTRUMENT, self::VIEW_ADMIN, self::TAKE_ASSESSMENT];
    }

    public static function register(): void
    {
        // Use `init` (not `admin_init`): the admin menu is built on `admin_menu`,
        // which fires *before* `admin_init`, so a later hook wouldn't reveal the
        // menu until a second page load.
        add_action('init', [self::class, 'maybeSync']);
    }

    /** Create roles and grant caps. Idempotent. */
    public static function install(): void
    {
        // Participant role for people who take the assessment.
        if (add_role('issac_participant', 'ISSAC Participant', [
            'read'                => true,
            self::TAKE_ASSESSMENT => true,
        ]) === null) {
            // Role already existed — make sure its cap is present.
            get_role('issac_participant')?->add_cap(self::TAKE_ASSESSMENT);
        }

        // Administrators manage the instrument and (later) the dashboards.
        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::adminCaps() as $cap) {
                $admin->add_cap($cap);
            }
        }

        update_option(self::OPTION, self::VERSION);
    }

    /** Runs on `init`; only writes when the stored version is stale. */
    public static function maybeSync(): void
    {
        if (get_option(self::OPTION) === self::VERSION) {
            return;
        }

        self::install();

        // The current user object may have already cached its capabilities for
        // this request; recompute them so the new caps take effect immediately
        // rather than only on the next page load.
        $user = wp_get_current_user();
        if ($user instanceof \WP_User && $user->exists()) {
            $user->get_role_caps();
        }
    }
}
