{{-- Persistent inline live mini-player. It reconnects as a viewer after full page navigation. --}}
<div x-data="globalLivePlayer()"
     x-cloak
     x-show="isVisible"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0 translate-y-3 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     class="fixed right-3 sm:right-5 bottom-[82px] sm:bottom-5 z-[60] w-[min(92vw,360px)] pointer-events-auto">

    <div class="overflow-hidden rounded-2xl bg-[#0b0f17]/95 border border-white/15 shadow-[0_18px_55px_rgba(0,0,0,0.65)] backdrop-blur-2xl ring-1 ring-amber-400/10">
        <div class="flex items-center justify-between gap-3 px-3 py-2 border-b border-white/10">
            <div class="flex items-center gap-2 min-w-0">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-mono uppercase tracking-wider text-rose-300">Jonli efir</p>
                    <p class="truncate text-xs font-semibold text-paper" x-text="title || 'Jonli efir'"></p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <a :href="liveUrl"
                   class="inline-flex h-7 w-7 items-center justify-center rounded-btn text-mist hover:text-amber-300 hover:bg-white/10 transition-colors"
                   title="Efir sahifasini ochish"
                   aria-label="Efir sahifasini ochish">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                    </svg>
                </a>
                <button type="button" @click="close()"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-btn text-mist hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                        title="Mini oynani yopish"
                        aria-label="Mini oynani yopish">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <div class="relative aspect-video bg-black" @click="if (needsUnmute) unmute()">
            <video x-ref="video"
                   autoplay
                   playsinline
                   muted
                   class="h-full w-full transition-opacity duration-200"
                   :class="videoFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'"></video>
            <audio x-ref="audio" autoplay playsinline class="hidden"></audio>

            <div x-show="isConnecting && !hasRemoteStream" class="absolute inset-0 flex items-center justify-center bg-ink-950/45 text-xs font-mono text-amber-300">
                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>Ulanmoqda...</span>
            </div>

            <button x-show="needsUnmute && hasRemoteStream" type="button" @click.stop="unmute()"
                    class="absolute inset-x-3 bottom-3 rounded-btn bg-[#C1392B]/90 px-3 py-2 text-xs font-mono font-bold text-paper shadow-lg hover:bg-[#a63024] transition-colors">
                Ovozini yoqish
            </button>
        </div>

        <div class="flex items-center justify-between gap-2 px-3 py-2 text-[10px] font-mono text-mist">
            <span class="truncate" x-text="hostName || 'Kitobxon Studio'"></span>
            <button type="button" @click="toggleFit()"
                    class="shrink-0 rounded-btn px-2 py-1 text-amber-300 hover:bg-white/10 transition-colors"
                    x-text="videoFitMode === 'cover' ? 'To‘liq kadr' : 'Kadrni to‘ldir'"></button>
        </div>
    </div>
</div>

