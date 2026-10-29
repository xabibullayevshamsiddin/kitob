        <div class="space-y-5 live-video-column">

            <!-- 0. Placeholder when Docked Mini Mode is active (keeps page layout 100% stable) -->
            <div x-show="isDockedMini" x-cloak
                 class="w-full aspect-video rounded-panel bg-ink-950/70 border-2 border-dashed border-amber-500/30 flex flex-col items-center justify-center p-6 text-center space-y-3 transition-all">
                <div class="w-12 h-12 rounded-full bg-ink-900 border border-amber-400/40 flex items-center justify-center text-amber-300 shadow-md">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M13 13h5v4h-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-paper font-serif">Video ixcham burchakda ko‘rsatilmoqda</p>
                    <p class="text-xs text-mist font-mono mt-0.5">Efirni kuzatgan holda sahifadagi barcha ma'lumotlar bilan ishlashingiz mumkin</p>
                </div>
                <button type="button" @click="restoreDockedVideo()"
                        class="ks-btn-primary text-xs py-1.5 px-3.5 font-mono flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                    <span>Asosiy ekranga qaytarish</span>
                </button>
            </div>

            <!-- Video Screen Viewport -->
            <div id="liveVideoViewport" class="relative w-full aspect-video rounded-panel bg-ink-950 border border-ink-border shadow-soft overflow-hidden flex items-center justify-center group transition-all duration-300"
                 :class="{ 'rounded-none': isVideoFullscreen, 'live-viewport-docked': isDockedMini, 'is-docked-dragging': isDockedDragging }"
                 :style="viewportStyle"
                 @pointerdown="if (isDockedMini) startDockedDrag($event)"
                 @click="if (!isHost && (needsUnmute || isViewerMuted)) unmuteAudio()">
                
                <!-- Native Picture-in-Picture Active Notice Overlay -->
                <div x-show="isPipActive" x-cloak
                     class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-ink-950/90 backdrop-blur-md p-6 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-ink-900 border border-amber-400/40 flex items-center justify-center text-amber-300 shadow-xl">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <rect x="12" y="9" width="8" height="6" rx="1" fill="currentColor" fill-opacity="0.3"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-paper font-serif">Video mini oynada ijro etilmoqda</p>
                        <p class="text-xs text-mist font-mono mt-0.5">Boshqa dasturlar yoki sahifalarga o‘tsangiz ham efir davom etadi</p>
                    </div>
                    <button type="button" @click.stop="exitPip()" class="ks-btn-primary text-xs py-2 px-4 font-mono">
                        Asosiy oynaga qaytarish
                    </button>
                </div>

                <!-- Docked Mini Mode Compact Top Bar Overlay -->
                <div x-show="isDockedMini" x-cloak
                     class="absolute top-2 inset-x-2 z-40 flex items-center justify-between gap-2 px-2.5 py-1 rounded-xl bg-ink-950/90 backdrop-blur-md border border-white/10 font-mono text-[11px] shadow-lg cursor-grab active:cursor-grabbing select-none"
                     @pointerdown="startDockedDrag($event)">
                    <div class="flex items-center gap-1.5 min-w-0 pointer-events-none">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                        </span>
                        <span class="truncate text-paper font-semibold">{{ $event->title }}</span>
                        <span class="text-[8px] font-mono px-1 py-0.2 rounded bg-white/10 text-mist flex items-center gap-0.5">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M5 12l4-4M5 12l4 4M19 12l-4-4M19 12l-4 4"/></svg>
                            <span>suring</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0" @pointerdown.stop @mousedown.stop>
                        <button type="button" @click.stop="restoreDockedVideo()"
                                class="p-1 rounded text-mist hover:text-amber-300 hover:bg-white/10 transition-colors cursor-pointer"
                                title="Kattalashtirish (Asosiy holatga qaytish)">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                        </button>
                        <button type="button" @click.stop="restoreDockedVideo()"
                                class="p-1 rounded text-mist hover:text-rose-400 hover:bg-white/10 transition-colors cursor-pointer"
                                title="Mini oynani yopish">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Live Video Element (Webcam / Stream) -->
                <video id="liveVideoPlayer" autoplay playsinline
                       class="w-full h-full transition-all duration-300 bg-black"
                       :class="{ 'opacity-0': !isVideoOn, 'opacity-100': isVideoOn }"
                       :style="videoElementStyle"
                       @loadedmetadata="updateCameraStatus(localStream ? localStream.getVideoTracks()[0] : null); applyVideoFitStyles();"
                       @playing="updateCameraStatus(localStream ? localStream.getVideoTracks()[0] : null); applyVideoFitStyles();">
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
                        <p class="text-xs text-mist font-mono mt-1 max-w-sm" x-text="streamEndedReason || '{{ __('site.live.ended_sub') }}'">{{ __('site.live.ended_sub') }}</p>
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
                <div x-show="!isDockedMini" class="absolute top-4 left-4 flex items-center gap-2 z-20">
                    <div class="px-2.5 py-0.5 rounded-pill bg-[#C1392B] text-paper font-mono text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-lg backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-paper animate-ping"></span>
                        <span>LIVE</span>
                    </div>
                    <div class="px-2.5 py-0.5 rounded-pill bg-ink-950/80 backdrop-blur-md text-paper font-mono text-xs border border-ink-border transition-colors"
                         :class="{ 'text-amber-400 border-amber-500/40 font-bold': durationSeconds >= (4.75 * 3600) }"
                         :title="'Efir davomiyligi (maksimal: 5 soat)'"
                         x-text="formattedDuration">
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
                <div x-show="isRecording && !isDockedMini" class="absolute top-4 right-4 z-20 animate-pulse">
                    <div class="px-2.5 py-0.5 rounded-pill bg-[#C1392B] text-paper font-mono text-xs font-bold flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-paper animate-ping"></span>
                        <span>REC <span x-text="formattedRecordDuration">00:00</span></span>
                    </div>
                </div>

                <!-- Bottom Left Audio VU Meter -->
                <div x-show="!isDockedMini" class="absolute bottom-4 left-4 z-20 flex items-center gap-2">
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
                <div x-show="!isDockedMini" class="absolute bottom-16 right-4 w-32 h-72 pointer-events-none z-30 overflow-hidden" aria-hidden="true">
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

                <!-- Bottom Right Watermark & Interactive Like Button & Video Controls -->
                <div x-show="!isDockedMini" class="absolute bottom-4 right-4 z-30 flex items-center gap-2">
                    <div class="hidden sm:flex items-center gap-1.5 bg-ink-950/80 backdrop-blur-md px-2 py-1 rounded-pill text-[10px] text-mist font-mono border border-ink-border">
                        <span>Kitobxon Studio</span>
                    </div>

                    <!-- Video viewing controls -->
                    <div class="relative" @click.outside="videoSettingsOpen = false">
                        <button type="button"
                                @click.stop="videoSettingsOpen = !videoSettingsOpen"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-btn bg-ink-950/85 hover:bg-ink-900 backdrop-blur-md border border-ink-border text-paper shadow-lg transition-all active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/80"
                                :class="videoSettingsOpen ? 'text-amber-300 border-amber-400/50 bg-ink-900' : ''"
                                aria-label="Video sozlamalari"
                                :aria-expanded="videoSettingsOpen.toString()">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                                <circle cx="9" cy="6" r="1.5" fill="currentColor" stroke="none"/>
                                <circle cx="15" cy="12" r="1.5" fill="currentColor" stroke="none"/>
                                <circle cx="10" cy="18" r="1.5" fill="currentColor" stroke="none"/>
                            </svg>
                        </button>

                        <div x-show="videoSettingsOpen" x-cloak x-transition
                             class="absolute bottom-full right-0 mb-2 w-60 p-1.5 rounded-panel bg-ink-950/95 border border-ink-border backdrop-blur-xl shadow-2xl font-mono text-[11px]">
                            <div class="px-2.5 py-1.5 text-[10px] uppercase tracking-wider text-mist border-b border-ink-border/70 mb-1">
                                Video sozlamalari
                            </div>

                            <button type="button" @click="toggleVideoFullscreen(); videoSettingsOpen = false"
                                    x-show="canUseFullscreen || isVideoFullscreen"
                                    class="w-full flex items-center justify-between gap-3 px-2.5 py-2 rounded-btn text-paper hover:bg-white/10 transition-colors text-left">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                                    <span x-text="isVideoFullscreen ? 'To‘liq ekrandan chiqish' : 'To‘liq ekran'"></span>
                                </span>
                            </button>

                            <button type="button" @click="activateMiniPlayer(); videoSettingsOpen = false"
                                    class="w-full flex items-center justify-between gap-3 px-2.5 py-2 rounded-btn text-paper hover:bg-white/10 transition-colors text-left">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M13 13h5v4h-5z"/></svg>
                                    <span x-text="isDockedMini ? 'Mini oynadan chiqish' : 'Mini oynada ko‘rish'"></span>
                                </span>
                                <span class="text-amber-300 text-[10px]" x-show="isDockedMini" x-cloak>Faol</span>
                            </button>

                            <button type="button" @click="cycleVideoFit()"
                                    class="w-full flex items-center justify-between gap-3 px-2.5 py-2 rounded-btn text-paper hover:bg-white/10 transition-colors text-left cursor-pointer"
                                    title="Kadr rejimini almashtirish">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h2M15 15h2"/></svg>
                                    <span>Kadr rejimi</span>
                                </span>
                                <span class="text-amber-300 font-mono text-xs font-semibold" x-text="videoFitMode === 'cover' ? 'To‘ldirish (Zoom)' : (videoFitMode === 'contain' ? 'To‘liq kadr (16:9)' : 'Kinoteatr (21:9)')"></span>
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ── VIDEO OSTIDAGI MA'LUMOT PANELI (YouTube uslubi) ── -->
            @php
                $hostUser = $event->hostUser;
                $hostRoleLabel = null;
                if ($hostUser) {
                    $isHostAdmin = (method_exists($hostUser, 'hasRole') && $hostUser->hasRole('admin')) || ($hostUser->role ?? null) === 'admin';
                    $hostRoleLabel = $isHostAdmin ? 'Admin' : 'Ustoz';
                }
            @endphp
            <section class="live-info-bar space-y-3" aria-label="Efir haqida">
                <h2 class="text-base sm:text-lg font-bold font-serif text-paper leading-snug break-words">{{ $event->title }}</h2>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Muallif + Obuna (YouTube Style) -->
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ $hostUser ? route('profile.show', $hostUser->username) : '#' }}" class="shrink-0">
                            <img src="{{ $hostUser?->avatar_url ?? 'https://ui-avatars.com/api/?name=Kitobxon&background=1e293b&color=fff' }}"
                                 alt="{{ $hostUser?->name ?? 'Ustoz' }}"
                                 class="w-10 h-10 rounded-full object-cover border border-ink-border hover:ring-2 hover:ring-amber-400/50 transition-all">
                        </a>
                        <div class="min-w-0 flex flex-col justify-center">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ $hostUser ? route('profile.show', $hostUser->username) : '#' }}" class="text-sm sm:text-base font-bold text-paper hover:text-amber-400 transition-colors truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ $hostUser?->name ?? 'Ustoz' }}
                                </a>
                                @if($hostRoleLabel)
                                    <span class="shrink-0 px-1.5 py-0.5 rounded bg-amber-400/15 border border-amber-400/30 text-amber-300 text-[9px] font-mono uppercase tracking-wider font-bold">{{ $hostRoleLabel }}</span>
                                @endif

                                <!-- YouTube-style Obuna bo'lish tugmasi to'g'ridan-to'g'ri nikning yonida -->
                                @if($isOwnStream)
                                    <span class="shrink-0 px-2.5 py-0.5 rounded-full bg-ink-800 text-mist border border-ink-border text-[10px] font-mono font-medium">Siz muallifsiz</span>
                                @elseif($hostUser)
                                    <button type="button" @click="toggleFollow()"
                                            :disabled="followBusy"
                                            class="shrink-0 px-3.5 py-1 rounded-full text-xs font-semibold transition-all duration-200 active:scale-95 flex items-center gap-1.5 shadow-sm"
                                            :class="isFollowing 
                                                ? 'bg-ink-800 hover:bg-ink-700 text-mist hover:text-paper border border-ink-border' 
                                                : 'bg-white hover:bg-neutral-200 text-neutral-900 font-extrabold'">
                                        <!-- Obuna bo'lingan: Qo'ng'iroqcha -->
                                        <svg x-show="isFollowing" x-cloak width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-amber-400 shrink-0">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" fill="none"/>
                                        </svg>
                                        <!-- Obuna bo'lish: Plus ikonkasi -->
                                        <svg x-show="!isFollowing" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span x-text="isFollowing ? @js(__('site.live.subscribed')) : @js(__('site.live.subscribe'))">{{ $isFollowingHost ? __('site.live.subscribed') : __('site.live.subscribe') }}</span>
                                    </button>
                                @endif
                            </div>
                            <p class="text-[11px] text-mist font-mono flex items-center gap-1.5 mt-0.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-mist/70">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <span x-text="@js(__('site.live.followers_count', ['count' => '___CNT___'])).replace('___CNT___', formatNumber(followersCount))">{{ __('site.live.followers_count', ['count' => $hostFollowersCount]) }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Amallar: Like, Ulashish, Chat, Yana -->
                    <div class="relative flex items-center gap-2 flex-wrap shrink-0">
                        <!-- Like tugmasi -->
                        <button type="button" @click="toggleLike()"
                                :disabled="likeBusy"
                                class="live-pill transition-all active:scale-95 group"
                                :class="hasLiked ? 'live-pill--liked' : ''"
                                :aria-pressed="hasLiked.toString()"
                                :title="hasLiked ? 'Like olib tashlash (L)' : 'Like bosish (L)'">
                            <!-- Bosilmagan: Outline Thumbs Up -->
                            <svg x-show="!hasLiked" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="live-btn-icon text-mist group-hover:text-paper group-hover:-translate-y-0.5 transition-all">
                                <path d="M7 10v12"/>
                                <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/>
                            </svg>
                            <!-- Bosilgan: To'ldirilgan Thumbs Up (Rose rangda) -->
                            <svg x-show="hasLiked" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" class="live-btn-icon text-rose-400 scale-110 transition-transform">
                                <path d="M7 10v12"/>
                                <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/>
                            </svg>
                            <span class="font-semibold tabular-nums" :class="hasLiked ? 'text-rose-200' : ''" x-text="formatNumber(likesCount)">{{ (int) ($event->likes_count ?? 0) }}</span>
                        </button>

                        <!-- Ulashish -->
                        <div class="relative" @click.outside="shareOpen = false">
                            <button type="button" @click="shareStream()" class="live-pill transition-all active:scale-95 group" title="Ulashish">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="live-btn-icon text-mist group-hover:text-paper group-hover:translate-x-0.5 transition-all">
                                    <path d="m15 4 6 6-6 6"/>
                                    <path d="M21 10H8a5 5 0 0 0-5 5v3"/>
                                </svg>
                                <span>Ulashish</span>
                            </button>
                            <div x-show="shareOpen" x-cloak x-transition
                                 class="live-menu right-0 md:right-auto md:left-0 w-64">
                                <div class="px-3 py-2 text-[10px] uppercase tracking-wider text-mist border-b border-ink-border/70 font-mono">Efirni ulashish</div>
                                <div class="p-2 flex items-center gap-2">
                                    <input type="text" readonly :value="shareUrl" @focus="$event.target.select()"
                                           class="flex-1 min-w-0 px-2 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-[11px] text-mist font-mono focus:outline-none focus:border-amber-400">
                                    <button type="button" @click="copyShareLink()"
                                            class="shrink-0 px-2.5 py-1.5 rounded-btn text-[11px] font-mono font-bold transition-colors"
                                            :class="shareCopied ? 'bg-emerald-500 text-ink-950' : 'bg-amber-400 text-ink-950 hover:bg-amber-300'"
                                            x-text="shareCopied ? 'Nusxalandi' : 'Nusxa'"></button>
                                </div>
                                <div class="grid grid-cols-4 gap-1 p-2 pt-0">
                                    <a :href="'https://t.me/share/url?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareText)" target="_blank" rel="noopener" class="live-share-tile">
                                        <span class="live-share-icon bg-[#229ED9]"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg></span>
                                        <span>Telegram</span>
                                    </a>
                                    <a :href="'https://wa.me/?text=' + encodeURIComponent(shareText + ' ' + shareUrl)" target="_blank" rel="noopener" class="live-share-tile">
                                        <span class="live-share-icon bg-[#25D366]"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1s-.5-.1-.7.1-.8 1-1 1.2-.4.2-.7.1a8.2 8.2 0 0 1-4-3.5c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.9.4 3.6 3.6 0 0 0 1.3 3.3 14.4 14.4 0 0 0 5.5 4.9c2 .9 2.8 1 3.9.8a3.3 3.3 0 0 0 2.1-1.5 2.7 2.7 0 0 0 .2-1.5c-.1-.1-.3-.2-.6-.3zM12 21.8a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 1 1 12 21.8zM12 0a12 12 0 0 0-10.3 18L0 24l6.2-1.6A12 12 0 1 0 12 0z"/></svg></span>
                                        <span>WhatsApp</span>
                                    </a>
                                    <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)" target="_blank" rel="noopener" class="live-share-tile">
                                        <span class="live-share-icon bg-[#1877F2]"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 8V6c0-.9.6-1 1-1h3V1h-4c-3.9 0-5 2.9-5 5v2H6v4h3v11h5V12h3.5l.5-4z"/></svg></span>
                                        <span>Facebook</span>
                                    </a>
                                    <a :href="'https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareText)" target="_blank" rel="noopener" class="live-share-tile">
                                        <span class="live-share-icon bg-black border border-white/20"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.2 2h3.4l-7.4 8.5L23 22h-6.8l-5.3-7-6.1 7H1.4l7.9-9L1 2h7l4.8 6.4L18.2 2zm-1.2 18h1.9L7.1 3.9H5.1L17 20z"/></svg></span>
                                        <span>X</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Chatni ochish / yopish -->
                        <button type="button" @click="toggleChat()" class="live-pill transition-all active:scale-95 group"
                                :class="chatOpen ? '' : 'live-pill--accent'"
                                :title="chatOpen ? 'Chatni yashirish (C)' : 'Chatni ko‘rsatish (C)'">
                            <!-- Chat ochiq paytda -->
                            <svg x-show="chatOpen" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="live-btn-icon text-mist group-hover:text-paper">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <!-- Chat yopiq paytda -->
                            <svg x-show="!chatOpen" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="live-btn-icon text-amber-400">
                                <path d="M8 9h8"/>
                                <path d="M8 13h5"/>
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span x-text="chatOpen ? 'Chatni yopish' : 'Chatni ochish'">Chatni yopish</span>
                        </button>

                        <!-- Yana (⋯) -->
                        <div class="relative" @click.outside="moreOpen = false">
                            <button type="button" @click="moreOpen = !moreOpen" class="live-pill live-pill--icon transition-all active:scale-95 group" aria-label="Boshqa amallar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="live-btn-icon text-mist group-hover:text-paper">
                                    <circle cx="5" cy="12" r="2"/>
                                    <circle cx="12" cy="12" r="2"/>
                                    <circle cx="19" cy="12" r="2"/>
                                </svg>
                            </button>
                            <div x-show="moreOpen" x-cloak x-transition class="live-menu right-0 w-60 p-1.5 font-mono text-[11px]">
                                <button type="button" @click="activateMiniPlayer(); moreOpen = false" class="live-menu-item">
                                    <span class="flex items-center gap-2">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="live-menu-icon text-amber-400/90"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M13 13h5v4h-5z"/></svg>
                                        <span>Mini oynada ko‘rish</span>
                                    </span>
                                    <kbd>I</kbd>
                                </button>
                                <button type="button" x-show="canUseFullscreen" @click="toggleVideoFullscreen(); moreOpen = false" class="live-menu-item">
                                    <span class="flex items-center gap-2">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="live-menu-icon text-mist"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                                        <span>To‘liq ekran</span>
                                    </span>
                                    <kbd>F</kbd>
                                </button>
                                <button type="button" @click="cycleVideoFit(); moreOpen = false" class="live-menu-item cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="live-menu-icon text-mist"><path d="M3 7V5a2 2 0 0 1 2-2h2m12 0h2a2 2 0 0 1 2 2v2m0 10v2a2 2 0 0 1-2 2h-2m-12 0H5a2 2 0 0 1-2-2v-2"/></svg>
                                        <span>Kadr: <span class="text-amber-300 font-mono font-semibold" x-text="videoFitMode === 'cover' ? 'To‘ldirish (Zoom)' : (videoFitMode === 'contain' ? 'To‘liq kadr (16:9)' : 'Kinoteatr (21:9)')"></span></span>
                                    </span>
                                </button>
                                <button type="button" @click="copyShareLink(); moreOpen = false" class="live-menu-item">
                                    <span class="flex items-center gap-2">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="live-menu-icon text-mist"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        <span>Havolani nusxalash</span>
                                    </span>
                                </button>
                                <div class="mt-1 pt-2 px-2.5 pb-1 border-t border-ink-border/70 text-[10px] text-mist leading-relaxed">
                                    Tezkor tugmalar: <kbd>L</kbd> like, <kbd>C</kbd> chat, <kbd>F</kbd> to‘liq ekran, <kbd>I</kbd> mini oyna @if(!$isHost) , <kbd>M</kbd> ovoz @endif
                                </div>
                            </div>
                        </div>

                        <!-- Mehmonlar uchun kirish taklifi -->
                        <div x-show="authPrompt" x-cloak x-transition @click.outside="authPrompt = ''"
                             class="live-menu right-0 top-full w-64 p-3 space-y-2">
                            <p class="text-xs text-paper font-semibold" x-text="authPrompt"></p>
                            <p class="text-[11px] text-mist">Hisobingizga kiring va efir muallifini qo‘llab-quvvatlang.</p>
                            <div class="flex items-center gap-2 pt-1">
                                <a :href="loginUrl" class="ks-btn-primary text-xs py-1.5 px-3 font-mono">Kirish</a>
                                <button type="button" @click="authPrompt = ''" class="text-[11px] text-mist hover:text-paper font-mono">Keyinroq</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tavsif qutisi -->
                <div class="live-desc-box rounded-panel bg-ink-900 border border-ink-border p-3.5 sm:p-4 text-xs sm:text-sm"
                     :class="descExpanded ? '' : 'cursor-pointer hover:bg-ink-800/80'"
                     @click="if (!descExpanded) descExpanded = true">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-semibold text-paper">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <span><span class="tabular-nums" x-text="formatNumber(Math.round(animatedViewerCount))">0</span> kishi tomosha qilmoqda</span>
                        </span>
                        <span class="text-mist">•</span>
                        <span>Efir <span x-text="startedAgoLabel"></span> boshlangan</span>
                        @if($event->group)
                            <span class="text-mist">•</span>
                            <a href="{{ route('groups.show', $event->group) }}" @click.stop class="text-amber-400 hover:text-amber-300">#{{ $event->group->name }}</a>
                        @endif
                        @if($event->book)
                            <span class="text-mist">•</span>
                            <span class="text-amber-400">📖 {{ $event->book->title }}</span>
                        @endif
                    </div>

                    @if($event->description)
                        <p class="mt-2 text-mist leading-relaxed whitespace-pre-line break-words"
                           :class="descExpanded ? '' : 'live-clamp-2'">{{ $event->description }}</p>
                    @endif

                    <div x-show="descExpanded" x-cloak class="mt-3 pt-3 border-t border-ink-border/70 space-y-1.5 text-[11px] text-mist font-mono">
                        <p>🗓 Rejalashtirilgan vaqt: {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M Y, H:i') ?? '—' }}</p>
                        <p>💬 Chat rejimi: {{ in_array($event->permission_mode, ['view_only', 'voice_only']) ? 'Faqat tomosha' : 'Yozma chat ochiq' }}</p>
                        <p>⚠️ Jonli efirlar yozib olinmaydi — efir tugagach saytdan butunlay o‘chiriladi.</p>
                    </div>

                    <button type="button" @click.stop="descExpanded = !descExpanded"
                            class="mt-2 text-xs font-semibold text-paper hover:text-amber-300"
                            x-text="descExpanded ? 'Kamroq' : '...yana'">...yana</button>
                </div>
            </section>

