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

## Header polish findings
- `resources/views/components/nav/main-header.blade.php` already keeps the home header transparent until `window.scrollY > 20`, then switches to the solid ink header.
- The transparent state styles theme, language, stats, and avatar controls, but notification hover and dropdown surfaces still use solid `ink-*` colors.
- The scoped header component is the safest change point because it is shared across layouts and already contains hero-state overrides.

## Live camera findings
- `resources/views/livewire/live/live-detail.blade.php` already applies `videoFilterStyle` to the shared live video element for both host preview and viewer playback.
- The existing default `hd` preset only adds modest contrast, saturation, and brightness; the screenshot's low-light gray cast and sensor grain need a brighter, lightly softened preset.
- Camera/WebRTC transport should remain unchanged for this targeted visual fix; changing tracks or peer negotiation would add unnecessary risk.

## Debugging-specialist diagnosis
- Symptom: the live preview is gray, underexposed, and visibly noisy even though the camera reports HD 1280x720 at 30fps.
- The earlier fix changed only the rendered `<video>` CSS filter; it did not tune the physical `MediaStreamTrack`, so the captured stream could remain underexposed for viewers.
- Root-cause hypothesis: low-light webcam auto-exposure/white-balance/focus settings are not being requested when the track is acquired. The fix will apply only capabilities the browser explicitly exposes, with a safe no-op fallback.

## Live viewing controls findings
- The live video viewport is a single aspect-ratio container with no fullscreen target or viewer display-mode controls.
- Host toolbar already contains hardware/source/quality settings, so new viewing controls should stay local to the video viewport and not alter signaling or peer negotiation.
- Fullscreen and Picture-in-Picture require browser feature detection; unsupported browsers need hidden/disabled controls rather than errors.

## Persistent mini-player findings
- Standard Picture-in-Picture is attached to the current page's `<video>` element; a full Laravel navigation destroys that element, so native PiP alone cannot guarantee persistence across pages.
- A site-wide player shell in the shared layout can survive page navigation only if the active live video's source/state is handed off through browser storage and a stable global controller.
- The mirror-camera setting is a preview-only enhancement and can be removed without touching WebRTC tracks or signaling.
- The popup implementation violates the requested UX: it opens a separate `about:blank`/browser window instead of a fixed player inside the site layout.
- The live signaling API accepts a fresh viewer peer ID and routes `offer`, `answer`, ICE, and `stream-status` signals; a layout-level player can reconnect after navigation using the persisted event ID and signal URLs.
