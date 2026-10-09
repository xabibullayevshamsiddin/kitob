/**
 * Kitobxon Ultra-Realistic 3D Flipbook Engine
 * Features:
 * - Full Interactive Mouse Drag & Touch Swipe 3D Turning Engine
 * - Single-click / tap on page to turn (right side -> next, left side -> prev)
 * - Real-time 3D page curl following cursor/finger with dynamic lighting & depth shadow
 * - Spring physics completion to next/prev spread with authentic paper audio
 * - High-DPI PDF.js rendering with offscreen canvas cache for 60fps instant transitions
 * - Dynamic viewport calculation (fits comfortably large and close to reader)
 * - Authentic page-turn sound using oxidvideos-page-flip-1-178322.mp3 audio pool
 * - Interactive Page Selector & Direct Jumper (Input + Dropdown)
 * - Zoom in/out/reset (+/-) with double-click zoom toggle
 * - Page scrubber slider & keyboard arrow navigation
 */

(function () {
  'use strict';

  // Global state
  let pdfDoc = null;
  let totalPages = 0;
  let currentSpread = 1; // page 1 = cover, then 2 (pages 2-3), 4 (pages 4-5)...
  let isFlipping = false;
  let zoomScale = 1.0;
  let isMobile = window.innerWidth < 900;
  let renderTasks = { left: null, right: null };
  let renderRequestId = 0;
  let initializedRoot = null;
  let soundEnabled = true;
  let cachedBaseViewport = null;

  // DOM elements cache
  let rootEl = null;
  let spreadEl = null;
  let canvasLeft = null;
  let canvasRight = null;
  let ctxLeft = null;
  let ctxRight = null;
  let flipContainer = null;
  let flipLeaf = null;
  let flipFront = null;
  let flipBack = null;
  let ctxFlipFront = null;
  let ctxFlipBack = null;
  let pageIndicator = null;
  let loadingOverlay = null;
  let sliderEl = null;
  let zoomLevelEl = null;

  // ── Ultra-Fast Zero-Latency Web Audio API Engine ──
  let webAudioCtx = null;
  let cachedAudioBuffer = null;
  let htmlAudioFallbackPool = [];
  const HTML_AUDIO_POOL_SIZE = 6;
  let htmlAudioPoolIdx = 0;
  let audioUnlocked = false;

  // ── High Performance Offscreen Page Canvas Cache ──
  const pageCanvasCache = new Map();
  const MAX_PAGE_CACHE = 24;

  function getCachedPageCanvas(pageNum, w, h) {
    const key = `${pageNum}_${w}x${h}`;
    return pageCanvasCache.get(key) || null;
  }

  function storeCachedPageCanvas(pageNum, w, h, sourceCanvas) {
    if (!sourceCanvas || sourceCanvas.width === 0 || sourceCanvas.height === 0) return;
    const key = `${pageNum}_${w}x${h}`;
    if (pageCanvasCache.size >= MAX_PAGE_CACHE) {
      const oldestKey = pageCanvasCache.keys().next().value;
      pageCanvasCache.delete(oldestKey);
    }
    const offscreen = document.createElement('canvas');
    offscreen.width = sourceCanvas.width;
    offscreen.height = sourceCanvas.height;
    const ctx = offscreen.getContext('2d');
    ctx.drawImage(sourceCanvas, 0, 0);
    pageCanvasCache.set(key, offscreen);
  }

  function clearPageCache() {
    pageCanvasCache.clear();
  }

  /**
   * Pre-warm AudioContext on earliest user touch/click/key for 0ms response
   */
  function unlockAudioContext() {
    if (audioUnlocked) return;
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;

    if (!webAudioCtx) {
      webAudioCtx = new AudioCtx();
    }
    if (webAudioCtx.state === 'suspended') {
      webAudioCtx.resume().catch(() => {});
    }
    audioUnlocked = true;
  }

  /**
   * Initialize and pre-decode page turn sound into RAM with auto-trimmed leading silence
   */
  function initAudio(soundUrl) {
    // 1. Setup HTML5 Audio fallback pool with eager preload
    htmlAudioFallbackPool = [];
    for (let i = 0; i < HTML_AUDIO_POOL_SIZE; i++) {
      const a = new Audio(soundUrl);
      a.preload = 'auto';
      htmlAudioFallbackPool.push(a);
    }

    // 2. Setup Web Audio API with zero latency
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;

    if (!webAudioCtx) {
      webAudioCtx = new AudioCtx();
    }

    // Fetch and decode MP3 into PCM in memory once
    fetch(soundUrl)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.arrayBuffer();
      })
      .then(arrayBuffer => webAudioCtx.decodeAudioData(arrayBuffer))
      .then(decodedBuffer => {
        cachedAudioBuffer = processCrispAudioBuffer(decodedBuffer);
      })
      .catch(err => {
        console.warn('Flipbook: Web Audio API dekodlashda xatolik, HTML5 Audio ishlatiladi:', err);
      });
  }

  /**
   * Trim leading silence and dead intro time so sound starts with 0ms delay
   */
  function processCrispAudioBuffer(buffer) {
    if (!webAudioCtx) return buffer;
    const pcm = buffer.getChannelData(0);
    const sampleRate = buffer.sampleRate;

    // Scan for peak amplitude
    let maxAmp = 0;
    for (let i = 0; i < pcm.length; i++) {
      const a = Math.abs(pcm[i]);
      if (a > maxAmp) maxAmp = a;
    }
    if (maxAmp < 0.01) return buffer;

    // Detect where audible paper rustle actually begins (5% of peak)
    const threshold = maxAmp * 0.05;
    let startSample = 0;
    for (let i = 0; i < pcm.length; i++) {
      if (Math.abs(pcm[i]) >= threshold) {
        // Step back 4ms (approx 176 samples at 44.1kHz) for natural transient attack
        startSample = Math.max(0, i - Math.round(sampleRate * 0.004));
        break;
      }
    }

    // A real book page flip is crisp and snappy: 0.5s - 0.7s duration
    const maxDurationSamples = Math.min(pcm.length - startSample, Math.round(sampleRate * 0.7));
    if (maxDurationSamples <= 0) return buffer;

    const trimmed = webAudioCtx.createBuffer(buffer.numberOfChannels, maxDurationSamples, sampleRate);
    for (let ch = 0; ch < buffer.numberOfChannels; ch++) {
      const src = buffer.getChannelData(ch);
      const dst = trimmed.getChannelData(ch);

      for (let j = 0; j < maxDurationSamples; j++) {
        dst[j] = src[startSample + j];
      }

      // Micro fade-in (2ms) to prevent any click/pop
      const fadeInSamples = Math.round(sampleRate * 0.002);
      for (let j = 0; j < fadeInSamples; j++) {
        dst[j] *= (j / fadeInSamples);
      }

      // Micro fade-out (35ms) for a silky smooth finish
      const fadeOutSamples = Math.round(sampleRate * 0.035);
      for (let j = 0; j < fadeOutSamples; j++) {
        const idx = maxDurationSamples - 1 - j;
        if (idx >= 0) {
          dst[idx] *= (j / fadeOutSamples);
        }
      }
    }

    return trimmed;
  }

  /**
   * Play page turn sound with 0ms latency
   */
  function playPaperTurnSound() {
    if (!soundEnabled) return;

    // Fast-path: Web Audio API (0ms instant hardware playback!)
    if (webAudioCtx && cachedAudioBuffer) {
      try {
        if (webAudioCtx.state === 'suspended') {
          webAudioCtx.resume().catch(() => {});
        }
        const source = webAudioCtx.createBufferSource();
        source.buffer = cachedAudioBuffer;
        
        // Add subtle natural pitch variation (0.98x to 1.03x) so repeated flips sound organic
        source.playbackRate.value = 0.98 + (Math.random() * 0.05);

        const gainNode = webAudioCtx.createGain();
        gainNode.gain.value = 0.95;

        source.connect(gainNode);
        gainNode.connect(webAudioCtx.destination);
        source.start(0);
        return;
      } catch (err) {
        // Fallback to HTML5 audio below
      }
    }

    // Fallback: HTML5 Audio Pool (skip leading silence with 0.12s start offset)
    if (htmlAudioFallbackPool.length > 0) {
      try {
        const audio = htmlAudioFallbackPool[htmlAudioPoolIdx];
        htmlAudioPoolIdx = (htmlAudioPoolIdx + 1) % HTML_AUDIO_POOL_SIZE;
        audio.currentTime = 0.12;
        const playPromise = audio.play();
        if (playPromise !== undefined) {
          playPromise.catch(() => {});
        }
      } catch (e) {
        // Audio playback blocked
      }
    }
  }

  // ── Drag & Touch State ──
  let isMouseDown = false;
  let isDragging = false;
  let startX = 0;
  let startY = 0;
  let dragStartTime = 0;
  let lastClickTime = 0;
  let dragDirection = null; // 'next' or 'prev'
  let dragSoundPlayed = false;

  function init() {
    const candidateRoot = document.getElementById('fb-root');
    if (!candidateRoot) return;

    rootEl = candidateRoot;
    if (rootEl === initializedRoot) {
      renderSpread(currentSpread);
      return;
    }

    spreadEl = document.getElementById('fb-spread');
    canvasLeft = document.getElementById('fb-canvas-left');
    canvasRight = document.getElementById('fb-canvas-right');
    flipContainer = document.getElementById('fb-flip-container');
    flipLeaf = document.getElementById('fb-flip-leaf');
    flipFront = document.getElementById('fb-flip-front');
    flipBack = document.getElementById('fb-flip-back');
    pageIndicator = document.getElementById('fb-page-indicator');
    loadingOverlay = document.getElementById('fb-loading-overlay');
    sliderEl = document.getElementById('fb-page-slider');
    zoomLevelEl = document.getElementById('fb-zoom-level');

    if (!canvasLeft || !canvasRight || !flipFront || !flipBack) return;

    ctxLeft = canvasLeft.getContext('2d');
    ctxRight = canvasRight.getContext('2d');
    ctxFlipFront = flipFront.getContext('2d');
    ctxFlipBack = flipBack.getContext('2d');

    const pdfUrl = rootEl.getAttribute('data-pdf-url');
    if (!pdfUrl) {
      console.warn('Flipbook: data-pdf-url maydoni topilmadi.');
      return;
    }

    // Initialize the real MP3 page-turn sound from public/sounds/
    const soundUrl = (rootEl && rootEl.getAttribute('data-sound-url')) || 
      (window.location.pathname.includes('/Kitob/public') 
        ? '/Kitob/public/sounds/oxidvideos-page-flip-1-178322.mp3' 
        : '/sounds/oxidvideos-page-flip-1-178322.mp3');
    initAudio(soundUrl);

    // Configure PDF.js worker
    if (window.pdfjsLib) {
      if (!window.pdfjsLib.GlobalWorkerOptions.workerSrc) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc =
          'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
      }
    } else {
      console.warn('Flipbook: PDF.js kutubxonasi hali yuklanmagan.');
      return;
    }

    initializedRoot = rootEl;

    // Window resize handler
    window.addEventListener('resize', debounce(() => {
      const wasMobile = isMobile;
      isMobile = window.innerWidth < 900;
      clearPageCache();
      if (wasMobile !== isMobile && totalPages > 0) {
        populatePageSelect(totalPages);
      }
      renderSpread(currentSpread);
    }, 180));

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!rootEl || !rootEl.isConnected) return;
      if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) return;
      if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
        fbGoNext();
      } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        fbGoPrev();
      } else if (e.key === '+' || e.key === '=') {
        fbZoomIn();
      } else if (e.key === '-') {
        fbZoomOut();
      } else if (e.key === '0') {
        fbZoomReset();
      }
    });

    // Double-click to toggle zoom between 100% and 135%
    if (spreadEl) {
      spreadEl.addEventListener('dblclick', (e) => {
        if (e.target.closest('button, input, select, a, .fb-nav-arrow')) return;
        if (zoomScale > 1.1) {
          fbZoomReset();
        } else {
          zoomScale = 1.35;
          updateZoomLabel();
          clearPageCache();
          renderSpread(currentSpread);
        }
      });
    }

    // Slider input change
    if (sliderEl) {
      sliderEl.addEventListener('input', (e) => {
        const val = parseInt(e.target.value, 10);
        if (!isNaN(val) && val >= 1 && val <= totalPages) {
          goToPage(val);
        }
      });
    }

    // Pre-warm Web Audio API on first user interaction for 0ms response
    ['pointerdown', 'mousedown', 'touchstart', 'keydown'].forEach((evt) => {
      window.addEventListener(evt, unlockAudioContext, { passive: true });
    });

    // Initialize Interactive Mouse Drag & Touch Gestures
    initDragAndTouchGestures();

    // Load PDF Document
    loadDocument(pdfUrl);
  }

  /**
   * ── Interactive Mouse Drag & Touch Swipe Gestures Engine ──
   */
  function initDragAndTouchGestures() {
    if (!rootEl) return;

    const targets = [rootEl, spreadEl].filter(Boolean);

    targets.forEach((target) => {
      // Prevent default browser drag-and-drop on book elements
      target.addEventListener('dragstart', (e) => {
        e.preventDefault();
        return false;
      });

      // Mouse drag start
      target.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return; // Only primary mouse button
        if (isFlipping || !pdfDoc) return;
        if (e.target.closest('button, input, select, a, .fb-nav-arrow')) return;

        e.preventDefault(); // Stop text selection & native HTML5 canvas drag
        handleDragStart(e.clientX, e.clientY, e.target);
      });

      // Touch drag start
      target.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        if (isFlipping || !pdfDoc) return;
        if (e.target.closest('button, input, select, a, .fb-nav-arrow')) return;

        const touch = e.touches[0];
        handleDragStart(touch.clientX, touch.clientY, e.target);
      }, { passive: true });
    });

    // Window-level tracking for smooth movement even outside the book boundary
    window.addEventListener('mousemove', (e) => {
      handleDragMove(e.clientX, e.clientY, e);
    }, { passive: false });

    window.addEventListener('mouseup', (e) => {
      handleDragEnd(e.clientX, e.clientY);
    });

    window.addEventListener('touchmove', (e) => {
      if (!isMouseDown || e.touches.length !== 1) return;
      const touch = e.touches[0];
      handleDragMove(touch.clientX, touch.clientY, e);
    }, { passive: false });

    window.addEventListener('touchend', (e) => {
      const touch = e.changedTouches ? e.changedTouches[0] : null;
      if (touch) {
        handleDragEnd(touch.clientX, touch.clientY);
      } else {
        cancelDragState();
      }
    });

    window.addEventListener('touchcancel', () => {
      cancelDragState();
    });
  }

  function handleDragStart(clientX, clientY, target) {
    if (isFlipping || !pdfDoc) return;
    if (target && target.closest && target.closest('button, input, select, a, .fb-nav-arrow')) return;

    unlockAudioContext();
    isMouseDown = true;
    isDragging = false;
    dragDirection = null;
    dragSoundPlayed = false;
    startX = clientX;
    startY = clientY;
    dragStartTime = performance.now();
  }

  function handleDragMove(clientX, clientY, e) {
    if (!isMouseDown || isFlipping || !pdfDoc) return;

    const deltaX = clientX - startX;
    const deltaY = clientY - startY;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);

    // If threshold crossed (>8px), activate dragging state
    if (!isDragging && absX > 8) {
      isDragging = true;
      if (spreadEl) spreadEl.classList.add('is-dragging');
      document.body.classList.add('fb-cursor-grabbing');
    }

    if (isDragging) {
      if (e && e.cancelable) e.preventDefault();

      const spreadWidth = spreadEl ? spreadEl.clientWidth : (window.innerWidth * 0.85);
      const pageWidth = isMobile ? spreadWidth : (spreadWidth / 2);

      // Dragging LEFT -> NEXT PAGE
      if (deltaX < 0) {
        const canNext = isMobile 
          ? (currentSpread < totalPages) 
          : ((currentSpread === 1 ? 2 : currentSpread + 2) <= totalPages);

        if (!canNext) {
          // Subtle elastic bounce resistance when at the last page
          const bounce = Math.min(10, absX * 0.08);
          if (flipLeaf) flipLeaf.style.transform = `rotateY(${-bounce}deg)`;
          return;
        }

        // Initialize flip leaf once upon entering 'next' direction
        if (dragDirection !== 'next') {
          dragDirection = 'next';
          if (!dragSoundPlayed) {
            playPaperTurnSound();
            dragSoundPlayed = true;
          }
          flipLeaf.className = isMobile 
            ? 'fb-flip-leaf fb-flip-leaf--right fb-flip-leaf--mobile' 
            : 'fb-flip-leaf fb-flip-leaf--right';
          flipLeaf.style.transition = 'none';

          // Fast 1ms offscreen canvas snapshot
          prepareFlipCanvas(canvasRight, flipFront);

          const nextNum = isMobile ? (currentSpread + 1) : (currentSpread === 1 ? 2 : currentSpread + 2);
          const cached = getCachedPageCanvas(nextNum, canvasRight.width, canvasRight.height);
          if (cached) {
            ctxFlipBack.drawImage(cached, 0, 0);
          } else {
            ctxFlipBack.fillStyle = '#f8f5ee';
            ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
            renderPageToCanvas(nextNum, flipBack, ctxFlipBack).catch(() => {});
          }

          flipContainer.classList.add('is-active');
        }

        const progress = Math.min(1, Math.max(0, absX / (pageWidth * 1.05)));
        const angle = - (progress * 165);
        const depth = Math.sin(progress * Math.PI);
        flipLeaf.style.transform = `rotateY(${angle}deg)`;
        flipLeaf.style.boxShadow = `${-22 * depth}px 14px 34px rgba(0,0,0,0.48)`;

      // Dragging RIGHT -> PREVIOUS PAGE
      } else if (deltaX > 0) {
        const canPrev = currentSpread > 1;

        if (!canPrev) {
          // Subtle elastic bounce resistance when at the first page
          const bounce = Math.min(10, absX * 0.08);
          if (flipLeaf) flipLeaf.style.transform = `rotateY(${bounce}deg)`;
          return;
        }

        // Initialize flip leaf once upon entering 'prev' direction
        if (dragDirection !== 'prev') {
          dragDirection = 'prev';
          if (!dragSoundPlayed) {
            playPaperTurnSound();
            dragSoundPlayed = true;
          }
          flipLeaf.className = isMobile 
            ? 'fb-flip-leaf fb-flip-leaf--left fb-flip-leaf--mobile' 
            : 'fb-flip-leaf fb-flip-leaf--left';
          flipLeaf.style.transition = 'none';

          prepareFlipCanvas(isMobile ? canvasRight : canvasLeft, flipFront);

          const prevNum = isMobile ? (currentSpread - 1) : (currentSpread <= 2 ? 1 : currentSpread - 1);
          const cached = getCachedPageCanvas(prevNum, (isMobile ? canvasRight : canvasLeft).width, (isMobile ? canvasRight : canvasLeft).height);
          if (cached) {
            ctxFlipBack.drawImage(cached, 0, 0);
          } else {
            ctxFlipBack.fillStyle = '#f8f5ee';
            ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
            renderPageToCanvas(prevNum, flipBack, ctxFlipBack).catch(() => {});
          }

          flipContainer.classList.add('is-active');
        }

        const progress = Math.min(1, Math.max(0, absX / (pageWidth * 1.05)));
        const angle = -180 + (progress * 165);
        const depth = Math.sin(progress * Math.PI);
        flipLeaf.style.transform = `rotateY(${angle}deg)`;
        flipLeaf.style.boxShadow = `${22 * depth}px 14px 34px rgba(0,0,0,0.48)`;
      }
    }
  }

  function handleDragEnd(clientX, clientY) {
    if (!isMouseDown) return;
    isMouseDown = false;
    document.body.classList.remove('fb-cursor-grabbing');
    if (spreadEl) spreadEl.classList.remove('is-dragging');

    const deltaX = clientX - startX;
    const deltaY = clientY - startY;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);
    const elapsed = performance.now() - dragStartTime;
    const velocity = absX / Math.max(1, elapsed);

    // If an active 3D drag occurred with page peeling
    if (isDragging && dragDirection) {
      isDragging = false;
      const shouldTurn = absX > 30 || (velocity > 0.22 && absX > 15);

      if (dragDirection === 'next') {
        if (shouldTurn) {
          // Smooth spring physics commit to -180deg
          isFlipping = true;
          if (!dragSoundPlayed) {
            playPaperTurnSound();
          }
          flipLeaf.style.transition = 'transform 260ms cubic-bezier(0.25, 1, 0.5, 1), box-shadow 260ms ease';
          flipLeaf.style.transform = 'rotateY(-180deg)';
          flipLeaf.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';

          const nextSpread = isMobile ? (currentSpread + 1) : (currentSpread === 1 ? 2 : currentSpread + 2);
          renderSpread(nextSpread).catch(() => {});

          setTimeout(() => {
            dragDirection = null;
            dragSoundPlayed = false;
            finishFlip();
          }, 260);
          return;
        } else {
          // Snap back to 0deg
          flipLeaf.style.transition = 'transform 200ms ease-out, box-shadow 200ms ease-out';
          flipLeaf.style.transform = 'rotateY(0deg)';
          flipLeaf.style.boxShadow = '0 4px 10px rgba(0,0,0,0.1)';
          setTimeout(() => {
            dragDirection = null;
            dragSoundPlayed = false;
            finishFlip();
          }, 200);
          return;
        }
      } else if (dragDirection === 'prev') {
        if (shouldTurn) {
          // Smooth spring physics commit to 0deg
          isFlipping = true;
          if (!dragSoundPlayed) {
            playPaperTurnSound();
          }
          flipLeaf.style.transition = 'transform 260ms cubic-bezier(0.25, 1, 0.5, 1), box-shadow 260ms ease';
          flipLeaf.style.transform = 'rotateY(0deg)';
          flipLeaf.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';

          const prevSpread = isMobile ? (currentSpread - 1) : (currentSpread <= 2 ? 1 : currentSpread - 2);
          renderSpread(prevSpread).catch(() => {});

          setTimeout(() => {
            dragDirection = null;
            dragSoundPlayed = false;
            finishFlip();
          }, 260);
          return;
        } else {
          // Snap back to -180deg
          flipLeaf.style.transition = 'transform 200ms ease-out, box-shadow 200ms ease-out';
          flipLeaf.style.transform = 'rotateY(-180deg)';
          flipLeaf.style.boxShadow = '0 4px 10px rgba(0,0,0,0.1)';
          setTimeout(() => {
            dragDirection = null;
            dragSoundPlayed = false;
            finishFlip();
          }, 200);
          return;
        }
      }
    }

    cancelDragState();

    // ── Single Click / Tap to Turn ──
    if (absX < 12 && absY < 12 && elapsed < 350) {
      const now = performance.now();
      if (now - lastClickTime < 280) {
        lastClickTime = now;
        return; // Allow double-click zoom
      }
      lastClickTime = now;

      const rect = (spreadEl || rootEl).getBoundingClientRect();
      const clickX = clientX - rect.left;
      const totalW = rect.width;

      if (isMobile) {
        if (clickX > totalW * 0.45) {
          fbGoNext();
        } else {
          fbGoPrev();
        }
      } else {
        if (clickX >= totalW / 2) {
          fbGoNext();
        } else {
          fbGoPrev();
        }
      }
    }
  }

  function cancelDragState() {
    isMouseDown = false;
    isDragging = false;
    dragDirection = null;
    dragSoundPlayed = false;
    document.body.classList.remove('fb-cursor-grabbing');
    if (spreadEl) spreadEl.classList.remove('is-dragging');
    if (flipLeaf) {
      flipLeaf.style.transition = '';
      flipLeaf.style.transform = '';
      flipLeaf.style.boxShadow = '';
    }
    if (flipContainer && !isFlipping) {
      flipContainer.classList.remove('is-active');
    }
  }

  function loadDocument(url) {
    if (loadingOverlay) loadingOverlay.classList.remove('is-hidden');

    const loadingTask = window.pdfjsLib.getDocument({
      url: url,
      withCredentials: true,
      cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
      cMapPacked: true,
    });

    loadingTask.promise
      .then((doc) => {
        pdfDoc = doc;
        totalPages = doc.numPages;
        currentSpread = 1;
        populatePageSelect(totalPages);
        if (sliderEl) {
          sliderEl.min = 1;
          sliderEl.max = totalPages;
          sliderEl.value = 1;
        }
        renderSpread(currentSpread).then(() => {
          if (loadingOverlay) loadingOverlay.classList.add('is-hidden');
        });
      })
      .catch((err) => {
        console.error('Flipbook PDF yuklashda xatolik:', err);
        if (loadingOverlay) {
          loadingOverlay.innerHTML = `
            <div class="text-center p-6 text-rose-400">
              <span class="text-4xl block mb-2">⚠️</span>
              <p class="font-bold text-sm">PDF faylni yuklashda xatolik yuz berdi.</p>
              <p class="text-xs text-slate-400 mt-1">${err.message || ''}</p>
            </div>
          `;
        }
      });
  }

  /**
   * Populate dropdown page selector <select id="fb-page-select">
   */
  function populatePageSelect(total) {
    const selects = document.querySelectorAll('#fb-page-select');
    selects.forEach(selectEl => {
      selectEl.innerHTML = '';

      const defaultOpt = document.createElement('option');
      defaultOpt.value = '';
      defaultOpt.textContent = 'Sahifaga o\'tish ▾';
      defaultOpt.className = 'bg-slate-900 text-slate-400';
      selectEl.appendChild(defaultOpt);

      if (isMobile) {
        for (let i = 1; i <= total; i++) {
          const opt = document.createElement('option');
          opt.value = i;
          opt.textContent = `${i}-sahifa`;
          opt.className = 'bg-slate-900 text-white';
          selectEl.appendChild(opt);
        }
      } else {
        const opt1 = document.createElement('option');
        opt1.value = 1;
        opt1.textContent = '1-sahifa (Muqova)';
        opt1.className = 'bg-slate-900 text-white';
        selectEl.appendChild(opt1);

        for (let i = 2; i <= total; i += 2) {
          const opt = document.createElement('option');
          opt.value = i;
          const endPage = Math.min(i + 1, total);
          opt.textContent = `${i}-${endPage} sahifalar`;
          opt.className = 'bg-slate-900 text-white';
          selectEl.appendChild(opt);
        }
      }
    });
  }

  /**
   * Calculate dynamically optimal book dimensions so the book is large, close,
   * and fills the available screen space without looking small or far away.
   */
  function calculateOptimalDimensions(baseViewport) {
    const isStandalone = !!document.getElementById('fb-standalone-container');
    const containerWidth = (rootEl && rootEl.clientWidth) ? rootEl.clientWidth : window.innerWidth;
    const containerHeight = window.innerHeight;

    // Available width and height
    const paddingH = isMobile ? 16 : (isStandalone ? 40 : 60);
    const paddingV = isMobile ? 160 : (isStandalone ? 120 : 140);

    const availW = Math.max(300, containerWidth - paddingH);
    const availH = Math.max(480, containerHeight - paddingV);

    const pageAspect = baseViewport.width / baseViewport.height; // typically ~0.707
    const spreadAspect = isMobile ? pageAspect : (pageAspect * 2); // typically ~1.414

    // Maximize book size within available container dimensions
    let targetW = availW;
    let targetH = targetW / spreadAspect;

    if (targetH > availH) {
      targetH = availH;
      targetW = targetH * spreadAspect;
    }

    // Apply user zoom multiplier
    targetH = targetH * zoomScale;
    targetW = targetW * zoomScale;

    const singlePageH = targetH;
    const singlePageW = isMobile ? targetW : (targetW / 2);

    return { singlePageW, singlePageH };
  }

  /**
   * Render single page onto a specific canvas with caching
   */
  async function renderPageToCanvas(pageNum, canvas, ctx, taskSlot = null, requestId = null) {
    if (!cachedBaseViewport && pdfDoc) {
      const p1 = await pdfDoc.getPage(1);
      cachedBaseViewport = p1.getViewport({ scale: 1.0 });
    }

    if (requestId !== null && requestId !== renderRequestId) return;

    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const dpr = window.devicePixelRatio || 1;

    canvas.width = Math.round(singlePageW * dpr);
    canvas.height = Math.round(singlePageH * dpr);
    canvas.style.width = `${Math.round(singlePageW)}px`;
    canvas.style.height = `${Math.round(singlePageH)}px`;

    if (pageNum < 1 || pageNum > totalPages) {
      // Faux leather blank cover/backside
      ctx.fillStyle = '#121824';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = 'rgba(212, 175, 55, 0.3)';
      ctx.font = `${Math.round(18 * dpr)}px serif`;
      ctx.textAlign = 'center';
      ctx.fillText('— Kitobxon —', canvas.width / 2, canvas.height / 2);
      return;
    }

    // Check high-speed offscreen cache
    const cached = getCachedPageCanvas(pageNum, canvas.width, canvas.height);
    if (cached) {
      ctx.drawImage(cached, 0, 0);
      return;
    }

    let page = null;
    let viewport = null;
    if (pageNum >= 1 && pageNum <= totalPages) {
      page = await pdfDoc.getPage(pageNum);
      if (requestId !== null && requestId !== renderRequestId) return;
      const pageVp = page.getViewport({ scale: 1.0 });
      const scale = (singlePageH / pageVp.height) * dpr;
      viewport = page.getViewport({ scale });
    }

    const task = page.render({ canvasContext: ctx, viewport });
    if (taskSlot) renderTasks[taskSlot] = task;

    try {
      await task.promise;
      storeCachedPageCanvas(pageNum, canvas.width, canvas.height, canvas);
    } finally {
      if (taskSlot && renderTasks[taskSlot] === task) {
        renderTasks[taskSlot] = null;
      }
    }
  }

  /**
   * Pre-cache adjacent pages during idle time so dragging is instantaneous
   */
  function preCacheAdjacentPages(spreadNum) {
    if (!pdfDoc) return;
    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const dpr = window.devicePixelRatio || 1;
    const targetW = Math.round(singlePageW * dpr);
    const targetH = Math.round(singlePageH * dpr);

    const pagesToCache = isMobile
      ? [spreadNum + 1, spreadNum - 1]
      : [spreadNum + 2, spreadNum + 3, spreadNum - 1, spreadNum - 2];

    pagesToCache.forEach(p => {
      if (p >= 1 && p <= totalPages && !getCachedPageCanvas(p, targetW, targetH)) {
        pdfDoc.getPage(p).then(page => {
          const off = document.createElement('canvas');
          off.width = targetW;
          off.height = targetH;
          const oCtx = off.getContext('2d');
          const pageVp = page.getViewport({ scale: 1.0 });
          const scale = (singlePageH / pageVp.height) * dpr;
          const viewport = page.getViewport({ scale });
          page.render({ canvasContext: oCtx, viewport }).promise.then(() => {
            storeCachedPageCanvas(p, targetW, targetH, off);
          }).catch(() => {});
        }).catch(() => {});
      }
    });
  }

  /**
   * Render the current 2-page spread (or 1 page in mobile)
   */
  async function renderSpread(spreadStart) {
    if (!pdfDoc) return;

    const requestId = ++renderRequestId;
    const activeTasks = Object.values(renderTasks).filter(Boolean);
    activeTasks.forEach(task => task.cancel());
    await Promise.allSettled(activeTasks.map(task => task.promise));
    if (requestId !== renderRequestId || !pdfDoc) return;

    if (isMobile) {
      const pageNum = Math.max(1, Math.min(spreadStart, totalPages));
      try {
        await renderPageToCanvas(pageNum, canvasRight, ctxRight, 'right', requestId);
      } catch (error) {
        if (requestId !== renderRequestId || error.name === 'RenderingCancelledException') return;
        throw error;
      }
      if (requestId !== renderRequestId) return;
      currentSpread = pageNum;
      updateIndicator(`${currentSpread} / ${totalPages}`, currentSpread);
      if (sliderEl) sliderEl.value = currentSpread;
      preCacheAdjacentPages(currentSpread);
      return;
    }

    // Dual page mode:
    let leftNum, rightNum;
    if (spreadStart === 1) {
      leftNum = 0; // Cover page alone on right
      rightNum = 1;
    } else {
      leftNum = spreadStart % 2 === 0 ? spreadStart : spreadStart - 1;
      rightNum = leftNum + 1;
    }

    currentSpread = leftNum > 0 ? leftNum : 1;

    try {
      await Promise.all([
        renderPageToCanvas(leftNum, canvasLeft, ctxLeft, 'left', requestId),
        renderPageToCanvas(rightNum, canvasRight, ctxRight, 'right', requestId),
      ]);
    } catch (error) {
      if (requestId !== renderRequestId || error.name === 'RenderingCancelledException') return;
      throw error;
    }
    if (requestId !== renderRequestId) return;

    if (currentSpread === 1) {
      updateIndicator(`1 (Muqova) / ${totalPages}`, 1);
      if (sliderEl) sliderEl.value = 1;
    } else {
      const rightStr = rightNum <= totalPages ? `-${rightNum}` : '';
      updateIndicator(`${leftNum}${rightStr} / ${totalPages}`, leftNum);
      if (sliderEl) sliderEl.value = leftNum;
    }

    preCacheAdjacentPages(currentSpread);
  }

  /**
   * Update Indicator and Sync all Inputs & Select dropdowns
   */
  function updateIndicator(text, pageNum) {
    if (pageIndicator) {
      pageIndicator.textContent = `Sahifa: ${text}`;
    }

    const currentVal = pageNum || currentSpread;

    const pageInputs = document.querySelectorAll('#fb-page-input');
    pageInputs.forEach(input => {
      input.value = currentVal;
      input.max = totalPages;
    });

    const totalEls = document.querySelectorAll('#fb-total-pages');
    totalEls.forEach(el => {
      el.textContent = totalPages;
    });

    const selects = document.querySelectorAll('#fb-page-select');
    selects.forEach(selectEl => {
      let targetVal = currentVal;
      if (!isMobile && targetVal > 1 && targetVal % 2 !== 0) {
        targetVal = targetVal - 1;
      }
      selectEl.value = targetVal;
    });
  }

  function updateZoomLabel() {
    if (zoomLevelEl) {
      zoomLevelEl.textContent = `${Math.round(zoomScale * 100)}%`;
    }
  }

  function getFlipDuration() {
    return prefersReducedMotion() ? 1 : 480;
  }

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function finishFlip() {
    if (flipContainer) flipContainer.classList.remove('is-active');
    if (flipLeaf) {
      flipLeaf.className = 'fb-flip-leaf';
      flipLeaf.style.transition = '';
      flipLeaf.style.transform = '';
      flipLeaf.style.boxShadow = '';
    }
    isFlipping = false;
  }

  /**
   * Jump to page from input
   */
  function fbJumpToPageInput() {
    const input = document.getElementById('fb-page-input');
    if (!input || !pdfDoc) return;
    let val = parseInt(input.value, 10);
    if (isNaN(val)) return;
    val = Math.max(1, Math.min(val, totalPages));
    goToPage(val);
  }

  /**
   * Flip to next page with 3D animation (Button / Arrow / Click / Drag trigger)
   */
  async function fbGoNext() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread >= totalPages) return;
      if (prefersReducedMotion()) {
        renderSpread(currentSpread + 1);
        return;
      }
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipFront);
      const nextNum = currentSpread + 1;
      const cached = getCachedPageCanvas(nextNum, canvasRight.width, canvasRight.height);
      if (cached) {
        ctxFlipBack.drawImage(cached, 0, 0);
      } else {
        ctxFlipBack.fillStyle = '#f8f5ee';
        ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
        renderPageToCanvas(nextNum, flipBack, ctxFlipBack).catch(() => {});
      }

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flip-leaf--mobile fb-flipping-next';
      flipContainer.classList.add('is-active');

      const flipDuration = getFlipDuration();
      renderSpread(nextNum).catch(() => {});

      setTimeout(() => {
        finishFlip();
      }, flipDuration);
      return;
    }

    // Dual mode next
    const nextSpread = currentSpread === 1 ? 2 : currentSpread + 2;
    if (nextSpread > totalPages) return;
    if (prefersReducedMotion()) {
      renderSpread(nextSpread);
      return;
    }

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasRight, flipFront);
    const turnedBackPage = currentSpread === 1 ? 2 : currentSpread + 2;
    const cached = getCachedPageCanvas(turnedBackPage, canvasRight.width, canvasRight.height);
    if (cached) {
      ctxFlipBack.drawImage(cached, 0, 0);
    } else {
      ctxFlipBack.fillStyle = '#f8f5ee';
      ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
      renderPageToCanvas(turnedBackPage, flipBack, ctxFlipBack).catch(() => {});
    }

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flipping-next';
    flipContainer.classList.add('is-active');

    const flipDuration = getFlipDuration();
    renderSpread(nextSpread).catch(() => {});

    setTimeout(() => {
      finishFlip();
    }, flipDuration);
  }

  /**
   * Flip to previous page with 3D animation (Button / Arrow / Click / Drag trigger)
   */
  async function fbGoPrev() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread <= 1) return;
      if (prefersReducedMotion()) {
        renderSpread(currentSpread - 1);
        return;
      }
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipFront);
      const prevNum = currentSpread - 1;
      const cached = getCachedPageCanvas(prevNum, canvasRight.width, canvasRight.height);
      if (cached) {
        ctxFlipBack.drawImage(cached, 0, 0);
      } else {
        ctxFlipBack.fillStyle = '#f8f5ee';
        ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
        renderPageToCanvas(prevNum, flipBack, ctxFlipBack).catch(() => {});
      }

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flip-leaf--mobile fb-flipping-prev';
      flipContainer.classList.add('is-active');

      const flipDuration = getFlipDuration();
      renderSpread(prevNum).catch(() => {});

      setTimeout(() => {
        finishFlip();
      }, flipDuration);
      return;
    }

    // Dual mode prev
    if (currentSpread <= 1) return;
    const prevSpread = currentSpread <= 2 ? 1 : currentSpread - 2;
    if (prefersReducedMotion()) {
      renderSpread(prevSpread);
      return;
    }

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasLeft, flipFront);
    const turnedBackPage = currentSpread <= 2 ? 1 : currentSpread - 1;
    const cached = getCachedPageCanvas(turnedBackPage, canvasLeft.width, canvasLeft.height);
    if (cached) {
      ctxFlipBack.drawImage(cached, 0, 0);
    } else {
      ctxFlipBack.fillStyle = '#f8f5ee';
      ctxFlipBack.fillRect(0, 0, flipBack.width, flipBack.height);
      renderPageToCanvas(turnedBackPage, flipBack, ctxFlipBack).catch(() => {});
    }

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flipping-prev';
    flipContainer.classList.add('is-active');

    const flipDuration = getFlipDuration();
    renderSpread(prevSpread).catch(() => {});

    setTimeout(() => {
      finishFlip();
    }, flipDuration);
  }

  function goToPage(targetPage) {
    if (!pdfDoc || isFlipping) return;
    const target = Math.max(1, Math.min(targetPage, totalPages));
    playPaperTurnSound();
    renderSpread(target);
  }

  function prepareFlipCanvas(sourceCanvas, targetCanvas) {
    targetCanvas.width = sourceCanvas.width;
    targetCanvas.height = sourceCanvas.height;
    targetCanvas.style.width = sourceCanvas.style.width;
    targetCanvas.style.height = sourceCanvas.style.height;
    const ctx = targetCanvas.getContext('2d');
    ctx.drawImage(sourceCanvas, 0, 0);
  }

  function fbZoomIn() {
    if (zoomScale < 2.2) {
      zoomScale = Math.min(2.2, +(zoomScale + 0.15).toFixed(2));
      updateZoomLabel();
      clearPageCache();
      renderSpread(currentSpread);
    }
  }

  function fbZoomOut() {
    if (zoomScale > 0.7) {
      zoomScale = Math.max(0.7, +(zoomScale - 0.15).toFixed(2));
      updateZoomLabel();
      clearPageCache();
      renderSpread(currentSpread);
    }
  }

  function fbZoomReset() {
    zoomScale = 1.0;
    updateZoomLabel();
    clearPageCache();
    renderSpread(currentSpread);
  }

  function fbToggleFullscreen() {
    const target = rootEl.closest('.fb-stage-wrapper') || rootEl;
    if (!document.fullscreenElement) {
      if (target.requestFullscreen) {
        target.requestFullscreen();
      } else if (target.webkitRequestFullscreen) {
        target.webkitRequestFullscreen();
      }
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      }
    }
  }

  function fbToggleSound() {
    soundEnabled = !soundEnabled;
    const soundBtns = document.querySelectorAll('#fb-sound-btn');
    soundBtns.forEach((btn) => {
      btn.textContent = soundEnabled ? '🔊 Ovoz yoqilgan' : '🔇 Ovoz o\'chirilgan';
    });
  }

  function debounce(fn, ms) {
    let t;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), ms);
    };
  }

  // Expose global API
  window.fbGoNext = fbGoNext;
  window.fbGoPrev = fbGoPrev;
  window.fbGoToPage = goToPage;
  window.fbJumpToPageInput = fbJumpToPageInput;
  window.fbZoomIn = fbZoomIn;
  window.fbZoomOut = fbZoomOut;
  window.fbZoomReset = fbZoomReset;
  window.fbToggleFullscreen = fbToggleFullscreen;
  window.fbToggleSound = fbToggleSound;
  window.initFlipbook = init;

  // Auto-init
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 50);
  }
})();
