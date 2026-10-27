@props([
    'name' => 'password',
    'id' => null,
    'label' => null,
    'placeholder' => '••••••••',
    'required' => true,
    'autocomplete' => 'current-password',
    'autofocus' => false,
    'showForgot' => false,
    'forgotRoute' => 'password.request',
    'help' => null,
    'model' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $errorMessage = $errors->first($name);
@endphp

<div x-data="{ show: false }">
    @if($label || $showForgot)
        <div class="flex items-center justify-between mb-1.5">
            @if($label)
                <label for="{{ $inputId }}" class="block text-xs font-mono uppercase tracking-wider {{ $hasError ? 'text-rose-400 font-semibold' : 'text-mist' }}">
                    {{ $label }}
                </label>
            @endif
            @if ($showForgot && Route::has($forgotRoute))
                <a href="{{ route($forgotRoute) }}" class="text-xs text-amber-500 hover:text-amber-400 transition-colors">
                    {{ __('site.auth.forgot_password') }}
                </a>
            @endif
        </div>
    @endif

    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none {{ $hasError ? 'text-rose-400' : 'text-mist' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <input id="{{ $inputId }}"
               :type="show ? 'text' : 'password'"
               type="password"
               name="{{ $name }}"
               {{ $model ? ($attributes->wire('model')) : '' }}
               {{ $required ? 'required' : '' }}
               {{ $autofocus ? 'autofocus' : '' }}
               autocomplete="{{ $autocomplete }}"
               placeholder="{{ $placeholder }}"
               class="ks-input pl-9 pr-10 {{ $hasError ? 'ks-input-invalid' : '' }} {{ $attributes->get('class') }}"
               {{ $attributes->except(['class', 'wire:model', 'wire:model.defer']) }}>

        <button type="button"
                @click="show = !show"
                onclick="window.__togglePassword && window.__togglePassword(this)"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-mist hover:text-paper focus:outline-none transition-colors select-none"
                tabindex="-1"
                aria-label="Parolni ko'rsatish yoki yashirish"
                title="Parolni ko'rsatish/yashirish">
            <!-- Ko'z ochiq (parol ko'rinmayotganda ko'rsatish uchun) -->
            <svg :class="show ? 'hidden' : 'block'" class="ks-eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <!-- Ko'z yopiq/ustidan chizilgan (parol ko'rinayotganda yashirish uchun) -->
            <svg :class="show ? 'block' : 'hidden'" class="ks-eye-closed w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            </svg>
        </button>
    </div>

    @if($hasError)
        <p class="text-[11px] text-rose-400 font-mono mt-1.5 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($help)
        <p class="text-[10px] text-mist mt-1 font-mono">{{ $help }}</p>
    @endif
</div>
