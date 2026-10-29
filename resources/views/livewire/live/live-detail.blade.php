<div class="max-w-7xl mx-auto space-y-6 pb-16"
    x-data="liveStudioController({
        isHost: @js($isHost),
        userId: @js(auth()->id()),
        eventId: {{ $event->id }},
        eventTitle: @js($event->title),
        hostName: @js($event->hostUser?->name ?? ''),
        startedAt: {{ $event->started_at ? $event->started_at->timestamp : ($event->created_at ? $event->created_at->timestamp : now()->timestamp) }},
        serverNow: {{ now()->timestamp }},
        initialLikes: {{ (int) ($event->likes_count ?? 0) }},
        initialLiked: @js((bool) $hasLiked),
        initialFollowing: @js((bool) $isFollowingHost),
        initialFollowers: {{ (int) $hostFollowersCount }},
        isGuest: @js(!auth()->check()),
        loginUrl: @js(route('login')),
        permissionMode: @js($event->permission_mode),
        signalSendUrl: @js(route('live.signal.send', $event)),
        signalPollUrl: @js(route('live.signal.poll', $event)),
        csrfToken: @js(csrf_token()),
        liveUrl: @js(route('live.show', $event)),
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
    @include('livewire.live.partials.header')

    <!-- ── 2. TWO-COLUMN STUDIO GRID ── -->
    <div class="live-studio-grid min-h-[620px] pb-12">
        <!-- ════ LEFT: BROADCAST VIDEO & HOST CONSOLE ════ -->
        <div class="space-y-4">
            @include('livewire.live.partials.video-viewport')
            @include('livewire.live.partials.host-toolbar')
        </div>

        <!-- ════ RIGHT: REAL-TIME CHAT & QUESTIONS STREAM ════ -->
        @include('livewire.live.partials.chat-column')
    </div>

    <!-- ── 3. STUDIO STYLES & WEBRTC CONTROLLER ── -->
    @include('livewire.live.partials.studio-styles')
    @include('livewire.live.partials.studio-controller')
</div>
