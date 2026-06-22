# Milestone 5B — Client-Side Autosave & Interactivity

> **Part 2 of 2.** This block adds JavaScript interactivity to the domain page.
> Block 5A (`prompts/milestone-5A-server-rendering.md`) must be complete first —
> it provides the server-rendered template, shortcode, `Assets.php` (with
> `wp_localize_script` already wired), and the SCSS file with base styles.
>
> **Prerequisite:** The domain page already renders correctly without JS. Scores
> are pre-selected server-side, descriptors highlight based on saved state, the
> progress bar shows the current completion. This block enhances that with live
> autosave so users never need to press a "Save" button.

---

## Context

Block 5A delivered (confirmed against actual implementation):
- `src/Frontend/Shortcodes.php` — `[issac_domain]` shortcode with access gate.
- `src/Frontend/Assets.php` — enqueues CSS + JS, localizes `window.issacData`
  with `{ restUrl, nonce, domainCode }`.
- `src/Frontend/templates/domain.php` — full accessible HTML with:
  - `.issac-domain[data-domain-code]` — the domain wrapper.
  - `.issac-item[data-item-code]` — each item article (also has `p-3 mb-3`).
  - `input.btn-check[type="radio"][name="score_{code}"][value="1-5"]` — score inputs
    (Bootstrap btn-check pattern, paired with `<label class="btn btn-outline-primary
    issac-score__btn">`). Has `id="score_{code}_{n}"` and `autocomplete="off"`.
  - `.issac-descriptor--1|3|5` with `.issac-descriptor--active` — highlighting.
  - `.issac-item__status[aria-live="polite"]` — status announcement region (empty).
  - `.issac-domain__progress-sticky` — outer sticky wrapper containing:
    - `.issac-domain__progress.progress` — the Bootstrap progress container.
      - `.issac-domain__progress-bar.progress-bar` — the fill (width via inline style).
    - `.issac-domain__progress-text` — the "X/Y items · Z%" text.
  - **No toast template exists yet** — 5B must create the Bootstrap toast markup
    (either in the template or dynamically in JS).
