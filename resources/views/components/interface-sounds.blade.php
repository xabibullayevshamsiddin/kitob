{{-- ── Kitobxon Universal Interface Sound Configuration Bridge ── --}}
@php
    $isAuthenticated = auth()->check();
    $soundUser = $isAuthenticated ? auth()->user() : null;
    $soundPrefs = $soundUser && is_array($soundUser->notification_prefs) ? $soundUser->notification_prefs : [];
    $soundEnabled = $isAuthenticated ? (array_key_exists('sound_enabled', $soundPrefs) ? (bool) $soundPrefs['sound_enabled'] : true) : null;
    $soundVolume = $isAuthenticated ? (array_key_exists('sound_volume', $soundPrefs) ? round(((float) $soundPrefs['sound_volume']) / 100, 2) : 0.20) : null;
@endphp
<script>
    window.__KITOB_SOUND_SERVER_CONFIG = {
        authenticated: {{ $isAuthenticated ? 'true' : 'false' }},
        enabled: {{ $soundEnabled !== null ? ($soundEnabled ? 'true' : 'false') : 'null' }},
        volume: {{ $soundVolume !== null ? $soundVolume : 'null' }}
    };

    @if($isAuthenticated && $soundEnabled !== null)
    // If KitobSound is already initialized, sync server preferences for authenticated user
    if (window.KitobSound) {
        window.KitobSound.setEnabled({{ $soundEnabled ? 'true' : 'false' }}, false);
        window.KitobSound.setVolume({{ $soundVolume }}, false);
    }
    @endif
</script>
