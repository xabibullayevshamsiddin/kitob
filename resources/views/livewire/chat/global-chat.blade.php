<div class="max-w-4xl mx-auto h-[calc(100dvh-12rem)] sm:h-[calc(100vh-8rem)] flex flex-col bg-ink-900 rounded-panel border border-ink-border shadow-soft overflow-hidden relative"
     wire:poll.visible.15s
     x-data="{
         deleteModalOpen: false,
         targetMessageId: null,
         banModalOpen: false,
         banUserId: null,
         banUserName: '',
         banDuration: '1_day',
         banReason: '',
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
         },
         openBan(id, name) {
             this.banUserId = id;
             this.banUserName = name;
             this.banDuration = '1_day';
             this.banReason = '';
             this.banModalOpen = true;
         },
         executeBan() {
             if (this.banUserId) {
                 $wire.banUser(this.banUserId, this.banDuration, this.banReason);
                 this.banModalOpen = false;
                 this.banUserId = null;
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
    <div class="p-3.5 sm:px-6 border-b border-ink-border flex items-center justify-between bg-ink-950/60 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">{{ __('site.chat.title') }}</h2>
                @if($isChatEnabled)
                    <span class="text-[11px] text-emerald-400 font-mono font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ __('site.chat.live') }}
                    </span>
                @else
                    <span class="text-[11px] text-amber-400 font-mono font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Vaqtincha to'xtatilgan ⛔
                    </span>
                @endif
            </div>
        </div>
        <span class="hidden sm:block text-xs font-mono text-mist">{{ __('site.chat.msg_count', ['count' => $messages->count()]) }}</span>
    </div>

    <!-- Messages Container -->
    <div id="chat-container" class="flex-1 p-4 sm:p-6 overflow-y-auto overflow-x-hidden space-y-4" x-init="setTimeout(() => { const c = document.getElementById('chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
        @forelse ($messages as $msg)
            @php 
                $isMe = auth()->check() && auth()->id() === $msg->user_id;
                $isAdmin = auth()->check() && (auth()->user()->role === 'admin' || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin')));
                $canDelete = $isMe || $isAdmin;

                $senderRank = $topFiveIds[$msg->user_id] ?? null;
                $senderTier = $senderRank ? match($senderRank) {
                    1 => 'gold',
                    2 => 'silver',
                    3 => 'bronze',
                    default => 'top5',
                } : null;

                $bubbleTierClass = match($senderTier) {
                    'gold'   => 'border-[#F59E0B]/60 bg-gradient-to-br from-[#F59E0B]/10 via-ink-950 to-ink-950 shadow-[0_0_12px_rgba(245,158,11,0.12)]',
                    'silver' => 'border-[#E2E8F0]/40 bg-gradient-to-br from-[#E2E8F0]/5 via-ink-950 to-ink-950 shadow-[0_0_10px_rgba(226,232,240,0.08)]',
                    'bronze' => 'border-[#D97706]/50 bg-gradient-to-br from-[#D97706]/10 via-ink-950 to-ink-950 shadow-[0_0_10px_rgba(217,119,6,0.08)]',
                    'top5'   => 'border-[#6366F1]/40 bg-gradient-to-br from-[#6366F1]/5 via-ink-950 to-ink-950 shadow-[0_0_8px_rgba(99,102,241,0.08)]',
                    default  => $isMe ? 'bg-ink-800 border-amber-500/40' : 'bg-ink-950 border-ink-border',
                };
            @endphp
            <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }}" wire:key="msg-{{ $msg->id }}">
                <x-ui.avatar :user="$msg->user" size="sm" :rank="$senderRank" link />
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                    <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }}">
                        @if($msg->user && !$isMe)
                            <a href="{{ route('profile.show', $msg->user->username) }}" class="text-xs font-semibold text-paper hover:text-amber-400 hover:underline transition-colors">
                                {{ $msg->user->name }}
                            </a>
                        @else
                            <span class="text-xs font-semibold text-paper">{{ $isMe ? __('site.chat.you') : ($msg->user?->name ?? __('site.chat.user')) }}</span>
                        @endif
                        @if($senderRank)
                            <x-ui.rank-badge :rank="$senderRank" size="xs" :compact="true" />
                        @endif
                        @if(($msg->user?->role ?? '') === 'admin' || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('admin')))
                            <span class="px-1.5 py-0.2 bg-amber-500/15 border border-amber-500/30 text-amber-400 font-mono font-bold text-[9px] rounded uppercase">{{ __('site.leaderboard.role_admin') }}</span>
                        @endif
                        <span class="text-[10px] font-mono text-mist">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>

                        @if($canDelete)
                            <button type="button"
                                @click="confirmDelete({{ $msg->id }})"
                                title="{{ $isMe ? __('site.chat.delete_own') : __('site.chat.delete_admin') }}"
                                class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 text-mist hover:text-rose-300 rounded">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endif

                        {{-- Admin Ban Button --}}
                        @if($isAdmin && !$isMe && $msg->user)
                            @if($msg->user->isBanned())
                                <button type="button"
                                    wire:click="unbanUser({{ $msg->user_id }})"
                                    title="Foydalanuvchi bloklangan ({{ $msg->user->ban_remaining }}). Banni yechish uchun bosing."
                                    class="p-0.5 text-rose-300 hover:text-emerald-400 rounded text-[10px] flex items-center gap-1 font-mono font-bold bg-rose-500/10 px-1.5 py-0.5 border border-rose-500/20 transition-all">
                                    <span>Bloklangan</span>
                                    <span class="text-emerald-400 hover:underline">Yechish</span>
                                </button>
                            @else
                                <button type="button"
                                    @click="openBan({{ $msg->user_id }}, '{{ addslashes($msg->user->name) }}')"
                                    title="Foydalanuvchini bloklash (Ban berish)"
                                    class="p-0.5 text-rose-300 hover:bg-rose-500/20 rounded text-[10px] flex items-center gap-0.5 font-mono font-bold transition-all px-1.5 py-0.5 bg-rose-500/10 border border-rose-500/20">
                                    <span>Ban</span>
                                </button>
                            @endif
                        @endif

                        @if(!$isMe)
                            <a href="{{ route('contact', [
                                'report'            => 1,
                                'reported_user_id'  => $msg->user_id,
                                'reported_name'     => $msg->user?->name ?? 'Foydalanuvchi',
                                'reported_username' => $msg->user?->username ?? 'user',
                                'source'            => 'Umumiy chat',
                                'message_id'        => $msg->id,
                                'message_text'      => Str::limit($msg->message, 300),
                                'url'               => url()->current(),
                                'time'              => $msg->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i'),
                            ]) }}"
                               title="Ushbu xabar yoki haqorat bo'yicha ma'muriyatga shikoyat qilish"
                               class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 text-mist hover:text-amber-400 rounded text-[11px] flex items-center gap-0.5">
                                <span class="text-rose-400 font-mono text-[10px]">🚩</span>
                                <span class="text-[10px] font-mono hidden sm:inline">Shikoyat</span>
                            </a>
                        @endif
                    </div>
                    @if($msg->audio_path)
                        <div class="p-3 rounded-card text-paper {{ $isMe ? 'rounded-tr-none' : 'rounded-tl-none' }} border {{ $bubbleTierClass }}"
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
                            <div class="flex items-center gap-3 min-w-[210px] sm:min-w-[250px]">
                                <button type="button" @click="toggle()"
                                        class="w-8 h-8 rounded-btn flex items-center justify-center shrink-0 transition-colors {{ $isMe ? 'bg-amber-500 text-ink-950' : 'bg-ink-800 text-paper border border-ink-border' }}">
                                    <template x-if="!playing">
                                        <svg class="w-3.5 h-3.5 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </template>
                                    <template x-if="playing">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </template>
                                </button>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-0.5 h-5 cursor-pointer"
                                         @click="if(audio && duration) { const r = $el.getBoundingClientRect(); const p = Math.max(0, Math.min(1, ($event.clientX - r.left) / r.width)); audio.currentTime = p * duration; }">
                                        <template x-for="(h, i) in [35, 65, 45, 90, 75, 40, 85, 100, 70, 50, 85, 95, 60, 45, 75, 55, 80, 40]" :key="i">
                                            <span class="flex-1 rounded-full transition-all"
                                                  :style="'height: ' + h + '%;'"
                                                  :class="(i / 18 * 100) <= progress ? 'bg-amber-400' : 'bg-ink-700'"></span>
                                        </template>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-mono text-mist">
                                        <span x-text="playing ? formatTime(currentTime) : 'Ovozli xabar'">Ovozli xabar</span>
                                        <span x-text="formatTime(duration)">{{ sprintf('%02d:%02d', floor(($msg->audio_duration ?? 0)/60), ($msg->audio_duration ?? 0)%60) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="px-3.5 py-2.5 rounded-card text-xs sm:text-sm leading-relaxed break-words [word-break:break-word] text-paper {{ $isMe ? 'rounded-tr-none text-left' : 'rounded-tl-none' }} border {{ $bubbleTierClass }}">{{ $msg->message }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-mist text-xs font-mono">
                {{ __('site.chat.empty') }}
            </div>
        @endforelse
    </div>

    <!-- Message Input Bar with Voice Note Recording -->
    @if(!$isChatEnabled && !$isAdmin)
        <div class="p-4 border-t border-ink-border bg-ink-950 flex items-center justify-center">
            <div class="inline-flex items-center gap-2.5 px-5 py-3 rounded-card bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-medium text-center">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Umumiy global chat ma'muriyat tomonidan vaqtincha to'xtatilgan.</span>
            </div>
        </div>
    @else
    <div class="p-3 sm:p-4 border-t border-ink-border bg-ink-950"
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
            <div x-show="isRecording" x-cloak class="flex items-center justify-between gap-3 p-2 bg-ink-900 border border-vermilion/30 rounded-card">
                <div class="flex items-center gap-3 pl-3">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-vermilion opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-vermilion"></span>
                    </span>
                    <span class="text-xs font-bold text-rose-300 font-mono tracking-wider" x-text="'Yozilmoqda: ' + formatDur(recordDuration)"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="cancelRecord()"
                            class="ks-btn-ghost py-1 px-3 text-xs">
                        ✕ Bekor qilish
                    </button>
                    <button type="button" @click="sendRecord()"
                            class="ks-btn-primary py-1 px-3 text-xs flex items-center gap-1.5">
                        <span>Yuborish</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            {{-- UPLOADING SPINNER --}}
            <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 py-3 text-xs font-mono text-amber-400">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Ovozli xabar yuklanmoqda...</span>
            </div>

            {{-- STANDARD FORM BAR --}}
            <form x-show="!isRecording && !isUploading" wire:submit.prevent="sendMessage" @submit="count = 0" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" 
                        wire:model.defer="message" 
                        x-on:input="count = $event.target.value.length"
                        placeholder="{{ __('site.chat.placeholder') }}" 
                        maxlength="250"
                        class="ks-input pr-14 text-xs sm:text-sm">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none"
                         :class="{
                             'text-mist': count < 200,
                             'text-amber-400 font-semibold': count >= 200 && count < 240,
                             'text-rose-300 font-bold': count >= 240
                         }">
                        <span x-text="count">0</span>/250
                    </div>
                </div>

                <!-- Voice Recording Mic Button -->
                <button type="button" @click="startRecord()"
                    class="ks-btn-ghost p-2.5 text-mist hover:text-amber-400 shrink-0"
                    title="Ovozli xabar yozish">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <button type="submit" 
                    x-on:click="count = 0"
                    class="ks-btn-primary py-2 px-3 sm:px-4 text-xs flex items-center gap-1.5 shrink-0">
                    <span class="hidden sm:inline">{{ __('site.chat.send') }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
            @error('message') <p class="text-rose-300 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
        @else
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3 bg-ink-900 rounded-card border border-ink-border">
                <div class="flex items-center gap-2 text-xs text-mist font-sans">
                    <span class="text-amber-400 font-bold">🔒</span>
                    <span>{{ __('site.chat.need_login') }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}" class="ks-btn-primary py-1.5 px-3 text-xs">
                        {{ __('site.auth.login_btn') }}
                    </a>
                    <a href="{{ route('register') }}" class="ks-btn-ghost py-1.5 px-3 text-xs">
                        {{ __('site.nav.register') }}
                    </a>
                </div>
            </div>
        @endauth
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         x-cloak
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Modal Dialog Card -->
        <div @click.away="deleteModalOpen = false"
             class="ks-panel bg-ink-900 border border-ink-border rounded-panel p-6 max-w-sm w-full text-center">

            <!-- Danger Badge Icon -->
            <div class="mx-auto w-12 h-12 rounded-btn bg-ink-950 border border-rose-500/30 text-rose-300 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h3 class="text-base font-bold font-serif text-paper">{{ __('site.chat.delete_title') }}</h3>
            <p class="text-xs text-mist leading-relaxed mt-2 mb-5 font-sans">
                {{ __('site.chat.delete_desc') }}
            </p>

            <div class="flex items-center justify-center gap-3">
                <button type="button"
                    @click="deleteModalOpen = false"
                    class="ks-btn-ghost flex-1 py-2 text-xs">
                    {{ __('site.chat.cancel') }}
                </button>
                <button type="button"
                    @click="executeDelete()"
                    class="ks-btn-primary flex-1 py-2 text-xs">
                    <span>{{ __('site.chat.delete') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Ban Modal -->
    <div x-show="banModalOpen"
         x-cloak
         @keydown.escape.window="banModalOpen = false"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="banModalOpen = false"
             class="ks-panel bg-ink-900 border border-ink-border rounded-panel p-6 max-w-md w-full text-left">

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-btn bg-ink-950 border border-rose-500/30 text-rose-300 flex items-center justify-center text-xl font-bold flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold font-serif text-paper">Foydalanuvchini bloklash (Ban)</h3>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        <strong class="text-paper" x-text="banUserName"></strong> uchun muddat tanlang
                    </p>
                </div>
            </div>

            <!-- Duration Selection -->
            <div class="mb-4">
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-2">
                    Ban muddati:
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 rounded-btn border cursor-pointer text-xs transition-colors"
                           :class="banDuration === '1_hour' ? 'border-amber-500/60 bg-amber-500/10 text-amber-300 font-bold' : 'border-ink-border bg-ink-950 text-mist'">
                        <input type="radio" name="banDuration" value="1_hour" x-model="banDuration" class="accent-amber-500">
                        <span>1 soat</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-btn border cursor-pointer text-xs transition-colors"
                           :class="banDuration === '1_day' ? 'border-amber-500/60 bg-amber-500/10 text-amber-300 font-bold' : 'border-ink-border bg-ink-950 text-mist'">
                        <input type="radio" name="banDuration" value="1_day" x-model="banDuration" class="accent-amber-500">
                        <span>1 kun</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-btn border cursor-pointer text-xs transition-colors"
                           :class="banDuration === '1_week' ? 'border-amber-500/60 bg-amber-500/10 text-amber-300 font-bold' : 'border-ink-border bg-ink-950 text-mist'">
                        <input type="radio" name="banDuration" value="1_week" x-model="banDuration" class="accent-amber-500">
                        <span>1 hafta</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-btn border cursor-pointer text-xs transition-colors"
                           :class="banDuration === '1_month' ? 'border-amber-500/60 bg-amber-500/10 text-amber-300 font-bold' : 'border-ink-border bg-ink-950 text-mist'">
                        <input type="radio" name="banDuration" value="1_month" x-model="banDuration" class="accent-amber-500">
                        <span>1 oy</span>
                    </label>
                    <label class="col-span-2 flex items-center gap-2 p-2 rounded-btn border cursor-pointer text-xs transition-colors"
                           :class="banDuration === 'permanent' ? 'border-rose-500/60 bg-rose-500/10 text-rose-300 font-bold' : 'border-ink-border bg-ink-950 text-mist'">
                        <input type="radio" name="banDuration" value="permanent" x-model="banDuration" class="accent-rose-500">
                        <span>Doimiy / Butun umrga</span>
                    </label>
                </div>
            </div>

            <!-- Reason -->
            <div class="mb-5">
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">
                    Ban sababi (ixtiyoriy):
                </label>
                <textarea x-model="banReason"
                          rows="2"
                          placeholder="Masalan: Chatda haqoratli so'z ishlatgani uchun..."
                          class="ks-input text-xs"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2.5">
                <button type="button"
                    @click="banModalOpen = false"
                    class="ks-btn-ghost py-1.5 px-3 text-xs">
                    Bekor qilish
                </button>
                <button type="button"
                    @click="executeBan()"
                    class="ks-btn-primary py-1.5 px-4 text-xs">
                    <span>Bloklash</span>
                </button>
            </div>
        </div>
    </div>
</div>
