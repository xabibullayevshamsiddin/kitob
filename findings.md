# Findings

## Existing flow
- Quiz questions already had `single` and `text` enum values, but both authoring forms forced `single` and required answer options.
- Learner quizzes run in Livewire one question at a time; choice answers are auto-graded and reward points are awarded when the attempt ends.
- Attempts already store a JSON `answers` payload, so written responses and per-question manual scores can stay in that existing structure.
- Admin and teacher panels have separate quiz-authoring forms/controllers and separate role-protected route groups.

## Implementation decisions
- Keep multiple-choice support and add optional question images; add `text` as a free-response type.
- Store uploaded question images on the public disk, limited to JPG/PNG/WEBP and 5 MB.
- Create a pending-review attempt if a quiz contains written questions; do not award points until staff scores every written question.
- Add review status/reviewer metadata to attempts, and give reward points only for the user's first attempt (preserving the existing anti-repeat-points rule).
- Notify the learner after review and show their latest pending/final result on the quiz page.
