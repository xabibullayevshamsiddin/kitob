<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden relative"
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
         const c = document.getElementById('chat-container');
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
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.chat.title') }}</h2>
                <span class="text-[11px] text-emerald-500 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('site.chat.live') }}
                </span>
            </div>
        </div>
        <span class="hidden sm:block text-xs text-slate-400">{{ __('site.chat.msg_count', ['count' => $messages->count()]) }}</span>
    </div>

    <!-- Messages Container -->
    <div id="chat-container" class="flex-1 p-4 sm:p-6 overflow-y-auto overflow-x-hidden space-y-4" x-init="setTimeout(() => { const c = document.getElementById('chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
        @forelse ($messages as $msg)
            @php 
                $isMe = auth()->check() && auth()->id() === $msg->user_id;
                $isAdmin = auth()->check() && (auth()->user()->role === 'admin' || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin')));
                $canDelete = $isMe || $isAdmin;
            @endphp
            <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }} animate-slide-up" wire:key="msg-{{ $msg->id }}">
                <img src="{{ $msg->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff' }}" class="w-8 h-8 rounded-xl object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700 mt-0.5" alt="{{ $msg->user?->name ?? __('site.chat.user') }}">
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                    <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }}">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $isMe ? __('site.chat.you') : ($msg->user?->name ?? __('site.chat.user')) }}</span>
                        @if(($msg->user?->role ?? '') === 'admin' || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('admin')))
                            <span class="px-1.5 py-0.5 bg-amber-500/10 text-amber-500 font-bold text-[9px] rounded uppercase">{{ __('site.leaderboard.role_admin') }}</span>
                        @endif
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>

                        @if($canDelete)
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
                    @if($msg->audio_path)
                        <div class="p-3 rounded-2xl shadow-sm {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-none border border-slate-200/60 dark:border-slate-700/60' }}"
                             x-data="{
                                 playing: false,
                                 progress: 0,
                                 duration: {{ $msg->audio_duration ?: 0 }},
                                 currentTime: 0,
                                 audio: null,
                                 init() {
                                     this.audio = new Audio('{{ $msg->audio_url }}');
                                     this.audio.addEventListener('loadedmetadata', () => {
                                         if (!this.duration) this.duration = Math.round(this.audio.duration);
                                     });
                                     this.audio.addEventListener('timeupdate', () => {
                                         this.currentTime = Math.round(this.audio.currentTime);
                                         if (this.duration) {
                                             this.progress = (this.audio.currentTime / this.duration) * 100;
                                         }
                                     });
                                     this.audio.addEventListener('ended', () => {
                                         this.playing = false;
                                         this.progress = 0;
                                         this.currentTime = 0;
                                     });
                                 },
                                 toggle() {
                                     if (!this.audio) return;
                                     if (this.playing) {
                                         this.audio.pause();
                                         this.playing = false;
                                     } else {
                                         document.querySelectorAll('audio').forEach(a => a.pause());
                                         this.audio.play();
                                         this.playing = true;
                                     }
                                 },
                                 formatTime(sec) {
                                     const m = Math.floor(sec / 60);
                                     const s = Math.floor(sec % 60);
                                     return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                                 }
                             }">
                            <div class="flex items-center gap-3 min-w-[210px] sm:min-w-[260px]">
                                <button type="button" @click="toggle()"
                                        class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform active:scale-90 {{ $isMe ? 'bg-white text-indigo-600 hover:bg-slate-100' : 'bg-indigo-600 text-white hover:bg-indigo-500' }} shadow-md">
                                    <template x-if="!playing">
                                        <svg class="w-4 h-4 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </template>
                                    <template x-if="playing">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </template>
                                </button>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-0.5 h-6 cursor-pointer"
                                         @click="if(audio && duration) { const r = $el.getBoundingClientRect(); const p = Math.max(0, Math.min(1, ($event.clientX - r.left) / r.width)); audio.currentTime = p * duration; }">
                                        <template x-for="(h, i) in [35, 65, 45, 90, 75, 40, 85, 100, 70, 50, 85, 95, 60, 45, 75, 55, 80, 40]" :key="i">
                                            <span class="flex-1 rounded-full transition-all"
                                                  :style="'height: ' + h + '%;'"
                                                  :class="(i / 18 * 100) <= progress ? '{{ $isMe ? 'bg-white' : 'bg-indigo-600' }}' : '{{ $isMe ? 'bg-white/40' : 'bg-slate-300 dark:bg-slate-600' }}'"></span>
                                        </template>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-mono {{ $isMe ? 'text-indigo-100' : 'text-slate-400' }}">
                                        <span x-text="playing ? formatTime(currentTime) : 'Ovozli xabar'">Ovozli xabar</span>
                                        <span x-text="formatTime(duration)">{{ sprintf('%02d:%02d', floor(($msg->audio_duration ?? 0)/60), ($msg->audio_duration ?? 0)%60) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none text-left shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-none' }}">{{ $msg->message }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                {{ __('site.chat.empty') }}
            </div>
        @endforelse
    </div>

    <!-- Message Input Bar with Voice Note Recording -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
         x-data="{
             isRecording: false,
             isUploading: false,
             recordDuration: 0,
             mediaRecorder: null,
             mediaStream: null,
             audioChunks: [],
             timerInterval: null,
             discard: false,
             startRecord() {
                 if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                     alert('Brauzeringiz audio yozishni qo\'llab-quvvatlamaydi.');
                     return;
                 }
                 navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                     this.mediaStream = stream;
                     this.audioChunks = [];
                     this.discard = false;
                     let options = {};
                     if (MediaRecorder.isTypeSupported('audio/webm')) {
                         options = { mimeType: 'audio/webm' };
                     } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                         options = { mimeType: 'audio/mp4' };
                     }
                     this.mediaRecorder = new MediaRecorder(stream, options);
                     this.mediaRecorder.ondataavailable = e => {
                         if (e.data.size > 0) this.audioChunks.push(e.data);
                     };
                     this.mediaRecorder.onstop = () => {
                         if (this.discard) {
                             this.cleanupStream();
                             return;
                         }
                         const mime = this.mediaRecorder.mimeType || 'audio/webm';
                         const audioBlob = new Blob(this.audioChunks, { type: mime });
                         this.uploadAndSend(audioBlob, this.recordDuration);
                         this.cleanupStream();
                     };
                     this.mediaRecorder.start(200);
                     this.isRecording = true;
                     this.recordDuration = 0;
                     this.timerInterval = setInterval(() => { this.recordDuration++; }, 1000);
                 }).catch(err => {
                     alert('Mikrofondan foydalanishga ruxsat berilmadi: ' + err.message);
                 });
             },
             cancelRecord() {
                 this.discard = true;
                 if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                     this.mediaRecorder.stop();
                 }
                 this.isRecording = false;
                 clearInterval(this.timerInterval);
             },
             sendRecord() {
                 this.discard = false;
                 if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                     this.mediaRecorder.stop();
                 }
                 this.isRecording = false;
                 clearInterval(this.timerInterval);
             },
             cleanupStream() {
                 if (this.mediaStream) {
                     this.mediaStream.getTracks().forEach(t => t.stop());
                     this.mediaStream = null;
                 }
                 clearInterval(this.timerInterval);
             },
             uploadAndSend(blob, dur) {
                 this.isUploading = true;
                 const formData = new FormData();
                 formData.append('audio', blob, 'voice_' + Date.now() + '.webm');
                 formData.append('duration', dur);
                 formData.append('_token', '{{ csrf_token() }}');

                 fetch('{{ route('chat.voice.upload') }}', {
                     method: 'POST',
                     body: formData
                 })
                 .then(res => res.json())
                 .then(data => {
                     this.isUploading = false;
                     if (data.success && data.path) {
                         $wire.sendVoiceMessage(data.path, data.duration || dur);
                     } else {
                         alert('Ovozli xabarni yuklashda xatolik yuz berdi.');
                     }
                 })
                 .catch(err => {
                     this.isUploading = false;
                     alert('Yuklashda tarmoq xatoligi yuz berdi.');
                 });
             },
             formatDur(s) {
                 const m = Math.floor(s / 60);
                 const sec = Math.floor(s % 60);
                 return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
             }
         }">
        @auth
            {{-- RECORDING ACTIVE BAR --}}
            <div x-show="isRecording" x-cloak class="flex items-center justify-between gap-3 p-2 bg-rose-500/10 border border-rose-500/25 rounded-2xl animate-fade-in">
                <div class="flex items-center gap-3 pl-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
                    </span>
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 font-mono tracking-wider" x-text="'Yozilmoqda: ' + formatDur(recordDuration)"></span>
                    <div class="flex items-center gap-1 h-4">
                        <span class="w-1 h-3 bg-rose-500 rounded-full animate-bounce"></span>
                        <span class="w-1 h-4 bg-rose-500 rounded-full animate-bounce [animation-delay:0.15s]"></span>
                        <span class="w-1 h-2 bg-rose-500 rounded-full animate-bounce [animation-delay:0.3s]"></span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="cancelRecord()"
                            class="px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-300 transition-colors">
                        ✕ Bekor qilish
                    </button>
                    <button type="button" @click="sendRecord()"
                            class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-rose-600 to-amber-500 text-white text-xs font-bold shadow-md shadow-rose-600/30 active:scale-95 transition-all flex items-center gap-1.5">
                        <span>Yuborish</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            {{-- UPLOADING SPINNER --}}
            <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 py-3 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Ovozli xabar yuklanmoqda...</span>
            </div>

            {{-- STANDARD FORM BAR --}}
            <form x-show="!isRecording && !isUploading" wire:submit.prevent="sendMessage" @submit="count = 0" class="flex items-center gap-2 sm:gap-3">
                <div class="relative flex-1">
                    <input type="text" 
                        wire:model.defer="message" 
                        x-on:input="count = $event.target.value.length"
                        placeholder="{{ __('site.chat.placeholder') }}" 
                        maxlength="250"
                        class="w-full pl-4 pr-16 py-3 bg-slate-100 dark:bg-slate-800 border-none rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none"
                         :class="{
                             'text-slate-400': count < 200,
                             'text-amber-500 font-semibold': count >= 200 && count < 240,
                             'text-rose-500 font-bold': count >= 240
                         }">
                        <span x-text="count">0</span>/250
                    </div>
                </div>

                <!-- Voice Recording Mic Button -->
                <button type="button" @click="startRecord()"
                    class="p-3 bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-600 hover:text-rose-600 dark:text-slate-300 dark:hover:text-rose-400 rounded-2xl transition-all border border-slate-200/60 dark:border-slate-700/60 active:scale-95 shrink-0"
                    title="Ovozli xabar yozish">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <button type="submit" 
                    x-on:click="count = 0"
                    class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md shadow-indigo-600/25 transition-all flex items-center gap-1.5 shrink-0">
                    <span>{{ __('site.chat.send') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
            @error('message') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
        @else
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                    <span class="text-lg">🔒</span>
                    <span>{{ __('site.chat.need_login') }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        {{ __('site.auth.login_btn') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl transition-all">
                        {{ __('site.nav.register') }}
                    </a>
                </div>
            </div>
        @endauth
    </div>

    <!-- Taste-Skill Delete Confirmation Modal -->
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
