# Plan: Last Review Fixes

## Goal
Apply only the six requested review-fix groups, validate each group, and create one scoped commit per group without absorbing unrelated existing work.

## Next Step
After Git write approval is available again, make the scoped Stage 5 commit first (stage only the logout-listener hunk from EventServiceProvider), then commit the Stage 6 fix set separately.

## Phases
1. **Repository hygiene** — ignore and untrack `offline-app/node_modules`, verify other tracked dependency/env paths, preserve offline build artifacts. Status: complete.
2. **Remove unrelated sensitive/local files** — inspect cookie presence without disclosing values; remove cookie, `.freebuff`, `.osp`, ignore them. Status: complete.
3. **Offline recovery behavior** — accessible status, auto-return after online recovery, remove irrelevant listeners, rebuild and verify precache paths. Status: complete.
4. **Trim offline font payload** — use selected Latin subsets/weights, precache only woff2, verify Uzbek glyphs and report size. Status: complete.
5. **Logout flash ordering** — Fortify order verified, response binding/test implemented and validated. Implementation complete; commit blocked by approval usage limit.
6. **Small fixes** — leaderboard tie rank, streak negative-result cache, TURN env config, PNG icons, remove toast emojis. Implementation complete and validated; commit blocked by approval usage limit.

## Commit Policy
One commit per numbered stage. Stage only paths belonging to that stage. Preserve the user's pre-existing changes outside scope. If an in-scope file already contains unrelated local edits that cannot be cleanly isolated, stop and report before committing it.

## Validation
- After each phase, record exact changed files and tests.
- Offline build after stages 3 and 4; check every generated precache file exists.
- Inspect Git status and commit contents before every commit.

## Errors Encountered
| Error | Attempt | Resolution |
|---|---|---|
| Stage 5 `git add`/commit required a sandbox escalation after prior Git commits; auto-review refused because the session hit its approval usage limit. | 1 | Did not bypass the approval gate; leave Stage 5 and Stage 6 changes unstaged and report that commits remain pending. |
| `php artisan test` initially could not write Symfony temporary process output. | 1 | Ran `php vendor/bin/phpunit` directly; 14 relevant tests pass. |
