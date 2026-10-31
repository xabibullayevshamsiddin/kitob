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

    console.log('2. Finding a book...');
    await page.goto('https://kitobxon.elitehost.uz/books', { waitUntil: 'networkidle2', timeout: 30000 });
    
    const bookUrl = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a[href*="/books/"]'));
        const bookLink = links.find(a => !a.href.includes('/categories') && !a.href.endsWith('/books'));
        return bookLink ? bookLink.href : null;
    });

    console.log('Selected book URL:', bookUrl);
    if (!bookUrl) {
        throw new Error('No book found!');
    }

    console.log('3. Navigating to book page...');
    await page.goto(bookUrl, { waitUntil: 'networkidle2', timeout: 30000 });
    await page.screenshot({ path: path.join(artifactDir, 'diagnose_book_page.png') });

    // Check reading buttons (read online, flipbook, etc.)
    const readUrls = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a'));
        return links
            .filter(a => a.href.includes('/read') || a.href.includes('/flipbook') || a.href.includes('/chapter'))
            .map(a => ({ text: a.innerText.trim(), href: a.href }));
    });
    console.log('Read links available:', readUrls);

    let targetReaderUrl = readUrls.length > 0 ? readUrls[0].href : null;
    if (!targetReaderUrl) {
        // Maybe the book page itself has reading tracker?
        targetReaderUrl = bookUrl;
    }

    console.log('4. Navigating to reader:', targetReaderUrl);
    await page.goto(targetReaderUrl, { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 3000));
    await page.screenshot({ path: path.join(artifactDir, 'diagnose_reader_page.png') });

    // Check Alpine readingTracker state
    const trackerInfo = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        if (!trackerEl) {
            return { exists: false, html: document.body.innerHTML.substring(0, 500) };
        }
        let alpineData = null;
        if (window.Alpine) {
            alpineData = window.Alpine.$data(trackerEl);
        }
        return {
            exists: true,
            hasAlpine: !!window.Alpine,
            data: alpineData ? {
                bookId: alpineData.bookId,
                pageType: alpineData.pageType,
                idleSeconds: alpineData.idleSeconds,
                sessionSeconds: alpineData.sessionSeconds,
                isPaused: alpineData.isPaused,
                isAfk: alpineData.isAfk,
                showAfkModal: alpineData.showAfkModal,
                afkLimitSeconds: alpineData.afkLimitSeconds
            } : null
        };
    });

    console.log('Tracker info initial:', JSON.stringify(trackerInfo, null, 2));

    // Monitor for 10 seconds to see if idleSeconds increments or gets reset
    console.log('5. Monitoring tracker idleSeconds for 10 seconds without any user interaction...');
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
                window.__eventsFired.push({ type: e.type, target: e.target?.tagName, time: Date.now() });
            }, { passive: true });
        });
    });

    for (let i = 1; i <= 5; i++) {
        await new Promise(r => setTimeout(r, 2000));
        const status = await page.evaluate(() => {
            const trackerEl = document.querySelector('#reading-tracker-root');
            const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
            return {
                idleSeconds: data?.idleSeconds,
                sessionSeconds: data?.sessionSeconds,
                isPaused: data?.isPaused,
                eventsCount: window.__eventsFired?.length,
                recentEvents: window.__eventsFired?.slice(-5)
            };
        });
        console.log(`Status at ${(i * 2)}s:`, JSON.stringify(status));
    }

    // Now test what happens when idleSeconds reaches 300!
    console.log('6. Simulating idleSeconds reaching 300...');
    const modalTriggered = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        if (!data) return false;
        data.idleSeconds = 299;
        return true;
    });

    console.log('Waiting 2 seconds for ticker to hit 300...');
    await new Promise(r => setTimeout(r, 2000));

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

    console.log('AFK status after 300s simulation:', JSON.stringify(afkStatus, null, 2));

    await page.screenshot({ path: path.join(artifactDir, 'diagnose_afk_modal.png') });

    await browser.close();
    console.log('Diagnostic finished!');
}

run().catch(err => {
    console.error('Diagnostic error:', err);
    process.exit(1);
});
