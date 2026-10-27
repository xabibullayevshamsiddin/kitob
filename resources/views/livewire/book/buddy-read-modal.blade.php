<div class="space-y-4" wire:poll.2500ms>
    @if($activeRoom)
        <!-- Active Buddy Read Room -->
        <div class="space-y-4">
            <!-- Room Top Bar -->
            <div class="flex items-center justify-between pb-3 border-b border-ink-border flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="selectBuddyRead(null)" class="text-xs font-mono text-mist hover:text-paper px-2.5 py-1 rounded-btn bg-ink-900 border border-ink-border transition-colors">
                        ← Barcha xonalar
                    </button>
                    <div>
                        <h4 class="text-sm font-bold text-paper font-serif">{{ $activeRoom->title }}</h4>
                        <p class="text-[11px] text-amber-500 font-mono flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Jonli efir • {{ $activeRoom->members->count() }}/{{ $activeRoom->max_members }} kitobxon</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($book->pdf_path)
                        <a href="{{ route('books.flipbook', $book->slug ?? $book->id) }}?page={{ $myMember?->current_page ?? 1 }}" 
                           class="px-3 py-1.5 rounded-btn bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all"
                           title="Kitobni ochish va 3D mutolaa qilish">
                            <span>📖</span>
                            <span>Mutolaaga o'tish</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('books.pdf', $book) }}" 
                           class="px-2.5 py-1.5 rounded-btn bg-rose-500/15 border border-rose-500/30 text-rose-400 hover:bg-rose-500/25 text-xs font-mono font-bold flex items-center gap-1 transition-all"
                           title="PDF yuklab olish">
                            <span>📥 PDF</span>
                        </a>
                    @endif

                    @if(!$myMember && auth()->check())
                        <button type="button" wire:click="joinBuddyRead({{ $activeRoom->id }})" class="ks-btn-primary text-xs py-1.5 px-3">
                            + Qo'shilish
                        </button>
                    @endif
                </div>
            </div>

            <!-- 🎙️ Jonli Ovozli Suhbat Xonasi (Live WebRTC P2P Voice Room Bar) -->
            @auth
                <div x-data="buddyLiveVoiceRoom({
                    roomId: {{ $activeRoom->id }},
                    userId: {{ auth()->id() }},
                    initialActive: {{ ($myMember && $myMember->voice_active) ? 'true' : 'false' }},
                    wire: $wire
                })" class="p-3 rounded-card bg-gradient-to-r from-amber-500/10 via-ink-900 to-ink-900 border border-amber-500/30 flex items-center justify-between flex-wrap gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm" 
                              :class="voiceActive ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 animate-pulse' : 'bg-ink-800 text-mist border border-ink-border'">
                            🎙️
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold font-serif text-paper">Jonli Ovozli Suhbat (WebRTC)</span>
                                <template x-if="voiceActive">
                                    <span class="px-1.5 py-0.5 rounded-badge bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                        <span x-text="connectedPeersCount > 0 ? ('Jonli efirdasiz • ' + connectedPeersCount + ' kishi bilan ulandiz') : 'Efirda siz (Do\'stlaringiz kutilmoqda...)'"></span>
                                    </span>
                                </template>
                            </div>
                            
                            <!-- Dynamic sound visualizer if active -->
                            <div x-show="voiceActive" x-cloak class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-mono text-emerald-400">Ovozingiz:</span>
                                <div class="flex items-center gap-0.5 h-3">
                                    <template x-for="i in 8" :key="i">
                                        <span class="w-1 rounded-full transition-all duration-75"
                                              :class="(i * 12) <= micLevel ? 'bg-emerald-400' : 'bg-ink-700'"
                                              :style="'height: ' + Math.max(3, Math.min(12, ((i * 12) <= micLevel ? (i * 2 + 4) : 3))) + 'px'"></span>
                                    </template>
                                </div>
                                <span x-show="isMuted" class="text-[10px] text-rose-400 font-mono">🔇 Mikrofoni o'chirilgan</span>
                                <span x-show="!isMuted && connectedPeersCount > 0" class="text-[10px] text-emerald-400 font-mono">✓ Ovoz uzatilmoqda</span>
                                <span x-show="!isMuted && connectedPeersCount === 0" class="text-[10px] text-mist font-mono">Xonadagilar efirga kirsa avtomatik eshitiladi</span>
                            </div>
                            <p x-show="!voiceActive" class="text-[11px] text-mist font-mono">Efirga kiring — do'stlaringiz bilan to'g'ridan-to'g'ri ovozli gaplashasiz</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Mute / Unmute Button -->
                        <template x-if="voiceActive">
                            <button type="button" @click="toggleMute()" 
                                class="px-2.5 py-1.5 rounded-btn text-xs font-mono font-bold border transition-colors flex items-center gap-1"
                                :class="isMuted ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : 'bg-ink-800 text-mist hover:text-paper border-ink-border'">
                                <span x-text="isMuted ? '🔇 Yoqish' : '🎤 O\'chirish'"></span>
                            </button>
                        </template>

                        <!-- Main Voice Enter/Leave Button -->
                        <button type="button" @click="toggleVoice()" 
                            :disabled="isConnecting"
                            class="px-3 py-1.5 rounded-btn text-xs font-bold font-mono transition-all flex items-center gap-1.5 shadow-sm"
                            :class="voiceActive ? 'bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40' : 'bg-amber-500 hover:bg-amber-400 text-ink-950'">
                            <span x-text="isConnecting ? 'Ulanmoqda...' : (voiceActive ? '🔇 Efirdan chiqish' : '🎙️ Ovozli efirga kirish')"></span>
                        </button>
                    </div>
                </div>
            @endauth

            <!-- Members Progress Tracker (StoryGraph style) -->
            <div class="p-3 rounded-card bg-ink-900 border border-ink-border space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-mono text-mist uppercase tracking-wider block">Kitobxonlar mutolaa tezligi:</span>
                    <span class="text-[10px] text-emerald-400 font-mono flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        Jonli yangilanmoqda
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($activeRoom->members as $member)
                        <div class="flex items-center gap-2 p-2 rounded bg-ink-950 border border-ink-border text-xs {{ $member->voice_active ? 'border-emerald-500/40 bg-emerald-950/10' : '' }}">
                            <div class="relative shrink-0">
                                <img src="{{ $member->user?->avatar_url ?? asset('images/default-avatar.png') }}" class="w-7 h-7 rounded-full object-cover border border-amber-500/40" alt="{{ $member->user?->name }}">
                                @if($member->voice_active)
                                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border border-ink-950 flex items-center justify-center text-[8px]" title="Ovozli efirda">🎙️</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-semibold text-paper truncate block text-[11px]">{{ $member->user?->name }}</span>
                                    @if(auth()->check() && $member->user_id === auth()->id())
                                        <span class="text-[9px] px-1 rounded bg-amber-500/20 text-amber-400 font-bold font-mono shrink-0">Siz</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-mist">
                                    <span class="truncate">{{ '@' . ($member->user?->username ?? 'user') }}</span>
                                    <span class="text-amber-400 font-bold shrink-0 ml-1">{{ $member->current_page }}-bet</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Spoilers-Protected Messages Thread with Audio Player -->
            <div id="buddy-chat-messages" 
                 x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
                 x-init="scrollToBottom()"
                 @buddy-chat-scroll.window="scrollToBottom()"
                 class="space-y-2.5 max-h-[350px] overflow-y-auto p-2 rounded-card bg-ink-950 border border-ink-border scroll-smooth">
                @forelse($activeRoom->messages as $msg)
                    @php
                        $myPage = $myMember?->current_page ?? 1;
                        $isAhead = $msg->page_number > $myPage;
                        $isRevealed = isset($revealedSpoilers[$msg->id]) || $msg->user_id === auth()->id();
                        $isVoice = !empty($msg->audio_path);
                        // Ovozli xabarlar faqat jo'natuvchi ularni ataylab "Spoyler" deb belgilasagina yashiriladi!
                        $hideSpoiler = ($msg->is_spoiler || (!$isVoice && $isAhead)) && !$isRevealed;
                    @endphp

                    <div class="p-2.5 rounded-card bg-ink-900/70 border border-ink-border space-y-1.5" wire:key="buddy-msg-{{ $msg->id }}">
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-paper">{{ $msg->user?->name }}</span>
                                @if(auth()->check() && $msg->user_id === auth()->id())
                                    <span class="text-[9px] px-1 rounded bg-amber-500/20 text-amber-400 font-bold font-mono">Siz</span>
                                @endif
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-400 text-[10px]">{{ $msg->page_number }}-bet</span>
                            </div>
                            <span class="text-mist text-[10px]">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>

                        @if($hideSpoiler)
                            <div class="p-3 rounded bg-amber-950/30 border border-amber-500/40 text-center space-y-1.5">
                                <div class="text-xs text-amber-400 font-bold flex items-center justify-center gap-1.5">
                                    <span>⚠️</span>
                                    <span>Spoyler himoyasi (Ushbu fikr {{ $msg->page_number }}-betga oid)</span>
                                </div>
                                <p class="text-[11px] text-mist">Siz hozir {{ $myPage }}-betdasiz. Syujetni oldindan bilib olmasligingiz uchun xabar yashirildi.</p>
                                <button type="button" wire:click="revealSpoiler({{ $msg->id }})" class="px-2.5 py-1 rounded bg-amber-500 text-slate-950 font-bold text-[11px] hover:bg-amber-400 transition-colors">
                                    Baribir o'qish (Ochish)
                                </button>
                            </div>
                        @elseif($msg->audio_path)
                            <!-- 🎙️ Ovozli xabar pleyeri -->
                            <div class="p-2.5 rounded-xl bg-ink-950 border border-ink-border/80 flex items-center gap-3"
                                 wire:key="buddy-audio-bubble-{{ $msg->id }}"
                                 x-data="voicePlayer('{{ $msg->audio_url }}', {{ (int) ($msg->audio_duration ?: 0) }})">
                                <button type="button" @click="toggle()" 
                                        onclick="if(!this.closest('[x-data]')?._x_dataStack && window.fallbackVoiceToggle){ window.fallbackVoiceToggle(this); }"
                                        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors bg-amber-500 text-ink-950 hover:bg-amber-400">
                                    <svg x-show="!playing" class="w-3.5 h-3.5 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg x-show="playing" x-cloak class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-0.5 h-5 cursor-pointer" @click="seek($event)">
                                        @foreach([35, 65, 45, 90, 75, 40, 85, 100, 70, 50, 85, 95, 60, 45, 75, 55, 80, 40] as $wIdx => $wH)
                                            <span class="flex-1 rounded-full transition-all" style="height: {{ $wH }}%;" :class="({{ $wIdx }} / 18 * 100) <= progress ? 'bg-amber-400' : 'bg-ink-700'"></span>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-mono text-mist mt-0.5">
                                        <span x-text="playing ? formatTime(currentTime) : 'Ovozli xabar'">Ovozli xabar</span>
                                        <span x-text="formatTime(duration)">{{ sprintf('%02d:%02d', floor(($msg->audio_duration ?? 0)/60), ($msg->audio_duration ?? 0)%60) }}</span>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($msg->message))
                                <p class="text-xs text-paper font-sans leading-relaxed mt-1">{{ $msg->message }}</p>
                            @endif
                        @else
                            <p class="text-xs text-paper font-sans leading-relaxed">{{ $msg->message }}</p>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-mist font-mono">
                        Hozircha muhokamalar yo'q. Birinchi bo'lib fikr yoki ovozli xabar qoldiring! 📖🎙️
                    </div>
                @endforelse
            </div>

            <!-- ✍️ Send Message & 🎙️ Voice Note Recording Box -->
            @if($myMember)
                <div x-data="{
                    isRecording: false,
                    isUploading: false,
                    isPreparing: false,
                    recordDuration: 0,
                    mediaRecorder: null,
                    mediaStream: null,
                    audioChunks: [],
                    timerInterval: null,
                    discard: false,

                    startRecord() {
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            alert('Brauzeringiz audio yozishni qo\'llab-quvvatlamaydi yoki xavfsiz (HTTPS) ulanish kerak.');
                            return;
                        }
                        this.isPreparing = true;
                        navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                            this.isPreparing = false;
                            this.mediaStream = stream;
                            this.audioChunks = [];
                            this.discard = false;

                            let options = {};
                            if (typeof MediaRecorder !== 'undefined' && typeof MediaRecorder.isTypeSupported === 'function') {
                                if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                                    options.mimeType = 'audio/webm;codecs=opus';
                                } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                                    options.mimeType = 'audio/webm';
                                } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                                    options.mimeType = 'audio/mp4';
                                }
                            }

                            try {
                                this.mediaRecorder = options.mimeType ? new MediaRecorder(stream, options) : new MediaRecorder(stream);
                            } catch(e) {
                                this.mediaRecorder = new MediaRecorder(stream);
                            }

                            this.mediaRecorder.ondataavailable = e => {
                                if (e.data && e.data.size > 0) {
                                    this.audioChunks.push(e.data);
                                }
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
                            if (this.timerInterval) clearInterval(this.timerInterval);
                            this.timerInterval = setInterval(() => {
                                this.recordDuration++;
                                if (this.recordDuration >= 300) {
                                    this.sendRecord();
                                }
                            }, 1000);
                        }).catch(err => {
                            this.isPreparing = false;
                            this.isRecording = false;
                            console.error('Mikrofon xatosi:', err);
                            const msg = (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')
                                ? 'Mikrofon taqiqlangan! Brauzeringiz sozlamalaridan ushbu sayt uchun mikrofonga ruxsat bering.'
                                : ('Mikrofon ulanmadi: ' + (err.message || err.name));
                            alert(msg);
                        });
                    },

                    cancelRecord() {
                        this.discard = true;
                        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                            try { this.mediaRecorder.stop(); } catch(e) {}
                        }
                        this.isRecording = false;
                        this.isPreparing = false;
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.cleanupStream();
                    },

                    sendRecord() {
                        this.discard = false;
                        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                            try { this.mediaRecorder.stop(); } catch(e) {}
                        }
                        this.isRecording = false;
                        this.isPreparing = false;
                        if (this.timerInterval) clearInterval(this.timerInterval);
                    },

                    cleanupStream() {
                        if (this.mediaStream) {
                            try {
                                this.mediaStream.getTracks().forEach(t => t.stop());
                            } catch(e) {}
                            this.mediaStream = null;
                        }
                        if (this.timerInterval) clearInterval(this.timerInterval);
                    },

                    uploadAndSend(blob, dur) {
                        this.isUploading = true;
                        const formData = new FormData();
                        const ext = blob.type && blob.type.includes('mp4') ? 'm4a' : 'webm';
                        formData.append('audio', blob, 'voice_' + Date.now() + '.' + ext);
                        formData.append('duration', dur);
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch('{{ route('chat.voice.upload') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.isUploading = false;
                            if (data.success && data.path) {
                                $wire.sendVoiceNote(data.path, data.duration || dur);
                            } else {
                                alert(data.error || 'Ovozli xabar yuklanmadi');
                            }
                        })
                        .catch(err => {
                            this.isUploading = false;
                            console.error('Upload error:', err);
                            alert('Ovozni yuborishda xatolik: ' + err.message);
                        });
                    },

                    formatDuration(sec) {
                        const s = Math.max(0, parseInt(sec) || 0);
                        const m = Math.floor(s / 60);
                        const rem = s % 60;
                        return (m < 10 ? '0' : '') + m + ':' + (rem < 10 ? '0' : '') + rem;
                    }
                }" class="p-3 rounded-card bg-ink-900 border border-ink-border space-y-2">
                    <!-- Standard Input Row -->
                    <div x-show="!isRecording && !isUploading" class="flex items-center gap-2">
                        <input type="text" wire:model.defer="newMessage" 
                               @keydown.enter.prevent="$wire.sendMessage()"
                               placeholder="Fikr yoki izoh yozing..." class="ks-input text-xs flex-1">
                        
                        <div class="flex items-center gap-1 bg-ink-950 border border-ink-border rounded-btn px-2 py-1 shrink-0" title="Ushbu fikr tegishli bo'lgan sahifa raqami">
                            <span class="text-[11px] text-mist font-mono">Bet:</span>
                            <input type="number" wire:model.defer="messagePage" min="1" class="w-12 text-center text-xs font-mono font-bold bg-transparent text-amber-400 outline-none">
                        </div>

                        <!-- 🎙️ Mikrofon tugmasi -->
                        <button type="button" @click="startRecord()" 
                                :disabled="isPreparing"
                                :title="isPreparing ? 'Mikrofon yoqilmoqda...' : 'Ovozli xabar yozish (Mikrofon)'"
                                class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400 text-amber-400 hover:text-amber-300 flex items-center justify-center shrink-0 transition-all"
                                :class="{ 'opacity-60 cursor-wait border-rose-500 text-rose-400': isPreparing }">
                            <template x-if="isPreparing">
                                <svg class="animate-spin w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isPreparing">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                    <line x1="12" y1="19" x2="12" y2="22"/>
                                </svg>
                            </template>
                        </button>

                        <button type="button" wire:click="sendMessage" class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">
                            Yuborish
                        </button>
                    </div>

                    <!-- 🔴 Ovoz yozish paneli (Recording in progress) -->
                    <div x-show="isRecording" x-cloak class="flex items-center justify-between gap-3 bg-ink-950 border border-rose-500/40 p-2.5 rounded-btn">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                            </span>
                            <span class="text-xs font-mono text-rose-400 font-bold" x-text="'Ovoz yozilmoqda: ' + formatDuration(recordDuration)"></span>
                            <div class="flex items-center gap-0.5 h-3">
                                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-2"></span>
                                <span class="w-1 bg-rose-400 rounded-full animate-pulse h-3.5" style="animation-delay: 0.15s"></span>
                                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-2.5" style="animation-delay: 0.3s"></span>
                                <span class="w-1 bg-rose-400 rounded-full animate-pulse h-3.5" style="animation-delay: 0.45s"></span>
                                <span class="w-1 bg-rose-500 rounded-full animate-pulse h-2" style="animation-delay: 0.6s"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="cancelRecord()" class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-mist text-xs hover:text-rose-400 transition-colors">
                                Bekor qilish
                            </button>
                            <button type="button" @click="sendRecord()" class="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-400 text-ink-950 font-bold text-xs flex items-center gap-1 shadow-sm transition-colors">
                                <span>Yuborish</span>
                                <span>✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- ⏳ Ovoz yuklanmoqda paneli (Uploading) -->
                    <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 bg-ink-950 border border-amber-500/40 p-2.5 rounded-btn text-xs font-mono text-amber-400">
                        <svg class="animate-spin w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Ovozli xabar saqlanmoqda...</span>
                    </div>

                    <label class="flex items-center gap-2 text-[11px] text-mist font-mono cursor-pointer">
                        <input type="checkbox" wire:model.defer="isSpoiler" class="rounded bg-ink-950 border-ink-border text-amber-500">
                        <span>Spoyler deb belgilash (Syujet siri saqlanadi)</span>
                    </label>
                </div>
            @elseif(auth()->check())
                <div class="text-center p-3 rounded bg-ink-900 border border-ink-border text-xs text-mist font-mono">
                    Muhokamada qatnashish va ovozli gaplashish uchun xonaga qo'shiling.
                </div>
            @endif
        </div>
    @else
        <!-- Rooms List & Create New Room -->
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-ink-border flex-wrap gap-2">
                <div>
                    <h4 class="text-sm font-bold text-paper font-serif">«{{ $book->title }}» bo'yicha Birgalikda Mutolaa</h4>
                    <p class="text-xs text-mist font-mono">Do'stlaringiz bilan birga o'qing, ovozli suhbatlashing va fikr almashing</p>
                </div>
                @if($book->pdf_path)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('books.flipbook', $book->slug ?? $book->id) }}" class="px-3 py-1.5 rounded-btn bg-amber-500 text-slate-950 font-bold text-xs flex items-center gap-1 shadow-sm">
                            <span>📖</span>
                            <span>Mutolaaga o'tish</span>
                        </a>
                        <a href="{{ route('books.pdf', $book) }}" class="px-2.5 py-1.5 rounded-btn bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-mono font-bold flex items-center gap-1">
                            <span>📥 PDF</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Create New Buddy Read Form -->
            @auth
                <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border space-y-2.5">
                    <span class="text-xs font-bold text-amber-400 font-mono block">+ Yangi mutolaa xonasi ochish</span>
                    <form wire:submit.prevent="createBuddyRead" class="flex gap-2">
                        <input type="text" wire:model.defer="newTitle" placeholder="Masalan: 3-kurs talabalari / Toshkent kitobxonlari" class="ks-input text-xs flex-1">
                        <button type="submit" class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">
                            Ochish
                        </button>
                    </form>
                    @error('newTitle') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                </div>
            @else
                <div class="p-3 rounded bg-ink-900 border border-ink-border text-xs text-center text-mist font-mono">
                    Birgalikda mutolaa xonasi ochish uchun <a href="{{ route('login') }}" class="text-amber-400 underline">tizimga kiring</a>.
                </div>
            @endauth

            <!-- Existing Rooms -->
            <div class="space-y-2">
                <span class="text-[11px] font-mono text-mist uppercase tracking-wider block">Mavjud guruhlar:</span>
                @forelse($allRooms as $room)
                    <div class="p-3 rounded-card bg-ink-900/60 border border-ink-border flex items-center justify-between hover:bg-ink-800/40 transition-colors">
                        <div>
                            <h5 class="text-xs font-bold text-paper">{{ $room->title }}</h5>
                            <span class="text-[11px] text-mist font-mono">Muallif: {{ $room->creator?->name }} • {{ $room->members_count }}/{{ $room->max_members }} a'zo</span>
                        </div>
                        <button type="button" wire:click="selectBuddyRead({{ $room->id }})" class="px-3 py-1 rounded bg-ink-800 hover:bg-ink-700 text-amber-400 text-xs font-mono font-bold border border-ink-border transition-colors">
                            Kirish →
                        </button>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-mist font-mono p-4 rounded bg-ink-900/40 border border-ink-border">
                        Hozircha birgalikda mutolaa xonalari yo'q. Birinchi xonani siz oching!
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

