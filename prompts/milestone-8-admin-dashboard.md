# Milestone 8 — Admin Dashboard

Read `@ISSAC-development-plan.md` and `@.cursor/rules/issac.mdc` first, then:

Implement Milestone 8 from `ISSAC-development-plan.md`, following `.cursor/rules/issac.mdc`.

> This milestone builds the **wp-admin dashboard** for site administrators and
> ISSAC managers. It adds three new admin pages under the existing ISSAC menu:
> an Overview page with headline stats, a Users list table with sortable
> columns, and a User Detail page with a read-only view of an individual's
> assessment and a "Download their PDF" button.
> See the plan §9, §4 (Roles & capabilities), and the Milestone 8 row in §11.

---

## Context — what is already built (Milestones 0–7)

Do **not** re-derive or rebuild any of this. It is complete and verified.

**Plugin bootstrap (`issac-assessment.php`):**
- `ISSAC_VERSION = '0.2.0'`, `ISSAC_PATH`, `ISSAC_URL`.
- Composer autoload loaded; mPDF is a dependency.

**Content model (M1–M2):**
- Three CPTs: `issac_domain`, `issac_subsection`, `issac_item` — private, grouped
  under the ISSAC admin menu.
- `PostTypes::MENU_SLUG = 'issac'` — the top-level admin menu, gated by
  `issac_edit_instrument`. CPTs attach via `show_in_menu => 'issac'`.
- `PostTypes::registerMenu()` creates the parent menu page on `admin_menu`.

**Capabilities (M0–M1, `Install\Capabilities`):**
- `Capabilities::EDIT_INSTRUMENT = 'issac_edit_instrument'` — gates CPTs + parent menu.
- `Capabilities::VIEW_ADMIN = 'issac_view_admin'` — gates the Overview/Users screens
  (exists as a constant and is granted to `administrator`, but no screen uses it yet).
- `Capabilities::TAKE_ASSESSMENT = 'issac_take_assessment'`.
- Role `issac_participant` — caps: `read`, `issac_take_assessment`.
- Role `issac_manager` — **defined in the plan but NOT yet created.** This milestone
  must add it in `Capabilities::install()` alongside the participant role. Caps:
  `read`, `issac_take_assessment`, `issac_edit_instrument`, `issac_view_admin`.
  Bump `Capabilities::VERSION` to trigger a re-sync.
- `adminCaps()` returns the three caps granted to `administrator`.

**Data layer (M3–M4):**
- `AssessmentRepository::findOrCreate(int $userId): object` — lazily creates an
  `in_progress` row.
- `AssessmentRepository::currentFor(int $userId): ?object` — returns the in_progress
  assessment, or null.
- `AssessmentRepository::getById(int $id): ?object`.
- `ResponseRepository::forAssessment(int $assessmentId): array` → `['1.1'=>3, …]`.
- `EventRepository::firedForAssessment(int $assessmentId): string[]`.
- `EventRepository::lastFired(int $assessmentId, string $eventKey): ?string`.
- `ScoringService::summary(array $tree, array $responses): array` — the full scoring
  breakdown used by dashboard, domain pages, and PDF.
- `InstrumentRepository::tree(): DomainNode[]`.

**Custom tables (M0):**
- `{prefix}issac_assessments` — `id, user_id, instrument_version, status,
  started_at, updated_at, completed_at, team_id`.
- `{prefix}issac_responses` — `id, assessment_id, item_code, score, updated_at`.
  FK to assessments with ON DELETE CASCADE.
- `{prefix}issac_events` — `id, assessment_id, event_key, created_at`.
  Unique key on `(assessment_id, event_key)`.

**PDF report (M7):**
- `ReportGenerator::generate(object $assessment, string $mode = 'stream'): ?string`
  — accepts an `$assessment` row object (not a user ID). In `'stream'` mode it
  records the `pdf_generated` event and calls `exit`. In `'save'` mode it writes
  the PDF to disk, records the event, and returns the file path. For the admin
  "Download their PDF" button, use `'save'` mode and stream the resulting file
  manually — this avoids recording a `pdf_generated` event on the participant's
  assessment (see Deliverable 6 for details).

