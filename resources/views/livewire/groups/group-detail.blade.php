<div class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">←</a>
            <div class="w-12 h-12 rounded-2xl {{ $group->is_private ? 'bg-slate-500/10 text-slate-500' : 'bg-amber-500/10 text-amber-500' }} flex items-center justify-center text-2xl">
                {{ $group->is_private ? '🔒' : '🚀' }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-manrope">{{ $group->name }}</h1>
                <p class="text-xs text-slate-400">{{ $members->count() }} {{ __('site.groups.members') }} {{ $group->book ? '• 📖 ' . $group->book->title : '' }}</p>
            </div>
        </div>

        @if($canDelete)
            <div>
                <button wire:click="openDeleteModal"
                    class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white border border-rose-500/25 text-xs font-bold rounded-xl transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>{{ __('site.groups.delete_group') }}</span>
                </button>
            </div>
        @endif
    </div>

    @if ($group->description)
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-3xl">{{ $group->description }}</p>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Group Chat -->
        <div class="lg:col-span-2 h-[36rem] flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden relative"
             wire:poll.visible.15s
             x-data="{
                 deleteModalOpen: false,
                 targetMessageId: null,
                 count: 0,
                 confirmDelete(id) {
                     this.targetMessageId = id;
                     this.deleteModalOpen = true;
                 },
                 executeDelete() {
                     if (this.targetMessageId) {
                         $wire.deleteMessage(this.targetMessageId);
                         this.deleteModalOpen = false;
                         this.targetMessageId = null;
                     }
                 }
             }"
             x-init="(() => {
                 const c = document.getElementById('group-chat-container');
                 if (!c) return;
                 let stick = true;
                 c.addEventListener('scroll', () => { stick = c.scrollHeight - c.scrollTop - c.clientHeight < 120; });
                 if (window.Livewire) {
                     Livewire.hook('message.processed', () => {
                         if (stick && document.body.contains(c)) {
                             requestAnimationFrame(() => { c.scrollTop = c.scrollHeight; });
                         }
                     });
                 }
                 window.addEventListener('chat-scroll-bottom', () => {
                     if (document.body.contains(c)) {
                         stick = true;
                         requestAnimationFrame(() => { c.scrollTop = c.scrollHeight; });
                     }
                 });
                 c.scrollTop = c.scrollHeight;
             })()">

            <!-- Chat Header -->
            <div class="p-4 sm:px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                        💬
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.groups.chat_title') }}</h2>
                        <span class="text-[11px] text-emerald-500 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('site.chat.live') }}
                        </span>
                    </div>
                </div>
                <span class="hidden sm:block text-xs text-slate-400">{{ __('site.chat.msg_count', ['count' => $messages->count()]) }}</span>
            </div>

            <!-- Messages Container -->
            <div id="group-chat-container" class="flex-1 p-4 sm:p-6 overflow-y-auto overflow-x-hidden space-y-4" x-init="setTimeout(() => { const c = document.getElementById('group-chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
                @forelse ($messages as $msg)
                    @php 
                        $isMe = auth()->check() && auth()->id() === $msg->user_id;
                        $isAdmin = auth()->check() && (auth()->user()->role === 'admin' || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin')));
                        $isGroupOwner = auth()->check() && ($group->created_by === auth()->id());
                        $canDeleteMsg = $isMe || $isAdmin || $isGroupOwner;
                    @endphp
                    <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }} animate-slide-up" wire:key="gm-{{ $msg->id }}">
                        <img src="{{ $msg->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff' }}" class="w-8 h-8 rounded-xl object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700 mt-0.5" alt="{{ $msg->user?->name ?? __('site.chat.user') }}">
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                            <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $isMe ? __('site.chat.you') : ($msg->user?->name ?? __('site.chat.user')) }}</span>
                                @if(($msg->user?->role ?? '') === 'admin' || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('admin')))
                                    <span class="px-1.5 py-0.5 bg-amber-500/10 text-amber-500 font-bold text-[9px] rounded uppercase">{{ __('site.leaderboard.role_admin') }}</span>
                                @elseif($msg->user_id === $group->created_by)
                                    <span class="px-1.5 py-0.5 bg-indigo-500/10 text-indigo-500 font-bold text-[9px] rounded uppercase">👑 Asoschi</span>
                                @endif
                                <span class="text-[10px] text-slate-400">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>

                                @if($canDeleteMsg)
                                    <button type="button"
                                        @click="confirmDelete({{ $msg->id }})"
                                        title="{{ $isMe ? __('site.chat.delete_own') : __('site.chat.delete_admin') }}"
                                        class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 rounded">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none text-left shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-none' }}">{{ $msg->message }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 text-sm">
                        {{ __('site.groups.chat_empty') }}
                    </div>
                @endforelse
            </div>

            <!-- Message Input Bar -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <form wire:submit.prevent="sendMessage" @submit="count = 0" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <input type="text" 
                            wire:model.defer="message" 
                            x-on:input="count = $event.target.value.length"
                            placeholder="{{ __('site.chat.placeholder') }}" 
                            maxlength="500"
                            class="w-full pl-4 pr-16 py-3 bg-slate-100 dark:bg-slate-800 border-none rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none"
                             :class="{
                                 'text-slate-400': count < 400,
                                 'text-amber-500 font-semibold': count >= 400 && count < 480,
                                 'text-rose-500 font-bold': count >= 480
                             }">
                            <span x-text="count">0</span>/500
                        </div>
                    </div>
                    <button type="submit" 
                        x-on:click="count = 0"
                        class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md shadow-indigo-600/25 transition-all flex items-center gap-1.5 shrink-0">
                        <span>{{ __('site.chat.send') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
                @error('message') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Taste-Skill Delete Message Confirmation Modal -->
            <div x-show="deleteModalOpen" 
                 x-cloak
                 @keydown.escape.window="deleteModalOpen = false"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <!-- Modal Dialog Card -->
                <div @click.away="deleteModalOpen = false"
                     class="bg-white dark:bg-[#0e1422] border border-slate-200/80 dark:border-white/[0.08] rounded-3xl shadow-2xl p-6 sm:p-7 max-w-sm w-full relative overflow-hidden text-center"
                     x-transition:enter="transition ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">

                    <!-- Soft ambient red glow -->
                    <div class="absolute -top-10 -left-10 w-28 h-28 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <!-- Danger Badge Icon -->
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/15 border border-rose-500/20 text-rose-500 dark:text-rose-400 flex items-center justify-center mb-4 shadow-lg shadow-rose-500/10">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ __('site.chat.delete_title') }}</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed mt-2 mb-6">
                        {{ __('site.chat.delete_desc') }}
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button"
                            @click="deleteModalOpen = false"
                            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
                            {{ __('site.chat.cancel') }}
                        </button>
                        <button type="button"
                            @click="executeDelete()"
                            class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center gap-1.5">
                            <span>{{ __('site.chat.delete') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Sidebar -->
        <div class="space-y-3">
            <div class="p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">{{ __('site.groups.members_list') }} ({{ $members->count() }})</h3>
                <div class="space-y-3">
                    @foreach ($members as $member)
                        <a href="{{ route('profile.show', $member->user->username) }}" class="flex items-center justify-between gap-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-xl p-2 transition-colors" wire:key="mem-{{ $member->id }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $member->user->avatar_url }}" class="w-8 h-8 rounded-lg object-cover" alt="{{ $member->user->name }}">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate block">{{ $member->user->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $member->role === 'admin' ? '👑 ' . __('site.leaderboard.role_admin') : ($member->role === 'moderator' ? '⭐ ' . __('site.groups.moderator') : __('site.groups.member')) }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 shrink-0">{{ number_format($member->user->total_points) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Group Confirmation Modal (Taste-Skill) -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-md p-6 sm:p-7 rounded-3xl bg-white dark:bg-[#0e1422] border border-slate-200 dark:border-white/10 shadow-2xl space-y-5 text-center">

                <!-- Danger Icon Badge -->
                <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/10 dark:bg-rose-500/15 border border-rose-500/20 text-rose-500 dark:text-rose-400 flex items-center justify-center text-2xl shadow-lg shadow-rose-500/10">
                    🗑️
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ __('site.groups.delete_confirm_title', ['name' => $group->name]) }}
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