<!-- 🎙️ WebRTC Live Voice Room & Voice Player Scripts -->
<script>
    function buddyLiveVoiceRoom(config) {
        return {
            roomId: config.roomId,
            userId: config.userId,
            voiceActive: config.initialActive || false,
            isConnecting: false,
            isMuted: false,
            mediaStream: null,
            audioContext: null,
            analyser: null,
            micLevel: 0,
            animFrame: null,
            pollInterval: null,
            peers: {},
            connectedPeersCount: 0,

            async toggleVoice() {
                if (!this.voiceActive) {
                    await this.startVoice();
                } else {
                    await this.stopVoice();
                }
            },

            async startVoice() {
                this.isConnecting = true;
                try {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        throw new Error('Brauzeringiz mikrofondan foydalanishni qo\'llab-quvvatlamaydi yoki xavfsiz (HTTPS) ulanish kerak.');
                    }

                    this.mediaStream = await navigator.mediaDevices.getUserMedia({
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true
                        }
                    });

                    // Setup local analyzer for visualizer
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (AudioCtx) {
                        this.audioContext = new AudioCtx();
                        this.analyser = this.audioContext.createAnalyser();
                        const source = this.audioContext.createMediaStreamSource(this.mediaStream);
                        source.connect(this.analyser);
                        this.analyser.fftSize = 64;
                        const dataArray = new Uint8Array(this.analyser.frequencyBinCount);

                        const updateLevel = () => {
                            if (!this.voiceActive || !this.analyser) return;
                            this.analyser.getByteFrequencyData(dataArray);
                            let sum = 0;
                            for (let i = 0; i < dataArray.length; i++) sum += dataArray[i];
                            this.micLevel = this.isMuted ? 0 : Math.min(100, Math.round((sum / dataArray.length) * 1.6));
                            this.animFrame = requestAnimationFrame(updateLevel);
                        };
                        updateLevel();
                    }

                    this.voiceActive = true;
                    this.isConnecting = false;
                    config.wire.toggleVoiceActive(true);

                    // Broadcast join signal
                    this.sendSignal('join', JSON.stringify({ userId: this.userId }));

                    // Start polling signals every 1.5 seconds
                    if (this.pollInterval) clearInterval(this.pollInterval);
                    this.pollInterval = setInterval(() => this.pollSignals(), 1500);
                    this.pollSignals();

                } catch (err) {
                    this.isConnecting = false;
                    console.error('Mikrofon xatosi:', err);
                    const msg = (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')
                        ? 'Mikrofonga ruxsat berilmadi! Brauzeringiz URL qatoridagi qulf belgisini bosib, mikrofonga ruxsat bering.'
                        : ('Mikrofon ulanmadi: ' + (err.message || err.name));
                    alert(msg);
                }
            },

            async stopVoice() {
                this.voiceActive = false;
                config.wire.toggleVoiceActive(false);

                // Broadcast leave signal
                this.sendSignal('leave', JSON.stringify({ userId: this.userId }));

                // Close all WebRTC peers
                Object.keys(this.peers).forEach(peerId => {
                    try { this.peers[peerId].close(); } catch(e) {}
                    const el = document.getElementById('peer-audio-' + peerId);
                    if (el) el.remove();
                });
                this.peers = {};
                this.connectedPeersCount = 0;

                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    this.pollInterval = null;
                }

                if (this.mediaStream) {
                    try { this.mediaStream.getTracks().forEach(t => t.stop()); } catch(e) {}
                    this.mediaStream = null;
                }

                if (this.audioContext) {
                    try { this.audioContext.close(); } catch(e) {}
                    this.audioContext = null;
                }

                if (this.animFrame) cancelAnimationFrame(this.animFrame);
                this.micLevel = 0;
            },

            toggleMute() {
                if (!this.mediaStream) return;
                this.isMuted = !this.isMuted;
                this.mediaStream.getAudioTracks().forEach(t => {
                    t.enabled = !this.isMuted;
                });
            },

            createPeerConnection(peerId) {
                if (this.peers[peerId]) return this.peers[peerId];

                const pc = new RTCPeerConnection({
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                        { urls: 'stun:stun2.l.google.com:19302' }
                    ]
                });

                if (this.mediaStream) {
                    this.mediaStream.getTracks().forEach(track => {
                        pc.addTrack(track, this.mediaStream);
                    });
                }

                pc.onicecandidate = (event) => {
                    if (event.candidate) {
                        this.sendSignal('candidate', JSON.stringify(event.candidate), peerId);
                    }
                };

                pc.onconnectionstatechange = () => {
                    if (pc.connectionState === 'connected') {
                        this.connectedPeersCount = Object.values(this.peers).filter(p => p.connectionState === 'connected').length;
                    } else if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                        this.connectedPeersCount = Object.values(this.peers).filter(p => p.connectionState === 'connected').length;
                    }
                };

                // Incoming remote audio stream
                pc.ontrack = (event) => {
                    let audio = document.getElementById('peer-audio-' + peerId);
                    if (!audio) {
                        audio = document.createElement('audio');
                        audio.id = 'peer-audio-' + peerId;
                        audio.autoplay = true;
                        audio.playsInline = true;
                        document.body.appendChild(audio);
                    }
                    audio.srcObject = event.streams[0];
                    audio.play().catch(e => console.warn('Autoplay error:', e));
                };

                this.peers[peerId] = pc;
                return pc;
            },

            async initiateCall(peerId) {
                try {
                    const pc = this.createPeerConnection(peerId);
                    const offer = await pc.createOffer();
                    await pc.setLocalDescription(offer);
                    this.sendSignal('offer', JSON.stringify(offer), peerId);
                } catch (err) {
                    console.error('Call initiation error:', err);
                }
            },

            async handleOffer(fromUserId, sdpString) {
                try {
                    const pc = this.createPeerConnection(fromUserId);
                    const desc = new RTCSessionDescription(JSON.parse(sdpString));
                    await pc.setRemoteDescription(desc);
                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    this.sendSignal('answer', JSON.stringify(answer), fromUserId);
                } catch (err) {
                    console.error('Offer handling error:', err);
                }
            },

            async handleAnswer(fromUserId, sdpString) {
                try {
                    const pc = this.peers[fromUserId];
                    if (pc) {
                        const desc = new RTCSessionDescription(JSON.parse(sdpString));
                        await pc.setRemoteDescription(desc);
                    }
                } catch (err) {
                    console.error('Answer handling error:', err);
                }
            },

            async handleCandidate(fromUserId, candidateString) {
                try {
                    const pc = this.peers[fromUserId];
                    if (pc && pc.remoteDescription) {
                        const candidate = new RTCIceCandidate(JSON.parse(candidateString));
                        await pc.addIceCandidate(candidate);
                    }
                } catch (err) {
                    console.error('Candidate handling error:', err);
                }
            },

            sendSignal(type, payload, toUserId = null) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('/buddy-read/' + this.roomId + '/signal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: type,
                        payload: payload,
                        to_user_id: toUserId
                    })
                }).catch(e => console.error('Signal sending error:', e));
            },

            pollSignals() {
                if (!this.voiceActive) return;
                fetch('/buddy-read/' + this.roomId + '/signals', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(async data => {
                    if (data.activeMembers && Array.isArray(data.activeMembers)) {
                        data.activeMembers.forEach(peerId => {
                            if (peerId !== this.userId && !this.peers[peerId] && this.userId < peerId) {
                                this.initiateCall(peerId);
                            }
                        });
                    }

                    if (data.signals && Array.isArray(data.signals)) {
                        for (const sig of data.signals) {
                            const fromId = sig.from_user_id;
                            if (sig.type === 'offer') {
                                await this.handleOffer(fromId, sig.payload);
                            } else if (sig.type === 'answer') {
                                await this.handleAnswer(fromId, sig.payload);
                            } else if (sig.type === 'candidate') {
                                await this.handleCandidate(fromId, sig.payload);
                            } else if (sig.type === 'join') {
                                if (fromId !== this.userId && this.userId < fromId) {
                                    this.initiateCall(fromId);
                                }
                            } else if (sig.type === 'leave') {
                                if (this.peers[fromId]) {
                                    try { this.peers[fromId].close(); } catch(e) {}
                                    delete this.peers[fromId];
                                    const el = document.getElementById('peer-audio-' + fromId);
                                    if (el) el.remove();
                                    this.connectedPeersCount = Object.values(this.peers).filter(p => p.connectionState === 'connected').length;
                                }
                            }
                        }
                    }
                })
                .catch(e => console.error('Signal polling error:', e));
            }
        };
    }

    if (typeof window.voicePlayer === 'undefined') {
        window.voicePlayer = function(audioUrl, initialDuration) {
            return {
                playing: false,
                progress: 0,
                duration: initialDuration || 0,
                currentTime: 0,
                audio: null,
                init() {
                    this.setupAudio();
                },
                setupAudio() {
                    if (this.audio) return;
                    this.audio = new Audio(audioUrl);
                    this.audio.preload = 'metadata';
                    this.audio.addEventListener('loadedmetadata', () => {
                        if (!this.duration || isNaN(this.duration) || this.duration === 0) {
                            this.duration = Math.round(this.audio.duration) || 0;
                        }
                    });
                    this.audio.addEventListener('timeupdate', () => {
                        this.currentTime = Math.round(this.audio.currentTime);
                        if (this.duration > 0) {
                            this.progress = Math.min(100, (this.audio.currentTime / this.duration) * 100);
                        }
                    });
                    this.audio.addEventListener('ended', () => {
                        this.playing = false;
                        this.progress = 0;
                        this.currentTime = 0;
                    });
                    this.audio.addEventListener('pause', () => { this.playing = false; });
                    this.audio.addEventListener('play', () => { this.playing = true; });
                },
                toggle() {
                    this.setupAudio();
                    if (this.playing) {
                        this.audio.pause();
                        this.playing = false;
                    } else {
                        document.querySelectorAll('audio').forEach(a => {
                            if (a !== this.audio) { try { a.pause(); } catch(e) {} }
                        });
                        const p = this.audio.play();
                        if (p !== undefined) {
                            p.then(() => { this.playing = true; }).catch(() => { this.playing = false; });
                        }
                    }
                },
                seek(event) {
                    this.setupAudio();
                    const el = event.currentTarget;
                    const r = el.getBoundingClientRect();
                    const p = Math.max(0, Math.min(1, (event.clientX - r.left) / r.width));
                    if (this.duration > 0) {
                        this.audio.currentTime = p * this.duration;
                        this.progress = p * 100;
                    }
                },
                formatTime(sec) {
                    const s = Math.max(0, sec || 0);
                    const m = Math.floor(s / 60);
                    const rem = Math.floor(s % 60);
                    return (m < 10 ? '0' : '') + m + ':' + (rem < 10 ? '0' : '') + rem;
                }
            };
        };
    }
</script>
