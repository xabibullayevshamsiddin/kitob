@extends('admin.layouts.app')

@section('title', 'Foydalanuvchilar')
@section('breadcrumb', 'Foydalanuvchilar')

@section('content')

{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Foydalanuvchilar</h1>
        <p class="text-sm text-slate-500 mt-0.5">Barcha ro'yxatdan o'tgan foydalanuvchilarni boshqaring</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-lg">
            Jami: <span class="text-indigo-400 font-semibold">{{ $users->total() }}</span> ta
        </span>
    </div>
</div>

{{-- Filters bar --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Ism yoki email bo'yicha qidirish..."
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-colors">
        </div>

        {{-- Role filter --}}
        <div class="relative">
            <select name="role"
                    class="appearance-none pl-4 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-colors cursor-pointer">
                <option value="">Barcha rollar</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>O'qituvchi</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>O'quvchi</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filtrlash
        </button>

        @if(request()->hasAny(['search', 'role']))
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl border border-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Tozalash
            </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-800">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Foydalanuvchi</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ball</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Qo'shilgan</th>
                    <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($users as $user)
                    @php
                        $role = $user->roles->first()?->name ?? 'student';
                        $roleConfig = [
                            'admin'   => ['label' => 'Admin',        'class' => 'bg-red-500/15 text-red-400 border-red-500/20'],
                            'teacher' => ['label' => "O'qituvchi",   'class' => 'bg-blue-500/15 text-blue-400 border-blue-500/20'],
                            'student' => ['label' => "O'quvchi",     'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                        ];
                        $rc = $roleConfig[$role] ?? ['label' => $role, 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/20'];
                        $avatarColors = ['from-indigo-500 to-violet-600', 'from-rose-500 to-pink-600', 'from-amber-500 to-orange-600', 'from-teal-500 to-cyan-600', 'from-emerald-500 to-green-600'];
                        $colorIndex = crc32($user->name) % count($avatarColors);
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors group">
                        <td class="px-5 py-4 text-sm text-slate-600 font-mono">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $avatarColors[$colorIndex] }} flex items-center justify-center text-sm font-bold text-white flex-shrink-0 shadow-lg">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-600">ID: {{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-400">{{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $rc['class'] }}">
                                {{ $rc['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5">
                                <span class="text-amber-400">⭐</span>
                                <span class="text-sm font-medium text-slate-300">{{ $user->points ?? 0 }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-400">{{ $user->created_at->format('d.m.Y') }}</p>
                            <p class="text-xs text-slate-600">{{ $user->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-500/20 hover:border-indigo-500/40 rounded-lg text-xs font-medium transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Tahrirlash
                                </a>

                                {{-- Delete --}}
                                <div x-data="{ confirm: false }">
                                    <button x-show="!confirm"
                                            @click="confirm = true"
                                            class="flex items-center gap-1.5 px-3 py-1.5 bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 hover:border-red-500/40 rounded-lg text-xs font-medium transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        O'chirish
                                    </button>

                                    <div x-show="confirm" class="flex items-center gap-1.5" style="display:none;">
                                        <span class="text-xs text-red-400 font-medium">Ishonchingiz komilmi?</span>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-medium transition-colors">
                                                Ha
                                            </button>
                                        </form>
                                        <button @click="confirm = false" class="px-2.5 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-xs font-medium transition-colors">
                                            Yo'q
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="text-5xl mb-4">👤</div>
                            <p class="text-slate-400 font-medium mb-1">Foydalanuvchilar topilmadi</p>
                            <p class="text-slate-600 text-sm">Qidiruv shartlarini o'zgartirib ko'ring</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-500">
                {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }} ta natija
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($users->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800 rounded-lg cursor-not-allowed">
                        ← Oldingi
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">
                        ← Oldingi
                    </a>
                @endif

                {{-- Page numbers --}}
                @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
                    @if($page == $users->currentPage())
                        <span class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-white bg-indigo-600 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">
                        Keyingi →
                    </a>
                @else
                    <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800 rounded-lg cursor-not-allowed">
                        Keyingi →
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
