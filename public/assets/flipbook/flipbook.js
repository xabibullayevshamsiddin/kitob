/**
 * Kitobxon Ultra-Realistic 3D Flipbook Engine
 * Features:
 * - Interactive Mouse Drag & Touch Gesture Engine (real-time 3D page curl following cursor/finger)
 * - Single-click / tap on page to turn (right side -> next, left side -> prev)
 * - Spring-loaded physics snap completion and cancel
 * - In-memory High-DPI Page Canvas Cache for 60fps instant transitions
 * - Dynamic viewport calculation (fits comfortably large and close to reader)
 * - Sharp high-DPI PDF.js canvas rendering
 * - High-quality authentic page-turn sound using oxidvideos-page-flip-1-178322.mp3
 * - Audio pool for rapid responsive page flipping
 * - Interactive Page Selector & Direct Jumper (Input + Dropdown)
 * - Zoom in/out/reset (+/-) with double-click zoom toggle
 * - Page scrubber slider & jump to page
 * - Touch swipe & keyboard arrow navigation
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

  // Real Audio Pool using /sounds/oxidvideos-page-flip-1-178322.mp3
  let soundAudioPool = [];
  const AUDIO_POOL_SIZE = 4;
  let audioPoolIdx = 0;

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

  function initAudio(soundUrl) {
    soundAudioPool = [];
    for (let i = 0; i < AUDIO_POOL_SIZE; i++) {
      const a = new Audio(soundUrl);
      a.preload = 'auto';
      soundAudioPool.push(a);
    }
  }

  function playPaperTurnSound() {
    if (!soundEnabled || soundAudioPool.length === 0) return;
    try {
      const audio = soundAudioPool[audioPoolIdx];
      audioPoolIdx = (audioPoolIdx + 1) % AUDIO_POOL_SIZE;
      audio.currentTime = 0;
      const playPromise = audio.play();
      if (playPromise !== undefined) {
        playPromise.catch(() => {});
      }
    } catch (e) {
      // Audio playback blocked or unavailable
    }
  }

  // ── Drag & Touch Gesture State ──
  let isPointerDown = false;
  let activePointerId = null;
  let dragStartX = 0;
  let dragStartY = 0;
  let dragStartTime = 0;
  let isDragging = false;
  let activeDragDirection = null; // 'next' | 'prev' | null
  let currentDragProgress = 0;
  let dragPageWidth = 0;
  let dragLeafInitialized = false;
  let lastClickTime = 0;

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

    // Initialize Interactive 3D Drag & Touch Gesture System
    initDragAndTouchGestures();

    // Load PDF Document
    loadDocument(pdfUrl);
  }

  /**
   * ── Interactive 3D Drag & Touch Gestures Engine ──
   * Allows reader to turn pages using mouse drag or finger swipe with realistic physical 3D leaf curl
   */
  function initDragAndTouchGestures() {
    if (!spreadEl) return;

    // Unified Pointer Events (Mouse, Touch, Pen)
    spreadEl.addEventListener('pointerdown', onPointerDown);
    window.addEventListener('pointermove', onPointerMove, { passive: false });
    window.addEventListener('pointerup', onPointerUp);
    window.addEventListener('pointercancel', onPointerCancel);
  }

  function onPointerDown(e) {
    if (isFlipping || !pdfDoc) return;
    if (e.button && e.button !== 0) return; // Only primary mouse button or touch
    if (e.target.closest('button, input, select, a, .fb-nav-arrow, [role="button"]')) return;

    isPointerDown = true;
    activePointerId = e.pointerId;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    dragStartTime = performance.now();
    isDragging = false;
    activeDragDirection = null;
    currentDragProgress = 0;
    dragLeafInitialized = false;
    dragPageWidth = isMobile ? spreadEl.clientWidth : (spreadEl.clientWidth / 2);

    try {
      if (spreadEl.setPointerCapture) spreadEl.setPointerCapture(e.pointerId);
    } catch (err) {}
  }

  function onPointerMove(e) {
    if (!isPointerDown || (activePointerId !== null && e.pointerId !== activePointerId)) return;
    if (isFlipping) return;

    const deltaX = e.clientX - dragStartX;
    const deltaY = e.clientY - dragStartY;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);

    if (!isDragging) {
      // If user is scrolling vertically on mobile page, do not hijack
      if (absY > absX * 1.5 && absY > 12) {
        isPointerDown = false;
        return;
      }
      // Horizontal threshold to initiate drag
      if (absX > 8) {
        isDragging = true;
        spreadEl.classList.add('is-dragging');
      }
    }

    if (isDragging) {
      if (e.cancelable) e.preventDefault();

      if (deltaX < 0) {
        // Dragging left -> Next Page
        const canNext = isMobile ? (currentSpread < totalPages) : ((currentSpread === 1 ? 2 : currentSpread + 2) <= totalPages);
        if (!canNext) {
          // Boundary rubber-band feedback
          const rubber = Math.min(18, -deltaX * 0.12);
          spreadEl.style.transform = `translateX(${-rubber}px)`;
          return;
        }

        if (activeDragDirection !== 'next') {
          activeDragDirection = 'next';
          setupDragLeaf('next');
        }

        const pw = Math.max(100, dragPageWidth);
        currentDragProgress = Math.min(1, Math.max(0, -deltaX / pw));
        const angle = -currentDragProgress * 180;
        const depth = Math.sin(currentDragProgress * Math.PI);

        flipLeaf.style.transition = 'none';
        flipLeaf.style.transform = `rotateY(${angle}deg)`;
        flipLeaf.style.boxShadow = `${-18 * depth}px 15px ${25 + 35 * depth}px rgba(0, 0, 0, ${0.2 + 0.35 * depth})`;

      } else if (deltaX > 0) {
        // Dragging right -> Prev Page
        const canPrev = currentSpread > 1;
        if (!canPrev) {
          // Boundary rubber-band feedback
          const rubber = Math.min(18, deltaX * 0.12);
          spreadEl.style.transform = `translateX(${rubber}px)`;
          return;
        }

        if (activeDragDirection !== 'prev') {
          activeDragDirection = 'prev';
          setupDragLeaf('prev');
        }

        const pw = Math.max(100, dragPageWidth);
        currentDragProgress = Math.min(1, Math.max(0, deltaX / pw));
        const angle = -180 + (currentDragProgress * 180);
        const depth = Math.sin(currentDragProgress * Math.PI);

        flipLeaf.style.transition = 'none';
        flipLeaf.style.transform = `rotateY(${angle}deg)`;
        flipLeaf.style.boxShadow = `${18 * depth}px 15px ${25 + 35 * depth}px rgba(0, 0, 0, ${0.2 + 0.35 * depth})`;
      }
    }
  }

  async function setupDragLeaf(dir) {
    if (dragLeafInitialized) return;
    dragLeafInitialized = true;

    if (dir === 'next') {
      if (isMobile) {
        prepareFlipCanvas(canvasRight, flipFront);
        flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right';
        flipContainer.classList.add('is-active');
        const nextNum = currentSpread + 1;
        renderPageToCanvas(nextNum, flipBack, ctxFlipBack);
      } else {
        prepareFlipCanvas(canvasRight, flipFront);
        flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right';
        flipContainer.classList.add('is-active');
        const turnedBack = currentSpread === 1 ? 2 : currentSpread + 2;
        renderPageToCanvas(turnedBack, flipBack, ctxFlipBack);
        // Pre-render underlying right page on canvasRight
        const underRight = currentSpread === 1 ? 3 : currentSpread + 3;
        if (underRight <= totalPages) {
          renderPageToCanvas(underRight, canvasRight, ctxRight);
        } else {
          renderPageToCanvas(0, canvasRight, ctxRight);
        }
      }
    } else if (dir === 'prev') {
      if (isMobile) {
        prepareFlipCanvas(canvasRight, flipBack);
        flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left';
        flipContainer.classList.add('is-active');
        const prevNum = currentSpread - 1;
        renderPageToCanvas(prevNum, flipFront, ctxFlipFront);
      } else {
        prepareFlipCanvas(canvasLeft, flipFront);
        flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left';
        flipContainer.classList.add('is-active');
        const turnedBack = currentSpread <= 2 ? 1 : currentSpread - 1;
        renderPageToCanvas(turnedBack, flipBack, ctxFlipBack);
        // Pre-render underlying left page on canvasLeft
        const underLeft = currentSpread <= 2 ? 0 : currentSpread - 2;
        renderPageToCanvas(underLeft, canvasLeft, ctxLeft);
      }
    }
  }

  async function onPointerUp(e) {
    if (!isPointerDown) return;
    isPointerDown = false;
    spreadEl.classList.remove('is-dragging');
    spreadEl.style.transform = '';

    try {
      if (activePointerId !== null && spreadEl.releasePointerCapture) {
        spreadEl.releasePointerCapture(activePointerId);
      }
    } catch (err) {}
    activePointerId = null;

    const deltaX = e.clientX - dragStartX;
    const deltaY = e.clientY - dragStartY;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);
    const elapsed = performance.now() - dragStartTime;
    const velocity = absX / Math.max(1, elapsed);

    if (isDragging) {
      isDragging = false;
      const shouldCommit = (currentDragProgress >= 0.22) || (velocity > 0.32 && absX > 25);

      if (shouldCommit && activeDragDirection) {
        // Complete the 3D flip smoothly
        isFlipping = true;
        playPaperTurnSound();
        const duration = Math.max(180, Math.min(380, Math.round((1 - currentDragProgress) * 360)));
        flipLeaf.style.transition = `transform ${duration}ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow ${duration}ms ease`;

        if (activeDragDirection === 'next') {
          flipLeaf.style.transform = 'rotateY(-180deg)';
          flipLeaf.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
        } else {
          flipLeaf.style.transform = 'rotateY(0deg)';
          flipLeaf.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
        }

        setTimeout(async () => {
          if (activeDragDirection === 'next') {
            if (isMobile) {
              currentSpread = Math.min(totalPages, currentSpread + 1);
              updateIndicator(`${currentSpread} / ${totalPages}`, currentSpread);
              if (sliderEl) sliderEl.value = currentSpread;
            } else {
              const nextSpread = currentSpread === 1 ? 2 : currentSpread + 2;
              await renderSpread(nextSpread);
            }
          } else {
            if (isMobile) {
              currentSpread = Math.max(1, currentSpread - 1);
              updateIndicator(`${currentSpread} / ${totalPages}`, currentSpread);
              if (sliderEl) sliderEl.value = currentSpread;
            } else {
              const prevSpread = currentSpread <= 2 ? 1 : currentSpread - 2;
              await renderSpread(prevSpread);
            }
          }
          finishFlip();
        }, duration);

      } else if (activeDragDirection) {
        // Cancel and snap back to origin
        isFlipping = true;
        const duration = Math.max(160, Math.min(320, Math.round(currentDragProgress * 320)));
        flipLeaf.style.transition = `transform ${duration}ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow ${duration}ms ease`;

        if (activeDragDirection === 'next') {
          flipLeaf.style.transform = 'rotateY(0deg)';
        } else {
          flipLeaf.style.transform = 'rotateY(-180deg)';
        }

        setTimeout(async () => {
          await renderSpread(currentSpread);
          finishFlip();
        }, duration);

      } else {
        finishFlip();
      }

    } else {
      // ── Single Click / Tap to Turn ──
      if (absX < 14 && absY < 14 && elapsed < 400) {
        const now = performance.now();
        if (now - lastClickTime < 280) {
          // Double-click detected (allow zoom handler)
          lastClickTime = now;
          return;
        }
        lastClickTime = now;

        const rect = spreadEl.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const spreadW = rect.width;

        if (isMobile) {
          if (clickX > spreadW * 0.45) {
            fbGoNext();
          } else {
            fbGoPrev();
          }
        } else {
          if (clickX >= spreadW / 2) {
            fbGoNext();
          } else {
            fbGoPrev();
          }
        }
      }
    }
  }

  function onPointerCancel() {
    isPointerDown = false;
    isDragging = false;
    spreadEl.classList.remove('is-dragging');
    spreadEl.style.transform = '';
    finishFlip();
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
    dragLeafInitialized = false;
    activeDragDirection = null;
    currentDragProgress = 0;
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
   * Flip to next page with 3D animation (Button / Arrow / Click trigger)
   */
  async function fbGoNext() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread >= totalPages) return;
      if (prefersReducedMotion()) {
        await renderSpread(currentSpread + 1);
        return;
      }
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipFront);
      const nextNum = currentSpread + 1;
      try {
        await renderPageToCanvas(nextNum, flipBack, ctxFlipBack);
      } catch (error) {
        finishFlip();
        throw error;
      }

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flipping-next';
      flipContainer.classList.add('is-active');

      try {
        await renderPageToCanvas(nextNum, canvasRight, ctxRight);
      } catch (error) {
        finishFlip();
        throw error;
      }

      setTimeout(() => {
        currentSpread = nextNum;
        updateIndicator(`${currentSpread} / ${totalPages}`, currentSpread);
        if (sliderEl) sliderEl.value = currentSpread;
        finishFlip();
      }, getFlipDuration());
      return;
    }

    // Dual mode next
    const nextSpread = currentSpread === 1 ? 2 : currentSpread + 2;
    if (nextSpread > totalPages) return;
    if (prefersReducedMotion()) {
      await renderSpread(nextSpread);
      return;
    }

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasRight, flipFront);
    const turnedBackPage = currentSpread === 1 ? 2 : currentSpread + 2;
    try {
      await renderPageToCanvas(turnedBackPage, flipBack, ctxFlipBack);
    } catch (error) {
      finishFlip();
      throw error;
    }

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flipping-next';
    flipContainer.classList.add('is-active');

    try {
      await renderSpread(nextSpread);
      setTimeout(finishFlip, getFlipDuration());
    } catch (error) {
      finishFlip();
      throw error;
    }
  }

  /**
   * Flip to previous page with 3D animation (Button / Arrow / Click trigger)
   */
  async function fbGoPrev() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread <= 1) return;
      if (prefersReducedMotion()) {
        await renderSpread(currentSpread - 1);
        return;
      }
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipBack);
      const prevNum = currentSpread - 1;
      try {
        await renderPageToCanvas(prevNum, flipFront, ctxFlipFront);
      } catch (error) {
        finishFlip();
        throw error;
      }

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flipping-prev';
      flipContainer.classList.add('is-active');

      try {
        await renderPageToCanvas(prevNum, canvasRight, ctxRight);
      } catch (error) {
        finishFlip();
        throw error;
      }

      setTimeout(() => {
        currentSpread = prevNum;
        updateIndicator(`${currentSpread} / ${totalPages}`, currentSpread);
        if (sliderEl) sliderEl.value = currentSpread;
        finishFlip();
      }, getFlipDuration());
      return;
    }

    // Dual mode prev
    if (currentSpread <= 1) return;
    const prevSpread = currentSpread <= 2 ? 1 : currentSpread - 2;
    if (prefersReducedMotion()) {
      await renderSpread(prevSpread);
      return;
    }

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasLeft, flipFront);
    const turnedBackPage = currentSpread <= 2 ? 1 : currentSpread - 1;
    try {
      await renderPageToCanvas(turnedBackPage, flipBack, ctxFlipBack);
    } catch (error) {
      finishFlip();
      throw error;
    }

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flipping-prev';
    flipContainer.classList.add('is-active');

    try {
      await renderSpread(prevSpread);
      setTimeout(finishFlip, getFlipDuration());
    } catch (error) {
      finishFlip();
      throw error;
    }
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
