import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
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

    console.log('Navigating to /reader/2...');
    await page.goto('https://kitobxon.elitehost.uz/reader/2', { waitUntil: 'networkidle2', timeout: 30000 });
    console.log('Current URL after /reader/2:', page.url());

    const result = await page.evaluate(() => {
        const trackerEl = document.querySelector('#reading-tracker-root');
        return {
            title: document.title,
            hasTracker: !!trackerEl,
            hasFlipbook: !!document.querySelector('#fb-root'),
            hasReaderContent: !!document.querySelector('.reader-content'),
            hasIframe: !!document.querySelector('iframe')
        };
    });

    console.log('Result on reader/2:', JSON.stringify(result, null, 2));
    await browser.close();
}

run();
