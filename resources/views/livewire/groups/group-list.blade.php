<div class="max-w-6xl mx-auto space-y-8 pb-16">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Kitobxon Guruhlari</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Do'stlaringiz yoki hamfikrlaringiz bilan birga o'qing</p>
        </div>
        <button wire:click="$set('showCreateModal', true)"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
            <span>+ Yangi guruh ochish</span>
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($groups as $group)
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4 flex flex-col" wire:key="group-{{ $group->id }}">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl {{ $group->is_private ? 'bg-slate-500/10 text-slate-500' : 'bg-amber-500/10 text-amber-500' }} flex items-center justify-center text-2xl font-bold">
                        {{ $group->is_private ? '🔒' : '🚀' }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $group->name }}</h3>
                        <span class="text-[11px] text-slate-400">{{ $group->members_count }} ta a'zo • {{ $group->is_private ? 'Yopiq' : 'Ommaviy' }}</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2 flex-1">
                    {{ $group->description ?? 'Tavsif yo\'q' }}
                </p>

                @if ($group->book)
                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">📖 {{ $group->book->title }}</span>
                @endif

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                    <span class="text-xs text-slate-400 truncate">{{ $group->is_owner ? '👑 Siz yaratkacingiz' : ($group->is_member ? '✓ A\'zo' : 'Faol muloqot') }}</span>

                    @if ($group->is_owner)
                        <a href="{{ route('groups.show', $group->id) }}" class="px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">Ochish</a>
                    @elseif ($group->is_member)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('groups.show', $group->id) }}" class="px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">Ochish</a>
                            <button wire:click="leave({{ $group->id }})" wire:confirm="Guruhni tark etasizmi?" class="text-[11px] text-slate-400 hover:text-rose-500 font-semibold">Chiqish</button>
                        </div>
                    @else
                        <button wire:click="join({{ $group->id }})"
                            class="px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                            Qo'shilish
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-slate-400 text-sm">
                Hali guruhlar yo'q — birinchi guruhni oching! 🚀
            </div>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="relative w-full max-w-md p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Yangi guruh ochish</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Guruh nomi</label>
                    <input type="text" wire:model="name" placeholder="Masalan: Atom Odatlar Challengers"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tavsif (ixtiyoriy)</label>
                    <textarea rows="3" wire:model="description" placeholder="Guruh haqida qisqacha..."
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white resize-none"></textarea>
                    @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 font-medium">
                    <input type="checkbox" wire:model="isPrivate" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Yopiq guruh (faqat taklif bilan)
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="$set('showCreateModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700">Bekor qilish</button>
                    <button wire:click="create" wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50">
                        Yaratish
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
