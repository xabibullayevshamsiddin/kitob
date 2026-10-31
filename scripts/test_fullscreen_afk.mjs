import puppeteer from 'puppeteer-core';
import path from 'path';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/2a693fb1-f0d0-42db-8ca0-069a78dbccc3';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

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

    console.log('3. Triggering Fullscreen mode (fbToggleFullscreen)...');
    await page.evaluate(() => {
        window.fbToggleFullscreen();
    });
    await new Promise(r => setTimeout(r, 1000));

    console.log('4. Now triggering AFK modal while in fullscreen...');
    const modalVisibleInFullscreen = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        if (data) {
            data.triggerFocusPause('idle');
        }
        const modalEl = document.querySelector('.reading-tracker-modal');
        const computed = modalEl ? window.getComputedStyle(modalEl) : null;
        return {
            isBodyMfs: document.body.classList.contains('fb-mobile-fullscreen-active'),
            modalExists: !!modalEl,
            modalDisplay: computed?.display,
            modalVisibility: computed?.visibility,
            modalZIndex: computed?.zIndex,
            parentDisplay: modalEl?.parentElement ? window.getComputedStyle(modalEl.parentElement).display : null
        };
    });
    console.log('Modal in Fullscreen check:', JSON.stringify(modalVisibleInFullscreen, null, 2));

    await page.screenshot({ path: path.join(artifactDir, 'flipbook_fullscreen_afk_modal.png') });

    await browser.close();
    console.log('Done test!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
