# Milestone 6 — Participant Dashboard

Read `@ISSAC-development-plan.md` and `@.cursor/rules/issac.mdc` first, then:

Implement Milestone 6 from `ISSAC-development-plan.md`, following `.cursor/rules/issac.mdc`.

> This milestone builds the **participant dashboard** — the landing page of the
> assessment (`/assessment/`, shortcode `[issac_dashboard]`). It is the entry point
> that orients a logged-in participant: overall progress, the five domains with their
> individual progress, and the navigation into each domain page (built in 5A/5B).
> See the plan §6.1, §6.2 and the Milestone 6 row in §11.

---

## Context — what is already built (Milestones 0–5B)

Do **not** re-derive or rebuild any of this. It is complete and verified.

**Milestone 5A (server rendering)** delivered the domain page:
- `Issac\Frontend\Shortcodes` — registers `[issac_domain]`, with the login/cap access
  gate that **returns** a login-prompt string (a shortcode cannot `wp_redirect()`).
- `Issac\Frontend\Assets` — see below; was reworked in 5B.
- `src/Frontend/templates/domain.php` — accessible, JS-free, server pre-selects scores.
- `assets/scss/issac.scss` → `assets/css/issac.css` (plugin states), and theme styles
  in `themes/issac-bystra/scss/_issac.scss` → `themes/issac-bystra/style.css`.
- `tests/render-smoke.php` — `wp eval-file` smoke test pattern.

**Milestone 5B (interactivity)** delivered:
- `assets/js/issac.js` — debounced autosave, retry queue, 403 nonce refresh, live
  progress bar, milestone toasts (Bootstrap Toast), instant descriptor highlight.
- `assets/js/issac-logic.mjs` + `tests/js/issac-logic.test.mjs` (pure helpers, 6 tests).
- A hidden Bootstrap toast template (`#issac-toast-template`) in `domain.php`.
- **Asset enqueue was reworked:** `Assets::registerAssets()` (on `wp_enqueue_scripts`)
  registers + localizes the `issac-css` / `issac-js` handles and attaches
  `window.issacData = { restUrl, nonce, domainCode }`; the **shortcode** then calls
  `Assets::enqueueAll()` at render time. This is because `has_shortcode()` cannot see a
  shortcode rendered through a page template. **M6 must follow the same pattern** —
  the dashboard shortcode calls `Assets::enqueueAll()` so its CSS/JS load reliably.

### Verified APIs (confirmed against the code — consume these, never `get_field()`)

- `InstrumentRepository::tree(): DomainNode[]` — ordered by `menu_order`.
  - `DomainNode`: `->id, ->code (string "1".."5"), ->title, ->description (wysiwyg),
    ->menuOrder, ->subsections (SubsectionNode[])`.
- `AssessmentRepository::findOrCreate(int $userId): object` (lazily creates the
  `in_progress` row); `currentFor(int $userId): ?object`.
- `ResponseRepository::forAssessment(int $assessmentId): array` → `['1.1'=>3, …]`.
- `ScoringService::summary(array $tree, array $responses): array`:
  - `['overall' => ['completion'=>float, 'average'=>?float, 'band'=>string, 'answered'=>int, 'total'=>int]]`
  - `['domains' => [ ['code'=>string, 'title'=>string, 'completion'=>float, 'average'=>?float, 'band'=>string, 'answered'=>int, 'total'=>int, 'subsections'=>[…]], … ]]`
  - **`domains` is a 0-based array in tree order, NOT keyed by code** — match on `['code']`.
  - `average` is `null` and `band` is `"Not yet rated"` when nothing is answered yet.
- `Capabilities::TAKE_ASSESSMENT` = `'issac_take_assessment'`.

### Manual prerequisite (human, not code)

- A WordPress **page must exist** containing `[issac_dashboard]` (e.g. at `/assessment/`).
- **No CodeKit project is active for this repo.** Any SCSS you add must be compiled to
  CSS as part of this milestone (`sass` CLI) and the compiled output committed —
  plugin: `assets/scss/issac.scss` → `assets/css/issac.css`; theme:
  `themes/issac-bystra/scss/style.scss` → `themes/issac-bystra/style.css`.

---

## Deliverables

### 1. `[issac_dashboard]` shortcode — `src/Frontend/Shortcodes.php`

