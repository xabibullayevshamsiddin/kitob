@extends('admin.layouts.app')

@section('title', 'Foydalanuvchini tahrirlash')
@section('breadcrumb', 'Foydalanuvchini tahrirlash')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Back link --}}
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-300 transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Foydalanuvchilar ro'yxatiga qaytish
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- User Profile Card --}}
        <div class="md:col-span-1">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                {{-- Cover gradient --}}
                <div class="h-24 bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 relative">
                    <div class="absolute inset-0 opacity-30">
                        <div class="absolute top-2 right-2 w-16 h-16 rounded-full bg-white/20"></div>
                        <div class="absolute bottom-1 left-3 w-10 h-10 rounded-full bg-white/10"></div>
                    </div>
                </div>

                {{-- Avatar --}}
                <div class="flex flex-col items-center px-5 pb-5 -mt-10">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-3xl font-bold text-white shadow-2xl shadow-indigo-900/50 border-4 border-slate-900">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3 class="mt-3 text-base font-bold text-white text-center">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5 text-center">{{ $user->email }}</p>

                    {{-- Current role badge --}}
                    @php
                        $currentRole = $user->roles->first()?->name ?? 'student';
                        $roleConfig = [
                            'admin'   => ['label' => 'Admin',       'class' => 'bg-red-500/15 text-red-400 border-red-500/20'],
                            'teacher' => ['label' => "O'qituvchi",  'class' => 'bg-blue-500/15 text-blue-400 border-blue-500/20'],
                            'student' => ['label' => "O'quvchi",    'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                        ];
                        $rc = $roleConfig[$currentRole] ?? ['label' => $currentRole, 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/20'];
                    @endphp
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold border {{ $rc['class'] }}">
                            Joriy rol: {{ $rc['label'] }}
                        </span>
                    </div>

                    {{-- Stats --}}
                    <div class="w-full mt-4 pt-4 border-t border-slate-800 grid grid-cols-2 gap-3">
                        <div class="text-center">
                            <p class="text-lg font-bold text-white">{{ $user->points ?? 0 }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Ball</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-white">{{ $user->created_at->format('d.m.y') }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">A'zo bo'lgan</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="mt-4 bg-slate-900 border border-red-500/20 rounded-2xl p-4">
                <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-3">Xavfli zona</p>
                <div x-data="{ confirm: false }">
                    <button x-show="!confirm" @click="confirm = true"
                            class="w-full flex items-center justify-center gap-2 py-2.5 bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 rounded-xl text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Foydalanuvchini o'chirish
                    </button>
                    <div x-show="confirm" class="space-y-2" style="display:none;">
                        <p class="text-xs text-red-400 text-center font-medium">Ishonchingiz komilmi? Bu amalni bekor qilib bo'lmaydi.</p>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition-colors">
                                Ha, o'chirib yuborish
                            </button>
                        </form>
                        <button @click="confirm = false" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-medium transition-colors border border-slate-700">
                            Bekor qilish
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Form --}}
        <div class="md:col-span-2">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800">
                    <h2 class="text-base font-semibold text-white">Ma'lumotlarni tahrirlash</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Foydalanuvchi rolini va ma'lumotlarini o'zgartiring</p>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Name (readonly) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ism</label>
                        <div class="flex items-center gap-3 px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-sm text-slate-400">{{ $user->name }}</span>
                            <span class="ml-auto text-xs text-slate-600 bg-slate-800 px-2 py-0.5 rounded">O'zgarmaydi</span>
                        </div>
                    </div>

                    {{-- Email (readonly) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <div class="flex items-center gap-3 px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm text-slate-400">{{ $user->email }}</span>
                            <span class="ml-auto text-xs text-slate-600 bg-slate-800 px-2 py-0.5 rounded">O'zgarmaydi</span>
                        </div>
                    </div>

                    {{-- Role select --}}
                    <div>
                        <label for="role" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                            Rol <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="role" id="role"
                                    class="w-full appearance-none pl-4 pr-10 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer">
                                @foreach($roles as $role)
                                    @php
                                        $isSelected = $user->roles->first()?->name === $role->name;
                                    @endphp
                                    <option value="{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $role->name === 'admin' ? 'Admin' : ($role->name === 'teacher' ? "O'qituvchi" : "O'quvchi") }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('role')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror

                        {{-- Role descriptions --}}
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="px-3 py-2.5 rounded-xl bg-red-500/5 border border-red-500/15 text-center">
                                <p class="text-xs font-semibold text-red-400">Admin</p>
                                <p class="text-[10px] text-slate-600 mt-0.5">To'liq kirish</p>
                            </div>
                            <div class="px-3 py-2.5 rounded-xl bg-blue-500/5 border border-blue-500/15 text-center">
                                <p class="text-xs font-semibold text-blue-400">O'qituvchi</p>
                                <p class="text-[10px] text-slate-600 mt-0.5">Kitob boshqaruvi</p>
                            </div>
                            <div class="px-3 py-2.5 rounded-xl bg-emerald-500/5 border border-emerald-500/15 text-center">
                                <p class="text-xs font-semibold text-emerald-400">O'quvchi</p>
                                <p class="text-[10px] text-slate-600 mt-0.5">Faqat o'qish</p>
                            </div>
                        </div>
                    </div>

                    {{-- Points --}}
                    <div>
                        <label for="points" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ball (ixtiyoriy)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-amber-400">
                                ⭐
                            </div>
                            <input type="number" name="points" id="points" value="{{ $user->points ?? 0 }}" min="0"
                                   class="w-full pl-9 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-slate-800 pt-5">
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-all shadow-lg shadow-indigo-900/30 hover:shadow-indigo-900/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Saqlash
                            </button>
                            <a href="{{ route('admin.users.index') }}"
                               class="flex-1 flex items-center justify-center py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-xl border border-slate-700 transition-colors">
                                Bekor qilish
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Activity log placeholder --}}
            <div class="mt-4 bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-white mb-3">Faollik tarixi</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Tizimga kirdi', 'text-emerald-400', now()->subMinutes(15)],
                        ['Profil yangilandi', 'text-blue-400', now()->subDays(2)],
                        ["Ro'yxatdan o'tdi", 'text-indigo-400', $user->created_at],
                    ] as [$action, $color, $time])
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $color) }} flex-shrink-0"></div>
                            <span class="{{ $color }}">{{ $action }}</span>
                            <span class="ml-auto text-xs text-slate-600">{{ $time->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
