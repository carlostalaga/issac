# Milestone 8 — Admin Dashboard — Completion Report

**Milestone:** 8 (Admin Dashboard)
**Spec:** `prompts/milestone-8-admin-dashboard.md`
**Contract:** `ISSAC-development-plan.md` §4, §9, §11
**Status:** Complete — smoke test ready, autoloader fix applied
**Date:** 2026-07-08

---

## 1. Summary

Milestone 8 delivers the **wp-admin dashboard** for site administrators and
ISSAC managers. Three admin pages are added under the existing ISSAC menu:
an Overview page with headline stats and per-domain aggregates, a Users list
table with sortable columns and search, and a User Detail page with a read-only
view of an individual's assessment plus an admin PDF download that does not
touch the participant's event history.

The implementation adds 6 new files, modifies 3 existing files, and introduces
the `issac_manager` role.

---

## 2. Deliverables

| # | Deliverable | File | Status |
|---|---|---|---|
| 1 | `issac_manager` role + caps VERSION bump | `src/Install/Capabilities.php` | Done |
| 2 | Admin submenu registration (Overview, Users, User Detail) | `src/Admin/AdminMenu.php` | Done |
| 3 | Overview page (headline stats + per-domain table) | `src/Admin/OverviewPage.php` | Done |
| 4 | Users list table (`WP_List_Table`, sortable, searchable) | `src/Admin/UsersListTable.php` | Done |
| 5 | Users page wrapper | `src/Admin/UsersPage.php` | Done |
| 6 | User Detail page (read-only view + admin PDF download) | `src/Admin/UserDetailPage.php` | Done |
| 7 | Wired into `Plugin::boot()` | `src/Plugin.php` | Done |
| 8 | Smoke test | `tests/admin-smoke.php` | Done |

---

## 3. Feature coverage (against spec)

| Spec section | Behaviour | Implementation |
|---|---|---|
| **issac_manager role** | `read`, `issac_take_assessment`, `issac_edit_instrument`, `issac_view_admin` | Added in `Capabilities::install()` with idempotent update-if-exists pattern; VERSION bumped to `'2'` |
| **Menu structure** | Overview + Users under ISSAC, User Detail hidden | `AdminMenu::addSubmenus()`: Overview shares parent slug (replaces landing), Users at `issac-users`, User Detail with `null` parent |
| **Overview counters** | Total participants, In Progress, Completed, PDFs generated | Aggregate SQL on `issac_assessments` and `issac_events` tables |
| **Overview per-domain table** | Avg completion %, Avg score, Participants answered | Per-domain aggregate queries using item codes from `InstrumentRepository::tree()` |
| **Users list table** | Paginated, sortable (User, Started, Last Activity, Overall %, Status), searchable | `WP_List_Table` with SQL-level sorting including `overall_pct` subquery; `LIKE` search on `display_name` / `user_email` |
| **Domain mini-columns** | Per-domain completion % (D1–D5) | Computed via `ScoringService::summary()` for each page row |
| **User Detail view** | User header, overall summary, per-domain table with progress bars | All data from `ScoringService::summary()`; progress bars via styled `<div>` elements |
| **Admin PDF download** | Downloads target user's PDF without recording `pdf_generated` event | `admin_post_issac_download_user_pdf` handler; snapshots existing event timestamp, generates via `save` mode, restores/removes event afterward |
| **Security** | Capability check + nonce on all pages and PDF download | `current_user_can(VIEW_ADMIN)` at render; `check_admin_referer()` + capability check on download |

---

## 4. Deviations from plan

1. **Autoloader fix for `InstrumentVersion.php`** — the file-level constant
   `CURRENT_INSTRUMENT_VERSION` was not in Composer's autoloader (PSR-4 only
   autoloads classes, not file constants). Added to `composer.json` `files`
   autoload and regenerated. This was a pre-existing bug that surfaced when
   the smoke test created a new assessment from scratch (any code path that
   called `AssessmentRepository::findOrCreate()` without an existing assessment
   would have failed in WP-CLI context).

2. **Event snapshot/restore instead of pure bypass** — the spec says to use
   `'save'` mode and stream manually to bypass event recording. However,
   `ReportGenerator::generate('save')` unconditionally records the
   `pdf_generated` event. Rather than modifying `ReportGenerator`, the admin
   download handler snapshots the existing event timestamp before generation
   and restores or removes it afterward. This preserves the participant's
   event history without changing the generator's contract.

3. **Overview page replaces parent landing** — the Overview submenu uses
   `PostTypes::MENU_SLUG` as its own slug, so clicking the top-level "ISSAC"
   menu item renders the Overview page instead of the old `renderLanding()`.
   The old method remains as dead code per the spec's instruction.

---

## 5. Architecture

