# Milestone 6 — Participant Dashboard — Completion Report

**Milestone:** 6 (Dashboard)
**Spec:** `prompts/milestone-6-dashboard.md`
**Contract:** `ISSAC-development-plan.md` §6.1, §6.2, §11
**Status:** Complete — 21/21 smoke tests pass, 23/23 PHPUnit tests pass (5 new + 18 existing)
**Date:** 2026-06-22

---

## 1. Summary

Milestone 6 delivers the **participant dashboard** — the landing page at `/assessment/`
rendered by the `[issac_dashboard]` shortcode. It orients a logged-in participant with
their overall progress (SVG donut ring), five domain cards showing per-domain completion
and average/band, and context-aware CTA buttons that link to each domain page. A disabled
"Download report" button is a placeholder for the M7 PDF endpoint.

All data flows through the existing `InstrumentRepository`, `ScoringService`, and
`ResponseRepository` — no new queries, no math in the template, no `get_field()` calls.

---

## 2. Deliverables

| # | Deliverable | File | Status |
|---|---|---|---|
| 1 | `[issac_dashboard]` shortcode + `domainCtaLabel()` helper | `src/Frontend/Shortcodes.php` | Done |
| 2 | Dashboard template (ring, cards, download button) | `src/Frontend/templates/dashboard.php` | Done |
| 3 | Plugin behaviour SCSS (ring colours, description clamp, stats layout) | `assets/scss/issac.scss` → `assets/css/issac.css` | Done |
| 4 | Theme presentation SCSS (dashboard layout, ring sizing/position) | `themes/issac-bystra/scss/_issac.scss` → `themes/issac-bystra/style.css` | Done |
| 5 | Best-effort early enqueue for `issac_dashboard` | `src/Frontend/Assets.php` | Done |
| 6 | Unit tests for `domainCtaLabel()` | `tests/Unit/DashboardHelpersTest.php` | Done — 5/5 pass |
| 7 | Dashboard smoke test | `tests/dashboard-smoke.php` | Done — 21/21 pass |
| 8 | PHPUnit test bootstrap | `tests/bootstrap.php` | Done |

---

## 3. Feature coverage (against spec §A–C)

| Spec section | Behaviour | Implementation |
|---|---|---|
| **A. Progress ring** | Inline SVG donut at `completion`%, centre label with integer percent + "{answered}/{total} items" | `stroke-dasharray`/`stroke-dashoffset` geometry computed in PHP; `role="img"` + `aria-label` + `visually-hidden` text for screen readers |
| **B. Domain cards** | Title, description (clamped), Bootstrap progress bar, conditional avg/band, context-aware CTA | Five cards in a responsive `.row`/`.col` grid; `wp_kses_post` for descriptions; average/band shown only when `answered >= 1`, always paired with "n/N answered" |
| **B. CTA labels** | Start (0%), Resume (partial), Review (100%) | `Shortcodes::domainCtaLabel()` — pure static method, unit-tested at boundaries |
| **B. CTA links** | `/assessment/domain/?d={code}` | `add_query_arg('d', $code, trailingslashit(get_permalink()) . 'domain/')` — builds from the dashboard page permalink + child slug |
| **C. Download button** | Disabled placeholder; "Coming soon" | `<button disabled>` with `TODO(M7)` comment; no href to a missing route |
| **Access gate** | Login prompt for logged-out or missing cap | Identical pattern to `renderDomain()` — returns `<p class="issac-login-prompt">` with `wp_login_url()` link |
| **Asset enqueue** | CSS/JS load on both dashboard and domain pages | `Assets::enqueueAll()` called from `renderDashboard()`; best-effort early enqueue also detects `issac_dashboard` shortcode |

---

## 4. Deviations from plan (recorded in `ISSAC-development-plan.md`)

1. **PHPUnit test bootstrap** — `phpunit.xml` previously bootstrapped from
   `vendor/autoload.php`. Files like `Shortcodes.php` have the standard WordPress
   `defined('ABSPATH') || exit;` guard, which kills the PHPUnit process before tests
   run. Added `tests/bootstrap.php` that defines `ABSPATH` as a stub, then loads the
   Composer autoloader. Updated `phpunit.xml` to use the new bootstrap. All 18
   pre-existing `ScoringServiceTest` tests continue to pass (ScoringService itself has
   no ABSPATH guard, so the change is backwards-compatible).

2. **CTA link construction** — the spec said "build via the dashboard page permalink".
   Initial implementation used `add_query_arg('d', $code, get_permalink())` which
   appended `?d=X` to the dashboard page itself (`/assessment/?d=1`) instead of the
   child domain page. Fixed to
   `add_query_arg('d', $code, trailingslashit(get_permalink()) . 'domain/')` which
   produces the correct `/assessment/domain/?d=1`. This relies on the domain page being
   a child of the dashboard page with slug `domain`.

3. **SCSS compiled via `sass` CLI** — same as M5B; no CodeKit project covers this
   plugin path. Both `assets/css/issac.css` and `themes/issac-bystra/style.css` compiled
   and committed.

The plugin file tree in §4 was updated to include the 3 new test files.

---

## 5. Verification

**Automated — PHPUnit (23/23)**
- `DashboardHelpersTest`: 5 passing (0→Start, 0.1→Resume, 50→Resume, 99.9→Resume, 100→Review)
- `ScoringServiceTest`: 18 passing (unchanged)

