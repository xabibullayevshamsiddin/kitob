    <style>
        /* ── TWO-COLUMN STUDIO GRID & CHAT RESPONSIVENESS ── */
        .live-studio-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
            width: 100% !important;
            align-items: start !important;
        }

        @media (min-width: 1024px) {
            .live-studio-grid.chat-is-open {
                grid-template-columns: minmax(0, 1fr) 380px !important;
            }
            .live-studio-grid.chat-is-closed {
                grid-template-columns: minmax(0, 1fr) !important;
            }
        }

        @media (min-width: 1400px) {
            .live-studio-grid.chat-is-open {
                grid-template-columns: minmax(0, 1fr) 420px !important;
            }
        }

        .live-video-column {
            min-width: 0 !important;
            width: 100% !important;
        }

        .live-chat-column {
            min-width: 0 !important;
            width: 100% !important;
            height: 620px;
            max-height: 80vh;
        }

        @media (min-width: 1024px) {
            .live-chat-column {
                position: sticky !important;
                top: 5rem !important;
                height: min(720px, calc(100vh - 6.5rem)) !important;
                max-height: calc(100vh - 6.5rem) !important;
            }
        }

        @media (max-width: 1023px) {
            .live-chat-column {
                height: 520px !important;
                max-height: 70vh !important;
            }
        }

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

        #liveVideoViewport:fullscreen,
        #liveVideoViewport:-webkit-full-screen {
            width: 100vw;
            height: 100vh;
            max-width: none;
            aspect-ratio: auto;
            border-radius: 0;
            border: 0;
            background: #05070d;
        }

        #liveVideoViewport:fullscreen #liveVideoPlayer,
        #liveVideoViewport:-webkit-full-screen #liveVideoPlayer {
            /* Object-fit rejimini foydalanuvchi tanlagan videoFitMode bo'yicha dinamik saqlash */
        }

        .live-viewport-docked {
            position: fixed !important;
            bottom: max(1.25rem, env(safe-area-inset-bottom, 1.25rem)) !important;
            right: 1.25rem !important;
            left: auto !important;
            width: min(92vw, 360px) !important;
            aspect-ratio: 16 / 9 !important;
            z-index: 60 !important;
            border-radius: 1rem !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(245, 158, 11, 0.4) !important;
            touch-action: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            cursor: grab;
            will-change: transform;
        }
        .live-viewport-docked,
        .live-viewport-docked * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-user-drag: none !important;
        }
        .live-viewport-docked ::selection {
            background: transparent !important;
            color: inherit !important;
        }
        .live-viewport-docked ::-moz-selection {
            background: transparent !important;
            color: inherit !important;
        }
        .live-viewport-docked.is-docked-dragging {
            cursor: grabbing !important;
            transition: none !important;
        }
        @media (max-width: 640px) {
            .live-viewport-docked {
                bottom: calc(5.25rem + env(safe-area-inset-bottom, 0px)) !important;
                right: 0.75rem !important;
                left: auto !important;
                width: calc(100vw - 1.5rem) !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #liveVideoPlayer {
                transition: none;
            }
        }

        /* ── YouTube-Style Action Pills & Menus ── */
        .live-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 2.375rem;
            padding: 0 0.95rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1;
            color: #f6f3eb;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .live-pill svg,
        .live-btn-icon {
            width: 1.125rem !important; /* 18px */
            height: 1.125rem !important;
            min-width: 1.125rem !important;
            min-height: 1.125rem !important;
            max-width: 1.125rem !important;
            max-height: 1.125rem !important;
            flex-shrink: 0 !important;
            vertical-align: middle;
            pointer-events: none;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.15s ease, fill 0.15s ease;
        }
        .live-pill svg[style*="display: none"],
        .live-pill svg[x-cloak],
        .live-btn-icon[style*="display: none"],
        .live-btn-icon[x-cloak] {
            display: none !important;
        }
        .live-pill:hover .live-btn-icon,
        .live-pill:hover svg {
            transform: scale(1.1);
        }
        .live-pill:active .live-btn-icon,
        .live-pill:active svg {
            transform: scale(0.92);
        }
        .live-pill--liked .live-btn-icon,
        .live-pill--liked svg {
            filter: drop-shadow(0 0 6px rgba(244, 63, 94, 0.5));
        }
        .live-menu-icon {
            width: 1rem !important; /* 16px */
            height: 1rem !important;
            min-width: 1rem !important;
            min-height: 1rem !important;
            flex-shrink: 0 !important;
            vertical-align: middle;
        }
        .live-pill:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.22);
        }
        .live-pill:active {
            transform: scale(0.96);
        }
        .live-pill:focus-visible {
            outline: 2px solid #f59e0b;
            outline-offset: 2px;
        }
        .live-pill:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
        }
        .live-pill--icon {
            width: 2.375rem;
            padding: 0;
        }
        .live-pill--primary {
            background: #f6f3eb;
            color: #0c111d;
            border-color: #f6f3eb;
            font-weight: 700;
        }
        .live-pill--primary:hover {
            background: #ffffff;
            color: #000000;
            box-shadow: 0 4px 14px rgba(255, 255, 255, 0.18);
        }
        .live-pill--muted {
            background: rgba(255, 255, 255, 0.06);
            color: #a8b0bd;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .live-pill--muted:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }
        .live-pill--liked {
            background: rgba(244, 63, 94, 0.18);
            color: #fda4af;
            border-color: rgba(244, 63, 94, 0.4);
        }
        .live-pill--liked:hover {
            background: rgba(244, 63, 94, 0.28);
            border-color: rgba(244, 63, 94, 0.55);
            color: #ffffff;
        }
        .live-pill--accent {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.35);
        }
        .live-pill--accent:hover {
            background: rgba(245, 158, 11, 0.25);
            border-color: rgba(245, 158, 11, 0.5);
        }

        .live-menu {
            position: absolute;
            top: calc(100% + 0.45rem);
            z-index: 50;
            border-radius: 0.875rem;
            background: #0f1523;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
        }
        .live-menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            color: #d1d5db;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: background 0.12s ease, color 0.12s ease;
            text-align: left;
        }
        .live-menu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        .live-menu-item kbd {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.3rem;
            font-size: 0.6875rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #9ca3af;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.25rem;
        }

        .live-share-tile {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e5e7eb;
            text-decoration: none;
            font-size: 0.6875rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .live-share-tile:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .live-share-tile svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .live-desc-box {
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .live-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

    </style>
