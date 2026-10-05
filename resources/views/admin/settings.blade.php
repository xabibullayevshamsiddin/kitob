@extends('admin.layouts.app')

@section('title', 'Tizim sozlamalari')
@section('breadcrumb', 'Sozlamalar')

@section('content')

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl lg:text-2xl font-bold font-display text-paper flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-sm font-mono">
                    ⚙️
                </span>
                Tizim sozlamalari & Diagnostika
            </h1>
            <p class="text-xs text-mist mt-1">Platforma konfiguratsiyasi, gamifikatsiya qoidalari, kesh boshqaruvi va server holati</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-badge text-xs font-mono {{ $isMaintenance ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' }}">
                <span class="w-2 h-2 rounded-full {{ $isMaintenance ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' }}"></span>
                {{ $isMaintenance ? 'Texnik xizmat rejimi faol' : 'Platforma faol va ochiq' }}
            </span>
        </div>
    </div>

    {{-- System Status & Diagnostics Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">PHP Versiyasi</p>
            <p class="text-sm font-bold text-sky-400 font-mono mt-1">{{ $systemStats['php_version'] }}</p>
            <p class="text-[10px] text-mist mt-0.5">{{ $systemStats['os'] }}</p>
        </div>

        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">Laravel</p>
            <p class="text-sm font-bold text-rose-400 font-mono mt-1">v{{ $systemStats['laravel_version'] }}</p>
            <p class="text-[10px] text-mist mt-0.5">{{ $systemStats['environment'] }}</p>
        </div>

        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">Ma'lumotlar Bazasi</p>
            <p class="text-sm font-bold text-amber-400 font-mono mt-1">{{ $systemStats['database_size_mb'] }} MB</p>
            <p class="text-[10px] text-mist mt-0.5">{{ $systemStats['database_tables'] }} ta jadval</p>
        </div>

        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">Fayllar (Storage)</p>
            <p class="text-sm font-bold text-paper font-mono mt-1">{{ $systemStats['storage_size_mb'] }} MB</p>
            <p class="text-[10px] {{ $systemStats['storage_symlink'] ? 'text-emerald-400' : 'text-rose-400' }} mt-0.5">
                {{ $systemStats['storage_symlink'] ? 'Symlink faol ✅' : 'Symlink yo\'q ⚠️' }}
            </p>
        </div>

        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">Server Limitlari</p>
            <p class="text-sm font-bold text-purple-400 font-mono mt-1">{{ $systemStats['upload_max_filesize'] }}</p>
            <p class="text-[10px] text-mist mt-0.5">Mem: {{ $systemStats['memory_limit'] }}</p>
        </div>

        <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border">
            <p class="text-[10px] text-mist uppercase tracking-wider font-mono">Kesh & Debug</p>
            <p class="text-sm font-bold {{ $systemStats['debug_mode'] ? 'text-amber-400' : 'text-emerald-400' }} font-mono mt-1">
                {{ $systemStats['debug_mode'] ? 'Debug ON ⚠️' : 'Debug OFF ✅' }}
            </p>
            <p class="text-[10px] text-mist mt-0.5">{{ $systemStats['cache_driver'] }}</p>
        </div>
    </div>

    {{-- Main Settings Form (General, Gamification, Community) --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: General Platform Settings --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Asosiy platforma sozlamalari</h2>
                    <p class="text-xs text-mist">Sayt nomi, kontakt ma'lumotlari va sahifalash parametrlari</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {{-- App Name --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Platforma nomi (APP_NAME) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="app_name"
                               value="{{ old('app_name', setting('app_name', config('app.name', 'Kitobxon'))) }}"
                               required
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                               placeholder="Kitobxon">
                        @error('app_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Contact Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Aloqa email manzili <span class="text-rose-400">*</span>
                        </label>
                        <input type="email" name="contact_email"
                               value="{{ old('contact_email', setting('contact_email', env('MAIL_FROM_ADDRESS', 'admin@kitobxon.uz'))) }}"
                               required
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                               placeholder="admin@kitobxon.uz">
                        @error('contact_email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- App URL --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Sayt URL manzili (APP_URL) <span class="text-rose-400">*</span>
                        </label>
                        <input type="url" name="app_url"
                               value="{{ old('app_url', setting('app_url', config('app.url', 'http://localhost/Kitob/public'))) }}"
                               required
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                               placeholder="http://localhost/Kitob/public">
                        @error('app_url') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Timezone --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Vaqt zonasi <span class="text-rose-400">*</span>
                        </label>
                        @php $currentTimezone = setting('timezone', config('app.timezone', 'Asia/Tashkent')); @endphp
                        <select name="timezone" class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <option value="Asia/Tashkent" {{ $currentTimezone === 'Asia/Tashkent' ? 'selected' : '' }}>Asia/Tashkent (UTC+5)</option>
                            <option value="Asia/Samarkand" {{ $currentTimezone === 'Asia/Samarkand' ? 'selected' : '' }}>Asia/Samarkand (UTC+5)</option>
                            <option value="UTC" {{ $currentTimezone === 'UTC' ? 'selected' : '' }}>UTC (Dunyo vaqti)</option>
                            <option value="Europe/Moscow" {{ $currentTimezone === 'Europe/Moscow' ? 'selected' : '' }}>Europe/Moscow (UTC+3)</option>
                        </select>
                    </div>

                    {{-- Per page --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Sahifadagi yozuvlar soni <span class="text-rose-400">*</span>
                        </label>
                        @php $currentPerPage = (int) setting('per_page', 15); @endphp
                        <select name="per_page" class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <option value="10" {{ $currentPerPage === 10 ? 'selected' : '' }}>10 ta</option>
                            <option value="15" {{ $currentPerPage === 15 ? 'selected' : '' }}>15 ta (tavsiya etiladi)</option>
                            <option value="20" {{ $currentPerPage === 20 ? 'selected' : '' }}>20 ta</option>
                            <option value="25" {{ $currentPerPage === 25 ? 'selected' : '' }}>25 ta</option>
                            <option value="50" {{ $currentPerPage === 50 ? 'selected' : '' }}>50 ta</option>
                        </select>
                    </div>

                    {{-- Telegram Channel --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Telegram kanal / aloqa
                        </label>
                        <input type="text" name="telegram_channel"
                               value="{{ old('telegram_channel', setting('telegram_channel', '@kitobxon_uz')) }}"
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                               placeholder="@kitobxon_uz">
                    </div>
                </div>

                {{-- Welcome message --}}
                <div>
                    <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                        Xush kelibsiz tabrigi (Bosh sahifa va bildirishnomalar uchun)
                    </label>
                    <input type="text" name="welcome_message"
                           value="{{ old('welcome_message', setting('welcome_message', 'Kitobxon platformasiga xush kelibsiz! Har kuni yangi bilimlar tomon intiling.')) }}"
                           class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                           placeholder="Kitobxon platformasiga xush kelibsiz!">
                </div>
            </div>
        </div>

        {{-- Section 2: Gamification & Reading Rules --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Gamifikatsiya & O'qish Qoidalari</h2>
                    <p class="text-xs text-mist">Ballar, tangalar, streak va test topshirish mezonlari</p>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {{-- Reading Points Per Minute --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            1 daqiqa mutolaa uchun ball <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="reading_points_per_minute" min="1" max="100"
                                   value="{{ old('reading_points_per_minute', setting('reading_points_per_minute', 1)) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <span class="absolute right-3.5 top-2.5 text-xs text-mist font-mono">ball / daq</span>
                        </div>
                        <p class="text-[11px] text-mist mt-1">Standart: 1 daqiqa kitob o'qish uchun 1 ball</p>
                    </div>

                    {{-- Reading Coins Per Minute --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            1 daqiqa mutolaa uchun tanga <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="reading_coins_per_minute" min="0" max="50"
                                   value="{{ old('reading_coins_per_minute', setting('reading_coins_per_minute', 1)) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <span class="absolute right-3.5 top-2.5 text-xs text-mist font-mono">tanga / daq</span>
                        </div>
                        <p class="text-[11px] text-mist mt-1">Do'konda mahsulot sotib olishga sarflanadi</p>
                    </div>

                    {{-- Streak Minimum Minutes --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Kunlik streak uchun minimal vaqt <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="streak_minimum_minutes" min="1" max="120"
                                   value="{{ old('streak_minimum_minutes', setting('streak_minimum_minutes', 15)) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <span class="absolute right-3.5 top-2.5 text-xs text-mist font-mono">daqiqa</span>
                        </div>
                        <p class="text-[11px] text-mist mt-1">Foydalanuvchi streakni saqlashi uchun zarur vaqt</p>
                    </div>

                    {{-- Quiz Passing Percent --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Testdan o'tish chegarasi (%) <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="quiz_passing_percent" min="10" max="100"
                                   value="{{ old('quiz_passing_percent', setting('quiz_passing_percent', 70)) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <span class="absolute right-3.5 top-2.5 text-xs text-mist font-mono">%</span>
                        </div>
                        <p class="text-[11px] text-mist mt-1">Minimal to'g'ri javoblar foizi</p>
                    </div>

                    {{-- Welcome Bonus Coins --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Ro'yxatdan o'tish bonusi <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="welcome_bonus_coins" min="0" max="1000"
                                   value="{{ old('welcome_bonus_coins', setting('welcome_bonus_coins', 50)) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                            <span class="absolute right-3.5 top-2.5 text-xs text-mist font-mono">tanga</span>
                        </div>
                        <p class="text-[11px] text-mist mt-1">Yangi foydalanuvchiga beriladigan dastlabki tangalar</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Community & Permissions --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Jamiyat & Guruhlar Sozlamalari</h2>
                    <p class="text-xs text-mist">Global chat, ro'yxatdan o'tish va guruhlarga a'zolik cheklovlari</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Global Chat Toggle --}}
                    <div class="p-4 rounded-card bg-ink-800 border border-ink-border flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-paper">Umumiy Global Chat</p>
                            <p class="text-xs text-mist mt-0.5">Barcha kitobxonlar uchun umumiy jonli chat xonasini yoqish yoki o'chirish</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="global_chat_enabled" value="1"
                                   {{ setting('global_chat_enabled', true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-ink-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- Registration Open Toggle --}}
                    <div class="p-4 rounded-card bg-ink-800 border border-ink-border flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-paper">Yangi a'zolarni qabul qilish</p>
                            <p class="text-xs text-mist mt-0.5">Saytda ochiq ro'yxatdan o'tish imkoniyatini ta'minlash</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="registration_open" value="1"
                                   {{ setting('registration_open', true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-ink-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- User Group Limit --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Talaba a'zo bo'ladigan max guruhlar <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" name="user_group_membership_limit" min="1" max="50"
                               value="{{ old('user_group_membership_limit', setting('user_group_membership_limit', 5)) }}"
                               required
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                        <p class="text-[11px] text-mist mt-1">Oddiy foydalanuvchi bir vaqtda qatnasha oladigan guruhlar soni</p>
                    </div>

                    {{-- Teacher Group Limit --}}
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Ustoz a'zo bo'ladigan max guruhlar <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" name="teacher_group_membership_limit" min="1" max="100"
                               value="{{ old('teacher_group_membership_limit', setting('teacher_group_membership_limit', 20)) }}"
                               required
                               class="w-full px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors">
                        <p class="text-[11px] text-mist mt-1">Ustoz / moderator a'zo bo'la oladigan guruhlar soni</p>
                    </div>
                </div>
            </div>

            {{-- Save All Settings Button --}}
            <div class="px-6 py-4 bg-ink-950/50 border-t border-ink-border flex items-center justify-between">
                <span class="text-xs text-mist font-mono">Barcha o'zgarishlar darhol bazada yangilanadi va kesh tozalanadi</span>
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-ink-950 font-semibold rounded-btn transition-colors text-sm shadow-glow-amber">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Sozlamalarni saqlash
                </button>
            </div>
        </div>
    </form>

    {{-- Grid for Maintenance Mode & Cache Control --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Maintenance Mode Card --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card"
             x-data="{ maintenance: {{ $isMaintenance ? 'true' : 'false' }} }">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Texnik xizmat ko'rsatish rejimi</h2>
                    <p class="text-xs text-mist">Saytni vaqtincha yopish yoki ochish (Maintenance mode)</p>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="p-4 rounded-card border"
                     :class="maintenance ? 'bg-amber-500/10 border-amber-500/30' : 'bg-ink-800 border-ink-border'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl" x-text="maintenance ? '⚠️' : '✅'"></span>
                            <div>
                                <p class="text-sm font-semibold" :class="maintenance ? 'text-amber-400' : 'text-emerald-400'">
                                    <span x-text="maintenance ? 'Texnik xizmat rejimi yoqilgan' : 'Platforma barcha foydalanuvchilar uchun ochiq'"></span>
                                </p>
                                <p class="text-xs text-mist mt-0.5" x-text="maintenance ? 'Oddiy tashrif buyuruvchilarga 503 xizmat sahifasi ko\'rsatiladi. Adminlar to\'liq kira oladi.' : 'Sayt 24/7 normal rejimda ishlamoqda.'"></p>
                            </div>
                        </div>

                        <button type="button"
                                @click="maintenance = !maintenance"
                                :class="maintenance ? 'bg-amber-500' : 'bg-ink-700'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none">
                            <span :class="maintenance ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.maintenance') }}">
                    @csrf
                    <input type="hidden" name="maintenance" :value="maintenance ? '1' : '0'">
                    <button type="submit"
                            :class="maintenance ? 'bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 hover:bg-ink-700 text-paper border border-ink-border'"
                            class="w-full flex items-center justify-center gap-2 px-5 py-2.5 text-sm rounded-btn transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-text="maintenance ? 'Texnik xizmat rejimini tasdiqlash va saqlash' : 'Saytni ochiq holatga o\'tkazish'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Cache Management Card --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Kesh boshqaruvi</h2>
                    <p class="text-xs text-mist">Tizim tezkor keshlarini tozalash va yangilash</p>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 gap-3">
                    {{-- All Caches --}}
                    <form method="POST" action="{{ route('admin.settings.cache') }}">
                        @csrf
                        <input type="hidden" name="action" value="clear">
                        <button type="submit"
                                class="w-full p-3.5 rounded-card bg-ink-800 border border-ink-border hover:border-rose-500/40 hover:bg-rose-500/10 text-left transition-colors group">
                            <span class="text-base">🧹</span>
                            <p class="text-xs font-semibold text-paper mt-2 group-hover:text-rose-400 transition-colors">Barcha keshlar</p>
                            <p class="text-[10px] text-mist font-mono mt-0.5">optimize:clear</p>
                        </button>
                    </form>

                    {{-- Config Cache --}}
                    <form method="POST" action="{{ route('admin.settings.cache') }}">
                        @csrf
                        <input type="hidden" name="action" value="config">
                        <button type="submit"
                                class="w-full p-3.5 rounded-card bg-ink-800 border border-ink-border hover:border-sky-500/40 hover:bg-sky-500/10 text-left transition-colors group">
                            <span class="text-base">⚙️</span>
                            <p class="text-xs font-semibold text-paper mt-2 group-hover:text-sky-400 transition-colors">Config kesh</p>
                            <p class="text-[10px] text-mist font-mono mt-0.5">config:clear</p>
                        </button>
                    </form>

                    {{-- Route Cache --}}
                    <form method="POST" action="{{ route('admin.settings.cache') }}">
                        @csrf
                        <input type="hidden" name="action" value="route">
                        <button type="submit"
                                class="w-full p-3.5 rounded-card bg-ink-800 border border-ink-border hover:border-emerald-500/40 hover:bg-emerald-500/10 text-left transition-colors group">
                            <span class="text-base">🧭</span>
                            <p class="text-xs font-semibold text-paper mt-2 group-hover:text-emerald-400 transition-colors">Route kesh</p>
                            <p class="text-[10px] text-mist font-mono mt-0.5">route:clear</p>
                        </button>
                    </form>

                    {{-- View Cache --}}
                    <form method="POST" action="{{ route('admin.settings.cache') }}">
                        @csrf
                        <input type="hidden" name="action" value="view">
                        <button type="submit"
                                class="w-full p-3.5 rounded-card bg-ink-800 border border-ink-border hover:border-amber-500/40 hover:bg-amber-500/10 text-left transition-colors group">
                            <span class="text-base">🎨</span>
                            <p class="text-xs font-semibold text-paper mt-2 group-hover:text-amber-400 transition-colors">View kesh</p>
                            <p class="text-[10px] text-mist font-mono mt-0.5">view:clear</p>
                        </button>
                    </form>
                </div>

                <div class="mt-4 p-3 rounded-card bg-ink-950/60 border border-ink-border/60">
                    <p class="text-[11px] text-mist leading-relaxed">
                        💡 Kesh tozalangandan so'ng, tizim yangilangan sozlamalar va andozalarni to'g'ridan-to'g'ri qayta yuklaydi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- System Tools: Storage Link & SMTP Test Email --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Tool 1: Storage Symlink Repair --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">Fayllar xotirasi (Storage Link)</h2>
                    <p class="text-xs text-mist">Kitob muqovalari va yuklangan fayllar havolasini yangilash</p>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <p class="text-xs text-mist leading-relaxed">
                    Agar foydalanuvchilar yoki admin yuklagan rasmlar, kitob muqovalari va audiolari ko'rinmay qolsa,
                    ushbu tugma <span class="font-mono text-paper">public/storage</span> va <span class="font-mono text-paper">storage/app/public</span> o'rtasidagi simvolik bog'lanishni avtomatik tiklaydi.
                </p>

                <div class="flex items-center justify-between p-3.5 rounded-card bg-ink-800 border border-ink-border">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $systemStats['storage_symlink'] ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        <span class="text-xs font-medium text-paper">
                            {{ $systemStats['storage_symlink'] ? 'Storage havolasi faol va ulangan' : 'Storage havolasi ulanmagan' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.storage-link') }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-ink-700 hover:bg-ink-600 border border-ink-border text-paper font-semibold rounded-btn text-xs transition-colors flex items-center gap-1.5">
                            <span>🔗</span>
                            Storage Link yaratish
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tool 2: Test Email Sender --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-card">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-ink-border bg-ink-950/50">
                <div class="w-8 h-8 rounded-btn bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-paper">SMTP / Email aloqasini tekshirish</h2>
                    <p class="text-xs text-mist">Server pochtani to'g'ri yuborayotganini sinov xati orqali tekshirish</p>
                </div>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.settings.test-email') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">
                            Qabul qiluvchi test email
                        </label>
                        <div class="flex gap-2">
                            <input type="email" name="test_email"
                                   value="{{ auth()->user()->email ?? 'admin@kitobxon.uz' }}"
                                   required
                                   class="flex-1 px-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-input text-sm text-paper focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-colors"
                                   placeholder="sizning-emailingiz@gmail.com">
                            <button type="submit"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-btn text-xs transition-colors flex items-center gap-2 flex-shrink-0">
                                <span>✉️</span>
                                Sinov xatini yuborish
                            </button>
                        </div>
                    </div>
                    <p class="text-[11px] text-mist">
                        Xat jo'natilganda SMTP ulanishi va email shlyuzi holati darhol aniqlanadi.
                    </p>
                </form>
            </div>
        </div>

    </div>

</div>

@endsection
