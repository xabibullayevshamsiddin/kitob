        <!-- ════ RIGHT: REAL-TIME CHAT & QUESTIONS STREAM ════ -->
        {{-- Alohida chat komponent: wire:poll.2s bilan avtomatik yangilanadi (umumiy chat kabi).
             WebRTC skripti bu blokda emas — polling video ulanishini BUZMAYDI. --}}
        <div class="live-chat-column"
             x-show="chatOpen"
             x-cloak
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            @livewire('live.live-chat-panel', ['event' => $event, 'isHost' => $isHost], key('live-chat-'.$event->id))
        </div>

