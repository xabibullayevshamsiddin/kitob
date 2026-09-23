<div class="max-w-7xl mx-auto space-y-6 pb-16" x-data="liveStudioController(@js($isHost), @js($event->permission_mode))" x-init="initStudio()">

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

                <!-- Avatar Backdrop when Camera is Off -->
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
function liveStudioController(isHost, initialPermission) {
    return {
        isHost: isHost,
        permissionMode: initialPermission,
        isMicOn: true,
        isVideoOn: true,
        isScreenSharing: false,
        isRecording: false,
        showDeviceSettings: false,

        // Devices
        audioDevices: [],
        videoDevices: [],
        selectedAudioDevice: '',
        selectedVideoDevice: '',

        // Stream instances
        localStream: null,
        screenStream: null,
        mediaRecorder: null,
        recordedChunks: [],

        // Audio Analyser
        audioContext: null,
        analyser: null,
        audioVolume: 20,

        // Timers
        durationSeconds: 0,
        recordSeconds: 0,
        durationInterval: null,
        recordInterval: null,

        async initStudio() {
            // Start duration ticker
            this.durationInterval = setInterval(() => {
                this.durationSeconds++;
            }, 1000);

            if (this.isHost) {
                await this.scanMediaDevices();
                await this.startMediaStream();
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
            const h = String(Math.floor(this.durationSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((this.durationSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(this.durationSeconds % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        get formattedRecordDuration() {
            const m = String(Math.floor(this.recordSeconds / 60)).padStart(2, '0');
            const s = String(this.recordSeconds % 60).padStart(2, '0');
            return `${m}:${s}`;
        },

        // 1. Enumerate all hardware microphones & cameras
        async scanMediaDevices() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                    return;
                }

                // Initial permission prompt if labels are empty
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

        // 2. Start or update local camera & mic stream
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
                    videoEl.srcObject = stream;
                    videoEl.play().catch(() => {});
                }

                this.setupAudioAnalyser(stream);
                await this.scanMediaDevices(); // Rescan to populate labels after permission granted
            } catch (err) {
                console.warn('Kamera yoki mikrofonga ulanish imkoni bo\'lmadi:', err);
                this.isVideoOn = false;
            }
        },

        // 3. Audio VU Meter setup via Web Audio API
        setupAudioAnalyser(stream) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;

                if (!this.audioContext) {
                    this.audioContext = new AudioCtx();
                }

                const source = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 64;
                source.connect(this.analyser);

                const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
                const updateVolume = () => {
                    if (!this.isMicOn || !this.localStream) {
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

        // 4. Change Microphone source dynamically
        async changeAudioSource(deviceId) {
            this.selectedAudioDevice = deviceId;
            if (this.localStream) {
                const audioTracks = this.localStream.getAudioTracks();
                audioTracks.forEach(t => t.stop());

                try {
                    const newAudioStream = await navigator.mediaDevices.getUserMedia({
                        audio: { deviceId: { exact: deviceId } }
                    });
                    const newAudioTrack = newAudioStream.getAudioTracks()[0];
                    newAudioTrack.enabled = this.isMicOn;

                    // Replace track
                    this.localStream.removeTrack(audioTracks[0]);
                    this.localStream.addTrack(newAudioTrack);
                    this.setupAudioAnalyser(this.localStream);
                } catch (e) {
                    console.error('Mikrofon almashtirishda xatolik:', e);
                }
            }
        },

        // 5. Change Video source dynamically
        async changeVideoSource(deviceId) {
            this.selectedVideoDevice = deviceId;
            if (this.localStream) {
                const videoTracks = this.localStream.getVideoTracks();
                videoTracks.forEach(t => t.stop());

                try {
                    const newVideoStream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: { exact: deviceId } }
                    });
                    const newVideoTrack = newVideoStream.getVideoTracks()[0];
                    newVideoTrack.enabled = this.isVideoOn;

                    this.localStream.removeTrack(videoTracks[0]);
                    this.localStream.addTrack(newVideoTrack);

                    const videoEl = document.getElementById('liveVideoPlayer');
                    if (videoEl) videoEl.srcObject = this.localStream;
                } catch (e) {
                    console.error('Kamera almashtirishda xatolik:', e);
                }
            }
        },

        // 6. Mic On/Off toggle
        toggleMic() {
            this.isMicOn = !this.isMicOn;
            if (this.localStream) {
                this.localStream.getAudioTracks().forEach(t => t.enabled = this.isMicOn);
            }
        },

        // 7. Video On/Off toggle
        toggleVideo() {
            this.isVideoOn = !this.isVideoOn;
            if (this.localStream) {
                this.localStream.getVideoTracks().forEach(t => t.enabled = this.isVideoOn);
            }
        },

        // 8. Screen Share (Ekran ulashish)
        async toggleScreenShare() {
            if (this.isScreenSharing) {
                // Stop screen share and revert to webcam
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

                    // Auto revert when user stops sharing from browser toolbar
                    screenStream.getVideoTracks()[0].onended = () => {
                        this.isScreenSharing = false;
                        this.startMediaStream();
                    };
                } catch (err) {
                    console.warn('Ekran ulashish bekor qilindi:', err);
                }
            }
        },

        // 9. Video Recording (MediaRecorder API - Zapis)
        toggleRecording() {
            if (this.isRecording) {
                // Stop recording & trigger download
                if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                    this.mediaRecorder.stop();
                }
                this.isRecording = false;
                clearInterval(this.recordInterval);
                this.recordSeconds = 0;
            } else {
                // Start recording
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

                    this.mediaRecorder.start(1000); // 1-second chunks
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
