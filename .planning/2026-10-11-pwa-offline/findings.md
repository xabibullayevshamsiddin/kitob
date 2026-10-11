# Findings: Kitobxon PWA Offline Mode

## Requirements
- Two authorized phases: (1) isolated React/Vite/Tailwind v4 offline mini-app, service worker, manifest, ping endpoint, PWA config/kill switch/shared partial; (2) in-page connectivity status/toasts/indicator with Livewire failure support.
- Never cache regular site navigation or private pages. Precache the standalone offline shell plus its generated assets/manifest icons; hashed `public/build/*` may be cache-first.
- Bypass SW for non-GET, `/livewire/*`, `/broadcasting/*`, live signalling endpoints, admin/teacher pages, `/ping`, audio/video/Range requests.
- Network-first navigation with 8s timeout; offline fallback has `reason` and a same-origin safe `from` URL.
- Do not force navigation while user is taking a quiz or in live session.
- Follow `design-system/kitobxon/MASTER.md`; offline mode self-hosts fonts/assets and works with either stored theme.
- Stage 3 saved-book downloads are not authorized yet.

## Research Findings
- The user requires Laravel 9, Blade, Livewire 2, Alpine, Vite 4.5, Tailwind 3.3 as the main app; React/Tailwind v4 must be isolated.
- Previous planning files at repository root are from a quiz task and must not be changed.
- Local `ui-ux-pro-max` exists; requested `impeccable` skill was not found in checked skill directories/catalog.
- Main package confirms `vite ^4.5`, `laravel-vite-plugin ^0.7`, `tailwindcss ^3.3`; current Node is v22.21.1 and npm 10.9.4, so a separate Tailwind v4/Vite build is supported without touching the main build.
- There are 16 true full-document Blade templates: 15 browser pages and one PDF-rendered book; the PWA head is integrated in all 15 browser documents, while PDF markup remains untouched.
- Existing global toast API is `window.toast({type, title?, message})` and the event is `toast`; Livewire uses v2.
- Existing theme key is `localStorage['kitob_theme']`; themes are `.light` and `.dark`, and same-tab updates dispatch `theme-changed`.
- WebRTC signalling endpoints are `/live/{event}/signal` (POST) and `/live/{event}/signals` (GET).
- Worktree already contains unrelated user changes in language files, error pages, and React error components. Preserve all of them.
- `public/offline/` is an independent built artifact; no main package changes are needed. Its relative Vite base supports the local `/Kitob/public/` mounting path as well as a domain root.
- `/ping` returns HTTP 204 with `Cache-Control: no-store` and no `Set-Cookie` on the current local server.
- Offline shell bundle is 149.55 kB JS raw / 48.58 kB gzip; fonts are self-hosted and a small inline boot fallback is present.

## Technical Decisions
| Decision | Rationale |
|----------|-----------|
| Add a standalone offline-app package/build | Main project is explicitly pinned to Vite 4.5 and Tailwind 3.3. |
| Use existing `window.toast` event contract | Avoid a second notification system and preserve existing toast UI. |
| Integrate 15 browser documents; omit the PDF template | PDF generation should not carry browser PWA registration or connectivity scripts. |
| Use relative Vite asset base (`./`) | Keeps local subdirectory testing functional without sacrificing root deployment. |

## Issues Encountered
| Issue | Resolution |
|-------|------------|
| `impeccable` unavailable | Use the available UI UX skill and existing design system instead. |

## Resources
- User prompt: supplied attachment `Pasted text.txt`.
- `design-system/kitobxon/MASTER.md`.
- `design-system/kitobxon/pages/admin.md` is admin-only; offline page should use Master theme tokens.

## Visual/Browser Findings
- No browser screenshot available; UI direction is derived from the user's prompt and the Kitobxon design tokens.