**Automated — smoke test (21/21)**

```
wp eval-file wp-content/plugins/issac-assessment/tests/dashboard-smoke.php --user=1
```

| # | Check | Result |
|---|---|---|
| 1 | Dashboard structure (wrapper, SVG, 5 cards, 5 bars) | 4/4 pass |
| 2 | Fresh: all CTAs "Start", download disabled, no avg/band | 3/3 pass |
| 3 | Partial D1: "Resume", others "Start", avg/band + count | 4/4 pass |
| 4 | Complete D4: "Review" | 1/1 pass |
| 5 | CTA links include `d={code}` for all 5 domains | 5/5 pass |
| 6 | Download button disabled, no PDF route link | 2/2 pass |
| 7 | Logged-out → login prompt | 2/2 pass |
| 8 | No-capability → login prompt | Skipped (no subscriber user) |

**Manual** — see checklist in `prompts/milestone-6-dashboard.md` §Manual Verification.

---

## 6. Accessibility

- SVG ring wrapped in `role="img"` with `aria-label="Overall progress: N%"`.
- `visually-hidden` span provides the full text ("N%, X of Y items answered") for
  screen readers.
- Progress bars have `role="progressbar"` with `aria-valuenow`/`aria-valuemin`/
  `aria-valuemax` and `aria-label` per domain.
- Description content rendered via `wp_kses_post` (preserves safe HTML from wysiwyg);
  CSS line-clamp is presentation-only — full text remains in the DOM.
- `prefers-reduced-motion` disables the ring fill transition.
- CTA buttons are standard `<a>` elements (keyboard-focusable by default).
- Download button uses `disabled` + `aria-disabled="true"`.

---

## 7. Security

- All output escaped: `esc_html` (titles, labels), `esc_attr` (attributes, SVG values),
  `wp_kses_post` (domain descriptions), `esc_url` (CTA links).
- User resolved from `get_current_user_id()`, never from request input.
- Access gate checks `is_user_logged_in()` AND `current_user_can(TAKE_ASSESSMENT)`.
- No new SQL or REST endpoints introduced — all data via existing repositories.

---

## 8. Manual verification checklist

**1. Fresh user state (0 answers)**

- Log in as a participant and visit `/assessment/`.
- The SVG ring should show **0%** with "0/69 items" (or your active item count).
- All five domain cards should display a **Start** button.
- Each card should show "0/N answered" with no average or band text.
- The download button should be disabled with "Coming soon" beneath it.

**2. Partial progress**

- Click **Start** on domain 1 — it should take you to `/assessment/domain/?d=1`.
- Answer 2–3 items, then navigate back to `/assessment/`.
- Domain 1 card should now show **Resume** (not Start).
- Domain 1 should display an average and band (e.g. "Avg 3.7 · Implementing") paired
  with "3/9 answered".
- The other four cards should still show **Start**.
- The overall ring should show a small percentage matching your answered count.

**3. Complete a domain**

- Go into domain 4 (the smallest, 5 items) and answer all items.
- Return to `/assessment/`.
- Domain 4 card should show **Review** with a 100% progress bar.
- The ring percentage should have increased accordingly.

**4. CTA links**

- Hover over each domain's button — the URL should be `/assessment/domain/?d=1`
  through `?d=5`.
- Click one and confirm it loads the correct domain page.

**5. Download button**

- With some answers in place, the button should say "Download progress report" but
  remain disabled — clicking does nothing.
- No 404 or console error should appear (there's no link to follow).

**6. Access gates**

- Log out, then visit `/assessment/` — you should see a login prompt with a "Log in"
  link, not the dashboard.
- Click the link — it should take you to the WP login page and redirect back to
  `/assessment/` after login.

**7. Responsive / mobile**

- Resize your browser to ~375px wide (or use DevTools device mode).
- Cards should stack single-column, the ring should stay centered, nothing should
  overflow.

**8. Accessibility**

- Tab through the page — CTA buttons should receive visible focus rings.
- Inspect the SVG ring in DevTools: it should have `role="img"` and
  `aria-label="Overall progress: N%"`.
- Progress bars should have `role="progressbar"` with correct `aria-valuenow`.

**9. Console check**

- Open the browser console on `/assessment/` — no 404s for `issac.css` or `issac.js`.
- Type `window.issacData` — it should return the object with `restUrl`, `nonce`, and
  `domainCode` (null on the dashboard, since there's no `?d=` param).

**10. View source / escaping**

- View page source and search for domain titles and descriptions — HTML entities should
  be escaped, no raw `<script>` tags anywhere.

---

## 9. Follow-ups / notes for later milestones

- **PDF report (M7):** the download button is a disabled `<button>` with a `TODO(M7)`
  comment. Wire it to `GET /issac/v1/report` once the endpoint exists. Switch to an
  `<a>` tag or JS-driven stream depending on the delivery mechanism.
- **Capability gate smoke test:** step 8 was skipped because no subscriber user existed
  in the test environment. Consider creating a dedicated test participant user for the
  full smoke suite.
- **Domain page link construction** assumes the domain page slug is `domain` and is a
  child of the dashboard page. If the page structure changes, the
  `trailingslashit(get_permalink()) . 'domain/'` pattern would need updating. A future
  hardening pass (M9) could look up the domain page dynamically.
