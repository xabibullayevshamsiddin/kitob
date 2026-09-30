@extends('errors.layout')

@section('code', '429')
@section('title', __('site.errors.e429_title'))
@section('badge', __('site.errors.e429_badge'))
@section('icon', '🚦')

@section('message')
    {{ __('site.errors.e429_msg') }}
@endsection

@section('actions')
    <button onclick="window.location.reload()" 
            class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ __('site.errors.e429_retry') }}</span>
    </button>

    <a href="{{ route('home') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        {{ __('site.common.back_home') }}
    </a>
@endsection
