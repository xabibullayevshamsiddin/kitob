{{-- ── Kitobxon Universal Interface Sound Configuration Bridge ── --}}
@php
    $soundUser = auth()->user();
    $soundPrefs = $soundUser && is_array($soundUser->notification_prefs) ? $soundUser->notification_prefs : [];
    $soundEnabled = array_key_exists('sound_enabled', $soundPrefs) ? (bool) $soundPrefs['sound_enabled'] : true;
    $soundVolume = array_key_exists('sound_volume', $soundPrefs) ? round(((float) $soundPrefs['sound_volume']) / 100, 2) : 0.20;
@endphp
<script>
    window.__KITOB_SOUND_SERVER_CONFIG = {
        authenticated: {{ auth()->check() ? 'true' : 'false' }},
        enabled: {{ $soundEnabled ? 'true' : 'false' }},
        volume: {{ $soundVolume }}
    };

    // If KitobSound is already initialized, sync server preferences
    if (window.KitobSound) {
        window.KitobSound.setEnabled({{ $soundEnabled ? 'true' : 'false' }}, false);
        window.KitobSound.setVolume({{ $soundVolume }}, false);
    }
</script>
