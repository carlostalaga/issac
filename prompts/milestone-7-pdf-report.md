# Milestone 7 — PDF Report

Read `@ISSAC-development-plan.md` and `@.cursor/rules/issac.mdc` first, then:

Implement Milestone 7 from `ISSAC-development-plan.md`, following `.cursor/rules/issac.mdc`.

> This milestone builds the **PDF report generator** using mPDF (already in
> `composer.json`). A participant can download their current progress — or
> completed assessment — as a branded PDF directly from the dashboard. The
> report is rendered from the same `ScoringService` data that powers the
> dashboard and domain pages: one source of truth for all numbers.
> See the plan §8, §6.4 (`GET /report`), and the Milestone 7 row in §11.

---

## Context — what is already built (Milestones 0–6)

Do **not** re-derive or rebuild any of this. It is complete and verified.

**Data layer (M3–M4):**
- `AssessmentRepository::findOrCreate(int $userId): object` — lazily creates an
  `in_progress` row.
- `ResponseRepository::forAssessment(int $assessmentId): array` → `['1.1'=>3, …]`.
- `EventRepository::record(int $assessmentId, string $eventKey): bool` — INSERT IGNORE,
  returns `true` on first insert (used for `pdf_generated` event).
- `ScoringService::summary(array $tree, array $responses): array` — the full scoring
  breakdown consumed by dashboard, domain pages, and now the PDF.
- `InstrumentRepository::tree(): DomainNode[]` — the cached instrument tree.

**REST (M4):**
- `RoutesController` provides shared `permissionCallback()` (401/403) and
  `resolveAssessment()`. Namespace constant: `RoutesController::NAMESPACE = 'issac/v1'`.
- All routes already registered in `RoutesController::registerRoutes()`.

**Dashboard (M6):**
- `templates/dashboard.php` has a disabled download `<button>` with a
  `TODO(M7)` comment at line ~93. This button must be wired to the new PDF route.
- Button label logic: `"Download progress report"` while `completion < 100`,
  `"Download report"` at `100%`. **Keep this label logic** — just enable the button and
  add the download link/action.

**Plugin bootstrap (`issac-assessment.php`):**
- `ISSAC_VERSION = '0.2.0'`, `ISSAC_PATH`, `ISSAC_URL`.
- Composer autoload is loaded; mPDF is already a dependency (`mpdf/mpdf: ^8.3`).

### Manual prerequisite (human, not code)

- **No CodeKit project is active for this repo.** If any SCSS changes are needed,
  compile with the `sass` CLI and commit the outputs.

---

## Deliverables

### 1. `src/Pdf/ReportGenerator.php`

The single class responsible for building and outputting the PDF.

```php
namespace Issac\Pdf;

final class ReportGenerator
{
    /**
     * Generate the PDF for an assessment.
     *
     * @param object $assessment  Row from wp_issac_assessments.
     * @param string $mode        'stream' (download) or 'save' (file).
     *                            Stage 1 only uses 'stream'. The parameter
     *                            exists for Stage 2 readiness (§12 of plan).
     * @return string|null        File path when $mode='save', null on stream.
     */
    public static function generate(object $assessment, string $mode = 'stream'): ?string
```

**Implementation flow:**

1. Load the instrument tree, responses, and summary via the repositories + `ScoringService`.
   Never compute math in this class — consume `$summary` as-is.
2. `ob_start()` / `include __DIR__ . '/templates/report.php'` / `$html = ob_get_clean()`.
3. Instantiate mPDF with a sensible config:
   - A4 portrait, reasonable margins.
   - `tempDir` → `wp-content/uploads/issac-tmp/` (create if missing; add a protective
     empty `index.php` and `.htaccess` with `Deny from all`).
   - Default font: a sans-serif bundled with mPDF (e.g. `dejavusans`). Do **not** import
     custom fonts in Stage 1 — keep it simple.
4. `$mpdf->WriteHTML($html)`.
5. **Stream mode:** `$mpdf->Output('ISSAC-Report-{Y-m-d}.pdf', \Mpdf\Output\Destination::DOWNLOAD)`.
   **Save mode:** write to `wp-content/uploads/issac-reports/{user_id}/` and return the path.
   Stage 1 only calls stream; implement save minimally (create dir, write file) so
   the parameter isn't dead code.
