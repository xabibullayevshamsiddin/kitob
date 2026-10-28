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

    console.log('1. Navigating to login...');
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'networkidle2', timeout: 30000 });

    console.log('2. Entering admin credentials...');
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');

    console.log('3. Submitting login form...');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.click('button[type="submit"]')
    ]);

    console.log('Current URL after login:', page.url());
    const afterLoginScreenshot = path.join(artifactDir, 'browser_step2_logged_in.png');
    await page.screenshot({ path: afterLoginScreenshot });
    console.log('Logged in screenshot saved:', afterLoginScreenshot);

    console.log('4. Navigating to /shop...');
    await page.goto('https://kitobxon.elitehost.uz/shop', { waitUntil: 'networkidle2', timeout: 30000 });

    const shopScreenshot = path.join(artifactDir, 'browser_step3_shop_main.png');
    await page.screenshot({ path: shopScreenshot, fullPage: true });
    console.log('Shop main screenshot saved:', shopScreenshot);

    // Let's inspect page elements for the 3 items
    const itemsData = await page.evaluate(() => {
        const items = [];
        document.querySelectorAll('h3, h4, h5, p, span').forEach(el => {
            const text = el.innerText.trim();
            if (text.includes('Streak Muzlatgich') || text.includes('Tanga kuchaytirgich') || text.includes('Viktorina qayta')) {
                const card = el.closest('div[class*="rounded"], div[class*="p-"]');
                items.push({
                    title: text,
                    cardText: card ? card.innerText.replace(/\n+/g, ' | ') : ''
                });
            }
        });
        return { items };
    });
    console.log('Found shop items on page:', JSON.stringify(itemsData, null, 2));

    await browser.close();
    console.log('Completed successfully!');
}

run().catch(err => {
    console.error('Error during browser test:', err);
    process.exit(1);
});