**Plugin wiring (`Plugin.php`):**
- `Plugin::boot()` calls: `Capabilities::register()`, `PostTypes::register()`,
  `Validation::register()`, `Guards::register()`, `InstrumentRepository::register()`,
  `ImportCommand::register()`, `RoutesController::register()`, `Shortcodes::register()`,
  `Assets::register()`.

**Existing admin menu structure:**
```
ISSAC                  ← PostTypes::registerMenu(), cap: issac_edit_instrument
├── Domains            ← issac_domain CPT (show_in_menu => 'issac')
├── Subsections        ← issac_subsection CPT
└── Items              ← issac_item CPT
```

### Manual prerequisite (human, not code)

- **No CodeKit project is active for this repo.** If any SCSS changes are needed,
  compile with the `sass` CLI and commit the outputs.

---

## Deliverables

### 1. Add the `issac_manager` role — `src/Install/Capabilities.php`

The plan §4 defines this role. Add it in `install()` alongside the participant role:

```php
if (add_role('issac_manager', 'ISSAC Manager', [
    'read'                         => true,
    self::TAKE_ASSESSMENT          => true,
    self::EDIT_INSTRUMENT          => true,
    self::VIEW_ADMIN               => true,
]) === null) {
    $role = get_role('issac_manager');
    if ($role) {
        foreach ([self::TAKE_ASSESSMENT, self::EDIT_INSTRUMENT, self::VIEW_ADMIN] as $cap) {
            $role->add_cap($cap);
        }
    }
}
```

Bump `VERSION` from `'1'` to `'2'` to trigger the `maybeSync()` re-sync on existing
sites. This ensures the role and its caps are applied on the next page load without
requiring plugin re-activation.

### 2. `src/Admin/AdminMenu.php`

Registers the **Overview** and **Users** submenu pages under the existing ISSAC menu.

```php
namespace Issac\Admin;

final class AdminMenu
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addSubmenus']);
    }

    public static function addSubmenus(): void
    {
        // Overview — uses the SAME slug as the parent so it replaces the
        // default landing page. Clicking the top-level "ISSAC" menu item
        // will render OverviewPage instead of PostTypes::renderLanding().
        add_submenu_page(
            PostTypes::MENU_SLUG,     // parent slug
            'ISSAC Overview',         // page title
            'Overview',               // menu title
            Capabilities::VIEW_ADMIN, // capability
            PostTypes::MENU_SLUG,     // menu slug = parent slug → replaces landing
            [OverviewPage::class, 'render'],
        );

        // Users
        add_submenu_page(
            PostTypes::MENU_SLUG,
            'ISSAC Users',
            'Users',
            Capabilities::VIEW_ADMIN,
            'issac-users',
            [UsersPage::class, 'render'],
        );
    }
}
```

**Important menu ordering:** The CPTs register at priority 10 on `admin_menu`.
`AdminMenu::addSubmenus()` should also run on `admin_menu` — WordPress auto-sorts
submenus, so the Overview and Users items will appear in the ISSAC menu alongside
the CPT entries. The resulting menu should be:

```
ISSAC
├── Overview            ← new (issac_view_admin)
├── Users               ← new (issac_view_admin)
├── Domains             ← existing CPT
├── Subsections         ← existing CPT
└── Items               ← existing CPT
```

The Overview submenu uses `PostTypes::MENU_SLUG` (`'issac'`) as its own slug. In
WordPress, when a submenu page shares the parent's slug it replaces the parent's
callback — so clicking the top-level "ISSAC" menu item renders `OverviewPage`,
not the old `PostTypes::renderLanding()`. The landing method can stay in
`PostTypes` (dead code, harmless) — do NOT delete or move it.

### 3. `src/Admin/OverviewPage.php`

A single wp-admin page showing headline stats and a per-domain breakdown. All data
comes from aggregate SQL on the custom tables — this is exactly why responses live
in custom tables rather than postmeta.

**Headline counters (top of page):**

| Counter | Query |
|---|---|
| Total participants | `COUNT(DISTINCT user_id)` from `issac_assessments` |
| In progress | `COUNT(*)` from `issac_assessments` where `status = 'in_progress'` |
| Completed | `COUNT(*)` from `issac_assessments` where `status = 'completed'` |
| PDFs generated | `COUNT(*)` from `issac_events` where `event_key = 'pdf_generated'` |

