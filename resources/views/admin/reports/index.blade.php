@extends('admin.layouts.app')

@section('title', 'Foydalanuvchilar shikoyatlari')
@section('breadcrumb', 'Shikoyatlar')

@section('content')

{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"
     x-data="{
         banModalOpen: false,
         targetUserId: null,
         targetUserName: '',
         banActionUrl: '',
         openBan(userId, userName) {
             this.targetUserId = userId;
             this.targetUserName = userName;
             this.banActionUrl = '{{ url('/admin/users') }}/' + userId + '/ban';
             this.banModalOpen = true;
         }
     }">
    <div>
        <h1 class="text-xl font-bold text-white flex items-center gap-2">
            <span>🚩</span> Foydalanuvchilar Shikoyatlari (Reports)
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">Chat va guruhlardagi haqoratlar, qoidabuzarliklar bo'yicha tushgan shikoyatlar</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs text-slate-400 bg-slate-900 border border-slate-800 px-3.5 py-1.5 rounded-xl">
            Kutilayotgan: <span class="text-amber-400 font-bold">{{ \App\Models\Report::pending()->count() }}</span> ta
        </span>
    </div>
</div>

@if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold mb-4 flex items-center gap-2">
        <span>✓</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold mb-4 flex items-center gap-2">
        <span>⚠</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Reports Table Card --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm"
     x-data="{
         banModalOpen: false,
         targetUserId: null,
         targetUserName: '',
         banActionUrl: '',
         openBan(userId, userName) {
             this.targetUserId = userId;
             this.targetUserName = userName;
             this.banActionUrl = '{{ url('/admin/users') }}/' + userId + '/ban';
             this.banModalOpen = true;
         }
     }">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-800">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Vaqt & Bo'lim</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Shikoyat qilingan foydalanuvchi</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Shikoyat yuboruvchi</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nojo'ya xabar matni</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Holat</th>
                    <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($reports as $report)
                    @php
                        $offender = $report->reportedUser;
                        $reporter = $report->reporter;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        {{-- Time & Source --}}
                        <td class="px-5 py-4">
                            <p class="text-xs font-bold text-white">{{ $report->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-mono bg-slate-800 text-slate-400 border border-slate-700">
                                {{ $report->source }}
                            </span>
                        </td>

                        {{-- Offender info --}}
                        <td class="px-5 py-4">
                            @if($offender)
                                <div class="flex items-center gap-3">
                                    <img src="{{ $offender->avatar_url }}" class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-700" alt="{{ $offender->name }}">
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $offender->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">{{ '@' . $offender->username }} (ID: {{ $offender->id }})</p>
                                        @if($offender->isBanned())
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                                🚫 Bloklangan ({{ $offender->ban_remaining }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-500 font-mono">Foydalanuvchi topilmadi</span>
                            @endif
                        </td>

                        {{-- Reporter info --}}
                        <td class="px-5 py-4">
                            @if($reporter)
                                <div>
                                    <p class="text-xs font-semibold text-slate-300">{{ $reporter->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ '@' . $reporter->username }}</p>
                                </div>
                            @else
                                <span class="text-xs text-slate-500">Mehmon / Tizim</span>
                            @endif
                        </td>

                        {{-- Message Content --}}
                        <td class="px-5 py-4 max-w-sm">
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-200 font-sans leading-relaxed whitespace-pre-line break-words">
                                {{ $report->message_content }}
                            </div>
                            @if($report->link)
                                <a href="{{ $report->link }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-indigo-400 hover:underline mt-1.5">
                                    <span>🔗 Sahifani ochish</span>
                                </a>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($report->status === 'pending')
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/25">
                                    ⏳ Kutilmoqda
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                                    ✓ Hal qilindi
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                {{-- 1-Click Ban Button --}}
                                @if($offender && $offender->id !== auth()->id())
                                    @if(!$offender->isBanned())
                                        <button type="button"
                                                @click="openBan({{ $offender->id }}, '{{ addslashes($offender->name) }}')"
                                                class="px-3 py-1.5 rounded-lg bg-rose-600/20 hover:bg-rose-600 border border-rose-500/30 text-rose-400 hover:text-white text-xs font-bold transition-all flex items-center gap-1">
                                            <span>🚫 Ban berish</span>
                                        </button>
                                    @else
                                        <form action="{{ route('admin.users.unban', $offender) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/30 text-xs font-bold transition-all">
                                                ✅ Banni yechish
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                {{-- Resolve Toggle --}}
                                @if($report->status === 'pending')
                                    <form action="{{ route('admin.reports.resolve', $report) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-colors" title="Ko'rib chiqildi deb belgilash">
                                            ✓ Yopish
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Ushbu shikoyatni o\'chirasizmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="O'chirish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-500 text-sm">
                            <span class="text-4xl block mb-2">🎉</span>
                            Hozircha foydalanuvchilardan shikoyatlar mavjud emas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $reports->links() }}
        </div>
    @endif

    {{-- Interactive Ban Modal --}}
    <div x-show="banModalOpen" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fade-in"
         @keydown.escape.window="banModalOpen = false">
        <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl text-left space-y-5"
             @click.away="banModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xl shrink-0">
                        🚫
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Foydalanuvchini bloklash (Ban)</h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Qoidabuzar: <span class="text-amber-400 font-bold" x-text="targetUserName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="banModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">✕</button>
            </div>

            <form :action="banActionUrl" method="POST" class="space-y-5">
                @csrf

                {{-- Muddat tanlash --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Bloklash muddatini tanlang:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/60 cursor-pointer">
                            <input type="radio" name="duration" value="1_hour" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-bold text-white">1 soat</p>
                                <p class="text-[10px] text-slate-500">Vaqtincha ogohlantirish</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/60 cursor-pointer">
                            <input type="radio" name="duration" value="1_day" checked class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-bold text-white">1 kun</p>
                                <p class="text-[10px] text-slate-500">24 soatga cheklash</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/60 cursor-pointer">
                            <input type="radio" name="duration" value="1_week" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-bold text-white">1 hafta</p>
                                <p class="text-[10px] text-slate-500">7 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/60 cursor-pointer">
                            <input type="radio" name="duration" value="1_month" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-bold text-white">1 oy</p>
                                <p class="text-[10px] text-slate-500">30 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="sm:col-span-2 flex items-center gap-3 p-3 rounded-xl border border-rose-500/30 hover:border-rose-500 bg-rose-500/10 cursor-pointer">
                            <input type="radio" name="duration" value="permanent" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-black text-rose-400">Butun umrga (Doimiy ban)</p>
                                <p class="text-[10px] text-slate-400">Foydalanuvchi hisobi butunlay yopiladi</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Sabab --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Ban berish sababi (ixtiyoriy):</label>
                    <input type="text" name="reason" placeholder="Masalan: Chatda haqoratli so'zlar / qoidabuzarlik"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="banModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30 transition-all active:scale-95">
                        🚫 Bloklashni tasdiqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
