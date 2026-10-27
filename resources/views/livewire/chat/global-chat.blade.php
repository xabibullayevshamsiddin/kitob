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
             Livewire.hook('message.processed', (message, component) => {
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    try { window.Alpine.initTree(c); } catch (e) {}
                }
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
                $chatStyle = \App\Services\Gamification\ChatStyleService::resolveStyle($msg->user, $senderRank, $isMe);
                $bubbleTierClass = $chatStyle['bubble_class'];
                $badgeText = $chatStyle['badge_text'];
                $badgeClass = $chatStyle['badge_class'];
                $authorNameClass = $chatStyle['name_class'];
            @endphp
            <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }}" wire:key="msg-{{ $msg->id }}">
                <x-ui.avatar :user="$msg->user" size="sm" :rank="$senderRank" link />
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md min-w-0">
                    <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }} flex-wrap">
                        @if($msg->user && !$isMe)
                            <a href="{{ route('profile.show', $msg->user->username) }}" class="text-xs font-semibold {{ $authorNameClass }} hover:underline transition-colors truncate max-w-[150px]">
                                {{ $msg->user->name }}
                            </a>
                        @else
                            <span class="text-xs font-semibold {{ $authorNameClass }} truncate max-w-[150px]">{{ $isMe ? __('site.chat.you') : ($msg->user?->name ?? __('site.chat.user')) }}</span>
                        @endif

                        @if($badgeText)
                            <span class="{{ $badgeClass }}">{{ $badgeText }}</span>
                        @endif

                        @if($senderTitle = $msg->user?->equipped_title)
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold"
                                  style="background-color: {{ $senderTitle->payload['badge_color'] ?? '#FBBF24' }}20; color: {{ $senderTitle->payload['badge_color'] ?? '#FBBF24' }}; border: 1px solid {{ $senderTitle->payload['badge_color'] ?? '#FBBF24' }}50;">
                                {{ $senderTitle->name_text }}
                            </span>
                        @endif

                        @if($senderRank && ($chatStyle['mode'] ?? '') !== 'rank')
                            <x-ui.rank-badge :rank="$senderRank" size="xs" :compact="true" />
                        @endif

                        @if(($msg->user?->role ?? '') === 'admin' || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('admin')))
                            <span class="px-1.5 py-0.2 bg-rose-500/10 dark:bg-amber-500/15 border border-rose-500/25 dark:border-amber-500/30 text-rose-700 dark:text-amber-400 font-mono font-bold text-[9px] rounded uppercase">{{ __('site.leaderboard.role_admin') }}</span>
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

                        {{-- Admin Ban Button (Adminlar daxlsizdir) --}}
                        @if($isAdmin && !$isMe && $msg->user && !$msg->user->isAdmin())
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

                        @if(!$isMe && !$msg->user?->isAdmin())
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
                             wire:key="audio-bubble-{{ $msg->id }}"
                             x-data="voicePlayer('{{ $msg->audio_url }}', {{ (int) ($msg->audio_duration ?: 0) }})">
                            <div class="flex items-center gap-3 min-w-[210px] sm:min-w-[250px]">
                                <button type="button"
                                        @click="toggle()"
                                        onclick="if(!this.closest('[x-data]')?._x_dataStack && window.fallbackVoiceToggle){ window.fallbackVoiceToggle(this); }"
                                        class="w-8 h-8 rounded-btn flex items-center justify-center shrink-0 transition-colors {{ $isMe ? 'bg-amber-500 text-ink-950' : 'bg-ink-800 text-paper border border-ink-border' }}"
                                        title="Ovozli xabarni ijro etish / to'xtatish">
                                    <svg x-show="!playing" class="w-3.5 h-3.5 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg x-show="playing" x-cloak class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-0.5 h-5 cursor-pointer"
                                         @click="seek($event)">
                                        @php
                                            $voiceWaves = [35, 65, 45, 90, 75, 40, 85, 100, 70, 50, 85, 95, 60, 45, 75, 55, 80, 40];
                                        @endphp
                                        @foreach($voiceWaves as $wIdx => $wH)
                                            <span class="flex-1 rounded-full transition-all"
                                                  style="height: {{ $wH }}%;"
                                                  :class="({{ $wIdx }} / 18 * 100) <= progress ? 'bg-amber-400' : 'bg-ink-700'"></span>
                                        @endforeach
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
                 navigator.mediaDevices.getUserMedia({
                     audio: {
                         echoCancellation: true,
                         noiseSuppression: true,
                         autoGainControl: true,
                         channelCount: 1,
                         sampleRate: 48000
                     }
                 }).then(stream => {
                     this.mediaStream = stream;
                     this.audioChunks = [];
                     this.discard = false;
                     let options = { audioBitsPerSecond: 64000 };
                     if (typeof MediaRecorder !== 'undefined' && typeof MediaRecorder.isTypeSupported === 'function') {
                         if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                             options.mimeType = 'audio/webm;codecs=opus';
                         } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                             options.mimeType = 'audio/webm';
                         } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                             options.mimeType = 'audio/mp4';
                         }
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
                         const p = $wire.sendVoiceMessage(data.path, data.duration || dur);
                        if (p && typeof p.then === 'function') {
                            p.then(() => {
                                setTimeout(() => {
                                    const c = document.getElementById('chat-container');
                                    if (c && window.Alpine && typeof window.Alpine.initTree === 'function') {
                                        window.Alpine.initTree(c);
                                    }
                                }, 50);
                            });
                        }
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
            @if(auth()->user()->isBanned())
                {{-- BANNED USER LOCKED INPUT BAR --}}
                <div class="p-3 rounded-xl bg-rose-950/30 border border-rose-500/35 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-300 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <p class="text-xs font-bold text-rose-300 font-mono flex flex-wrap items-center gap-1.5">
                                <span>🔒 Xabar yozish qulflangan (Siz bloklangansiz!)</span>
                                <span class="px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/30 text-[10px] text-rose-200">
                                    {{ auth()->user()->ban_remaining }}
                                </span>
                            </p>
                            <p class="text-[11px] text-mist font-sans">
                                <span class="text-rose-200/90 font-mono">Sabab: {{ auth()->user()->ban_reason ?? 'Qoidabuzarlik' }}.</span>
                                ⚖️ Noto'g'ri ban berilgan bo'lsa, mana bu joyga yozib qoldiring:
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('contact', ['appeal' => 1, 'user_id' => auth()->id()]) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-200 font-bold text-xs shrink-0 transition-colors">
                        <span>📩 Aloqa bo'limi</span>
                        <span>→</span>
                    </a>
                </div>
            @else
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
            @endif
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
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md"
         x-transition:enter="transition-opacity ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-320"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Modal Dialog Card -->
        <div x-show="deleteModalOpen"
             x-transition:enter="ks-blur-in-enter-active"
             x-transition:enter-start="ks-blur-in-enter"
             x-transition:enter-end="ks-blur-in-enter-end"
             x-transition:leave="ks-blur-in-leave-active"
             x-transition:leave-start="ks-blur-in-leave"
             x-transition:leave-end="ks-blur-in-leave-end"
             @click.away="deleteModalOpen = false"
             class="ks-panel bg-ink-900 border border-ink-border rounded-panel p-6 max-w-sm w-full text-center shadow-[0_30px_70px_rgba(0,0,0,0.95)] ks-modal-card">

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
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md"
         x-transition:enter="transition-opacity ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-320"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div x-show="banModalOpen"
             x-transition:enter="ks-blur-in-enter-active"
             x-transition:enter-start="ks-blur-in-enter"
             x-transition:enter-end="ks-blur-in-enter-end"
             x-transition:leave="ks-blur-in-leave-active"
             x-transition:leave-start="ks-blur-in-leave"
             x-transition:leave-end="ks-blur-in-leave-end"
             @click.away="banModalOpen = false"
             class="ks-panel bg-ink-900 border border-ink-border rounded-panel p-6 max-w-md w-full text-left shadow-[0_30px_70px_rgba(0,0,0,0.95)] ks-modal-card">

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
