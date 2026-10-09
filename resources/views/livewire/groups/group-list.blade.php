<div class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-paper font-serif">{{ __('site.groups.title') }}</h1>
                @auth
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill text-xs font-mono {{ ($myMembershipLimit !== null && $myJoinedCount >= $myMembershipLimit) ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'bg-ink-800 text-mist border border-ink-border' }}">
                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('site.groups.membership') }}:</span>
                        <strong class="text-paper">{{ $myJoinedCount }} / {{ $myMembershipLimit === null ? '∞' : $myMembershipLimit }}</strong>
                    </span>
                @endauth
            </div>
            <p class="text-xs sm:text-sm text-mist font-sans mt-1">{{ __('site.groups.subtitle') }}</p>
        </div>
        <button wire:click="openCreateModal"
            class="ks-btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            <span>{{ __('site.groups.create_btn') }}</span>
        </button>
    </div>


    <!-- ── GURUHLAR FILTERLARI VA QIDIRUV ── -->
    <div class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5 space-y-3.5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Privacy & Membership Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-ink-950/80 border border-ink-border rounded-btn overflow-x-auto no-scrollbar">
                <button type="button" wire:click="setAllFilters"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ ($privacyFilter === 'all' && $membershipFilter === 'all') ? 'bg-ink-800 text-amber-400 border border-ink-border shadow-sm' : 'text-mist hover:text-paper' }}">
                    <span>{{ __('site.groups.all') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] {{ ($privacyFilter === 'all' && $membershipFilter === 'all') ? 'bg-ink-900 text-amber-400' : 'bg-ink-800 text-mist' }}">
                        {{ $totalGroupsCount }}
                    </span>
                </button>
                <button type="button" wire:click="setPrivacyFilter('public')"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $privacyFilter === 'public' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-mist hover:text-emerald-400' }}">
                    <svg class="w-3 h-3 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <span>{{ __('site.groups.public') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-emerald-500/20 text-emerald-400">
                        {{ $publicGroupsCount }}
                    </span>
                </button>
                <button type="button" wire:click="setPrivacyFilter('private')"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $privacyFilter === 'private' ? 'bg-[#C1392B]/15 text-rose-300 border border-rose-500/30' : 'text-mist hover:text-rose-300' }}">
                    <svg class="w-3 h-3 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>{{ __('site.groups.private') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-rose-500/20 text-rose-300">
                        {{ $privateGroupsCount }}
                    </span>
                </button>
                @auth
                    <button type="button" wire:click="setMembershipFilter('joined')"
                        class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $membershipFilter === 'joined' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-amber-400' }}">
                        <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('site.groups.joined') }}</span>
                        <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-amber-500/20 text-amber-400">
                            {{ $myJoinedCount }}
                        </span>
                    </button>
                    <button type="button" wire:click="setMembershipFilter('my_groups')"
                        class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $membershipFilter === 'my_groups' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-amber-400' }}">
                        <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
                        <span>{{ __('site.groups.my_groups') }}</span>
                        <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-amber-500/20 text-amber-400">
                            {{ $myGroupCount }}
                        </span>
                    </button>
                @endauth
            </div>

            <!-- Qidiruv qatori -->
            <div class="relative flex-1 md:max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.groups.search_placeholder') }}"
                    class="w-full pl-8 pr-7 py-1.5 bg-ink-950/80 border border-ink-border rounded-btn text-xs font-sans text-paper placeholder-mist focus:border-amber-400 focus:outline-none transition-all">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-mist hover:text-paper font-mono text-xs">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- 2-qator: Saralash va filtrlarni tozalash -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2.5 border-t border-ink-border text-xs font-mono">
            <div class="flex items-center gap-2">
                <span class="text-mist">{{ __('site.groups.sort_by') }}</span>
                <select wire:model="sortBy"
                    class="px-2.5 py-1 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none">
                    <option value="latest">{{ __('site.groups.sort_latest') }}</option>
                    <option value="popular">{{ __('site.groups.sort_popular') }}</option>
                    <option value="name">{{ __('site.groups.sort_name') }}</option>
                </select>
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-amber-400 text-xs transition-all">
                    <span>{{ __('site.groups.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($groups as $group)
            <div class="p-5 rounded-panel bg-ink-900 border border-ink-border space-y-4 flex flex-col justify-between hover:border-amber-500/30 transition-all" wire:key="group-{{ $group->id }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($group->cover_image_url)
                                <img src="{{ $group->cover_image_url }}" alt="{{ $group->name }}" class="w-10 h-10 rounded-btn object-cover border border-ink-border shrink-0 shadow-sm">
                            @else
                                <div class="w-10 h-10 rounded-btn {{ $group->is_private ? 'bg-[#C1392B]/15 border border-rose-500/30 text-rose-300' : 'bg-amber-500/10 border border-amber-500/25 text-amber-400' }} flex items-center justify-center shrink-0">
                                    @if($group->is_private)
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    @else
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @endif
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-paper font-serif truncate">{{ $group->name }}</h3>
                                <span class="text-[11px] font-mono text-mist block">{{ $group->members_count }} {{ __('site.groups.members') }}</span>
                            </div>
                        </div>

                        <!-- Status Badge & Delete Button -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($group->is_private)
                                <span class="px-2 py-0.5 rounded-pill bg-[#C1392B]/15 border border-rose-500/30 text-[10px] font-mono text-rose-300">
                                    {{ __('site.groups.private') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-pill bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-mono text-emerald-400">
                                    {{ __('site.groups.public') }}
                                </span>
                            @endif

                            @if($group->can_delete)
                                <button wire:click="openDeleteModal({{ $group->id }})" title="{{ __('site.groups.delete_group') }}"
                                    class="w-7 h-7 rounded-btn bg-ink-800 hover:bg-[#C1392B]/20 text-mist hover:text-rose-300 border border-ink-border flex items-center justify-center transition-all text-xs"
                                    aria-label="{{ __('site.groups.delete_group') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-mist leading-relaxed line-clamp-2 font-sans break-words">
                        {{ $group->description ?? __('site.groups.no_desc') }}
                    </p>

                    @if ($group->book)
                        <div class="text-[11px] text-amber-400 font-mono font-medium truncate flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>{{ $group->book->title }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-ink-border flex items-center justify-between gap-2">
                    <span class="text-xs font-mono text-mist truncate">
                        @if($group->is_owner)
                            <span class="text-amber-400 font-bold">👑 {{ __('site.groups.owner') }}</span>
                        @elseif($group->is_member)
                            <span class="text-emerald-400 font-semibold">✓ {{ __('site.groups.member') }}</span>
                        @else
                            {{ $group->is_private ? __('site.groups.password_required') : __('site.groups.open_group') }}
                        @endif
                    </span>

                    @if ($group->is_owner)
                        <a href="{{ route('groups.show', $group->id) }}" class="ks-btn-primary text-xs py-1.5 px-3">
                            {{ __('site.groups.enter') }}
                        </a>
                    @elseif ($group->is_member)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('groups.show', $group->id) }}" class="ks-btn-ghost text-xs py-1.5 px-3">
                                {{ __('site.groups.enter') }}
                            </a>
                            <button wire:click="leave({{ $group->id }})" wire:confirm="{{ __('site.groups.leave_confirm') }}" class="text-[11px] font-mono text-mist hover:text-rose-300 transition-colors">
                                {{ __('site.groups.leave') }}
                            </button>
                        </div>
                    @else
                        @if($group->is_private)
                            <button wire:click="openJoinModal({{ $group->id }})"
                                class="px-3 py-1.5 rounded-btn bg-[#C1392B]/15 hover:bg-[#C1392B]/25 text-rose-300 border border-rose-500/30 text-xs font-mono font-medium transition-all flex items-center gap-1.5 shrink-0">
                                <svg class="w-3 h-3 text-rose-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>{{ __('site.groups.join_with_password') }}</span>
                            </button>
                        @else
                            <button wire:click="join({{ $group->id }})"
                                class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">
                                {{ __('site.groups.join') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 rounded-panel bg-ink-900 border border-ink-border p-6 space-y-3">
                @if($hasActiveFilters)
                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-paper font-serif">{{ __('site.groups.no_results_t') }}</h3>
                    <p class="text-xs text-mist font-mono max-w-sm mx-auto">{{ __('site.groups.no_results_s') }}</p>
                    <button type="button" wire:click="resetFilters" class="ks-btn-ghost text-xs py-1.5 px-3.5 font-mono">
                        {{ __('site.groups.show_all') }}
                    </button>
                @else
                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-paper font-serif">{{ __('site.groups.empty') }}</h3>
                    <p class="text-xs text-mist font-sans max-w-sm mx-auto">{{ __('site.groups.empty_sub') }}</p>
                    <button wire:click="openCreateModal" class="ks-btn-primary text-xs py-1.5 px-3.5">
                        {{ __('site.groups.create_first') }}
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    @if ($groups->hasPages())
        <div class="pt-4 font-mono text-xs">
            {{ $groups->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif

    <!-- Create Group Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-ink-950/80 backdrop-blur-md" wire:click="$set('showCreateModal', false)"></div>
            <div class="relative w-full max-w-md p-6 rounded-panel bg-ink-900 border border-ink-border shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-ink-border pb-3">
                    <h3 class="text-sm font-bold text-paper font-serif">{{ __('site.groups.create_title') }}</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-mist hover:text-paper text-sm font-mono">✕</button>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.groups.name_label') }}</label>
                        <input type="text" wire:model.defer="name" placeholder="{{ __('site.groups.name_placeholder') }}"
                            class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                        @error('name') <p class="text-rose-300 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.groups.desc_label') }}</label>
                        <textarea rows="2" wire:model.defer="description" placeholder="{{ __('site.groups.desc_placeholder') }}"
                            class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none resize-none font-sans"></textarea>
                        @error('description') <p class="text-rose-300 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">Guruh rasmi (Ixtiyoriy)</label>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-btn border border-ink-border bg-ink-950/80 overflow-hidden flex items-center justify-center shrink-0">
                                @if($coverImage)
                                    <img src="{{ $coverImage->temporaryUrl() }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-5 h-5 text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @endif
                            </div>
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-btn bg-ink-800 hover:bg-ink-700 text-paper border border-ink-border text-xs font-mono cursor-pointer transition-all">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Rasm tanlash</span>
                                <input type="file" wire:model="coverImage" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                            </label>
                            <div wire:loading wire:target="coverImage" class="text-[10px] font-mono text-amber-400">
                                Yuklanmoqda...
                            </div>
                        </div>
                        @error('coverImage') <p class="text-rose-300 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                    </div>

                    <!-- Private Group Checkbox -->
                    <div class="p-3.5 rounded-btn bg-ink-950/60 border border-ink-border space-y-2.5">
                        <label class="flex items-center gap-2.5 text-xs text-paper font-medium cursor-pointer">
                            <input type="checkbox" wire:model="isPrivate" class="rounded border-ink-border text-amber-500 focus:ring-0 w-4 h-4 bg-ink-900">
                            <span>{{ __('site.groups.private_label') }}</span>
                        </label>

                        @if ($isPrivate)
                            <div class="pt-2 border-t border-ink-border space-y-1.5">
                                <label class="block text-xs font-mono text-amber-400">
                                    {{ __('site.groups.password_label') }}
                                </label>
                                <input type="text" wire:model.defer="password" placeholder="{{ __('site.groups.password_placeholder') }}"
                                    class="w-full px-3 py-1.5 bg-ink-900 border border-amber-500/40 rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                                @error('password') <p class="text-rose-300 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                                <p class="text-[10px] text-mist font-mono">{{ __('site.groups.password_hint') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-ink-border">
                    <button wire:click="$set('showCreateModal', false)" class="px-3.5 py-1.5 text-xs font-mono text-mist hover:text-paper transition-colors">
                        {{ __('site.chat.cancel') }}
                    </button>
                    <button wire:click="create" wire:loading.attr="disabled"
                        class="ks-btn-primary text-xs py-1.5 px-3.5 disabled:opacity-50">
                        <span wire:loading.remove wire:target="create">{{ __('site.groups.create_btn') }}</span>
                        <span wire:loading wire:target="create">{{ __('site.groups.creating') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Join Private Group Modal -->
    @if ($showJoinModal && $this->targetGroup)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-ink-950/80 backdrop-blur-md" wire:click="$set('showJoinModal', false)"></div>
            <div class="relative w-full max-w-sm p-6 rounded-panel bg-ink-900 border border-ink-border shadow-2xl space-y-4 text-center">

                <div class="mx-auto w-10 h-10 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-paper font-serif">
                        «{{ $this->targetGroup->name }}»
                    </h3>
                    <p class="text-xs text-mist font-sans mt-0.5">
                        {{ __('site.groups.join_modal_desc') }}
                    </p>
                </div>

                <form wire:submit.prevent="submitJoinPassword" class="space-y-3.5 text-left">
                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.groups.password_field') }}</label>
                        <input type="password" wire:model.defer="joinPassword" autofocus placeholder="••••••••"
                            class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                        @error('joinPassword') 
                            <p class="text-rose-300 text-xs mt-1.5 font-mono flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p> 
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="$set('showJoinModal', false)"
                            class="flex-1 py-1.5 px-3 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors text-center">
                            {{ __('site.chat.cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 py-1.5 px-3 ks-btn-primary text-xs text-center justify-center">
                            <span wire:loading.remove wire:target="submitJoinPassword">{{ __('site.groups.enter') }}</span>
                            <span wire:loading wire:target="submitJoinPassword">{{ __('site.groups.checking') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Group Confirmation Modal -->
    @if ($showDeleteModal && $this->deleteTargetGroup)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-ink-950/80 backdrop-blur-md" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-md p-6 rounded-panel bg-ink-900 border border-ink-border shadow-2xl space-y-4 text-center">

                <div class="mx-auto w-10 h-10 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-bold text-paper font-serif">
                        {{ __('site.groups.delete_confirm_title', ['name' => $this->deleteTargetGroup->name]) }}
                    </h3>
                    <p class="text-xs text-mist font-sans leading-relaxed">
                        {{ __('site.groups.delete_confirm_desc') }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-1.5 px-3 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors">
                        {{ __('site.chat.cancel') }}
                    </button>
                    <button type="button" wire:click="confirmDeleteGroup" wire:loading.attr="disabled"
                        class="flex-1 py-1.5 px-3 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper text-xs font-mono font-bold transition-all flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="confirmDeleteGroup">{{ __('site.groups.delete_yes') }}</span>
                        <span wire:loading wire:target="confirmDeleteGroup">{{ __('site.groups.deleting') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
