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

/**
 * enhanceOpusAudioSdp — WebRTC Opus audio parametrlarini studiya sifatiga moslashtirish:
 * - maxaveragebitrate=64000 (64 kbps tiniq inson nutqi)
 * - useinbandfec=1 (paketlar yo'qolganda avtomatik tiklash, ovoz uzilib qolmaydi)
 * - usedtx=0 (so'z boshidagi yoki jimlikdan keyingi bo'g'inlar yutib ketilmaydi)
 * - minptime=10 (kechikishni 10ms ga kamaytiradi)
 */
function enhanceOpusAudioSdp(sdp) {
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
}

/**
 * applyAudioSenderQuality — RTCRtpSender orqali audio paketlariga tarmoqda eng yuqori ustuvorlik
 * va 64kbps bitreyt berish (video tiqilib qolsa ham ovoz tiniq va kechikishsiz o'tadi).
 */
function applyAudioSenderQuality(pc) {
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
        videoSettingsOpen: false,
        videoFitMode: ['cover', 'contain', 'cinema'].includes(localStorage.getItem('kitob_video_fit')) ? localStorage.getItem('kitob_video_fit') : 'cover',
        isVideoFullscreen: false,
        canUseFullscreen: typeof document !== 'undefined' && !!(
            document.fullscreenEnabled ||
            document.webkitFullscreenEnabled ||
            (typeof HTMLVideoElement !== 'undefined' && HTMLVideoElement.prototype.webkitEnterFullscreen)
        ),
        canUsePip: typeof document !== 'undefined' && !!document.pictureInPictureEnabled,
        isPipActive: false,
        isDockedMini: false,
        dockedDragX: 0,
        dockedDragY: 0,
        isDockedDragging: false,
        dockedHasMoved: false,
        dockedDragStartX: 0,
        dockedDragStartY: 0,
        dockedInitialX: 0,
        dockedInitialY: 0,
        dockedMinX: -9999,
        dockedMaxX: 9999,
        dockedMinY: -9999,
        dockedMaxY: 9999,
        _onDockedMove: null,
        _onDockedUp: null,

        // YouTube-style Info Bar, Guest Support, Likes & Follow
        hasLiked: Boolean(config.initialLiked || false),
        isFollowing: Boolean(config.initialFollowing || false),
        followersCount: Number(config.initialFollowers || 0),
        isGuest: Boolean(config.isGuest),
        loginUrl: config.loginUrl || '/login',
        likeBusy: false,
        followBusy: false,
        chatOpen: (function() {
            try {
                const stored = localStorage.getItem('kitob_live_chat_open');
                return stored === null ? true : stored === 'true';
            } catch(e) {
                return true;
            }
        })(),
        shareOpen: false,
        shareCopied: false,
        moreOpen: false,
        descExpanded: false,
        authPrompt: '',
        shareUrl: config.liveUrl || (typeof window !== 'undefined' ? window.location.href : ''),
        shareText: config.eventTitle ? ('🔴 Jonli efir: ' + config.eventTitle + ' — Tomosha qiling!') : '🔴 Jonli efir — Tomosha qiling!',
        startedAgoLabel: 'hozirgina',

        // Real-time Likes & Viewers
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
        streamEndedReason: '',
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
                { urls: 'stun:stun.services.mozilla.com:3478' },
                { urls: 'stun:stun.relay.metered.ca:80' },
                @if (!empty(config('webrtc.turn.urls')))
                @json(config('webrtc.turn')),
                @endif
            ],
            iceCandidatePoolSize: 10,
        },

        async initStudio() {
            const syncFullscreenState = () => {
                const viewport = document.getElementById('liveVideoViewport');
                this.isVideoFullscreen = !!viewport && (
                    document.fullscreenElement === viewport ||
                    document.webkitFullscreenElement === viewport
                );
            };
            document.addEventListener('fullscreenchange', syncFullscreenState);
            document.addEventListener('webkitfullscreenchange', syncFullscreenState);

            // Picture-in-Picture (Rasm ichida rasm) holatini kuzatish
            const videoEl = document.getElementById('liveVideoPlayer');
            if (videoEl) {
                videoEl.addEventListener('enterpictureinpicture', () => {
                    this.isPipActive = true;
                });
                videoEl.addEventListener('leavepictureinpicture', () => {
                    this.isPipActive = false;
                });
            }

            // Kadr rejimini darhol qo'llash
            this.$nextTick(() => {
                this.setVideoFit(this.videoFitMode);
            });

            // 1. Duration Synchronizer (server-clock aligned)
            const clientNow = Math.floor(Date.now() / 1000);
            const clockSkew = clientNow - serverNow;
            const maxDurationSec = 5 * 3600; // 5 soatlik maksimal limit (18,000 soniya)
            const updateTimer = () => {
                const nowSec = Math.floor(Date.now() / 1000) - clockSkew;
                this.durationSeconds = Math.max(0, nowSec - startedAt);
                this.updateStartedAgo();

                // 5 soat bo'lishi bilan efir darhol avtomatik yakunlanadi
                if (this.durationSeconds >= maxDurationSec && !this.streamEnded) {
                    this.handleMaxDurationReached();
                }
            };
            updateTimer();
            this.durationInterval = setInterval(updateTimer, 1000);

            // 2. Setup WebRTC Signaling (BroadcastChannel + HTTP Polling fallback)
            this.setupSignaling();

            // Efir holatini brauzer xotirasiga saqlash (boshqa sahifalarga o'tganda mini pleyer davom etishi uchun)
            this.saveLiveStateToStorage();

            window.addEventListener('beforeunload', () => {
                if (!this.streamEnded) {
                    this.saveLiveStateToStorage();
                }
            });

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (!link || !link.href) return;
                if (!this.streamEnded && link.hostname === window.location.hostname) {
                    this.saveLiveStateToStorage();
                }
            }, { passive: true });

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

            // Chatni ochish/yopish eventini tinglash (chat panelidagi 'X' tugmasi uchun)
            window.addEventListener('toggle-live-chat', () => this.toggleChat());

            // Tezkor tugmalar (L: like, C: chat, F: to'liq ekran, I: mini oyna, M: ovoz)
            window.addEventListener('keydown', (e) => {
                const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                if (tag === 'input' || tag === 'textarea' || tag === 'select' || (e.target && e.target.isContentEditable) || (e.target && e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"]'))) {
                    return;
                }
                if (e.ctrlKey || e.metaKey || e.altKey) return;

                const key = e.key ? e.key.toLowerCase() : '';
                if (key === 'l') {
                    e.preventDefault();
                    this.toggleLike();
                } else if (key === 'c') {
                    e.preventDefault();
                    this.toggleChat();
                } else if (key === 'f') {
                    e.preventDefault();
                    if (this.canUseFullscreen) {
                        this.toggleVideoFullscreen();
                    }
                } else if (key === 'i') {
                    e.preventDefault();
                    this.activateMiniPlayer();
                } else if (key === 'm') {
                    if (!this.isHost) {
                        e.preventDefault();
                        this.toggleViewerMute();
                    }
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

        setVideoFit(mode) {
            const valid = ['cover', 'contain', 'cinema'];
            this.videoFitMode = valid.includes(mode) ? mode : 'cover';
            try {
                localStorage.setItem('kitob_video_fit', this.videoFitMode);
            } catch (e) {}
            this.applyVideoFitStyles();
        },

        cycleVideoFit() {
            const modes = ['cover', 'contain', 'cinema'];
            const idx = modes.indexOf(this.videoFitMode);
            const next = modes[(idx + 1) % modes.length];
            this.setVideoFit(next);
        },

        async toggleVideoFullscreen() {
            const viewport = document.getElementById('liveVideoViewport');
            const videoEl = document.getElementById('liveVideoPlayer');
            if (!viewport) return;

            // Agar burchakka qadalgan mini rejim faol bo'lsa, to'liq ekranga o'tganda uni to'xtatamiz
            if (this.isDockedMini) {
                this.isDockedMini = false;
            }

            try {
                if (document.fullscreenElement || document.webkitFullscreenElement) {
                    if (document.exitFullscreen) {
                        await document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                    return;
                }

                if (viewport.requestFullscreen) {
                    await viewport.requestFullscreen();
                } else if (viewport.webkitRequestFullscreen) {
                    viewport.webkitRequestFullscreen();
                } else if (videoEl && videoEl.webkitEnterFullscreen) {
                    videoEl.webkitEnterFullscreen();
                }
            } catch (err) {
                console.warn('[VIDEO] Fullscreen unavailable:', err);
            }
        },

        activateMiniPlayer() {
            this.isDockedMini = !this.isDockedMini;
            if (!this.isDockedMini) {
                this.dockedDragX = 0;
                this.dockedDragY = 0;
            }
            this.saveLiveStateToStorage();
        },

        restoreDockedVideo() {
            this.isDockedMini = false;
            this.dockedDragX = 0;
            this.dockedDragY = 0;
        },

        startDockedDrag(e) {
            if (!this.isDockedMini) return;
            if (e.target.closest('button, a, input, select, textarea')) return;
            if (e.button !== undefined && e.button !== 0) return;

            this.isDockedDragging = true;
            this.dockedHasMoved = false;
            this.dockedDragStartX = e.clientX;
            this.dockedDragStartY = e.clientY;
            this.dockedInitialX = this.dockedDragX;
            this.dockedInitialY = this.dockedDragY;

            const viewport = document.getElementById('liveVideoViewport');
            if (viewport) {
                const rect = viewport.getBoundingClientRect();
                const pad = 10;
                this.dockedMinX = this.dockedInitialX + (pad - rect.left);
                this.dockedMaxX = this.dockedInitialX + (window.innerWidth - rect.width - pad - rect.left);
                this.dockedMinY = this.dockedInitialY + (pad - rect.top);
                this.dockedMaxY = this.dockedInitialY + (window.innerHeight - rect.height - pad - rect.top);
            }

            this._onDockedMove = (evt) => this.onDockedDrag(evt);
            this._onDockedUp = (evt) => this.stopDockedDrag(evt);

            window.addEventListener('pointermove', this._onDockedMove, { passive: false });
            window.addEventListener('pointerup', this._onDockedUp);
            window.addEventListener('pointercancel', this._onDockedUp);

            if (e.cancelable) e.preventDefault();
        },

        onDockedDrag(e) {
            if (!this.isDockedDragging) return;
            const dx = e.clientX - this.dockedDragStartX;
            const dy = e.clientY - this.dockedDragStartY;

            if (!this.dockedHasMoved && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
                this.dockedHasMoved = true;
            }

            let targetX = this.dockedInitialX + dx;
            let targetY = this.dockedInitialY + dy;

            targetX = Math.max(this.dockedMinX, Math.min(this.dockedMaxX, targetX));
            targetY = Math.max(this.dockedMinY, Math.min(this.dockedMaxY, targetY));

            this.dockedDragX = Math.round(targetX);
            this.dockedDragY = Math.round(targetY);

            if (e.cancelable) e.preventDefault();
        },

        stopDockedDrag(e) {
            if (!this.isDockedDragging) return;
            this.isDockedDragging = false;

            if (this._onDockedMove) window.removeEventListener('pointermove', this._onDockedMove);
            if (this._onDockedUp) {
                window.removeEventListener('pointerup', this._onDockedUp);
                window.removeEventListener('pointercancel', this._onDockedUp);
            }
            this._onDockedMove = null;
            this._onDockedUp = null;

            if (!this.dockedHasMoved && !this.isHost && (this.needsUnmute || this.isViewerMuted)) {
                this.unmuteAudio();
            }
        },

        async toggleNativePip() {
            const videoEl = document.getElementById('liveVideoPlayer');
            if (!videoEl || typeof videoEl.requestPictureInPicture !== 'function') return;
            try {
                if (document.pictureInPictureElement) {
                    await document.exitPictureInPicture();
                    this.isPipActive = false;
                } else {
                    await videoEl.requestPictureInPicture();
                    this.isPipActive = true;
                }
            } catch (e) {
                console.warn('[PIP] Native PiP error:', e);
            }
        },

        async exitPip() {
            if (document.pictureInPictureElement) {
                try {
                    await document.exitPictureInPicture();
                } catch (e) {}
            }
            this.isPipActive = false;
        },

        saveLiveStateToStorage() {
            if (this.streamEnded || this.isHost) return;
            const state = {
                eventId: this.eventId,
                title: config.eventTitle || 'Jonli efir',
                hostName: config.hostName || '',
                liveUrl: config.liveUrl || window.location.href,
                signalSendUrl: this.signalSendUrl,
                signalPollUrl: this.signalPollUrl,
                csrfToken: this.csrfToken,
                userId: config.userId || null,
                timestamp: Date.now()
            };

            try {
                localStorage.setItem('kitobxon_live_state', JSON.stringify(state));
            } catch (e) {}

            window.dispatchEvent(new CustomEvent('activate-global-live-player', { detail: state }));
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
                if (res.status === 404 || res.status === 410) {
                    // Efir o'chirilgan yoki 5 soatlik limitga yetgan — "Efir tugadi" ekranini ko'rsat
                    this.showStreamEnded('Jonli efir 5 soatlik maksimal limitga yetdi va avtomatik yakunlandi.');
                    return;
                }
                if (!res.ok) return;
                const data = await res.json();
                if (data.stream_ended) {
                    this.showStreamEnded(data.reason || 'Jonli efir 5 soatlik maksimal limitga yetdi va avtomatik yakunlandi.');
                    return;
                }
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

        // 5 soatlik maksimal vaqtga yetganda avtomatik yakunlash
        handleMaxDurationReached() {
            if (this.streamEnded) return;
            console.log('[LIVE] 5 soatlik limitga yetildi. Efir avtomatik yakunlanmoqda...');
            this.showStreamEnded('Jonli efir 5 soatlik maksimal limitga yetdi va avtomatik yakunlandi.');

            if (this.isHost) {
                this.sendSignal('stream-status', 'all', {
                    active: false,
                    ended: true,
                    reason: 'Jonli efir 5 soatlik maksimal vaqtga yetdi va avtomatik yakunlandi.'
                });
                if (window.Livewire) {
                    @this.call('endLiveStream');
                }
            }
        },

        // Efir tugadi: polling/WebRTC to'xtatiladi, "Efir tugadi" overlay ko'rsatiladi
        showStreamEnded(reason) {
            if (this.streamEnded) return;
            this.streamEnded = true;
            if (reason) this.streamEndedReason = reason;
            try { localStorage.removeItem('kitobxon_live_state'); } catch (e) {}
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
                        applyAudioSenderQuality(pc);

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
                const enhancedSdp = enhanceOpusAudioSdp(offer.sdp);
                const tunedOffer = new RTCSessionDescription({
                    type: offer.type,
                    sdp: enhancedSdp
                });
                await pc.setLocalDescription(tunedOffer);
                this.applyVideoSenderQuality(pc);
                applyAudioSenderQuality(pc);
                await this.sendSignal('offer', viewerId, {
                    type: tunedOffer.type,
                    sdp: normalizeSdp(tunedOffer.sdp)
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
                const enhancedSdp = enhanceOpusAudioSdp(answer.sdp);
                const tunedAnswer = new RTCSessionDescription({
                    type: answer.type,
                    sdp: enhancedSdp
                });
                await pc.setLocalDescription(tunedAnswer);
                applyAudioSenderQuality(pc);

                console.log('[VIEWER] Answer SDP has audio?', tunedAnswer.sdp.includes('m=audio'));

                await this.sendSignal('answer', 'host:*', {
                    type: tunedAnswer.type,
                    sdp: normalizeSdp(tunedAnswer.sdp)
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

            // 1. Primary Video Player (Video display ONLY - always muted to prevent double-audio robotic echo)
            if (videoEl) {
                if (videoEl.srcObject !== this.remoteStream) {
                    videoEl.srcObject = this.remoteStream;
                }
                videoEl.muted = true; // Video element is strictly silent visual display
                const vPlay = videoEl.play();
                if (vPlay !== undefined) {
                    vPlay.catch(err => {
                        if (err.name === 'AbortError') {
                            setTimeout(() => { if (videoEl) videoEl.play().catch(() => {}); }, 120);
                        }
                    });
                }
            }

            // 2. Dedicated Single Audio Output (Exclusively handles all stream sound with zero phase interference)
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
                        console.log('[VIEWER] audioEl playback ACTIVE (crystal clear dedicated output)');
                        this.needsUnmute = false;
                    }).catch(err => {
                        if (err.name !== 'AbortError') {
                            console.warn('[VIEWER] Autoplay policy blocked unmuted audio, user interaction required:', err);
                            audioEl.muted = true;
                            audioEl.play().catch(() => {});
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

            // Video player stays muted to prevent double audio playback and flanging
            if (videoEl) {
                videoEl.muted = true;
                videoEl.play().catch(() => {});
            }
            // Audio player exclusively outputs sound
            if (audioEl) {
                audioEl.muted = false;
                audioEl.volume = 1.0;
                audioEl.play().catch(() => {});
            }
            if (this.audioContext && this.audioContext.state === 'suspended') {
                this.audioContext.resume().catch(() => {});
            }
            console.log('[VIEWER] unmuteAudio: dedicated audio player unmuted, volume=1.0');
        },

        toggleViewerMute() {
            this.isViewerMuted = !this.isViewerMuted;
            const audioEl = document.getElementById('liveAudioPlayer');
            const videoEl = document.getElementById('liveVideoPlayer');
            if (audioEl) audioEl.muted = this.isViewerMuted;
            if (videoEl) videoEl.muted = true; // Always muted
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

        async optimizeCameraTrack(track) {
            if (!track || typeof track.getCapabilities !== 'function' || typeof track.applyConstraints !== 'function') {
                return;
            }

            try {
                const capabilities = track.getCapabilities() || {};
                const supported = {};
                const enumValue = (name, preferred) => {
                    const values = capabilities[name];
                    if (Array.isArray(values) && values.includes(preferred)) {
                        supported[name] = preferred;
                    }
                };
                const rangeValue = (name, ratio) => {
                    const range = capabilities[name];
                    if (!range || !Number.isFinite(range.min) || !Number.isFinite(range.max)) {
                        return;
                    }
                    supported[name] = range.min + ((range.max - range.min) * ratio);
                };

                // Keep the camera's own adaptive controls active in changing room light.
                enumValue('exposureMode', 'continuous');
                enumValue('whiteBalanceMode', 'continuous');
                enumValue('focusMode', 'continuous');

                // Only use hardware controls when the browser exposes a safe numeric range.
                // A mild lift is intentional; the CSS preset handles the final preview polish.
                rangeValue('brightness', 0.58);
                rangeValue('contrast', 0.54);
                rangeValue('saturation', 0.52);

                if (Object.keys(supported).length > 0) {
                    await track.applyConstraints(supported);
                }

                // Ask WebRTC encoders to preserve camera detail where supported.
                if ('contentHint' in track) {
                    track.contentHint = 'detail';
                }
            } catch (err) {
                // Camera capability support is inconsistent across browsers and devices.
                // Rendering and streaming must continue when a vendor rejects a hint.
                console.debug('[CAMERA] Optional image tuning skipped:', err);
            }
        },

        get videoElementStyle() {
            let fit = 'contain';
            let scale = '1';

            if (this.videoFitMode === 'cover') {
                fit = 'cover';
                scale = '1.18';
            } else if (this.videoFitMode === 'cinema') {
                fit = 'cover';
                scale = '1';
            } else {
                fit = 'contain';
                scale = '1';
            }

            return 'object-fit: ' + fit + ' !important; transform: scale(' + scale + '); transform-origin: center center; background-color: #000; transition: transform 0.35s cubic-bezier(0.2, 0, 0, 1), object-fit 0.3s ease; ' + this.videoFilterStyle;
        },

        get viewportStyle() {
            if (this.isDockedMini) {
                return 'transform: translate3d(' + this.dockedDragX + 'px, ' + this.dockedDragY + 'px, 0); ' + (this.isDockedDragging ? 'transition: none !important;' : '');
            }
            if (this.videoFitMode === 'cinema') {
                return 'aspect-ratio: 21 / 9 !important; transition: aspect-ratio 0.35s cubic-bezier(0.2, 0, 0, 1);';
            }
            return 'aspect-ratio: 16 / 9 !important; transition: aspect-ratio 0.35s cubic-bezier(0.2, 0, 0, 1);';
        },

        applyVideoFitStyles() {
            const videoEl = document.getElementById('liveVideoPlayer');
            const viewport = document.getElementById('liveVideoViewport');
            if (!videoEl) return;

            let fit = 'contain';
            let scale = '1';
            if (this.videoFitMode === 'cover') {
                fit = 'cover';
                scale = '1.18';
            } else if (this.videoFitMode === 'cinema') {
                fit = 'cover';
                scale = '1';
            } else {
                fit = 'contain';
                scale = '1';
            }

            videoEl.style.setProperty('object-fit', fit, 'important');
            videoEl.style.setProperty('transform', 'scale(' + scale + ')', 'important');
            videoEl.style.setProperty('transform-origin', 'center center', 'important');
            videoEl.style.setProperty('transition', 'transform 0.35s cubic-bezier(0.2, 0, 0, 1), object-fit 0.3s ease', 'important');

            if (viewport && !this.isDockedMini) {
                if (this.videoFitMode === 'cinema') {
                    viewport.style.setProperty('aspect-ratio', '21 / 9', 'important');
                } else {
                    viewport.style.setProperty('aspect-ratio', '16 / 9', 'important');
                }
            }
        },

        get videoFilterStyle() {
            if (this.videoEnhance === 'bright') {
                return 'filter: brightness(1.24) contrast(1.05) saturate(1.08) blur(0.18px);';
            }
            if (this.videoEnhance === 'sharp') {
                return 'filter: brightness(1.08) contrast(1.12) saturate(1.06) blur(0.08px);';
            }
            if (this.videoEnhance === 'hd' || this.videoEnhance === true || this.videoEnhance === 'true') {
                // Default low-light cleanup: lift shadows without washing out skin tones,
                // then use a barely perceptible blur to soften webcam sensor grain.
                return 'filter: brightness(1.16) contrast(1.08) saturate(1.12) blur(0.22px);';
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
                    await this.optimizeCameraTrack(newVideoTrack);
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
                        await this.optimizeCameraTrack(fbTrack);
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

                const baseAudioConstraints = {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 48000,
                    channelCount: 1
                };
                const audioConstraints = this.selectedAudioDevice
                    ? { ...baseAudioConstraints, deviceId: { ideal: this.selectedAudioDevice } }
                    : baseAudioConstraints;
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
                    await this.optimizeCameraTrack(vTrack);
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
                        audio: {
                            deviceId: { exact: deviceId },
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true,
                            sampleRate: 48000,
                            channelCount: 1
                        }
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
                        await this.optimizeCameraTrack(newVideoTrack);
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
                        await this.optimizeCameraTrack(newTrack);
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

        // ── YOUTUBE-STYLE LIKES (1 LIKE PER USER), FOLLOW, SHARE & CHAT TOGGLE ──
        async toggleLike() {
            if (this.isGuest) {
                this.authPrompt = "Like bosish uchun hisobingizga kiring";
                return;
            }
            if (this.likeBusy) return;
            this.likeBusy = true;

            const previousState = this.hasLiked;
            const previousTotal = this.likesCount;
            this.hasLiked = !previousState;
            this.likesCount = Math.max(0, previousTotal + (this.hasLiked ? 1 : -1));
            if (this.hasLiked) {
                this.spawnFloatingHeart(true);
            }

            try {
                const comp = this.$wire || (window.Livewire && this.$el ? window.Livewire.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) : null);
                if (comp && typeof comp.call === 'function') {
                    const res = await comp.call('toggleLike');
                    if (res && typeof res.liked !== 'undefined') {
                        this.hasLiked = Boolean(res.liked);
                        if (typeof res.total === 'number') {
                            this.likesCount = Number(res.total);
                        }
                    }
                }
            } catch (e) {
                console.error('toggleLike error:', e);
                this.hasLiked = previousState;
                this.likesCount = previousTotal;
            } finally {
                this.likeBusy = false;
            }
        },

        sendLike() {
            this.toggleLike();
        },

        burstLike() {
            this.toggleLike();
        },

        async toggleFollow() {
            if (this.isGuest) {
                this.authPrompt = @js(__('site.live.subscribe_login_prompt'));
                return;
            }
            if (this.followBusy) return;
            this.followBusy = true;

            const previousState = this.isFollowing;
            const previousCount = this.followersCount;
            this.isFollowing = !previousState;
            this.followersCount = Math.max(0, previousCount + (this.isFollowing ? 1 : -1));

            try {
                const comp = this.$wire || (window.Livewire && this.$el ? window.Livewire.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) : null);
                if (comp && typeof comp.call === 'function') {
                    const res = await comp.call('toggleFollow');
                    if (res && typeof res.following !== 'undefined') {
                        this.isFollowing = Boolean(res.following);
                        if (typeof res.count === 'number') {
                            this.followersCount = Number(res.count);
                        }
                    }
                }
            } catch (e) {
                console.error('toggleFollow error:', e);
                this.isFollowing = previousState;
                this.followersCount = previousCount;
            } finally {
                this.followBusy = false;
            }
        },

        toggleChat() {
            this.chatOpen = !this.chatOpen;
            try {
                localStorage.setItem('kitob_live_chat_open', this.chatOpen ? 'true' : 'false');
            } catch (e) {}
            if (this.chatOpen) {
                this.$nextTick(() => {
                    this.scrollChat();
                });
            }
        },

        async shareStream() {
            if (typeof navigator !== 'undefined' && navigator.share) {
                try {
                    await navigator.share({
                        title: config.eventTitle || 'Jonli efir',
                        text: this.shareText,
                        url: this.shareUrl
                    });
                    return;
                } catch (e) {
                    if (e.name === 'AbortError') return;
                }
            }
            this.shareOpen = !this.shareOpen;
        },

        async copyShareLink() {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(this.shareUrl);
                } else {
                    const input = document.createElement('input');
                    input.value = this.shareUrl;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                }
                this.shareCopied = true;
                setTimeout(() => { this.shareCopied = false; }, 2200);
            } catch (e) {
                console.warn('Copy link error:', e);
            }
        },

        updateStartedAgo() {
            const s = Math.max(0, this.durationSeconds || 0);
            if (s < 60) {
                this.startedAgoLabel = 'hozirgina';
            } else if (s < 3600) {
                this.startedAgoLabel = Math.floor(s / 60) + ' daqiqa oldin';
            } else {
                const h = Math.floor(s / 3600);
                const m = Math.floor((s % 3600) / 60);
                this.startedAgoLabel = h + ' soat ' + (m > 0 ? (m + ' daqiqa ') : '') + 'oldin';
            }
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