Display these as four simple stat cards in a row (use standard wp-admin markup with
inline styles or a small `<style>` block — no external CSS file needed for admin
pages).

**Per-domain stats table:**

| Domain | Avg completion % | Avg score | Participants answered |
|---|---|---|---|
| 1. Inclusive Site Culture | 42.3% | 3.1 | 12 |

For each domain in tree order:
- **Participants answered** — distinct `assessment_id` count that has at least one
  response in the domain's item codes. This is the denominator for the next metric.
- **Avg completion %** — averaged only over assessments that have at least one
  response in the domain (i.e. the "Participants answered" set). For each such
  assessment, compute `answered / total_active_items` for the domain's item codes,
  then average those fractions. Assessments with zero answers in the domain are
  excluded — they haven't started the domain and would deflate the metric.
- **Avg score** across all responses for that domain's item codes (a simple
  `AVG(score)` — no per-assessment grouping needed).

Use `InstrumentRepository::tree()` to get the domain metadata (title, item codes).
Write the aggregate queries as raw SQL via `$wpdb->prepare()`. The queries should
be efficient — a handful of aggregate SELECTs, not N+1 loops.

**Implementation approach:** Create a private static method or helper (e.g.
`OverviewPage::gatherStats()`) that returns a structured array, then pass it to
a template or render inline. Keep the SQL in the page class — no new repository
methods are needed unless a query is reused elsewhere.

### 4. `src/Admin/UsersListTable.php`

Extends `WP_List_Table` to show all users who have an assessment.

**Columns:**

| Column | Source | Sortable |
|---|---|---|
| User | `display_name` (linked to User Detail) | Yes |
| Started | `assessment.started_at` | Yes |
| Last activity | `assessment.updated_at` | Yes |
| Overall % | Computed from responses + tree | Yes |
| Domain 1–5 (five mini columns) | Per-domain completion % | No |
| Status | `assessment.status` | Yes |

**Data source:** A single query joining `issac_assessments` with `wp_users`,
optionally with `COUNT`/aggregates from `issac_responses`. Since overall % and
per-domain % need the instrument tree, one approach is:

1. Query all assessments (paginated, sorted) with user data.
2. For each assessment on the current page, load responses and compute via
   `ScoringService::summary()`. With 20 rows per page and the cached tree,
   this is fast enough.
3. Alternatively, for the sortable "Overall %" column, compute it in SQL as
   `(COUNT(responses) / total_active_items) * 100` — this allows server-side
   sorting without loading all summaries.

**Choose the simpler approach** — pre-computing per-page is acceptable for Stage 1.
If sorting by Overall % requires SQL, add the computation there.

**Search:** Filter by user `display_name` or `user_email` (use a `LIKE` clause).

**Pagination:** Standard `WP_List_Table` pagination, 20 items per page.

**Row actions:** Each user row should link to the User Detail page:
`admin.php?page=issac-user-detail&assessment_id={id}`.

### 5. `src/Admin/UsersPage.php`

A thin wrapper that instantiates `UsersListTable`, calls `prepare_items()`, and
renders the table inside a standard `.wrap` admin container with a title and
search box.

```php
public static function render(): void
{
    $table = new UsersListTable();
    $table->prepare_items();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('ISSAC Users', 'issac-assessment') . '</h1>';
    $table->search_box(__('Search users', 'issac-assessment'), 'issac-user-search');
    $table->display();
    echo '</div>';
}
```

### 6. `src/Admin/UserDetailPage.php`

A read-only view of a single user's assessment, showing the same data as the
participant dashboard and PDF — plus a "Download their PDF" button.

**Registration:** This page is a **hidden submenu** (null parent) so it has a URL
but doesn't appear in the sidebar:

```php
add_submenu_page(
    null,                            // no parent — hidden page
    'User Assessment Detail',
    'User Detail',
    Capabilities::VIEW_ADMIN,
    'issac-user-detail',
    [UserDetailPage::class, 'render'],
);
```

Register this in `AdminMenu::addSubmenus()`.

**Input:** `$_GET['assessment_id']` (validated with `absint()`). If missing or
invalid, show an error notice and a back link.

