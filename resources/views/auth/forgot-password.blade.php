@extends('layouts.auth')

@section('title', 'Parolni tiklash')

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">Parolni tiklash</h1>
    <p class="text-xs text-mist font-sans">Telefon raqamingiz yoki emailingiz orqali parolni yangilang</p>
</div>

@if ($errors->any())
    <div class="mb-4 p-3 bg-ink-950 border border-rose-500/30 text-rose-300 rounded-card text-xs space-y-1">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
@endif

@if (session('status'))
    <div class="mb-4 p-3 bg-ink-950 border border-emerald-500/30 text-emerald-400 rounded-card text-xs font-medium flex items-center gap-2">
        <span>✅</span>
        <span>{{ session('status') }}</span>
    </div>
@endif


<form method="POST" action="{{ route('password.email') }}" class="space-y-4"
      x-data="{
          cooldown: {{ (int) session('cooldown', session('status') ? 60 : 0) }},
          timer: null,
          init() {
              if (this.cooldown > 0) {
                  this.timer = setInterval(() => {
                      if (this.cooldown > 0) {
                          this.cooldown--;
                      } else {
                          clearInterval(this.timer);
                      }
                  }, 1000);
              }
          }
      }"
      @submit="if (cooldown > 0) { $event.preventDefault(); }">
    @csrf

    <x-phone-input name="email" id="email" label="Telefon raqam" :required="true" :autofocus="true" help="Telegram ulangan bo'lsa, havola botga yuboriladi." />

    <button type="submit"
        :disabled="cooldown > 0"
        :class="cooldown > 0 ? 'opacity-60 cursor-not-allowed pointer-events-none select-none' : ''"
        class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
        <template x-if="cooldown > 0">
            <span>🔒 Qayta yuborish (<span x-text="cooldown"></span>s)</span>
        </template>
        <template x-if="cooldown <= 0">
            <span class="inline-flex items-center gap-2">
                <span>Tiklash havolasini olish</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </template>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <a href="{{ route('login') }}" class="text-xs text-amber-400 font-semibold hover:text-amber-300 transition-colors inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>{{ __('site.auth.back_to_login') }}</span>
    </a>
</div>
@endsection
