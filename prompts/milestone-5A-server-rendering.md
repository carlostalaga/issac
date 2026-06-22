# Milestone 5A — Server-Rendered Domain Page

> **Part 1 of 2.** This block builds the PHP server-rendering layer. Block 5B
> (`prompts/milestone-5B-client-interactivity.md`) adds the JavaScript autosave,
> retry queue, progress updates, and milestone toasts on top of what 5A delivers.
>
> **Do NOT implement any JavaScript behaviour in this block.** The template must
> work fully without JS (progressive enhancement). All interactive features belong
> to 5B.

---

## Context

Milestones 0–4 are complete. The REST API, `InstrumentRepository`, `ScoringService`,
and all repositories are working. This block creates the PHP frontend layer that
renders the domain assessment page with pre-selected scores from the database.

### Verified facts about the existing code (do not re-derive — these are confirmed)

- `InstrumentRepository::tree(): DomainNode[]` — ordered by `menu_order`.
- `DomainNode`: `->id, ->code (string "1".."5"), ->title, ->description, ->menuOrder, ->subsections (SubsectionNode[])`.
- `SubsectionNode`: `->id, ->title, ->menuOrder, ->domainId, ->items (ItemNode[])`.
- `ItemNode`: `->id, ->itemCode (string "1.1".."5.18"), ->label, ->prompt (plain textarea → use esc_html), ->descriptor1/3/5 (wysiwyg → use wp_kses_post), ->isActive (bool), ->menuOrder, ->subsectionId`.
- `AssessmentRepository::findOrCreate(int $userId): object` (lazily creates the in_progress row); `currentFor(int $userId): ?object`.
- `ResponseRepository::forAssessment(int $assessmentId): array` → `['1.1' => 3, ...]` (item_code => int score).
- `ScoringService::summary(array $tree, array $responses): array` — returns:
  - `['overall' => ['completion'=>float, 'average'=>?float, 'band'=>string, 'answered'=>int, 'total'=>int]]`
  - `['domains' => [ ['code'=>string, 'title'=>string, 'completion'=>float, 'average'=>?float, 'band'=>string, 'answered'=>int, 'total'=>int, 'subsections'=>[...] ], ... ]]`
  - **IMPORTANT: `domains` is a 0-based numerically-indexed array in tree order, NOT keyed by domain code.** To get one domain's row you must match on `['code']`, not `$summary['domains'][$code]`.
- `Capabilities::TAKE_ASSESSMENT` constant = `'issac_take_assessment'`.

### Manual prerequisites (not code — for the human testing this)

- A WordPress **page must exist** that contains the `[issac_domain]` shortcode
  (e.g. at `/assessment/domain/`). The shortcode renders nothing until such a page
  exists and is visited with `?d=1`.
- No CodeKit project is active for this repo. Any required SCSS/JS compilation is
  part of this implementation block and the compiled output must be created before
  manual verification.
- Theme-level ISSAC styles live in `themes/issac-bystra/scss/_issac.scss` where
  possible. Ensure `themes/issac-bystra/scss/style.scss` imports/uses that partial,
  then compile `themes/issac-bystra/scss/style.scss` to
  `themes/issac-bystra/style.css`.
- Plugin-owned SCSS/JS still belongs in the plugin asset structure described below;
  create the source files and compiled/enqueued files as part of the milestone.

---

## Deliverables

### 1. `src/Frontend/Shortcodes.php`

- Namespace `Issac\Frontend`.
- Static `register()` method that adds shortcodes via `add_shortcode`.
- Register `[issac_domain]` shortcode.
- **Access gate — do NOT use `wp_redirect()` here.** A shortcode runs during the
  `the_content` filter, when headers are already sent, so `wp_redirect()` fails
  silently. Instead, if the user is not logged in OR lacks
  `Capabilities::TAKE_ASSESSMENT`, **return** a short markup string with a login
  link: `wp_login_url(get_permalink())`. (e.g. "Please log in to take the
  assessment." + link). Return early — render nothing else.
- Read `$_GET['d']` (sanitized via `sanitize_text_field`), validate it's a known
  domain code from `InstrumentRepository::tree()`.
- If invalid/missing domain code → return a clear user-facing error message string.
- Resolve the current user's assessment via `AssessmentRepository::findOrCreate(get_current_user_id())`.
- Load responses via `ResponseRepository::forAssessment((int) $assessment->id)`.
- Compute the full summary via `ScoringService::summary($tree, $responses)`, then
  **extract this domain's row** by matching `code` (the `domains` array is indexed
  0..4 in tree order, not keyed by code):
  ```php
  $domainSummary = null;
  foreach ($summary['domains'] as $row) {
      if ($row['code'] === $domain->code) { $domainSummary = $row; break; }
  }
  ```
