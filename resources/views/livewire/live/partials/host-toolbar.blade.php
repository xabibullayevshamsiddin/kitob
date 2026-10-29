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
                                    <span class="text-[10px] font-mono text-amber-300" x-text="videoEnhance === 'bright' ? 'Yorqin' : (videoEnhance === 'sharp' ? 'Ultra Tiniq' : (videoEnhance === 'hd' ? 'Studio Clean' : 'O‘chirilgan'))"></span>
                                </div>
                                <p class="text-[10px] text-mist">Sayt tasvirni biroz oqartiradi, rangni tiklaydi va mayda shovqinni yumshatadi</p>
                                <div class="grid grid-cols-4 gap-1.5 pt-1 font-mono text-[11px]">
                                    <button type="button" @click="setVideoEnhance('hd')"
                                        :class="videoEnhance === 'hd' ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-800 text-mist hover:text-paper'"
                                        class="py-1 px-1 rounded text-center transition-all">
                                        Studio Clean
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

