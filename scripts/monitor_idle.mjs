import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
    console.log('Testing 70 seconds of idle reading...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 3000));

    // Listen to any events that match the tracker's events
    await page.evaluate(() => {
        window.__loggedEvents = [];
        const events = [
            'mousemove', 'mousedown', 'mouseup', 'click',
            'keydown', 'keyup',
            'touchstart', 'touchmove', 'touchend',
            'pointerdown', 'pointermove', 'pointerup',
            'scroll', 'wheel',
            'reading-activity', 'flipbook:pagechange', 'reader:scroll', 'reader:pagechange'
        ];
        events.forEach(evt => {
            window.addEventListener(evt, (e) => {
                window.__loggedEvents.push({ type: e.type, time: Date.now() });
            }, { passive: true });
        });
    });

    for (let sec = 5; sec <= 70; sec += 5) {
        await new Promise(r => setTimeout(r, 5000));
        const data = await page.evaluate(() => {
            const trackerEl = document.querySelector('#reading-tracker-root');
            const alpine = window.Alpine ? window.Alpine.$data(trackerEl) : null;
            return {
                idleSeconds: alpine?.idleSeconds,
                sessionSeconds: alpine?.sessionSeconds,
                unsentSeconds: alpine?.unsentSeconds,
                isPaused: alpine?.isPaused,
                eventsFiredSinceLast: window.__loggedEvents.splice(0)
            };
        });
        console.log(`[T=${sec}s]:`, JSON.stringify(data));
    }

    await browser.close();
    console.log('Finished 70s run.');
}

run().catch(e => {
    console.error(e);
    process.exit(1);
});
