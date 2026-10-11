# Task Plan: Kitobxon PWA Offline Mode

## Goal
Implement the requested standalone offline experience and safe service-worker/network-status integration for Kitobxon, without caching private pages or disrupting quizzes/live/video/audio, then provide the user a local test URL.

## Next Step
Finish cache/scope checks and hand the user the working local test URL with offline-testing steps.

## Current Phase
Phase 4

## Phases

### Phase 1: Audit and architecture
- [x] Inspect current layout/theme/toast/Livewire integration and all HTML-bearing views.
- [x] Inspect routes/middleware for ping, live signalling, streaming, and admin/teacher boundaries.
- [x] Check Node/npm versions, Vite/Tailwind versions, git state, and installed skills.
- [x] Record findings and decide the isolated offline-app build strategy.
- **Status:** complete

### Phase 2: Offline mini-app and PWA shell
- [x] Add isolated React + Vite + Tailwind v4 app under `offline-app/`.
- [x] Implement the CSS 3D book, offline/slow states, themes, accessibility, retry/backoff, and safe return URL.
- [x] Add local quotes and inline CSS fallback; self-host available fonts and add replaceable icon placeholders.
- [x] Build into `public/offline/` and automatically generate the versioned precache list.
- [x] Add manifest, `public/sw.js`, PWA config, and kill-switch-aware shared head partial.
- [x] Add lightweight `GET /ping` with no session middleware and no-store response.
- **Status:** complete

### Phase 3: Site connectivity UI
- [x] Integrate PWA partial into all 15 full HTML browser documents identified by audit; omit PDF output.
- [x] Add online/offline/ping and Livewire failure detection, existing toast events, and an offline-only indicator.
- [x] Ensure quiz/live pages never auto-redirect and excluded routes bypass all SW strategies.
- **Status:** complete

### Phase 4: Verification and handoff
- [x] Run isolated build, Laravel/view checks, static scope/exclusion checks, and relevant existing tests.
- [x] Review SW cache policies and dirty-tree diff; do not run migrations or commit.
- [x] Provide the user a local URL to test offline mode plus Chrome DevTools steps and limitations.
- **Status:** complete

## Key Constraints
- Do not modify the main Vite/Tailwind build; offline app builds separately to `public/offline/`.
- Site navigation/private HTML is never cached (the standalone offline shell is precached by design). Non-GET, Livewire, signalling, admin/teacher, ping, audio/video bypass caching.
- Never force-redirect an active quiz or live session when connectivity drops.
- Stage 3 book downloads/offline reading is explicitly out of scope until the user says “davom et”.
- Preserve unrelated user changes; do not commit or run production migrations.

## Decisions Made
| Decision | Rationale |
|----------|-----------|
| Use a separate Vite app for offline UI | Keeps React/Tailwind v4 independent from Laravel's Vite 4.5/Tailwind 3.3 build. |
| Use `.planning/2026-10-11-pwa-offline/` | Existing root planning files belong to an unrelated completed quiz task and must remain untouched. |
| Integrate 15 browser documents; omit the PDF template | PDF generation should not carry browser PWA registration and connectivity JavaScript. |
| Use relative Vite asset base (`./`) | Keeps the local `/Kitob/public/` test path working while also supporting a domain root. |
| Do not implement book caching | Prompt explicitly marks it optional for a later “davom et” authorization. |

## Errors Encountered
| Error | Attempt | Resolution |
|-------|---------|------------|
| `impeccable` skill not found in the skill catalog or local skill directories checked | 1 | Continue using available `ui-ux-pro-max` and the project's design-system tokens; report unavailable skill at handoff. |

## Notes
- The user attached an implementation prompt; treat it as the requested requirements, not as permission to install dependencies if that requires network escalation.
- Offline requires HTTPS or localhost.
