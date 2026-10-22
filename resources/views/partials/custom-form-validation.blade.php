{{--
    Kitobxon Custom Form Validation & Tooltips
    Universal dark & light mode client-side validation that replaces the browser's
    generic HTML5 popups ("Заполните это поле") with native Kitobxon-styled tooltips.
--}}
<style>
    /* ── Input Invalid Highlight (Dark Mode Default) ── */
    .ks-input-invalid,
    input.ks-input-invalid,
    textarea.ks-input-invalid,
    select.ks-input-invalid,
    .ks-phone-box.ks-input-invalid {
        border-width: 2px !important;
        border-color: #F43F5E !important;
        background-color: rgba(244, 63, 94, 0.12) !important;
        color: #FDA4AF !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.22) !important;
    }

    /* ── Input Invalid Highlight (Light Mode) ── */
    html.light:not(.force-dark) .ks-input-invalid,
    html.light:not(.force-dark) input.ks-input-invalid,
    html.light:not(.force-dark) textarea.ks-input-invalid,
    html.light:not(.force-dark) select.ks-input-invalid,
    html.light:not(.force-dark) .ks-phone-box.ks-input-invalid {
        border-width: 2px !important;
        border-color: #E11D48 !important;
        background-color: rgba(244, 63, 94, 0.08) !important;
        color: #881337 !important;
        box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.16) !important;
    }

    .ks-input-invalid::placeholder {
        color: rgba(253, 164, 175, 0.6) !important;
    }
    html.light:not(.force-dark) .ks-input-invalid::placeholder {
        color: rgba(190, 18, 60, 0.5) !important;
    }

    /* ── Shake Animation on Failed Validation ── */
    @keyframes ksInputShake {
        0%, 100% { transform: translateX(0); }
        15%, 45%, 75% { transform: translateX(-5px); }
        30%, 60%, 90% { transform: translateX(5px); }
    }
    .ks-shake {
        animation: ksInputShake 360ms cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }

    /* ── Custom Validation Tooltip ── */
    .ks-custom-tooltip {
        position: fixed;
        z-index: 999999;
        pointer-events: none;
        max-width: min(360px, calc(100vw - 32px));
        transform-origin: center top;
        animation: ksTooltipPopIn 190ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .ks-custom-tooltip.ks-place-above {
        transform-origin: center bottom;
        animation-name: ksTooltipPopInAbove;
    }

    .ks-custom-tooltip.ks-tooltip-closing {
        animation: ksTooltipPopOut 130ms ease-in forwards !important;
    }

    @keyframes ksTooltipPopIn {
        0% {
            opacity: 0;
            transform: translateY(-6px) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes ksTooltipPopInAbove {
        0% {
            opacity: 0;
            transform: translateY(6px) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes ksTooltipPopOut {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        100% {
            opacity: 0;
            transform: scale(0.95);
        }
    }

    /* Tooltip Inner Box (Dark Mode Default) */
    .ks-tooltip-inner {
        position: relative;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 13px;
        border-radius: 11px;
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1.35;
        background: #0E131F;
        border: 1px solid rgba(244, 63, 94, 0.55);
        color: #FECDD3;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.8),
                    0 0 18px rgba(244, 63, 94, 0.22);
    }

    .ks-tooltip-icon {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        color: #FB7185;
    }

    .ks-tooltip-text {
        flex: 1;
    }

    /* Tooltip Beak Arrow (Dark Mode) */
    .ks-tooltip-beak {
        position: absolute;
        width: 11px;
        height: 11px;
        transform: rotate(45deg);
        background: #0E131F;
        z-index: 1;
    }

    .ks-place-below .ks-tooltip-beak {
        top: -6px;
        border-left: 1px solid rgba(244, 63, 94, 0.55);
        border-top: 1px solid rgba(244, 63, 94, 0.55);
    }

    .ks-place-above .ks-tooltip-beak {
        bottom: -6px;
        border-right: 1px solid rgba(244, 63, 94, 0.55);
        border-bottom: 1px solid rgba(244, 63, 94, 0.55);
    }

    /* ── Tooltip (Light Mode Support) ── */
    html.light:not(.force-dark) .ks-tooltip-inner {
        background: #FFF8F8;
        border: 1.5px solid #F43F5E;
        color: #881337;
        box-shadow: 0 12px 28px -4px rgba(225, 29, 72, 0.2),
                    0 4px 12px rgba(0, 0, 0, 0.08);
    }

    html.light:not(.force-dark) .ks-tooltip-icon {
        color: #E11D48;
    }

    html.light:not(.force-dark) .ks-tooltip-beak {
        background: #FFF8F8;
    }

    html.light:not(.force-dark) .ks-place-below .ks-tooltip-beak {
        border-left: 1.5px solid #F43F5E;
        border-top: 1.5px solid #F43F5E;
    }

    html.light:not(.force-dark) .ks-place-above .ks-tooltip-beak {
        border-right: 1.5px solid #F43F5E;
        border-bottom: 1.5px solid #F43F5E;
    }

    /* Light mode high-contrast text for server errors */
    html.light:not(.force-dark) .text-rose-400 {
        color: #E11D48 !important;
    }
    html.light:not(.force-dark) .text-rose-300 {
        color: #BE123C !important;
    }
    html.light:not(.force-dark) .text-rose-200 {
        color: #9F1239 !important;
    }
</style>

<script>
(function() {
    let activeTooltip = null;
    let activeTarget = null;
    let burstLock = false;
    let burstTimer = null;

    function escapeHtml(str) {
        if (!str) return '';
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }

    function getUzbekMessage(input) {
        const name = (input.name || '').toLowerCase();
        const id = (input.id || '').toLowerCase();
        const type = (input.type || '').toLowerCase();
        const val = (input.value || '').trim();

        // 1. Missing value
        if (!val) {
            if (name === 'phone' || id === 'phone' || (name === 'email' && (type === 'tel' || input.classList.contains('ks-phone-field')))) {
                return "Iltimos, telefon raqamingizni kiriting.";
            }
            if (name === 'password_confirmation' || id === 'password_confirmation') {
                return "Iltimos, parolni tasdiqlash maydonini to'ldiring.";
            }
            if (name === 'password' || id === 'password') {
                return "Iltimos, parolingizni kiriting.";
            }
            if (name === 'name' || id === 'name') {
                return "Iltimos, ism va familiyangizni kiriting.";
            }
            if (name === 'username' || id === 'username') {
                return "Iltimos, foydalanuvchi nomini (username) kiriting.";
            }
            if (name === 'code' || id === 'code') {
                return "Iltimos, 6 xonali tasdiqlash kodini kiriting.";
            }
            if (type === 'email' || name === 'email') {
                return "Iltimos, email manzilingizni kiriting.";
            }
            return "Iltimos, ushbu maydonni to'ldiring.";
        }

        // 2. Phone input with incomplete digits
        if (type === 'tel' || name === 'phone' || input.classList.contains('ks-phone-field')) {
            const digits = val.replace(/\D/g, '');
            const pureDigits = digits.startsWith('998') ? digits.substring(3) : digits;
            if (pureDigits.length < 9) {
                return "Iltimos, 9 ta raqamdan iborat to'liq telefon raqamini kiriting (masalan: 90 123 45 67).";
            }
        }

        // 3. Email format
        if (type === 'email' || (name === 'email' && type !== 'tel' && !input.classList.contains('ks-phone-field'))) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                return "Iltimos, to'g'ri email manzilini kiriting (masalan: ali@misol.uz).";
            }
        }

        // 4. Password confirmation mismatch
        if (name === 'password_confirmation') {
            const form = input.closest('form');
            const pass = form ? form.querySelector('input[name="password"]') : null;
            if (pass && pass.value && pass.value !== val) {
                return "Kiritilgan parollar bir-biriga mos kelmadi.";
            }
        }

        // 5. Verification code length
        if (name === 'code' && val.length < 6) {
            return "Iltimos, 6 xonali tasdiqlash kodini to'liq kiriting.";
        }

        // 6. Native validation message fallback
        if (input.validationMessage) {
            if (input.validity.valueMissing) return "Iltimos, ushbu maydonni to'ldiring.";
            if (input.validity.typeMismatch) return "Iltimos, to'g'ri formatda kiriting.";
            if (input.validity.tooShort) return `Iltimos, kamida ${input.minLength} ta belgi kiriting.`;
        }

        return "Iltimos, maydonni to'g'ri to'ldiring.";
    }

    function hideTooltip() {
        if (activeTooltip) {
            const el = activeTooltip;
            activeTooltip = null;
            activeTarget = null;
            el.classList.add('ks-tooltip-closing');
            setTimeout(function() {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 130);
        }
    }

    function positionTooltip(anchorEl, tooltip) {
        if (!anchorEl || !tooltip) return;

        const rect = anchorEl.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        const viewportW = window.innerWidth;
        const viewportH = window.innerHeight;

        // Position below by default, or above if close to bottom
        const spaceBelow = viewportH - rect.bottom;
        const placeAbove = spaceBelow < 60 && rect.top > 60;

        let top = 0;
        let left = 0;
        const beak = tooltip.querySelector('.ks-tooltip-beak');

        if (placeAbove) {
            top = rect.top - tooltipRect.height - 8;
            tooltip.classList.add('ks-place-above');
            tooltip.classList.remove('ks-place-below');
        } else {
            top = rect.bottom + 8;
            tooltip.classList.add('ks-place-below');
            tooltip.classList.remove('ks-place-above');
        }

        // Keep inside horizontal viewport boundaries
        left = Math.max(14, Math.min(viewportW - tooltipRect.width - 14, rect.left + 8));

        tooltip.style.top = Math.round(top) + 'px';
        tooltip.style.left = Math.round(left) + 'px';

        if (beak) {
            const anchorCenter = rect.left + Math.min(36, rect.width / 2);
            const beakLeft = Math.max(14, Math.min(tooltipRect.width - 20, anchorCenter - left));
            beak.style.left = Math.round(beakLeft) + 'px';
        }
    }

    function showTooltip(anchorEl, inputEl, message) {
        hideTooltip();

        const tooltip = document.createElement('div');
        tooltip.id = 'ks-custom-validation-tooltip';
        tooltip.className = 'ks-custom-tooltip';
        tooltip.setAttribute('role', 'alert');

        tooltip.innerHTML = [
            '<div class="ks-tooltip-beak"></div>',
            '<div class="ks-tooltip-inner">',
                '<svg class="ks-tooltip-icon" viewBox="0 0 20 20" fill="currentColor">',
                    '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />',
                '</svg>',
                '<span class="ks-tooltip-text">' + escapeHtml(message) + '</span>',
            '</div>'
        ].join('');

        document.body.appendChild(tooltip);
        activeTooltip = tooltip;
        activeTarget = inputEl;

        positionTooltip(anchorEl, tooltip);
    }

    function handleInvalid(input, customMsg) {
        if (!input) return;

        const phoneBox = input.closest('.ks-phone-box');
        const visualTarget = phoneBox || input;

        // Apply error styling
        visualTarget.classList.add('ks-input-invalid');
        if (phoneBox) {
            const prefix = phoneBox.querySelector('.ks-phone-prefix');
            if (prefix) prefix.classList.add('text-rose-400', 'border-rose-500/40');
        }

        // Shake animation
        visualTarget.classList.remove('ks-shake');
        void visualTarget.offsetWidth;
        visualTarget.classList.add('ks-shake');
        setTimeout(function() {
            visualTarget.classList.remove('ks-shake');
        }, 400);

        // When multiple invalid events fire during form submit, focus & tooltip only the FIRST one
        if (burstLock) return;
        burstLock = true;
        clearTimeout(burstTimer);
        burstTimer = setTimeout(function() { burstLock = false; }, 350);

        try {
            input.focus({ preventScroll: true });
        } catch(e) {}

        visualTarget.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        const message = customMsg || getUzbekMessage(input);
        showTooltip(visualTarget, input, message);

        // Clear error as soon as user types
        function onType() {
            visualTarget.classList.remove('ks-input-invalid');
            if (phoneBox) {
                phoneBox.classList.remove('ks-input-invalid');
                const prefix = phoneBox.querySelector('.ks-phone-prefix');
                if (prefix) prefix.classList.remove('text-rose-400', 'border-rose-500/40');
            }
            hideTooltip();
            input.removeEventListener('input', onType);
        }
        input.addEventListener('input', onType);
    }

    // ── 1. Intercept native 'invalid' event to suppress browser default popup ──
    document.addEventListener('invalid', function(e) {
        e.preventDefault(); // SUPPRESSES BROWSER'S OS-LEVEL WHITE TOOLTIP!
        handleInvalid(e.target);
    }, true);

    // ── 2. Intercept 'submit' for custom checks (phone 9 digits, password confirmation) ──
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // A. Phone Input validation
        const phoneInput = form.querySelector('input.ks-phone-field, input[type="tel"]');
        if (phoneInput && phoneInput.hasAttribute('required')) {
            const digits = (phoneInput.value || '').replace(/\D/g, '');
            const pureDigits = digits.startsWith('998') ? digits.substring(3) : digits;
            if (pureDigits.length === 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                handleInvalid(phoneInput, "Iltimos, telefon raqamingizni kiriting.");
                return false;
            } else if (pureDigits.length < 9) {
                e.preventDefault();
                e.stopImmediatePropagation();
                handleInvalid(phoneInput, "Iltimos, 9 ta raqamdan iborat to'liq telefon raqamini kiriting.");
                return false;
            }
        }

        // B. Password Confirmation mismatch check
        const pass = form.querySelector('input[name="password"]');
        const passConf = form.querySelector('input[name="password_confirmation"]');
        if (pass && passConf) {
            if (passConf.hasAttribute('required') && !passConf.value.trim()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                handleInvalid(passConf, "Iltimos, parolni tasdiqlash maydonini to'ldiring.");
                return false;
            } else if (pass.value && passConf.value && pass.value !== passConf.value) {
                e.preventDefault();
                e.stopImmediatePropagation();
                handleInvalid(passConf, "Kiritilgan parollar bir-biriga mos kelmadi.");
                return false;
            }
        }

        // C. Check required inputs if form has novalidate attribute
        if (form.hasAttribute('novalidate')) {
            const requiredEls = form.querySelectorAll('input[required], select[required], textarea[required]');
            for (let i = 0; i < requiredEls.length; i++) {
                const el = requiredEls[i];
                if (!el.disabled && !el.value.trim()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    handleInvalid(el);
                    return false;
                }
            }
        }
    }, true);

    // ── 3. Auto-dismiss tooltip on scroll, resize, escape or outside click ──
    window.addEventListener('resize', hideTooltip, { passive: true });
    window.addEventListener('scroll', hideTooltip, { passive: true });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') hideTooltip();
    });
    document.addEventListener('pointerdown', function(e) {
        if (activeTooltip && !activeTooltip.contains(e.target)) {
            hideTooltip();
        }
    }, { passive: true });

})();
</script>