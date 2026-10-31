import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,900']
    });

    const page = await browser.newPage();
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc/flipbook', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 4000));

    await page.evaluate(() => {
        window.fbToggleFullscreen();
        const trackerEl = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
        if (data) data.triggerFocusPause('idle');
    });
    await new Promise(r => setTimeout(r, 1000));

    const cssRules = await page.evaluate(() => {
        const modalEl = document.querySelector('.reading-tracker-modal');
        if (!modalEl) return null;

        // Check matched CSS rules
        const matched = [];
        for (const sheet of document.styleSheets) {
            try {
                for (const rule of sheet.cssRules || []) {
                    if (rule.selectorText && modalEl.matches(rule.selectorText)) {
                        matched.push({
                            selector: rule.selectorText,
                            cssText: rule.cssText
                        });
                    }
                }
            } catch (e) {}
        }
        return {
            inlineStyle: modalEl.getAttribute('style'),
            computedDisplay: window.getComputedStyle(modalEl).display,
            matchedRules: matched
        };
    });

    console.log('CSS Debug:', JSON.stringify(cssRules, null, 2));
    await browser.close();
}

run();
