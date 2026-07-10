# ISSAC scoring — client presentation brief

## How scores work (30-second version)

Each practice item is rated **1–5**. ISSAC then shows:

1. **Completion** — how much of the instrument has been answered
2. **Maturity average** — the mean of those answers
3. **Band** — Exploring / Implementing / Sustained Action

Scores roll up at **subsection**, **domain**, and **overall** levels. There is no weighting: every answered item counts equally. Unanswered items do not count as zero — they are simply left out of the average (and lower the completion %).

---

## The instrument

- **5 domains** → **18 subsections** → **69 items**
- Item codes are stable (`1.1` … `5.18`)
- Domain sizes differ (e.g. Domain 1 has 9 items; Domain 3 has 22), so a large domain can pull the overall average more when more of its items are answered

---

## The 1–5 scale and bands

| Score | Meaning (anchors) |
|-------|-------------------|
| 1 | Exploring |
| 3 | Implementing |
| 5 | Sustained Action |
| 2 / 4 | Valid midpoints between anchors |

**Maturity bands** (from the rounded average):

| Average | Band |
|---------|------|
| No answers yet | Not yet rated |
| Below 2.5 | Exploring |
| 2.5 – under 4.0 | Implementing |
| 4.0 and above | Sustained Action |

Averages are rounded to **one decimal place** before banding.

---

## What is computed

For overall, each domain, and each subsection:

- **Completion %** = answered active items ÷ active items × 100
- **Average** = mean of answered active scores only
- **Band** = from that average

**Important product points for the client:**

- Blanks are not zeros — partial progress still shows a real maturity signal from what has been rated
- Inactive items are excluded from scoring (content can be retired without breaking history)
- Completing an assessment does not require 100% of items answered
- The UI always uses the server summary — one source of truth for dashboard, domain pages, and PDF

---

## Scenario 1 — Early progress (honest partial picture)

**Situation:** A school has just started. They rate one item in Domain 1 as **4**.

| Level | Completion | Average | Band |
|-------|------------|---------|------|
| Overall | ~1.4% (1 of 69) | 4.0 | Sustained Action |
| Domain 1 | ~11% (1 of 9) | 4.0 | Sustained Action |
| Other domains | 0% | — | Not yet rated |

**Talking point:** Completion shows they have barely started; the average reflects only what they have rated so far. High early averages are expected and not “gaming” — the completion bar keeps context honest.

---

## Scenario 2 — Mixed maturity across domains

**Situation:** Domain 1 is fully rated with mostly mid scores; Domain 5 is partly rated high; Domains 2–4 untouched.

Example Domain 1 answers (9 items): `2, 2, 3, 3, 3, 3, 4, 3, 2`  
→ Domain 1 average **2.8** → **Implementing**, completion **100%**

Domain 5: 6 of 18 items answered, all **5**  
→ Domain 5 average **5.0** → **Sustained Action**, completion **33%**

Overall: 15 answered of 69 → completion ~**21.7%**; average is the mean of those 15 scores (Domain 1’s nine + Domain 5’s six), not the average of domain averages.

**Talking point:** Leaders can see where practice is strong vs emerging **by domain**, while overall maturity stays grounded in item-level answers. Larger domains with more answers influence the overall figure more — by design, equal weight per practice item.

---

## One-liner for a slide

> ISSAC rates each practice 1–5, then reports completion and an equal-weight maturity average — banded Exploring / Implementing / Sustained Action — at subsection, domain, and whole-school level. Unanswered items never drag the score down as zeros.
