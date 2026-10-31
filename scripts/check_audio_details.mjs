import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc', { waitUntil: 'domcontentloaded', timeout: 30000 });

    const audioDetails = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('audio')).map(a => ({
            id: a.id,
            className: a.className,
            src: a.src,
            outerHTML: a.outerHTML,
            parentHTML: a.parentElement ? a.parentElement.outerHTML.substring(0, 200) : null
        }));
    });

    console.log('Audio details:', JSON.stringify(audioDetails, null, 2));
    await browser.close();
}

run();
