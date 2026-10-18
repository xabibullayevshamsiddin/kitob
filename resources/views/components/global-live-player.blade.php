{{-- Persistent inline live mini-player. It reconnects as a viewer after full page navigation. --}}
<div x-data="globalLivePlayer()"
     x-cloak
     x-show="isVisible"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="global-live-dock"
     :class="{ 'is-dragging': isDragging }"
     :style="'transform: translate3d(' + dragX + 'px, ' + dragY + 'px, 0);'"
     @pointerdown="startDrag($event)">

    <div class="global-live-dock-card">
        <!-- Header -->
        <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-white/10 bg-[#0b0f17] select-none cursor-grab active:cursor-grabbing">
            <div class="flex items-center gap-2 min-w-0 pointer-events-none">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <p class="text-[9px] font-mono uppercase tracking-wider text-rose-300 font-bold leading-none">{{ __('site.global_live.badge') }}</p>
                        <span class="text-[8px] font-mono px-1 py-0.2 rounded bg-white/10 text-mist flex items-center gap-0.5" title="{{ __('site.global_live.drag_tooltip') }}">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M5 12l4-4M5 12l4 4M19 12l-4-4M19 12l-4 4"/></svg>
                            <span>{{ __('site.global_live.drag_text') }}</span>
                        </span>
                    </div>
                    <p class="truncate text-xs font-semibold text-paper mt-0.5" x-text="title || @js(__('site.global_live.badge'))"></p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0" @pointerdown.stop @mousedown.stop>
                <a :href="liveUrl"
                   class="inline-flex h-6 w-6 items-center justify-center rounded-btn text-mist hover:text-amber-300 hover:bg-white/10 transition-colors cursor-pointer"
                   title="{{ __('site.global_live.open_page') }}"
                   aria-label="{{ __('site.global_live.open_page') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                    </svg>
                </a>
                <button type="button" @click.stop="close()"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-btn text-mist hover:text-rose-300 hover:bg-rose-500/10 transition-colors cursor-pointer"
                        title="{{ __('site.global_live.close') }}"
                        aria-label="{{ __('site.global_live.close') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <!-- Video Frame Box -->
        <div class="global-live-dock-video-box">
            <video x-ref="video"
                   autoplay
                   playsinline
                   muted
                   :class="videoFitMode === 'contain' ? 'fit-contain' : ''"></video>
            <audio x-ref="audio" autoplay playsinline class="hidden"></audio>

            <div x-show="isConnecting && !hasRemoteStream" class="absolute inset-0 flex items-center justify-center bg-black/60 text-xs font-mono text-amber-300 p-2 text-center pointer-events-none">
                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>{{ __('site.global_live.connecting') }}</span>
            </div>

            <button x-show="needsUnmute && hasRemoteStream" type="button" @click.stop="unmute()" @pointerdown.stop @mousedown.stop
                    class="absolute inset-x-3 bottom-3 rounded-btn bg-[#C1392B] px-3 py-1.5 text-xs font-mono font-bold text-paper shadow-lg hover:bg-[#a63024] transition-colors flex items-center justify-center gap-1.5 z-20 cursor-pointer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                <span>{{ __('site.global_live.unmute') }}</span>
            </button>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between gap-2 px-3 py-1.5 text-[10px] font-mono text-mist bg-[#0b0f17] border-t border-white/10 select-none">
            <span class="truncate flex items-center gap-1.5 pointer-events-none">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                <span class="truncate" x-text="hostName || 'Kitobxon Studio'"></span>
            </span>
            <div @pointerdown.stop @mousedown.stop>
                <button type="button" @click.stop="toggleFit()"
                        class="shrink-0 rounded-btn px-2 py-0.5 text-amber-300 hover:bg-white/10 transition-colors cursor-pointer"
                        x-text="videoFitMode === 'cover' ? @js(__('site.global_live.fit_contain')) : @js(__('site.global_live.fit_cover'))"></button>
            </div>
        </div>
    </div>
</div>

<style>
.global-live-dock {
    position: fixed !important;
    right: 1.25rem !important;
    bottom: 1.25rem !important;
    width: 360px !important;
    max-width: min(92vw, 360px) !important;
    z-index: 999999 !important;
    pointer-events: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    touch-action: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
    will-change: transform;
}
.global-live-dock,
.global-live-dock * {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
    -webkit-user-drag: none !important;
}
.global-live-dock ::selection {
    background: transparent !important;
    color: inherit !important;
}
.global-live-dock ::-moz-selection {
    background: transparent !important;
    color: inherit !important;
}
.global-live-dock-card {
    overflow: hidden !important;
    border-radius: 1rem !important;
    background: #0b0f17 !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(245, 158, 11, 0.35) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    cursor: grab;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.global-live-dock-card:hover {
    border-color: rgba(245, 158, 11, 0.55) !important;
    box-shadow: 0 25px 65px -10px rgba(0, 0, 0, 0.98), 0 0 0 1.5px rgba(245, 158, 11, 0.55) !important;
}
.global-live-dock.is-dragging,
.global-live-dock.is-dragging .global-live-dock-card {
    cursor: grabbing !important;
    transition: none !important;
    border-color: rgba(245, 158, 11, 0.9) !important;
    box-shadow: 0 30px 70px -5px rgba(0, 0, 0, 1), 0 0 0 2px rgba(245, 158, 11, 0.9) !important;
}
.global-live-dock-video-box {
    position: relative !important;
    width: 100% !important;
    aspect-ratio: 16 / 9 !important;
    height: auto !important;
    background: #000000 !important;
    overflow: hidden !important;
    cursor: grab;
}
.global-live-dock.is-dragging .global-live-dock-video-box {
    cursor: grabbing !important;
}
.global-live-dock-video-box video {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    object-fit: cover !important;
    transform: scale(1.18);
    transform-origin: center center;
    transition: transform 0.35s cubic-bezier(0.2, 0, 0, 1), object-fit 0.3s ease;
    background: #000000 !important;
    pointer-events: none !important;
}
.global-live-dock-video-box video.fit-contain {
    object-fit: contain !important;
    transform: scale(1) !important;
}
@media (max-width: 640px) {
    .global-live-dock {
        right: 0.75rem !important;
        bottom: 5.5rem !important;
        width: calc(100vw - 1.5rem) !important;
        max-width: calc(100vw - 1.5rem) !important;
    }
}
</style>

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

            dragX: 0,
            dragY: 0,
            isDragging: false,
            hasMoved: false,
            dragStartX: 0,
            dragStartY: 0,
            initialDragX: 0,
            initialDragY: 0,
            minDragX: -9999,
            maxDragX: 9999,
            minDragY: -9999,
            maxDragY: 9999,
            _onPointerMove: null,
            _onPointerUp: null,
            _onResize: null,

            isOnLiveDetailPage() {
                try {
                    const path = window.location.pathname || '';
                    // Faqat aynan /live/{id} efir xonasi sahifasida bo'lsa global mini pleyer ko'rinmaydi
                    return /\/live\/\d+/.test(path);
                } catch (e) {
                    return false;
                }
            },

            init() {
                if (this.isOnLiveDetailPage()) {
                    this.isVisible = false;
                    return;
                }

                this.restoreDragPosition();

                this._onResize = () => this.clampPosition();
                window.addEventListener('resize', this._onResize);

                window.addEventListener('activate-global-live-player', (event) => {
                    if (this.isOnLiveDetailPage()) return;
                    this.activate(event.detail || {});
                });

                this.restoreState();
            },

            startDrag(e) {
                if (e.target.closest('button, a, input, select, textarea')) return;
                if (e.button !== undefined && e.button !== 0) return;

                this.isDragging = true;
                this.hasMoved = false;
                this.dragStartX = e.clientX;
                this.dragStartY = e.clientY;
                this.initialDragX = this.dragX;
                this.initialDragY = this.dragY;

                const rect = this.$el.getBoundingClientRect();
                const pad = 10;
                this.minDragX = this.initialDragX + (pad - rect.left);
                this.maxDragX = this.initialDragX + (window.innerWidth - rect.width - pad - rect.left);
                this.minDragY = this.initialDragY + (pad - rect.top);
                this.maxDragY = this.initialDragY + (window.innerHeight - rect.height - pad - rect.top);

                this._onPointerMove = (evt) => this.onDrag(evt);
                this._onPointerUp = (evt) => this.stopDrag(evt);

                window.addEventListener('pointermove', this._onPointerMove, { passive: false });
                window.addEventListener('pointerup', this._onPointerUp);
                window.addEventListener('pointercancel', this._onPointerUp);

                if (e.cancelable) e.preventDefault();
            },

            onDrag(e) {
                if (!this.isDragging) return;
                const dx = e.clientX - this.dragStartX;
                const dy = e.clientY - this.dragStartY;

                if (!this.hasMoved && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
                    this.hasMoved = true;
                }

                let targetX = this.initialDragX + dx;
                let targetY = this.initialDragY + dy;

                targetX = Math.max(this.minDragX, Math.min(this.maxDragX, targetX));
                targetY = Math.max(this.minDragY, Math.min(this.maxDragY, targetY));

                this.dragX = Math.round(targetX);
                this.dragY = Math.round(targetY);

                if (e.cancelable) e.preventDefault();
            },

            stopDrag(e) {
                if (!this.isDragging) return;
                this.isDragging = false;

                if (this._onPointerMove) window.removeEventListener('pointermove', this._onPointerMove);
                if (this._onPointerUp) {
                    window.removeEventListener('pointerup', this._onPointerUp);
                    window.removeEventListener('pointercancel', this._onPointerUp);
                }
                this._onPointerMove = null;
                this._onPointerUp = null;

                try {
                    localStorage.setItem('kitob_mini_drag_pos', JSON.stringify({ x: this.dragX, y: this.dragY }));
                } catch (err) {}

                if (!this.hasMoved && this.needsUnmute) {
                    this.unmute();
                }
            },

            clampPosition() {
                if (!this.$el || !this.isVisible) return;
                const rect = this.$el.getBoundingClientRect();
                if (!rect.width || !rect.height) return;

                const pad = 10;
                if (rect.left < pad) {
                    this.dragX += Math.round(pad - rect.left);
                } else if (rect.right > window.innerWidth - pad) {
                    this.dragX -= Math.round(rect.right - (window.innerWidth - pad));
                }

                if (rect.top < pad) {
                    this.dragY += Math.round(pad - rect.top);
                } else if (rect.bottom > window.innerHeight - pad) {
                    this.dragY -= Math.round(rect.bottom - (window.innerHeight - pad));
                }
            },

            restoreDragPosition() {
                try {
                    const saved = localStorage.getItem('kitob_mini_drag_pos');
                    if (saved) {
                        const pos = JSON.parse(saved);
                        if (typeof pos.x === 'number' && typeof pos.y === 'number') {
                            this.dragX = pos.x;
                            this.dragY = pos.y;
                        }
                    }
                } catch (err) {}
                this.$nextTick(() => this.clampPosition());
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
                const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                this.csrfToken = metaToken || data.csrfToken || '';
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

                if (this._connectTimeout) clearTimeout(this._connectTimeout);
                this._connectTimeout = setTimeout(() => {
                    if (!this.hasRemoteStream) {
                        this.close();
                    }
                }, 15000);

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
                    if (typeof payload.isHostOnline === 'boolean') {
                        this.isConnecting = !payload.isHostOnline;
                        if (!payload.isHostOnline) {
                            this.close();
                        }
                    }
                } else if (msg.type === 'stream-ended') {
                    this.close();
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
                if (this._connectTimeout) {
                    clearTimeout(this._connectTimeout);
                    this._connectTimeout = null;
                }
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
                if (this._connectTimeout) {
                    clearTimeout(this._connectTimeout);
                    this._connectTimeout = null;
                }
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
                if (this._onPointerMove) window.removeEventListener('pointermove', this._onPointerMove);
                if (this._onPointerUp) {
                    window.removeEventListener('pointerup', this._onPointerUp);
                    window.removeEventListener('pointercancel', this._onPointerUp);
                }
                this._onPointerMove = null;
                this._onPointerUp = null;
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
                this.dragX = 0;
                this.dragY = 0;
                try {
                    localStorage.removeItem('kitobxon_live_state');
                    localStorage.removeItem('kitob_mini_drag_pos');
                } catch (e) {}
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