```
Admin menu structure:
ISSAC (cap: issac_edit_instrument)
├── Overview            (cap: issac_view_admin, slug: issac)
├── Users               (cap: issac_view_admin, slug: issac-users)
├── Domains             (CPT: issac_domain)
├── Subsections         (CPT: issac_subsection)
└── Items               (CPT: issac_item)
    [hidden] User Detail (cap: issac_view_admin, slug: issac-user-detail)

Admin clicks "Download their PDF" on User Detail
  → GET admin-post.php?action=issac_download_user_pdf&assessment_id=…&_wpnonce=…
    → UserDetailPage::downloadPdf()
      → current_user_can(VIEW_ADMIN) check
      → check_admin_referer() nonce verification
      → AssessmentRepository::getById()
      → Snapshot existing pdf_generated event timestamp (if any)
      → ReportGenerator::generate($assessment, 'save')
      → Restore/remove pdf_generated event (undo side-effect)
      → Stream file with headers + readfile()
      → unlink() temp file + exit
```

---

## 6. Files changed vs. created

| File | Action | Lines |
|---|---|---|
| `src/Install/Capabilities.php` | Edit — added `issac_manager` role, bumped VERSION | +15 |
| `src/Plugin.php` | Edit — added `AdminMenu::register()` | +2 |
| `composer.json` | Edit — added `files` autoload for `InstrumentVersion.php` | +3 |
| `src/Admin/AdminMenu.php` | **New** | 54 |
| `src/Admin/OverviewPage.php` | **New** | 189 |
| `src/Admin/UsersListTable.php` | **New** | 223 |
| `src/Admin/UsersPage.php` | **New** | 27 |
| `src/Admin/UserDetailPage.php` | **New** | 233 |
| `tests/admin-smoke.php` | **New** | 195 |

---

## 7. Verification

**Automated — smoke test (`tests/admin-smoke.php`) — 22/22 passing**

```
wp eval-file wp-content/plugins/issac-assessment/tests/admin-smoke.php --user=1
```

| # | Check | Assertions |
|---|---|---|
| 1 | `issac_manager` role exists with correct capabilities | 5 |
| 2 | Administrator has `issac_view_admin` and `issac_edit_instrument` | 2 |
| 3 | Admin submenu pages registered under `issac` slug | 2 |
| 4 | Overview page renders stat cards and domain table | 3 |
| 5 | `UsersListTable` contains test user | 2 |
| 6 | User Detail page renders user name, download button, overall card, domain table, back link | 5 |
| 7 | Invalid assessment shows error with back link | 2 |
| 8 | `issac_participant` does NOT have `issac_view_admin` | 1 |

---

## 8. Security

- All admin pages gated by `Capabilities::VIEW_ADMIN` (`issac_view_admin`).
- PDF download verifies nonce via `check_admin_referer()` and capability.
- Assessment ID validated via `absint()`.
- Admin can view any user's assessment (read-only) — by design for auditing.
- All output escaped: `esc_html()`, `esc_attr()`, `esc_url()`.
- All SQL via `$wpdb->prepare()` with parameterised placeholders.
- Admin PDF download does not alter participant's event history.

---

## 9. Manual verification checklist

1. **Menu structure:** log in as administrator — ISSAC menu shows Overview,
   Users, Domains, Subsections, Items.
2. **Overview page:** four stat cards (Total Participants, In Progress,
   Completed, PDFs Generated) and per-domain stats table with correct averages.
3. **Overview empty state:** fresh install with no assessments — counters all 0,
   domain table shows 0% / 0 participants.
4. **Users page:** paginated table of users with assessments, columns display
   correctly, search filters by name/email.
5. **Users sorting:** click column headers — table re-sorts correctly.
6. **User Detail:** click a user row — shows assessment summary with correct
   scores, progress bars, user info, dates.
7. **Download their PDF:** click the download button — downloads a PDF for the
   target user (not the admin). Verify: no new `pdf_generated` event appears in
   `wp_issac_events` for the participant.
8. **issac_manager role:** create a user with `issac_manager` — they can see the
   ISSAC menu, access Overview/Users, edit instrument content, and take the
   assessment. They cannot access Plugins, Themes, or WP Users.
9. **Access gate — subscriber:** ISSAC menu not visible, direct URL shows error.
10. **Access gate — participant:** ISSAC admin menu not visible (only has
    `issac_take_assessment`).
11. **Invalid assessment:** navigate to User Detail with bogus `assessment_id` —
    shows error message, not a PHP fatal.

---

## 10. Follow-ups / notes for later milestones

- **Charts and CSV export (Stage 2):** the Overview page's `gatherStats()`
  returns structured data ready for Chart.js consumption. A CSV streaming
  endpoint can query the same aggregate SQL.
- **Mobile admin polish (M9):** the stat cards use flexbox and wrap on narrow
  viewports, but a dedicated mobile pass would improve the Users table layout.
- **Configurable footer (M9):** the admin PDF download reuses the existing
  `ReportGenerator`, so any footer customisation applies to admin downloads too.
