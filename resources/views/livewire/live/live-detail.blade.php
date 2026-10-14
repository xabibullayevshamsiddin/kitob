<div class="max-w-7xl mx-auto space-y-6 pb-16" 
    x-data="liveStudioController({
        isHost: @js($isHost),
        userId: @js(auth()->id()),
        eventId: {{ $event->id }},
        startedAt: {{ $event->started_at ? $event->started_at->timestamp : ($event->created_at ? $event->created_at->timestamp : now()->timestamp) }},
        serverNow: {{ now()->timestamp }},
        initialLikes: {{ (int) ($event->likes_count ?? 0) }},
        permissionMode: @js($event->permission_mode),
        signalSendUrl: @js(route('live.signal.send', $event)),
        signalPollUrl: @js(route('live.signal.poll', $event)),
        csrfToken: @js(csrf_token()),
        liveIndexUrl: @js($event->group_id ? route('groups.show', $event->group_id) : route('live.index')),
        micOn: @js(__('site.live.mic_on')),
        micOff: @js(__('site.live.mic_off')),
        camOn: @js(__('site.live.cam_on')),
        camOff: @js(__('site.live.cam_off')),
        stopShare: @js(__('site.live.stop_share')),
        shareScreen: @js(__('site.live.share_screen')),
        stopRec: @js(__('site.live.stop_rec')),
        startRec: @js(__('site.live.start_rec')),
        micCamOff: @js(__('site.live.mic_cam_off')),
        micStreaming: @js(__('site.live.mic_streaming')),
        waitingHost: @js(__('site.live.waiting_host')),
    })" 
    x-init="initStudio()">

    <!-- ── 1. TOP HEADER & STATUS BAR ── -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-ink-900 border border-ink-border p-4 sm:p-5 rounded-panel shadow-soft">
        <div class="flex items-center gap-3">
            <a href="{{ $event->group_id ? route('groups.show', $event->group_id) : route('live.index') }}" class="p-2 rounded-btn bg-ink-950/80 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors" title="{{ $event->group_id ? 'Guruhga qaytish' : 'Orqaga' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-bold font-serif text-paper truncate max-w-xl">{{ $event->title }}</h1>
                    @if($event->group)
                        <a href="{{ route('groups.show', $event->group) }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill bg-amber-500/15 border border-amber-500/30 text-amber-400 hover:text-amber-300 text-[11px] font-mono font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $event->group->name }}</span>
                        </a>
                    @endif
                    @if($event->is_live)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 text-[11px] font-mono font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                            <span>{{ __('site.live.filter_live') }}</span>
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist text-[11px] font-mono uppercase">
                            {{ ucfirst($event->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs font-mono text-mist mt-0.5 flex flex-wrap items-center gap-2">
                    @if($event->hostUser)
                        <span>{{ __('site.live.host') }}: <strong class="text-paper">{{ $event->hostUser->name }}</strong></span>
                    @endif
                    @if($event->book)
                        <span>• {{ $event->book->title }}</span>
                    @endif
                    <span>• {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i') }}</span>
                </p>
            </div>
        </div>

        <!-- Right: Current Permission Pill & Host Quick End -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Permission indicator badge -->
            <div class="px-3 py-1.5 rounded-btn bg-ink-950/80 border border-ink-border text-mist font-mono text-xs font-medium flex items-center gap-1.5">
                @if(in_array($event->permission_mode, ['view_only', 'voice_only']))
                    <span>{{ __('site.live.perm_filter_view') }}</span>
                @else
                    <span>{{ __('site.live.perm_filter_chat') }}</span>
                @endif
            </div>

            @if($isHost || $canManage)
                @if($event->status === 'live')
                    <button wire:click="endLiveStream" wire:confirm="{{ __('site.live.end_confirm', ['title' => '']) }}"
                        class="px-3.5 py-1.5 bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider rounded-btn transition-all">
                        {{ __('site.live.end') }}
                    </button>
                @else
                    <button wire:click="restartLiveStream"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-paper font-mono font-bold text-xs uppercase tracking-wider rounded-btn transition-all">
                        ▶ {{ __('site.live.restart') }}
                    </button>
                @endif
            @endif
        </div>
    </div>


    <!-- ── 2. TWO-COLUMN STUDIO GRID ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- ════ LEFT: BROADCAST VIDEO & HOST CONSOLE (8 Cols) ════ -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-5">

            <!-- Video Screen Viewport -->
            <div class="relative w-full aspect-video rounded-panel bg-ink-950 border border-ink-border shadow-soft overflow-hidden flex items-center justify-center group"
                 @click="if (!isHost && (needsUnmute || isViewerMuted)) unmuteAudio()">
                
                <!-- Live Video Element (Webcam / Stream) -->
                <video id="liveVideoPlayer" autoplay playsinline class="w-full h-full object-cover transition-opacity duration-300"
                       :style="videoFilterStyle"
                       :class="{ 'opacity-0': !isVideoOn, 'opacity-100': isVideoOn }"
                       @loadedmetadata="updateCameraStatus(localStream ? localStream.getVideoTracks()[0] : null)"
                       @playing="updateCameraStatus(localStream ? localStream.getVideoTracks()[0] : null)">
                </video>

                <!-- Dedicated Audio Element for WebRTC audio stream playback -->
                <audio id="liveAudioPlayer" autoplay playsinline style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"></audio>

                <!-- Efir tugadi overlay -->
                <div x-show="streamEnded" x-cloak
                    class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-ink-950/95 p-6 text-center space-y-3">
                    <div class="w-16 h-16 rounded-btn bg-ink-900 border border-ink-border flex items-center justify-center text-amber-400">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-paper font-serif">{{ __('site.live.ended_title') }}</h3>
                        <p class="text-xs text-mist font-mono mt-1 max-w-sm">{{ __('site.live.ended_sub') }}</p>
                    </div>
                    <a :href="liveIndexUrl"
                        class="ks-btn-primary text-xs py-2 px-4 font-mono">
                        {{ __('site.live.watch_others') }} →
                    </a>
                </div>

                <!-- Avatar Backdrop when Camera is Off -->
                <div x-show="!isVideoOn" class="absolute inset-0 flex flex-col items-center justify-center bg-ink-950 p-6 text-center space-y-3">
                    <div class="relative">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-panel border-2 border-amber-400/40 p-1">
                            <img src="{{ $event->hostUser?->avatar_url ?? 'https://ui-avatars.com/api/?name=Kitobxon&background=1e293b&color=fff' }}" 
                                class="w-full h-full rounded-panel object-cover">
                        </div>
                        <div x-show="isMicOn" class="absolute -inset-1 rounded-panel border border-amber-400/40 animate-ping pointer-events-none"></div>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-paper font-serif">{{ $event->hostUser?->name ?? 'Ustoz' }}</h3>
                        <p class="text-xs text-mist font-mono">
                            <span x-text="!hasRemoteStream && !isHost ? waitingHost : (isMicOn ? micStreaming : micCamOff)"></span>
                        </p>
                    </div>
                </div>

                <!-- Viewer Connecting / Status Pill -->
                <div x-show="!isHost && !hasRemoteStream" class="absolute top-4 left-32 z-20 flex items-center gap-2 bg-ink-900/90 border border-amber-500/40 text-amber-400 text-xs font-mono px-3 py-1 rounded-pill backdrop-blur-md animate-pulse shadow-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                    <span>{{ __('site.live.connecting') }}</span>
                </div>

                <!-- Reconnect Button -->
                <div x-show="!isHost && !hasRemoteStream && connectionAttempts >= 2" class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20">
                    <button @click="reconnect()" class="px-3.5 py-1.5 rounded-btn bg-ink-900/90 hover:bg-ink-800 border border-ink-border text-paper font-mono text-xs flex items-center gap-2 shadow-2xl backdrop-blur-md transition-all active:scale-95">
                        <span>{{ __('site.live.reconnect') }}</span>
                    </button>
                </div>

                <!-- Viewer Unmute prompt banner -->
                <div x-show="!isHost && needsUnmute" class="absolute inset-0 z-40 flex items-center justify-center bg-ink-950/80 backdrop-blur-sm p-4">
                    <button @click="unmuteAudio()" class="px-5 py-2.5 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider shadow-2xl flex items-center gap-2 transform hover:scale-105 active:scale-95 transition-all">
                        <span>{{ __('site.live.unmute_prompt') }}</span>
                    </button>
                </div>

                <!-- Top Left Overlays (LIVE badge + Timer + Viewers count) -->
                <div class="absolute top-4 left-4 flex items-center gap-2 z-20">
                    <div class="px-2.5 py-0.5 rounded-pill bg-[#C1392B] text-paper font-mono text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-lg backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-paper animate-ping"></span>
                        <span>LIVE</span>
                    </div>
                    <div class="px-2.5 py-0.5 rounded-pill bg-ink-950/80 backdrop-blur-md text-paper font-mono text-xs border border-ink-border" x-text="formattedDuration">
                        00:00:00
                    </div>
                    <div class="px-2.5 py-0.5 rounded-pill bg-ink-950/80 backdrop-blur-md text-amber-400 font-mono text-xs border border-ink-border flex items-center gap-1.5 shadow-md"
                         title="Jonli efirni tomosha qilayotganlar">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="font-bold tabular-nums" x-text="formatNumber(Math.round(animatedViewerCount))">0</span>
                    </div>

                    <!-- Camera Resolution Badge (Host only) -->
                    <div x-show="isHost && isVideoOn && currentCamResolution" x-cloak
                         class="px-2.5 py-0.5 rounded-pill bg-ink-950/80 backdrop-blur-md text-amber-300 font-mono text-[11px] border border-amber-500/30 flex items-center gap-1 shadow-md"
                         :title="'Kameraning haqiqiy faol aniqligi: ' + currentCamResolution">
                        <svg class="w-3 h-3 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                        <span x-text="currentCamResolution"></span>
                    </div>
                </div>

                <!-- Top Right Recording Overlay -->
                <div x-show="isRecording" class="absolute top-4 right-4 z-20 animate-pulse">
                    <div class="px-2.5 py-0.5 rounded-pill bg-[#C1392B] text-paper font-mono text-xs font-bold flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-paper animate-ping"></span>
                        <span>REC <span x-text="formattedRecordDuration">00:00</span></span>
                    </div>
                </div>

                <!-- Bottom Left Audio VU Meter -->
                <div class="absolute bottom-4 left-4 z-20 flex items-center gap-2">
                    <div class="flex items-center gap-2 bg-ink-950/80 backdrop-blur-md px-2.5 py-1 rounded-btn border border-ink-border" title="Ovoz signali kuchi">
                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
                        <div class="flex items-end gap-1 h-3.5">
                            <div class="w-1 bg-emerald-500 rounded-full transition-all duration-75" :style="{ height: (audioVolume > 0 ? Math.min(100, Math.max(15, audioVolume * 1.4)) : 15) + '%', opacity: audioVolume > 5 ? 1 : 0.35 }"></div>
                            <div class="w-1 bg-emerald-400 rounded-full transition-all duration-75" :style="{ height: (audioVolume > 0 ? Math.min(100, Math.max(15, audioVolume * 1.8)) : 15) + '%', opacity: audioVolume > 15 ? 1 : 0.35 }"></div>
                            <div class="w-1 bg-amber-400 rounded-full transition-all duration-75" :style="{ height: (audioVolume > 0 ? Math.min(100, Math.max(15, audioVolume * 2.3)) : 15) + '%', opacity: audioVolume > 30 ? 1 : 0.35 }"></div>
                            <div class="w-1 bg-[#C1392B] rounded-full transition-all duration-75" :style="{ height: (audioVolume > 0 ? Math.min(100, Math.max(15, audioVolume * 2.8)) : 15) + '%', opacity: audioVolume > 50 ? 1 : 0.35 }"></div>
                        </div>
                    </div>

                    <!-- Viewer Mute/Unmute Quick Toggle -->
                    <template x-if="!isHost && hasRemoteStream">
                        <div class="flex items-center gap-2">
                            <button @click="unmuteAudio()"
                                x-show="needsUnmute || isViewerMuted"
                                class="flex items-center gap-1.5 bg-[#C1392B] hover:bg-[#a63024] backdrop-blur-md px-3 py-1 rounded-btn border border-rose-500/40 text-paper text-xs font-mono font-bold transition-all shadow-lg active:scale-95 animate-pulse">
                                <span>{{ __('site.live.unmute_short') }}</span>
                            </button>
                            <button @click="toggleViewerMute()"
                                x-show="!needsUnmute && !isViewerMuted"
                                class="flex items-center gap-1.5 bg-ink-950/80 hover:bg-ink-800 backdrop-blur-md px-3 py-1 rounded-btn border border-ink-border text-paper text-xs font-mono transition-colors">
                                <span>{{ __('site.live.muted_on') }}</span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Floating Hearts Container (Instagram / TikTok Live style) -->
                <div class="absolute bottom-16 right-4 w-32 h-72 pointer-events-none z-30 overflow-hidden" aria-hidden="true">
                    <template x-for="heart in floatingHearts" :key="heart.id">
                        <div class="ks-floating-heart absolute bottom-0 right-4 select-none"
                             :style="{
                                 '--tx': heart.tx + 'px',
                                 '--rot': heart.rot + 'deg',
                                 '--scale': heart.scale,
                                 '--dur': heart.dur + 'ms',
                                 color: heart.color
                             }">
                            <svg class="w-6 h-6 fill-current drop-shadow-md" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Bottom Right Watermark & Interactive Like Button -->
                <div class="absolute bottom-4 right-4 z-30 flex items-center gap-2">
                    <div class="hidden sm:flex items-center gap-1.5 bg-ink-950/80 backdrop-blur-md px-2 py-1 rounded-pill text-[10px] text-mist font-mono border border-ink-border">
                        <span>Kitobxon Studio</span>
                    </div>

                    <!-- Like Button (Instagram/TikTok Live uslubi — istalgancha bosish mumkin) -->
                    @auth
                    <button type="button"
                            @click.stop="burstLike()"
                            class="group/like relative flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-ink-950/85 hover:bg-ink-900 backdrop-blur-md shadow-lg transition-all active:scale-90 cursor-pointer border border-rose-500/40 hover:border-rose-400 text-paper"
                            title="Jonli efirga like bosish">
                        <span class="transition-transform group-hover/like:scale-125 flex items-center justify-center text-rose-400">
                            <svg class="w-4.5 h-4.5 fill-current text-rose-500 transition-colors" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </span>
                        <span class="font-bold font-mono min-w-[18px] text-left tabular-nums text-xs text-paper"
                              x-text="formatNumber(likesCount)">0</span>
                    </button>
                    @else
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-ink-950/85 backdrop-blur-md border border-ink-border text-paper font-mono"
                          title="Like bosish uchun tizimga kiring">
                        <svg class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <span class="font-bold tabular-nums text-xs" x-text="formatNumber(likesCount)">0</span>
                    </span>
                    @endauth
                </div>
            </div>

            <!-- ── HOST TOOLBAR (Ustoz / Admin Uskunalari) ── -->
            @if($isHost)
                <div class="p-4 sm:p-5 rounded-panel bg-ink-900 border border-ink-border shadow-soft space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs sm:text-sm font-bold font-serif text-paper">{{ __('site.live.studio_title') }}</span>
                            <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-pill bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 font-bold">Broadcaster</span>
                        </div>

                        <!-- Hardware Settings Toggle button -->
                        <button @click="showDeviceSettings = !showDeviceSettings"
                            class="text-xs font-mono px-2.5 py-1 rounded-btn border border-ink-border text-mist hover:text-paper hover:bg-ink-800 flex items-center gap-1.5 transition-colors">
                            <span>{{ __('site.live.device_settings') }}</span>
                            <span x-text="showDeviceSettings ? '▲' : '▼'"></span>
                        </button>
                    </div>

                    <!-- Action Buttons Toolbar -->
                    <div class="flex flex-wrap items-center gap-2 pt-1 font-mono text-xs">
                        <!-- Mic toggle -->
                        <button @click="toggleMic()"
                            class="px-3.5 py-2 rounded-btn font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-sm"
                            :class="isMicOn ? 'ks-btn-primary' : 'bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 hover:bg-[#C1392B]/25'">
                            <span x-text="isMicOn ? micOn : micOff"></span>
                        </button>

                        <!-- Camera toggle -->
                        <button @click="toggleVideo()"
                            class="px-3.5 py-2 rounded-btn font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-sm"
                            :class="isVideoOn ? 'ks-btn-primary' : 'ks-btn-ghost'">
                            <span x-text="isVideoOn ? camOn : camOff"></span>
                        </button>

                        <!-- Screen share -->
                        <button @click="toggleScreenShare()"
                            class="px-3.5 py-2 rounded-btn font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-sm"
                            :class="isScreenSharing ? 'ks-btn-gold' : 'ks-btn-ghost'">
                            <span x-text="isScreenSharing ? stopShare : shareScreen"></span>
                        </button>

                        <!-- Video Recording -->
                        <button @click="toggleRecording()"
                            class="px-3.5 py-2 rounded-btn font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-sm"
                            :class="isRecording ? 'bg-[#C1392B] text-paper animate-pulse' : 'ks-btn-ghost'">
                            <span x-text="isRecording ? stopRec : startRec"></span>
                        </button>
                    </div>

                    <!-- Ekran ulashish audio eslatmasi -->
                    <div x-show="isScreenSharing" x-transition
                        class="px-3.5 py-2.5 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-300 text-xs flex items-start gap-2">
                        <div>
                            <p class="font-bold mb-0.5 font-serif">{{ __('site.live.share_audio_title') }}</p>
                            <p class="font-sans text-[11px] leading-relaxed">{!! __('site.live.share_audio_sub') !!}</p>
                            <p class="mt-1 font-mono text-[10px] text-amber-400/80">⚠️ {{ __('site.live.share_audio_warn') }}</p>
                        </div>
                    </div>

                    <!-- Qurilma sozlamalari -->
                    <div x-show="showDeviceSettings" x-collapse x-cloak class="p-3.5 rounded-btn bg-ink-950/80 border border-ink-border space-y-3 font-mono text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-mist mb-1">
                                    {{ __('site.live.mic_select') }}
                                </label>
                                <select id="audioSourceSelect" @change="changeAudioSource($event.target.value)"
                                    class="w-full px-2.5 py-1.5 bg-ink-900 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none">
                                    <template x-for="device in audioDevices" :key="device.deviceId">
                                        <option :value="device.deviceId" x-text="device.label || `Mikrafon ${$index + 1}`" :selected="device.deviceId === selectedAudioDevice"></option>
                                    </template>
                                </select>
                                <p class="text-[10px] text-mist mt-1 font-sans">{{ __('site.live.mic_select_hint') }}</p>
                            </div>

                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-mist mb-1">
                                    {{ __('site.live.cam_select') }}
                                </label>
                                <select id="videoSourceSelect" @change="changeVideoSource($event.target.value)"
                                    class="w-full px-2.5 py-1.5 bg-ink-900 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none">
                                    <template x-for="device in videoDevices" :key="device.deviceId">
                                        <option :value="device.deviceId" x-text="device.label || `Kamera ${$index + 1}`" :selected="device.deviceId === selectedVideoDevice"></option>
                                    </template>
                                </select>
                                <p class="text-[10px] text-mist mt-1 font-sans">{{ __('site.live.cam_select_hint') }}</p>
                            </div>

                            {{-- Video Quality (Resolution) Selector --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] uppercase tracking-wider text-mist">
                                        Video Sifati (Aniqlik)
                                    </label>
                                    <span class="text-[10px] font-mono text-amber-300 font-bold" x-text="currentCamResolution || 'Faol'"></span>
                                </div>
                                <select id="videoQualitySelect" x-model="videoQuality" @change="changeVideoQuality($event.target.value)"
                                    class="w-full px-2.5 py-1.5 bg-ink-900 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none font-mono">
                                    <option value="1080p">Full HD (1080p) — Yuqori tiniqlik 🌟</option>
                                    <option value="720p">HD (720p) — Standart tiniq</option>
                                    <option value="480p">SD (480p) — Yengil / Trafik tejash</option>
                                </select>
                                <p class="text-[10px] text-mist mt-1 font-sans">Kamerangiz apparat imkoniyatiga qarab maksimal aniqlikda ishlaydi</p>
                            </div>

                            {{-- Studio HD Enhancer Selector --}}
                            <div class="p-2.5 rounded-btn bg-ink-900/60 border border-ink-border space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-paper font-medium">Studio HD Enhancer</span>
                                        <span class="text-[9px] px-1 py-0.5 rounded bg-amber-400/20 text-amber-300 font-mono">PRO</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-amber-300" x-text="videoEnhance === 'bright' ? 'Yorqin' : (videoEnhance === 'sharp' ? 'Ultra Tiniq' : (videoEnhance === 'hd' ? 'Tiniq HD' : 'O‘chirilgan'))"></span>
                                </div>
                                <p class="text-[10px] text-mist">Veb-kamera xiraligini yo'qotib, kontrast va tabiiy ranglarni kuchaytiradi</p>
                                <div class="grid grid-cols-4 gap-1.5 pt-1 font-mono text-[11px]">
                                    <button type="button" @click="setVideoEnhance('hd')"
                                        :class="videoEnhance === 'hd' ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 text-mist hover:text-paper'"
                                        class="py-1 px-1 rounded text-center transition-all">
                                        Tiniq HD
                                    </button>
                                    <button type="button" @click="setVideoEnhance('sharp')"
                                        :class="videoEnhance === 'sharp' ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 text-mist hover:text-paper'"
                                        class="py-1 px-1 rounded text-center transition-all">
                                        Ultra Tiniq
                                    </button>
                                    <button type="button" @click="setVideoEnhance('bright')"
                                        :class="videoEnhance === 'bright' ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 text-mist hover:text-paper'"
                                        class="py-1 px-1 rounded text-center transition-all">
                                        Yorqin
                                    </button>
                                    <button type="button" @click="setVideoEnhance('off')"
                                        :class="videoEnhance === 'off' ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 text-mist hover:text-paper'"
                                        class="py-1 px-1 rounded text-center transition-all">
                                        O‘chiq
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-ink-border text-xs">
                            <span class="text-mist">{{ __('site.live.devices_hint') }}</span>
                            <button @click="scanMediaDevices()" class="text-amber-400 hover:underline">
                                {{ __('site.live.rescan_devices') }}
                            </button>
                        </div>
                    </div>

                    <!-- Audience permission switcher -->
                    @if($canEditSettings)
                    <div class="pt-3 border-t border-ink-border space-y-2">
                        <label class="block text-xs font-mono uppercase tracking-wider text-mist">
                            {{ __('site.live.audience_perms') }}
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono">
                            <button type="button" wire:click="updatePermissionMode('chat_only')"
                                class="p-2.5 rounded-btn border text-left text-xs transition-all {{ in_array($event->permission_mode, ['chat_only', 'both']) ? 'bg-amber-500/10 border-amber-400 text-amber-400 font-bold' : 'border-ink-border text-mist hover:text-paper' }}">
                                <span class="block">{{ __('site.live.perm_chat') }}</span>
                                <span class="text-[10px] opacity-75 font-normal block">{{ __('site.live.perm_chat_sub') }}</span>
                            </button>

                            <button type="button" wire:click="updatePermissionMode('view_only')"
                                class="p-2.5 rounded-btn border text-left text-xs transition-all {{ in_array($event->permission_mode, ['view_only', 'voice_only']) ? 'bg-amber-500/10 border-amber-400 text-amber-400 font-bold' : 'border-ink-border text-mist hover:text-paper' }}">
                                <span class="block">{{ __('site.live.perm_view') }}</span>
                                <span class="text-[10px] opacity-75 font-normal block">{{ __('site.live.perm_view_sub') }}</span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Event Details Note -->
            @if($event->description)
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border shadow-soft">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.live.about') }}</h3>
                    <p class="text-xs sm:text-sm text-mist leading-relaxed font-sans break-words whitespace-pre-line">{{ $event->description }}</p>
                </div>
            @endif

        </div>

        <!-- ════ RIGHT: REAL-TIME CHAT & QUESTIONS STREAM (4-5 Cols) ════ -->
        {{-- Alo hida chat komponent: wire:poll.2s bilan avtomatik yangilanadi (umumiy chat kabi).
             WebRTC skripti bu blokda emas — polling video ulanishini BUZMAYDI. --}}
        <div class="lg:col-span-5 xl:col-span-4 h-[650px]">
            @livewire('live.live-chat-panel', ['event' => $event, 'isHost' => $isHost], key('live-chat-'.$event->id))
        </div>

    </div>

    <style>
        @keyframes ksFloatHeart {
            0% {
                opacity: 1;
                transform: translateY(0) translateX(0) rotate(0deg) scale(var(--scale, 1));
            }
            50% {
                opacity: 0.95;
                transform: translateY(-90px) translateX(calc(var(--tx, 0px) * 0.7)) rotate(calc(var(--rot, 0deg) * 0.5)) scale(calc(var(--scale, 1) * 1.15));
            }
            100% {
                opacity: 0;
                transform: translateY(-220px) translateX(var(--tx, 0px)) rotate(var(--rot, 0deg)) scale(calc(var(--scale, 1) * 0.7));
            }
        }
        .ks-floating-heart {
            animation: ksFloatHeart var(--dur, 1600ms) cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            will-change: transform, opacity;
            pointer-events: none;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- ── 3. WEBRTC / MEDIA STUDIO JAVASCRIPT CONTROLLER ── -->
    <script>

/**
 * normalizeSdp — Ensures RFC 4566 compliance for WebRTC Session Descriptions:
 * 1. Splits on any line break (\r\n, \r, or \n) regardless of transport corruption
 * 2. Trims trailing whitespace from each line
 * 3. Removes blank lines (prohibited by RFC 4566, causes "Invalid SDP line" parser errors)
 * 4. Rejoins with strict CRLF (\r\n) and ensures a trailing CRLF
 */
function normalizeSdp(sdp) {
    if (!sdp || typeof sdp !== 'string') return sdp || '';
    const lines = sdp.split(/\r\n|\r|\n/);
    const cleaned = [];
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trimEnd();
        if (line.length > 0) {
            cleaned.push(line);
        }
    }
    return cleaned.join('\r\n') + '\r\n';
}

function liveStudioController(config) {
    const isHost = Boolean(config.isHost);
    const eventId = Number(config.eventId);
    const startedAt = Number(config.startedAt || Math.floor(Date.now() / 1000));
    const serverNow = Number(config.serverNow || Math.floor(Date.now() / 1000));

    return {
        isHost: isHost,
        eventId: eventId,
        permissionMode: config.permissionMode || 'both',

        // Tarjima qilingan matnlar (blade'dan uzatiladi)
        micOn: config.micOn,
        micOff: config.micOff,
        camOn: config.camOn,
        camOff: config.camOff,
        stopShare: config.stopShare,
        shareScreen: config.shareScreen,
        stopRec: config.stopRec,
        startRec: config.startRec,
        micCamOff: config.micCamOff,
        micStreaming: config.micStreaming,
        waitingHost: config.waitingHost,

        // Media states
        isMicOn: true,
        isVideoOn: isHost ? true : false,
        hasRemoteStream: false,
        isConnecting: !isHost,
        needsUnmute: false,
        isViewerMuted: false,
        isScreenSharing: false,
        isRecording: false,
        showDeviceSettings: false,

        // Real-time Likes (Instagram/TikTok uslubi — cheksiz tap, batch'langan) & Viewers
        likesCount: Number(config.initialLikes || 0),
        pendingLikes: 0,
        likeBatchTimer: null,
        floatingHearts: [],
        viewerCount: 0,
        animatedViewerCount: 0,
        activeViewers: new Map(), // Host: viewerId -> lastSeenTimestamp
        viewerPingInterval: null,

        // Hardware devices (host)
        audioDevices: [],
        videoDevices: [],
        selectedAudioDevice: '',
        selectedVideoDevice: '',
        videoQuality: localStorage.getItem('kitob_video_quality') || '1080p',
        videoEnhance: localStorage.getItem('kitob_video_enhance') || 'hd',
        currentCamResolution: '',

        // Media streams
        localStream: null,
        remoteStream: null,
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

        // WebRTC Signaling & Dynamic URLs
        signalSendUrl: config.signalSendUrl,
        signalPollUrl: config.signalPollUrl,
        csrfToken: config.csrfToken,
        connectionAttempts: 0,
        streamEnded: false,
        liveIndexUrl: config.liveIndexUrl || '/live',
        isHostOnline: isHost ? true : false,
        pendingViewerIds: new Set(),
        offerHostPeerId: null, // Viewer: qaysi hostdan offer qabul qilingani (duplicate filtrlash uchun)

        // Peer ID HAR DOIM unikal: rol + foydalanuvchi + tasodifiy qism.
        // Shu tufayli ikkita admin bir vaqtda kirsaham to'qnashmaydi (signalling buzilmaydi).
        myPeerId: (isHost ? 'host_' : 'viewer_')
            + (config.userId || 'anon') + '_'
            + Math.random().toString(36).substring(2, 9),
        lastSignalId: 0,
        processedSignalKeys: new Set(),
        broadcastChannel: null,
        peers: {}, // Host: map of viewerId -> RTCPeerConnection
        hostIceQueues: {}, // Host: map of viewerId -> candidate[]
        peerConnection: null, // Viewer: RTCPeerConnection
        iceCandidateQueue: [],

        // WebRTC ICE serverlari: STUN (ochiq tarmoqlar) + TURN (qattiq NAT/firewall uchun majburiy).
        // TURN'siz turli tarmoqlarda (Wi-Fi <-> mobil) P2P ulanish o'rnatilmaydi — video/audio yetmaydi.
        // TURN sozlamalari WEBRTC_TURN_* orqali beriladi; OpenRelay defaultlari faqat sinov/zaxira uchun.
        rtcConfig: {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun.cloudflare.com:3478' },
                @if (!empty(config('webrtc.turn.urls')))
                @json(config('webrtc.turn')),
                @endif
            ],
            iceCandidatePoolSize: 10,
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

                // Periodic heartbeat from Host to announce presence and prune inactive viewers
                this.heartbeatInterval = setInterval(() => {
                    if (this.streamEnded) {
                        clearInterval(this.heartbeatInterval);
                        return;
                    }
                    this.sendSignal('stream-status', 'all', {
                        isVideoOn: this.isVideoOn,
                        isMicOn: this.isMicOn,
                        isHostOnline: true
                    });

                    // Prune inactive viewers (no ping in last 7 seconds)
                    const now = Date.now();
                    for (const [id, ts] of this.activeViewers.entries()) {
                        if (now - ts > 7000) {
                            this.activeViewers.delete(id);
                            if (this.peers && this.peers[id]) {
                                try { this.peers[id].close(); } catch(e) {}
                                delete this.peers[id];
                            }
                        }
                    }
                    this.broadcastViewerCount();
                }, 2500);
            } else {
                // Auto-unmute for viewer on first user interaction anywhere on page (click, touch, key)
                const autoUnmuteHandler = () => {
                    this.unmuteAudio();
                };
                window.addEventListener('click', autoUnmuteHandler, { passive: true });
                window.addEventListener('touchstart', autoUnmuteHandler, { passive: true });
                window.addEventListener('keydown', autoUnmuteHandler, { passive: true });

                // Viewer startup: send join announcement to host
                this.sendSignal('join', 'host:*', { ts: Date.now() });

                // Periodic presence ping every 3 seconds to keep viewer count fresh on host
                this.viewerPingInterval = setInterval(() => {
                    if (this.streamEnded) {
                        clearInterval(this.viewerPingInterval);
                        return;
                    }
                    this.sendSignal('viewer-ping', 'host:*', { ts: Date.now() });
                }, 3000);

                // Notify host immediately when tab is closed or navigated away
                const handleLeave = () => {
                    this.sendSignal('leave', 'host:*', { ts: Date.now() });
                };
                window.addEventListener('beforeunload', handleLeave);
                window.addEventListener('pagehide', handleLeave);

                // Retry join only if we still have no remote stream AND peer is not already connecting
                this.heartbeatInterval = setInterval(() => {
                    if (this.hasRemoteStream) {
                        // Already connected — stop heartbeat entirely
                        clearInterval(this.heartbeatInterval);
                        return;
                    }
                    const pc = this.peerConnection;
                    const pcState = pc ? pc.connectionState : null;
                    // Do NOT send join if peer is currently connecting (would cause loop)
                    if (pcState === 'connecting' || pcState === 'new') return;
                    // If connected but no remote stream yet — also skip (tracks might still be arriving)
                    if (pcState === 'connected') return;

                    // Only retry on failed/closed/null state
                    this.connectionAttempts++;
                    console.log('[VIEWER] Retrying join (attempt #' + this.connectionAttempts + ', peer=' + (pcState ?? 'none') + ')');
                    this.sendSignal('join', 'host:*', { ts: Date.now() });
                }, 4000); // increased to 4s to reduce signal storm
            }

            // Scroll chat to bottom
            this.scrollChat();
            window.addEventListener('livewire:load', () => {
                if (window.Livewire) {
                    window.Livewire.hook('message.processed', () => this.scrollChat());
                }
            });
        },

        reconnect() {
            this.connectionAttempts++;
            this.hasRemoteStream = false;
            this.isConnecting = true;
            if (this.peerConnection) {
                try { this.peerConnection.close(); } catch (e) {}
                this.peerConnection = null;
            }
            if (this.remoteStream) {
                try {
                    this.remoteStream.getTracks().forEach(t => t.stop());
                } catch(e) {}
                this.remoteStream = null;
            }
            this.sendSignal('join', 'host:*', { ts: Date.now() });
        },

        scrollChat() {
            const chatEl = document.getElementById('liveChatScroll');
            if (chatEl) {
                chatEl.scrollTop = chatEl.scrollHeight;
            }
        },

        // Viewer faqat KUZATUVCHI: hostning kamera/mikrofon holatini oladi,
        // hech qachon teskari signallar (stream-status) yubormaydi.
        // Aks holda viewer signali hostda toggleMic/toggleVideo metodlariga tushib,
        // host kamerasi/mikrofoni o'z-o'zidan o'chib-qoladi.
        handleViewerStreamStatus(payload) {
            if (typeof payload.isVideoOn === 'boolean') {
                this.isVideoOn = payload.isVideoOn;
            }
            if (typeof payload.isMicOn === 'boolean') {
                this.isMicOn = payload.isMicOn;
            }
            if (payload.isHostOnline) {
                this.isHostOnline = true;
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
            this.pollInterval = setInterval(() => this.pollSignals(), 700);
        },

        async pollSignals() {
            if (!this.signalPollUrl) return;
            try {
                const url = `${this.signalPollUrl}?peer_id=${encodeURIComponent(this.myPeerId)}&since_id=${this.lastSignalId}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.status === 404) {
                    // Efir o'chirilgan (host tugatgan) — "Efir tugadi" ekranini ko'rsat
                    this.showStreamEnded();
                    return;
                }
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

        // Efir tugadi: polling/WebRTC to'xtatiladi, "Efir tugadi" overlay ko'rsatiladi
        showStreamEnded() {
            if (this.streamEnded) return;
            this.streamEnded = true;
            console.log('[LIVE] Efir tugadi — overlay ko\'rsatilmoqda');
            // Barcha polling/heartbeat/WebRTC ni to'xtatish
            if (this.pollInterval) clearInterval(this.pollInterval);
            if (this.heartbeatInterval) clearInterval(this.heartbeatInterval);
            if (this.broadcastChannel) { try { this.broadcastChannel.close(); } catch (e) {} }
            if (this.peerConnection) { try { this.peerConnection.close(); } catch (e) {} }
            Object.values(this.peers).forEach(pc => { try { pc.close(); } catch (e) {} });
            if (this.localStream) { try { this.localStream.getTracks().forEach(t => t.stop()); } catch (e) {} }
            if (this.remoteStream) { try { this.remoteStream.getTracks().forEach(t => t.stop()); } catch (e) {} }
        },

        async sendSignal(type, receiverId, payload) {
            const msgId = 'sig_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);

            // WebRTC objects (RTCIceCandidate, RTCSessionDescription) have prototype getters.
            // Using object spread {...payload} drops these getters. We must serialize them via toJSON() or extract them.
            let plainPayload = payload;
            if (payload && typeof payload.toJSON === 'function') {
                plainPayload = payload.toJSON();
            } else if (payload && typeof payload === 'object' && ('candidate' in payload || 'sdp' in payload)) {
                plainPayload = {
                    candidate: payload.candidate,
                    sdpMid: payload.sdpMid,
                    sdpMLineIndex: payload.sdpMLineIndex,
                    usernameFragment: payload.usernameFragment,
                    type: payload.type,
                    sdp: payload.sdp,
                    ...payload
                };
            }

            const wrappedPayload = (plainPayload && typeof plainPayload === 'object')
                ? { ...plainPayload, _msg_id: msgId }
                : { data: plainPayload, _msg_id: msgId };

            const msg = {
                msg_id: msgId,
                sender_id: this.myPeerId,
                receiver_id: receiverId,
                type: type,
                payload: wrappedPayload
            };

            // 1. BroadcastChannel dispatch (instant local)
            if (this.broadcastChannel) {
                try {
                    this.broadcastChannel.postMessage(msg);
                } catch (e) {}
            }

            // 2. HTTP POST dispatch (persistent & cross-browser)
            if (this.signalSendUrl) {
                try {
                    await fetch(this.signalSendUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken || ''
                        },
                        body: JSON.stringify({
                            sender_id: msg.sender_id,
                            receiver_id: msg.receiver_id,
                            type: msg.type,
                            payload: msg.payload
                        })
                    });
                } catch (e) {}
            }
        },

        async handleSignalMessage(msg) {
            if (!msg || !msg.type || msg.sender_id === this.myPeerId) return;

            // Universal deduplication: matches both BroadcastChannel and HTTP polling
            const payload = msg.payload || {};
            const dedupeKey = msg.msg_id
                || payload._msg_id
                || (msg.id ? `db_${msg.id}` : null)
                || `${msg.sender_id}_${msg.type}_${payload.type || ''}`;

            if (this.processedSignalKeys.has(dedupeKey)) return;
            this.processedSignalKeys.add(dedupeKey);
            if (this.processedSignalKeys.size > 500) {
                const first = this.processedSignalKeys.values().next().value;
                this.processedSignalKeys.delete(first);
            }

            // Universal real-time stream signals: Likes
            if (msg.type === 'like') {
                if (payload && typeof payload.total === 'number') {
                    // Server totali + hozirgacha yuborilmagan taplarimiz
                    this.likesCount = payload.total + (this.pendingLikes || 0);
                }
                // O'z batch'imiz qaytganda qayta yurak chizmaymiz (tap paytida chizilgan edi)
                if (msg.sender_id !== ('server_' + this.userId)) {
                    this.spawnFloatingHeart(false);
                }
                return;
            }

            // Universal real-time stream signals: Viewer Count
            if (msg.type === 'viewer-count') {
                if (payload && typeof payload.count === 'number') {
                    this.updateViewerCount(payload.count);
                }
                return;
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

            // Track real-time viewer presence on ANY viewer signal
            if (viewerId && viewerId.startsWith('viewer_')) {
                if (msg.type === 'leave') {
                    this.activeViewers.delete(viewerId);
                    if (this.peers && this.peers[viewerId]) {
                        try { this.peers[viewerId].close(); } catch (e) {}
                        delete this.peers[viewerId];
                    }
                    this.broadcastViewerCount();
                    return;
                }

                const isNew = !this.activeViewers.has(viewerId);
                this.activeViewers.set(viewerId, Date.now());
                if (isNew) {
                    this.broadcastViewerCount();
                }
            }

            if (msg.type === 'viewer-ping') {
                return;
            }

            if (msg.type === 'join') {
                await this.createPeerForViewer(viewerId);
            } else if (msg.type === 'answer') {
                const pc = this.peers[viewerId];
                if (pc && pc.signalingState !== 'stable') {
                    try {
                        const cleanAnswer = {
                            type: msg.payload.type,
                            sdp: normalizeSdp(msg.payload.sdp)
                        };
                        await pc.setRemoteDescription(new RTCSessionDescription(cleanAnswer));
                        this.applyVideoSenderQuality(pc);

                        // Drain queued ICE candidates if any arrived early
                        if (this.hostIceQueues && this.hostIceQueues[viewerId] && this.hostIceQueues[viewerId].length > 0) {
                            while (this.hostIceQueues[viewerId].length > 0) {
                                const cand = this.hostIceQueues[viewerId].shift();
                                try { await pc.addIceCandidate(new RTCIceCandidate(cand)); } catch(e) {}
                            }
                        }
                    } catch (e) {
                        console.warn('Host setRemoteDescription error:', e);
                    }
                }
            } else if (msg.type === 'ice-candidate') {
                const pc = this.peers[viewerId];
                const candData = msg.payload?.candidate !== undefined ? msg.payload : (msg.payload?.data || null);
                if (candData && candData.candidate) {
                    const rtcCand = new RTCIceCandidate({
                        candidate: candData.candidate,
                        sdpMid: candData.sdpMid ?? '0',
                        sdpMLineIndex: candData.sdpMLineIndex ?? 0
                    });
                    if (pc && pc.remoteDescription && pc.remoteDescription.type) {
                        try {
                            await pc.addIceCandidate(rtcCand);
                            console.log('[HOST → ' + viewerId + '] ICE candidate added');
                        } catch (e) {
                            console.warn('[HOST] addIceCandidate error:', e);
                        }
                    } else {
                        if (!this.hostIceQueues) this.hostIceQueues = {};
                        if (!this.hostIceQueues[viewerId]) this.hostIceQueues[viewerId] = [];
                        this.hostIceQueues[viewerId].push(rtcCand);
                    }
                }
            }
        },

        async createPeerForViewer(viewerId) {
            const activeStream = this.isScreenSharing ? this.screenStream : this.localStream;
            if (!activeStream) {
                this.pendingViewerIds.add(viewerId);
                return;
            }

            // ── GUARD: Do NOT recreate a healthy peer connection ──
            // Only close and recreate if the existing peer is in a terminal/bad state
            const existingPc = this.peers[viewerId];
            if (existingPc) {
                const state = existingPc.connectionState;
                if (state === 'new' || state === 'connecting' || state === 'connected') {
                    console.log('[HOST → ' + viewerId + '] Peer already in state "' + state + '", skipping recreate.');
                    return;
                }
                // Terminal state (failed/closed/disconnected) — close and recreate
                try { existingPc.close(); } catch (e) {}
            }

            const pc = new RTCPeerConnection(this.rtcConfig);
            this.peers[viewerId] = pc;

            // Ensure tracks reflect current mute/unmute state before adding
            activeStream.getAudioTracks().forEach(t => { t.enabled = this.isMicOn; });
            activeStream.getVideoTracks().forEach(t => { t.enabled = this.isVideoOn; });

            // Add audio & video tracks from host active stream
            activeStream.getTracks().forEach(track => {
                try { pc.addTrack(track, activeStream); } catch(e) {}
            });

            // If screen sharing is active, ensure host local microphone audio is also included
            if (this.isScreenSharing && this.localStream) {
                const micTrack = this.localStream.getAudioTracks()[0];
                if (micTrack && !pc.getSenders().some(s => s.track && s.track.kind === 'audio')) {
                    micTrack.enabled = this.isMicOn;
                    try { pc.addTrack(micTrack, this.localStream); } catch(e) {}
                }
            }

            // [DEBUG] Log local tracks being sent to viewer
            console.log('[HOST → ' + viewerId + '] Local audio tracks:', activeStream.getAudioTracks().map(t => t.label + ' enabled=' + t.enabled));
            console.log('[HOST → ' + viewerId + '] Local video tracks:', activeStream.getVideoTracks().map(t => t.label + ' enabled=' + t.enabled));
            console.log('[HOST → ' + viewerId + '] RTCPeerConnection senders:', pc.getSenders().map(s => s.track?.kind ?? 'no-track'));

            // Eslatma: bitreyt sozlamasi (applyVideoSenderQuality) answer qabul qilingandan
            // keyin qo'llanadi — negotiationdan oldin encodings bo'sh bo'ladi va setParameters rad etiladi.

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    const candData = event.candidate.toJSON ? event.candidate.toJSON() : {
                        candidate: event.candidate.candidate,
                        sdpMid: event.candidate.sdpMid,
                        sdpMLineIndex: event.candidate.sdpMLineIndex
                    };
                    console.log('[HOST → ' + viewerId + '] ICE candidate generated:', candData.candidate ? candData.candidate.slice(0, 35) : 'null');
                    this.sendSignal('ice-candidate', viewerId, candData);
                }
            };

            pc.onconnectionstatechange = () => {
                console.log('[HOST → ' + viewerId + '] Connection state:', pc.connectionState);
                if (pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                    delete this.peers[viewerId];
                }
            };

            pc.oniceconnectionstatechange = () => {
                console.log('[HOST → ' + viewerId + '] ICE state:', pc.iceConnectionState);
            };

            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                await this.sendSignal('offer', viewerId, {
                    type: offer.type,
                    sdp: normalizeSdp(offer.sdp)
                });

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
                await this.handleOfferFromHost(msg.payload, msg.sender_id);
            } else if (msg.type === 'ice-candidate') {
                if (this.offerHostPeerId && msg.sender_id !== this.offerHostPeerId && !msg.sender_id.startsWith('host_')) {
                    return;
                }
                const candData = msg.payload?.candidate !== undefined ? msg.payload : (msg.payload?.data || null);
                if (candData && candData.candidate) {
                    const rtcCand = new RTCIceCandidate({
                        candidate: candData.candidate,
                        sdpMid: candData.sdpMid ?? '0',
                        sdpMLineIndex: candData.sdpMLineIndex ?? 0
                    });
                    if (this.peerConnection && this.peerConnection.remoteDescription) {
                        try {
                            await this.peerConnection.addIceCandidate(rtcCand);
                            console.log('[VIEWER] ICE candidate added from host');
                        } catch (e) {
                            console.warn('[VIEWER] addIceCandidate error:', e);
                        }
                    } else {
                        this.iceCandidateQueue.push(rtcCand);
                    }
                }
            } else if (msg.type === 'stream-status') {
                // Faqat hostdan kelgan status qabul qilinadi — viewer o'zi hech qachon
                // stream-status yubormaydi (aks holda host tugmalari o'z-o'zidan o'chib qoladi).
                this.handleViewerStreamStatus(msg.payload || {});
            }
        },

        async handleOfferFromHost(offer, senderId = null) {
            if (!offer || !offer.sdp) return;

            // ── GUARD: healthy ulanishni buzmaslik + bir xil hostdan duplicate offerni tashlash ──
            // Eslatma: bir xil hostdan KELGAN yangi offer (host kamera/qurilma qayta ulaganda
            // renegotiation) — qabul qilinadi: eski peer yopilib, yangisi bilan davom etamiz.
            if (this.peerConnection) {
                const state = this.peerConnection.connectionState;
                const sigState = this.peerConnection.signalingState;
                const sameHost = !senderId || senderId === this.offerHostPeerId;
                if (state === 'connecting') {
                    // Ulanish alla ishlayapti — boshqa hostning (ko-host) offersini e'tiborsiz qoldiramiz
                    if (!sameHost) {
                        console.log('[VIEWER] Ignoring offer from secondary host — already connecting to primary');
                        return;
                    }
                    console.log('[VIEWER] Ignoring duplicate offer — connecting:', state);
                    return;
                }
                if (sigState === 'have-remote-offer' && sameHost) {
                    console.log('[VIEWER] Ignoring duplicate offer from same host');
                    return;
                }
                // Renegotiation yoki terminal holat: eski peer'ni yopib yangisini quramiz
                if (state === 'connected' || this.hasRemoteStream) {
                    console.log('[VIEWER] Renegotiation offer from host — re-establishing connection');
                }
                try { this.peerConnection.close(); } catch (e) {}
            }
            this.offerHostPeerId = senderId;

            const pc = new RTCPeerConnection(this.rtcConfig);
            this.peerConnection = pc;

            // Reset remote stream for new connection
            this.remoteStream = new MediaStream();

            pc.ontrack = (event) => {
                const track = event.track;
                if (!track) return;

                console.log('[VIEWER] ontrack received:', track.kind, 'id=' + track.id, 'readyState=' + track.readyState, 'enabled=' + track.enabled);

                // Add track to persistent remoteStream
                if (!this.remoteStream.getTracks().some(t => t.id === track.id)) {
                    this.remoteStream.addTrack(track);
                }

                console.log('[VIEWER] remoteStream tracks:', this.remoteStream.getTracks().map(t => t.kind + '(enabled=' + t.enabled + ')'));

                if (track.kind === 'video') {
                    this.isVideoOn = true;
                }
                if (track.kind === 'audio') {
                    this.isMicOn = true;
                    this.setupAudioAnalyser(new MediaStream([track]));
                }

                this.hasRemoteStream = true;
                this.isConnecting = false;

                // Debounce attachment by 60ms so both audio & video tracks arrive before assigning srcObject,
                // completely eliminating the "AbortError: interrupted by new load request"
                clearTimeout(this._attachTimer);
                this._attachTimer = setTimeout(() => {
                    this.playRemoteStream();
                }, 60);
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    const candData = event.candidate.toJSON ? event.candidate.toJSON() : {
                        candidate: event.candidate.candidate,
                        sdpMid: event.candidate.sdpMid,
                        sdpMLineIndex: event.candidate.sdpMLineIndex
                    };
                    const targetHostId = this.offerHostPeerId || 'host:*';
                    console.log('[VIEWER → ' + targetHostId + '] ICE candidate generated:', candData.candidate ? candData.candidate.slice(0, 35) : 'null');
                    this.sendSignal('ice-candidate', targetHostId, candData);
                }
            };

            pc.onconnectionstatechange = () => {
                console.log('[VIEWER] Connection state:', pc.connectionState);
            };

            pc.oniceconnectionstatechange = () => {
                console.log('[VIEWER] ICE state:', pc.iceConnectionState);
            };

            try {
                // Ensure RFC 4566 compliant CRLF SDP line formatting
                const cleanOffer = {
                    type: offer.type,
                    sdp: normalizeSdp(offer.sdp)
                };
                await pc.setRemoteDescription(new RTCSessionDescription(cleanOffer));

                console.log('[VIEWER] setRemoteDescription done. Offer SDP has audio?', cleanOffer.sdp.includes('m=audio'));
                console.log('[VIEWER] Receivers after setRemoteDescription:', pc.getReceivers().map(r => r.track?.kind ?? 'no-track'));

                while (this.iceCandidateQueue.length > 0) {
                    const cand = this.iceCandidateQueue.shift();
                    try {
                        await pc.addIceCandidate(cand instanceof RTCIceCandidate ? cand : new RTCIceCandidate(cand));
                        console.log('[VIEWER] Drained queued ICE candidate');
                    } catch (e) {}
                }

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);

                console.log('[VIEWER] Answer SDP has audio?', answer.sdp.includes('m=audio'));

                await this.sendSignal('answer', 'host:*', {
                    type: answer.type,
                    sdp: normalizeSdp(answer.sdp)
                });
            } catch (err) {
                console.error('Viewer error processing host offer:', err);
            }
        },

        playRemoteStream() {
            if (!this.remoteStream) return;
            const videoEl = document.getElementById('liveVideoPlayer');
            const audioEl = document.getElementById('liveAudioPlayer');

            if (this.remoteStream.getVideoTracks().length > 0) {
                this.isVideoOn = true;
            }
            if (this.remoteStream.getAudioTracks().length > 0) {
                this.isMicOn = true;
            }

            // 1. Primary Video Player (Video display + Speaker audio)
            if (videoEl) {
                if (videoEl.srcObject !== this.remoteStream) {
                    videoEl.srcObject = this.remoteStream;
                }
                videoEl.volume = 1.0;
                videoEl.muted = this.isViewerMuted;

                const vPlay = videoEl.play();
                if (vPlay !== undefined) {
                    vPlay.then(() => {
                        console.log('[VIEWER] videoEl playback ACTIVE (unmuted)');
                        this.needsUnmute = false;
                    }).catch(err => {
                        if (err.name === 'AbortError') {
                            setTimeout(() => { if (videoEl) videoEl.play().catch(() => {}); }, 120);
                            return;
                        }
                        console.warn('[VIEWER] Autoplay policy blocked unmuted video, starting muted playback:', err);
                        videoEl.muted = true;
                        videoEl.play().catch(() => {});
                        this.needsUnmute = true;
                    });
                }
            }

            // 2. Fallback Audio Player
            const audioTracks = this.remoteStream.getAudioTracks();
            if (audioEl && audioTracks.length > 0) {
                const aStream = new MediaStream([audioTracks[0]]);
                if (audioEl.srcObject !== aStream) {
                    audioEl.srcObject = aStream;
                }
                audioEl.volume = 1.0;
                audioEl.muted = this.isViewerMuted;

                const aPlay = audioEl.play();
                if (aPlay !== undefined) {
                    aPlay.then(() => {
                        console.log('[VIEWER] audioEl playback ACTIVE');
                    }).catch(err => {
                        if (err.name !== 'AbortError') {
                            this.needsUnmute = true;
                        }
                    });
                }
            }
        },

        unmuteAudio() {
            this.isViewerMuted = false;
            this.needsUnmute = false;

            const videoEl = document.getElementById('liveVideoPlayer');
            const audioEl = document.getElementById('liveAudioPlayer');

            if (videoEl) {
                videoEl.muted = false;
                videoEl.volume = 1.0;
                videoEl.play().catch(() => {});
            }
            if (audioEl) {
                audioEl.muted = false;
                audioEl.volume = 1.0;
                audioEl.play().catch(() => {});
            }
            if (this.audioContext && this.audioContext.state === 'suspended') {
                this.audioContext.resume().catch(() => {});
            }
            console.log('[VIEWER] unmuteAudio: all players unmuted, volume=1.0');
        },

        toggleViewerMute() {
            this.isViewerMuted = !this.isViewerMuted;
            const audioEl = document.getElementById('liveAudioPlayer');
            const videoEl = document.getElementById('liveVideoPlayer');
            if (audioEl) audioEl.muted = this.isViewerMuted;
            if (videoEl) videoEl.muted = this.isViewerMuted;
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

        get videoFilterStyle() {
            if (this.videoEnhance === 'bright') {
                return 'filter: contrast(1.18) saturate(1.22) brightness(1.12);';
            }
            if (this.videoEnhance === 'sharp') {
                return 'filter: contrast(1.25) saturate(1.10) brightness(1.02);';
            }
            if (this.videoEnhance === 'hd' || this.videoEnhance === true || this.videoEnhance === 'true') {
                return 'filter: contrast(1.12) saturate(1.15) brightness(1.04);';
            }
            return '';
        },

        setVideoEnhance(mode) {
            this.videoEnhance = mode;
            try { localStorage.setItem('kitob_video_enhance', mode); } catch (e) {}
        },

        updateCameraStatus(track) {
            const videoEl = document.getElementById('liveVideoPlayer');
            let w = 0;
            let h = 0;
            let fps = 30;

            if (track) {
                const settings = track.getSettings ? track.getSettings() : {};
                w = settings.width || 0;
                h = settings.height || 0;
                fps = Math.round(settings.frameRate || 30);
            }

            if ((!w || !h) && videoEl && videoEl.videoWidth && videoEl.videoHeight) {
                w = videoEl.videoWidth;
                h = videoEl.videoHeight;
            }

            if (!track || (!w && !h && !this.isVideoOn)) {
                this.currentCamResolution = '';
                return;
            }

            if (w && h) {
                let tag = 'SD';
                if (w >= 1920 || h >= 1080) tag = 'FHD';
                else if (w >= 1280 || h >= 720) tag = 'HD';
                this.currentCamResolution = `${tag} ${w}×${h} (${fps}fps)`;
            } else {
                this.currentCamResolution = this.isVideoOn ? 'Faol' : '';
            }
            console.log('[CAMERA STATUS]', this.currentCamResolution);
        },

        getVideoConstraints(quality = this.videoQuality || '1080p') {
            const deviceIdConstraint = this.selectedVideoDevice ? { ideal: this.selectedVideoDevice } : undefined;

            if (quality === '1080p') {
                return {
                    deviceId: deviceIdConstraint,
                    width: { ideal: 1920, min: 1280 },
                    height: { ideal: 1080, min: 720 },
                    aspectRatio: { ideal: 1.7777777778 }
                };
            } else if (quality === '720p') {
                return {
                    deviceId: deviceIdConstraint,
                    width: { ideal: 1280, min: 960, max: 1280 },
                    height: { ideal: 720, min: 540, max: 720 },
                    aspectRatio: { ideal: 1.7777777778 }
                };
            } else {
                // 480p SD
                return {
                    deviceId: deviceIdConstraint,
                    width: { ideal: 640, max: 854 },
                    height: { ideal: 480, max: 480 },
                    aspectRatio: { ideal: 1.3333333333 }
                };
            }
        },

        async acquireVideoStream(constraints) {
            const deviceIdConstraint = this.selectedVideoDevice ? { ideal: this.selectedVideoDevice } : undefined;

            // 1-urinish: So'ralgan aniqlik bo'yicha
            try {
                return await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: constraints
                });
            } catch (err1) {
                console.warn('[CAMERA] 1-urinish olinmadi:', err1);
                await new Promise(r => setTimeout(r, 100));
            }

            // 2-urinish: Keng imkoniyatli HD (qattiq min/max cheklovlarsiz)
            try {
                return await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: {
                        deviceId: deviceIdConstraint,
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        aspectRatio: { ideal: 1.7777777778 }
                    }
                });
            } catch (err2) {
                console.warn('[CAMERA] 2-urinish (720p ideal) olinmadi:', err2);
                await new Promise(r => setTimeout(r, 100));
            }

            // 3-urinish: Standart kamera
            return await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: deviceIdConstraint ? { deviceId: deviceIdConstraint } : true
            });
        },

        async changeVideoQuality(quality) {
            this.videoQuality = quality;
            try { localStorage.setItem('kitob_video_quality', quality); } catch (e) {}

            if (!this.localStream || this.isScreenSharing) return;

            const constraints = this.getVideoConstraints(quality);

            // 1-bosqich: applyConstraints orqali tezkor urinish
            const currentTrack = this.localStream.getVideoTracks()[0];
            if (currentTrack && currentTrack.applyConstraints) {
                try {
                    await currentTrack.applyConstraints(constraints);
                    const s = currentTrack.getSettings ? currentTrack.getSettings() : {};
                    if (s.height && (quality === '480p' ? s.height <= 500 : (quality === '720p' ? s.height <= 750 : true))) {
                        this.updateCameraStatus(currentTrack);
                        this.replaceTracksOnAllPeers(this.localStream);
                        return;
                    }
                } catch (applyErr) {
                    console.warn('[CAMERA] applyConstraints orqali olinmadi, apparat oqimi qayta ulanmoqda:', applyErr);
                }
            }

            // 2-bosqich: Windows DirectShow/MediaFoundation apparat oqimini to'liq qayta ulash
            const oldTracks = this.localStream.getVideoTracks();
            oldTracks.forEach(t => {
                this.localStream.removeTrack(t);
                try { t.stop(); } catch (e) {}
            });

            // Windows drayveri kamerani bo'shatishi uchun 180ms kutish
            await new Promise(resolve => setTimeout(resolve, 180));

            try {
                const newVideoStream = await this.acquireVideoStream(constraints);
                const newVideoTrack = newVideoStream.getVideoTracks()[0];
                if (newVideoTrack) {
                    newVideoTrack.enabled = this.isVideoOn;

                    const audioTracks = this.localStream.getAudioTracks();
                    this.localStream = new MediaStream([...audioTracks, newVideoTrack]);

                    const videoEl = document.getElementById('liveVideoPlayer');
                    if (videoEl) {
                        videoEl.srcObject = null;
                        videoEl.srcObject = this.localStream;
                        videoEl.play().catch(() => {});
                    }

                    this.updateCameraStatus(newVideoTrack);
                    this.replaceTracksOnAllPeers(this.localStream);
                }
            } catch (err) {
                console.error('Kamera sifatini almashtirishda xatolik:', err);
                try {
                    const fallbackStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                    const fbTrack = fallbackStream.getVideoTracks()[0];
                    if (fbTrack) {
                        fbTrack.enabled = this.isVideoOn;
                        this.localStream.addTrack(fbTrack);
                        const videoEl = document.getElementById('liveVideoPlayer');
                        if (videoEl) {
                            videoEl.srcObject = null;
                            videoEl.srcObject = this.localStream;
                            videoEl.play().catch(() => {});
                        }
                        this.updateCameraStatus(fbTrack);
                        this.replaceTracksOnAllPeers(this.localStream);
                    }
                } catch (e2) {}
            }
        },

        // Tomoshabinga ketayotgan video oqim bitreytini oshirish.
        // Faqat negotiationdan KEYIN ishlaydi (undan oldin encodings bo'sh).
        applyVideoSenderQuality(pc) {
            if (!pc || !pc.getSenders) return;
            const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
            if (!sender || !sender.getParameters) return;
            try {
                const params = sender.getParameters();
                if (!params.encodings || params.encodings.length === 0) return;
                params.encodings[0].maxBitrate = 3500000; // 3.5 Mbps — Full HD / HD yuz-kadr uchun tiniq
                params.encodings[0].maxFramerate = 30;
                params.degradationPreference = 'maintain-resolution'; // tarmoq sekinlashsa — xiralashmasin, FPS kamaysin
                sender.setParameters(params).catch(err => console.warn('[RTC] setParameters:', err));
            } catch (e) {}
        },

        toggleVideoEnhance() {
            this.videoEnhance = !this.videoEnhance;
            try { localStorage.setItem('kitob_video_enhance', this.videoEnhance); } catch (e) {}
        },

        async startMediaStream() {
            try {
                if (this.localStream) {
                    this.localStream.getTracks().forEach(t => t.stop());
                }

                const audioConstraints = this.selectedAudioDevice ? { deviceId: { ideal: this.selectedAudioDevice } } : true;
                let stream = null;

                try {
                    // 1. Audio va Full HD / HD video birgalikda so'raladi
                    stream = await navigator.mediaDevices.getUserMedia({
                        audio: audioConstraints,
                        video: this.getVideoConstraints()
                    });
                } catch (e1) {
                    console.warn('Audio va video birgalikda olinmadi, alohida olinmoqda:', e1);
                    await new Promise(r => setTimeout(r, 100));

                    let audioStream = null;
                    try {
                        audioStream = await navigator.mediaDevices.getUserMedia({ audio: audioConstraints });
                    } catch (ea) {
                        console.warn('Audio olinmadi:', ea);
                        this.isMicOn = false;
                    }

                    let videoStream = null;
                    try {
                        videoStream = await this.acquireVideoStream(this.getVideoConstraints());
                    } catch (ev) {
                        console.warn('Video olinmadi:', ev);
                        this.isVideoOn = false;
                    }

                    if (audioStream && videoStream) {
                        stream = new MediaStream([
                            ...audioStream.getAudioTracks(),
                            ...videoStream.getVideoTracks()
                        ]);
                    } else {
                        stream = audioStream || videoStream;
                    }
                }

                if (!stream) {
                    console.warn('Hech qanday media oqim topilmadi');
                    return;
                }

                this.localStream = stream;

                // Haqiqiy olingan kamera aniqligi va kadr tezligini konsolga chiqarish
                const vTrack = this.localStream.getVideoTracks()[0];
                if (vTrack) {
                    this.updateCameraStatus(vTrack);
                }

                // Sync track enabled state with UI toggles
                this.localStream.getAudioTracks().forEach(t => t.enabled = this.isMicOn);
                this.localStream.getVideoTracks().forEach(t => t.enabled = this.isVideoOn);

                const videoEl = document.getElementById('liveVideoPlayer');
                if (videoEl) {
                    videoEl.muted = true; // Host local preview is muted to prevent acoustic feedback loop
                    videoEl.srcObject = null;
                    videoEl.srcObject = stream;
                    videoEl.play().catch(() => {});
                }

                this.setupAudioAnalyser(stream);
                await this.scanMediaDevices();

                // Process any viewers that joined before localStream was ready
                if (this.pendingViewerIds.size > 0) {
                    this.pendingViewerIds.forEach(vid => this.createPeerForViewer(vid));
                    this.pendingViewerIds.clear();
                }

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
                            if (newTrack.kind === 'video') this.applyVideoSenderQuality(pc);
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

                if (this._vuAnimFrame) {
                    cancelAnimationFrame(this._vuAnimFrame);
                }

                const source = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 64;
                this.analyser.smoothingTimeConstant = 0.4;
                source.connect(this.analyser);

                const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
                const updateVolume = () => {
                    const isMuted = (this.isHost && !this.isMicOn) || (!this.isHost && (this.isViewerMuted || this.needsUnmute));
                    if (isMuted || !this.analyser) {
                        this.audioVolume = 0;
                        this._vuAnimFrame = requestAnimationFrame(updateVolume);
                        return;
                    }
                    this.analyser.getByteFrequencyData(dataArray);
                    // Inson nutqi chastotalari (1-16 bin oralig'i)
                    const vocalBins = Math.min(16, dataArray.length);
                    let sum = 0;
                    let peak = 0;
                    for (let i = 0; i < vocalBins; i++) {
                        sum += dataArray[i];
                        if (dataArray[i] > peak) peak = dataArray[i];
                    }
                    const avg = sum / vocalBins;
                    const level = (avg * 0.6) + (peak * 0.4);
                    const targetVol = level > 3 ? Math.min(100, Math.floor((level / 120) * 100)) : 0;
                    // Silliq harakat (smooth lerp)
                    this.audioVolume = Math.round(this.audioVolume * 0.25 + targetVol * 0.75);
                    this._vuAnimFrame = requestAnimationFrame(updateVolume);
                };
                updateVolume();
            } catch (e) {}
        },

        async changeAudioSource(deviceId) {
            this.selectedAudioDevice = deviceId;
            if (this.localStream) {
                try {
                    const newAudioStream = await navigator.mediaDevices.getUserMedia({
                        audio: { deviceId: { exact: deviceId } }
                    });
                    const newAudioTrack = newAudioStream.getAudioTracks()[0];
                    newAudioTrack.enabled = this.isMicOn;

                    const oldTracks = this.localStream.getAudioTracks();
                    oldTracks.forEach(t => {
                        t.stop();
                        this.localStream.removeTrack(t);
                    });

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
                oldTracks.forEach(t => {
                    try { t.stop(); } catch (e) {}
                });

                await new Promise(r => setTimeout(r, 150));

                try {
                    const newVideoStream = await this.acquireVideoStream(this.getVideoConstraints());
                    const newVideoTrack = newVideoStream.getVideoTracks()[0];
                    if (newVideoTrack) {
                        newVideoTrack.enabled = this.isVideoOn;

                        const audioTracks = this.localStream.getAudioTracks();
                        this.localStream = new MediaStream([...audioTracks, newVideoTrack]);

                        const videoEl = document.getElementById('liveVideoPlayer');
                        if (videoEl) {
                            videoEl.srcObject = null;
                            videoEl.srcObject = this.localStream;
                            videoEl.play().catch(() => {});
                        }

                        this.updateCameraStatus(newVideoTrack);
                        this.replaceTracksOnAllPeers(this.localStream);
                    }
                } catch (e) {
                    console.error('Kamera almashtirishda xatolik:', e);
                }
            }
        },

        // Track'ni barcha ulangan tomoshabinlarga yetkazish.
        // Peer'da bu turdagi sender bo'lsa — replaceTrack (uzilishsiz).
        // Bo'lmasa (masalan, audio-only offer) — yangi track renegotiation talab qiladi,
        // shuning uchun peer'ni video bilan qayta yaratamiz (yangi offer ketadi).
        attachTrackToPeers(kind, track) {
            Object.entries(this.peers).forEach(([viewerId, pc]) => {
                if (!pc || !pc.getSenders) return;
                const sender = pc.getSenders().find(s => s.track && s.track.kind === kind);
                if (sender) {
                    sender.replaceTrack(track).catch(() => {});
                } else {
                    try { pc.close(); } catch (e) {}
                    delete this.peers[viewerId];
                    this.createPeerForViewer(viewerId);
                }
            });
        },

        async toggleMic() {
            this.isMicOn = !this.isMicOn;

            const sharing = this.isScreenSharing && this.screenStream;
            const stream = sharing ? this.screenStream : this.localStream;
            const audioTracks = stream ? stream.getAudioTracks() : [];

            if (audioTracks.length > 0) {
                // Track mavjud — faqat yoqish/o'chirish
                audioTracks.forEach(t => t.enabled = this.isMicOn);
            } else if (this.isMicOn && !sharing) {
                // Audio track UMUMAN YO'Q (start paytida mikrofon olinmagan bo'lsa)
                // — endi mikrofonni foydalanuvchidan so'rab olamiz
                try {
                    const audioStream = await navigator.mediaDevices.getUserMedia({
                        audio: this.selectedAudioDevice ? { deviceId: { ideal: this.selectedAudioDevice } } : true
                    });
                    const newTrack = audioStream.getAudioTracks()[0];
                    if (newTrack) {
                        newTrack.enabled = true;
                        if (!this.localStream) this.localStream = new MediaStream();
                        const old = this.localStream.getAudioTracks()[0];
                        if (old) { this.localStream.removeTrack(old); try { old.stop(); } catch (e) {} }
                        this.localStream.addTrack(newTrack);
                        this.attachTrackToPeers('audio', newTrack);
                        this.setupAudioAnalyser(this.localStream);
                        this.scanMediaDevices();
                    }
                } catch (err) {
                    console.warn('Mikrofonni yoqib bo\'lmadi:', err);
                    this.isMicOn = false;
                }
            }

            this.sendSignal('stream-status', 'all', {
                isVideoOn: this.isVideoOn,
                isMicOn: this.isMicOn
            });
        },

        async toggleVideo() {
            this.isVideoOn = !this.isVideoOn;

            const sharing = this.isScreenSharing && this.screenStream;
            const stream = sharing ? this.screenStream : this.localStream;
            const videoTracks = stream ? stream.getVideoTracks() : [];

            if (videoTracks.length > 0) {
                // Track mavjud — faqat yoqish/o'chirish
                videoTracks.forEach(t => t.enabled = this.isVideoOn);
                if (this.isVideoOn) {
                    this.updateCameraStatus(videoTracks[0]);
                } else {
                    this.currentCamResolution = '';
                }
            } else if (this.isVideoOn && !sharing) {
                // Video track UMUMAN YO'Q (start paytida kamera band/ruhsatsiz bo'lgan,
                // yoki audio-only fallback ishlagan) — ENDI KAMERANI SO'RAMIZ
                try {
                    const videoStream = await this.acquireVideoStream(this.getVideoConstraints());
                    const newTrack = videoStream.getVideoTracks()[0];
                    if (newTrack) {
                        newTrack.enabled = true;
                        if (!this.localStream) this.localStream = new MediaStream();
                        const old = this.localStream.getVideoTracks()[0];
                        if (old) { this.localStream.removeTrack(old); try { old.stop(); } catch (e) {} }
                        this.localStream.addTrack(newTrack);

                        // Host preview: o'z kamerasini ko'rsatish
                        if (!this.isScreenSharing) {
                            const videoEl = document.getElementById('liveVideoPlayer');
                            if (videoEl) {
                                videoEl.muted = true; // echo oldini olish
                                videoEl.srcObject = null;
                                videoEl.srcObject = this.localStream;
                                videoEl.play().catch(() => {});
                            }
                        }

                        this.updateCameraStatus(newTrack);
                        // Tomoshabinlarga yangi video track yetkazish
                        this.attachTrackToPeers('video', newTrack);
                        this.scanMediaDevices();
                    }
                } catch (err) {
                    console.warn('Kamerani yoqib bo\'lmadi:', err);
                    // Kamera olinmadi — tugma holatini qaytarish (aldovchi "yoqilgan" bo'lmasin)
                    this.isVideoOn = false;
                    this.currentCamResolution = '';
                }
            } else {
                this.currentCamResolution = '';
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

                    // If host has mic in localStream, add mic track to screenStream so audio continues
                    if (this.localStream) {
                        const micTrack = this.localStream.getAudioTracks()[0];
                        if (micTrack && screenStream.getAudioTracks().length === 0) {
                            screenStream.addTrack(micTrack);
                        }
                    }

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
                    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=h264,opus')
                        ? 'video/webm;codecs=h264,opus'
                        : (MediaRecorder.isTypeSupported('video/mp4;codecs=avc1,mp4a.40.2')
                            ? 'video/mp4;codecs=avc1,mp4a.40.2'
                            : (MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')
                                ? 'video/webm;codecs=vp9,opus'
                                : (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')
                                    ? 'video/webm;codecs=vp8,opus'
                                    : 'video/webm')));

                    this.mediaRecorder = new MediaRecorder(streamToRecord, {
                        mimeType,
                        videoBitsPerSecond: 6000000, // 6.0 Mbps yuqori tiniqlikda yozib olish
                        audioBitsPerSecond: 160000   // 160 kbps toza audio
                    });

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
        },

        // ── REAL-TIME LIKES (Instagram/TikTok Live uslubi: cheksiz tap) & VIEWER COUNT ──
        burstLike() {
            // Optimistic UI: har tap = +1 like va suzuvchi yurak (server javobini kutmasdan)
            this.likesCount++;
            this.pendingLikes++;
            this.spawnFloatingHeart(true);

            // Batch/debounce: taplarni 800ms to'playmiz, so'ng BITTA so'rov bilan yuboramiz,
            // aks holda ko'p odam tez bosganda server haddan tashqari ko'p so'rov oladi.
            if (this.likeBatchTimer) return;
            this.likeBatchTimer = setTimeout(() => {
                const batch = this.pendingLikes;
                this.pendingLikes = 0;
                this.likeBatchTimer = null;
                if (batch < 1) return;
                const comp = this.$wire || (window.Livewire && this.$el ? window.Livewire.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) : null);
                if (comp && typeof comp.call === 'function') {
                    comp.call('sendLikes', batch);
                }
            }, 800);
        },

        sendLike() {
            this.burstLike();
        },

        broadcastViewerCount() {
            const count = this.activeViewers ? this.activeViewers.size : 0;
            this.updateViewerCount(count);
            this.sendSignal('viewer-count', 'all', { count: count });
        },

        spawnFloatingHeart(isLocal = true) {
            const id = Date.now() + Math.random();
            const colors = ['#F43F5E', '#FB7185', '#E11D48', '#FDA4AF', '#F59E0B', '#FBBF24'];
            const heart = {
                id: id,
                tx: (Math.random() - 0.5) * 60,
                rot: (Math.random() - 0.5) * 44,
                scale: (isLocal ? 1.0 : 0.8) + (Math.random() * 0.4),
                dur: 1400 + Math.floor(Math.random() * 600),
                color: colors[Math.floor(Math.random() * colors.length)]
            };
            this.floatingHearts.push(heart);
            if (this.floatingHearts.length > 25) {
                this.floatingHearts.shift();
            }
            setTimeout(() => {
                this.floatingHearts = this.floatingHearts.filter(h => h.id !== id);
            }, heart.dur);
        },

        updateViewerCount(newCount) {
            const target = Math.max(0, Number(newCount) || 0);
            this.viewerCount = target;
            // Kichik count-up animatsiya: raqam keskin almashmasin.
            // GSAP Alpine reaktiv proksisi ustida ishlamagani uchun yengil rAF tween ishlatamiz.
            if (this._viewerTween && this._viewerTween.cancel) {
                this._viewerTween.cancel();
            }
            const from = Number(this.animatedViewerCount) || 0;
            const start = performance.now();
            const dur = 500;
            const step = (t) => {
                const p = Math.min(1, (t - start) / dur);
                const eased = 1 - Math.pow(1 - p, 3);
                this.animatedViewerCount = from + (target - from) * eased;
                if (p < 1) {
                    this._viewerTween = requestAnimationFrame(step);
                }
            };
            this._viewerTween = requestAnimationFrame(step);
        },

        formatNumber(num) {
            num = Math.round(Number(num) || 0);
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
            }
            return String(num);
        }
    };
}
    </script>

</div>
