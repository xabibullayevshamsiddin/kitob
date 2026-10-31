import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: false, // run with real Chrome window
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1200,800']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1200, height: 800 });

    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Move mouse to bottom right near the timer widget
    await page.mouse.move(1100, 750);

    // Track all events
    await page.evaluate(() => {
        window.__eventsLogged = [];
        ['mousemove', 'pointermove', 'scroll'].forEach(evt => {
            window.addEventListener(evt, e => {
                window.__eventsLogged.push({
                    type: e.type,
                    x: e.clientX,
                    y: e.clientY,
                    movementX: e.movementX,
                    movementY: e.movementY,
                    target: e.target?.tagName + '.' + (e.target?.className || '')
                });
            }, { passive: true });
        });
    });

    console.log('Testing 15 seconds with mouse stationary at (1100, 750)...');
    for (let i = 1; i <= 5; i++) {
        await new Promise(r => setTimeout(r, 3000));
        const status = await page.evaluate(() => {
            const trackerEl = document.querySelector('#reading-tracker-root');
            const data = window.Alpine ? window.Alpine.$data(trackerEl) : null;
            return {
                idleSeconds: data?.idleSeconds,
                sessionSeconds: data?.sessionSeconds,
                eventsCount: window.__eventsLogged.length,
                events: window.__eventsLogged.splice(0)
            };
        });
        console.log(`Status ${i * 3}s:`, JSON.stringify(status));
    }

    await browser.close();
}

run().catch(console.error);
