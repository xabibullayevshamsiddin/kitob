<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden"
     x-data="{ sendMessage() { this.$wire.sendMessage(); } }"
     x-on:chat-scroll-bottom.window="setTimeout(() => { const c = document.getElementById('chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 100)">

    <!-- Chat Header -->
    <div class="p-4 sm:px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                💬
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Umumiy Jamiyat Chati</h2>
                <span class="text-[11px] text-emerald-500 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Jonli muloqot
                </span>
            </div>
        </div>
        <span class="hidden sm:block text-xs text-slate-400">{{ $messages->count() }} ta xabar</span>
    </div>

    <!-- Messages Container -->
    <div id="chat-container" class="flex-1 p-4 sm:p-6 overflow-y-auto space-y-4" x-init="setTimeout(() => { const c = document.getElementById('chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
        @forelse ($messages as $msg)
            <div class="flex items-start gap-3 group" wire:key="msg-{{ $msg->id }}">
                <img src="{{ $msg->user->avatar_url }}" class="w-9 h-9 rounded-xl object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700 mt-0.5" alt="{{ $msg->user->name }}">
                <div class="flex-1 max-w-xl">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $msg->user->name }}</span>
                        @if($msg->user->hasRole('admin') || $msg->user->role === 'admin')
                            <span class="px-1.5 py-0.5 bg-amber-500/10 text-amber-500 font-bold text-[9px] rounded uppercase">Admin</span>
                        @endif
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed inline-block shadow-sm break-words">
                        {{ $msg->message }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                Hali xabarlar yo'q — birinchi bo'lib yozing! 💬
            </div>
        @endforelse
    </div>

    <!-- Message Input Bar -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <form wire:submit="sendMessage" class="flex items-center gap-3">
            <input type="text" wire:model="message" placeholder="Fikringizni yoki savolingizni yozing..." maxlength="500"
                class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-800 border-none rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md shadow-indigo-600/25 transition-all flex items-center gap-1.5 shrink-0">
                <span>Yuborish</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
        @error('message') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
    </div>
</div>
