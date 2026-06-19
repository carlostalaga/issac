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
