import puppeteer from 'puppeteer-core';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.goto('https://kitobxon.elitehost.uz/books', { waitUntil: 'domcontentloaded', timeout: 30000 });
    
    const books = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('a[href*="/books/"]')).map(a => ({
            text: a.innerText.trim(),
            href: a.href
        })).filter(b => b.text.length > 0 && !b.href.endsWith('/books') && !b.href.includes('/categories'));
    });

    console.log('Books list:', JSON.stringify(books, null, 2));
    await browser.close();
}

run();
