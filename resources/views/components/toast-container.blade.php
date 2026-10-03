{{-- 
    Platform-Wide Toast Notification System (Kitobxon Master Design System)
    Supports:
      1. Livewire events: $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => '...'])
      2. Session flash: session('success'), session('error'), session('warning'), session('info')
      3. Global JS: window.toast({ type: 'success', message: '...' })
--}}

<div 
    x-data="kitobxonToastManager()" 
    x-init="init()"
    @toast.window="addToast($event.detail)" 
    @keydown.escape.window="removeLastToast()"
    class="fixed z-[9999] pointer-events-none flex flex-col gap-2.5 transition-all duration-300
           bottom-4 inset-x-4 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-[400px] max-w-full"
    role="region"
    aria-label="Bildirishnomalar"
    aria-live="polite"
>
    <template x-for="t in toasts" :key="t.id">
        <div 
            class="pointer-events-auto relative overflow-hidden transition-all duration-300"
            :class="[
                t.exiting ? 'opacity-0 translate-x-8 max-h-0 mb-0 py-0 overflow-hidden' : 'opacity-100 translate-x-0 max-h-40',
                t.reducedMotion ? 'transition-opacity duration-150' : 'transition-all duration-300 ease-out'
            ]"
            @mouseenter="pauseToast(t)"
            @mouseleave="resumeToast(t)"
            :role="t.type === 'error' ? 'alert' : 'status'"
            :aria-live="t.type === 'error' ? 'assertive' : 'polite'"
            aria-atomic="true"
        >
            <div 
                class="relative flex items-start gap-3.5 p-4 rounded-xl shadow-2xl backdrop-blur-xl border"
                :class="{
                    'bg-[#0D111A]/95 border-emerald-500/30 shadow-emerald-950/20': t.type === 'success',
                    'bg-[#0D111A]/95 border-rose-500/35 shadow-rose-950/25': t.type === 'error',
                    'bg-[#0D111A]/95 border-amber-500/30 shadow-amber-950/20': t.type === 'warning',
                    'bg-[#0D111A]/95 border-indigo-500/30 shadow-indigo-950/20': t.type === 'info'
                }"
                style="box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.7), 0 0 1px 1px rgba(255, 255, 255, 0.05);"
            >
                {{-- Left Icon --}}
                <div 
                    class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                    :class="{
                        'bg-emerald-500/15 text-emerald-400': t.type === 'success',
                        'bg-rose-500/15 text-rose-400': t.type === 'error',
                        'bg-amber-500/15 text-amber-400': t.type === 'warning',
                        'bg-indigo-500/15 text-indigo-400': t.type === 'info'
                    }"
                >
                    {{-- Success: CheckCircle --}}
                    <template x-if="t.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>

                    {{-- Error: ExclamationCircle --}}
                    <template x-if="t.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>

                    {{-- Warning: AlertTriangle --}}
                    <template x-if="t.type === 'warning'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>

                    {{-- Info: InformationCircle --}}
                    <template x-if="t.type === 'info'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0 pr-2">
                    <h5 
                        class="text-xs font-bold font-sans tracking-wide uppercase"
                        :class="{
                            'text-emerald-400': t.type === 'success',
                            'text-rose-400': t.type === 'error',
                            'text-amber-400': t.type === 'warning',
                            'text-indigo-400': t.type === 'info'
                        }"
                        x-text="t.title"
                    ></h5>
                    <p 
                        class="text-xs font-sans mt-0.5 leading-relaxed break-words"
                        :class="t.type === 'error' ? 'text-rose-200' : 'text-[#F0EDE6]'"
                        x-text="t.message"
                    ></p>
                </div>

                {{-- Close Button --}}
                <button 
                    type="button" 
                    @click="removeToast(t.id)"
                    class="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer flex-shrink-0 focus:outline-none focus:ring-1 focus:ring-amber-500"
                    aria-label="Xabarni yopish"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Animated Progress Bar --}}
                <div class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-white/[0.06] overflow-hidden rounded-b-xl">
                    <div 
                        class="h-full transition-all duration-75 ease-linear"
                        :style="'width: ' + t.progress + '%; background-color: ' + t.accentColor"
                    ></div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function kitobxonToastManager() {
        return {
            toasts: [],
            reducedMotion: false,
            counter: 0,

            init() {
                this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                // 50ms interval ticker for smooth progress and hover pause
                setInterval(() => {
                    const now = Date.now();
                    this.toasts.forEach(t => {
                        if (t.paused || t.exiting) return;
                        const elapsed = now - t.lastTick;
                        t.lastTick = now;
                        t.remaining -= elapsed;
                        t.progress = Math.max(0, (t.remaining / t.duration) * 100);

                        if (t.remaining <= 0) {
                            this.removeToast(t.id);
                        }
                    });
                }, 50);
            },

            addToast(detail) {
                if (!detail || !detail.message) return;

                const type = detail.type || 'info';
                const titles = {
                    success: 'Muvaffaqiyatli!',
                    error: 'Xatolik yuz berdi',
                    warning: 'Diqqat!',
                    info: "Ma'lumot"
                };

                const colors = {
                    success: '#10B981',
                    error: '#F43F5E',
                    warning: '#F59E0B',
                    info: '#6366F1'
                };

                const id = 'toast_' + Date.now() + '_' + (++this.counter);
                const duration = detail.duration || (type === 'error' ? 5500 : 4500);

                const newToast = {
                    id: id,
                    type: type,
                    title: detail.title || titles[type] || "Bildirishnoma",
                    message: detail.message,
                    duration: duration,
                    remaining: duration,
                    progress: 100,
                    paused: false,
                    exiting: false,
                    lastTick: Date.now(),
                    accentColor: colors[type] || '#6366F1',
                    reducedMotion: this.reducedMotion
                };

                // Limit max toasts visible at once to 5
                if (this.toasts.length >= 5) {
                    this.removeToast(this.toasts[0].id);
                }

                this.toasts.push(newToast);
            },

            pauseToast(toast) {
                toast.paused = true;
            },

            resumeToast(toast) {
                toast.paused = false;
                toast.lastTick = Date.now();
            },

            removeToast(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (!toast || toast.exiting) return;

                toast.exiting = true;
                // Wait for exit transition (250ms), then delete from array
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 280);
            },

            removeLastToast() {
                if (this.toasts.length > 0) {
                    this.removeToast(this.toasts[this.toasts.length - 1].id);
                }
            }
        };
    }

    // Global helper: window.toast({ type: 'success', message: '...' })
    window.toast = function(detail) {
        window.dispatchEvent(new CustomEvent('toast', { detail: detail }));
    };
</script>

{{-- Session Flash Bridge: Automatically dispatch toast if controller redirected with session message --}}
@if (session()->has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.toast({ type: 'success', message: @js(session('success')), title: 'Muvaffaqiyatli!' });
            }, 100);
        });
    </script>
@endif

@if (session()->has('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.toast({ type: 'error', message: @js(session('error')), title: 'Xatolik yuz berdi' });
            }, 100);
        });
    </script>
@endif

@if (session()->has('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.toast({ type: 'warning', message: @js(session('warning')), title: 'Diqqat!' });
            }, 100);
        });
    </script>
@endif

@if (session()->has('info'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.toast({ type: 'info', message: @js(session('info')), title: "Ma'lumot" });
            }, 100);
        });
    </script>
@endif
