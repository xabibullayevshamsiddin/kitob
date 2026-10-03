<div class="max-w-6xl mx-auto space-y-8 pb-16">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">{{ __('site.groups.title') }}</h1>
                @auth
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ ($myMembershipLimit !== null && $myJoinedCount >= $myMembershipLimit) ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20' }}">
                        <span>👥 {{ __('site.groups.membership') }}</span>
                        <strong class="font-bold">{{ $myJoinedCount }} / {{ $myMembershipLimit === null ? '∞ (' . __('site.groups.unlimited') . ')' : $myMembershipLimit . ' ' . __('site.groups.groups_unit') }}</strong>
                    </span>
                @endauth
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('site.groups.subtitle') }}</p>
        </div>
        <button wire:click="openCreateModal"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/25 active:scale-95 transition-all flex items-center gap-2 shrink-0">
            <span>+ {{ __('site.groups.create_btn') }}</span>
        </button>
    </div>

    <!-- Flash Notifications via Toast -->
    @if (session()->has('success'))
        <div x-init="window.toast({ type: 'success', message: @js(session('success')), title: 'Muvaffaqiyatli!' })"></div>
    @endif
    @if (session()->has('error'))
        <div x-init="window.toast({ type: 'error', message: @js(session('error')), title: 'Xatolik yuz berdi' })"></div>
    @endif

    <!-- ── GURUHLAR FILTERLARI VA QIDIRUV ── -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-soft space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Privacy & Membership Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto">
                <button type="button" wire:click="setAllFilters"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ ($privacyFilter === 'all' && $membershipFilter === 'all') ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>{{ __('site.groups.all') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($privacyFilter === 'all' && $membershipFilter === 'all') ? 'bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-white' : 'bg-slate-200/60 dark:bg-slate-700 text-slate-500' }}">
                        {{ $totalGroupsCount }}
                    </span>
                </button>
                <button type="button" wire:click="setPrivacyFilter('public')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $privacyFilter === 'public' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-500 hover:text-emerald-500 dark:hover:text-emerald-400' }}">
                    <span>🌐 {{ __('site.groups.public') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $privacyFilter === 'public' ? 'bg-emerald-700 text-white' : 'bg-emerald-500/10 text-emerald-500' }}">
                        {{ $publicGroupsCount }}
                    </span>
                </button>
                <button type="button" wire:click="setPrivacyFilter('private')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $privacyFilter === 'private' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-slate-500 hover:text-rose-500 dark:hover:text-rose-400' }}">
                    <span>🔒 {{ __('site.groups.private') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $privacyFilter === 'private' ? 'bg-rose-700 text-white' : 'bg-rose-500/10 text-rose-500' }}">
                        {{ $privateGroupsCount }}
                    </span>
                </button>
                @auth
                    <button type="button" wire:click="setMembershipFilter('joined')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $membershipFilter === 'joined' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-500 hover:text-indigo-500 dark:hover:text-indigo-400' }}">
                        <span>👥 {{ __('site.groups.joined') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $membershipFilter === 'joined' ? 'bg-indigo-700 text-white' : 'bg-indigo-500/10 text-indigo-400' }}">
                            {{ $myJoinedCount }}
                        </span>
                    </button>
                    <button type="button" wire:click="setMembershipFilter('my_groups')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $membershipFilter === 'my_groups' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-500 hover:text-amber-500 dark:hover:text-amber-400' }}">
                        <span>👑 {{ __('site.groups.my_groups') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $membershipFilter === 'my_groups' ? 'bg-amber-700 text-white' : 'bg-amber-500/10 text-amber-500' }}">
                            {{ $myGroupCount }}
                        </span>
                    </button>
                @endauth
            </div>

            <!-- Qidiruv qatori -->
            <div class="relative flex-1 md:max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.groups.search_placeholder') }}"
                    class="w-full pl-9 pr-8 py-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-2xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- 2-qator: Saralash va filtrlarni tozalash -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-medium">{{ __('site.groups.sort_by') }}</span>
                <select wire:model="sortBy"
                    class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="latest">{{ __('site.groups.sort_latest') }}</option>
                    <option value="popular">{{ __('site.groups.sort_popular') }}</option>
                    <option value="name">{{ __('site.groups.sort_name') }}</option>
                </select>
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-500 text-xs font-semibold transition-all">
                    <span>{{ __('site.groups.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

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
                                <span class="text-[11px] text-slate-400 block">{{ $group->members_count }} {{ __('site.groups.members') }}</span>
                            </div>
                        </div>

                        <!-- Status Badge & Delete Button -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($group->is_private)
                                <span class="px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-[10px] font-bold text-rose-400">
                                    🔒 {{ __('site.groups.private') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-bold text-emerald-400">
                                    {{ __('site.groups.public') }}
                                </span>
                            @endif

                            @if($group->can_delete)
                                <button wire:click="openDeleteModal({{ $group->id }})" title="{{ __('site.groups.delete_group') }}"
                                    class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white border border-rose-500/20 flex items-center justify-center transition-all text-xs"
                                    aria-label="{{ __('site.groups.delete_group') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                        {{ $group->description ?? __('site.groups.no_desc') }}
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
                            <span class="text-amber-500 font-bold">👑 {{ __('site.groups.owner') }}</span>
                        @elseif($group->is_member)
                            <span class="text-emerald-500 font-semibold">✓ {{ __('site.groups.member') }}</span>
                        @else
                            {{ $group->is_private ? __('site.groups.password_required') : __('site.groups.open_group') }}
                        @endif
                    </span>

                    @if ($group->is_owner)
                        <a href="{{ route('groups.show', $group->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition-all">
                            {{ __('site.groups.enter') }}
                        </a>
                    @elseif ($group->is_member)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('groups.show', $group->id) }}" class="px-3.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                {{ __('site.groups.enter') }}
                            </a>
                            <button wire:click="leave({{ $group->id }})" wire:confirm="{{ __('site.groups.leave_confirm') }}" class="text-[11px] text-slate-400 hover:text-rose-500 font-semibold transition-colors">
                                {{ __('site.groups.leave') }}
                            </button>
                        </div>
                    @else
                        @if($group->is_private)
                            <button wire:click="openJoinModal({{ $group->id }})"
                                class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white border border-rose-500/25 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shrink-0">
                                <span>🔒 {{ __('site.groups.join_with_password') }}</span>
                            </button>
                        @else
                            <button wire:click="join({{ $group->id }})"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow transition-all shrink-0">
                                {{ __('site.groups.join') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-8 space-y-3">
                @if($hasActiveFilters)
                    <span class="text-4xl block">🔍</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.groups.no_results_t') }}</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('site.groups.no_results_s') }}</p>
                    <button type="button" wire:click="resetFilters" class="px-5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl shadow transition-all">
                        {{ __('site.groups.show_all') }}
                    </button>
                @else
                    <span class="text-4xl block">🚀</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.groups.empty') }}</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('site.groups.empty_sub') }}</p>
                    <button wire:click="openCreateModal" class="px-5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                        {{ __('site.groups.create_first') }}
                    </button>
                @endif
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
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('site.groups.create_title') }}</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('site.groups.name_label') }}</label>
                        <input type="text" wire:model.defer="name" placeholder="{{ __('site.groups.name_placeholder') }}"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('site.groups.desc_label') }}</label>
                        <textarea rows="3" wire:model.defer="description" placeholder="{{ __('site.groups.desc_placeholder') }}"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                        @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Private Group Checkbox -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                        <label class="flex items-center gap-2.5 text-xs text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                            <input type="checkbox" wire:model="isPrivate" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>{{ __('site.groups.private_label') }}</span>
                        </label>

                        @if ($isPrivate)
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1.5">
                                <label class="block text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ __('site.groups.password_label') }}
                                </label>
                                <input type="text" wire:model.defer="password" placeholder="{{ __('site.groups.password_placeholder') }}"
                                    class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-indigo-400/40 dark:border-indigo-500/40 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                                @error('password') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                                <p class="text-[11px] text-slate-400">{{ __('site.groups.password_hint') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-white/10">
                    <button wire:click="$set('showCreateModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                        {{ __('site.chat.cancel') }}
                    </button>
                    <button wire:click="create" wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50 flex items-center gap-1.5">
                        <span wire:loading.remove wire:target="create">{{ __('site.groups.create_btn') }}</span>
                        <span wire:loading wire:target="create">{{ __('site.groups.creating') }}</span>
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
                        {{ __('site.groups.join_modal_desc') }}
                    </p>
                </div>

                <form wire:submit.prevent="submitJoinPassword" class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('site.groups.password_field') }}</label>
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
                            {{ __('site.chat.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/25 transition-all text-center flex items-center justify-center gap-1.5">
                            <span wire:loading.remove wire:target="submitJoinPassword">{{ __('site.groups.enter') }}</span>
                            <span wire:loading wire:target="submitJoinPassword">{{ __('site.groups.checking') }}</span>
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
                        {{ __('site.groups.delete_confirm_title', ['name' => $this->deleteTargetGroup->name]) }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        {{ __('site.groups.delete_confirm_desc') }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors">
                        {{ __('site.chat.cancel') }}
                    </button>
                    <button type="button" wire:click="confirmDeleteGroup" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-500 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/25 transition-all flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="confirmDeleteGroup">{{ __('site.groups.delete_yes') }}</span>
                        <span wire:loading wire:target="confirmDeleteGroup">{{ __('site.groups.deleting') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
