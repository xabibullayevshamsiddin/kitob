<div class="max-w-3xl mx-auto space-y-8 pb-16">

    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white font-manrope">Hisob sozlamalari</h1>
        <p class="text-xs text-slate-400 mt-1">Shaxsiy ma'lumotlaringiz va o'qish afzalliklaringizni boshqaring</p>
    </div>

    <!-- Profile Settings Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-6">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">Profil ma'lumotlari</h3>

        <!-- Avatar Preview -->
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl overflow-hidden ring-2 ring-indigo-500/20 shrink-0">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Profil surati</label>
                <input type="file" wire:model="avatar" accept="image/*"
                    class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 file:cursor-pointer">
                <span wire:loading wire:target="avatar" class="text-[11px] text-amber-500 block mt-1">Yuklanmoqda...</span>
                @error('avatar') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">To'liq ism</label>
                <input type="text" wire:model="name"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
                <input type="text" wire:model="username"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
                @error('username') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input type="email" value="{{ auth()->user()->email }}" disabled
                    class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bio</label>
                <textarea rows="3" wire:model="bio" placeholder="O'zingiz haqingizda qisqacha..."
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white resize-none"></textarea>
                @error('bio') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <span wire:loading wire:target="save" class="text-xs text-slate-400">Saqlanmoqda...</span>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                O'zgarishlarni saqlash
            </button>
        </div>
    </div>
</div>
