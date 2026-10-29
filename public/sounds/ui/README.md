# Kitobxon UI Sound Assets

This directory contains acoustic and calming interface sound assets for the Kitobxon platform.

## Sources & Licensing

- **Inspiration & Sound Design**: Kenney Interface Sounds (https://kenney.nl/assets/interface-sounds)
- **License**: Creative Commons Zero (CC0 1.0 Universal) - Public Domain Dedication.
  You can copy, modify, distribute, and perform the work, even for commercial purposes, all without asking permission.
- **Synthesis Format**: 44,100 Hz, 16-bit PCM Mono WAV.
- **Fallback System**: Native Web Audio API procedural synthesis is built-in as the primary real-time sound engine. If network access or audio loading fails, the browser seamlessly falls back to the in-code acoustic synthesizer.

## Audio Assets Reference

1. `bookmark.wav` (~95ms): Gentle wooden tap / pop for bookmarking or saving a book.
2. `settings_saved.wav` (~300ms): Gentle two-tone confirmation chord for saving preferences.
3. `quiz_correct.wav` (~280ms): Soft ascending melodic chime (C5-E5-G5) for correct quiz answers.
4. `quiz_wrong.wav` (~220ms): Polite, warm low acoustic note for wrong quiz answers.
5. `quiz_finish.wav` (~420ms): Gentle closing chord for completed tests.
6. `achievement.wav` (~950ms): Rewarding ~1-second celestial harp arpeggio for badges and accomplishments.
7. `level_up.wav` (~950ms): Rewarding celebratory chime for reaching a new level.
8. `daily_bonus.wav` (~420ms): Short celebratory chime for claiming daily reading bonus or goal.
9. `note_saved.wav` (~140ms): Gentle double tap for saving a personal reading note.
10. `group_message.wav` (~180ms): Soft ambient harmonic ping for open group messages.
11. `theme_light.wav` (~160ms): Warm rising chime for switching to Light mode.
12. `theme_dark.wav` (~180ms): Calm descending twilight chime for switching to Dark mode.
13. `language_switch.wav` (~120ms): Crisp tactile page-turn tap for changing interface language.
14. `purchase.wav` (~320ms): Delicate crystalline coin shimmer for shop transactions.
15. `chat_message.wav` (~160ms): Soft organic droplet/ping for incoming chat notifications.
16. `error.wav` (~250ms): Muted, calm double pulse for important system alerts.
