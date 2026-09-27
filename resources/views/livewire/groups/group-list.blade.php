<div class="max-w-6xl mx-auto space-y-8 pb-16">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Kitobxon Guruhlari</h1>
                @auth
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $myGroupCount >= $myGroupLimit ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20' }}">
                        <span>📊 Guruhlaringiz:</span>
                        <strong class="font-bold">{{ $myGroupCount }} / {{ $myGroupLimit }}</strong>
                    </span>
                @endauth
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Do'stlaringiz yoki hamfikrlaringiz bilan birga o'qing va fikr almashing</p>
        </div>
        <button wire:click="openCreateModal"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/25 active:scale-95 transition-all flex items-center gap-2 shrink-0">
            <span>+ Yangi guruh ochish</span>
        </button>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-semibold flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs font-semibold flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($groups as $group)
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all" wire:key="group-{{ $group->id }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl {{ $group->is_private ? 'bg-rose-500/10 border border-rose-500/20 text-rose-500' : 'bg-amber-500/10 border border-amber-500/20 text-amber-500' }} flex items-center justify-center text-2xl font-bold shrink-0">
                                {{ $group->is_private ? '🔒' : '🚀' }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $group->name }}</h3>
                                <span class="text-[11px] text-slate-400 block">{{ $group->members_count }} ta a'zo</span>
                            </div>
                        </div>

                        <!-- Status Badge & Delete Button -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($group->is_private)
                                <span class="px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-[10px] font-bold text-rose-400">
                                    🔒 Yopiq
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-bold text-emerald-400">
                                    Ommaviy
                                </span>
                            @endif

                            @if($group->can_delete)
                                <button wire:click="openDeleteModal({{ $group->id }})" title="Guruhni o'chirish"
                                    class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white border border-rose-500/20 flex items-center justify-center transition-all text-xs"
                                    aria-label="Guruhni o'chirish">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                        {{ $group->description ?? 'Guruh haqida ma\'lumot kiritilmagan.' }}
                    </p>

                    @if ($group->book)
                        <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold truncate flex items-center gap-1">
                            <span>📖</span> <span>{{ $group->book->title }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-400 truncate">
                        @if($group->is_owner)
                            <span class="text-amber-500 font-bold">👑 Siz yaratkancisiz</span>
                        @elseif($group->is_member)
                            <span class="text-emerald-500 font-semibold">✓ A'zo</span>
                        @else
                            {{ $group->is_private ? 'Parol kerak' : 'Ochiq guruh' }}
                        @endif
                    </span>

                    @if ($group->is_owner)
                        <a href="{{ route('groups.show', $group->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition-all">
                            Kirish →
                        </a>
                    @elseif ($group->is_member)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('groups.show', $group->id) }}" class="px-3.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                Kirish →
                            </a>
                            <button wire:click="leave({{ $group->id }})" wire:confirm="Guruhni tark etmoqchimisiz?" class="text-[11px] text-slate-400 hover:text-rose-500 font-semibold transition-colors">
                                Chiqish
                            </button>
                        </div>
                    @else
                        @if($group->is_private)
                            <button wire:click="openJoinModal({{ $group->id }})"
                                class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white border border-rose-500/25 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shrink-0">
                                <span>🔒 Parol bilan kirish</span>
                            </button>
                        @else
                            <button wire:click="join({{ $group->id }})"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition-all shrink-0">
                                Qo'shilish
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-8 space-y-3">
                <span class="text-4xl block">🚀</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hozircha guruhlar mavjud emas</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Birinchi bo'lib do'stlaringiz uchun umumiy yoki yopiq kitobxonlar guruhini oching!</p>
                <button wire:click="openCreateModal" class="px-5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                    + Birinchi guruhni yaratish
                </button>
            </div>
        @endforelse
    </div>

    @if ($groups->hasPages())
        <div class="pt-4">
            {{ $groups->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif

    <!-- Create Group Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" wire:click="$set('showCreateModal', false)"></div>
            <div class="relative w-full max-w-md p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#0e1422] border border-slate-200 dark:border-white/10 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Yangi guruh ochish</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Guruh nomi *</label>
                        <input type="text" wire:model.defer="name" placeholder="Masalan: Atom Odatlar Klubi"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tavsif (ixtiyoriy)</label>
                        <textarea rows="3" wire:model.defer="description" placeholder="Guruh nima maqsadda ochildi va kimlar uchun..."
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                        @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Private Group Checkbox -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                        <label class="flex items-center gap-2.5 text-xs text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                            <input type="checkbox" wire:model="isPrivate" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>🔒 Yopiq guruh (faqat parol bilan kiriladi)</span>
                        </label>

                        @if ($isPrivate)
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1.5">
                                <label class="block text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    Guruh parolini o'rnating *
                                </label>
                                <input type="text" wire:model.defer="password" placeholder="Masalan: 1234 yoki sirli_kod"
                                    class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-indigo-400/40 dark:border-indigo-500/40 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                                @error('password') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                                <p class="text-[11px] text-slate-400">Qo'shilmoqchi bo'lgan foydalanuvchilar faqat ushbu parolni to'g'ri kiritgandagina guruhga kira oladilar.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-white/10">
                    <button wire:click="$set('showCreateModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                        Bekor qilish
                    </button>
                    <button wire:click="create" wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50 flex items-center gap-1.5">
                        <span wire:loading.remove wire:target="create">Guruhni yaratish</span>
                        <span wire:loading wire:target="create">Yaratilmoqda...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Join Private Group Modal (Taste-Skill) -->
    @if ($showJoinModal && $this->targetGroup)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" wire:click="$set('showJoinModal', false)"></div>
            <div class="relative w-full max-w-sm p-6 sm:p-7 rounded-3xl bg-white dark:bg-[#0e1422] border border-slate-200 dark:border-white/10 shadow-2xl space-y-5 text-center">

                <!-- Padlock Icon Badge -->
                <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/15 border border-rose-500/20 text-rose-500 dark:text-rose-400 flex items-center justify-center text-2xl shadow-lg shadow-rose-500/10">
                    🔒
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                        «{{ $this->targetGroup->name }}»
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Ushbu guruh yopiq. Guruhga qo'shilish va xabarlarni o'qish uchun parolni kiriting:
                    </p>
                </div>

                <form wire:submit.prevent="submitJoinPassword" class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Guruh paroli</label>
                        <input type="password" wire:model.defer="joinPassword" autofocus placeholder="••••••••"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                        @error('joinPassword') 
                            <p class="text-rose-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p> 
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showJoinModal', false)"
                            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors text-center">
                            Bekor qilish
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/25 transition-all text-center flex items-center justify-center gap-1.5">
                            <span wire:loading.remove wire:target="submitJoinPassword">Kirish →</span>
                            <span wire:loading wire:target="submitJoinPassword">Tekshirilmoqda...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Group Confirmation Modal (Taste-Skill) -->
    @if ($showDeleteModal && $this->deleteTargetGroup)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-md p-6 sm:p-7 rounded-3xl bg-white dark:bg-[#0e1422] border border-slate-200 dark:border-white/10 shadow-2xl space-y-5 text-center">

                <!-- Danger Icon Badge -->
                <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/15 border border-rose-500/20 text-rose-500 dark:text-rose-400 flex items-center justify-center text-2xl shadow-lg shadow-rose-500/10">
                    🗑️
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        «{{ $this->deleteTargetGroup->name }}» guruhini o'chirmoqchimisiz?
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Ushbu amalni ortga qaytarib bo'lmaydi. Guruhdagi barcha xabarlar va unga a'zo bo'lgan barcha a'zolik ma'lumotlari butunlay o'chiriladi.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors">
                        Bekor qilish
                    </button>
                    <button type="button" wire:click="confirmDeleteGroup" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-500 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/25 transition-all flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="confirmDeleteGroup">Ha, o'chirilsin</span>
                        <span wire:loading wire:target="confirmDeleteGroup">O'chirilmoqda...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
