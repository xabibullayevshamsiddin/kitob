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

    console.log('3. Navigating to /shop?tab=chest...');
    await page.goto('https://kitobxon.elitehost.uz/shop?tab=chest', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Screenshot before opening
    const beforeScreen = path.join(artifactDir, 'test_fixes_before_open.png');
    await page.screenshot({ path: beforeScreen });
    console.log('Saved before screenshot:', beforeScreen);

    // Get initial header coins & points text
    const initialHeader = await page.evaluate(() => {
        const headerPill = document.querySelector('.header-stats-pill');
        return headerPill ? headerPill.innerText : '';
    });
    console.log('Initial Header Stats:', initialHeader);

    console.log('4. Clicking open mystery chest...');
    const clicked = await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click="openMysteryChest"]');
        if (btn) {
            btn.click();
            return true;
        }
        return false;
    });
    console.log('Button clicked:', clicked);

    // Wait for modal & live balance update
    await new Promise(r => setTimeout(r, 3500));

    const afterScreen = path.join(artifactDir, 'test_fixes_after_open.png');
    await page.screenshot({ path: afterScreen });
    console.log('Saved after screenshot:', afterScreen);

    const updatedHeader = await page.evaluate(() => {
        const headerPill = document.querySelector('.header-stats-pill');
        return headerPill ? headerPill.innerText : '';
    });
    console.log('Updated Header Stats:', updatedHeader);

    // Close the result modal
    await page.evaluate(() => {
        const closeBtn = document.querySelector('button[wire\\:click="closeChestResult"]');
        if (closeBtn) closeBtn.click();
    });

    await new Promise(r => setTimeout(r, 2000));
    const closedModalScreen = path.join(artifactDir, 'test_fixes_closed_modal.png');
    await page.screenshot({ path: closedModalScreen });
    console.log('Saved closed modal screenshot:', closedModalScreen);

    await browser.close();
    console.log('Finished testing successfully!');
}

run().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
