<div class="space-y-4" wire:poll.2500ms>
    <!-- 🎙️ WebRTC Live Voice Room & Voice Player Scripts (MUST BE AT TOP) -->
    <script>
        window.goToBuddyReading = function(targetPage) {
            window.dispatchEvent(new CustomEvent('close-buddy-modal'));
            window.dispatchEvent(new CustomEvent('close-buddy-drawer'));

            const readerEl = document.getElementById('online-reader') || document.getElementById('fb-root');
            if (readerEl) {
                readerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (targetPage && typeof window.fbGoToPage === 'function') {
                    setTimeout(() => {
                        try { window.fbGoToPage(targetPage); } catch (e) {}
                    }, 350);
                }
            } else {
                const url = "{{ route('books.flipbook', $book->slug ?? $book->id) }}?page=" + (targetPage || 1);
                window.location.href = url;
            }
        };

        window.buddyLiveVoiceRoom = function(config) {
            return {
                roomId: config.roomId,
                userId: config.userId,
                voiceActive: config.initialActive || false,
                isConnecting: false,
                isMuted: false,
                isFullModalOpen: true,
                mediaStream: null,
                audioContext: null,
                analyser: null,
                micLevel: 0,
                animFrame: null,
                pollInterval: null,
                peers: {},
                callingPeers: {},
                candidateQueues: {},
                lastSignalId: 0,
                connectedPeersCount: 0,
                dragX: 0,
                dragY: 0,
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,
                initialDragX: 0,
                initialDragY: 0,
                _onPointerMove: null,
                _onPointerUp: null,

                startDockDrag(e) {
                    if (e.target.closest('button, a, input, select, textarea')) return;
                    if (e.button !== undefined && e.button !== 0) return;

                    this.isDragging = true;
                    this.dragStartX = e.clientX;
                    this.dragStartY = e.clientY;
                    this.initialDragX = this.dragX;
                    this.initialDragY = this.dragY;

                    this._onPointerMove = (evt) => {
                        if (!this.isDragging) return;
                        const dx = evt.clientX - this.dragStartX;
                        const dy = evt.clientY - this.dragStartY;
                        this.dragX = this.initialDragX + dx;
                        this.dragY = this.initialDragY + dy;
                    };
                    this._onPointerUp = () => {
                        this.isDragging = false;
                        if (this._onPointerMove) window.removeEventListener('pointermove', this._onPointerMove);
                        if (this._onPointerUp) {
                            window.removeEventListener('pointerup', this._onPointerUp);
                            window.removeEventListener('pointercancel', this._onPointerUp);
                        }
                        this._onPointerMove = null;
                        this._onPointerUp = null;
                    };

                    window.addEventListener('pointermove', this._onPointerMove, { passive: false });
                    window.addEventListener('pointerup', this._onPointerUp);
                    window.addEventListener('pointercancel', this._onPointerUp);
                },

                init() {
                    window.addEventListener('open-buddy-modal', () => { this.isFullModalOpen = true; });
                    window.addEventListener('close-buddy-modal', () => { this.isFullModalOpen = false; });
                    window.addEventListener('open-buddy-drawer', () => { this.isFullModalOpen = true; });
                    window.addEventListener('close-buddy-drawer', () => { this.isFullModalOpen = false; });
                    window.addEventListener('flipbook:pagechange', (e) => {
                        if (e.detail && e.detail.page && config.wire && typeof config.wire.updateCurrentPage === 'function') {
                            config.wire.updateCurrentPage(e.detail.page);
                        }
                    });
                },

                openFullModal() {
                    this.isFullModalOpen = true;
                    window.dispatchEvent(new CustomEvent('open-buddy-modal'));
                    window.dispatchEvent(new CustomEvent('open-buddy-drawer'));
                },

                goToReading(targetPage = null) {
                    if (window.goToBuddyReading) {
                        window.goToBuddyReading(targetPage);
                    }
                },

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
                                autoGainControl: true,
                                channelCount: 1,
                                sampleRate: 48000
                            }
                        });

                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (AudioCtx) {
                            try {
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
                            } catch(e) {
                                console.warn('AudioContext error:', e);
                            }
                        }

                        this.voiceActive = true;
                        this.isConnecting = false;
                        config.wire.toggleVoiceActive(true);

                        this.sendSignal('join', JSON.stringify({ userId: this.userId }));

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

                    this.sendSignal('leave', JSON.stringify({ userId: this.userId }));

                    Object.keys(this.peers).forEach(peerId => {
                        try { this.peers[peerId].close(); } catch(e) {}
                        const el = document.getElementById('peer-audio-' + peerId);
                        if (el) el.remove();
                    });
                    this.peers = {};
                    this.callingPeers = {};
                    this.candidateQueues = {};
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
                    this.isMuted = !this.isMuted;
                    if (this.mediaStream) {
                        try {
                            this.mediaStream.getAudioTracks().forEach(t => {
                                t.enabled = !this.isMuted;
                            });
                        } catch(e) {
                            console.warn('Track mute error:', e);
                        }
                    }

                    // WebRTC PeerConnection RTP senderlarini ham o'chirish/yoqish
                    Object.values(this.peers || {}).forEach(pc => {
                        try {
                            if (pc && typeof pc.getSenders === 'function') {
                                pc.getSenders().forEach(sender => {
                                    if (sender.track && sender.track.kind === 'audio') {
                                        sender.track.enabled = !this.isMuted;
                                    }
                                });
                            }
                        } catch(e) {}
                    });

                    // Agar ovoz o'chirilgan bo'lsa, mikrofon indikatorini 0 ga tushirish
                    if (this.isMuted) {
                        this.micLevel = 0;
                    }
                },

                enhanceOpusAudioSdp(sdp) {
                    if (!sdp || typeof sdp !== 'string') return sdp || '';
                    const opusMatch = sdp.match(/a=rtpmap:(\d+)\s+opus\/48000[^\r\n]*/i);
                    if (!opusMatch) return sdp;
                    const pt = opusMatch[1];
                    const fmtpRegex = new RegExp('a=fmtp:' + pt + ' [^\\r\\n]*');
                    const opusParams = 'maxaveragebitrate=64000;useinbandfec=1;usedtx=0;stereo=0;sprop-stereo=0;cbr=0;minptime=10;maxptime=60';
                    if (fmtpRegex.test(sdp)) {
                        return sdp.replace(fmtpRegex, (m) => {
                            const params = m.replace(new RegExp('^a=fmtp:' + pt + '\\s*'), '');
                            let clean = params.replace(/useinbandfec=\d+/g, '')
                                              .replace(/usedtx=\d+/g, '')
                                              .replace(/maxaveragebitrate=\d+/g, '')
                                              .replace(/minptime=\d+/g, '')
                                              .replace(/maxptime=\d+/g, '')
                                              .replace(/cbr=\d+/g, '')
                                              .replace(/stereo=\d+/g, '')
                                              .replace(/sprop-stereo=\d+/g, '')
                                              .replace(/;+/g, ';')
                                              .replace(/^;|;$/g, '').trim();
                            return 'a=fmtp:' + pt + ' ' + (clean ? clean + ';' : '') + opusParams;
                        });
                    } else {
                        return sdp.replace(opusMatch[0], opusMatch[0] + '\r\na=fmtp:' + pt + ' ' + opusParams);
                    }
                },

                applyAudioSenderQuality(pc) {
                    if (!pc || !pc.getSenders) return;
                    const sender = pc.getSenders().find(s => s.track && s.track.kind === 'audio');
                    if (!sender || !sender.getParameters) return;
                    try {
                        const params = sender.getParameters();
                        if (params.encodings && params.encodings.length > 0) {
                            params.encodings[0].maxBitrate = 64000;
                            params.encodings[0].priority = 'high';
                            params.encodings[0].networkPriority = 'high';
                            sender.setParameters(params).catch(() => {});
                        }
                    } catch (e) {}
                },

                createPeerConnection(peerId) {
                    if (this.peers[peerId]) return this.peers[peerId];

                    const pc = new RTCPeerConnection({
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' },
                            { urls: 'stun:stun1.l.google.com:19302' },
                            { urls: 'stun:stun.cloudflare.com:3478' },
                            { urls: 'stun:stun.services.mozilla.com:3478' },
                            { urls: 'stun:stun.relay.metered.ca:80' },
                            @if (!empty(config('webrtc.turn.urls')))
                            @json(config('webrtc.turn')),
                            @endif
                        ],
                        iceCandidatePoolSize: 10
                    });

                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => {
                            try { pc.addTrack(track, this.mediaStream); } catch(e) {}
                        });
                    }

                    pc.onicecandidate = (event) => {
                        if (event.candidate) {
                            const candData = event.candidate.toJSON ? event.candidate.toJSON() : {
                                candidate: event.candidate.candidate,
                                sdpMid: event.candidate.sdpMid,
                                sdpMLineIndex: event.candidate.sdpMLineIndex,
                                usernameFragment: event.candidate.usernameFragment
                            };
                            this.sendSignal('candidate', JSON.stringify(candData), peerId);
                        }
                    };

                    pc.onconnectionstatechange = () => {
                        const connected = Object.values(this.peers).filter(p => p.connectionState === 'connected').length;
                        this.connectedPeersCount = connected;
                    };

                    pc.ontrack = (event) => {
                        let audio = document.getElementById('peer-audio-' + peerId);
                        if (!audio) {
                            audio = document.createElement('audio');
                            audio.id = 'peer-audio-' + peerId;
                            audio.autoplay = true;
                            audio.playsInline = true;
                            document.body.appendChild(audio);
                        }
                        audio.volume = 1.0;
                        audio.muted = false;
                        if (event.streams && event.streams[0]) {
                            audio.srcObject = event.streams[0];
                        } else {
                            const s = new MediaStream();
                            s.addTrack(event.track);
                            audio.srcObject = s;
                        }
                        const p = audio.play();
                        if (p !== undefined) {
                            p.catch(e => {
                                console.warn('WebRTC audio autoplay warning:', e);
                            });
                        }
                    };

                    this.peers[peerId] = pc;
                    return pc;
                },

                async initiateCall(peerId) {
                    if (this.callingPeers[peerId] || (this.peers[peerId] && this.peers[peerId].connectionState === 'connected')) {
                        return;
                    }
                    this.callingPeers[peerId] = true;
                    try {
                        const pc = this.createPeerConnection(peerId);
                        const offer = await pc.createOffer({ offerToReceiveAudio: true });
                        const enhancedSdp = this.enhanceOpusAudioSdp(offer.sdp);
                        const tunedOffer = new RTCSessionDescription({
                            type: offer.type,
                            sdp: enhancedSdp
                        });
                        await pc.setLocalDescription(tunedOffer);
                        this.applyAudioSenderQuality(pc);
                        this.sendSignal('offer', JSON.stringify(tunedOffer), peerId);
                    } catch (err) {
                        this.callingPeers[peerId] = false;
                        console.error('Call initiation error:', err);
                    }
                },

                async handleOffer(fromUserId, sdpString) {
                    try {
                        const pc = this.createPeerConnection(fromUserId);
                        const desc = new RTCSessionDescription(JSON.parse(sdpString));
                        await pc.setRemoteDescription(desc);

                        // Drain queued candidates for this peer
                        if (this.candidateQueues[fromUserId]) {
                            for (const cand of this.candidateQueues[fromUserId]) {
                                try { await pc.addIceCandidate(new RTCIceCandidate(cand)); } catch(e){}
                            }
                            delete this.candidateQueues[fromUserId];
                        }

                        const answer = await pc.createAnswer();
                        const enhancedSdp = this.enhanceOpusAudioSdp(answer.sdp);
                        const tunedAnswer = new RTCSessionDescription({
                            type: answer.type,
                            sdp: enhancedSdp
                        });
                        await pc.setLocalDescription(tunedAnswer);
                        this.applyAudioSenderQuality(pc);
                        this.sendSignal('answer', JSON.stringify(tunedAnswer), fromUserId);
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
                            this.applyAudioSenderQuality(pc);

                            // Drain queued candidates for this peer
                            if (this.candidateQueues[fromUserId]) {
                                for (const cand of this.candidateQueues[fromUserId]) {
                                    try { await pc.addIceCandidate(new RTCIceCandidate(cand)); } catch(e){}
                                }
                                delete this.candidateQueues[fromUserId];
                            }
                        }
                    } catch (err) {
                        console.error('Answer handling error:', err);
                    }
                },

                async handleCandidate(fromUserId, candidateString) {
                    try {
                        const candidateData = JSON.parse(candidateString);
                        const pc = this.peers[fromUserId];
                        if (!pc || !pc.remoteDescription) {
                            if (!this.candidateQueues[fromUserId]) this.candidateQueues[fromUserId] = [];
                            this.candidateQueues[fromUserId].push(candidateData);
                        } else {
                            await pc.addIceCandidate(new RTCIceCandidate(candidateData));
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
                    fetch('/buddy-read/' + this.roomId + '/signals?after_id=' + this.lastSignalId, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(async data => {
                        if (data.signals && Array.isArray(data.signals)) {
                            for (const sig of data.signals) {
                                if (sig.id > this.lastSignalId) {
                                    this.lastSignalId = sig.id;
                                }
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
                                        delete this.callingPeers[fromId];
                                        const el = document.getElementById('peer-audio-' + fromId);
                                        if (el) el.remove();
                                        this.connectedPeersCount = Object.values(this.peers).filter(p => p.connectionState === 'connected').length;
                                    }
                                }
                            }
                        }

                        if (data.activeMembers && Array.isArray(data.activeMembers)) {
                            data.activeMembers.forEach(peerId => {
                                if (peerId !== this.userId && !this.peers[peerId] && !this.callingPeers[peerId] && this.userId < peerId) {
                                    this.initiateCall(peerId);
                                }
                            });
                        }
                    })
                    .catch(e => console.error('Signal polling error:', e));
                }
            };
        };

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
                    this.audio.addEventListener('error', (e) => {
                        console.warn('Voice player error:', e, audioUrl);
                        this.playing = false;
                    });
                },
                toggle() {
                    this.setupAudio();
                    if (this.playing) {
                        this.audio.pause();
                        this.playing = false;
                    } else {
                        if (this.audio.ended) {
                            this.audio.currentTime = 0;
                        }
                        document.querySelectorAll('audio').forEach(a => {
                            if (a !== this.audio && !a.id.startsWith('peer-audio-')) {
                                try { a.pause(); } catch(e) {}
                            }
                        });
                        const p = this.audio.play();
                        if (p !== undefined) {
                            p.then(() => {
                                this.playing = true;
                            }).catch(e => {
                                console.warn('Play interrupted/error:', e);
                                this.playing = false;
                            });
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
                    const s = Math.max(0, parseInt(sec) || 0);
                    const m = Math.floor(s / 60);
                    const rem = s % 60;
                    return (m < 10 ? '0' : '') + m + ':' + (rem < 10 ? '0' : '') + rem;
                }
            };
        };

        window.fallbackVoiceToggle = function(btn, fallbackUrl) {
            const root = btn ? btn.closest('[x-data]') : null;
            if (root && root._x_dataStack && root._x_dataStack[0] && typeof root._x_dataStack[0].toggle === 'function') {
                root._x_dataStack[0].toggle();
                return;
            }
            if (!btn._audio) {
                btn._audio = new Audio(fallbackUrl);
                btn._audio.onended = () => {
                    btn._playing = false;
                    btn.classList.remove('ring-4', 'ring-emerald-400');
                };
            }
            if (btn._playing) {
                btn._audio.pause();
                btn._playing = false;
                btn.classList.remove('ring-4', 'ring-emerald-400');
            } else {
                document.querySelectorAll('audio').forEach(a => (!a.id.startsWith('peer-audio-') && a !== btn._audio) && a.pause());
                btn._audio.play().then(() => {
                    btn._playing = true;
                    btn.classList.add('ring-4', 'ring-emerald-400');
                }).catch(e => alert('Audio ijro etilmadi: ' + e.message));
            }
        };
    </script>
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
                        <button type="button" 
                                onclick="window.goToBuddyReading({{ $myMember?->current_page ?? 1 }})" 
                                class="px-3 py-1.5 rounded-btn bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
                                title="Kitobni ochish va 3D mutolaa qilish (Ovozli suhbat uzilmaydi)">
                            <span>📖</span>
                            <span>Mutolaaga o'tish</span>
                            <span>→</span>
                        </button>
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
                <div wire:ignore x-data="buddyLiveVoiceRoom({
                    roomId: {{ $activeRoom->id }},
                    userId: {{ auth()->id() }},
                    initialActive: {{ ($myMember && $myMember->voice_active) ? 'true' : 'false' }},
                    wire: @this
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
                                class="px-2.5 py-1.5 rounded-btn text-xs font-mono font-bold border transition-colors flex items-center gap-1 active:scale-95 cursor-pointer"
                                :class="isMuted ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : 'bg-ink-800 text-mist hover:text-paper border-ink-border'"
                                :title="isMuted ? 'Ovozni yoqish' : 'Ovozni o\'chirish'">
                                <span x-text="isMuted ? '🔇 Ovoz o\'chiq' : '🎤 Ovoz ochiq'"></span>
                            </button>
                        </template>

                        <!-- Main Voice Enter/Leave Button -->
                        <button type="button" @click="toggleVoice()" 
                            :disabled="isConnecting"
                            class="px-3.5 py-1.5 rounded-btn text-xs font-bold font-mono transition-all flex items-center gap-1.5 shadow-sm whitespace-nowrap"
                            :class="voiceActive ? 'bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40' : 'bg-amber-500 hover:bg-amber-400 text-ink-950'">
                            <span x-text="isConnecting ? '⏳ Ulanmoqda...' : (voiceActive ? '🔇 Efirdan chiqish' : '🎙️ Ovozli efirga kirish')">🎙️ Ovozli efirga kirish</span>
                        </button>
                    </div>

                    <!-- ── Teleported Floating Mini Voice Dock (Visible while reading book) ── -->
                    <template x-teleport="body">
                        <div x-show="voiceActive && !isFullModalOpen" x-cloak
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             :style="'bottom: 84px; right: 1.25rem; z-index: 9999999; transform: translate3d(' + dragX + 'px, ' + dragY + 'px, 0);'"
                             @pointerdown="startDockDrag($event)"
                             class="fixed flex items-center gap-2 p-2 rounded-2xl bg-[#0b0f17]/95 border border-emerald-500/50 shadow-2xl backdrop-blur-xl text-paper select-none cursor-grab active:cursor-grabbing max-w-[calc(100vw-2rem)]">
                            
                            <!-- Drag Handle -->
                            <div class="pl-1 text-white/30 hover:text-white/60 cursor-grab active:cursor-grabbing shrink-0" title="Paneni surish">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                                    <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                    <circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/>
                                </svg>
                            </div>

                            <!-- Live Beacon & Partner Status -->
                            <button type="button" 
                                    @click.stop="openFullModal()" 
                                    @pointerdown.stop
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-colors text-left cursor-pointer"
                                    title="Suhbat va xonani to'liq ochish">
                                <span class="relative flex h-2.5 w-2.5 shrink-0">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                </span>
                                <div class="min-w-0 pr-1">
                                    <p class="text-[9px] font-mono font-bold uppercase tracking-wider text-emerald-400 leading-tight">Jonli Ovoz</p>
                                    <p class="text-xs font-semibold text-paper truncate max-w-[130px]" x-text="connectedPeersCount > 0 ? (connectedPeersCount + ' sherik ulangan') : 'Efirda siz (kutilmoqda)'"></p>
                                </div>
                            </button>

                            <!-- Live VU Meter -->
                            <div class="flex items-center gap-0.5 h-4 px-1" title="Sizning ovozingiz darajasi">
                                <template x-for="i in 5" :key="i">
                                    <span class="w-1 rounded-full transition-all duration-75"
                                          :class="(!isMuted && (i * 20) <= micLevel) ? 'bg-emerald-400' : 'bg-white/10'"
                                          :style="'height: ' + Math.max(4, Math.min(16, ((!isMuted && (i * 20) <= micLevel) ? (i * 3 + 4) : 4))) + 'px'"></span>
                                </template>
                            </div>

                            <!-- Mute Toggle -->
                            <button type="button" 
                                    @click.stop="toggleMute()"
                                    @pointerdown.stop
                                    class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold border transition-all flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95"
                                    :class="isMuted ? 'bg-rose-500/25 text-rose-300 border-rose-500/50 hover:bg-rose-500/35' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/30'"
                                    :title="isMuted ? 'Ovozni yoqish' : 'Ovozni o\'chirish'">
                                <span x-text="isMuted ? '🔇 Ovoz o\'chiq' : '🎤 Ovoz ochiq'"></span>
                            </button>

                            <!-- Re-open Modal / Chat -->
                            <button type="button" 
                                    @click.stop="openFullModal()"
                                    @pointerdown.stop
                                    class="p-2 rounded-xl text-mist hover:text-amber-400 hover:bg-white/10 border border-white/5 transition-colors cursor-pointer"
                                    title="Do'stlar xonasi va chatni ochish">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </button>

                            <!-- Leave Voice -->
                            <button type="button" 
                                    @click.stop="stopVoice()"
                                    @pointerdown.stop
                                    class="p-2 rounded-xl text-mist hover:text-rose-400 hover:bg-rose-500/10 border border-white/5 transition-colors cursor-pointer"
                                    title="Ovozli efirdan chiqish">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </template>
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
                                        onclick="if(!this.closest('[x-data]')?._x_dataStack && window.fallbackVoiceToggle){ window.fallbackVoiceToggle(this, '{{ $msg->audio_url }}'); }"
                                        class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-transform active:scale-95 bg-amber-500 hover:bg-amber-400 text-ink-950 shadow-md cursor-pointer"
                                        title="Ovozli xabarni eshitish (Tinglash)">
                                    <svg x-show="!playing" class="w-4 h-4 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg x-show="playing" x-cloak class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
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
                        navigator.mediaDevices.getUserMedia({
                            audio: {
                                echoCancellation: true,
                                noiseSuppression: true,
                                autoGainControl: true,
                                channelCount: 1,
                                sampleRate: 48000
                            }
                        }).then(stream => {
                            this.isPreparing = false;
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
                                @this.call('sendVoiceNote', data.path, data.duration || dur);
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
                                title="Ovozli xabar yozish (Mikrofon)"
                                class="w-9 h-9 rounded-btn bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/40 text-amber-400 hover:text-amber-300 flex items-center justify-center shrink-0 transition-all shadow-sm"
                                :class="{ 'opacity-60 cursor-wait border-rose-500 text-rose-400': isPreparing }">
                            <svg x-show="isPreparing" x-cloak class="animate-spin w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="!isPreparing" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
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
                        <button type="button" 
                                onclick="window.goToBuddyReading(1)" 
                                class="px-3 py-1.5 rounded-btn bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1 shadow-sm transition-all cursor-pointer"
                                title="Kitobni ochish va 3D mutolaa qilish (Ovozli suhbat uzilmaydi)">
                            <span>📖</span>
                            <span>Mutolaaga o'tish</span>
                        </button>
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
