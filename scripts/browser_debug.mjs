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

    console.log('Navigating to login...');
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded' });

    await page.screenshot({ path: path.join(artifactDir, 'debug_1_initial.png') });

    console.log('Typing email and password...');
    // Focus and type into #email
    await page.click('#email');
    await page.keyboard.type('admin@kitobxon.uz');

    await page.click('#password');
    await page.keyboard.type('password');

    await page.screenshot({ path: path.join(artifactDir, 'debug_2_typed.png') });

    const emailValue = await page.$eval('#email', el => el.value);
    console.log('Current value in #email:', emailValue);

    console.log('Clicking submit...');
    await page.click('button[type="submit"]');

    console.log('Waiting 4 seconds...');
    await new Promise(r => setTimeout(r, 4000));

    console.log('Current URL:', page.url());
    await page.screenshot({ path: path.join(artifactDir, 'debug_3_after_click.png') });

    const pageInfo = await page.evaluate(() => ({
        url: window.location.href,
        text: document.body.innerText.substring(0, 500),
        errors: Array.from(document.querySelectorAll('.text-rose-400, .text-rose-300, .text-red-500, alert')).map(e => e.innerText)
    }));
    console.log('Page info:', JSON.stringify(pageInfo, null, 2));

    await browser.close();
}

run().catch(e => {
    console.error('Error:', e);
    process.exit(1);
});
