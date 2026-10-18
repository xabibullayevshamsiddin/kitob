# Quiz question types and manual review

## Scope
Allow quiz authors to attach an image to a question and choose between multiple-choice and written-answer questions. Persist written submissions and let authorized teachers/admins review and score them.

## Plan
1. [x] Audit quiz schema, authoring, learner submission/scoring, roles, routes, and existing tests.
2. [x] Design additive schema and backend behavior that preserves existing multiple-choice quizzes and reports.
3. [x] Implement authoring UI and validation for question types/images.
4. [x] Implement learner rendering/submission for written answers and image prompts.
5. [x] Implement teacher/admin pending-review and scoring workflow with authorization.
6. [x] Add/adjust tests and run targeted validation; inspect final diff for unrelated changes.
7. [x] Align home transparent-header dropdowns and notification hover with the glass hero state; validate Blade/CSS output.
8. [x] Improve live camera preview in low light with a default clean enhancement preset; validate Livewire view and live-stream tests.
9. [x] Add live video viewing controls (fullscreen, PiP, fit mode, host mirror, settings popover) without changing WebRTC transport; validate view and live-stream tests.

10. [x] Remove camera mirror control and make the live mini-player persist when navigating to other pages; preserve existing WebRTC behavior and validate.
11. [x] Replace the popup mini-player with an inline global floating player that reconnects to the active live stream across page navigation; validate.

## Current Phase
Phase 11: inline global live mini-player — complete

## Next Step
No remaining steps for this request.

## Errors Encountered
| Error | Attempt | Resolution |
|---|---:|---|
| Windows Symfony Process could not write its default temp output lock | 1 | Re-ran PHPUnit with a writable workspace-local `sys_temp_dir`; all targeted tests passed. |
| PowerShell response-inspection snippet had a parser error | 1 | Re-ran the read-only localhost check with a separate snippet variable; endpoint inspection completed. |

## Constraints
- Preserve existing user changes in the dirty worktree.
- Do not run production database migrations or push/commit.
- Keep legacy multiple-choice behavior compatible.
