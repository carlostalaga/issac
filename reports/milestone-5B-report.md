# Milestone 5B — Client-Side Autosave & Interactivity — Completion Report

**Milestone:** 5B (Domain page — interactivity)
**Spec:** `prompts/milestone-5B-client-interactivity.md`
**Contract:** `ISSAC-development-plan.md` §6.5 (shared 5A/5B DOM & data contract)
**Status:** Complete — verified by unit tests + PHP lint; manual browser plan delivered
**Date:** 2026-06-19

---

## 1. Summary

5B layers JavaScript interactivity onto the JS-free domain page from 5A. Clicking a
score now autosaves to the REST API with no save button, the sticky progress bar and
descriptor highlight update live, milestone toasts fire via Bootstrap, and failed
saves retry with backoff. The server-rendered baseline from 5A remains the source of
truth — the client never computes scores or percentages.

A mid-session bug (assets not loading on the live page) was diagnosed and fixed: the
shortcode renders through a page template, so `has_shortcode()` could not detect it
and neither the CSS nor JS were enqueued. The enqueue strategy was reworked to a
register-then-enqueue-from-shortcode pattern.

---

## 2. Deliverables

| # | Deliverable | File | Status |
|---|---|---|---|
| 1 | Autosave + interactivity script | `assets/js/issac.js` | Done |
| 2 | Interactive-state SCSS (saved glyph) | `assets/scss/issac.scss` → `assets/css/issac.css` | Done (compiled via `sass`) |
| 3 | Bootstrap toast template | `src/Frontend/templates/domain.php` | Done |
| 4 | Pure-logic helpers (extracted) | `assets/js/issac-logic.mjs` | Done |
| 5 | JS unit tests | `tests/js/issac-logic.test.mjs` | Done — 6/6 pass |
| 6 | Manual test plan | `tests/manual/milestone-5B-test-plan.md` | Done |
| 7 | Asset enqueue fix | `src/Frontend/Assets.php`, `src/Frontend/Shortcodes.php` | Done |

---

## 3. Feature coverage (against spec §A–F)

| Spec section | Behaviour | Implementation |
|---|---|---|
| **A. Debounced autosave** | 300 ms per-item debounce; POST `/responses` with `X-WP-Nonce` | `init()` change delegation + `debounceTimers` map |
| **A. Race handling** | Out-of-order responses ignored | `latestScores` map; result discarded if score no longer current |
| **A. No input disabling** | User can always re-pick | Inputs never disabled; only `.issac-item__scores` dims via CSS |
| **A. Stale-nonce** | Refresh once on 403, replay save | `refreshNonce()` reads `_nonce` from WP REST index |
| **B. Retry queue** | Backoff 1s→2s→4s, 3 attempts, then permanent "click to retry" | `enqueueRetry()` / `scheduleRetry()` |
| **C. Progress bar** | Width + text from `summary.domains[]` matched by `code` | `updateProgressBar()`; CSS animates width |
| **D. Milestone events** | POST `/events/check` after each save; server dedupes | `checkMilestoneEvents()` → `showToast()` |
| **D. Toasts** | Bootstrap Toast, auto-dismiss 5 s, stack top-right | Clones `#issac-toast-template` into a fixed container |
| **E. Descriptor highlight** | Instant on change; 1–2→1, 3–4→3, 5→5 (rounds down) | `descriptorAnchorForScore()` (matches 5A PHP) |
| **F. Initialization** | Guards for `.issac-domain` and `window.issacData`; readyState-safe | `init()` + `document.readyState` guard |

---

## 4. Deviations from plan (recorded in `ISSAC-development-plan.md`)

1. **Asset enqueue strategy** — `has_shortcode()` gating replaced with
   `Assets::registerAssets()` (register + localize) plus `Assets::enqueueAll()` called
   from `Shortcodes::renderDomain()`. Root cause of the "buttons don't save" symptom.
2. **Nonce refresh mechanism** — now explicitly: `GET /wp-json/` to read a fresh
   `_nonce` (no dedicated endpoint exists or is needed).
3. **Toast template ownership** — added in 5B (the contract permitted either block).
4. **`readyState` init guard** — footer script may run after `DOMContentLoaded`.
5. **`ISSAC_VERSION` bump 0.1.0 → 0.2.0** — cache-bust the stale 5A stub.
6. **SCSS compiled via `sass` CLI** — no CodeKit project covers this plugin path.
7. **JS unit tests added** — helpers extracted cleanly to `issac-logic.mjs` without
   disturbing the classic-script enqueue, so the optional Option-A tests were delivered.

The plugin structure tree in §4 and the §6.5 contract note were updated to match.

---

## 5. Verification

**Automated**
- `node --test tests/js/issac-logic.test.mjs` → 6 passing
  (`descriptorAnchorForScore` 2→1/4→3/5→5/0→0; `retryDelay` [1000,2000,4000] then null).
- `php -l` clean on `Assets.php`, `Shortcodes.php`, `templates/domain.php`.

**Manual (browser)** — full checklist in `tests/manual/milestone-5B-test-plan.md`.
Quick smoke: score click → POST after ~300 ms; rapid clicks → single POST with final
score; checkmark fades after 2 s; progress bar animates; descriptor highlight switches
instantly; offline → unsaved → online → auto-retry; domain 100% → toast once; reload →
scores persist; corrupt nonce → refreshes once and still saves.

> Note: the descriptor highlight was confirmed working in-browser during the session
> (proving the change handler binds); end-to-end POST verification should be re-run
> after a hard refresh now that the enqueue fix and version bump are in place.

---

## 6. Accessibility

- `.issac-item__status[aria-live="polite"]` receives JS-written "Saved" / "Save failed…"
  text (never via CSS `content`) so screen readers announce state.
- Toasts use `role="status"` (implicit polite live region).
- No colour-only signalling: decorative `✓` glyph + status text accompany colour.
- `prefers-reduced-motion` disables progress/descriptor transitions.
- Real radio inputs retain native keyboard operation.

---

## 7. Follow-ups / notes for later milestones

- **Dashboard (M6)** reuses the same toast template and `issacData` contract — keep
  `Assets::enqueueAll()` callable from the dashboard shortcode too.
- **Security checklist (§10)** "output escaping everywhere" stays unchecked until the
  dashboard, admin and PDF surfaces are built; the domain template already escapes all
  output.
- Consider a tiny dedicated REST route for nonce refresh if the WP index response shape
  ever changes; current approach relies on core's `_nonce` field.