**Content:**

1. **User header:** Display name, email, assessment status, started date, last
   activity date, instrument version.

2. **Overall summary:** Completion %, overall average, overall band — mirroring
   the PDF cover. Use `ScoringService::summary()`.

3. **Per-domain table:** Same layout as the PDF summary table — domain title,
   items answered, average, band. Include progress bars (simple `<div>` or use
   wp-admin's native progress styling).

4. **"Download their PDF" button:** An `<a>` styled as a WP admin button
   (`button button-primary`) linking to a URL that triggers PDF download for
   this specific user's assessment.

   **Implementation for the download action:** Because the existing `GET /report`
   REST endpoint resolves the assessment from `get_current_user_id()` (which would
   be the admin, not the target user), you need a separate mechanism. Options:

   - **Option A (recommended): `admin-post.php` action.** Register a handler on
     the `admin_post_issac_download_user_pdf` hook that accepts `assessment_id`
     as a query param, verifies the nonce and that the current user has
     `issac_view_admin`, verifies the assessment exists via
     `AssessmentRepository::getById()`, clears output buffers, and generates
     the PDF **without recording the `pdf_generated` event** (see note below).
     The button links to
     `wp_nonce_url(admin_url('admin-post.php?action=issac_download_user_pdf&assessment_id=…'), 'issac_download_pdf_{id}')`.

   - **Option B: a new REST endpoint** `GET /report/{assessment_id}` with an
     `issac_view_admin` permission callback.

   Choose Option A unless you have a reason to prefer B — it's simpler and
   doesn't require changing the existing REST auth model.

   **Important — do NOT record the `pdf_generated` event.** The existing
   `ReportGenerator::generate($assessment, 'stream')` always records the
   `pdf_generated` event on the participant's assessment and then calls
   `exit`. An admin downloading someone else's PDF should not touch the
   participant's event history (it would update their "Last report" date on
   their own dashboard, which is confusing). Instead, use `'save'` mode to
   generate the PDF to a temp file, then stream that file to the admin with
   manual headers + `readfile()` + `unlink()`. This bypasses the event
   recording entirely without modifying `ReportGenerator`.

5. **Back link:** "← Back to Users" linking to `admin.php?page=issac-users`.

**Security:**
- `current_user_can(Capabilities::VIEW_ADMIN)` check at the top of `render()`.
- `assessment_id` via `absint()`.
- The admin can view ANY user's assessment (read-only) — this is by design
  (auditing and oversight). They cannot edit responses.
- The PDF download action must verify the nonce and capability.

### 7. Wire into `Plugin.php`

Add `AdminMenu::register()` to `Plugin::boot()`:

```php
use Issac\Admin\AdminMenu;
// ...
AdminMenu::register();
```

### 8. Tests

Follow the existing convention — **do NOT scaffold a WP PHPUnit harness.**

**Smoke test:** create `tests/admin-smoke.php`, runnable via
`wp eval-file tests/admin-smoke.php --user=1`, mirroring the existing smoke tests:

1. Verify the `issac_manager` role exists and has the expected capabilities
   (`read`, `issac_take_assessment`, `issac_edit_instrument`, `issac_view_admin`).
2. Verify an `administrator` user has `issac_view_admin`.
3. Verify the admin submenu pages are registered:
   - The global `$submenu` array contains entries under `'issac'` with slugs
     `issac-overview` and `issac-users`.
4. Create a test assessment with a few responses, then:
   - Render the Overview page and assert the output contains the stat counter
     markup and the per-domain table.
   - Instantiate `UsersListTable`, call `prepare_items()`, assert `$items` is
     non-empty and contains the test user.
5. Test the User Detail page: simulate `$_GET['assessment_id']` and render —
   assert the output contains the user's display name and the "Download" button.
6. Access gate: verify that a user WITHOUT `issac_view_admin` cannot see the
   admin pages (the submenu items should not be registered for them, and direct
   page access should fail the capability check).
7. Clean up: delete test responses, assessment; restore user state.

---

## Conventions (from `.cursor/rules/issac.mdc`)

