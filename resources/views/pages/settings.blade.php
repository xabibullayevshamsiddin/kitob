@extends('layouts.app')

@section('title', 'Sozlamalar')

@section('content')
<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white font-manrope">Hisob sozlamalari</h1>
        <p class="text-xs text-slate-400 mt-1">Shaxsiy ma'lumotlaringiz va o'qish afzalliklaringizni boshqaring</p>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->has('telegram'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs font-semibold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ $errors->first('telegram') }}</span>
        </div>
    @endif

    <!-- Profile Settings Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-6">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">Profil ma'lumotlari</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">To'liq ism</label>
                <input type="text" value="{{ auth()->user()->name }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
                <input type="text" value="{{ auth()->user()->username }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input type="text" value="{{ auth()->user()->email ?: 'Elektron pochta ko\'rsatilmagan' }}" disabled class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Telefon raqam</label>
                    @if(auth()->user()->phone)
                        @if(auth()->user()->hasVerifiedPhone())
                            <span class="text-[11px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">✅ Tasdiqlangan</span>
                        @else
                            <a href="{{ route('auth.telegram.verify-phone') }}" class="text-[11px] font-bold text-amber-500 hover:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20 transition-colors">
                                ⚠️ Tasdiqlanmagan (Botda tasdiqlash →)
                            </a>
                        @endif
                    @endif
                </div>
                <input type="text" value="{{ auth()->user()->phone ? \App\Support\PhoneNumber::mask(auth()->user()->phone) : 'Telefon kiritilmagan' }}" disabled class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bio</label>
                <textarea rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">{{ auth()->user()->bio }}</textarea>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                O'zgarishlarni saqlash
            </button>
        </div>
    </div>

    <!-- Telegram Integratsiyasi Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-500/10 flex items-center justify-center text-sky-400">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Telegram hisobi</h3>
                    <p class="text-[11px] text-slate-400">Tezkor kirish, xavfsizlik va muhim xabarnomalar uchun</p>
                </div>
            </div>

            @if(auth()->user()->isTelegramLinked())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Ulangan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/25">
                    Ulanmagan
                </span>
            @endif
        </div>

        @if(auth()->user()->isTelegramLinked())
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-3">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>{{ auth()->user()->telegram_username ? '@' . auth()->user()->telegram_username : 'Telegram foydalanuvchisi' }}</span>
                        @if(auth()->user()->isTelegramBlocked())
                            <span class="text-[10px] text-rose-500 bg-rose-500/10 px-2 py-0.5 rounded-full border border-rose-500/20">Bot bloklangan</span>
                        @endif
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono">ID: {{ auth()->user()->telegram_id }} • Ulandi: {{ auth()->user()->telegram_linked_at?->format('d.m.Y') }}</p>
                </div>

                <form method="POST" action="{{ route('auth.telegram.unlink') }}"
                      data-confirm="Haqiqatan ham Telegram hisobingizni uzmoqchimisiz?"
                      data-confirm-title="Telegram hisobni uzish"
                      data-confirm-type="danger"
                      data-confirm-btn="Ha, uzish">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-xl text-xs font-bold transition-all">
                        Telegramni uzish
                    </button>
                </form>
            </div>

            <!-- Bildirishnoma sozlamalari -->
            <form method="POST" action="{{ route('settings.telegram.notifications') }}" class="pt-2 space-y-4">
                @csrf
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Telegram bildirishnomalari</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/50 dark:border-slate-700/50 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <input type="checkbox" name="streak_reminder" value="1" {{ auth()->user()->prefersTelegramNotification('streak_reminder') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">🔥 Streak xavf ostida eslatmasi</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Kechki soat 20:30 da bugun hali mutolaa qilmagan bo'lsangiz, olovni saqlab qolish uchun bot eslatadi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/50 dark:border-slate-700/50 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <input type="checkbox" name="daily_quote" value="1" {{ auth()->user()->prefersTelegramNotification('daily_quote') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">✨ Kun hikmati</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Har kuni ertalab 09:00 da kitoblardan tanlangan ibratli iqtiboslarni bot orqali qabul qilish.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/50 dark:border-slate-700/50 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <input type="checkbox" name="coin_reward" value="1" {{ auth()->user()->prefersTelegramNotification('coin_reward') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">🪙 Tangalar va yutuqlar</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Testlarni muvaffaqiyatli topshirganda yoki do'kondan sovg'a olganda bildirishnoma.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/50 dark:border-slate-700/50 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <input type="checkbox" name="security" value="1" {{ auth()->user()->prefersTelegramNotification('security') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">🔐 Xavfsizlik ogohlantirishlari</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Parol yangilanganda yoki yangi qurilmadan kirilganda darhol xabar berish.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs rounded-xl shadow transition-all">
                        Bildirishnoma sozlamalarini saqlash
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 space-y-3">
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Hisobingizga Telegram'ni ulasangiz, keyingi safar parolsiz bir bosishda kira olasiz, do'kon va streak xabarnomalarini bot orqali olasiz.
                </p>

                <div class="pt-1">
                    <a href="{{ route('auth.telegram.redirect') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-500 hover:bg-sky-400 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 transition-all">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/></svg>
                        <span>Telegram hisobini ulash</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
