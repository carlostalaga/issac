# Add Descriptors for Scores 2 and 4

Read `@ISSAC-development-plan.md` and `@.cursor/rules/issac.mdc` first, then:

Expand the instrument's descriptor model from three anchors (1/3/5) to all five
(1–5). Build the empty structure now — the descriptor **text** for 2 and 4 is
authored later in wp-admin (never generated from memory) — and switch the
frontend highlight from round-down-to-anchor to **exact per-score matching**
(1→1 … 5→5).

> Enhancement to the Milestone 5 domain page (`§6.3`/`§6.5`) and the content
> model (`§2.1`). The source DOCX has no descriptor paragraphs for scores 2 and
> 4; those fields ship empty and are filled in via the WP admin editors.

---

## Decisions (confirmed with client)

- **Structure only**: add empty `descriptor_2` / `descriptor_4` fields. Do NOT
  invent descriptor text — content is authored later in wp-admin.
- **Five columns, exact highlight**: render all five descriptors as equal columns
  aligned under the score buttons; each score highlights its own descriptor,
  replacing the current round-down rule.
- Columns 2 and 4 have no band name in the source, so they render without a text
  label (the numbered score buttons already identify them). Empty descriptor text
  is expected until authored.

---

## Context — what already exists (3-anchor model)

The whole stack deliberately supports descriptors only at anchors 1, 3, 5:

- ACF: `descriptor_1/3/5` in `plugins/issac-assessment/acf-json/group_issac_item.json`
- Value object: `ItemNode` (`descriptor1/3/5`)
- Read path: `InstrumentRepository::toItem()`
- Frontend: `templates/domain.php` renders three `col-md-4` columns; the highlight
  rounds a score **down** to the nearest anchor (1–2→1, 3–4→3, 5→5)
- JS: `issac.js` / `issac-logic.mjs` `descriptorAnchorForScore()` (same round-down rule)
- Importer + `data/instrument-2023.06.json` carry only `descriptor_1/3/5`
- Tests: `issac-logic.test.mjs`, `render-smoke.php` assert the round-down behaviour
- The PDF report renders only scores/bands (no descriptor text) — **unaffected**.

---

## Changes

### Data model

- **`acf-json/group_issac_item.json`**: insert `field_issac_item_descriptor_2`
  (after descriptor_1) and `field_issac_item_descriptor_4` (after descriptor_3),
  both `wysiwyg`, mirroring the existing descriptor field config. Update the
  `descriptor_5` instruction to drop "Scores 2 and 4 are valid midpoints with no
  descriptor paragraph". **Bump `"modified"` to the current Unix timestamp**
  (`date +%s`) so ACF offers Sync.
- **`src/Domain/ItemNode.php`**: add `descriptor2` and `descriptor4` readonly
  properties (ordered 1,2,3,4,5); update the class docblock.
- **`src/Domain/InstrumentRepository.php`**: in `toItem()`, read `descriptor_2`
  and `descriptor_4` and pass them to `ItemNode`.

### Import path

- **`src/Content/Importer.php`**: in `importItem()` (both the create and
  `--update-text` branches) set `descriptor_2` / `descriptor_4` using
  `$data['descriptor_2'] ?? ''` so the existing JSON (which omits them) imports
  cleanly.
- **`data/instrument-2023.06.json`**: no change required — the importer defaults
  the missing keys to empty. Content is added in wp-admin.

### Frontend

- **`src/Frontend/templates/domain.php`**:
  - Change `$activeDescriptor` from the round-down `match` to exact: the
    descriptor equal to `$currentScore` (1–5), else 0.
  - Replace the three `col-md-4` columns with a five-column row
    (`row row-cols-1 row-cols-md-5`, each child `col issac-descriptor
    issac-descriptor--N`), adding `--2` and `--4`. Keep labels "Exploring" (1),
    "Implementing" (3), "Sustained Action" (5); leave 2 and 4 label-less.
- **`assets/js/issac.js`** and **`assets/js/issac-logic.mjs`**: change
  `descriptorAnchorForScore()` to return the score itself for 1–5 (0 otherwise).
  The existing `updateDescriptors()` selector `.issac-descriptor--` + anchor then
  targets the exact column.
- **`assets/scss/issac.scss`**: no change — active styling is the generic
  `.issac-descriptor--active`; layout is Bootstrap utility classes. No CodeKit
  recompile needed.

### Tests

- **`tests/js/issac-logic.test.mjs`**: update expectations to exact mapping
  (2→2, 4→4).
- **`tests/render-smoke.php`**: the "score 4" assertion now expects
  `issac-descriptor--4 issac-descriptor--active` (update regex + comment).

### Docs

- **`ISSAC-development-plan.md`**: update the `issac_item` field list in §2.1, the
  domain-page descriptor sketch in §6.3, and the descriptor tie-break note in §6.5
  to describe five descriptors with exact per-score highlighting. Add a short
  "Planned vs Actual"-style note recording the move from 3-anchor to 5-anchor.

---

## Verification

- Run `node --test` on the JS logic tests.
- Run `wp eval-file wp-content/plugins/issac-assessment/tests/render-smoke.php --user=1`.
- In wp-admin: the ACF Item field group shows the Sync prompt; sync it; confirm
  two new descriptor editors appear.
- On `/assessment/domain/?d=1`: five columns render (2 and 4 empty until
  authored), and clicking each score highlights the matching column.
