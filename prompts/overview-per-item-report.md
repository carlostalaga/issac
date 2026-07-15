# Overview — Per-Item Statistics Report

Read `@ISSAC-development-plan.md` and `@.cursor/rules/issac.mdc` first, then:

Add a **Per-Item Statistics** table to the admin Overview page, below the existing
per-domain table, showing aggregate stats for each of the 69 instrument items.

> This is a small enhancement to the Milestone 8 Overview page (`§9` of the plan).
> It mirrors the existing per-domain table but breaks results down to the item level
> so admins can see which individual items score low or are frequently skipped.

---

## Context — what already exists

`src/Admin/OverviewPage.php` renders:

1. Four headline stat cards (participants, in progress, completed, PDFs).
2. A **Per-Domain Statistics** table with columns: Domain, Avg Completion %,
   Avg Score, Participants Answered.

The per-domain table runs 3 SQL queries **per domain** (15 total) against
`{prefix}issac_responses`, each filtered by `item_code IN (...)` for that domain's
active items. Data source is aggregate SQL on the custom tables; domain metadata
(codes, titles, item codes) comes from `InstrumentRepository::tree()`.

Relevant schema — `{prefix}issac_responses`:

```
id, assessment_id, item_code, score, updated_at
UNIQUE KEY assessment_item (assessment_id, item_code)
KEY item_code (item_code)
```

The `UNIQUE KEY (assessment_id, item_code)` is critical to the design below.

---

## What to build

A new **Per-Item Statistics** table below the per-domain table.

### Columns (only these three — no redundant data)

| Column | Source | Notes |
|--------|--------|-------|
| Item | `itemCode . '. ' . label` from `ItemNode` | truncate long labels |
| Avg Score | `ROUND(AVG(score), 1)` | `—` if no responses |
| Answered | `COUNT(*)` | how many participants scored this item |

**Deliberately excluded, to avoid redundant data:**

- **No "Domain" column.** Rows are grouped under a domain header row (below), so a
  per-row domain column would just repeat it.
- **Only one count column.** Because of `UNIQUE KEY (assessment_id, item_code)`, each
  assessment can answer a given item at most once, so for a single item
  `COUNT(*)` and `COUNT(DISTINCT assessment_id)` are always equal — a second count
  column would print identical numbers. (They legitimately differ at the *domain*
  level because a domain spans many items, which is why the per-domain table keeps
  both.)
- **No "Avg Completion %".** At item level an item is simply answered or not; the
  Answered count already carries that.

### Visual grouping

69 rows in a flat table is hard to scan. Group rows by **domain** using a spanning
header row (`<tr>` with a `<th colspan="3">`) before each domain's items. This
mirrors the instrument structure and matches how the per-domain table lists domains.

---

## Implementation — single file change

All changes go in `src/Admin/OverviewPage.php`.

### 1. `gatherStats()` — add item-level aggregation

After the per-domain loop, run **one** aggregate query (not N+1):

```sql
SELECT item_code,
       ROUND(AVG(score), 1) AS avg_score,
       COUNT(*)             AS answered
FROM {$responses}
GROUP BY item_code
```

Index the result rows by `item_code`. Then walk the instrument tree
(domains → subsections → active items), building an `items` structure grouped by
domain, pulling each item's stats from the indexed results (items with zero
responses fall back to `—` / `0`). Return `items` alongside `counters` and
`domains`.

This query has no user input to interpolate, but keep it consistent with the file's
style. Cast counts with `(int)` and treat a null `avg_score` as `—`.

### 2. `render()` — add the table

After the per-domain table:

- Heading: "Per-Item Statistics".
- `<table class="issac-item-table widefat striped">` with columns: Item, Avg Score,
  Answered.
- A domain group header row (`<th colspan="3">` with `code . '. ' . title`) before
  each domain's items.
- One row per active item; show `—` for items with no responses.
- Empty-state row if there are no items.

Escape all output (`esc_html`).

### 3. Inline CSS

Add `.issac-item-table` styles (reuse the `.issac-domain-table` pattern already in
the same `<style>` block) and a `.issac-group-header` rule for the domain separator
rows.

---

## Conventions (from `.cursor/rules/issac.mdc`)

- PHP 8.1+, PSR-12, namespace `Issac\Admin`.
- Instrument content ONLY via `InstrumentRepository::tree()` — no scattered
  `get_field()` calls.
- Totals computed live from the tree — never hard-coded.
- All SQL through `$wpdb->prepare()` where there is input; escape ALL output.
- Minimalistic, readable code — no defensive fallback chains.

---

## Manual verification

1. Overview page shows the new Per-Item Statistics table below the per-domain table.
2. Items are grouped under their domain headers, in tree order.
3. Avg Score matches a hand check for a couple of items; unanswered items show `—`
   and `0`.
4. "Answered" equals the number of distinct participants who scored the item.
5. Fresh install (no responses): every item shows `—` / `0`, no PHP errors.
