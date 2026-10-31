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

    // Login
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'networkidle2', timeout: 30000 });
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.click('button[type="submit"]')
    ]);

    // Go to topup page
    await page.goto('https://kitobxon.elitehost.uz/shop/topup', { waitUntil: 'networkidle2', timeout: 30000 });

    const btnInfo = await page.evaluate(() => {
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            outerHTML: b.outerHTML,
            innerText: b.innerText,
            textContent: b.textContent,
            clientWidth: b.clientWidth,
            clientHeight: b.clientHeight,
            classes: b.className,
            computedColor: window.getComputedStyle(b).color,
            computedBg: window.getComputedStyle(b).backgroundColor,
        }));
        return {
            url: window.location.href,
            buttons: btns
        };
    });

    console.log(JSON.stringify(btnInfo, null, 2));

    await page.screenshot({ path: path.join(artifactDir, 'topup_debug_screen.png'), fullPage: true });
    await browser.close();
}

run().catch(console.error);
