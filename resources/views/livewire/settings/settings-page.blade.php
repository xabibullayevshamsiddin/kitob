<div class="max-w-3xl mx-auto space-y-6 pb-16">

    <div>
        <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">{{ __('site.settings.title') }}</h1>
        <p class="text-xs font-mono text-mist mt-1">{{ __('site.settings.subtitle') }}</p>
    </div>

    <!-- Profile Settings Card -->
    <div class="p-6 sm:p-7 rounded-panel bg-ink-900 border border-ink-border shadow-soft space-y-5">
        <h3 class="text-sm font-bold text-paper font-serif border-b border-ink-border pb-3">{{ __('site.settings.profile_info') }}</h3>

        <!-- Avatar Preview -->
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-panel overflow-hidden border-2 border-amber-400/40 shrink-0">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.settings.avatar') }}</label>
                <input type="file" wire:model="avatar" accept="image/*"
                    class="text-xs font-mono text-mist file:mr-3 file:py-1.5 file:px-3 file:rounded-btn file:border-0 file:text-xs file:font-mono file:font-semibold file:bg-amber-400 file:text-ink-950 hover:file:bg-amber-300 file:cursor-pointer">
                <span wire:loading wire:target="avatar" class="text-[11px] font-mono text-amber-400 block mt-1">{{ __('site.settings.uploading') }}</span>
                @error('avatar') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
                @if (auth()->user()->avatar)
                    <div class="mt-2">
                        <button type="button" wire:click="removeAvatar" wire:target="removeAvatar" wire:loading.attr="disabled"
                            wire:confirm="{{ __('site.settings.remove_avatar_confirm') }}"
                            class="inline-flex items-center gap-1.5 text-[11px] font-mono text-rose-300/90 hover:text-rose-200 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            {{ __('site.settings.remove_avatar') }}
                        </button>
                        <span wire:loading wire:target="removeAvatar" class="text-[11px] font-mono text-mist block mt-1">{{ __('site.settings.removing') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.full_name') }}</label>
                <input type="text" wire:model="name"
                    class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none">
                @error('name') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.username') }}</label>
                <input type="text" wire:model="username"
                    class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none">
                @error('username') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.email') }}</label>
                <input type="email" value="{{ auth()->user()->email }}" disabled
                    class="w-full px-3.5 py-2 bg-ink-950/40 border border-ink-border rounded-btn text-xs sm:text-sm text-mist/60 cursor-not-allowed font-mono">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.bio') }}</label>
                <textarea rows="3" wire:model="bio" placeholder="{{ __('site.settings.bio_placeholder') }}"
                    class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none resize-none font-sans"></textarea>
                @error('bio') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <span wire:loading wire:target="save" class="text-xs font-mono text-mist">{{ __('site.settings.saving') }}</span>
            <button wire:click="save" wire:loading.attr="disabled"
                class="ks-btn-primary text-xs py-2 px-4">
                {{ __('site.settings.save') }}
            </button>
        </div>
    </div>
</div>
