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

    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc', { waitUntil: 'networkidle2', timeout: 30000 });

    const pageDetails = await page.evaluate(() => {
        const buttonsAndLinks = Array.from(document.querySelectorAll('a, button')).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            href: el.href || null,
            onclick: el.getAttribute('onclick') || null
        })).filter(x => x.text.length > 0 && x.text.length < 50);

        return {
            title: document.title,
            buttonsAndLinks
        };
    });

    console.log('Page details:', JSON.stringify(pageDetails, null, 2));
    await browser.close();
}

run();
