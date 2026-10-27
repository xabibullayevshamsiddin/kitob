@extends('layouts.auth')

@section('title', __('site.auth.login_title'))

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">{{ __('site.auth.welcome') }}</h1>
    <p class="text-xs text-mist font-sans">{{ __('site.auth.welcome_sub') }}</p>
</div>

@if ($errors->any())
    <div class="mb-4 p-3 bg-ink-950 border border-rose-500/30 text-rose-300 rounded-card text-xs space-y-2">
        @foreach ($errors->all() as $error)
            <div class="flex items-start gap-2">
                <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="space-y-1.5">
                    <span>{{ $error }}</span>
                    @if(str_contains(mb_strtolower($error), 'bloklangan'))
                        <div>
                            <a href="{{ route('contact', ['appeal' => 1]) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-400 hover:text-amber-300 underline">
                                <span>⚖️ Noto'g'ri ban berildimi? Aloqa bo'limiga yozib qoldiring →</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

@if (session('status'))
    <div class="mb-4 p-3 bg-ink-950 border border-emerald-500/30 text-emerald-400 rounded-card text-xs font-medium">
        {{ session('status') }}
    </div>
@endif


<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <x-phone-input name="email" id="email" label="Telefon raqam" :required="true" :autofocus="true" />

    <x-password-input name="password" id="password" :label="__('site.auth.password')" :showForgot="true" placeholder="••••••••" />

    <div class="flex items-center justify-between pt-1">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded bg-ink-950 border-ink-border text-amber-500 focus:ring-amber-500 focus:ring-offset-ink-900">
            <span class="ml-2 text-xs text-mist select-none">{{ __('site.auth.remember_me') }}</span>
        </label>
    </div>

    <button type="submit"
        class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
        <span>{{ __('site.auth.login_btn') }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <p class="text-xs text-mist">
        {{ __('site.auth.no_account') }}
        <a href="{{ route('register') }}" class="text-amber-400 font-semibold hover:text-amber-300 ml-1 transition-colors">{{ __('site.auth.register_now') }}</a>
    </p>
</div>
@endsection
