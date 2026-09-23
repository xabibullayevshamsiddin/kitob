<div class="max-w-7xl mx-auto space-y-6 pb-16" 
    x-data="liveStudioController({
        isHost: @js($isHost),
        eventId: {{ $event->id }},
        startedAt: {{ $event->started_at ? $event->started_at->timestamp : ($event->created_at ? $event->created_at->timestamp : now()->timestamp) }},
        serverNow: {{ now()->timestamp }},
        permissionMode: @js($event->permission_mode)
    })" 
    x-init="initStudio()">

    <!-- ── 1. TOP HEADER & STATUS BAR ── -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-3xl shadow-soft">
        <div class="flex items-center gap-3">
            <a href="{{ route('live.index') }}" class="p-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors" title="Orqaga">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white font-manrope truncate max-w-xl">{{ $event->title }}</h1>
                    @if($event->is_live)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold uppercase tracking-wider animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Efirda</span>
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 text-xs font-bold uppercase">
                            {{ ucfirst($event->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-0.5 flex flex-wrap items-center gap-2">
                    @if($event->hostUser)
                        <span>Ustoz: <strong class="text-slate-700 dark:text-slate-200">{{ $event->hostUser->name }}</strong></span>
                    @endif
                    @if($event->book)
                        <span>• 📖 {{ $event->book->title }}</span>
                    @endif
                    <span>• {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i') }}</span>
                </p>
            </div>
        </div>

        <!-- Right: Current Permission Pill & Host Quick End -->
        <div class="flex items-center gap-3 shrink-0">
            <!-- Permission indicator badge -->
            <div class="px-3.5 py-2 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center gap-2">
                @if($event->permission_mode === 'chat_only')
                    <span>💬 Faqat Chat</span>
                @elseif($event->permission_mode === 'voice_only')
                    <span>🎙️ Faqat Ovoz</span>
                @elseif($event->permission_mode === 'view_only')
                    <span>🔒 Faqat Ma'ruza (Tinglash)</span>
                @else
                    <span>✨ Chat & Ovoz Ochiq</span>
                @endif
            </div>

            @if($isHost)
                @if($event->status === 'live')
                    <button wire:click="endLiveStream" wire:confirm="Rostdan ham efirni yakunlamoqchimisiz?"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                        ⏹️ Efirni Yakunlash
                    </button>
                @else
                    <button wire:click="restartLiveStream"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                        ▶ Efirni Qayta Boshlash
                    </button>
                @endif
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2 animate-fade-in">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold flex items-center gap-2 animate-fade-in">
            <span>⚠</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ── 2. TWO-COLUMN STUDIO GRID ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ════ LEFT: BROADCAST VIDEO & HOST CONSOLE (8 Cols) ════ -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-5">

            <!-- Video Screen Viewport -->
            <div class="relative w-full aspect-video rounded-3xl bg-slate-950 border border-slate-800 shadow-2xl overflow-hidden flex items-center justify-center group">
                
                <!-- Live Video Element (Webcam / Stream) -->
                <video id="liveVideoPlayer" autoplay playsinline class="w-full h-full object-cover transition-opacity duration-300"
                       :class="{ 'opacity-0': !isVideoOn, 'opacity-100': isVideoOn }">
                </video>

                <!-- Avatar Backdrop when Camera is Off or audio-only -->
                <div x-show="!isVideoOn" class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 p-6 text-center space-y-4">
                    <div class="relative">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-rose-500 to-amber-500 p-1 shadow-2xl">
                            <img src="{{ $event->hostUser?->avatar_url ?? 'https://ui-avatars.com/api/?name=Kitobxon&background=4f46e5&color=fff' }}" 
                                class="w-full h-full rounded-[22px] object-cover">
                        </div>
                        <!-- Audio Wave Ping if mic is on -->
                        <div x-show="isMicOn" class="absolute -inset-2 rounded-3xl border-2 border-rose-500/40 animate-ping pointer-events-none"></div>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-white">{{ $event->hostUser?->name ?? 'Ustoz' }}</h3>
                        <p class="text-xs text-slate-400 font-mono">
                            <span x-text="isMicOn ? '🎙️ Ovoz uzatilmoqda (Kamera o\'chiq)' : '🔇 Mikrofon va kamera o\'chiq'"></span>
                        </p>
                    </div>
                </div>

                <!-- Viewer Connecting / Waiting for Host Overlay -->
                <div x-show="!isHost && !hasRemoteStream" class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-slate-950/90 backdrop-blur-sm p-6 text-center space-y-3">
                    <div class="relative flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full border-4 border-rose-500/20 border-t-rose-500 animate-spin"></div>
                        <span class="absolute text-lg">📡</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Ustoz jonli efiriga ulanmoqda...</h4>
                        <p class="text-xs text-slate-400 mt-1">Jonli video va audio oqim sozlanmoqda</p>
                    </div>
                </div>

                <!-- Viewer Unmute prompt banner (when browser policy requires user gesture for sound) -->
                <div x-show="!isHost && needsUnmute" class="absolute inset-0 z-40 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4">
                    <button @click="unmuteAudio()" class="px-6 py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm shadow-2xl flex items-center gap-2.5 transform hover:scale-105 active:scale-95 transition-all">
                        <span class="text-lg">🔊</span>
                        <span>Ovozni yoqish (Tinglash uchun bosing)</span>
                    </button>
                </div>

                <!-- Top Left Overlays (LIVE badge + Timer) -->
                <div class="absolute top-4 left-4 flex items-center gap-2 z-20">
                    <div class="px-3 py-1 rounded-full bg-rose-600/90 text-white font-mono text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-lg backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                        <span>LIVE</span>
                    </div>
                    <div class="px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-white font-mono text-xs font-semibold" x-text="formattedDuration">
                        00:00:00
                    </div>
                </div>

                <!-- Top Right Recording Overlay -->
                <div x-show="isRecording" class="absolute top-4 right-4 z-20 animate-pulse">
                    <div class="px-3 py-1 rounded-full bg-rose-500/90 text-white font-mono text-xs font-bold flex items-center gap-2 shadow-lg backdrop-blur-md">
                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                        <span>REC <span x-text="formattedRecordDuration">00:00</span></span>
                    </div>
                </div>

                <!-- Bottom Left Audio VU Meter (Mikrofon indikatori) -->
                <div class="absolute bottom-4 left-4 z-20 flex items-center gap-2 bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-2xl border border-white/10">
                    <span class="text-xs" x-text="isMicOn ? '🎙️' : '🔇'"></span>
                    <div class="flex items-center gap-1 h-3">
                        <div class="w-1 bg-emerald-500 rounded-full transition-all duration-75" :style="{ height: Math.min(100, Math.max(20, audioVolume * 1.5)) + '%' }"></div>
                        <div class="w-1 bg-emerald-400 rounded-full transition-all duration-75" :style="{ height: Math.min(100, Math.max(20, audioVolume * 2)) + '%' }"></div>
                        <div class="w-1 bg-amber-400 rounded-full transition-all duration-75" :style="{ height: Math.min(100, Math.max(20, audioVolume * 2.5)) + '%' }"></div>
                        <div class="w-1 bg-rose-500 rounded-full transition-all duration-75" :style="{ height: Math.min(100, Math.max(20, audioVolume * 3)) + '%' }"></div>
                    </div>
                </div>

                <!-- Bottom Right Watermark -->
                <div class="absolute bottom-4 right-4 z-20 hidden sm:flex items-center gap-2 bg-black/50 backdrop-blur-md px-3 py-1 rounded-xl text-[11px] text-slate-300 font-mono">
                    <span>Kitobxon Live Studio</span>
                </div>
            </div>

            <!-- ── HOST TOOLBAR (Ustoz / Admin Uskunalari) ── -->
            @if($isHost)
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">🎛️ Ustoz Studiya Boshqaruvi</span>
                            <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-500 font-bold">Broadcaster</span>
                        </div>

                        <!-- Hardware Settings Toggle button -->
                        <button @click="showDeviceSettings = !showDeviceSettings"
                            class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-1.5 transition-colors">
                            <span>⚙️ Qurilmalar sozlamasi</span>
                            <span x-text="showDeviceSettings ? '▲' : '▼'"></span>
                        </button>
                    </div>

                    <!-- Action Buttons Toolbar -->
                    <div class="flex flex-wrap items-center gap-2.5 pt-1">
                        <!-- Mic toggle -->
                        <button @click="toggleMic()"
                            class="px-4 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 transition-all active:scale-95 shadow-sm"
                            :class="isMicOn ? 'bg-indigo-600 hover:bg-indigo-500 text-white' : 'bg-rose-500/10 border border-rose-500/30 text-rose-500 hover:bg-rose-500/20'">
                            <span x-text="isMicOn ? '🎙️ Mikrafon Yoqilgan' : '🔇 Mikrafon O\'chiq'"></span>
                        </button>

                        <!-- Camera toggle -->
                        <button @click="toggleVideo()"
                            class="px-4 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 transition-all active:scale-95 shadow-sm"
                            :class="isVideoOn ? 'bg-indigo-600 hover:bg-indigo-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'">
                            <span x-text="isVideoOn ? '📹 Kamera Yoqilgan' : '📷 Kamera O\'chiq'"></span>
                        </button>

                        <!-- Screen share -->
                        <button @click="toggleScreenShare()"
                            class="px-4 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 transition-all active:scale-95 shadow-sm"
                            :class="isScreenSharing ? 'bg-amber-500 text-ink-950 font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'">
                            <span x-text="isScreenSharing ? '🖥️ Ekranni To\'xtatish' : '🖥️ Ekran Ulashish'"></span>
                        </button>

                        <!-- Video Recording (Zapis) -->
                        <button @click="toggleRecording()"
                            class="px-4 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 transition-all active:scale-95 shadow-sm"
                            :class="isRecording ? 'bg-rose-600 text-white animate-pulse' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'">
                            <span x-text="isRecording ? '⏹️ Yozuvni To\'xtatish & Saqlash' : '🔴 Video Yozib Olish (Zapis)'"></span>
                        </button>
                    </div>

                    <!-- ── KOMPYUTER MIKRAFON VA KAMERA SOZLAMALARI (Collapsible) ── -->
                    <div x-show="showDeviceSettings" x-collapse x-cloak class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Microphone Selector (enumerateDevices) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                                    <span>🎤</span>
                                    <span>Mikrafon Tanlash (Kompyuterdagi)</span>
                                </label>
                                <select id="audioSourceSelect" @change="changeAudioSource($event.target.value)"
                                    class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                                    <template x-for="device in audioDevices" :key="device.deviceId">
                                        <option :value="device.deviceId" x-text="device.label || `Mikrafon ${$index + 1}`" :selected="device.deviceId === selectedAudioDevice"></option>
                                    </template>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">Kompyuteringizga ulangan barcha tashqi va ichki mikrofonlar ro'yxati</p>
                            </div>

                            <!-- Camera Selector (enumerateDevices) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                                    <span>📹</span>
                                    <span>Kamera Tanlash</span>
                                </label>
                                <select id="videoSourceSelect" @change="changeVideoSource($event.target.value)"
                                    class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                                    <template x-for="device in videoDevices" :key="device.deviceId">
                                        <option :value="device.deviceId" x-text="device.label || `Kamera ${$index + 1}`" :selected="device.deviceId === selectedVideoDevice"></option>
                                    </template>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">Vebkamera yoki tashqi ulangan kamera qurilmasi</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-700 text-xs">
                            <span class="text-slate-400">Qurilmalar ko'rinmayotgan bo'lsa, brauzer ruxsatini tekshiring</span>
                            <button @click="scanMediaDevices()" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                🔄 Qurilmalarni qayta skanerlash
                            </button>
                        </div>
                    </div>

                    <!-- ── AUDIENCE PERMISSION SWITCHER (Real-time) ── -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            ⚙️ Tashrif buyuruvchilar huquqi (Hozirgi sozlama):
                        </label>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <!-- Both -->
                            <button wire:click="updatePermissionMode('both')"
                                class="p-2.5 rounded-xl border text-left text-xs transition-all {{ $event->permission_mode === 'both' ? 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <span class="block">✨ Ikkalasi ham</span>
                                <span class="text-[10px] opacity-75 font-normal block">Chat va Ovoz</span>
                            </button>

                            <!-- Chat only -->
                            <button wire:click="updatePermissionMode('chat_only')"
                                class="p-2.5 rounded-xl border text-left text-xs transition-all {{ $event->permission_mode === 'chat_only' ? 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <span class="block">💬 Faqat Chat</span>
                                <span class="text-[10px] opacity-75 font-normal block">Yozish mumkin</span>
                            </button>

                            <!-- Voice only -->
                            <button wire:click="updatePermissionMode('voice_only')"
                                class="p-2.5 rounded-xl border text-left text-xs transition-all {{ $event->permission_mode === 'voice_only' ? 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <span class="block">🎙️ Faqat Ovoz</span>
                                <span class="text-[10px] opacity-75 font-normal block">Mikrofon orqali</span>
                            </button>

                            <!-- View only -->
                            <button wire:click="updatePermissionMode('view_only')"
                                class="p-2.5 rounded-xl border text-left text-xs transition-all {{ $event->permission_mode === 'view_only' ? 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <span class="block">🔒 Faqat Ma'ruza</span>
                                <span class="text-[10px] opacity-75 font-normal block">Ikkalasi yopiq</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Event Details Note -->
            @if($event->description)
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Efir haqida</h3>
                    <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $event->description }}</p>
                </div>
            @endif

        </div>

        <!-- ════ RIGHT: REAL-TIME CHAT & QUESTIONS STREAM (4-5 Cols) ════ -->
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col h-[650px] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-soft overflow-hidden">
            
            <!-- Chat Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 dark:text-white">💬 Jonli Muloqot & Savollar</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>

                <!-- Voice Request Button (If voice allowed) -->
                @if(in_array($event->permission_mode, ['both', 'voice_only']) && !$isHost)
                    <button wire:click="requestVoiceSpeech"
                        class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-[11px] flex items-center gap-1 shadow-sm active:scale-95 transition-all">
                        <span>✋ Qo'l ko'tarish</span>
                    </button>
                @endif
            </div>

            <!-- Permission notice strip -->
            <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>
                    @if($event->permission_mode === 'chat_only')
                        💬 Faqat yozma chat faol
                    @elseif($event->permission_mode === 'voice_only')
                        🎙️ Faqat ovozli savollar qabul qilinadi
                    @elseif($event->permission_mode === 'view_only')
                        🔒 Ma'ruza rejimi (savol berish yopiq)
                    @else
                        ✨ Chat va mikrofon ruxsat etilgan
                    @endif
                </span>
                <span class="font-mono text-[10px] text-slate-400">{{ $allQuestions->count() }} ta xabar</span>
            </div>

            <!-- Messages Stream Area -->
            <div class="flex-1 p-4 overflow-y-auto space-y-3" id="liveChatScroll">
                @if($allQuestions->isEmpty())
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400 space-y-2">
                        <span class="text-3xl">💭</span>
                        <p class="text-xs">Hozircha savol yoki xabarlar yo'q. Birinchi bo'lib fikringizni bildiring!</p>
                    </div>
                @else
                    @foreach($allQuestions as $msg)
                        <div class="p-3 rounded-2xl {{ $msg->is_selected ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-slate-50 dark:bg-slate-800/50' }} text-xs space-y-1 group" wire:key="msg-{{ $msg->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $msg->user->avatar_url }}" class="w-5 h-5 rounded-full object-cover">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $msg->user->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $msg->created_at->format('H:i') }}</span>
                                </div>

                                <!-- Host Controls on Question -->
                                @if($isHost)
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="toggleSelectQuestion({{ $msg->id }})" title="Muhim savol sifatida belgilash"
                                            class="p-1 rounded text-amber-500 hover:bg-amber-500/20">
                                            {{ $msg->is_selected ? '★' : '☆' }}
                                        </button>
                                        <button wire:click="markQuestionAnswered({{ $msg->id }})" title="Javob berildi"
                                            class="p-1 rounded text-emerald-500 hover:bg-emerald-500/20">
                                            {{ $msg->is_answered ? '✓' : '○' }}
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed pl-7">{{ $msg->question }}</p>

                            @if($msg->is_answered)
                                <div class="pl-7">
                                    <span class="text-[10px] font-bold text-emerald-500">✓ Efirda javob berildi</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- ── BOTTOM INPUT CONTROLS ── -->
            <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
                @if($event->permission_mode === 'view_only' && !$isHost)
                    <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-center text-xs text-slate-400">
                        🔒 Ushbu efir faqat ma'ruza rejimida. Savol yozish cheklangan.
                    </div>
                @elseif($event->permission_mode === 'voice_only' && !$isHost)
                    <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-center space-y-2">
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">
                            🎙️ Ushbu efirda faqat ovozli savollar qabul qilinadi.
                        </p>
                        <button wire:click="requestVoiceSpeech"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                            ✋ Navbatga turish (Ovozli savol so'rash)
                        </button>
                    </div>
                @else
                    <!-- Chat Input Form -->
                    @auth
                        <form wire:submit.prevent="submitQuestion" class="flex items-center gap-2">
                            <input type="text" wire:model.defer="question" placeholder="Fikringiz yoki savolingiz..." maxlength="300"
                                class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <button type="submit"
                                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-95 shrink-0">
                                Yuborish
                            </button>
                        </form>
                    @else
                        <div class="text-center p-2">
                            <a href="{{ route('login') }}" class="text-xs font-bold text-rose-500 hover:underline">
                                Savol berish yoki chatda yozish uchun kiring →
                            </a>
                        </div>
                    @endauth
                @endif
            </div>

        </div>

    </div>

</div>

<!-- ── 3. WEBRTC / MEDIA STUDIO JAVASCRIPT CONTROLLER ── -->
<script>
function liveStudioController(config) {
    const isHost = Boolean(config.isHost);
    const eventId = Number(config.eventId);
    const startedAt = Number(config.startedAt || Math.floor(Date.now() / 1000));
    const serverNow = Number(config.serverNow || Math.floor(Date.now() / 1000));

    return {
        isHost: isHost,
        eventId: eventId,
        permissionMode: config.permissionMode || 'both',

        // Media states
        isMicOn: true,
        isVideoOn: isHost ? true : false,
        hasRemoteStream: false,
        isConnecting: !isHost,
        needsUnmute: false,
        isScreenSharing: false,
        isRecording: false,
        showDeviceSettings: false,

        // Hardware devices (host)
        audioDevices: [],
        videoDevices: [],
        selectedAudioDevice: '',
        selectedVideoDevice: '',

        // Media streams
        localStream: null,
        screenStream: null,
        mediaRecorder: null,
        recordedChunks: [],

        // Audio Analyser
        audioContext: null,
        analyser: null,
        audioVolume: 0,

        // Timers
        durationSeconds: 0,
        recordSeconds: 0,
        durationInterval: null,
        recordInterval: null,
        pollInterval: null,
        heartbeatInterval: null,

        // WebRTC Signaling
        myPeerId: isHost ? 'host' : ('viewer_' + Math.random().toString(36).substring(2, 9)),
        lastSignalId: 0,
        processedSignalKeys: new Set(),
        broadcastChannel: null,
        peers: {}, // Host: map of viewerId -> RTCPeerConnection
        peerConnection: null, // Viewer: RTCPeerConnection
        iceCandidateQueue: [],

        rtcConfig: {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        },

        async initStudio() {
            // 1. Duration Synchronizer (server-clock aligned)
            const clientNow = Math.floor(Date.now() / 1000);
            const clockSkew = clientNow - serverNow;
            const updateTimer = () => {
                const nowSec = Math.floor(Date.now() / 1000) - clockSkew;
                this.durationSeconds = Math.max(0, nowSec - startedAt);
            };
            updateTimer();
            this.durationInterval = setInterval(updateTimer, 1000);

            // 2. Setup WebRTC Signaling (BroadcastChannel + HTTP Polling fallback)
            this.setupSignaling();

            // 3. Role-specific startup
            if (this.isHost) {
                await this.scanMediaDevices();
                await this.startMediaStream();

                // Periodic heartbeat from Host to announce presence
                this.heartbeatInterval = setInterval(() => {
                    this.sendSignal('stream-status', 'all', {
                        isVideoOn: this.isVideoOn,
                        isMicOn: this.isMicOn,
                        isHostOnline: true
                    });
                }, 3000);
            } else {
                // Viewer startup: send join announcement to host
                this.sendSignal('join', 'host', { ts: Date.now() });

                // If remote stream hasn't arrived yet, ping host every 3 seconds
                this.heartbeatInterval = setInterval(() => {
                    if (!this.hasRemoteStream) {
                        this.sendSignal('join', 'host', { ts: Date.now() });
                    }
                }, 3000);
            }

            // Scroll chat to bottom
            this.scrollChat();
            window.addEventListener('livewire:load', () => {
                if (window.Livewire) {
                    window.Livewire.hook('message.processed', () => this.scrollChat());
                }
            });
        },

        scrollChat() {
            const chatEl = document.getElementById('liveChatScroll');
            if (chatEl) {
                chatEl.scrollTop = chatEl.scrollHeight;
            }
        },

        get formattedDuration() {
            const total = this.durationSeconds;
            const h = String(Math.floor(total / 3600)).padStart(2, '0');
            const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
            const s = String(total % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        get formattedRecordDuration() {
            const m = String(Math.floor(this.recordSeconds / 60)).padStart(2, '0');
            const s = String(this.recordSeconds % 60).padStart(2, '0');
            return `${m}:${s}`;
        },

        // ── SIGNALING SYSTEM ──
        setupSignaling() {
            // A. BroadcastChannel: 0ms latency for same-origin tabs
            if ('BroadcastChannel' in window) {
                try {
                    this.broadcastChannel = new BroadcastChannel('kitobxon_live_' + this.eventId);
                    this.broadcastChannel.onmessage = (event) => {
                        this.handleSignalMessage(event.data);
                    };
                } catch (e) {
                    console.warn('BroadcastChannel error:', e);
                }
            }

            // B. HTTP Polling: Cross-browser & Cross-device
            this.pollSignals();
            this.pollInterval = setInterval(() => this.pollSignals(), 800);
        },

        async pollSignals() {
            try {
                const res = await fetch(`/live/${this.eventId}/signals?peer_id=${encodeURIComponent(this.myPeerId)}&since_id=${this.lastSignalId}`);
                if (!res.ok) return;
                const data = await res.json();
                if (data.signals && data.signals.length > 0) {
                    for (const sig of data.signals) {
                        if (sig.id > this.lastSignalId) {
                            this.lastSignalId = sig.id;
                        }
                        this.handleSignalMessage(sig);
                    }
                }
            } catch (err) {
                // Ignore transient network errors
            }
        },

        async sendSignal(type, receiverId, payload) {
            const msg = {
                msg_id: 'sig_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8),
                sender_id: this.myPeerId,
                receiver_id: receiverId,
                type: type,
                payload: payload
            };

            // 1. BroadcastChannel dispatch
            if (this.broadcastChannel) {
                try {
                    this.broadcastChannel.postMessage(msg);
                } catch (e) {}
            }

            // 2. HTTP POST dispatch
            try {
                await fetch(`/live/${this.eventId}/signal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sender_id: msg.sender_id,
                        receiver_id: msg.receiver_id,
                        type: msg.type,
                        payload: msg.payload
                    })
                });
            } catch (e) {}
        },

        async handleSignalMessage(msg) {
            if (!msg || !msg.type || msg.sender_id === this.myPeerId) return;

            // De-duplicate duplicate messages from dual channels
            const dedupeKey = msg.msg_id || (msg.id ? `id_${msg.id}` : `${msg.sender_id}_${msg.type}_${JSON.stringify(msg.payload).slice(0, 30)}`);
            if (this.processedSignalKeys.has(dedupeKey)) return;
            this.processedSignalKeys.add(dedupeKey);
            if (this.processedSignalKeys.size > 250) {
                const first = this.processedSignalKeys.values().next().value;
                this.processedSignalKeys.delete(first);
            }

            if (this.isHost) {
                await this.handleHostSignal(msg);
            } else {
                await this.handleViewerSignal(msg);
            }
        },

        // ── HOST SIGNAL HANDLERS ──
        async handleHostSignal(msg) {
            const viewerId = msg.sender_id;

            if (msg.type === 'join') {
                await this.createPeerForViewer(viewerId);
            } else if (msg.type === 'answer') {
                const pc = this.peers[viewerId];
                if (pc && pc.signalingState !== 'stable') {
                    try {
                        await pc.setRemoteDescription(new RTCSessionDescription(msg.payload));
                    } catch (e) {
                        console.warn('Host setRemoteDescription error:', e);
                    }
                }
            } else if (msg.type === 'ice-candidate') {
                const pc = this.peers[viewerId];
                if (pc && msg.payload) {
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(msg.payload));
                    } catch (e) {}
                }
            }
        },

        async createPeerForViewer(viewerId) {
            if (this.peers[viewerId]) {
                try { this.peers[viewerId].close(); } catch (e) {}
            }

            const pc = new RTCPeerConnection(this.rtcConfig);
            this.peers[viewerId] = pc;

            // Add audio & video tracks from host active stream
            const activeStream = this.isScreenSharing ? this.screenStream : this.localStream;
            if (activeStream) {
                activeStream.getTracks().forEach(track => {
                    pc.addTrack(track, activeStream);
                });
            }

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    this.sendSignal('ice-candidate', viewerId, event.candidate);
                }
            };

            pc.onconnectionstatechange = () => {
                if (pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                    delete this.peers[viewerId];
                }
            };

            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                await this.sendSignal('offer', viewerId, offer);

                // Broadcast current stream status to viewer
                await this.sendSignal('stream-status', viewerId, {
                    isVideoOn: this.isVideoOn,
                    isMicOn: this.isMicOn,
                    isHostOnline: true
                });
            } catch (err) {
                console.error('Host offer creation error:', err);
            }
        },

        // ── VIEWER SIGNAL HANDLERS ──
        async handleViewerSignal(msg) {
            if (msg.type === 'offer') {
                await this.handleOfferFromHost(msg.payload);
            } else if (msg.type === 'ice-candidate') {
                if (this.peerConnection && this.peerConnection.remoteDescription) {
                    try {
                        await this.peerConnection.addIceCandidate(new RTCIceCandidate(msg.payload));
                    } catch (e) {}
                } else if (msg.payload) {
                    this.iceCandidateQueue.push(msg.payload);
                }
            } else if (msg.type === 'stream-status') {
                if (typeof msg.payload.isVideoOn === 'boolean') {
                    this.isVideoOn = msg.payload.isVideoOn;
                }
                if (typeof msg.payload.isMicOn === 'boolean') {
                    this.isMicOn = msg.payload.isMicOn;
                }
            }
        },

        async handleOfferFromHost(offer) {
            if (this.peerConnection) {
                try { this.peerConnection.close(); } catch (e) {}
            }

            const pc = new RTCPeerConnection(this.rtcConfig);
            this.peerConnection = pc;

            pc.ontrack = (event) => {
                const videoEl = document.getElementById('liveVideoPlayer');
                if (videoEl && event.streams && event.streams[0]) {
                    const stream = event.streams[0];
                    if (videoEl.srcObject !== stream) {
                        videoEl.srcObject = stream;
                    }
                    videoEl.play().then(() => {
                        this.needsUnmute = false;
                    }).catch(err => {
                        console.warn('Autoplay with audio blocked by browser policy, muting video:', err);
                        videoEl.muted = true;
                        videoEl.play().catch(()=>{});
                        this.needsUnmute = true;
                    });

                    this.hasRemoteStream = true;
                    this.isConnecting = false;
                    this.setupAudioAnalyser(stream);
                }
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    this.sendSignal('ice-candidate', 'host', event.candidate);
                }
            };

            try {
                await pc.setRemoteDescription(new RTCSessionDescription(offer));

                while (this.iceCandidateQueue.length > 0) {
                    const cand = this.iceCandidateQueue.shift();
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(cand));
                    } catch (e) {}
                }

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await this.sendSignal('answer', 'host', answer);
            } catch (err) {
                console.error('Viewer error processing host offer:', err);
            }
        },

        unmuteAudio() {
            const videoEl = document.getElementById('liveVideoPlayer');
            if (videoEl) {
                videoEl.muted = false;
                videoEl.play().catch(()=>{});
            }
            this.needsUnmute = false;
        },

        // ── MEDIA STREAM & DEVICE MANAGEMENT (HOST) ──
        async scanMediaDevices() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                    return;
                }
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.audioDevices = devices.filter(d => d.kind === 'audioinput');
                this.videoDevices = devices.filter(d => d.kind === 'videoinput');

                if (this.audioDevices.length > 0 && !this.selectedAudioDevice) {
                    this.selectedAudioDevice = this.audioDevices[0].deviceId;
                }
                if (this.videoDevices.length > 0 && !this.selectedVideoDevice) {
                    this.selectedVideoDevice = this.videoDevices[0].deviceId;
                }
            } catch (err) {
                console.warn('Qurilmalarni skanerlashda xatolik:', err);
            }
        },

        async startMediaStream() {
            try {
                if (this.localStream) {
                    this.localStream.getTracks().forEach(t => t.stop());
                }

                const constraints = {
                    audio: this.selectedAudioDevice ? { deviceId: { exact: this.selectedAudioDevice } } : true,
                    video: this.selectedVideoDevice ? { deviceId: { exact: this.selectedVideoDevice }, width: { ideal: 1280 }, height: { ideal: 720 } } : true
                };

                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.localStream = stream;

                const videoEl = document.getElementById('liveVideoPlayer');
                if (videoEl) {
                    videoEl.muted = true; // Host local preview is muted to prevent acoustic feedback loop
                    videoEl.srcObject = stream;
                    videoEl.play().catch(() => {});
                }

                this.setupAudioAnalyser(stream);
                await this.scanMediaDevices();

                // Update tracks for any active viewers
                this.replaceTracksOnAllPeers(stream);

                this.sendSignal('stream-status', 'all', {
                    isVideoOn: this.isVideoOn,
                    isMicOn: this.isMicOn,
                    isHostOnline: true
                });
            } catch (err) {
                console.warn('Kamera yoki mikrofonga ulanish imkoni bo\'lmadi:', err);
                this.isVideoOn = false;
            }
        },

        replaceTracksOnAllPeers(newStream) {
            if (!this.peers) return;
            const newTracks = newStream.getTracks();
            Object.values(this.peers).forEach(pc => {
                if (pc && pc.getSenders) {
                    const senders = pc.getSenders();
                    newTracks.forEach(newTrack => {
                        const sender = senders.find(s => s.track && s.track.kind === newTrack.kind);
                        if (sender) {
                            sender.replaceTrack(newTrack).catch(()=>{});
                        } else {
                            try { pc.addTrack(newTrack, newStream); } catch(e){}
                        }
                    });
                }
            });
        },

        setupAudioAnalyser(stream) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;

                if (!this.audioContext) {
                    this.audioContext = new AudioCtx();
                }

                if (this.audioContext.state === 'suspended') {
                    const resume = () => {
                        this.audioContext.resume();
                        document.removeEventListener('click', resume);
                    };
                    document.addEventListener('click', resume);
                }

                const audioTracks = stream.getAudioTracks();
                if (!audioTracks || audioTracks.length === 0) return;

                const source = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 64;
                source.connect(this.analyser);

                const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
                const updateVolume = () => {
                    if (!this.isMicOn) {
                        this.audioVolume = 0;
                        requestAnimationFrame(updateVolume);
                        return;
                    }
                    this.analyser.getByteFrequencyData(dataArray);
                    let sum = 0;
                    for (let i = 0; i < dataArray.length; i++) {
                        sum += dataArray[i];
                    }
                    const avg = sum / dataArray.length;
                    this.audioVolume = Math.min(100, Math.floor(avg * 1.2));
                    requestAnimationFrame(updateVolume);
                };
                updateVolume();
            } catch (e) {}
        },

        async changeAudioSource(deviceId) {
            this.selectedAudioDevice = deviceId;
            if (this.localStream) {
                const oldTracks = this.localStream.getAudioTracks();
                oldTracks.forEach(t => t.stop());

                try {
                    const newAudioStream = await navigator.mediaDevices.getUserMedia({
                        audio: { deviceId: { exact: deviceId } }
                    });
                    const newAudioTrack = newAudioStream.getAudioTracks()[0];
                    newAudioTrack.enabled = this.isMicOn;

                    if (oldTracks[0]) this.localStream.removeTrack(oldTracks[0]);
                    this.localStream.addTrack(newAudioTrack);
                    this.setupAudioAnalyser(this.localStream);
                    this.replaceTracksOnAllPeers(this.localStream);
                } catch (e) {
                    console.error('Mikrofon almashtirishda xatolik:', e);
                }
            }
        },

        async changeVideoSource(deviceId) {
            this.selectedVideoDevice = deviceId;
            if (this.localStream) {
                const oldTracks = this.localStream.getVideoTracks();
                oldTracks.forEach(t => t.stop());

                try {
                    const newVideoStream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: { exact: deviceId } }
                    });
                    const newVideoTrack = newVideoStream.getVideoTracks()[0];
                    newVideoTrack.enabled = this.isVideoOn;

                    if (oldTracks[0]) this.localStream.removeTrack(oldTracks[0]);
                    this.localStream.addTrack(newVideoTrack);

                    const videoEl = document.getElementById('liveVideoPlayer');
                    if (videoEl) videoEl.srcObject = this.localStream;

                    this.replaceTracksOnAllPeers(this.localStream);
                } catch (e) {
                    console.error('Kamera almashtirishda xatolik:', e);
                }
            }
        },

        toggleMic() {
            this.isMicOn = !this.isMicOn;
            const stream = this.isScreenSharing ? this.screenStream : this.localStream;
            if (stream) {
                stream.getAudioTracks().forEach(t => t.enabled = this.isMicOn);
            }
            this.sendSignal('stream-status', 'all', {
                isVideoOn: this.isVideoOn,
                isMicOn: this.isMicOn
            });
        },

        toggleVideo() {
            this.isVideoOn = !this.isVideoOn;
            const stream = this.isScreenSharing ? this.screenStream : this.localStream;
            if (stream) {
                stream.getVideoTracks().forEach(t => t.enabled = this.isVideoOn);
            }
            this.sendSignal('stream-status', 'all', {
                isVideoOn: this.isVideoOn,
                isMicOn: this.isMicOn
            });
        },

        async toggleScreenShare() {
            if (this.isScreenSharing) {
                if (this.screenStream) {
                    this.screenStream.getTracks().forEach(t => t.stop());
                }
                this.isScreenSharing = false;
                await this.startMediaStream();
            } else {
                try {
                    const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });
                    this.screenStream = screenStream;
                    this.isScreenSharing = true;

                    const videoEl = document.getElementById('liveVideoPlayer');
                    if (videoEl) {
                        videoEl.srcObject = screenStream;
                    }

                    this.replaceTracksOnAllPeers(screenStream);
                    this.sendSignal('stream-status', 'all', {
                        isVideoOn: true,
                        isMicOn: this.isMicOn
                    });

                    screenStream.getVideoTracks()[0].onended = () => {
                        this.isScreenSharing = false;
                        this.startMediaStream();
                    };
                } catch (err) {
                    console.warn('Ekran ulashish bekor qilindi:', err);
                }
            }
        },

        toggleRecording() {
            if (this.isRecording) {
                if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                    this.mediaRecorder.stop();
                }
                this.isRecording = false;
                clearInterval(this.recordInterval);
                this.recordSeconds = 0;
            } else {
                const streamToRecord = this.isScreenSharing ? this.screenStream : this.localStream;
                if (!streamToRecord) {
                    alert('Yozib olish uchun faol kamera yoki mikrofon mavjud emas.');
                    return;
                }

                try {
                    this.recordedChunks = [];
                    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')
                        ? 'video/webm;codecs=vp9,opus'
                        : (MediaRecorder.isTypeSupported('video/webm') ? 'video/webm' : 'video/mp4');

                    this.mediaRecorder = new MediaRecorder(streamToRecord, { mimeType });

                    this.mediaRecorder.ondataavailable = (event) => {
                        if (event.data && event.data.size > 0) {
                            this.recordedChunks.push(event.data);
                        }
                    };

                    this.mediaRecorder.onstop = () => {
                        const blob = new Blob(this.recordedChunks, { type: mimeType });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = `kitobxon-jonli-efir-${new Date().toISOString().slice(0, 10)}.webm`;
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => {
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(url);
                        }, 100);
                    };

                    this.mediaRecorder.start(1000);
                    this.isRecording = true;
                    this.recordSeconds = 0;
                    this.recordInterval = setInterval(() => {
                        this.recordSeconds++;
                    }, 1000);
                } catch (err) {
                    console.error('Yozib olishni boshlashda xatolik:', err);
                    alert('Brauzeringizda ushbu formatda yozib olish qo\'llab-quvvatlanmadi.');
                }
            }
        }
    };
}
</script>
