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
                <p class="text-xs text-slate-400">{{ $members->count() }} ta a'zo {{ $group->book ? '• 📖 ' . $group->book->title : '' }}</p>
            </div>
        </div>
    </div>

    @if ($group->description)
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-3xl">{{ $group->description }}</p>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Group Chat -->
        <div class="lg:col-span-2 h-[32rem] flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden"
             x-on:chat-scroll-bottom.window="setTimeout(() => { const c = document.getElementById('group-chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 100)">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">💬 Guruh suhbati</h3>
            </div>

            <div id="group-chat-container" class="flex-1 p-4 overflow-y-auto space-y-4" x-init="setTimeout(() => { const c = document.getElementById('group-chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
                @forelse ($messages as $msg)
                    <div class="flex items-start gap-3" wire:key="gm-{{ $msg->id }}">
                        <img src="{{ $msg->user->avatar_url }}" class="w-8 h-8 rounded-lg object-cover shrink-0" alt="{{ $msg->user->name }}">
                        <div class="flex-1 max-w-xl">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $msg->user->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed inline-block break-words">
                                {{ $msg->message }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 text-sm">Guruhda hali xabarlar yo'q — salom bering! 👋</div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                <form wire:submit="sendMessage" class="flex items-center gap-2">
                    <input type="text" wire:model="message" placeholder="Xabar yozing..." maxlength="500"
                        class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all shrink-0">
                        Yuborish
                    </button>
                </form>
                @error('message') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Members Sidebar -->
        <div class="space-y-3">
            <div class="p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">A'zolar ({{ $members->count() }})</h3>
                <div class="space-y-3">
                    @forelse ($members as $member)
                        <a href="{{ route('profile.show', $member->user->username) }}" class="flex items-center justify-between gap-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-xl p-2 transition-colors" wire:key="mem-{{ $member->id }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $member->user->avatar_url }}" class="w-8 h-8 rounded-lg object-cover" alt="{{ $member->user->name }}">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate block">{{ $member->user->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $member->role === 'admin' ? '👑 Admin' : ($member->role === 'moderator' ? '⭐ Moderator' : 'A\'zo') }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 shrink-0">{{ number_format($member->user->total_points) }}</span>
                        </a>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