Add a `renderDashboard()` method and register `[issac_dashboard]` alongside the
existing `[issac_domain]` registration.

- **Access gate — identical pattern to `renderDomain()`.** If the user is not logged
  in OR lacks `Capabilities::TAKE_ASSESSMENT`, **return** a login-prompt string with a
  `wp_login_url(get_permalink())` link. Do NOT `wp_redirect()`.
- Call `Assets::enqueueAll()` at the top of the render path (same as the domain
  shortcode) so dashboard CSS/JS load.
- Resolve the assessment: `AssessmentRepository::findOrCreate(get_current_user_id())`.
- `$tree = InstrumentRepository::tree();`
  `$responses = ResponseRepository::forAssessment((int) $assessment->id);`
  `$summary = ScoringService::summary($tree, $responses);`
- `ob_start()` / `include templates/dashboard.php` / `return ob_get_clean()`.
- Pass to the template: `$tree`, `$summary` (and `$assessment` if the template needs
  `status`). The template renders entirely from `$summary` — never compute math in the
  template.

### 2. `src/Frontend/templates/dashboard.php`

Server-rendered, accessible, works without JS. Variables in scope: `$tree`,
`$summary`, `$assessment`.

**A. Overall progress ring (inline SVG)**
- Inline `<svg>` donut using `stroke-dasharray` / `stroke-dashoffset` to show
  `$summary['overall']['completion']` (a float 0–100).
- Centre label: integer percent + small caption `"{answered}/{total} items"`.
- Accessible: wrap with `role="img"` and an `aria-label` like
  `"Overall progress: 42%"`, or provide a `visually-hidden` text equivalent. The ring
  is decorative reinforcement — the number must be readable to screen readers.

**B. Five domain cards** — one per `$summary['domains']` row, in tree order:
- Domain title (`esc_html`).
- A short description from the matching `DomainNode->description`. It is a wysiwyg
  field → `wp_kses_post`, and may be long; trim/clamp to a lead sentence or use a CSS
  line-clamp (presentation only — do not mutate content).
- Bootstrap linear progress bar (`.progress` / `.progress-bar`) at `completion`%,
  with `role="progressbar"` + aria values, mirroring the domain page markup.
- Average + band **only once `answered >= 1`** (`average` is `null` otherwise). Show
  e.g. `"Avg 3.4 · Implementing"` and always pair it with `"{answered}/{total} answered"`
  so a partial result can't be misread (plan §7).
- **Context-aware action button** linking to the domain page
  `/assessment/domain/?d={code}` (build via the dashboard page permalink or a filtered
  base; keep the `?d=` query arg). Label by completion:
  - `completion == 0` → **Start**
  - `0 < completion < 100` → **Resume**
  - `completion == 100` → **Review**
  - Use a single pure helper for this mapping so it is obvious and testable, e.g.
    `domain_cta_label(float $completion): string`.

**C. Download report button** (plan §6.2)
- Render the button with its state logic: enabled once
  `$summary['overall']['answered'] >= 1`; label `"Download progress report"` while
  `overall completion < 100`, `"Download report"` at 100%.
- **Cross-milestone dependency:** the PDF endpoint (`GET /report`) is **Milestone 7**.
  For M6, render the button **disabled** (or visibly "coming soon") rather than linking
  to a route that 404s. Leave a `TODO(M7)` comment marking where the href/stream wires
  in. Do not build any PDF logic in this milestone.

### 3. Styles & compilation

- Prefer Bootstrap utilities/components in the markup (`.row`, `.col`, `.card`,
  `.progress`, spacing utilities). **Do not `@import` Bootstrap** — the theme loads it.
- Plugin-behaviour/state styles → `assets/scss/issac.scss`. The SVG ring sizing and
  the dashboard card grid that are *theme presentation* → `themes/issac-bystra/scss/_issac.scss`.
  Use your judgement per the rule in `issac.mdc`; keep each file short.
- Use Bootstrap CSS custom properties (`--bs-*`) for colours to stay in the theme skin.
- Respect `prefers-reduced-motion` for any ring/bar transitions.
- **Compile both** (`sass` CLI) and commit the outputs. CodeKit is not active here.

### 4. Wire into `Plugin.php`

No new boot call is needed if `Shortcodes::register()` and `Assets::register()` are
already wired (they are, from 5A). Just ensure `register()` now also adds
`[issac_dashboard]`.

### 5. Tests

