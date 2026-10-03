<div class="max-w-6xl mx-auto space-y-5 pb-16">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.index') }}" class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="w-10 h-10 rounded-btn {{ $group->is_private ? 'bg-[#C1392B]/15 border border-rose-500/30 text-rose-300' : 'bg-amber-500/10 border border-amber-500/25 text-amber-400' }} flex items-center justify-center shrink-0">
                @if($group->is_private)
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                @else
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @endif
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">{{ $group->name }}</h1>
                <p class="text-xs font-mono text-mist">{{ $members->count() }} {{ __('site.groups.members') }} {{ $group->book ? '• ' . $group->book->title : '' }}</p>
            </div>
        </div>

        @if($canDelete)
            <div>
                <button wire:click="openDeleteModal"
                    class="px-3.5 py-2 bg-[#C1392B]/15 hover:bg-[#C1392B]/25 text-rose-300 border border-rose-500/30 text-xs font-mono font-medium rounded-btn transition-all flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>{{ __('site.groups.delete_group') }}</span>
                </button>
            </div>
        @endif
    </div>

    @if ($group->description)
        <p class="text-xs sm:text-sm text-mist leading-relaxed max-w-3xl font-sans">{{ $group->description }}</p>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Group Chat -->
        <div class="lg:col-span-2 h-[36rem] flex flex-col bg-ink-900 rounded-panel border border-ink-border shadow-soft overflow-hidden relative"
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
                 const c = document.getElementById('group-chat-container');
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
            <div class="p-3.5 sm:px-5 border-b border-ink-border flex items-center justify-between bg-ink-950/80 backdrop-blur-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-paper font-serif">{{ __('site.groups.chat_title') }}</h2>
                        <span class="text-[10px] font-mono text-emerald-400 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ __('site.chat.live') }}
                        </span>
                    </div>
                </div>
                <span class="text-xs font-mono text-mist">{{ __('site.chat.msg_count', ['count' => $messages->count()]) }}</span>
            </div>

            <!-- Messages Container -->
            <div id="group-chat-container" class="flex-1 p-4 sm:p-5 overflow-y-auto overflow-x-hidden space-y-3.5" x-init="setTimeout(() => { const c = document.getElementById('group-chat-container'); if (c) c.scrollTop = c.scrollHeight; }, 50)">
                @forelse ($messages as $msg)
                    @php 
                        $isMe = auth()->check() && auth()->id() === $msg->user_id;
                        $isAdmin = auth()->check() && (auth()->user()->role === 'admin' || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin')));
                        $isGroupOwner = auth()->check() && ($group->created_by === auth()->id());
                        $canDeleteMsg = $isMe || $isAdmin || $isGroupOwner;
                    @endphp
                    <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }}" wire:key="gm-{{ $msg->id }}">
                        <img src="{{ $msg->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=1e293b&color=fff' }}" class="w-7 h-7 rounded-btn object-cover shrink-0 border border-ink-border mt-0.5" alt="{{ $msg->user?->name ?? __('site.chat.user') }}">

                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                            <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <span class="text-xs font-bold text-paper font-sans">{{ $isMe ? __('site.chat.you') : ($msg->user?->name ?? __('site.chat.user')) }}</span>
                                @if(($msg->user?->role ?? '') === 'admin' || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('admin')))
                                    <span class="px-1.5 py-0.2 bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 font-mono text-[9px] rounded-pill uppercase">{{ __('site.leaderboard.role_admin') }}</span>
                                @elseif($msg->user_id === $group->created_by)
                                    <span class="px-1.5 py-0.2 bg-amber-500/10 border border-amber-500/25 text-amber-400 font-mono text-[9px] rounded-pill uppercase">Asoschi</span>
                                @endif
                                <span class="text-[10px] font-mono text-mist">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>

                                @if($canDeleteMsg)
                                    <button type="button"
                                        @click="confirmDelete({{ $msg->id }})"
                                        title="{{ $isMe ? __('site.chat.delete_own') : __('site.chat.delete_admin') }}"
                                        class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 text-mist hover:text-rose-300 rounded">
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
                                            class="p-0.5 text-rose-300 hover:text-emerald-400 rounded text-[10px] flex items-center gap-1 font-mono font-bold bg-[#C1392B]/15 px-1.5 py-0.5 border border-rose-500/30 transition-all">
                                            <span>Bloklangan</span>
                                            <span class="text-emerald-400 hover:underline">Yechish</span>
                                        </button>
                                    @else
                                        <button type="button"
                                            @click="openBan({{ $msg->user_id }}, '{{ addslashes($msg->user->name) }}')"
                                            title="Foydalanuvchini bloklash (Ban berish)"
                                            class="p-0.5 text-rose-300 hover:bg-[#C1392B]/20 rounded text-[10px] flex items-center gap-0.5 font-mono font-bold transition-all px-1.5 py-0.5 bg-[#C1392B]/15 border border-rose-500/30">
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
                                        'source'            => 'Guruh: ' . $group->name,
                                        'message_id'        => $msg->id,
                                        'message_text'      => Str::limit($msg->message, 300),
                                        'url'               => url()->current(),
                                        'time'              => $msg->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i'),
                                    ]) }}"
                                       title="Shikoyat qilish"
                                       class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 text-mist hover:text-amber-400 rounded text-[11px] flex items-center gap-0.5">
                                        <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                    </a>
                                @endif
                            </div>

                            @if($msg->audio_path)
                                <div class="p-3 rounded-panel shadow-sm {{ $isMe ? 'bg-ink-800 border border-ink-border text-paper rounded-tr-none' : 'bg-ink-950/80 border border-ink-border text-paper rounded-tl-none' }}"
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
                                                class="w-9 h-9 rounded-btn flex items-center justify-center shrink-0 transition-transform active:scale-95 bg-amber-400 text-ink-950 hover:bg-amber-300">
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
                                                          :class="(i / 18 * 100) <= progress ? 'bg-amber-400' : 'bg-ink-800'"></span>
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
                                <div class="px-3.5 py-2 rounded-panel text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] {{ $isMe ? 'bg-ink-800 border border-ink-border text-paper rounded-tr-none text-left' : 'bg-ink-950/80 border border-ink-border text-paper rounded-tl-none' }}">{{ $msg->message }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-mist font-mono text-xs">
                        {{ __('site.groups.chat_empty') }}
                    </div>
                @endforelse
            </div>

            <!-- Message Input Bar with Voice Note Recording -->
            <div class="p-3 border-t border-ink-border bg-ink-950/80"
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
                {{-- RECORDING ACTIVE BAR --}}
                <div x-show="isRecording" x-cloak class="flex items-center justify-between gap-3 p-2 bg-[#C1392B]/15 border border-rose-500/30 rounded-btn">
                    <div class="flex items-center gap-3 pl-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#C1392B]"></span>
                        </span>
                        <span class="text-xs font-mono text-rose-300 font-bold tracking-wider" x-text="'Yozilmoqda: ' + formatDur(recordDuration)"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="cancelRecord()"
                                class="px-2.5 py-1 rounded-btn bg-ink-800 text-mist text-xs font-mono hover:text-paper transition-colors">
                            ✕ Bekor qilish
                        </button>
                        <button type="button" @click="sendRecord()"
                                class="px-3 py-1 rounded-btn bg-amber-400 hover:bg-amber-300 text-ink-950 text-xs font-mono font-bold transition-all flex items-center gap-1.5">
                            <span>Yuborish</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- UPLOADING SPINNER --}}
                <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 py-2 text-xs font-mono text-amber-400">
                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
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
                            maxlength="500"
                            class="w-full pl-3.5 pr-14 py-2 bg-ink-900 border border-ink-border rounded-btn text-xs font-sans text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none text-mist">
                            <span x-text="count">0</span>/500
                        </div>
                    </div>

                    <!-- Voice Recording Mic Button -->
                    <button type="button" @click="startRecord()"
                        class="p-2 bg-ink-900 hover:bg-ink-800 text-mist hover:text-amber-400 rounded-btn transition-all border border-ink-border shrink-0"
                        title="Ovozli xabar yozish">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </button>

                    <button type="submit" 
                        x-on:click="count = 0"
                        class="ks-btn-primary text-xs py-2 px-3.5 shrink-0">
                        <span>{{ __('site.chat.send') }}</span>
                    </button>
                </form>
                @error('message') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Delete Message Confirmation Modal -->
            <div x-show="deleteModalOpen" 
                 x-cloak
                 @keydown.escape.window="deleteModalOpen = false"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-md">

                <div @click.away="deleteModalOpen = false"
                     class="bg-ink-900 border border-ink-border rounded-panel shadow-2xl p-5 max-w-sm w-full relative overflow-hidden text-center">

                    <div class="mx-auto w-10 h-10 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <h3 class="text-sm font-bold text-paper font-serif">{{ __('site.chat.delete_title') }}</h3>
                    <p class="text-xs text-mist font-sans leading-relaxed mt-1 mb-4">
                        {{ __('site.chat.delete_desc') }}
                    </p>

                    <div class="flex items-center justify-center gap-2.5">
                        <button type="button"
                            @click="deleteModalOpen = false"
                            class="flex-1 py-1.5 px-3 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors">
                            {{ __('site.chat.cancel') }}
                        </button>
                        <button type="button"
                            @click="executeDelete()"
                            class="flex-1 py-1.5 px-3 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper text-xs font-mono font-bold transition-all">
                            <span>{{ __('site.chat.delete') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ban Modal (Guruh chatida admin uchun tezkor ban oynasi) -->
            <div x-show="banModalOpen"
                 x-cloak
                 @keydown.escape.window="banModalOpen = false"
                 class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-md">

                <div @click.away="banModalOpen = false"
                     class="bg-ink-900 border border-ink-border rounded-panel shadow-2xl p-5 max-w-md w-full relative overflow-hidden text-left">

                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-paper font-serif">Foydalanuvchini bloklash (Ban)</h3>
                            <p class="text-xs text-mist font-mono mt-0.5">
                                <span class="text-amber-400" x-text="banUserName"></span> uchun muddat tanlang
                            </p>
                        </div>
                    </div>

                    <div class="mb-3.5">
                        <label class="block text-[11px] font-mono text-mist mb-1.5 uppercase tracking-wider">
                            Ban muddati:
                        </label>
                        <div class="grid grid-cols-2 gap-2 font-mono text-xs">
                            <label class="flex items-center gap-2 p-2 rounded-btn border border-ink-border cursor-pointer transition-colors"
                                   :class="banDuration === '1_hour' ? 'border-amber-400 bg-amber-500/10 text-amber-400' : 'text-mist'">
                                <input type="radio" name="gBanDuration" value="1_hour" x-model="banDuration" class="accent-amber-400">
                                <span>1 soat</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-btn border border-ink-border cursor-pointer transition-colors"
                                   :class="banDuration === '1_day' ? 'border-amber-400 bg-amber-500/10 text-amber-400' : 'text-mist'">
                                <input type="radio" name="gBanDuration" value="1_day" x-model="banDuration" class="accent-amber-400">
                                <span>1 kun</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-btn border border-ink-border cursor-pointer transition-colors"
                                   :class="banDuration === '1_week' ? 'border-amber-400 bg-amber-500/10 text-amber-400' : 'text-mist'">
                                <input type="radio" name="gBanDuration" value="1_week" x-model="banDuration" class="accent-amber-400">
                                <span>1 hafta</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-btn border border-ink-border cursor-pointer transition-colors"
                                   :class="banDuration === '1_month' ? 'border-amber-400 bg-amber-500/10 text-amber-400' : 'text-mist'">
                                <input type="radio" name="gBanDuration" value="1_month" x-model="banDuration" class="accent-amber-400">
                                <span>1 oy</span>
                            </label>
                            <label class="col-span-2 flex items-center gap-2 p-2 rounded-btn border border-ink-border cursor-pointer transition-colors"
                                   :class="banDuration === 'permanent' ? 'border-rose-500/60 bg-[#C1392B]/15 text-rose-300' : 'text-mist'">
                                <input type="radio" name="gBanDuration" value="permanent" x-model="banDuration" class="accent-rose-500">
                                <span>Doimiy / Butun umrga</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-[11px] font-mono text-mist mb-1 uppercase tracking-wider">
                            Ban sababi (ixtiyoriy):
                        </label>
                        <textarea x-model="banReason"
                                  rows="2"
                                  placeholder="Masalan: Guruhda haqoratli so'z ishlatgani uchun..."
                                  class="w-full rounded-btn border border-ink-border bg-ink-950/80 p-2.5 text-xs text-paper focus:outline-none focus:border-amber-400 placeholder-mist"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5">
                        <button type="button"
                            @click="banModalOpen = false"
                            class="py-1.5 px-3.5 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors">
                            Bekor qilish
                        </button>
                        <button type="button"
                            @click="executeBan()"
                            class="py-1.5 px-4 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper text-xs font-mono font-bold transition-all">
                            <span>Bloklash</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Sidebar -->
        <div class="space-y-3">
            <div class="p-4 bg-ink-900 rounded-panel border border-ink-border">
                <h3 class="text-xs font-mono uppercase tracking-wider text-mist mb-3">{{ __('site.groups.members_list') }} ({{ $members->count() }})</h3>
                <div class="space-y-2">
                    @foreach ($members as $member)
                        <a href="{{ route('profile.show', $member->user->username) }}" class="flex items-center justify-between gap-2 hover:bg-ink-800/50 rounded-btn p-2 transition-colors" wire:key="mem-{{ $member->id }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $member->user->avatar_url }}" class="w-7 h-7 rounded object-cover border border-ink-border" alt="{{ $member->user->name }}">
                                <div class="min-w-0">
                                    <span class="text-xs font-medium text-paper truncate block font-sans">{{ $member->user->name }}</span>
                                    <span class="text-[10px] font-mono text-mist">{{ $member->role === 'admin' ? __('site.leaderboard.role_admin') : ($member->role === 'moderator' ? __('site.groups.moderator') : __('site.groups.member')) }}</span>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-amber-400 shrink-0">{{ number_format($member->user->total_points) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Group Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-ink-950/80 backdrop-blur-md" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-md p-6 rounded-panel bg-ink-900 border border-ink-border shadow-2xl space-y-4 text-center">

                <div class="mx-auto w-10 h-10 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-sm font-bold text-paper font-serif">
                        {{ __('site.groups.delete_confirm_title', ['name' => $group->name]) }}
                    </h3>
                    <p class="text-xs text-mist font-sans leading-relaxed">
                        {{ __('site.groups.delete_confirm_desc') }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-1.5 px-3 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors">
                        {{ __('site.chat.cancel') }}
                    </button>
                    <button type="button" wire:click="confirmDeleteGroup" wire:loading.attr="disabled"
                        class="flex-1 py-1.5 px-3 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper text-xs font-mono font-bold transition-all">
                        <span wire:loading.remove wire:target="confirmDeleteGroup">{{ __('site.groups.delete_yes') }}</span>
                        <span wire:loading wire:target="confirmDeleteGroup">{{ __('site.groups.deleting') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
