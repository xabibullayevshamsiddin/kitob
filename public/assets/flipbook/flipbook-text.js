/**
 * Flipbook "Matn rejimi" — Butun PDF kitobning barcha sahifalaridan matnni ajratib olib,
 * shrift turi / o'lchami / qator oralig'i / mavzuni JONLI o'zgartirish imkonini beradi.
 * Sozlamalar reader.blade.php (matnli mutolaa) bilan umumiy localStorage kalitlarida saqlanadi.
 */
(function () {
  'use strict';

  const FONTS = {
    merriweather: "'Merriweather', Georgia, serif",
    spectral: "'Spectral', Georgia, serif",
    sans: "'DM Sans', system-ui, sans-serif",
    mono: "'DM Mono', ui-monospace, monospace",
  };
  const FONT_LABELS = {
    merriweather: 'Merriweather',
    spectral: 'Spectral',
    sans: 'DM Sans',
    mono: 'DM Mono',
  };
  const LINE_LABELS = {
    '1.4': 'Qator: zich',
    '1.6': "Qator: o'rtacha",
    '1.8': 'Qator: qulay',
    '2.1': 'Qator: keng',
  };

  const MIN_SIZE = 13;
  const MAX_SIZE = 40;
  const DEF_SIZE = 19;

  const state = {
    open: false,
    page: 1,
    total: 1,
    size: parseInt(localStorage.getItem('reader_size'), 10) || DEF_SIZE,
    font: localStorage.getItem('reader_font') || 'merriweather',
    line: parseFloat(localStorage.getItem('reader_line')) || 1.8,
    theme: localStorage.getItem('fbt_theme') || 'dark',
    cache: {},
    renderedPages: {},
    token: 0,
     programmaticScroll: false,
  };
  if (!FONTS[state.font]) state.font = 'merriweather';
  state.size = Math.min(MAX_SIZE, Math.max(MIN_SIZE, state.size));

  const $ = (id) => document.getElementById(id);

  function closeAllDropdowns(exceptId) {
    document.querySelectorAll('.fbt-dd.is-open').forEach((dd) => {
      if (dd.id !== exceptId) dd.classList.remove('is-open');
    });
  }

  function toggleDropdown(id) {
    const dd = $(id);
    if (!dd) return;
    const willOpen = !dd.classList.contains('is-open');
    closeAllDropdowns(id);
    dd.classList.toggle('is-open', willOpen);
  }

  function applyStyle() {
    const panel = $('fbt-panel');
    const body = $('fbt-body');
    if (!panel || !body) return;
    body.style.fontSize = state.size + 'px';
    body.style.lineHeight = String(state.line);
    body.style.fontFamily = FONTS[state.font] || FONTS.merriweather;
    panel.setAttribute('data-theme', state.theme);

    const label = $('fbt-size-label');
    if (label) label.textContent = state.size + 'px';
    const range = $('fbt-size-range');
    if (range) range.value = state.size;

    const fontLabel = $('fbt-font-label');
    if (fontLabel) fontLabel.textContent = FONT_LABELS[state.font] || 'Merriweather';
    document.querySelectorAll('[data-fbt-font]').forEach((btn) => {
      btn.classList.toggle('is-selected', btn.getAttribute('data-fbt-font') === state.font);
    });

    const lineKey = String(state.line);
    const lineLabel = $('fbt-line-label');
    if (lineLabel) lineLabel.textContent = LINE_LABELS[lineKey] || 'Qator: ' + lineKey;
    document.querySelectorAll('[data-fbt-line]').forEach((btn) => {
      btn.classList.toggle('is-selected', btn.getAttribute('data-fbt-line') === lineKey);
    });

    document.querySelectorAll('[data-fbt-theme]').forEach((b) => {
      b.classList.toggle('is-active', b.getAttribute('data-fbt-theme') === state.theme);
    });
  }

  function save() {
    localStorage.setItem('reader_size', state.size);
    localStorage.setItem('reader_font', state.font);
    localStorage.setItem('reader_line', state.line);
    localStorage.setItem('fbt_theme', state.theme);
  }

  function setSize(n) {
    state.size = Math.min(MAX_SIZE, Math.max(MIN_SIZE, Math.round(n)));
    save();
    applyStyle();
  }

  function updatePageIndicators() {
    const total = state.total || 1;
    const cur = Math.min(Math.max(1, state.page), total);
    state.page = cur;

    const ind = $('fbt-page-indicator');
    if (ind) ind.textContent = cur + ' / ' + total;
    const indFoot = $('fbt-page-indicator-foot');
    if (indFoot) indFoot.textContent = 'Sahifa ' + cur + ' / ' + total;

    ['fbt-prev', 'fbt-prev-top'].forEach((id) => {
      const btn = $(id);
      if (btn) btn.disabled = cur <= 1;
    });
    ['fbt-next', 'fbt-next-top'].forEach((id) => {
      const btn = $(id);
      if (btn) btn.disabled = cur >= total;
    });

    document.querySelectorAll('#fbt-page-menu [data-fbt-page]').forEach((btn) => {
      btn.classList.toggle('is-selected', parseInt(btn.getAttribute('data-fbt-page'), 10) === cur);
    });
  }

  function buildPageDropdownMenu(total) {
    const menu = $('fbt-page-menu');
    if (!menu) return;
    let html = '';
    for (let p = 1; p <= total; p++) {
      const sel = p === state.page ? ' is-selected' : '';
      html +=
        '<button type="button" class="fbt-dd-item' +
        sel +
        '" data-fbt-page="' +
        p +
        '" onclick="fbtJumpToPage(' +
        p +
        ')">' +
        '<span>' +
        p +
        '-sahifa</span>' +
        '<span class="fbt-dd-check">✓</span>' +
        '</button>';
    }
    menu.innerHTML = html;
  }

  function joinParts(parts, rowH) {
    let text = '';
    let prevEnd = null;
    parts.forEach((p) => {
      if (prevEnd !== null) {
        const gap = p.x - prevEnd;
        if (gap > rowH * 0.14 && !text.endsWith(' ') && !p.s.startsWith(' ')) {
          text += ' ';
        }
      }
      text += p.s;
      prevEnd = p.x + p.w;
    });
    return text.replace(/\s+/g, ' ').trim();
  }

  /**
   * PDF sahifasidagi textContent elementlarini qator, ustun (glossary/jadval),
   * sarlavha va paragraflarga aniq yig'ish.
   */
  function buildBlocks(items) {
    const rows = [];
    items.forEach((it) => {
      const str = it.str;
      if (str === undefined || str === null || str === '') return;
      const h = Math.abs(it.height) || Math.abs(it.transform[3]) || 10;
      const x = it.transform[4];
      const y = it.transform[5];
      const w = it.width || str.length * h * 0.45;

      // Superscript ("2nd") va bir qatordagi bo'laklarni birlashtirish
      let row = rows.find((r) => Math.abs(r.y - y) <= Math.max(4.5, Math.max(r.h, h) * 0.6));
      if (!row) {
        row = { y: y, h: h, parts: [] };
        rows.push(row);
      }
      row.parts.push({ x: x, w: w, h: h, s: str });
      if (h > row.h) {
        row.y = y;
        row.h = h;
      }
    });

    if (!rows.length) return [];

    // Tepadan pastga saralash
    rows.sort((a, b) => b.y - a.y);

    // Har bir qatorning chap va o'ng chegaralarini aniqlash
    const rawLines = rows
      .map((r) => {
        r.parts.sort((a, b) => a.x - b.x);
        // Ketma-ket bir xil yoki juda yaqin bo'laklarni birlashtirib ustun uzilishlarini topish
        const segments = [];
        let curSeg = null;
        r.parts.forEach((p) => {
          if (!p.s.trim() && !curSeg) return;
          if (!curSeg) {
            curSeg = { startX: p.x, endX: p.x + p.w, parts: [p] };
            segments.push(curSeg);
          } else {
            const gap = p.x - curSeg.endX;
            if (gap > Math.max(11, r.h * 1.15)) {
              curSeg = { startX: p.x, endX: p.x + p.w, parts: [p] };
              segments.push(curSeg);
            } else {
              curSeg.parts.push(p);
              curSeg.endX = Math.max(curSeg.endX, p.x + p.w);
            }
          }
        });

        const segTexts = segments
          .map((s) => ({
            startX: s.startX,
            endX: s.endX,
            text: joinParts(s.parts, r.h),
          }))
          .filter((s) => s.text.length > 0);

        if (!segTexts.length) return null;

        const fullText = segTexts.map((s) => s.text).join(' ');
        return {
          y: r.y,
          h: r.h,
          startX: segTexts[0].startX,
          endX: segTexts[segTexts.length - 1].endX,
          segments: segTexts,
          text: fullText,
        };
      })
      .filter(Boolean);

    if (!rawLines.length) return [];

    // Sahifa ostidagi yolg'iz sahifa raqamini olib tashlash (chunki sahifa kartasida raqam bor)
    const lines = rawLines.filter((l, idx) => {
      if (idx === rawLines.length - 1 && /^\d{1,4}$/.test(l.text) && l.y < 90) {
        return false;
      }
      return true;
    });

    if (!lines.length) return [];

    const heights = lines.map((l) => l.h).sort((a, b) => a - b);
    const medianH = heights[Math.floor(heights.length / 2)] || 10;

    const startXs = lines.map((l) => l.startX).sort((a, b) => a - b);
    const leftMargin = startXs[Math.floor(startXs.length * 0.15)] || startXs[0] || 72;

    const endXs = lines.map((l) => l.endX).sort((a, b) => a - b);
    const rightMargin = endXs[Math.floor(endXs.length * 0.85)] || endXs[endXs.length - 1] || 500;

    const gaps = [];
    for (let i = 1; i < lines.length; i++) {
      const g = lines[i - 1].y - lines[i].y;
      if (g > 0) gaps.push(g);
    }
    gaps.sort((a, b) => a - b);
    const medianGap = gaps.length ? gaps[Math.floor(gaps.length / 2)] : medianH * 1.35;

    const headingRegex =
      /^(chapter\s+\d+|unit\s+\d+|part\s+\d+|lesson\s+\d+|section\s+\d+|glossary|contents|table of contents|mundarija|kirish|xulosa|muqaddima|\d+[\s\-–—.]*bob|\d+[\s\-–—.]*qism)\b/i;
    const listRegex = /^(\d+[\.\)]|[•\-–—▪◦*]|[a-zA-Z][\.\)])\s+/;

    const blocks = [];
    let lastColX = null;

    for (let i = 0; i < lines.length; i++) {
      const l = lines[i];
      const prevLine = i > 0 ? lines[i - 1] : null;
      const gap = prevLine ? prevLine.y - l.y : 0;
      const prevBlock = blocks.length ? blocks[blocks.length - 1] : null;

      // 1) Sarlavha tekshiruvi
      const isHeading =
        (l.h > medianH * 1.18 && l.text.length < 130) ||
        (headingRegex.test(l.text) && l.text.length < 95) ||
        (i === 0 && l.text.length < 110 && l.segments.length === 1 && l.endX < rightMargin - medianH * 3);

      if (isHeading) {
        blocks.push({ type: 'heading', text: l.text });
        lastColX = null;
        continue;
      }

      // 2) Ikki ustunli qator (masalan: Glossary "word   definition")
      if (
        l.segments.length >= 2 &&
        l.segments[0].startX <= leftMargin + 35 &&
        l.segments[1].startX >= leftMargin + 42 &&
        !listRegex.test(l.segments[0].text + ' ')
      ) {
        const keyText = l.segments[0].text;
        const valText = l.segments
          .slice(1)
          .map((s) => s.text)
          .join(' ');
        lastColX = l.segments[1].startX;
        blocks.push({ type: 'kv', key: keyText, val: valText, colX: lastColX });
        continue;
      }

      // 3) Ikki ustunli qatorning o'ng ustun davomi (indented continuation line)
      if (
        prevBlock &&
        prevBlock.type === 'kv' &&
        l.segments.length === 1 &&
        l.startX >= leftMargin + 38 &&
        gap <= medianGap * 1.65
      ) {
        if (prevBlock.val.endsWith('-')) {
          prevBlock.val = prevBlock.val.slice(0, -1) + l.text;
        } else {
          prevBlock.val += ' ' + l.text;
        }
        continue;
      }

      // 4) Oddiy matn / paragraf
      const isList = listRegex.test(l.text);
      const prevReachedRightEdge =
        prevLine && prevLine.endX >= rightMargin - Math.max(36, medianH * 4.2);
      const normalGap = gap > 0 && gap <= medianGap * 1.38;
      const startsNearLeft = l.startX <= leftMargin + 26;

      const canMergeWithPrev =
        prevBlock &&
        prevBlock.type === 'p' &&
        !isList &&
        normalGap &&
        prevReachedRightEdge &&
        startsNearLeft;

      if (canMergeWithPrev) {
        if (prevBlock.text.endsWith('-')) {
          prevBlock.text = prevBlock.text.slice(0, -1) + l.text;
        } else {
          prevBlock.text += ' ' + l.text;
        }
      } else {
        blocks.push({ type: 'p', text: l.text });
      }
      lastColX = null;
    }

    return blocks;
  }

  async function getPageBlocks(n) {
    if (state.cache[n]) return state.cache[n];
    const pdf = window.fbGetPdfDoc && window.fbGetPdfDoc();
    if (!pdf) return [];
    const page = await pdf.getPage(n);
    const content = await page.getTextContent();
    const blocks = buildBlocks(content.items);
    state.cache[n] = blocks;
    return blocks;
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  function renderBlocksHtml(blocks) {
    if (!blocks || !blocks.length) {
      return (
        '<p class="fbt-muted">Bu sahifada ajratib olinadigan matn topilmadi (rasm yoki muqova bo‘lishi mumkin).</p>'
      );
    }
    return blocks
      .map((b) => {
        if (b.type === 'heading') {
          return '<h3 class="fbt-h">' + esc(b.text) + '</h3>';
        }
        if (b.type === 'kv') {
          return (
            '<div class="fbt-kv">' +
            '<span class="fbt-k">' +
            esc(b.key) +
            '</span>' +
            '<span class="fbt-v">' +
            esc(b.val) +
            '</span>' +
            '</div>'
          );
        }
        return '<p>' + esc(b.text) + '</p>';
      })
      .join('');
  }

  async function renderSinglePageSection(pageNum, total, myToken) {
    if (myToken !== state.token) return;
    const container = $('fbt-page-content-' + pageNum);
    if (!container) return;
    try {
      const blocks = await getPageBlocks(pageNum);
      if (myToken !== state.token) return;
      container.innerHTML = renderBlocksHtml(blocks);
      state.renderedPages[pageNum] = true;
    } catch (e) {
      if (myToken !== state.token) return;
      container.innerHTML = '<p class="fbt-muted">Sahifa matnini o‘qishda xatolik yuz berdi.</p>';
    }
  }

  /**
   * Butun kitobning barcha sahifalarini (1..total) ketma-ket chiqarish
   * va joriy sahifaga avtomatik olib borish.
   */
  async function renderFullBook() {
    const body = $('fbt-body');
    if (!body) return;
    const total = (window.fbGetTotalPages && window.fbGetTotalPages()) || 1;
    state.total = total;
    state.page = Math.min(Math.max(1, state.page), total);
    state.renderedPages = {};
    const my = ++state.token;

    buildPageDropdownMenu(total);
    updatePageIndicators();

    // Barcha sahifalar uchun karkas (skeleton) yaratamiz
    let skeletons = '';
    for (let p = 1; p <= total; p++) {
      skeletons +=
        '<section class="fbt-page" id="fbt-page-' +
        p +
        '" data-page="' +
        p +
        '">' +
        '<div class="fbt-page-head">' +
        '<span class="fbt-page-badge">Sahifa ' +
        p +
        ' / ' +
        total +
        '</span>' +
        '<span>Matn rejimi</span>' +
        '</div>' +
        '<div id="fbt-page-content-' +
        p +
        '" class="fbt-page-content">' +
        '<p class="fbt-muted">Sahifa ' +
        p +
        ' yuklanmoqda…</p>' +
        '</div>' +
        '</section>';
    }
    body.innerHTML = skeletons;

    // Avval foydalanuvchi turgan sahifani va uning yonidagi sahifani yuklaymiz
    const startPage = state.page;
    const priorityPages = [startPage];
    if (startPage + 1 <= total) priorityPages.push(startPage + 1);
    if (startPage - 1 >= 1) priorityPages.push(startPage - 1);

    for (const p of priorityPages) {
      await renderSinglePageSection(p, total, my);
    }
    if (my !== state.token) return;

    // Foydalanuvchi turgan sahifaga silliq o'tkazamiz
    scrollToPage(startPage, false);

    // Qolgan barcha sahifalarni ketma-ket yuklab chiqamiz (butun kitob to'liq ochiladi)
    for (let p = 1; p <= total; p++) {
      if (my !== state.token) return;
      if (!state.renderedPages[p]) {
        await renderSinglePageSection(p, total, my);
      }
    }
  }

  function scrollToPage(pageNum, smooth) {
    const scroller = $('fbt-scroll');
    const target = $('fbt-page-' + pageNum);
    if (!scroller || !target) return;
    state.programmaticScroll = true;
    const top = target.offsetTop - scroller.offsetTop - 12;
    scroller.scrollTo({
      top: Math.max(0, top),
      behavior: smooth ? 'smooth' : 'auto',
    });
    setTimeout(() => {
      state.programmaticScroll = false;
    }, smooth ? 420 : 60);
  }

  function jumpToPage(n) {
    const total = state.total || (window.fbGetTotalPages && window.fbGetTotalPages()) || 1;
    const targetPage = Math.min(Math.max(1, parseInt(n, 10) || 1), total);
    state.page = targetPage;
    updatePageIndicators();
    closeAllDropdowns();
    scrollToPage(targetPage, true);
  }

  function open() {
    const panel = $('fbt-panel');
    if (!panel) return;
    state.page = (window.fbGetCurrentPage && window.fbGetCurrentPage()) || 1;
    state.total = (window.fbGetTotalPages && window.fbGetTotalPages()) || 1;
    state.open = true;
    panel.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    applyStyle();
    renderFullBook();
  }

  function close() {
    const panel = $('fbt-panel');
    if (!panel) return;
    state.open = false;
    closeAllDropdowns();
    panel.classList.remove('is-open');
    document.body.style.overflow = '';
    if (window.fbGoToPage) window.fbGoToPage(state.page);
  }

  function go(delta) {
    jumpToPage(state.page + delta);
  }

  // Klaviatura: panel ochiq bo'lsa flipbook'ning o'z tugmalaridan oldin ushlab olamiz
  document.addEventListener(
    'keydown',
    function (e) {
      if (!state.open) return;
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      const t = e.target;
      const typing = t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.tagName === 'SELECT');
      if (e.key === 'Escape') {
        e.preventDefault();
        e.stopImmediatePropagation();
        const openDd = document.querySelector('.fbt-dd.is-open');
        if (openDd) {
          closeAllDropdowns();
        } else {
          close();
        }
        return;
      }
      if (typing) return;
      let handled = true;
      if (e.key === '+' || e.key === '=') setSize(state.size + 1);
      else if (e.key === '-') setSize(state.size - 1);
      else if (e.key === '0') setSize(DEF_SIZE);
      else if (e.key === 'ArrowRight' || e.key === 'PageDown') go(1);
      else if (e.key === 'ArrowLeft' || e.key === 'PageUp') go(-1);
      else handled = false;
      if (handled) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    },
    true
  );

  document.addEventListener('DOMContentLoaded', function () {
    // Shrift tanlash tugmalari
    document.querySelectorAll('[data-fbt-font]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const f = btn.getAttribute('data-fbt-font');
        if (FONTS[f]) {
          state.font = f;
          save();
          applyStyle();
        }
        closeAllDropdowns();
      });
    });

    // Qator oralig'ini tanlash tugmalari
    document.querySelectorAll('[data-fbt-line]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const l = parseFloat(btn.getAttribute('data-fbt-line'));
        if (l) {
          state.line = l;
          save();
          applyStyle();
        }
        closeAllDropdowns();
      });
    });

    // Shrift o'lchami slayderi
    const range = $('fbt-size-range');
    if (range) range.addEventListener('input', (e) => setSize(parseInt(e.target.value, 10)));

    // Mavzu tugmalari
    document.querySelectorAll('[data-fbt-theme]').forEach((b) =>
      b.addEventListener('click', () => {
        state.theme = b.getAttribute('data-fbt-theme');
        save();
        applyStyle();
      })
    );

    // Tashqariga bosilganda ochiq dropdownlarni yopish
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.fbt-dd')) {
        closeAllDropdowns();
      }
    });

    // Scroll paytida qaysi sahifada ekanligini avtomatik aniqlash
    const scroller = $('fbt-scroll');
    if (scroller) {
      scroller.addEventListener(
        'scroll',
        () => {
          if (!state.open || state.programmaticScroll) return;
          const scrollMid = scroller.scrollTop + scroller.clientHeight * 0.32;
          const pages = scroller.querySelectorAll('.fbt-page');
          let current = state.page;
          pages.forEach((sec) => {
            const top = sec.offsetTop - scroller.offsetTop;
            if (top <= scrollMid) {
              current = parseInt(sec.getAttribute('data-page'), 10) || current;
            }
          });
          if (current !== state.page) {
            state.page = current;
            updatePageIndicators();
          }
        },
        { passive: true }
      );
    }

    applyStyle();
  });

  window.fbtOpen = open;
  window.fbtClose = close;
  window.fbtToggleDd = toggleDropdown;
  window.fbtJumpToPage = jumpToPage;
  window.fbtSizeUp = () => setSize(state.size + 1);
  window.fbtSizeDown = () => setSize(state.size - 1);
  window.fbtSizeReset = () => setSize(DEF_SIZE);
  window.fbtPrev = () => go(-1);
  window.fbtNext = () => go(1);
})();
