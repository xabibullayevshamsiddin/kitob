@extends('admin.layouts.app')

@section('title', 'Sozlamalar')
@section('breadcrumb', 'Sozlamalar')

@section('content')

<div class="max-w-4xl mx-auto space-y-5">

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Tizim sozlamalari</h1>
        <p class="text-sm text-slate-500 mt-0.5">Platforma konfiguratsiyasini boshqaring</p>
    </div>

    {{-- Platform Settings Form --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">Platforma sozlamalari</h2>
                <p class="text-xs text-slate-500">Asosiy konfiguratsiya parametrlari</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- App Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Platforma nomi (APP_NAME)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <input type="text" name="app_name" value="{{ config('app.name', 'Kitobxon') }}"
                               class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-600"
                               placeholder="Kitobxon">
                    </div>
                </div>

                {{-- Contact Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Aloqa email manzili
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" name="contact_email" value="{{ env('MAIL_FROM_ADDRESS', 'admin@kitobxon.uz') }}"
                               class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-600"
                               placeholder="admin@kitobxon.uz">
                    </div>
                </div>

                {{-- App URL --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Sayt manzili (APP_URL)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <input type="url" name="app_url" value="{{ config('app.url') }}"
                               class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-600"
                               placeholder="https://kitobxon.uz">
                    </div>
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Vaqt zonasi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <select name="timezone" class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none">
                            <option value="Asia/Tashkent" {{ config('app.timezone') === 'Asia/Tashkent' ? 'selected' : '' }}>Asia/Tashkent (UTC+5)</option>
                            <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Europe/Moscow" {{ config('app.timezone') === 'Europe/Moscow' ? 'selected' : '' }}>Europe/Moscow (UTC+3)</option>
                        </select>
                    </div>
                </div>

                {{-- Per page --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Sahifadagi yozuvlar soni
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <select name="per_page" class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none">
                            <option value="10">10 ta</option>
                            <option value="15" selected>15 ta</option>
                            <option value="20">20 ta</option>
                            <option value="25">25 ta</option>
                            <option value="50">50 ta</option>
                        </select>
                    </div>
                </div>

                {{-- Welcome message --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Xush kelibsiz matni
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <input type="text" name="welcome_message" value="Kitobxon platformasiga xush kelibsiz!"
                               class="w-full pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-600">
                    </div>
                </div>
            </div>

            {{-- Save --}}
            <div class="flex items-center justify-end pt-2 border-t border-slate-800">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-all shadow-lg shadow-indigo-900/30 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Sozlamalarni saqlash
                </button>
            </div>
        </form>
    </div>

    {{-- Maintenance Mode --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden" x-data="{ maintenance: false }">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">Texnik xizmat ko'rsatish rejimi</h2>
                <p class="text-xs text-slate-500">Saytni vaqtincha yopish va ochish</p>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-start gap-5">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-4">
                        <button @click="maintenance = !maintenance"
                                :class="maintenance ? 'bg-amber-500' : 'bg-slate-700'"
                                class="relative inline-flex h-7 w-13 w-[52px] items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                            <span :class="maintenance ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-200"></span>
                        </button>
                        <div>
                            <p class="text-sm font-medium" :class="maintenance ? 'text-amber-400' : 'text-slate-400'">
                                <span x-text="maintenance ? '⚠️ Texnik xizmat rejimi faol' : '✅ Sayt ishlayapti'"></span>
                            </p>
                            <p class="text-xs text-slate-600 mt-0.5">Foydalanuvchilar saytga kira olmaydi</p>
                        </div>
                    </div>

                    <div x-show="maintenance"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 mb-4"
                         style="display:none;">
                        <div class="flex items-start gap-3">
                            <span class="text-xl flex-shrink-0">⚠️</span>
                            <div>
                                <p class="text-sm font-semibold text-amber-400">Diqqat!</p>
                                <p class="text-xs text-amber-400/70 mt-1">Texnik xizmat rejimi faollashtirilganda, barcha foydalanuvchilar (admindan tashqari) saytga kira olmaydi. Bu amalni ehtiyotkorlik bilan bajaring.</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.maintenance') }}">
                        @csrf
                        <input type="hidden" name="maintenance" :value="maintenance ? '1' : '0'">
                        <button type="submit"
                                :class="maintenance ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-900/30' : 'bg-slate-700 hover:bg-slate-600 border border-slate-600'"
                                class="flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition-all shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="maintenance ? 'Rejimni yoqish' : 'Rejimni o\'chirish'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Cache Management --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-purple-500/15 border border-purple-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">Kesh boshqaruvi</h2>
                <p class="text-xs text-slate-500">Sayt keshini tozalash va optimallashtirish</p>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                {{-- Clear all cache --}}
                <form method="POST" action="{{ route('admin.settings.cache') }}">
                    @csrf
                    <input type="hidden" name="action" value="clear">
                    <button type="submit"
                            class="w-full flex flex-col items-center gap-3 p-4 rounded-xl bg-slate-800 border border-slate-700 hover:border-red-500/30 hover:bg-red-500/5 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-red-500/15 border border-red-500/20 flex items-center justify-center group-hover:bg-red-500/25 transition-colors">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-300">Keshni tozalash</p>
                            <p class="text-[10px] text-slate-600 mt-0.5">php artisan cache:clear</p>
                        </div>
                    </button>
                </form>

                {{-- Config cache --}}
                <form method="POST" action="{{ route('admin.settings.cache') }}">
                    @csrf
                    <input type="hidden" name="action" value="config">
                    <button type="submit"
                            class="w-full flex flex-col items-center gap-3 p-4 rounded-xl bg-slate-800 border border-slate-700 hover:border-blue-500/30 hover:bg-blue-500/5 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/25 transition-colors">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-300">Config kesh</p>
                            <p class="text-[10px] text-slate-600 mt-0.5">config:cache</p>
                        </div>
                    </button>
                </form>

                {{-- Route cache --}}
                <form method="POST" action="{{ route('admin.settings.cache') }}">
                    @csrf
                    <input type="hidden" name="action" value="route">
                    <button type="submit"
                            class="w-full flex flex-col items-center gap-3 p-4 rounded-xl bg-slate-800 border border-slate-700 hover:border-emerald-500/30 hover:bg-emerald-500/5 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/25 transition-colors">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-300">Route kesh</p>
                            <p class="text-[10px] text-slate-600 mt-0.5">route:cache</p>
                        </div>
                    </button>
                </form>

                {{-- View cache --}}
                <form method="POST" action="{{ route('admin.settings.cache') }}">
                    @csrf
                    <input type="hidden" name="action" value="view">
                    <button type="submit"
                            class="w-full flex flex-col items-center gap-3 p-4 rounded-xl bg-slate-800 border border-slate-700 hover:border-amber-500/30 hover:bg-amber-500/5 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center group-hover:bg-amber-500/25 transition-colors">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-300">View kesh</p>
                            <p class="text-[10px] text-slate-600 mt-0.5">view:cache</p>
                        </div>
                    </button>
                </form>
            </div>

            <div class="mt-4 p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Keshni tozalash so'rovlarni biroz sekinlashtirishi mumkin, chunki tizim qayta kesh yarataveradi.
                        Bu jarayon odatda bir necha soniya davom etadi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Environment Info --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-slate-700 border border-slate-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">Muhit ma'lumotlari</h2>
                <p class="text-xs text-slate-500">Server va dastur konfiguratsiyasi</p>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @php
                    $envInfo = [
                        ['label' => 'PHP versiyasi', 'value' => PHP_VERSION, 'icon' => '🔵', 'color' => 'text-blue-400'],
                        ['label' => 'Laravel versiyasi', 'value' => app()->version(), 'icon' => '🔴', 'color' => 'text-red-400'],
                        ['label' => 'Muhit', 'value' => config('app.env', 'production'), 'icon' => '🟡', 'color' => 'text-amber-400'],
                        ['label' => 'Debug rejimi', 'value' => config('app.debug') ? 'Yoqilgan' : 'O\'chirilgan', 'icon' => config('app.debug') ? '⚠️' : '✅', 'color' => config('app.debug') ? 'text-amber-400' : 'text-emerald-400'],
                        ['label' => 'Kesh haydovchi', 'value' => config('cache.default', 'file'), 'icon' => '💾', 'color' => 'text-purple-400'],
                        ['label' => 'Queue haydovchi', 'value' => config('queue.default', 'sync'), 'icon' => '⚙️', 'color' => 'text-indigo-400'],
                    ];
                @endphp
                @foreach($envInfo as $info)
                    <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/50 border border-slate-700/50">
                        <span class="text-lg">{{ $info['icon'] }}</span>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider">{{ $info['label'] }}</p>
                            <p class="text-sm font-semibold {{ $info['color'] }} font-mono mt-0.5">{{ $info['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@endsection