- `assets/scss/issac.scss` (compiled to `assets/css/issac.css`) — base styles
  including `.issac-item--saving|--saved|--unsaved` class colour hooks (without the
  `::before` checkmark glyph — that belongs to 5B's additions).
  **Bootstrap is loaded by the theme** — the plugin SCSS does not import it.
- Theme-level ISSAC styles live in `themes/issac-bystra/scss/_issac.scss` and
  `themes/issac-bystra/scss/style.scss` compiles to `themes/issac-bystra/style.css`.
  No CodeKit project is active for this repo, so required compiled CSS/JS outputs are
  part of the implementation.

The REST endpoints (from Milestone 4 — confirmed against the code):
- `POST {restUrl}responses` — JSON body `{ item_code, score }`, header `X-WP-Nonce`.
  Returns `{ responses: { "1.1": 3, … }, summary: {…} }`. `400` on invalid
  score/unknown item; `401`/`403` on auth/cap/nonce failure.
- `POST {restUrl}events/check` — no body required (any `domain_code` sent is
  ignored; the server evaluates all milestones). Returns
  `{ new_events: [ { key, toast }, … ] }`.
- `summary.domains` is a **0-based array in tree order, keyed by index not code** —
  locate the current domain by matching `domain.code === issacData.domainCode`.
- A `403` with code `rest_cookie_invalid_nonce` means the REST nonce expired (see
  the stale-nonce handling in §A).

---

## Deliverables

### 1. `assets/js/issac.js`

Vanilla JS. ES2020+ (no IE). No framework, no npm-heavy bundler. Create the plugin
JS source/output expected by `Assets.php` as part of implementation; if a minified
or compiled plugin JS output is added by the plan, generate it before verification.
Single IIFE or module pattern to avoid polluting global scope.

```js
// Available via wp_localize_script:
// window.issacData = { restUrl, nonce, domainCode }
```

#### A. Debounced Autosave (300ms)

- Listen for `change` events on `.issac-item input[type="radio"]` (event delegation
  on `.issac-domain` is fine).
- On change, identify the item (`closest('.issac-item').dataset.itemCode`) and score.
- Per-item debounce: if the same item is changed again within 300ms, cancel the
  previous timer and restart. Different items save independently.
- When the timer fires: set `.issac-item--saving` class, POST to
  `{issacData.restUrl}responses` with JSON body `{ item_code, score }` and headers
  `{ 'Content-Type': 'application/json', 'X-WP-Nonce': issacData.nonce }`.
- **Race handling (out-of-order responses):** keep a per-item "latest score" map.
  When a response returns, if the score it saved no longer matches the item's
  current latest score, **ignore that response** (do not flip the item to saved or
  update the progress bar from it) — a newer save is already in flight and will be
  the authority. This prevents a slow earlier request from clobbering a newer one.
- Do NOT disable the inputs while saving (no `pointer-events: none` on the control).
  The user must always be able to change their answer; a new change simply
  supersedes the in-flight save via the latest-score map above.
- On success (200): remove `--saving`, add `--saved`, set status region text to
  "Saved", clear `--saved` after 2 seconds. Update progress bar (see C).
- On failure (4xx/5xx/network): remove `--saving`, add `--unsaved`, enqueue retry (see B).
- **Stale-nonce handling:** a `403`/`rest_cookie_invalid_nonce` means the page sat
  open long enough for the REST nonce to expire (this is a save-and-resume tool, so
  pages stay open for hours). On a 403, attempt to refresh the nonce ONCE before
  giving up — fetch a fresh one (e.g. `GET {restUrl}` root or a tiny dedicated call,
  or via the WP heartbeat `nonces` payload) and replay the save with the new nonce.
  If it still fails, fall through to the normal retry/unsaved path. A score is never
  silently dropped because a nonce aged out.

#### B. Retry Queue

- Failed saves enter a retry queue: `{ itemCode, score, attempts: 0 }`.
- Retry with exponential backoff: 1s → 2s → 4s (3 attempts max).
- On retry success: normal saved flow.
- On final failure (after 3 attempts): item stays `.issac-item--unsaved`, status
  text says "Save failed — click score again to retry". The score is never silently
  dropped.
- When user clicks a new score on an unsaved item, clear its retry entry and start
  fresh (the new score supersedes).

#### C. Progress Bar Update

- On each successful save, the response includes `summary.domains[]`. Find the
  domain matching `issacData.domainCode`.
- Update `.issac-domain__progress-bar` width to `{completion}%`.
- Update `.issac-domain__progress-text` to `"{answered}/{total} items · {completion}%"`.
- Animate the width transition (CSS handles via `transition: width 0.3s`).

#### D. Milestone Events

- **After every successful save, POST to `{issacData.restUrl}events/check`** (same
  nonce header, no body needed). Do NOT try to pre-decide client-side which
  milestone fired — `EventsEndpoint::check()` already evaluates every condition
  (per-domain 100%, overall ≥50% "halfway", overall 100% "all reviewed") and
  dedupes via a unique key, so it only ever returns genuinely-new events. Mirroring
  that logic in JS would just risk drift (e.g. a missed "halfway" toast).
- Response shape: `{ "new_events": [ { "key": "...", "toast": "..." }, ... ] }`.
  For each entry, display a toast using `entry.toast` as the message.
- Toast component: create a `<div class="issac-toast" role="status" aria-live="polite">`
  appended to `<body>`, auto-dismiss after 5 seconds, slide-in from top-right.
- Multiple toasts stack vertically.

#### E. Descriptor Highlighting (instant, no server round-trip)

- On radio `change` (before save completes), immediately update descriptor highlighting.
- **This mapping MUST match 5A's server-side rule exactly** (tie-break rounds down):
  - Remove `.issac-descriptor--active` from all descriptors in that item.
  - Score 1–2 → add to `.issac-descriptor--1`.
  - Score 3–4 → add to `.issac-descriptor--3`.
  - Score 5 → add to `.issac-descriptor--5`.
- Put this in a single pure function (e.g. `descriptorAnchorForScore(score)` →
  `1 | 3 | 5`) so it is unit-testable (see Tests) and there is one place to keep in
  sync with 5A.
- This is purely cosmetic and happens instantly — the server-rendered class from
  page load is the initial state.

#### F. Initialization

- Run on `DOMContentLoaded`.
- Guard: if `!document.querySelector('.issac-domain')` → exit early (not on a domain page).
- Guard: if `!window.issacData` → exit early (script loaded on wrong page).

### 2. SCSS Additions and Compilation

Add plugin-owned interactive-state SCSS to `assets/scss/issac.scss` and compile it
to `assets/css/issac.css` as part of this block. If an interactive style is actually
theme presentation rather than plugin behaviour, put it in
`themes/issac-bystra/scss/_issac.scss` where possible and compile
`themes/issac-bystra/scss/style.scss` to `themes/issac-bystra/style.css`.

Add these interactive-state styles (the base file from 5A already has the structure).

**Bootstrap context:** The theme loads Bootstrap — use its variables in the plugin SCSS.
Bootstrap's `.toast` component handles the milestone notification UI; do not write a
custom toast from scratch — just trigger Bootstrap's toast API via JS.

**Rule: no user-facing words in CSS.** All status *text* ("Saved", "Save failed…")
is written by JS into `.issac-item__status` (announced via `aria-live`). CSS handles
only colour and a decorative glyph.

```scss
// Saving state — subtle dim (no pointer-events change — see §A race notes)
.issac-item--saving .issac-item__scores { opacity: 0.7; }

// Saved state — use Bootstrap's success variable
.issac-item--saved .issac-item__status {
  color: var(--bs-success);
}
.issac-item--saved .issac-item__status::before {
  content: '✓';
  margin-right: 0.35em;
}

// Unsaved state — use Bootstrap's danger variable
.issac-item--unsaved .issac-item__status {
  color: var(--bs-danger);
}

// Reduced motion
@media (prefers-reduced-motion: reduce) {
  .issac-domain__progress-bar { transition: none; }
}
```

**Milestone toasts via Bootstrap Toast API** (replaces the custom `.issac-toast`):
- Create a hidden Bootstrap toast template in the domain template markup (5A can add
  it) — e.g. a `<div id="issac-toast-template" class="toast" role="status"
  aria-live="polite" aria-atomic="true">` with a `.toast-body` child.
- In JS, clone the template, set the message text, append to `<body>`, and initialise
  with `bootstrap.Toast.getOrCreateInstance(el).show()`.
- This reuses Bootstrap's existing show/hide/aria logic; no animation CSS needed.

### 3. Tests

`issac.js` is enqueued as a **classic WordPress script** (`wp_enqueue_script`), not
an ES module. Do NOT restructure the enqueue into `type="module"` just to make tests
importable — that is scope creep and changes how 5A wired the asset.

#### Primary: Manual test plan (always deliver this)

This is the authoritative verification for 5B — the behaviour is DOM/network/UX
driven and is best confirmed in a browser.

#### Optional: pure-logic unit tests (only if cleanly extractable)

ONLY if you can do it without disturbing the classic-script enqueue: pull the pure
helpers into a sidecar file (e.g. `assets/js/issac-logic.js`) exposed in a way that
both the browser script and a Node test can read, and add
`tests/js/issac-logic.test.mjs` run via `node --test`:

```js
import { test } from 'node:test';
import assert from 'node:assert';

// test: descriptorAnchorForScore(2) === 1, (4) === 3, (5) === 5  (matches 5A)
// test: debounce fires once for rapid calls
// test: backoff intervals are [1000, 2000, 4000]
// test: retry gives up after 3 attempts
```

If extraction would force ES-module loading or otherwise complicate the WP enqueue,
**skip Option A** and rely on the manual plan. Do not over-engineer.

#### Manual test plan file

Create `tests/manual/milestone-5B-test-plan.md`:

```markdown
# Milestone 5B — Manual Test Plan

## Autosave
- [ ] Click a score → Network tab shows POST /responses after ~300ms
- [ ] Rapid clicks 1→2→3→4 on same item → only 1 POST with score=4
- [ ] Click different items rapidly → each fires its own independent POST

## Save feedback
- [ ] Successful save → green checkmark appears on item, fades after 2s
- [ ] During save → score area briefly dims (saving state)
- [ ] Item returns to neutral after saved state clears

## Retry / failure handling
- [ ] DevTools → Network → Offline → click score → item shows unsaved (⚠)
- [ ] Go back online → wait up to 4s → item retries and shows saved
- [ ] Block network for 15+ seconds → item shows "Save failed" permanently
- [ ] Click a new score on a failed item → clears failure, starts fresh save
- [ ] Out-of-order: throttle to slow 3G, click 3 then quickly 5 → final state is 5,
      progress reflects 5 (earlier slow response for 3 is ignored, not clobbering)
- [ ] Stale nonce: leave the page open past nonce lifetime (or manually corrupt
      `issacData.nonce` in console) → click a score → it refreshes the nonce once
      and the save still succeeds (does NOT get stuck unsaved)

## Progress bar
- [ ] Save a score → progress bar width animates to new percentage
- [ ] Text updates to "{answered}/{total} items · {completion}%"
- [ ] Save all items in domain → bar reaches 100%

## Milestone toasts
- [ ] Complete all items in Domain 1 → "Domain completed" toast slides in
- [ ] Toast auto-dismisses after 5 seconds
- [ ] Same toast does NOT appear on subsequent saves (server deduplication)
- [ ] Reach 50% overall → "Halfway there" toast appears

## Descriptor highlighting
- [ ] Click score 1 → first descriptor column highlights immediately
- [ ] Click score 3 → middle descriptor column highlights
- [ ] Click score 5 → last descriptor column highlights
- [ ] Highlight updates before save confirms (instant feedback)

## Persistence
- [ ] Save scores → reload page → all scores still selected (server-rendered)
- [ ] Open two tabs → save in Tab A → reload Tab B → score visible

## Accessibility
- [ ] VoiceOver/NVDA: "Saved" announced after successful save
- [ ] Toast has role="status" — announced by screen reader
- [ ] prefers-reduced-motion: ON → no animations, instant state changes
- [ ] Keyboard: Tab + arrow keys to select score → triggers autosave
```

**Deliver both if possible** — extractable unit tests for logic + manual plan for
integration/UX verification.

### 4. Accessibility

- `.issac-item__status[aria-live="polite"]` text is updated on save success/failure
  → screen readers announce it without interrupting.
- Toast uses `role="status"` (implicit `aria-live="polite"`).
- No information conveyed by colour alone: a decorative glyph plus the JS-written
  status text ("Saved" / "Save failed — click score again to retry") accompany the
  colour states. The text lives in the DOM (not CSS `content`) so screen readers
  announce it via the `aria-live` region.

---

## Conventions

- Vanilla JS — no framework, no npm, no TypeScript, no bundler.
- No CodeKit project is active for this repo. The implementation must create and
  compile the required plugin SCSS/JS outputs, and compile any theme SCSS changes
  through `themes/issac-bystra/scss/style.scss` to `themes/issac-bystra/style.css`.
- REST calls include `X-WP-Nonce` header from `issacData.nonce`.
- Never compute scores/percentages client-side — always trust the server response
  values from `summary`.
- Minimalistic code. A single well-structured file, not over-engineered abstractions.
- All user-facing text is in the JS (toasts, status messages) — these could be
  localised later but for Stage 1, English hardcoded is fine.

---

## Manual Verification Checklist (Quick Smoke Test)

1. Click a score → POST appears in Network tab after ~300ms delay.
2. Rapid clicks on one item → only the final score is sent.
3. Checkmark appears after save, fades after 2s.
4. Progress bar animates to new value.
5. Descriptor column highlighting switches instantly on click.
6. Offline mode → score flagged unsaved → back online → auto-retries.
7. All items answered in domain → toast slides in once.
8. Page reload → all saved scores still checked.
9. Screen reader announces "Saved" after each autosave.
10. `prefers-reduced-motion` → no animations.
11. Corrupt `issacData.nonce` in console → save still succeeds (nonce refreshed once).

---

## What This Block Does NOT Include (already done in 5A)

- Shortcode registration and access gate logic.
- Template HTML structure and server-rendered pre-selection.
- Base SCSS (layout, score buttons, descriptor grid, progress bar structure).
- `Assets.php` and `Plugin.php` wiring.
- PHP integration tests for rendering.
