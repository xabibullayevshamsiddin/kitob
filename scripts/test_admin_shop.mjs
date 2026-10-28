import puppeteer from 'puppeteer-core';
import path from 'path';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/2a693fb1-f0d0-42db-8ca0-069a78dbccc3';

async function run() {
    console.log('1. Launching browser...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,1000']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 1000 });

    console.log('2. Logging in as admin@kitobxon.uz...');
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded' });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    console.log('Logged in! Current URL:', page.url());

    console.log('3. Navigating to /shop...');
    await page.goto('https://kitobxon.elitehost.uz/shop', { waitUntil: 'networkidle2', timeout: 30000 });
    console.log('Shop loaded! Current URL:', page.url());

    // Take screenshot of the shop page
    const shopPageScreenshot = path.join(artifactDir, 'admin_shop_step1_overview.png');
    await page.screenshot({ path: shopPageScreenshot, fullPage: true });
    console.log('Overview screenshot saved:', shopPageScreenshot);

    // Let's inspect the balance and the 3 items on the page
    const shopInfo = await page.evaluate(() => {
        // Look for the items: Streak Muzlatgich, Tanga kuchaytirgich, Viktorina qayta urinish chiptasi
        const items = [];
        document.querySelectorAll('h3, h4, h5, div, span').forEach(el => {
            const txt = (el.innerText || '').trim();
            if (txt === 'Streak Muzlatgich' || txt.includes('Tanga kuchaytirgich') || txt.includes('Viktorina qayta urinish')) {
                // Find parent container with button
                let container = el.parentElement;
                while (container && !container.querySelector('button') && container.parentElement) {
                    container = container.parentElement;
                }
                const btn = container ? container.querySelector('button') : null;
                items.push({
                    name: txt,
                    hasButton: !!btn,
                    buttonText: btn ? btn.innerText.trim() : null,
                    buttonWireClick: btn ? btn.getAttribute('wire:click') : null,
                    textSnippet: container ? container.innerText.replace(/\n+/g, ' | ').substring(0, 200) : ''
                });
            }
        });

        // Find user balance element
        const coinBalanceElem = document.body.innerText.match(/(\d+)\s*(tanga|tangalar|coin)/i);

        return {
            title: document.title,
            matchedItems: items,
            detectedCoinSnippet: coinBalanceElem ? coinBalanceElem[0] : null
        };
    });

    console.log('Shop Info:', JSON.stringify(shopInfo, null, 2));

    await browser.close();
}

run().catch(e => {
    console.error('Test error:', e);
    process.exit(1);
});
