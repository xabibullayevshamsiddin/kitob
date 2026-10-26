/**
 * Kitobxon Ultra-Realistic 3D Flipbook Engine (Powered by Nodlik/StPageFlip + PDF.js)
 * Features:
 * - Authentic soft diagonal paper curling & folding via Nodlik/StPageFlip (drawSoft)
 * - Corner hover fold preview, mouse drag, touch swipe, and single-click page turning
 * - High-DPI PDF.js page rendering with priority pre-fetching & LRU image cache
 * - Zero-latency Web Audio API paper rustle sound (oxidvideos-page-flip-1-178322.mp3)
 * - Full support for page jumper input, dropdown selector, scrubber slider, zoom, and PDF annotations
 */

(function () {
  'use strict';

  // Global state
  let pdfDoc = null;
  let totalPages = 0;
  let currentPage = 1; // 1-based page number
  let zoomScale = 1.0;
  let isMobile = window.innerWidth < 900;
  let lastWindowWidth = window.innerWidth;
  let initializedRoot = null;
  let soundEnabled = true;
  let cachedBaseViewport = null;
  let pageFlipInstance = null;
  let renderGeneration = 0;
  let autoPlayInterval = null;
  let autoPlayDelay = 7000;
  let currentPaperTheme = 'ivory';

  // DOM elements cache
  let rootEl = null;
  let spreadEl = null;
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
  let lastSoundTimestamp = 0;

  // ── High Performance Rendered Page Image Cache (LRU) ──
  const pageImageCache = new Map();
  const inFlightPageRenders = new Map();
  const MAX_PAGE_CACHE = 36;

  function getPageFlipClass() {
    return window.PageFlip || (window.St && window.St.PageFlip) || null;
  }

  function getCacheKey(pageNum, w, h) {
    return `${pageNum}_${w}x${h}`;
  }

  function getCachedPageImage(pageNum, w, h) {
    return pageImageCache.get(getCacheKey(pageNum, w, h)) || null;
  }

  function storeCachedPageImage(pageNum, w, h, dataUrl) {
    if (!dataUrl) return;
    const maxCache = isMobile ? 8 : MAX_PAGE_CACHE;
    const key = getCacheKey(pageNum, w, h);
    if (pageImageCache.has(key)) {
      pageImageCache.delete(key);
    } else if (pageImageCache.size >= maxCache) {
      const oldestKey = pageImageCache.keys().next().value;
      pageImageCache.delete(oldestKey);
    }
    pageImageCache.set(key, dataUrl);
  }

  function clearPageCache() {
    pageImageCache.clear();
    inFlightPageRenders.clear();
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
    if (isMobile) {
      // Mobil qurilmalarda xotira va CPU ni tejash uchun ovoz oldindan majburiy yuklanmaydi
      return;
    }
    htmlAudioFallbackPool = [];
    for (let i = 0; i < HTML_AUDIO_POOL_SIZE; i++) {
      const a = new Audio(soundUrl);
      a.preload = 'auto';
      htmlAudioFallbackPool.push(a);
    }

    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;

    if (!webAudioCtx) {
      webAudioCtx = new AudioCtx();
    }

    fetch(soundUrl)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.arrayBuffer();
      })
      .then((arrayBuffer) => webAudioCtx.decodeAudioData(arrayBuffer))
      .then((decodedBuffer) => {
        cachedAudioBuffer = processCrispAudioBuffer(decodedBuffer);
      })
      .catch((err) => {
        console.warn('Flipbook: Web Audio API dekodlashda xatolik, HTML5 Audio ishlatiladi:', err);
      });
  }

  function processCrispAudioBuffer(buffer) {
    if (!webAudioCtx) return buffer;
    const pcm = buffer.getChannelData(0);
    const sampleRate = buffer.sampleRate;

    let maxAmp = 0;
    for (let i = 0; i < pcm.length; i++) {
      const a = Math.abs(pcm[i]);
      if (a > maxAmp) maxAmp = a;
    }
    if (maxAmp < 0.01) return buffer;

    const threshold = maxAmp * 0.05;
    let startSample = 0;
    for (let i = 0; i < pcm.length; i++) {
      if (Math.abs(pcm[i]) >= threshold) {
        startSample = Math.max(0, i - Math.round(sampleRate * 0.004));
        break;
      }
    }

    const maxDurationSamples = Math.min(pcm.length - startSample, Math.round(sampleRate * 0.7));
    if (maxDurationSamples <= 0) return buffer;

    const trimmed = webAudioCtx.createBuffer(buffer.numberOfChannels, maxDurationSamples, sampleRate);
    for (let ch = 0; ch < buffer.numberOfChannels; ch++) {
      const src = buffer.getChannelData(ch);
      const dst = trimmed.getChannelData(ch);

      for (let j = 0; j < maxDurationSamples; j++) {
        dst[j] = src[startSample + j];
      }

      const fadeInSamples = Math.round(sampleRate * 0.002);
      for (let j = 0; j < fadeInSamples; j++) {
        dst[j] *= j / fadeInSamples;
      }

      const fadeOutSamples = Math.round(sampleRate * 0.035);
      for (let j = 0; j < fadeOutSamples; j++) {
        const idx = maxDurationSamples - 1 - j;
        if (idx >= 0) {
          dst[idx] *= j / fadeOutSamples;
        }
      }
    }

    return trimmed;
  }

  function playPaperTurnSound() {
    if (!soundEnabled) return;
    const now = performance.now();
    if (now - lastSoundTimestamp < 190) return;
    lastSoundTimestamp = now;

    // Mobil qurilmalarda talab qilinganda birinchi marta ovoz yuklanadi
    if (isMobile && !cachedAudioBuffer && htmlAudioFallbackPool.length === 0) {
      try {
        const soundUrl =
          (rootEl && rootEl.getAttribute('data-sound-url')) ||
          (window.location.pathname.includes('/Kitob/public')
            ? '/Kitob/public/sounds/oxidvideos-page-flip-1-178322.mp3'
            : '/sounds/oxidvideos-page-flip-1-178322.mp3');
        const a = new Audio(soundUrl);
        htmlAudioFallbackPool.push(a);
      } catch (_) {}
    }

    if (webAudioCtx && cachedAudioBuffer) {
      try {
        if (webAudioCtx.state === 'suspended') {
          webAudioCtx.resume().catch(() => {});
        }
        const source = webAudioCtx.createBufferSource();
        source.buffer = cachedAudioBuffer;
        source.playbackRate.value = 0.98 + Math.random() * 0.05;

        const gainNode = webAudioCtx.createGain();
        gainNode.gain.value = 0.95;

        source.connect(gainNode);
        gainNode.connect(webAudioCtx.destination);
        source.start(0);
        return;
      } catch (_) {}
    }

    if (htmlAudioFallbackPool.length > 0) {
      try {
        const audio = htmlAudioFallbackPool[htmlAudioPoolIdx];
        htmlAudioPoolIdx = (htmlAudioPoolIdx + 1) % HTML_AUDIO_POOL_SIZE;
        audio.currentTime = 0.12;
        const playPromise = audio.play();
        if (playPromise !== undefined) {
          playPromise.catch(() => {});
        }
      } catch (_) {}
    }
  }

  function init() {
    const candidateRoot = document.getElementById('fb-root');
    if (!candidateRoot) return;

    rootEl = candidateRoot;
    spreadEl = document.getElementById('fb-spread');
    pageIndicator = document.getElementById('fb-page-indicator');
    loadingOverlay = document.getElementById('fb-loading-overlay');
    sliderEl = document.getElementById('fb-page-slider');
    zoomLevelEl = document.getElementById('fb-zoom-level');

    if (!spreadEl) return;

    try {
      const savedTheme = localStorage.getItem('kitob_paper_theme');
      if (savedTheme) {
        currentPaperTheme = savedTheme;
      }
    } catch (_) {}
    fbSetPaperTheme(currentPaperTheme);

    if (rootEl === initializedRoot && pdfDoc && pageFlipInstance) {
      try {
        pageFlipInstance.update();
      } catch (_) {}
      ensurePagesRenderedAround(currentPage);
      return;
    }

    const pdfUrl = rootEl.getAttribute('data-pdf-url');
    if (!pdfUrl) {
      console.warn('Flipbook: data-pdf-url maydoni topilmadi.');
      return;
    }

    const soundUrl =
      (rootEl && rootEl.getAttribute('data-sound-url')) ||
      (window.location.pathname.includes('/Kitob/public')
        ? '/Kitob/public/sounds/oxidvideos-page-flip-1-178322.mp3'
        : '/sounds/oxidvideos-page-flip-1-178322.mp3');
    initAudio(soundUrl);

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
    lastWindowWidth = window.innerWidth;

    // Window resize handler (Mobil aylantirishda manzil satri o'zgarishidan kelib chiqadigan qotishni to'liq bartaraf etadi)
    window.addEventListener(
      'resize',
      debounce(() => {
        if (!pdfDoc || !rootEl || !rootEl.isConnected) return;
        const curWidth = window.innerWidth;
        const widthDiff = Math.abs(curWidth - lastWindowWidth);
        const nextMobile = curWidth < 900;
        const modeChanged = nextMobile !== isMobile;

        // Mobil qurilmalarda faqat vertikal balandlik (address bar ko'rinishi) o'zgarganda qayta qurmaslik!
        if (widthDiff < 30 && !modeChanged) {
          return;
        }

        lastWindowWidth = curWidth;
        isMobile = nextMobile;
        clearPageCache();
        if (modeChanged && totalPages > 0) {
          populatePageSelect(totalPages);
        }
        rebuildStPageFlipBook();
      }, 300)
    );

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!rootEl || !rootEl.isConnected) return;
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      const activeEl = document.activeElement;
      if (
        (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT' || e.target.isContentEditable || (e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"]')))) ||
        (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT' || activeEl.isContentEditable))
      ) {
        return;
      }
      if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
        e.preventDefault();
        fbGoNext();
      } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        e.preventDefault();
        fbGoPrev();
      } else if (e.key === '+' || e.key === '=') {
        fbZoomIn();
      } else if (e.key === '-') {
        fbZoomOut();
      } else if (e.key === '0') {
        fbZoomReset();
      } else if (e.key === 'f' || e.key === 'F') {
        e.preventDefault();
        fbToggleFullscreen();
      } else if (e.key === 's' || e.key === 'S') {
        e.preventDefault();
        fbToggleSound();
      } else if (e.key === 'a' || e.key === 'A') {
        e.preventDefault();
        fbToggleAutoPlay();
      } else if (e.key === 'm' || e.key === 'M') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('open-ambient-music'));
      } else if (e.key === 'Escape') {
        fbStopAutoPlay();
      }
    });

    // Double-click to toggle zoom between 100% and 130%
    spreadEl.addEventListener('dblclick', (e) => {
      if (e.target.closest('button, input, select, a, .fb-nav-arrow')) return;
      if (zoomScale > 1.1) {
        fbZoomReset();
      } else {
        zoomScale = 1.3;
        updateZoomLabel();
        clearPageCache();
        rebuildStPageFlipBook();
      }
    });

    // Slider input change
    if (sliderEl) {
      sliderEl.addEventListener('change', (e) => {
        const val = parseInt(e.target.value, 10);
        if (!isNaN(val) && val >= 1 && val <= totalPages) {
          goToPage(val);
        }
      });
    }

    ['pointerdown', 'mousedown', 'touchstart', 'keydown'].forEach((evt) => {
      window.addEventListener(evt, unlockAudioContext, { passive: true });
    });

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
      .then(async (doc) => {
        pdfDoc = doc;
        totalPages = doc.numPages;
        currentPage = 1;

        try {
          const urlParams = new URLSearchParams(window.location.search);
          const requestedPage = parseInt(urlParams.get('page'), 10);
          if (!isNaN(requestedPage) && requestedPage >= 1 && requestedPage <= totalPages) {
            currentPage = requestedPage;
          }
        } catch (_) {}

        const firstPage = await doc.getPage(1);
        cachedBaseViewport = firstPage.getViewport({ scale: 1.0 });

        populatePageSelect(totalPages);
        if (sliderEl) {
          sliderEl.min = 1;
          sliderEl.max = totalPages;
          sliderEl.value = currentPage;
        }

        await rebuildStPageFlipBook();
        if (loadingOverlay) loadingOverlay.classList.add('is-hidden');
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
   * Build / Rebuild Nodlik/StPageFlip instance inside #fb-spread
   */
  async function rebuildStPageFlipBook() {
    if (!pdfDoc || !spreadEl) return;
    const PageFlipClass = getPageFlipClass();
    if (!PageFlipClass) {
      console.error('Flipbook: StPageFlip (window.PageFlip) topilmadi.');
      return;
    }

    const gen = ++renderGeneration;

    if (pageFlipInstance) {
      try {
        pageFlipInstance.destroy();
      } catch (_) {}
      pageFlipInstance = null;
    }

    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const pageW = Math.max(160, Math.round(singlePageW));
    const pageH = Math.max(220, Math.round(singlePageH));
    const bookTotalW = isMobile ? pageW : pageW * 2;

    // Prepare clean DOM structure inside #fb-spread
    spreadEl.innerHTML = `
      <div id="fb-stf-host" class="fb-stf-host" style="width:${bookTotalW}px;height:${pageH}px;overflow:hidden;opacity:0;transition:opacity 0.25s ease;">
        <div id="fb-stf-book" class="fb-stf-book" style="width:${bookTotalW}px;height:${pageH}px;overflow:hidden;"></div>
      </div>
      <div class="fb-book-spine"></div>
    `;

    const bookEl = document.getElementById('fb-stf-book');
    if (!bookEl) return;

    const fragment = document.createDocumentFragment();
    for (let p = 1; p <= totalPages; p++) {
      const pageDiv = document.createElement('div');
      pageDiv.className = 'fb-stf-page';
      pageDiv.setAttribute('data-density', 'soft');
      pageDiv.setAttribute('data-page-num', String(p));
      pageDiv.innerHTML = `
        <div class="fb-stf-page-inner">
          <div class="fb-stf-page-loader">
            <span class="fb-stf-page-loader-spinner"></span>
            <span>${p}-bet</span>
          </div>
          <img class="fb-stf-page-img" alt="${p}-bet" draggable="false" />
          <div class="fb-stf-spine-lighting"></div>
        </div>
      `;
      fragment.appendChild(pageDiv);
    }

    // Ensure even page count in 2-page landscape mode so StPageFlip never forces the last page to 'hard'
    if (totalPages % 2 !== 0) {
      const endpaperDiv = document.createElement('div');
      endpaperDiv.className = 'fb-stf-page fb-stf-page--endpaper';
      endpaperDiv.setAttribute('data-density', 'soft');
      endpaperDiv.setAttribute('data-page-num', String(totalPages + 1));
      endpaperDiv.setAttribute('data-endpaper', '1');
      endpaperDiv.innerHTML = `
        <div class="fb-stf-page-inner fb-stf-endpaper-inner">
          <div class="fb-stf-endpaper-ornament">
            <span>✦</span>
            <p>Kitobxon</p>
            <small>Mutolaa yakuni</small>
          </div>
          <div class="fb-stf-spine-lighting"></div>
        </div>
      `;
      fragment.appendChild(endpaperDiv);
    }

    bookEl.appendChild(fragment);

    // Pre-render the initial visible spread before initializing so it appears immediately crisp
    const startIdx = Math.max(0, Math.min(currentPage - 1, totalPages - 1));
    const initialPagesToRender = isMobile
      ? [currentPage]
      : [currentPage, currentPage + 1, currentPage - 1].filter((n) => n >= 1 && n <= totalPages);

    await Promise.allSettled(initialPagesToRender.map((p) => renderSinglePdfPage(p, pageW, pageH, gen)));
    if (gen !== renderGeneration) return;

    // Instantiate Nodlik/StPageFlip
    // Note: minWidth = Math.round(pageW * 0.75) ensures:
    // - On Desktop (bookTotalW = 2 * pageW): 2 * pageW >= 2 * 0.75 * pageW -> 'landscape' (2-page spread)
    // - On Mobile  (bookTotalW = 1 * pageW): 1 * pageW <  2 * 0.75 * pageW -> 'portrait'  (1-page spread)
    pageFlipInstance = new PageFlipClass(bookEl, {
      width: pageW,
      height: pageH,
      size: 'stretch',
      minWidth: Math.round(pageW * 0.75),
      maxWidth: Math.round(pageW * 1.25),
      minHeight: Math.round(pageH * 0.75),
      maxHeight: Math.round(pageH * 1.25),
      drawShadow: !isMobile,
      maxShadowOpacity: isMobile ? 0.25 : 0.68,
      showCover: false,
      usePortrait: true,
      mobileScrollSupport: true,
      flippingTime: isMobile ? 550 : 780,
      swipeDistance: isMobile ? 20 : 30,
      showPageCorners: !isMobile,
      clickEventForward: true,
      useMouseEvents: !isMobile,
      disableFlipByClick: false,
      startPage: startIdx,
    });

    pageFlipInstance.loadFromHTML(bookEl.querySelectorAll('.fb-stf-page'));

    // Reveal book smoothly once StPageFlip has wrapped and positioned pages
    const hostEl = document.getElementById('fb-stf-host');
    if (hostEl) {
      hostEl.style.opacity = '1';
    }

    // Enforce soft paper curling density on all pages
    try {
      const stfPages =
        pageFlipInstance.getPageCollection &&
        pageFlipInstance.getPageCollection().getPages &&
        pageFlipInstance.getPageCollection().getPages();
      if (Array.isArray(stfPages)) {
        stfPages.forEach((p) => {
          if (p.setDensity) p.setDensity('soft');
          if (p.setDrawingDensity) p.setDrawingDensity('soft');
        });
      }
    } catch (_) {}

    pageFlipInstance.on('flip', (e) => {
      const idx = typeof e.data === 'number' ? e.data : 0;
      const newPage = Math.max(1, Math.min(totalPages, idx + 1));
      currentPage = newPage;
      syncIndicators(currentPage);
      ensurePagesRenderedAround(currentPage);
    });

    pageFlipInstance.on('changeState', (e) => {
      if (e.data === 'flipping' || e.data === 'user_fold') {
        playPaperTurnSound();
        ensurePagesRenderedAround(currentPage);
      }
    });

    syncIndicators(currentPage);
    ensurePagesRenderedAround(currentPage);
  }

  /**
   * Render a single PDF page to a DataURL and populate its .fb-stf-page-img element(s)
   */
  async function renderSinglePdfPage(pageNum, pageW, pageH, gen = renderGeneration) {
    if (!pdfDoc || pageNum < 1 || pageNum > totalPages) return;

    // Mobil qurilmalarda DPI ni 1.25 bilan cheklash (yuqori DPI li telefonlarda GPU xotirasi to'lib qotmasligi uchun)
    const maxDpr = isMobile ? 1.25 : 2;
    const dpr = Math.min(window.devicePixelRatio || 1, maxDpr);
    const targetW = Math.round(pageW * dpr);
    const targetH = Math.round(pageH * dpr);
    const cacheKey = getCacheKey(pageNum, targetW, targetH);

    const existingUrl = getCachedPageImage(pageNum, targetW, targetH);
    if (existingUrl) {
      applyImageToPageNodes(pageNum, existingUrl);
      return;
    }

    if (inFlightPageRenders.has(cacheKey)) {
      return inFlightPageRenders.get(cacheKey);
    }

    const renderPromise = (async () => {
      try {
        const page = await pdfDoc.getPage(pageNum);
        if (gen !== renderGeneration) return;

        const pageVp = page.getViewport({ scale: 1.0 });
        const scale = (pageH / pageVp.height) * dpr;
        const viewport = page.getViewport({ scale });

        const offscreen = document.createElement('canvas');
        offscreen.width = Math.round(viewport.width);
        offscreen.height = Math.round(viewport.height);
        const ctx = offscreen.getContext('2d', { alpha: false });
        ctx.fillStyle = '#faf8f5';
        ctx.fillRect(0, 0, offscreen.width, offscreen.height);

        await page.render({ canvasContext: ctx, viewport }).promise;
        if (gen !== renderGeneration) return;

        const dataUrl = offscreen.toDataURL('image/jpeg', isMobile ? 0.85 : 0.92);
        storeCachedPageImage(pageNum, targetW, targetH, dataUrl);
        applyImageToPageNodes(pageNum, dataUrl);
      } catch (err) {
        if (err && err.name !== 'RenderingCancelledException') {
          console.warn(`Flipbook: ${pageNum}-betni chizishda xatolik:`, err);
        }
      } finally {
        inFlightPageRenders.delete(cacheKey);
      }
    })();

    inFlightPageRenders.set(cacheKey, renderPromise);
    return renderPromise;
  }

  function applyImageToPageNodes(pageNum, dataUrl) {
    if (!spreadEl) return;
    const nodes = spreadEl.querySelectorAll(`.fb-stf-page[data-page-num="${pageNum}"]`);
    nodes.forEach((node) => {
      const img = node.querySelector('.fb-stf-page-img');
      const loader = node.querySelector('.fb-stf-page-loader');
      if (img && img.getAttribute('src') !== dataUrl) {
        img.setAttribute('src', dataUrl);
        img.classList.add('is-loaded');
      }
      if (loader) {
        loader.classList.add('is-hidden');
      }
    });
  }

  /**
   * Priority render visible spread + adjacent pages ahead and behind
   */
  function ensurePagesRenderedAround(centerPage) {
    if (!pdfDoc) return;
    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const pageW = Math.max(160, Math.round(singlePageW));
    const pageH = Math.max(220, Math.round(singlePageH));
    const gen = renderGeneration;

    // Mobil qurilmalarda bir vaqtning o'zida ko'p sahifa chizish protsessorni to'ldirib yubormasligi uchun
    // faqat joriy va keyingi 1 ta sahifa avval chiziladi:
    const priorityOffsets = isMobile ? [0, 1, -1] : [0, 1, -1, 2, 3, -2, -3, 4, 5, -4];
    const pagesToRender = [];
    priorityOffsets.forEach((offset) => {
      const p = centerPage + offset;
      if (p >= 1 && p <= totalPages && !pagesToRender.includes(p)) {
        pagesToRender.push(p);
      }
    });

    (async () => {
      for (const p of pagesToRender) {
        if (gen !== renderGeneration) break;
        await renderSinglePdfPage(p, pageW, pageH, gen);
      }
    })();
  }

  /**
   * Populate dropdown page selector <select id="fb-page-select">
   */
  function populatePageSelect(total) {
    const selects = document.querySelectorAll('#fb-page-select');
    selects.forEach((selectEl) => {
      selectEl.innerHTML = '';

      const defaultOpt = document.createElement('option');
      defaultOpt.value = '';
      defaultOpt.textContent = "Sahifaga o'tish ▾";
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
        for (let i = 1; i <= total; i += 2) {
          const opt = document.createElement('option');
          opt.value = i;
          const endPage = Math.min(i + 1, total);
          opt.textContent = i === endPage ? `${i}-sahifa` : `${i}-${endPage} sahifalar`;
          opt.className = 'bg-slate-900 text-white';
          selectEl.appendChild(opt);
        }
      }
    });
  }

  /**
   * Calculate optimal single-page dimensions for current viewport & zoomScale
   */
  function calculateOptimalDimensions(baseViewport) {
    const isStandalone = !!document.getElementById('fb-standalone-container');
    const isMfsActive = document.body.classList.contains('fb-mobile-fullscreen-active');
    const containerWidth = rootEl && rootEl.clientWidth ? rootEl.clientWidth : window.innerWidth;
    const containerHeight = window.innerHeight;

    let paddingH = isMobile ? 20 : isStandalone ? 48 : 68;
    let paddingV = isMobile ? 160 : isStandalone ? 125 : 145;

    if (isMfsActive) {
      paddingH = isMobile ? 4 : 24;
      paddingV = isMobile ? 8 : 40;
    }

    const availW = Math.max(280, (isMfsActive && isMobile ? window.innerWidth : containerWidth) - paddingH);
    const availH = Math.max(420, (isMfsActive && isMobile ? window.innerHeight : containerHeight) - paddingV);

    const pageAspect = baseViewport.width / baseViewport.height;
    const spreadAspect = isMobile ? pageAspect : pageAspect * 2;

    let targetW = availW;
    let targetH = targetW / spreadAspect;

    if (targetH > availH) {
      targetH = availH;
      targetW = targetH * spreadAspect;
    }

    targetH = targetH * zoomScale;
    targetW = targetW * zoomScale;

    const singlePageH = targetH;
    const singlePageW = isMobile ? targetW : targetW / 2;

    return { singlePageW, singlePageH };
  }

  /**
   * Sync page indicators, inputs, selects, slider, and dispatch flipbook:pagechange event
   */
  function syncIndicators(pageNum) {
    const clamped = Math.max(1, Math.min(totalPages || 1, pageNum));
    let leftPage = clamped;
    let rightPage = clamped;

    if (!isMobile) {
      leftPage = clamped % 2 === 1 ? clamped : clamped - 1;
      rightPage = Math.min(totalPages, leftPage + 1);
    }

    const labelText =
      isMobile || leftPage === rightPage
        ? `${leftPage} / ${totalPages}`
        : `${leftPage}-${rightPage} / ${totalPages}`;

    if (pageIndicator) {
      pageIndicator.textContent = `Sahifa: ${labelText}`;
    }

    if (sliderEl) {
      sliderEl.value = leftPage;
    }

    document.querySelectorAll('#fb-page-input').forEach((input) => {
      input.value = leftPage;
      input.max = totalPages;
    });

    document.querySelectorAll('#fb-total-pages').forEach((el) => {
      el.textContent = totalPages;
    });

    document.querySelectorAll('#fb-page-select').forEach((selectEl) => {
      selectEl.value = leftPage;
    });

    const mfsPageText = document.getElementById('fb-mfs-page-text');
    if (mfsPageText) {
      mfsPageText.textContent = `${leftPage} / ${totalPages} sahifa`;
    }
    const mfsSlider = document.getElementById('fb-mfs-page-slider');
    if (mfsSlider) {
      mfsSlider.min = 1;
      mfsSlider.max = totalPages;
      mfsSlider.value = leftPage;
    }
    const mfsZoomText = document.getElementById('fb-mfs-zoom-text');
    if (mfsZoomText) {
      mfsZoomText.textContent = Math.round(zoomScale * 100) + '%';
    }

    const visiblePages =
      isMobile || leftPage === rightPage ? [leftPage] : [leftPage, rightPage].filter((p) => p <= totalPages);

    window.dispatchEvent(
      new CustomEvent('flipbook:pagechange', {
        detail: { page: leftPage, totalPages, visiblePages },
      })
    );
  }

  function updateZoomLabel() {
    if (zoomLevelEl) {
      zoomLevelEl.textContent = `${Math.round(zoomScale * 100)}%`;
    }
  }

  function settleRunningAnimation() {
    if (!pageFlipInstance) return;
    try {
      const render = pageFlipInstance.getRender && pageFlipInstance.getRender();
      if (render && typeof render.finishAnimation === 'function') {
        render.finishAnimation();
      }
    } catch (_) {}
  }

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  /**
   * Flip to next page/spread with StPageFlip 3D soft curl animation
   */
  async function fbGoNext() {
    if (!pdfDoc || !pageFlipInstance) return;
    settleRunningAnimation();

    const idx = pageFlipInstance.getCurrentPageIndex();
    const step = isMobile ? 1 : 2;
    if (idx + step >= totalPages) return;

    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const pageW = Math.max(160, Math.round(singlePageW));
    const pageH = Math.max(220, Math.round(singlePageH));

    const nextPage1 = idx + 1 + step;
    const nextPage2 = nextPage1 + 1;
    await Promise.allSettled(
      [nextPage1, nextPage2]
        .filter((p) => p >= 1 && p <= totalPages)
        .map((p) => renderSinglePdfPage(p, pageW, pageH))
    );

    if (prefersReducedMotion()) {
      pageFlipInstance.turnToNextPage();
    } else {
      pageFlipInstance.flipNext('bottom');
    }
  }

  /**
   * Flip to previous page/spread with StPageFlip 3D soft curl animation
   */
  async function fbGoPrev() {
    if (!pdfDoc || !pageFlipInstance) return;
    settleRunningAnimation();

    const idx = pageFlipInstance.getCurrentPageIndex();
    if (idx <= 0) return;

    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const pageW = Math.max(160, Math.round(singlePageW));
    const pageH = Math.max(220, Math.round(singlePageH));

    const prevPage1 = Math.max(1, idx);
    const prevPage2 = Math.max(1, idx - 1);
    await Promise.allSettled([prevPage1, prevPage2].map((p) => renderSinglePdfPage(p, pageW, pageH)));

    if (prefersReducedMotion()) {
      pageFlipInstance.turnToPrevPage();
    } else {
      pageFlipInstance.flipPrev('bottom');
    }
  }

  /**
   * Jump / Flip to specific 1-based page number
   */
  async function goToPage(targetPage) {
    if (!pdfDoc || !pageFlipInstance) return;
    const clamped = Math.max(1, Math.min(targetPage, totalPages));
    const targetIdx = clamped - 1;

    settleRunningAnimation();

    const baseVp = cachedBaseViewport || { width: 595, height: 842 };
    const { singlePageW, singlePageH } = calculateOptimalDimensions(baseVp);
    const pageW = Math.max(160, Math.round(singlePageW));
    const pageH = Math.max(220, Math.round(singlePageH));

    const spreadLeft = isMobile ? clamped : clamped % 2 === 1 ? clamped : clamped - 1;
    const spreadRight = Math.min(totalPages, spreadLeft + 1);
    await Promise.allSettled([spreadLeft, spreadRight].map((p) => renderSinglePdfPage(p, pageW, pageH)));

    if (prefersReducedMotion()) {
      pageFlipInstance.turnToPage(targetIdx);
      currentPage = clamped;
      syncIndicators(currentPage);
      ensurePagesRenderedAround(currentPage);
    } else {
      pageFlipInstance.flip(targetIdx, 'bottom');
    }
  }

  function fbJumpToPageInput() {
    const input = document.getElementById('fb-page-input');
    if (!input || !pdfDoc) return;
    let val = parseInt(input.value, 10);
    if (isNaN(val)) return;
    val = Math.max(1, Math.min(val, totalPages));
    goToPage(val);
  }

  function fbZoomIn() {
    if (zoomScale < 2.2) {
      zoomScale = Math.min(2.2, +(zoomScale + 0.15).toFixed(2));
      updateZoomLabel();
      clearPageCache();
      rebuildStPageFlipBook();
    }
  }

  function fbZoomOut() {
    if (zoomScale > 0.7) {
      zoomScale = Math.max(0.7, +(zoomScale - 0.15).toFixed(2));
      updateZoomLabel();
      clearPageCache();
      rebuildStPageFlipBook();
    }
  }

  function fbZoomReset() {
    zoomScale = 1.0;
    updateZoomLabel();
    clearPageCache();
    rebuildStPageFlipBook();
  }

  function ensureMobileFullscreenUI() {
    let overlay = document.getElementById('fb-mfs-overlay');
    if (!overlay && rootEl) {
      overlay = document.createElement('div');
      overlay.id = 'fb-mfs-overlay';
      overlay.className = 'fb-mfs-overlay';
      overlay.style.display = 'none';
      overlay.innerHTML = `
        <!-- Floating top-left back button (<) -->
        <button type="button" class="fb-mfs-back-btn" onclick="fbToggleFullscreen()" title="To'liq ekrandan chiqish">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>

        <!-- Floating page pill (e.g. 2 / 826 sahifa) -->
        <div class="fb-mfs-page-pill" onclick="fbToggleMfsSlider()" title="Sahifalar slayderini ochish">
          <span id="fb-mfs-page-text">${currentPage} / ${totalPages || 1} sahifa</span>
        </div>

        <!-- Quick Slider Popup -->
        <div id="fb-mfs-slider-wrap" class="fb-mfs-slider-wrap" style="display: none;">
          <input type="range" id="fb-mfs-page-slider" min="1" max="${totalPages || 1}" value="${currentPage}" class="fb-page-slider w-full">
        </div>

        <!-- Bottom Sleek Control Bar (Image 2 style) -->
        <div class="fb-mfs-bottom-bar">
          <button type="button" class="fb-mfs-bar-btn" onclick="fbCycleTheme()" title="Qog'oz rangi">
            <span id="fb-mfs-theme-icon" style="font-size: 16px;">📜</span>
          </button>
          <button type="button" class="fb-mfs-bar-btn" onclick="fbGoPrev()" title="Oldingi sahifa">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" class="fb-mfs-bar-btn" onclick="fbGoNext()" title="Keyingi sahifa">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
          </button>
          <button type="button" class="fb-mfs-bar-btn" onclick="fbToggleMfsZoom()" title="Kattalashtirish">
            <span id="fb-mfs-zoom-text" class="text-[11px] font-mono font-bold text-amber-400">100%</span>
          </button>
          <button type="button" class="fb-mfs-bar-btn" onclick="if(typeof fbtOpen==='function') fbtOpen();" title="Matn rejimi">
            <span class="font-serif font-bold text-sm">Aa</span>
          </button>
          <button type="button" class="fb-mfs-bar-btn" onclick="fbToggleFullscreen()" title="To'liq ekrandan chiqish">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      `;
      rootEl.appendChild(overlay);

      const mfsSlider = overlay.querySelector('#fb-mfs-page-slider');
      if (mfsSlider) {
        mfsSlider.addEventListener('change', (e) => {
          const val = parseInt(e.target.value, 10);
          if (!isNaN(val) && val >= 1 && val <= totalPages) {
            goToPage(val);
          }
        });
      }

      // Tap-to-toggle overlay on middle of book page
      if (spreadEl) {
        spreadEl.addEventListener('click', (e) => {
          if (!document.body.classList.contains('fb-mobile-fullscreen-active')) return;
          if (e.target.closest('button, input, select, a, .fb-nav-arrow, .fb-mfs-overlay')) return;
          overlay.classList.toggle('is-hidden-controls');
        });
      }
    }
    return overlay;
  }

  function fbToggleMfsSlider() {
    const wrap = document.getElementById('fb-mfs-slider-wrap');
    if (!wrap) return;
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
  }

  function fbCycleTheme() {
    const themes = ['ivory', 'sepia', 'night'];
    const idx = themes.indexOf(currentPaperTheme);
    const nextTheme = themes[(idx + 1) % themes.length];
    fbSetPaperTheme(nextTheme);
    const iconEl = document.getElementById('fb-mfs-theme-icon');
    if (iconEl) {
      iconEl.textContent = nextTheme === 'sepia' ? '🍵' : nextTheme === 'night' ? '🌙' : '📜';
    }
  }

  function fbToggleMfsZoom() {
    if (zoomScale > 1.2) {
      zoomScale = 1.0;
    } else {
      zoomScale = 1.25;
    }
    updateZoomLabel();
    const mfsZoom = document.getElementById('fb-mfs-zoom-text');
    if (mfsZoom) mfsZoom.textContent = Math.round(zoomScale * 100) + '%';
    clearPageCache();
    rebuildStPageFlipBook();
  }

  function fbToggleFullscreen() {
    const isMfs = document.body.classList.contains('fb-mobile-fullscreen-active');
    if (isMfs) {
      fbExitMobileFullscreen();
    } else {
      fbEnterMobileFullscreen();
    }
  }

  function fbEnterMobileFullscreen() {
    document.body.classList.add('fb-mobile-fullscreen-active');
    document.documentElement.classList.add('fb-mobile-fullscreen-active');
    ensureMobileFullscreenUI();
    const overlay = document.getElementById('fb-mfs-overlay');
    if (overlay) {
      overlay.style.display = 'block';
      overlay.classList.remove('is-hidden-controls');
    }

    // Brauzerning o'zini ham fullscreenga chaqirish (Android Chrome qo'llab-quvvatlasa)
    try {
      const docEl = document.documentElement;
      if (docEl.requestFullscreen) {
        docEl.requestFullscreen().catch(() => {});
      } else if (docEl.webkitRequestFullscreen) {
        docEl.webkitRequestFullscreen();
      }
    } catch (_) {}

    syncIndicators(currentPage);
    clearPageCache();
    rebuildStPageFlipBook();
  }

  function fbExitMobileFullscreen() {
    document.body.classList.remove('fb-mobile-fullscreen-active');
    document.documentElement.classList.remove('fb-mobile-fullscreen-active');
    const overlay = document.getElementById('fb-mfs-overlay');
    if (overlay) overlay.style.display = 'none';
    const sliderWrap = document.getElementById('fb-mfs-slider-wrap');
    if (sliderWrap) sliderWrap.style.display = 'none';

    try {
      if (document.fullscreenElement || document.webkitFullscreenElement) {
        if (document.exitFullscreen) {
          document.exitFullscreen().catch(() => {});
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        }
      }
    } catch (_) {}

    clearPageCache();
    rebuildStPageFlipBook();
  }

  // Synchronize active classes on paper theme buttons
  function updateThemeButtons(activeTheme) {
    document.querySelectorAll('[data-paper-theme-btn]').forEach((btn) => {
      const btnTheme = btn.getAttribute('data-paper-theme-btn');
      if (btnTheme === activeTheme) {
        btn.classList.add('is-active');
        btn.setAttribute('aria-pressed', 'true');
      } else {
        btn.classList.remove('is-active');
        btn.setAttribute('aria-pressed', 'false');
      }
    });
  }

  document.addEventListener('fullscreenchange', () => {
    updateThemeButtons(currentPaperTheme);
    if (pageFlipInstance) {
      setTimeout(() => {
        try {
          pageFlipInstance.update();
        } catch (_) {}
      }, 100);
    }
  });

  function fbToggleSound() {
    soundEnabled = !soundEnabled;
    const soundBtns = document.querySelectorAll('#fb-sound-btn');
    soundBtns.forEach((btn) => {
      btn.textContent = soundEnabled ? '🔊 Ovoz' : "🔇 Ovoz o'chiq";
    });
  }

  function fbToggleAutoPlay(delayMs) {
    if (delayMs) autoPlayDelay = delayMs;
    if (autoPlayInterval) {
      fbStopAutoPlay();
      return false;
    } else {
      autoPlayInterval = setInterval(() => {
        if (!pageFlipInstance || !pdfDoc) {
          fbStopAutoPlay();
          return;
        }
        const idx = pageFlipInstance.getCurrentPageIndex();
        const step = isMobile ? 1 : 2;
        if (idx + step >= totalPages) {
          fbStopAutoPlay();
        } else {
          fbGoNext();
        }
      }, autoPlayDelay);

      window.dispatchEvent(
        new CustomEvent('flipbook:autoplay', { detail: { active: true, delay: autoPlayDelay } })
      );
      return true;
    }
  }

  function fbStopAutoPlay() {
    if (autoPlayInterval) {
      clearInterval(autoPlayInterval);
      autoPlayInterval = null;
      window.dispatchEvent(new CustomEvent('flipbook:autoplay', { detail: { active: false } }));
    }
  }

  function fbSetPaperTheme(theme) {
    currentPaperTheme = theme || 'ivory';
    try {
      localStorage.setItem('kitob_paper_theme', currentPaperTheme);
    } catch (_) {}

    const stages = document.querySelectorAll('.fb-stage-wrapper');
    if (stages.length > 0) {
      stages.forEach((s) => s.setAttribute('data-paper-theme', currentPaperTheme));
    } else if (rootEl) {
      rootEl.setAttribute('data-paper-theme', currentPaperTheme);
    }

    if (currentPaperTheme === 'sepia') {
      document.documentElement.style.setProperty('--fb-paper-tone', '#f4ebd0');
    } else if (currentPaperTheme === 'night') {
      document.documentElement.style.setProperty('--fb-paper-tone', '#0f1420');
    } else {
      document.documentElement.style.setProperty('--fb-paper-tone', '#faf8f5');
    }

    updateThemeButtons(currentPaperTheme);

    window.dispatchEvent(
      new CustomEvent('flipbook:themechange', { detail: { theme: currentPaperTheme } })
    );
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
  window.fbToggleMfsSlider = fbToggleMfsSlider;
  window.fbCycleTheme = fbCycleTheme;
  window.fbToggleMfsZoom = fbToggleMfsZoom;
  window.fbEnterMobileFullscreen = fbEnterMobileFullscreen;
  window.fbExitMobileFullscreen = fbExitMobileFullscreen;
  window.fbToggleSound = fbToggleSound;
  window.fbToggleAutoPlay = fbToggleAutoPlay;
  window.fbStopAutoPlay = fbStopAutoPlay;
  window.fbSetPaperTheme = fbSetPaperTheme;
  window.fbGetPdfDoc = function () { return pdfDoc; };
  window.fbGetCurrentPage = function () { return currentPage; };
  window.fbGetTotalPages = function () { return totalPages; };
  window.initFlipbook = init;

  // Auto-init
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 50);
  }
})();
