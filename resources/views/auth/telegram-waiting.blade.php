@extends('layouts.auth')

@section('title', 'Telegram orqali kirish')

@section('content')
<div class="text-center space-y-6">
    <div class="space-y-1.5">
        <h1 class="text-2xl font-bold font-serif text-paper">Telegram orqali kirish</h1>
        <p class="text-xs text-mist font-sans">Telegram botingiz ochilmoqda. Kirishni tasdiqlash uchun botda <b>«Start»</b> va <b>«✅ Tasdiqlash»</b> tugmasini bosing.</p>
    </div>

    @if(!empty($securityCode))
    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 max-w-xs mx-auto space-y-1">
        <span class="text-[11px] text-amber-300 font-mono uppercase tracking-wider block">Xavfsizlik tasdiqlash kodi</span>
        <div class="text-2xl font-black font-mono tracking-[0.25em] text-amber-400">{{ $securityCode }}</div>
        <p class="text-[11px] text-mist">Botda chiqqan kod ushbu kod bilan bir xil ekanligiga ishonch hosil qiling.</p>
    </div>
    @endif

    <!-- Animated Telegram Icon Pulse -->
    <div class="relative py-6 flex justify-center">
        <div class="relative w-20 h-20 flex items-center justify-center">
            <span class="absolute inset-0 rounded-full bg-[#24A1DE]/20 animate-ping"></span>
            <span class="absolute inset-2 rounded-full bg-[#24A1DE]/30"></span>
            <div class="relative z-10 w-16 h-16 rounded-full bg-[#24A1DE] text-white flex items-center justify-center shadow-lg shadow-[#24A1DE]/40">
                <svg class="w-8 h-8 fill-current translate-x-[-1px] translate-y-[-1px]" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Live Status Text -->
    <div id="status-container" class="space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ink-950 border border-ink-border text-xs text-mist font-mono">
            <svg id="status-spinner" class="w-3.5 h-3.5 animate-spin text-[#24A1DE]" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span id="status-text">Bot javobi kutilmoqda...</span>
        </div>
        <p class="text-[11px] text-mist/70">
            Botda tasdiqlashingiz bilan ushbu sahifa o'zi avtomatik ochiladi.
        </p>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-3 pt-2">
        <a id="btn-tg-open" href="{{ $tgUrl }}" target="_blank"
           class="w-full py-3 px-4 rounded-xl bg-[#24A1DE] hover:bg-[#2092c9] text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md shadow-[#24A1DE]/25">
            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/>
            </svg>
            <span>Telegram ilovasida ochish (Start)</span>
            <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs text-mist hover:text-paper font-mono transition-colors">
                ← Boshqa usul bilan kirish
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const token = @json($token);
        const checkUrl = @json(route('auth.telegram.check', ['token' => $token]));
        const tgAppUrl = @json($tgAppUrl);

        // Telegram ilovasini to'g'ridan-to'g'ri ochishga urinish (agar o'rnatilgan bo'lsa)
        try {
            window.location.href = tgAppUrl;
        } catch(e) {}

        const statusText = document.getElementById('status-text');
        const statusSpinner = document.getElementById('status-spinner');

        let attempts = 0;
        const maxAttempts = 200; // ~5 daqiqa

        const pollTimer = setInterval(async () => {
            attempts++;

            if (attempts > maxAttempts) {
                clearInterval(pollTimer);
                if (statusText) statusText.textContent = "Vaqt tugadi. Iltimos, qaytadan urinib ko'ring.";
                if (statusSpinner) statusSpinner.remove();
                return;
            }

            try {
                const response = await fetch(checkUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();

                if (data.confirmed) {
                    clearInterval(pollTimer);
                    if (statusText) {
                        statusText.textContent = "Tasdiqlandi! Sahifa yangilanmoqda...";
                        statusText.classList.add('text-emerald-400');
                    }
                    if (statusSpinner) {
                        statusSpinner.className = "w-4 h-4 text-emerald-400";
                        statusSpinner.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" stroke="currentColor"/>';
                    }

                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/';
                    }, 400);
                } else if (data.banned || data.rejected) {
                    clearInterval(pollTimer);
                    alert(data.message || "Kirish bekor qilindi");
                    window.location.href = '/login';
                } else if (data.expired) {
                    clearInterval(pollTimer);
                    if (statusText) statusText.textContent = "Sessiya eskirgan.";
                    window.location.href = '/login';
                }
            } catch (err) {
                // Tarmoq xatoligi bo'lsa keyingi urinishda qayta tekshiradi
            }
        }, 1500);
    })();
</script>
@endpush
@endsection
