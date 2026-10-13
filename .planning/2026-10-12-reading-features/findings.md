# Findings

## Existing application behavior
- The requested bookmark/note base already exists: `bookmarks`, `user_notes`, `Bookmark`, `UserNote`, `BookChapter` relations, `User::bookmarks()/notes()`, profile notes tab, and `components/reading-tracker.blade.php` hooks.
- Reading progress already exists through `BookReadingProgress` and `ReadingSession`.
- Quiz attempts are persisted by `App\Http\Livewire\Quiz\TakeQuiz`, but the initial search shows persistence is currently tied to submission/completion, not per-answer autosave.
- No recommendation implementation surfaced in the focused initial search; inspect catalog/home/profile flows and genre/read-history data before adding one.
- Routes/views identified: `resources/views/pages/reader.blade.php`, `resources/views/livewire/quiz/take-quiz.blade.php`, `resources/views/livewire/catalog/catalog-page.blade.php`, `app/Http/Livewire/Quiz/TakeQuiz.php`, profile component, and `routes/web.php`.

## Decisions / constraints
- Follow current Laravel 9, Blade/Livewire, and design-system patterns.
- Avoid schema or UI duplication if an equivalent feature already exists.
- Likely focus: finish/connect existing bookmark and note models in the reader, build recommendations from existing reading history, and introduce recoverable draft answers without treating drafts as completed attempts.
- Reader routes are already inside the authenticated route group. The chapter reader is a Blade/Alpine page; it tracks scroll position in `BookReadingProgress`, but its save action currently fires only when the chapter is marked complete.
- There are no current controller/query usages for `Bookmark` or `UserNote`; existing DB tables and profile notes display are dormant foundations, so no duplicate schema is needed for reading notes/bookmarks.
- Quiz `TakeQuiz` has in-memory `draftAnswers` and preserves them across question navigation/time-up, but `mount()` resets state/start time and answers are not stored on refresh. `quiz_attempts.completed_at` is nullable, however finished-result/reporting queries expect actual attempts; use a separate draft store to avoid counting drafts as submissions.
- Catalog is Livewire and already exposes genre, reading progress and popularity filters. Recommendations can be a separate personalized collection above the existing filtered catalog without changing filter semantics.
- Shared app routes are authenticated. Book recommendation queries must still apply `Book::availableForUser()` so early-access visibility rules remain intact.
- Reader provides the ideal interaction surface: an Alpine root, scroll percentage, and a chapter content article. Persist bookmark scroll offsets and notes to the existing tables; expose them in the reader and keep private ownership checks server-side.
- Quiz answer edits currently use `wire:model.defer` and option selection via `$set`, so autosave must explicitly persist on text update/choice, not just question navigation. Resume also needs to retain the original `started_at` to prevent refreshing a timed quiz from resetting its clock.
- Use a dedicated quiz-draft table rather than incomplete `quiz_attempts` rows; the latter feed result counts, duplicate-attempt checks, teacher review, and reward logic.
- `php artisan test` failed before PHPUnit because Symfony Process could not write to `D:\OSPanel\userdata\temp`. Invoking `php vendor/bin/phpunit` directly bypassed it successfully.
- Reader annotation/recommendation/draft feature tests pass (7 tests, 32 assertions); existing quiz timer and written-review tests pass (17 tests, 138 assertions).
- Quiz drafts live in `quiz_attempt_drafts` and are not counted as submitted attempts; original `started_at` is restored, so a page refresh cannot reset a timed quiz.
- Local fallback is keyed by authenticated user ID and quiz ID, revalidated against current question/option IDs, and only used if newer than the server draft and not superseded by a completed attempt.
- Profile pages are publicly viewable, so notes are now fetched and rendered only for their owner; profile tab changes are also server-guarded.
- The existing pending streak/shop migration is outside this task; only the quiz-draft migration was applied locally.

