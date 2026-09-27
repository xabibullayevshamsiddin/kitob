/**
 * Kitobxon Ultra-Realistic 3D Flipbook Engine
 * Features:
 * - Dynamic viewport calculation (fits comfortably large and close to reader)
 * - Sharp high-DPI PDF.js canvas rendering
 * - High-quality authentic page-turn sound using D:\OSPanel\domains\localhost\Kitob\public\sounds\oxidvideos-page-flip-1-178322.mp3
 * - Audio pool for rapid responsive page flipping
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
  let pageIndicator = null;
  let loadingOverlay = null;
  let sliderEl = null;
  let zoomLevelEl = null;

  // Real Audio Pool using /sounds/oxidvideos-page-flip-1-178322.mp3
  let soundAudioPool = [];
  const AUDIO_POOL_SIZE = 4;
  let audioPoolIdx = 0;

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

  function init() {
    rootEl = document.getElementById('fb-root');
    if (!rootEl) return;

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

    // Window resize handler
    window.addEventListener('resize', debounce(() => {
      const wasMobile = isMobile;
      isMobile = window.innerWidth < 900;
      renderSpread(currentSpread);
    }, 180));

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!rootEl || !rootEl.isConnected) return;
      if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
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

    // Touch swipe support
    let touchStartX = 0;
    let touchStartY = 0;
    rootEl.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    rootEl.addEventListener('touchend', (e) => {
      const diffX = e.changedTouches[0].screenX - touchStartX;
      const diffY = e.changedTouches[0].screenY - touchStartY;
      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 40) {
        if (diffX < 0) {
          fbGoNext(); // swiped left -> next
        } else {
          fbGoPrev(); // swiped right -> prev
        }
      }
    }, { passive: true });

    // Double-click to toggle zoom between 100% and 140%
    if (spreadEl) {
      spreadEl.addEventListener('dblclick', () => {
        if (zoomScale > 1.1) {
          fbZoomReset();
        } else {
          zoomScale = 1.35;
          updateZoomLabel();
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

    // Load PDF Document
    loadDocument(pdfUrl);
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
   * Render single page onto a specific canvas
   */
  async function renderPageToCanvas(pageNum, canvas, ctx) {
    if (!cachedBaseViewport && pdfDoc) {
      const p1 = await pdfDoc.getPage(1);
      cachedBaseViewport = p1.getViewport({ scale: 1.0 });
    }

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

    const page = await pdfDoc.getPage(pageNum);
    const pageVp = page.getViewport({ scale: 1.0 });
    const scale = (singlePageH / pageVp.height) * dpr;
    const viewport = page.getViewport({ scale });

    const renderContext = {
      canvasContext: ctx,
      viewport: viewport,
    };

    return page.render(renderContext).promise;
  }

  /**
   * Render the current 2-page spread (or 1 page in mobile)
   */
  async function renderSpread(spreadStart) {
    if (!pdfDoc) return;

    if (renderTasks.left && renderTasks.left.cancel) renderTasks.left.cancel();
    if (renderTasks.right && renderTasks.right.cancel) renderTasks.right.cancel();

    if (isMobile) {
      const pageNum = Math.max(1, Math.min(spreadStart, totalPages));
      currentSpread = pageNum;
      await renderPageToCanvas(pageNum, canvasRight, ctxRight);
      updateIndicator(`${currentSpread} / ${totalPages}`);
      if (sliderEl) sliderEl.value = currentSpread;
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

    await Promise.all([
      renderPageToCanvas(leftNum, canvasLeft, ctxLeft),
      renderPageToCanvas(rightNum, canvasRight, ctxRight),
    ]);

    if (currentSpread === 1) {
      updateIndicator(`1 (Muqova) / ${totalPages}`);
      if (sliderEl) sliderEl.value = 1;
    } else {
      const rightStr = rightNum <= totalPages ? `-${rightNum}` : '';
      updateIndicator(`${leftNum}${rightStr} / ${totalPages}`);
      if (sliderEl) sliderEl.value = leftNum;
    }
  }

  function updateIndicator(text) {
    if (pageIndicator) {
      pageIndicator.textContent = `Sahifa: ${text}`;
    }
  }

  function updateZoomLabel() {
    if (zoomLevelEl) {
      zoomLevelEl.textContent = `${Math.round(zoomScale * 100)}%`;
    }
  }

  /**
   * Flip to next page with 3D animation
   */
  async function fbGoNext() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread >= totalPages) return;
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipFront);
      prepareBlankCanvas(flipBack);

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flipping-next';
      flipContainer.classList.add('is-active');

      const nextNum = currentSpread + 1;
      await renderPageToCanvas(nextNum, canvasRight, ctxRight);

      setTimeout(() => {
        flipContainer.classList.remove('is-active');
        flipLeaf.className = 'fb-flip-leaf';
        currentSpread = nextNum;
        updateIndicator(`${currentSpread} / ${totalPages}`);
        if (sliderEl) sliderEl.value = currentSpread;
        isFlipping = false;
      }, 480);
      return;
    }

    // Dual mode next
    const nextSpread = currentSpread === 1 ? 2 : currentSpread + 2;
    if (nextSpread > totalPages) return;

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasRight, flipFront);
    prepareBlankCanvas(flipBack);

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--right fb-flipping-next';
    flipContainer.classList.add('is-active');

    renderSpread(nextSpread).then(() => {
      setTimeout(() => {
        flipContainer.classList.remove('is-active');
        flipLeaf.className = 'fb-flip-leaf';
        isFlipping = false;
      }, 480);
    });
  }

  /**
   * Flip to previous page with 3D animation
   */
  async function fbGoPrev() {
    if (isFlipping || !pdfDoc) return;

    if (isMobile) {
      if (currentSpread <= 1) return;
      isFlipping = true;
      playPaperTurnSound();

      prepareFlipCanvas(canvasRight, flipFront);
      prepareBlankCanvas(flipBack);

      flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flipping-prev';
      flipContainer.classList.add('is-active');

      const prevNum = currentSpread - 1;
      await renderPageToCanvas(prevNum, canvasRight, ctxRight);

      setTimeout(() => {
        flipContainer.classList.remove('is-active');
        flipLeaf.className = 'fb-flip-leaf';
        currentSpread = prevNum;
        updateIndicator(`${currentSpread} / ${totalPages}`);
        if (sliderEl) sliderEl.value = currentSpread;
        isFlipping = false;
      }, 480);
      return;
    }

    // Dual mode prev
    if (currentSpread <= 1) return;
    const prevSpread = currentSpread <= 2 ? 1 : currentSpread - 2;

    isFlipping = true;
    playPaperTurnSound();

    prepareFlipCanvas(canvasLeft, flipFront);
    prepareBlankCanvas(flipBack);

    flipLeaf.className = 'fb-flip-leaf fb-flip-leaf--left fb-flipping-prev';
    flipContainer.classList.add('is-active');

    renderSpread(prevSpread).then(() => {
      setTimeout(() => {
        flipContainer.classList.remove('is-active');
        flipLeaf.className = 'fb-flip-leaf';
        isFlipping = false;
      }, 480);
    });
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

  function prepareBlankCanvas(targetCanvas) {
    const ref = canvasRight || canvasLeft;
    targetCanvas.width = ref ? ref.width : 500;
    targetCanvas.height = ref ? ref.height : 700;
    targetCanvas.style.width = ref ? ref.style.width : '500px';
    targetCanvas.style.height = ref ? ref.style.height : '700px';
    const ctx = targetCanvas.getContext('2d');
    ctx.fillStyle = '#faf8f5';
    ctx.fillRect(0, 0, targetCanvas.width, targetCanvas.height);
  }

  function fbZoomIn() {
    if (zoomScale < 2.2) {
      zoomScale = Math.min(2.2, +(zoomScale + 0.15).toFixed(2));
      updateZoomLabel();
      renderSpread(currentSpread);
    }
  }

  function fbZoomOut() {
    if (zoomScale > 0.7) {
      zoomScale = Math.max(0.7, +(zoomScale - 0.15).toFixed(2));
      updateZoomLabel();
      renderSpread(currentSpread);
    }
  }

  function fbZoomReset() {
    zoomScale = 1.0;
    updateZoomLabel();
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