- PHP 8.1+, PSR-12, namespace `Issac\Admin`.
- Instrument content ONLY via `InstrumentRepository::tree()`.
- Scoring from `ScoringService` — **never computed in the admin templates**.
- Escape ALL output: `esc_html`, `esc_attr`. The admin pages are server-rendered
  HTML in wp-admin — use standard WP admin markup patterns.
- All SQL through `$wpdb->prepare()`; cast IDs with `absint()`.
- Resolve the *viewing* user from `current_user_can()` for permission checks.
  The *target* user's assessment is resolved from the URL parameter, not the session.
- Minimalistic, readable code.

---

## Manual Verification Checklist

1. **Menu structure:** log in as administrator → the ISSAC menu should show
   Overview, Users, Domains, Subsections, Items (in that order or close to it).
2. **Overview page:** shows four stat counters (participants, in progress, completed,
   PDFs generated) and a per-domain stats table with correct averages.
3. **Overview with no data:** fresh install with no assessments → counters all 0,
   domain table shows 0% / 0 participants.
4. **Users page:** shows a paginated table of all users with assessments. Columns
   display correctly, search filters by name/email.
5. **Users sorting:** click column headers (User, Started, Last activity, Overall %,
   Status) → table re-sorts correctly.
6. **User Detail:** click a user row → shows their assessment summary with correct
   scores and progress bars. Shows their name, status, dates.
7. **Download their PDF:** click the download button on User Detail → downloads a
   PDF for that specific user (not the admin's own assessment). Verify the PDF
   contains the target user's name and scores. Also verify: the admin download
   does NOT update the participant's "Last report" date on their own dashboard
   (check `wp_issac_events` — no new `pdf_generated` row should appear from the
   admin action).
8. **issac_manager role:** create a user with the `issac_manager` role → they can
   see the ISSAC menu, access Overview/Users, edit instrument content, AND take
   the assessment. They should NOT see Plugins, Themes, Users (WP), or Settings.
9. **Access gate — subscriber:** log in as a plain subscriber → the ISSAC admin
   menu should not appear. Directly navigating to `admin.php?page=issac-overview`
   should show a permission error.
10. **Access gate — participant:** log in as `issac_participant` → the ISSAC admin
    menu should not appear (they only have `issac_take_assessment`, not
    `issac_view_admin` or `issac_edit_instrument`).
11. **Empty state on User Detail:** navigate to User Detail with an invalid
    `assessment_id` → should show a clear error message, not a PHP fatal.

---

## What This Milestone Does NOT Include

- **Editing user responses** from admin — Stage 1 is auditing/oversight only.
- **Admin charts or CSV export** — Stage 2 (add Chart.js + CSV streaming endpoint).
- **User creation/management** — uses native WP user management; the admin assigns
  the `issac_participant` or `issac_manager` role through the standard WP Users screen.
- **Hardening/polish** — accessibility pass, mobile admin, edge states (M9).
- **No frontend (participant-facing) changes** — this milestone is entirely wp-admin.
- **No new REST endpoints** unless Option B is chosen for the admin PDF download.
  The existing `GET /report` endpoint remains unchanged (it serves the current user's
  own PDF from the participant dashboard).

---

## Notes

- The plan says `AdminMenu.php` registers the top-level ISSAC menu, but M1 already
  implemented this in `PostTypes::registerMenu()`. `AdminMenu.php` should only add
  the **submenu pages** (Overview, Users, hidden User Detail) — do NOT move or
  duplicate the parent menu registration.
- The plan's file tree lists `UsersListTable.php` and `UserDetailPage.php` under
  `Admin/`. `OverviewPage.php` is implied by the "Overview" screen described in §9
  but not explicitly listed — add it.
- The per-domain stats on the Overview page require aggregate SQL across all
  assessments. The domain's item codes come from `InstrumentRepository::tree()`.
  Build a flat list of item codes per domain from the tree, then query aggregate
  stats filtered by those codes.
- `WP_List_Table` requires loading manually in plugins:
  `if (!class_exists('WP_List_Table')) { require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php'; }`
- The `issac_manager` role is new to this milestone. It is distinct from
  `administrator` in that it has NO access to plugin/theme/user management — only
  ISSAC-specific capabilities plus `read`. This makes it safe to assign to school
  coordinators who should manage the instrument and view results without full site
  admin.