<script>
(function () {
    function registerGlobalLivePlayer() {
        if (!window.Alpine) return;

        Alpine.data('globalLivePlayer', () => ({
            isVisible: false,
            isConnecting: false,
            hasRemoteStream: false,
            needsUnmute: true,
            streamEnded: false,
            title: '',
            hostName: '',
            liveUrl: '',
            eventId: null,
            signalSendUrl: '',
            signalPollUrl: '',
            csrfToken: '',
            userId: null,
            myPeerId: '',
            lastSignalId: 0,
            pollInterval: null,
            viewerPingInterval: null,
            retryInterval: null,
            broadcastChannel: null,
            peerConnection: null,
            remoteStream: null,
            offerHostPeerId: null,
            iceCandidateQueue: [],
            processedSignalKeys: new Set(),
            videoFitMode: localStorage.getItem('kitob_video_fit') === 'contain' ? 'contain' : 'cover',

            isOnLiveDetailPage() {
                try {
                    const path = window.location.pathname || '';
                    return /\/live(\/\d+)?(\/.*)?$/.test(path) || path.includes('/live/');
                } catch (e) {
                    return false;
                }
            },

            init() {
                if (this.isOnLiveDetailPage()) {
                    this.isVisible = false;
                    return;
                }

                window.addEventListener('activate-global-live-player', (event) => {
                    if (this.isOnLiveDetailPage()) return;
                    this.activate(event.detail || {});
                });

                this.restoreState();
            },

            activate(data, persist = true) {
                if (this.isOnLiveDetailPage()) return;
                if (!data || !data.eventId || !data.signalPollUrl || !data.signalSendUrl) return;

                const sameEvent = Number(this.eventId) === Number(data.eventId);
                this.eventId = Number(data.eventId);
                this.title = data.title || 'Jonli efir';
                this.hostName = data.hostName || '';
                this.liveUrl = data.liveUrl || '/live';
                this.signalSendUrl = data.signalSendUrl;
                this.signalPollUrl = data.signalPollUrl;
                this.csrfToken = data.csrfToken || '';
                this.userId = data.userId || null;
                this.isVisible = true;
                this.streamEnded = false;

                if (persist) {
                    try {
                        localStorage.setItem('kitobxon_live_state', JSON.stringify({
                            eventId: this.eventId,
                            title: this.title,
                            hostName: this.hostName,
                            liveUrl: this.liveUrl,
                            signalSendUrl: this.signalSendUrl,
                            signalPollUrl: this.signalPollUrl,
                            csrfToken: this.csrfToken,
                            userId: this.userId,
                            timestamp: Date.now()
                        }));
                    } catch (e) {}
                }

                const state = this.peerConnection?.connectionState;
                if (!sameEvent || !this.peerConnection || state === 'failed' || state === 'closed') {
                    this.stopConnection();
                    this.startViewer();
                }
            },

            restoreState() {
                if (this.isOnLiveDetailPage()) return;
                try {
                    const raw = localStorage.getItem('kitobxon_live_state');
                    if (!raw) return;
                    const saved = JSON.parse(raw);
                    if (!saved.timestamp || Date.now() - saved.timestamp > 12 * 60 * 60 * 1000) {
                        localStorage.removeItem('kitobxon_live_state');
                        return;
                    }
                    this.activate(saved, false);
                } catch (e) {
                    localStorage.removeItem('kitobxon_live_state');
                }
            },

            startViewer() {
                this.myPeerId = 'viewer_mini_' + (this.userId || 'anon') + '_' + Math.random().toString(36).slice(2, 9);
                this.lastSignalId = 0;
                this.offerHostPeerId = null;
                this.iceCandidateQueue = [];
                this.processedSignalKeys = new Set();
                this.remoteStream = null;
                this.hasRemoteStream = false;
                this.needsUnmute = true;
                this.isConnecting = true;
                this.setupSignaling();
                this.sendSignal('join', 'host:*', { ts: Date.now() });

                this.viewerPingInterval = setInterval(() => {
                    if (!this.isVisible || this.streamEnded) return;
                    this.sendSignal('viewer-ping', 'host:*', { ts: Date.now() });
                }, 3000);

                this.retryInterval = setInterval(() => {
                    const state = this.peerConnection?.connectionState;
                    if (!this.hasRemoteStream && state !== 'connecting' && state !== 'connected') {
                        this.sendSignal('join', 'host:*', { ts: Date.now() });
                    }
                }, 4000);
            },

            setupSignaling() {
                if ('BroadcastChannel' in window) {
                    try {
                        this.broadcastChannel = new BroadcastChannel('kitobxon_live_' + this.eventId);
                        this.broadcastChannel.onmessage = (event) => this.handleSignal(event.data);
                    } catch (e) {}
                }

                this.pollSignals();
                this.pollInterval = setInterval(() => this.pollSignals(), 700);
            },

            async pollSignals() {
                if (!this.signalPollUrl || !this.myPeerId) return;
                try {
                    const url = `${this.signalPollUrl}?peer_id=${encodeURIComponent(this.myPeerId)}&since_id=${this.lastSignalId}`;
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (response.status === 404) {
                        this.close();
                        return;
                    }
                    if (!response.ok) return;
                    const data = await response.json();
                    for (const signal of (data.signals || [])) {
                        if (signal.id > this.lastSignalId) this.lastSignalId = signal.id;
                        await this.handleSignal(signal);
                    }
                } catch (e) {}
            },

            async sendSignal(type, receiverId, payload) {
                if (!this.signalSendUrl || !this.myPeerId) return;
                const msgId = 'floating_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
                const msg = {
                    msg_id: msgId,
                    sender_id: this.myPeerId,
                    receiver_id: receiverId,
                    type: type,
                    payload: { ...(payload || {}), _msg_id: msgId }
                };

                if (this.broadcastChannel) {
                    try { this.broadcastChannel.postMessage(msg); } catch (e) {}
                }

                try {
                    await fetch(this.signalSendUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify(msg)
                    });
                } catch (e) {}
            },

            async handleSignal(msg) {
                if (!msg || !msg.type || msg.sender_id === this.myPeerId) return;
                if (msg.receiver_id && ![this.myPeerId, 'all', 'host:*'].includes(msg.receiver_id)) return;

                const payload = msg.payload || {};
                const key = msg.msg_id || payload._msg_id || (msg.id ? `db_${msg.id}` : `${msg.sender_id}_${msg.type}`);
                if (this.processedSignalKeys.has(key)) return;
                this.processedSignalKeys.add(key);
                if (this.processedSignalKeys.size > 500) {
                    this.processedSignalKeys.delete(this.processedSignalKeys.values().next().value);
                }

                if (msg.type === 'offer') {
                    await this.handleOffer(payload, msg.sender_id);
                } else if (msg.type === 'ice-candidate') {
                    await this.handleIce(payload, msg.sender_id);
                } else if (msg.type === 'stream-status') {
                    if (typeof payload.isHostOnline === 'boolean') this.isConnecting = !payload.isHostOnline;
                }
            },

            async handleOffer(offer, senderId) {
                if (!offer || !offer.sdp) return;
                if (this.peerConnection && ['connecting', 'connected'].includes(this.peerConnection.connectionState)) return;

                this.offerHostPeerId = senderId;
                const pc = new RTCPeerConnection({
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                        { urls: 'stun:stun.cloudflare.com:3478' }
                    ],
                    iceCandidatePoolSize: 10
                });
                this.peerConnection = pc;
                this.remoteStream = new MediaStream();

                pc.ontrack = (event) => {
                    if (!event.track || this.remoteStream.getTracks().some(track => track.id === event.track.id)) return;
                    this.remoteStream.addTrack(event.track);
                    this.hasRemoteStream = true;
                    this.isConnecting = false;
                    this.attachRemoteStream();
                };

                pc.onicecandidate = (event) => {
                    if (!event.candidate) return;
                    const candidate = event.candidate.toJSON ? event.candidate.toJSON() : event.candidate;
                    this.sendSignal('ice-candidate', 'host:*', candidate);
                };

                pc.onconnectionstatechange = () => {
                    if (['failed', 'closed', 'disconnected'].includes(pc.connectionState)) {
                        this.hasRemoteStream = false;
                        this.isConnecting = true;
                    }
                };

                try {
                    await pc.setRemoteDescription(new RTCSessionDescription({
                        type: offer.type,
                        sdp: this.normalizeSdp(offer.sdp)
                    }));

                    while (this.iceCandidateQueue.length) {
                        try { await pc.addIceCandidate(this.iceCandidateQueue.shift()); } catch (e) {}
                    }

                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    await this.sendSignal('answer', 'host:*', {
                        type: answer.type,
                        sdp: this.normalizeSdp(answer.sdp)
                    });
                } catch (e) {
                    this.isConnecting = true;
                }
            },

            async handleIce(data, senderId) {
                if (this.offerHostPeerId && senderId !== this.offerHostPeerId) return;
                if (!data || !data.candidate) return;
                const candidate = new RTCIceCandidate({
                    candidate: data.candidate,
                    sdpMid: data.sdpMid ?? '0',
                    sdpMLineIndex: data.sdpMLineIndex ?? 0
                });
                if (this.peerConnection?.remoteDescription) {
                    try { await this.peerConnection.addIceCandidate(candidate); } catch (e) {}
                } else {
                    this.iceCandidateQueue.push(candidate);
                }
            },

            attachRemoteStream() {
                if (!this.remoteStream) return;
                const video = this.$refs.video;
                const audio = this.$refs.audio;
                if (video && video.srcObject !== this.remoteStream) {
                    video.srcObject = this.remoteStream;
                    video.muted = true;
                    video.play().catch(() => {});
                }
                if (audio && audio.srcObject !== this.remoteStream) {
                    audio.srcObject = this.remoteStream;
                    audio.muted = this.needsUnmute;
                    audio.play().catch(() => { this.needsUnmute = true; });
                }
            },

            unmute() {
                this.needsUnmute = false;
                const audio = this.$refs.audio;
                if (audio) {
                    audio.muted = false;
                    audio.play().catch(() => {});
                }
            },

            toggleFit() {
                this.videoFitMode = this.videoFitMode === 'cover' ? 'contain' : 'cover';
                try { localStorage.setItem('kitob_video_fit', this.videoFitMode); } catch (e) {}
            },

            normalizeSdp(sdp) {
                return String(sdp || '').split(/\r\n|\r|\n/).map(line => line.trimEnd()).filter(Boolean).join('\r\n') + '\r\n';
            },

            stopConnection() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                if (this.viewerPingInterval) clearInterval(this.viewerPingInterval);
                if (this.retryInterval) clearInterval(this.retryInterval);
                this.pollInterval = null;
                this.viewerPingInterval = null;
                this.retryInterval = null;
                if (this.broadcastChannel) {
                    try { this.broadcastChannel.close(); } catch (e) {}
                    this.broadcastChannel = null;
                }
                if (this.peerConnection) {
                    try { this.peerConnection.close(); } catch (e) {}
                    this.peerConnection = null;
                }
                if (this.remoteStream) {
                    try { this.remoteStream.getTracks().forEach(track => track.stop()); } catch (e) {}
                    this.remoteStream = null;
                }
                if (this.$refs.video) this.$refs.video.srcObject = null;
                if (this.$refs.audio) this.$refs.audio.srcObject = null;
                this.hasRemoteStream = false;
            },

            close() {
                this.stopConnection();
                this.isVisible = false;
                this.streamEnded = false;
                try { localStorage.removeItem('kitobxon_live_state'); } catch (e) {}
            }
        }));
    }

    if (window.Alpine) {
        registerGlobalLivePlayer();
    } else {
        document.addEventListener('alpine:init', registerGlobalLivePlayer, { once: true });
    }
})();
</script>