- `ob_start()` / `include templates/domain.php` / `return ob_get_clean()`.
- Pass to template: `$domain` (the single DomainNode for `?d=`), `$responses`
  (item_code → score map), `$domainSummary` (this domain's summary row — the
  template uses its `completion`, `answered`, `total` for the progress bar).

### 2. `src/Frontend/Assets.php`

- Namespace `Issac\Frontend`.
- Static `register()` method hooking `wp_enqueue_scripts`.
- Enqueue `issac-css` → `assets/css/issac.css` (compiled from plugin SCSS during
  implementation).
- Enqueue `issac-js` → `assets/js/issac.js`.
  - **Create an empty placeholder `assets/js/issac.js` in this block** (a one-line
    comment is fine). `wp_localize_script()` must attach to a real enqueued handle,
    and a placeholder avoids a 404 for the script. 5B fills this file in.
- Conditional: only enqueue on pages that actually use the shortcode. Use
  `has_shortcode(get_post()->post_content ?? '', 'issac_domain')` (guard for the
  no-post case). Do not enqueue site-wide.
- `wp_localize_script('issac-js', 'issacData', [...])` with:
  - `restUrl` → `esc_url_raw(rest_url('issac/v1/'))`
  - `nonce` → `wp_create_nonce('wp_rest')`
  - `domainCode` → current domain code from `$_GET['d']` (sanitized) or `null`

### 3. `src/Frontend/templates/domain.php`

Server-rendered template. Variables in scope: `$domain` (single DomainNode),
`$responses` (item_code => score), `$domainSummary` (this domain's summary row).

```html
<div class="issac-domain" data-domain-code="<?= esc_attr($domain->code) ?>">

  <header class="issac-domain__header">
    <h1><?= esc_html($domain->title) ?></h1>
    <div class="issac-domain__description"><?= wp_kses_post($domain->description) ?></div>
  </header>

  <!-- aria-valuenow / width / text all from $domainSummary -->
  <div class="issac-domain__progress" role="progressbar"
       aria-valuenow="<?= (int) round($domainSummary['completion']) ?>"
       aria-valuemin="0" aria-valuemax="100">
    <div class="issac-domain__progress-bar"
         style="width: <?= esc_attr($domainSummary['completion']) ?>%"></div>
    <span class="issac-domain__progress-text">
      <?= (int) $domainSummary['answered'] ?>/<?= (int) $domainSummary['total'] ?>
      items · <?= esc_html($domainSummary['completion']) ?>%
    </span>
  </div>

  <!-- For each subsection -->
  <section class="issac-subsection">
    <h2 class="issac-subsection__title">{title}</h2>

    <!-- For each active item in subsection -->
    <article class="issac-item" data-item-code="{item_code}">
      <div class="issac-item__prompt">
        <span class="issac-item__code"><?= esc_html($item->itemCode) ?></span>
        <?= esc_html($item->prompt) ?>   <!-- prompt is a plain textarea: esc_html, NOT wp_kses_post -->
      </div>

      <fieldset class="issac-item__scores">
        <legend class="screen-reader-text">Score for item {item_code}</legend>
        <!-- For $score = 1 to 5 -->
        <label class="issac-score">
          <input type="radio" name="score_{item_code}" value="{score}"
                 <?php checked($responses[$item->itemCode] ?? 0, $score); ?>>
          <span class="issac-score__btn">{score}</span>
        </label>
      </fieldset>

      <div class="issac-item__descriptors">
        <div class="issac-descriptor issac-descriptor--1 {--active if score 1-2}">
          <strong class="issac-descriptor__label">Exploring</strong>
          <div class="issac-descriptor__text">{wp_kses_post(descriptor_1)}</div>
        </div>
        <div class="issac-descriptor issac-descriptor--3 {--active if score 3-4}">
          <strong class="issac-descriptor__label">Implementing</strong>
          <div class="issac-descriptor__text">{wp_kses_post(descriptor_3)}</div>
        </div>
        <div class="issac-descriptor issac-descriptor--5 {--active if score 5}">
          <strong class="issac-descriptor__label">Sustained Action</strong>
          <div class="issac-descriptor__text">{wp_kses_post(descriptor_5)}</div>
        </div>
      </div>

      <div class="issac-item__status" aria-live="polite"></div>
    </article>
  </section>

</div>
```

**Descriptor highlighting logic (server-side) — SHARED CONTRACT with 5B.**
Descriptors only exist at anchors 1, 3, 5. Scores 2 and 4 are equidistant between
anchors; the tie-break rounds **down** to the lower anchor. **5B replicates this
exact mapping in JS — the two must stay identical:**
- No score → no descriptor highlighted.
- Score 1–2 → `.issac-descriptor--1` gets `.issac-descriptor--active`.
- Score 3–4 → `.issac-descriptor--3` gets `.issac-descriptor--active`.
- Score 5 → `.issac-descriptor--5` gets `.issac-descriptor--active`.

**Data attributes for 5B to consume:**
- `.issac-domain[data-domain-code]` — identifies the current domain.
- `.issac-item[data-item-code]` — identifies each item for JS targeting.

### 4. Styles and compilation

#### Theme SCSS: `themes/issac-bystra/scss/_issac.scss` → `themes/issac-bystra/style.css`

- Put new styles that are part of the Bystra theme presentation in
  `themes/issac-bystra/scss/_issac.scss` where possible.
- If it is not already wired, add `@use 'issac';` to
  `themes/issac-bystra/scss/style.scss`.
- Compile `themes/issac-bystra/scss/style.scss` to
  `themes/issac-bystra/style.css` as part of the implementation. Do not leave this
  for CodeKit or manual follow-up.

#### Plugin SCSS: `assets/scss/issac.scss` → `assets/css/issac.css`

Create the source file and compile it during implementation: source =
`assets/scss/issac.scss`, output = `assets/css/issac.css`. `Assets.php` enqueues
`assets/css/issac.css`.

**Bootstrap context:** The Bystra Issac theme loads Bootstrap. Do NOT `@import`
Bootstrap inside this file — it is already on the page. Use Bootstrap utility classes
in the template markup wherever possible and write SCSS only for what Bootstrap
cannot provide.

Bootstrap components and utilities to use **in the template markup** (no plugin CSS needed):
- `.progress` / `.progress-bar` — domain completion bar.
- `.btn-check` + `.btn.btn-outline-*` — score radio buttons (Bootstrap's built-in
  radio-button styling pattern).
- `visually-hidden` — replaces `screen-reader-text` in fieldset legends.
- `.row` / `.col-md-4` — descriptor 3-column layout on desktop.
- Spacing utilities (`p-*`, `gap-*`) for item card padding.
- Bootstrap CSS variables `--bs-success`, `--bs-danger` for save-state colours.
- Bootstrap's `.toast` component for milestone notifications (5B builds on this;
  no custom toast CSS needed).

