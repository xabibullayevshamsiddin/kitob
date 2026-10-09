# Quiz question types and manual review

## Scope
Allow quiz authors to attach an image to a question and choose between multiple-choice and written-answer questions. Persist written submissions and let authorized teachers/admins review and score them.

## Plan
1. Audit quiz schema, authoring, learner submission/scoring, roles, routes, and existing tests.
2. Design additive schema and backend behavior that preserves existing multiple-choice quizzes and reports.
3. Implement authoring UI and validation for question types/images.
4. Implement learner rendering/submission for written answers and image prompts.
5. Implement teacher/admin pending-review and scoring workflow with authorization.
6. Add/adjust tests and run targeted validation; inspect final diff for unrelated changes.

## Constraints
- Preserve existing user changes in the dirty worktree.
- Do not run production database migrations or push/commit.
- Keep legacy multiple-choice behavior compatible.
