@extends('errors.layout')

@section('code', '404')
@section('title', __('site.errors.e404_title'))
@section('badge', __('site.errors.e404_badge'))
@section('icon', '🧭')

@section('message')
    {{ __('site.errors.e404_msg') }}
@endsection

@section('actions')
    <a href="{{ route('home') }}" 
       class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2">
        <span>{{ __('site.errors.back_home') }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </a>

    <a href="{{ route('books.public') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        {{ __('site.errors.go_catalog') }}
    </a>
@endsection