What to write in `issac.scss` (plugin-specific only):
- **Score selector:** sizing and spacing for the 1–5 `.btn-check` radio group;
  the circular/pill shape if Bootstrap's default rectangle doesn't fit the design.
- **Descriptor active state:** `.issac-descriptor--active` — background tint +
  left border accent (layout comes from `.col-*` in markup).
- **Save state colours:**
  ```scss
  .issac-item--saved   .issac-item__status { color: var(--bs-success); }
  .issac-item--unsaved .issac-item__status { color: var(--bs-danger);  }
  .issac-item--saving  .issac-item__scores { opacity: 0.7; }
  ```
- **Sticky progress wrapper:** `position: sticky; top: 0; z-index: 100;` on
  `.issac-domain__progress-sticky`.
- **`prefers-reduced-motion`:** disable transitions on the progress bar.
- Keep the file short — if Bootstrap already handles it, don't duplicate it.

### 5. Wire into `Plugin.php`

Add to `Plugin::boot()`:

```php
\Issac\Frontend\Shortcodes::register();
\Issac\Frontend\Assets::register();
```

### 6. Tests

**Do NOT scaffold a WordPress PHPUnit integration harness** — this repo has none
(no `wp-tests` bootstrap), and setting one up is out of scope. The existing test
convention is a pure-PHP unit suite (`tests/Unit/ScoringServiceTest.php`) plus a
`wp eval-file` smoke script (`tests/rest-smoke.php`). **Follow that pattern.**

Create `tests/render-smoke.php`, runnable via `wp eval-file tests/render-smoke.php`,
that:
1. `wp_set_current_user()` to a user WITH `issac_take_assessment` (otherwise the
   access gate returns the login prompt and there is nothing to assert).