Follow the existing convention — **do NOT scaffold a WP PHPUnit harness.**

- **Pure logic:** if you add `domain_cta_label()` / a ring-geometry helper as pure
  functions, add a tiny PHPUnit test in the existing `tests/Unit/` style, matching
  `ScoringServiceTest.php`. For `domain_cta_label()`, cover only the boundary cases:
  `0 → Start`, `0 < completion < 100 → Resume`, `100 → Review`.
- **Smoke test:** create `tests/dashboard-smoke.php`, runnable via
  `wp eval-file tests/dashboard-smoke.php`, mirroring `tests/render-smoke.php`:
  1. `wp_set_current_user()` to a user WITH `issac_take_assessment`.
  2. Render `do_shortcode('[issac_dashboard]')`, capture output, and assert
     PASS/FAIL per check:
     - Fresh assessment: output contains the dashboard wrapper, ring SVG, five domain
       cards, and five progress bars.
     - Fresh assessment: all five domain CTAs show **Start**; download button is
       disabled; no average/band text is shown yet.
     - After upserting one active response in domain 1: domain 1 shows **Resume**,
       the other domains still show **Start**, and domain 1 shows its average/band
       paired with its answered count.
     - After answering all active items in domain 4: domain 4 shows **Review**.
     - CTA links include the correct `?d={code}` for their domain.
     - Download button remains present-but-disabled for M7, including after one or
       more responses; it must not link to a missing PDF route.
     - Logged-out and logged-in-without-`issac_take_assessment` both return the
       login-prompt string and no fatal.
  3. Clean up test data at the end: delete responses for the test assessment, delete
     the test assessment row, and restore the original user/cap state.

---

## Conventions (from `.cursor/rules/issac.mdc`)

- PHP 8.1+, PSR-12, namespace `Issac\Frontend`.
- Instrument content ONLY via `InstrumentRepository::tree()` — never scattered `get_field()`.
- Totals/percentages/averages come from `ScoringService` — **never computed in the
  template** and never hard-coded.
- Escape ALL output: `esc_html`, `esc_attr`, `wp_kses_post` (descriptions), `esc_url`.
- Resolve the user from `get_current_user_id()`, never from request input.
- Minimalistic, readable code — no elaborate defensive fallback chains.

---

## Manual Verification Checklist

1. Visit `/assessment/` logged in as a participant → ring shows overall %, five domain
   cards render with titles, descriptions, progress bars.
2. **Fresh user (0 answers):** ring at 0%, every card shows **Start**, download button
   disabled.
3. **Partial:** answer some items in domain 1 (via the domain page or `.http`), reload
   → domain 1 card shows **Resume** with the right bar %, others still **Start**.
4. **Complete a domain:** finish a small domain → its card shows **Review** at 100%.
5. Averages only appear once a domain has ≥1 answer, always paired with "n/N answered".
6. CTA links land on `/assessment/domain/?d={code}` for the right domain.
7. Download button is disabled with a clear "coming soon"/M7 affordance (no 404 link).
8. Logged out → login prompt with a working link (no redirect). Missing cap → same.
9. Mobile (375px): cards stack, ring scales, nothing overflows.
10. Keyboard + screen reader: ring has an accessible label/percent; cards are reachable;
    focus rings visible on the CTA buttons.
11. Console clean: no 404 for `issac.css`/`issac.js`; `window.issacData` present.
12. View source: descriptions/ titles escaped (no raw `<script>`).

---

## What This Milestone Does NOT Include

- **PDF generation / `GET /report`** — Milestone 7. The dashboard's download button is
  a disabled placeholder until then.
- Admin dashboard / stats / user list — Milestone 8.
- Any change to the domain page autosave flow (5B) or the REST API (Milestone 4).
- No new milestone-toast triggering on the dashboard: toasts fire on save (domain page).
  The 5B toast template + `issacData` contract are reused, but the dashboard itself
  enters no scores, so it surfaces no new events. (No nonce-refresh concern here either,
  since the dashboard does not autosave.)

---

## Notes carried over from the Milestone 5B report

- Reuse the same toast template + `issacData` contract; keep `Assets::enqueueAll()`
  callable from the dashboard shortcode (this milestone exercises exactly that).
- Escaping all dashboard output moves the §10 security checklist item
  ("output escaping everywhere") closer to done — keep it rigorous here.
