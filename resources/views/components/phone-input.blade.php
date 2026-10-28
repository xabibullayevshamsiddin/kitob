@props([
    'name' => 'phone',
    'id' => null,
    'label' => 'Telefon raqam',
    'value' => null,
    'required' => true,
    'autofocus' => false,
    'help' => null,
])

@php
    $inputId = $id ?? $name;
    $rawVal = $value ?? old($name);
    if ($rawVal && str_starts_with($rawVal, '+998')) {
        $rawVal = substr($rawVal, 4);
    } elseif ($rawVal && str_starts_with($rawVal, '998')) {
        $rawVal = substr($rawVal, 3);
    }
    $hasError = $errors->has($name) || ($name === 'email' && $errors->has('phone')) || ($name === 'phone' && $errors->has('phone'));
    $errorMessage = $errors->first($name) ?: ($errors->first('phone') ?: $errors->first('email'));
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-mono uppercase tracking-wider {{ $hasError ? 'text-rose-400 font-semibold' : 'text-mist' }} mb-1.5">{{ $label }}</label>
    @endif
    <div onclick="document.getElementById('{{ $inputId }}').focus()"
         class="ks-phone-box relative flex items-center w-full rounded-input px-3.5 py-2.5 transition-colors duration-base cursor-text {{ $hasError ? 'bg-rose-500/10 border-2 border-rose-500 focus-within:border-rose-500 focus-within:ring-2 focus-within:ring-rose-500/30' : 'bg-ink-950 border border-ink-border focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500' }}">
        
        <!-- Telefon belgisi va doimiy +998 prefiksi -->
        <div class="ks-phone-prefix flex items-center gap-2 select-none pointer-events-none {{ $hasError ? 'text-rose-400' : 'text-mist' }} shrink-0 pr-2 border-r {{ $hasError ? 'border-rose-500/40' : 'border-ink-border/50' }}">
            <svg class="w-4 h-4 {{ $hasError ? 'text-rose-400' : 'text-mist' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <span class="font-mono text-sm font-semibold {{ $hasError ? 'text-rose-300' : 'text-paper' }} tracking-wider">+998</span>
        </div>

        <!-- 9 ta raqam yoki login kiritiladigan maydon -->
        <input id="{{ $inputId }}"
               type="text"
               name="{{ $name }}"
               value="{{ $rawVal }}"
               autocomplete="username"
               {{ $required ? 'required' : '' }}
               {{ $autofocus ? 'autofocus' : '' }}
               maxlength="64"
               class="ks-phone-field w-full bg-transparent border-0 p-0 pl-2.5 text-sm {{ $hasError ? 'text-rose-200' : 'text-paper' }} font-mono tracking-wider placeholder:text-mist/40 focus:ring-0 focus:outline-none"
               placeholder="90 123 45 67 yoki login">
    </div>

    @if($hasError)
        <p class="text-[11px] text-rose-400 font-mono mt-1.5 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($help)
        <p class="text-[10px] text-mist mt-1 font-mono">{{ $help }}</p>
    @endif
</div>

<script>
(function() {
    const input = document.getElementById('{{ $inputId }}');
    if (!input) return;
    const prefix = input.parentElement ? input.parentElement.querySelector('.ks-phone-prefix') : null;

    function formatUzPhone(val) {
        if (!val) return { formatted: '', digits: '' };
        if (/[a-zA-Z@]/.test(val)) {
            return { formatted: val, digits: '' };
        }

        let digits = (val || '').replace(/\D/g, '');
        if (digits.startsWith('998')) {
            digits = digits.substring(3);
        } else if (digits.length === 10 && digits.startsWith('8')) {
            digits = digits.substring(1);
        }
        digits = digits.substring(0, 9);

        let res = '';
        if (digits.length > 0) res += digits.substring(0, 2);
        if (digits.length > 2) res += ' ' + digits.substring(2, 5);
        if (digits.length > 5) res += ' ' + digits.substring(5, 7);
        if (digits.length > 7) res += ' ' + digits.substring(7, 9);
        return { formatted: res, digits: digits };
    }

    function applyFormat() {
        if (prefix) {
            prefix.style.display = /[a-zA-Z@]/.test(input.value) ? 'none' : 'flex';
        }
        const { formatted } = formatUzPhone(input.value);
        input.value = formatted;
    }

    input.addEventListener('input', applyFormat);
    input.addEventListener('paste', function() {
        setTimeout(applyFormat, 10);
    });

    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            if (/[a-zA-Z@]/.test(input.value)) {
                return;
            }
            let digits = input.value.replace(/\D/g, '');
            if (digits.startsWith('998')) digits = digits.substring(3);
            if (digits.length === 9) {
                input.value = '+998' + digits;
            }
        });
    }

    if (input.value) {
        applyFormat();
    }
})();
</script>
