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

    const audios = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('audio')).map(a => ({
            id: a.id,
            className: a.className,
            src: a.src,
            paused: a.paused,
            ended: a.ended,
            closestAmbient: !!a.closest('.ambient-music-scope')
        }));
    });

    console.log('Audios on book page:', audios);
    await browser.close();
}

run();