2. Sets `$_GET['d'] = '1'`, calls `do_shortcode('[issac_domain]')`, captures output.
3. Asserts, printing PASS/FAIL per check:
   - **Valid domain:** output contains `.issac-domain`, `.issac-item`, `type="radio"`,
     and the descriptor divs.
   - **Pre-selected scores:** after upserting a response via `ResponseRepository`,
     the matching radio carries `checked`.
   - **Inactive items excluded:** an `is_active = false` item's code is absent.
   - **Invalid domain:** `$_GET['d'] = '99'` yields the error string, not a fatal.
   - **Escaping:** a prompt/descriptor containing `<script>` renders escaped.

Keep it to plain `assert()`/echo PASS·FAIL like `tests/rest-smoke.php` — no new
test framework.

---

## Conventions

- PHP 8.1+, PSR-12, namespace `Issac\Frontend`.
- Read instrument ONLY via `InstrumentRepository::tree()` — never `get_field()`.
- Escape ALL output: `esc_html`, `esc_attr`, `wp_kses_post` for wysiwyg descriptors.
- No form plugins. Keep build tooling minimal and local to the existing plan; the
  implementation must still produce the compiled theme CSS and plugin CSS/JS assets
  because CodeKit is not active for this project.
- Security: resolve user from `get_current_user_id()`, never from request input.
- Code style: minimalistic, readable. No elaborate defensive fallbacks.

---

## Manual Verification Checklist

After implementation, verify these by hand in the browser:

1. **Happy path:** Visit `/assessment/domain/?d=1` logged in as a participant →
   see Domain 1 title, description, all subsections with items, score buttons 1–5,
   and three descriptor columns per item.
2. **Pre-selected scores:** Use the `.http` files (or REST client) to save some
   scores first, then reload the page → those scores appear checked.
3. **Descriptor highlight:** Items with existing scores show the matching descriptor
   column highlighted.
4. **Invalid domain:** Visit `?d=6` → clear error message (not a white screen).
5. **Unauthenticated:** Visit while logged out → see the "Please log in" prompt with
   a working login link (NOT a redirect — shortcodes can't redirect).
6. **Missing cap:** Log in as a user WITHOUT `issac_take_assessment` → same prompt.
7. **Mobile layout:** Resize to 375px width → descriptors stack vertically, score
   buttons remain in a row.
8. **Keyboard:** Tab through the page → focus rings visible on score buttons,
   arrow keys change selection within a fieldset.
9. **View source:** Confirm `esc_html`/`esc_attr` wrapping (no raw `<script>` injection
   possible in prompts or descriptors).
10. **Console clean:** No 404 for `issac.js`; no CSS 404s for the plugin stylesheet
    or the compiled theme `style.css`.
11. **`issacData` present:** In the console, `window.issacData` exists with
    `restUrl`, `nonce`, and the correct `domainCode`.

---

## Implementation Notes (completed)

Minor deviations from the pseudocode in §3 above:

1. **Progress bar wrapper:** Added a `<div class="issac-domain__progress-sticky">` outer
   wrapper around the progress bar and text. The spec's pseudocode had the sticky class
   directly on the `role="progressbar"` div, but separating the sticky container from
   the Bootstrap `.progress` element is cleaner. 5B targets
   `.issac-domain__progress-bar` (for width) and `.issac-domain__progress-text` (for
   text) — both exist unchanged.

2. **Bootstrap `.btn-check` pattern:** The radio inputs use `class="btn-check"` with
   paired `<label class="btn btn-outline-primary issac-score__btn">` — Bootstrap's
   standard radio-as-button pattern. The `name="score_{item_code}"` and `value`
   attributes match the selectors 5B expects.

3. **No toast template:** 5B §2 mentions "5A can add" a hidden Bootstrap toast template
   to the domain markup. We did NOT add it here — 5B will create it when implementing
   the toast logic, since it makes more sense to co-locate the template with the JS
   that consumes it.

4. **Compiled CSS includes `::before` glyph placeholder:** The plugin SCSS from 5A does
   NOT include the `.issac-item--saved .issac-item__status::before { content: '✓' }`
   rule — that belongs to 5B's interactive-state additions.

---

## What This Block Does NOT Include (belongs to 5B)

- `assets/js/issac.js` — autosave, debounce, retry queue.
- Save/unsaved visual state transitions (JS-driven).
- Progress bar live updates after saving.
- Milestone toast notifications.
- `POST /responses` calls from the client.
- `POST /events/check` calls from the client.