6. After streaming/saving, record the `pdf_generated` event via a new
   `EventRepository::recordOrTouch(int $assessmentId, string $eventKey): void`
   method (see deliverable §8 below). Unlike the once-only milestone events,
   `pdf_generated` must **update its `created_at` timestamp** on every generation so
   the dashboard can display "Last report generated: {date}".

**Error handling:** if mPDF throws, catch `\Mpdf\MpdfException` and return a
`WP_Error` / wp_die with a user-friendly message — don't expose the internal trace.

### 2. `src/Pdf/templates/report.php`

An HTML template designed for mPDF rendering (not browser rendering). Variables in scope
must be passed from `ReportGenerator`: `$summary`, `$tree`, `$responses`, `$assessment`,
`$user` (the WP user object for name display).

**mPDF HTML/CSS constraints (critical — mPDF is NOT a browser):**
- **No flexbox, no CSS grid.** Use `<table>` for layout, `float` for simple positioning.
- **No CSS custom properties** (`var(--bs-*)` won't work). Use hardcoded hex colours.
- **No Bootstrap classes** — they rely on a full Bootstrap CSS load that mPDF won't have.
- Keep CSS inline or in a `<style>` block inside the template. External stylesheets
  require explicit mPDF loading and add complexity.
- Use `%` or `mm` for widths; `px` works but can be unpredictable for print.
- Images: use absolute file paths (`ISSAC_PATH . 'assets/img/...'`), not URLs.

**Contents (from plan §8):**

**A. Cover / header area:**
- Report title: "ISSAC Assessment Report"
- Participant name: `$user->display_name` (escaped).
- Date generated: current date formatted nicely.
- Overall completion: `"{answered}/{total} items · {completion}%"`.
- Instrument version: `$assessment->instrument_version`.
- Assessment status: `$assessment->status` (in_progress / completed).

**B. Summary table:**
A table with one row per domain:

| Domain | Items answered | Average | Band |
|---|---|---|---|
| 1. Inclusive Site Culture | 7/9 | 3.4 | Implementing |

- Show `"—"` for average and band when `answered === 0` for that domain.
- Overall row at the bottom.

**C. Domain progress bars:**
- For each domain, a simple horizontal bar showing completion % — implemented as a
  `<div>` with a background-colour and width. No chart library.
- mPDF renders `<div>` widths reliably; use inline styles for the bar width and colour.

**D. Per-domain detail:**
For each domain in tree order:
- Domain heading with title.
- For each subsection: subsection heading, then a table of items:

| Code | Item prompt | Score | Band |
|---|---|---|---|
| 1.1 | There is a shared site vision… | 4 | Sustained Action |
| 1.2 | The school community values… | — | — |

- Unanswered items: show `"—"` for score and band.
- **Inactive items that have responses:** include them greyed out with a note
  "(item retired)" — the responses are preserved, just visually distinct. Use
  `$item->isActive` to check. Match inactive items by checking `$responses` for their
  `item_code`.
- Item prompts: `esc_html()` — they are plain text, not wysiwyg.
- For individual item banding, use `ScoringService::band()` with a single-item "average"
  (the score itself cast to float, or null if unanswered).

**E. Footer:**
- A simple footer line: "Generated by ISSAC Assessment Platform · {date}".
- Use mPDF's `SetFooter()` or inline it at the bottom of the HTML.

### 3. REST endpoint: `GET /report` — `src/Rest/ReportEndpoint.php`

Register in `RoutesController::registerRoutes()` alongside the existing endpoints.

```php
register_rest_route(RoutesController::NAMESPACE, '/report', [
    'methods'             => 'GET',
    'callback'            => [self::class, 'download'],
    'permission_callback' => [RoutesController::class, 'permissionCallback'],
]);
```

**Callback:**
1. Resolve assessment via `RoutesController::resolveAssessment()`.
2. Validate the assessment has at least one response (`ResponseRepository::forAssessment()`
   must be non-empty) — return a 400 `WP_Error` if there's nothing to report on.
3. Call `ReportGenerator::generate($assessment, 'stream')`.
4. The mPDF `Output()` call sends headers + body and exits — the REST response is never
   reached. This is the expected pattern for file-download endpoints in WP REST.

**Important:** because mPDF streams directly and calls `exit`, the endpoint should ensure
no output buffering interferes. If WP's REST dispatch has started output, clean the buffer
before streaming. A simple `while (ob_get_level()) { ob_end_clean(); }` before the
`generate()` call handles this.

### 4. Wire the dashboard download button — `templates/dashboard.php`

Replace the existing disabled `<button>` (the `TODO(M7)` block) with a working download
link:

- **Enabled** once `$summary['overall']['answered'] >= 1`.
- **Disabled** (keep the existing disabled state) when `answered === 0`.
- The link should point to the REST endpoint:
  `rest_url('issac/v1/report')` with the nonce appended via `wp_nonce_url()` or as a
  query arg `_wpnonce`.
  **However**, because REST nonce validation uses the `X-WP-Nonce` header (not a query
  param), and this is a direct download link (no JS), use `wp_nonce_url()` with the
  `_wpnonce` parameter which WP REST automatically checks as a fallback.
  Alternatively, use an `admin-post.php` action — **choose whichever is simpler**. The
  plan §6.4 notes this flexibility: `"Or an admin-post.php action if you prefer a plain
  download URL"`.
- Keep the label logic: `"Download progress report"` while `completion < 100`,
  `"Download report"` at `100%`.
- Remove the "Coming soon" `<small>` text.
- **Show last-generated date:** below the button, display the date of the last PDF
  generation if one exists. The shortcode (`renderDashboard()`) should query
  `EventRepository::lastFired((int) $assessment->id, 'pdf_generated')` (see §8)
  and pass the result to the template as `$lastReportDate` (a `?string` in
  `Y-m-d H:i:s` UTC, or `null` if never generated). The template renders it as e.g.
  `<small>Last report: 14 Mar 2026</small>` — use `wp_date()` to format in the site's
  timezone. Show nothing when `$lastReportDate` is `null`.

### 5. Wire into `Plugin.php`

No new boot call is needed if `RoutesController::register()` already handles route
registration. Just ensure the new `ReportEndpoint::register()` is called from
`RoutesController::registerRoutes()`.

### 6. mPDF temp directory protection

When `ReportGenerator` first runs, it creates the mPDF temp directory at
`wp-content/uploads/issac-tmp/`. Protect it:

- Create an empty `index.php` (`<?php // Silence is golden.`).
- Create a `.htaccess` with `Deny from all` (Apache) — harmless on Nginx.
- This prevents directory listing and direct file access to mPDF's temp files.

### 7. Extend `EventRepository` — `src/Domain/EventRepository.php`

Add two methods to the existing class:

**A. `recordOrTouch(int $assessmentId, string $eventKey): void`**

Uses `INSERT ... ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)` so that
repeatable events like `pdf_generated` update their timestamp on every call, while
still using the same `issac_events` table. The existing `record()` method (INSERT
IGNORE, returns bool) remains unchanged — milestone toasts still use it.

**B. `lastFired(int $assessmentId, string $eventKey): ?string`**

Returns the `created_at` value (UTC datetime string) for a specific event key, or
`null` if the event was never recorded. A single `SELECT created_at ... WHERE ...`
query. This is called by the dashboard shortcode to display the last PDF generation
date.

### 8. Tests

Follow the existing convention — **do NOT scaffold a WP PHPUnit harness.**

**Smoke test:** create `tests/report-smoke.php`, runnable via
`wp eval-file tests/report-smoke.php`, mirroring the existing smoke tests:

1. `wp_set_current_user()` to a user WITH `issac_take_assessment`.
2. Create a test assessment and upsert a few responses across 2+ domains.
3. Test `ReportGenerator::generate()` in save mode (don't stream — capture
   the file path). Assert:
   - The returned path is a valid file that exists on disk.
   - The file is a PDF (starts with `%PDF`).
   - The file is non-empty (> 1 KB is reasonable for a multi-page report).
   - The file name contains the date.
4. Test the generator with a fresh assessment (0 responses):
   - It should still generate (the report shows "—" for unanswered items), OR
   - if you prefer, test that the REST endpoint rejects it with 400.
   Choose one approach and be consistent between the endpoint and generator.
5. Test that `EventRepository::lastFired($assessmentId, 'pdf_generated')` returns
   a non-null datetime string after generation.
6. Generate a second time, then verify `lastFired()` returns a **later** timestamp
   than the first — confirming `recordOrTouch()` updates rather than ignoring.
7. Test the REST endpoint registration:
   - Assert the route `/issac/v1/report` exists (via `rest_get_server()->get_routes()`).
8. Access gate: test that a logged-out request to the route returns 401
   (use `wp_set_current_user(0)` + a direct `WP_REST_Request`).
9. Dashboard integration: render `do_shortcode('[issac_dashboard]')` after PDF
   generation and assert the output contains a "Last report" string with a date.
10. Clean up: delete the generated PDF file, test events, test responses, test
    assessment.

**No new unit tests needed** — the scoring logic is already covered by
`ScoringServiceTest.php`, and the PDF generation depends on mPDF (integration, not unit).

---

## Conventions (from `.cursor/rules/issac.mdc`)

- PHP 8.1+, PSR-12, namespace `Issac\Pdf` for the generator, `Issac\Rest` for the endpoint.
- Instrument content ONLY via `InstrumentRepository::tree()`.
- Scoring from `ScoringService` — **never computed in the template**.
- Escape ALL output in the HTML template: `esc_html` for text, even though it's
  going to mPDF not a browser — defence in depth.
- Resolve the user from `get_current_user_id()`, never from request input.
- All SQL through `$wpdb->prepare()`.
- Minimalistic, readable code.

---

## Manual Verification Checklist

1. **Dashboard button active:** log in as a participant with at least 1 response,
   visit `/assessment/` → the download button should be enabled (not greyed out).
2. **Partial report:** click the download button → a PDF downloads named
   `ISSAC-Report-{date}.pdf`. Open it and verify:
   - Cover shows participant name, date, overall completion %, instrument version.
   - Summary table lists all 5 domains with correct answered counts.
   - Domains with 0 answers show "—" for average and band.
   - Domains with answers show the correct average and band.
   - Progress bars reflect completion percentages visually.
   - Per-domain detail shows item prompts with scores or "—".
3. **Complete report:** complete all items (or enough to reach 100% in one domain),
   download again → verify the completed domain shows 100% bar and all items scored.
4. **Fresh user (0 answers):** the download button should be disabled.
   Manually hitting `GET /wp-json/issac/v1/report` should return a 400 error.
5. **Button label:** while incomplete → "Download progress report"; at 100% overall →
   "Download report".
6. **PDF event + last-generated date:** after first download, reload `/assessment/` →
   below the download button a "Last report: {date}" line should appear. Download a
   second time, reload → the date should update to the newer timestamp. Check
   `wp_issac_events` — there should be exactly one `pdf_generated` row (not two),
   with its `created_at` reflecting the latest download.
7. **Access gate:** log out and hit the report URL directly → should get 401, not a
   PHP error or empty PDF.
8. **File integrity:** open the downloaded PDF in a viewer — it should not be
   corrupted, all text should be readable, no broken characters.
9. **Mobile:** verify the download button is reachable and functional on a narrow
   viewport.
10. **Console clean:** no 404s or JS errors on the dashboard after the button is wired.

---

## What This Milestone Does NOT Include

- **Admin "download their PDF" button** — Milestone 8. The admin can view user detail
  and trigger a PDF download for any user. The `ReportGenerator` is designed with this
  in mind (accepts an `$assessment` object, not a user ID), but the admin UI is M8 scope.
- No PDF archive / history (`issac_reports` table) — Stage 2.
- No custom fonts or branding beyond basic layout — hardening/polish pass is M9.
- No email delivery of the PDF — not in scope for Stage 1.
- The generator's `save` mode is implemented minimally for Stage 2 readiness but is not
  exercised by any user-facing feature in Stage 1.

---

## Notes

- mPDF's `Output()` with `Destination::DOWNLOAD` calls `exit` after sending the file.
  This is expected — the REST callback will never return a `WP_REST_Response` for the
  download route.
- The `pdf_generated` event key is already listed in the plan's §3 events table schema
  as a reserved key. Unlike milestone events (which use `record()` / INSERT IGNORE and
  fire once), `pdf_generated` uses the new `recordOrTouch()` method (INSERT ... ON
  DUPLICATE KEY UPDATE) so its `created_at` reflects the most recent generation. This
  lets the dashboard show "Last report: {date}" without a new table. Stage 2 can layer
  a full `issac_reports` archive table on top if download history is needed.
- The report template is intentionally plain HTML/CSS (no JS, no Bootstrap, no CSS vars).
  mPDF has its own rendering engine with limited CSS support. Keep it simple — tables,
  inline styles, hardcoded colours. Visual polish is M9.
