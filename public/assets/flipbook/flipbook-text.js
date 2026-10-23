/**
 * Flipbook "Matn rejimi" — PDF sahifalaridan matnni ajratib olib,
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
  const MIN_SIZE = 13;
  const MAX_SIZE = 40;
  const DEF_SIZE = 19;

  const state = {
    open: false,
    page: 1,
    size: parseInt(localStorage.getItem('reader_size'), 10) || DEF_SIZE,
    font: localStorage.getItem('reader_font') || 'merriweather',
    line: parseFloat(localStorage.getItem('reader_line')) || 1.8,
    theme: localStorage.getItem('fbt_theme') || 'dark',
    cache: {},
    token: 0,
  };
  if (!FONTS[state.font]) state.font = 'merriweather';
  state.size = Math.min(MAX_SIZE, Math.max(MIN_SIZE, state.size));

  const $ = (id) => document.getElementById(id);

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
    const fontSel = $('fbt-font-select');
    if (fontSel) fontSel.value = state.font;
    const lineSel = $('fbt-line-select');
    if (lineSel) lineSel.value = String(state.line);
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

  /** PDF textContent elementlarini qator va paragraflarga yig'ish. */
  function buildBlocks(items) {
    const rows = [];
    items.forEach((it) => {
      const str = it.str;
      if (str === undefined || str === null) return;
      const h = Math.abs(it.height) || Math.abs(it.transform[3]) || 10;
      const x = it.transform[4];
      const y = it.transform[5];
      let row = rows.find((r) => Math.abs(r.y - y) <= Math.max(2, h * 0.45));
      if (!row) {
        row = { y: y, h: h, parts: [] };
        rows.push(row);
      }
      row.parts.push({ x: x, w: it.width || 0, h: h, s: str });
      row.h = Math.max(row.h, h);
    });

    rows.sort((a, b) => b.y - a.y);
    const lines = rows
      .map((r) => {
        r.parts.sort((a, b) => a.x - b.x);
        let text = '';
        let prevEnd = null;
        r.parts.forEach((p) => {
          if (prevEnd !== null) {
            const gap = p.x - prevEnd;
            if (gap > r.h * 3) text += '  —  ';
            else if (gap > r.h * 0.12 && !text.endsWith(' ') && !p.s.startsWith(' ')) text += ' ';
          }
          text += p.s;
          prevEnd = p.x + p.w;
        });
        return { y: r.y, h: r.h, text: text.replace(/\s+$/g, '').replace(/^\s+/g, '') };
      })
      .filter((l) => l.text.length > 0);

    if (!lines.length) return [];

    const heights = lines.map((l) => l.h).sort((a, b) => a - b);
    const medianH = heights[Math.floor(heights.length / 2)] || 10;
    const gaps = [];
    for (let i = 1; i < lines.length; i++) gaps.push(lines[i - 1].y - lines[i].y);
    gaps.sort((a, b) => a - b);
    const medianGap = gaps.length ? gaps[Math.floor(gaps.length / 2)] : medianH * 1.3;

    const blocks = [];
    let cur = null;
    lines.forEach((l, i) => {
      const isHeading = l.h > medianH * 1.25 && l.text.length < 120;
      const gap = i > 0 ? lines[i - 1].y - l.y : 0;
      const newPara = !cur || isHeading || cur.heading || gap > medianGap * 1.55;
      if (newPara) {
        cur = { heading: isHeading, text: l.text };
        blocks.push(cur);
      } else if (cur.text.endsWith('-')) {
        cur.text = cur.text.slice(0, -1) + l.text;
      } else {
        cur.text += ' ' + l.text;
      }
    });
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
    return s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  async function render() {
    const body = $('fbt-body');
    if (!body) return;
    const total = (window.fbGetTotalPages && window.fbGetTotalPages()) || 1;
    state.page = Math.min(Math.max(1, state.page), total);
    const my = ++state.token;

    const ind = $('fbt-page-indicator');
    if (ind) ind.textContent = state.page + ' / ' + total;
    const prev = $('fbt-prev');
    const next = $('fbt-next');
    if (prev) prev.disabled = state.page <= 1;
    if (next) next.disabled = state.page >= total;

    body.innerHTML = '<p class="fbt-muted">Matn yuklanmoqda…</p>';
    let blocks = [];
    try {
      blocks = await getPageBlocks(state.page);
    } catch (e) {
      blocks = [];
    }
    if (my !== state.token) return;

    if (!blocks.length) {
      body.innerHTML =
        '<p class="fbt-muted">Bu sahifada ajratib olinadigan matn topilmadi (u rasm yoki skaner bo‘lishi mumkin). ' +
        'Boshqa sahifaga o‘ting yoki asl ko‘rinishga qayting.</p>';
      return;
    }
    body.innerHTML = blocks
      .map((b) => (b.heading ? '<h3 class="fbt-h">' + esc(b.text) + '</h3>' : '<p>' + esc(b.text) + '</p>'))
      .join('');
    const scroller = $('fbt-scroll');
    if (scroller) scroller.scrollTop = 0;
  }

  function open() {
    const panel = $('fbt-panel');
    if (!panel) return;
    state.page = (window.fbGetCurrentPage && window.fbGetCurrentPage()) || 1;
    state.open = true;
    panel.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    applyStyle();
    render();
  }

  function close() {
    const panel = $('fbt-panel');
    if (!panel) return;
    state.open = false;
    panel.classList.remove('is-open');
    document.body.style.overflow = '';
    if (window.fbGoToPage) window.fbGoToPage(state.page);
  }

  function go(delta) {
    state.page += delta;
    render();
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
        close();
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
    const fontSel = $('fbt-font-select');
    if (fontSel) fontSel.addEventListener('change', (e) => { state.font = e.target.value; save(); applyStyle(); });
    const lineSel = $('fbt-line-select');
    if (lineSel) lineSel.addEventListener('change', (e) => { state.line = parseFloat(e.target.value) || 1.8; save(); applyStyle(); });
    const range = $('fbt-size-range');
    if (range) range.addEventListener('input', (e) => setSize(parseInt(e.target.value, 10)));
    document.querySelectorAll('[data-fbt-theme]').forEach((b) =>
      b.addEventListener('click', () => { state.theme = b.getAttribute('data-fbt-theme'); save(); applyStyle(); })
    );
    applyStyle();
  });

  window.fbtOpen = open;
  window.fbtClose = close;
  window.fbtSizeUp = () => setSize(state.size + 1);
  window.fbtSizeDown = () => setSize(state.size - 1);
  window.fbtSizeReset = () => setSize(DEF_SIZE);
  window.fbtPrev = () => go(-1);
  window.fbtNext = () => go(1);
})();
