import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/2a693fb1-f0d0-42db-8ca0-069a78dbccc3';

async function run() {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

    page.on('console', msg => console.log('BROWSER_LOG:', msg.text()));

    console.log('1. Logging in...');
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    console.log('2. Navigating to flipbook...');
    const flipbookUrl = 'https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc/flipbook';
    await page.goto(flipbookUrl, { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 4000));

    await page.screenshot({ path: path.join(artifactDir, 'flipbook_initial.png') });

    // Inspect Alpine tracker state
    const trackerInfo = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        if (!trackerEl) return { exists: false };
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        return {
            exists: true,
            data: data ? {
                bookId: data.bookId,
                pageType: data.pageType,
                idleSeconds: data.idleSeconds,
                sessionSeconds: data.sessionSeconds,
                isPaused: data.isPaused,
                isAfk: data.isAfk,
                showAfkModal: data.showAfkModal,
                afkLimitSeconds: data.afkLimitSeconds
            } : null
        };
    });
    console.log('Flipbook tracker info:', JSON.stringify(trackerInfo, null, 2));

    // Monitor events and idleSeconds in Flipbook for 15 seconds
    console.log('3. Monitoring idleSeconds & events in Flipbook for 15 seconds...');
    await page.evaluate(() => {
        window.__eventsFired = [];
        const trackEvents = [
            'mousemove', 'mousedown', 'mouseup', 'click',
            'keydown', 'keyup',
            'touchstart', 'touchmove', 'touchend',
            'pointerdown', 'pointermove', 'pointerup',
            'scroll', 'wheel',
            'reading-activity', 'flipbook:pagechange', 'reader:scroll', 'reader:pagechange'
        ];
        trackEvents.forEach(evt => {
            window.addEventListener(evt, (e) => {
                window.__eventsFired.push({
                    type: e.type,
                    target: e.target?.tagName + (e.target?.id ? '#' + e.target.id : '') + (e.target?.className ? '.' + String(e.target.className).slice(0, 20) : ''),
                    time: Date.now()
                });
            }, { passive: true });
        });
    });

    for (let i = 1; i <= 6; i++) {
        await new Promise(r => setTimeout(r, 2500));
        const status = await page.evaluate(() => {
            const trackerEl = document.querySelector('#reading-tracker-root');
            const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
            return {
                idleSeconds: data?.idleSeconds,
                sessionSeconds: data?.sessionSeconds,
                isPaused: data?.isPaused,
                isAfk: data?.isAfk,
                eventsCount: window.__eventsFired?.length,
                recentEvents: window.__eventsFired?.slice(-5)
            };
        });
        console.log(`Flipbook status at ${(i * 2.5)}s:`, JSON.stringify(status));
    }

    // Now test what happens when idle reaches 300!
    console.log('4. Simulating idleSeconds reaching 300...');
    await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        if (data) {
            data.idleSeconds = 299;
        }
    });

    console.log('Waiting 3 seconds for ticker to hit 300 and trigger modal...');
    await new Promise(r => setTimeout(r, 3000));

    const afkStatus = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        const modalEl = document.querySelector('.reading-tracker-modal');
        const modalComputed = modalEl ? window.getComputedStyle(modalEl) : null;
        return {
            idleSeconds: data?.idleSeconds,
            isPaused: data?.isPaused,
            isAfk: data?.isAfk,
            showAfkModal: data?.showAfkModal,
            modalInDom: !!modalEl,
            modalDisplay: modalComputed ? modalComputed.display : null,
            modalVisibility: modalComputed ? modalComputed.visibility : null,
            modalOpacity: modalComputed ? modalComputed.opacity : null,
            modalZIndex: modalComputed ? modalComputed.zIndex : null,
            modalOffsetHeight: modalEl?.offsetHeight,
            modalOffsetWidth: modalEl?.offsetWidth
        };
    });

    console.log('AFK status in Flipbook after 300s simulation:', JSON.stringify(afkStatus, null, 2));

    await page.screenshot({ path: path.join(artifactDir, 'flipbook_afk_modal.png') });

    // Also test: in Fullscreen mode!
    console.log('5. Testing in mobile fullscreen or full screen mode...');
    const hasFsButton = await page.evaluate(() => {
        const btn = document.querySelector('#fb-btn-fullscreen, .fb-fullscreen-btn, [onclick*="fbToggleFullscreen"]');
        return !!btn;
    });
    console.log('Fullscreen button exists:', hasFsButton);

    await browser.close();
    console.log('Completed flipbook diagnostic!');
}

run().catch(err => {
    console.error('Flipbook diagnostic error:', err);
    process.exit(1);
});
