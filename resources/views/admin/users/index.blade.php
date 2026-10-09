@extends('admin.layouts.app')

@section('title', 'Foydalanuvchilar')
@section('breadcrumb', 'Foydalanuvchilar')

@section('content')

<div x-data="{
    banModalOpen: false,
    rulesModalOpen: false,
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

{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold font-serif text-paper">Foydalanuvchilar</h1>
        <p class="text-xs text-mist font-mono mt-0.5">Barcha ro'yxatdan o'tgan foydalanuvchilarni boshqaring</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
        <button type="button"
                @click="rulesModalOpen = true"
                class="ks-btn-gold py-1.5 px-3 text-xs inline-flex items-center gap-1.5 font-mono font-bold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Qoidalar & Ban me'yorlari</span>
        </button>

        <span class="text-xs text-mist bg-ink-950 border border-ink-border px-3 py-1.5 rounded-badge font-mono">
            Jami: <span class="text-amber-400 font-bold">{{ $users->total() }}</span> ta
        </span>
    </div>
</div>

{{-- Filters bar --}}
<div class="bg-ink-900 border border-ink-border rounded-panel p-3.5 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-2.5">
        {{-- Search --}}
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Ism yoki email bo'yicha qidirish..."
                   class="ks-input pl-9 text-xs">
        </div>

        {{-- Role filter --}}
        <div class="relative">
            <select name="role"
                    class="ks-input py-2 px-3 text-xs font-mono pr-8">
                <option value="">Barcha rollar</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>O'qituvchi</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>O'quvchi</option>
            </select>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="ks-btn-primary py-2 px-4 text-xs font-mono inline-flex items-center gap-1.5 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span>Filtrlash</span>
        </button>

        @if(request()->hasAny(['search', 'role']))
            <a href="{{ route('admin.users.index') }}"
               class="ks-btn-ghost py-2 px-3 text-xs font-mono inline-flex items-center gap-1.5 shrink-0">
                <span>Tozalash</span>
            </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-sm">
    <div class="admin-table-scroll overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-ink-950/60 border-b border-ink-border text-[11px] font-mono uppercase text-mist">
                    <th class="py-2.5 px-3.5">#</th>
                    <th class="py-2.5 px-3.5">Foydalanuvchi</th>
                    <th class="py-2.5 px-3.5">Email</th>
                    <th class="py-2.5 px-3.5">Rol</th>
                    <th class="py-2.5 px-3.5">Holat</th>
                    <th class="py-2.5 px-3.5">Ball</th>
                    <th class="py-2.5 px-3.5">Qo'shilgan</th>
                    <th class="py-2.5 px-3.5 text-right">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-border/50 text-xs">
                @forelse($users as $user)
                    @php
                        $role = $user->roles->first()?->name ?? 'student';
                        $roleConfig = [
                            'admin'   => ['label' => 'Admin',        'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/30'],
                            'teacher' => ['label' => "O'qituvchi",   'class' => 'bg-ink-950 text-mist border-ink-border'],
                            'student' => ['label' => "O'quvchi",     'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                        ];
                        $rc = $roleConfig[$role] ?? ['label' => $role, 'class' => 'bg-ink-950 text-mist border-ink-border'];
                        $isBanned = $user->isBanned();
                        $isAdminTarget = $user->isAdmin() || $user->role === 'admin';
                    @endphp
                    <tr class="hover:bg-ink-800/40 transition-colors group">
                        <td class="py-2.5 px-3.5 text-mist font-mono text-[11px]">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="py-2.5 px-3.5">
                            <div class="flex items-center gap-2.5">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                         class="w-7 h-7 rounded-btn object-cover bg-ink-950 border border-ink-border flex-shrink-0">
                                @else
                                    <div class="w-7 h-7 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-xs font-mono font-bold text-paper flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-paper group-hover:text-amber-400 transition-colors truncate">{{ $user->name }}</p>
                                    <p class="text-[10px] text-mist font-mono truncate">ID: {{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3.5 font-mono text-[11px] text-mist">
                            {{ $user->email }}
                        </td>
                        <td class="py-2.5 px-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold border {{ $rc['class'] }}">
                                {{ $rc['label'] }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3.5">
                            @if($isBanned)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold bg-rose-500/15 text-rose-300 border border-rose-500/30"
                                      title="{{ $user->ban_reason ? 'Sabab: ' . $user->ban_reason : '' }}">
                                    <span>Bloklangan</span>
                                    <span class="opacity-80">({{ $user->ban_remaining }})</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                                    <span>Faol</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 font-mono text-[11px]">
                            <span class="text-amber-400 font-bold">{{ $user->total_points }}</span>
                        </td>
                        <td class="py-2.5 px-3.5 font-mono text-[11px] text-mist">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="py-2.5 px-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                {{-- Ban / Unban --}}
                                @if($user->id === auth()->id())
                                    <span class="text-[10px] font-mono text-mist italic px-1.5 py-0.5 bg-ink-950 rounded-badge border border-ink-border">
                                        (Siz)
                                    </span>
                                @elseif($isAdminTarget)
                                    <span class="text-[10px] font-mono text-amber-400/80 px-1.5 py-0.5 bg-ink-950 rounded-badge border border-amber-500/25" title="Admin himoyalangan">
                                        🛡️ Himoyalangan
                                    </span>
                                @elseif($isBanned)
                                    <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 rounded-btn text-[10px] font-mono font-bold transition-all">
                                            Yechish
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            @click="openBan({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="px-2 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 rounded-btn text-[10px] font-mono font-bold transition-all">
                                        Ban
                                    </button>
                                @endif

                                {{-- Edit --}}
                                @if(!$isAdminTarget)
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="ks-btn-ghost py-1 px-2 text-[10px] font-mono inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Tahrirlash</span>
                                    </a>
                                @endif

                                {{-- Delete --}}
                                @if(!$isAdminTarget)
                                <div x-data="{ confirm: false }">
                                    <button x-show="!confirm"
                                            @click="confirm = true"
                                            class="px-2 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 rounded-btn text-[10px] font-mono font-medium transition-all">
                                        O'chirish
                                    </button>

                                    <div x-show="confirm" class="flex items-center gap-1" style="display:none;">
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-0.5 bg-rose-600 hover:bg-rose-500 text-white rounded-btn text-[10px] font-mono">
                                                Ha
                                            </button>
                                        </form>
                                        <button @click="confirm = false" class="px-2 py-0.5 bg-ink-950 text-mist rounded-btn text-[10px] font-mono">
                                            Yo'q
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-mist font-mono text-xs">
                            Foydalanuvchilar topilmadi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="p-3.5 border-t border-ink-border">
            {{ $users->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif

    {{-- Interactive Ban Modal --}}
    <div x-show="banModalOpen" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         @keydown.escape.window="banModalOpen = false">
        <div class="ks-panel relative w-full max-w-lg bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-6 shadow-2xl text-left space-y-4"
             @click.away="banModalOpen = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-btn bg-ink-950 border border-rose-500/30 text-rose-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold font-serif text-paper">Foydalanuvchini bloklash (Ban)</h3>
                        <p class="text-xs text-mist font-mono mt-0.5">
                            Qoidabuzar: <span class="text-amber-400 font-bold" x-text="targetUserName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="banModalOpen = false" class="text-mist hover:text-paper p-1 rounded-btn">✕</button>
            </div>

            <form :action="banActionUrl" method="POST" class="space-y-4">
                @csrf

                {{-- Muddat tanlash --}}
                <div class="space-y-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-mist">Bloklash muddatini tanlang:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label class="flex items-center gap-2.5 p-2 rounded-btn border border-ink-border bg-ink-950 cursor-pointer">
                            <input type="radio" name="duration" value="1_hour" class="accent-amber-500">
                            <div>
                                <p class="text-xs font-bold text-paper font-mono">1 soat</p>
                                <p class="text-[10px] text-mist">Vaqtincha ogohlantirish</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-2 rounded-btn border border-ink-border bg-ink-950 cursor-pointer">
                            <input type="radio" name="duration" value="1_day" checked class="accent-amber-500">
                            <div>
                                <p class="text-xs font-bold text-paper font-mono">1 kun</p>
                                <p class="text-[10px] text-mist">24 soatga cheklash</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-2 rounded-btn border border-ink-border bg-ink-950 cursor-pointer">
                            <input type="radio" name="duration" value="1_week" class="accent-amber-500">
                            <div>
                                <p class="text-xs font-bold text-paper font-mono">1 hafta</p>
                                <p class="text-[10px] text-mist">7 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-2 rounded-btn border border-ink-border bg-ink-950 cursor-pointer">
                            <input type="radio" name="duration" value="1_month" class="accent-amber-500">
                            <div>
                                <p class="text-xs font-bold text-paper font-mono">1 oy</p>
                                <p class="text-[10px] text-mist">30 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="sm:col-span-2 flex items-center gap-2.5 p-2 rounded-btn border border-rose-500/30 bg-ink-950 cursor-pointer">
                            <input type="radio" name="duration" value="permanent" class="accent-rose-500">
                            <div>
                                <p class="text-xs font-bold text-rose-300 font-mono">Doimiy (Butun umrga)</p>
                                <p class="text-[10px] text-mist">Foydalanuvchi butunlay bloklanadi</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Sabab --}}
                <div class="space-y-1">
                    <label class="block text-xs font-mono uppercase tracking-wider text-mist">Ban berish sababi (ixtiyoriy):</label>
                    <input type="text" name="reason" placeholder="Masalan: Chatda haqoratli so'zlar / qoidabuzarlik"
                           class="ks-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="banModalOpen = false"
                            class="ks-btn-ghost py-1.5 px-3 text-xs">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="ks-btn-primary py-1.5 px-4 text-xs font-mono">
                        Bloklashni tasdiqlash
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rules Modal -->
    <div x-show="rulesModalOpen"
         x-cloak
         @keydown.escape.window="rulesModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm">

        <div @click.away="rulesModalOpen = false"
             class="ks-panel bg-ink-900 border border-ink-border rounded-panel max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden relative">

            <div class="px-5 py-3.5 border-b border-ink-border flex items-center justify-between bg-ink-950/60 flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="text-amber-400 font-bold">✦</span>
                    <div>
                        <h3 class="text-sm font-bold font-serif text-paper">Qoidabuzarliklar va Ban Me'yorlari</h3>
                        <p class="text-[11px] text-mist font-mono">Adminlar uchun intizomiy choralar qo'llanmasi</p>
                    </div>
                </div>
                <button type="button" @click="rulesModalOpen = false" class="text-mist hover:text-paper p-1 rounded-btn">✕</button>
            </div>

            <div class="p-5 overflow-y-auto space-y-3 text-xs">
                <div class="p-3 rounded-card bg-ink-950 border border-amber-500/25 text-mist text-xs">
                    <strong class="text-amber-400 font-serif">Admin eslatmasi:</strong> Ban berishda adolatli va xolis bo'ling. Foydalanuvchi birinchi marta xato qilgan bo'lsa, qisqa muddat (1 soat yoki 1 kun) yetarlidir. Qayta takrorlasa, muddatni bosqichma-bosqich oshiring.
                </div>

                <div class="p-3 rounded-card bg-ink-950 border border-ink-border space-y-1">
                    <span class="font-mono text-amber-400 font-bold uppercase text-[10px]">⏱ 1 SOAT — 1-Daraja: Yengil tartibbuzarlik</span>
                    <p class="text-mist">Spam, ketma-ket ma'nosiz xabarlar yuborish, mayda provokatsiyalar.</p>
                </div>

                <div class="p-3 rounded-card bg-ink-950 border border-ink-border space-y-1">
                    <span class="font-mono text-amber-400 font-bold uppercase text-[10px]">⏱ 1 KUN — 2-Daraja: O'rtacha tartibbuzarlik</span>
                    <p class="text-mist">Boshqa foydalanuvchilarga shaxsiy haqorat, noo'rin leksika, 1 soatlik bandan keyin takrorlash.</p>
                </div>

                <div class="p-3 rounded-card bg-ink-950 border border-ink-border space-y-1">
                    <span class="font-mono text-amber-500 font-bold uppercase text-[10px]">⏱ 1 HAFTA — 3-Daraja: Jiddiy tartibbuzarlik</span>
                    <p class="text-mist">Reklama tarqatish, guruhlarda doimiy ziddiyat chiqarish, ma'muriyatni haqorat qilish.</p>
                </div>

                <div class="p-3 rounded-card bg-ink-950 border border-rose-500/30 space-y-1">
                    <span class="font-mono text-rose-300 font-bold uppercase text-[10px]">🚫 DOIMIY — 4-Daraja: O'ta og'ir qoidabuzarlik</span>
                    <p class="text-mist">Qonunga zid materiallar, firibgarlik, tizimga zarar yetkazish harakatlari.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
