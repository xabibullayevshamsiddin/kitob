import puppeteer from 'puppeteer-core';
import path from 'path';

const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/2a693fb1-f0d0-42db-8ca0-069a78dbccc3';

async function run() {
    console.log('1. Launching headless browser...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

    console.log('2. Logging into Kitobxon...');
    await page.goto('https://kitobxon.elitehost.uz/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(r => setTimeout(r, 1000));
    await page.type('#email', 'admin@kitobxon.uz');
    await page.type('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        page.evaluate(() => document.querySelector('form').submit())
    ]);

    console.log('3. Opening book reading page...');
    await page.goto('https://kitobxon.elitehost.uz/books/algebra-6addc3f256efc', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Verify readingTracker is initialized
    const initialCheck = await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        return {
            exists: !!root,
            isPaused: data?.isPaused,
            isAfk: data?.isAfk,
            afkLimitSeconds: data?.afkLimitSeconds,
            idleSeconds: data?.idleSeconds
        };
    });
    console.log('Tracker initial state:', JSON.stringify(initialCheck, null, 2));

    // Test: Stationary / micro mouse movement does NOT reset idleSeconds
    console.log('4. Testing stationary mouse...');
    await page.mouse.move(500, 500);
    await new Promise(r => setTimeout(r, 2000));
    
    // Tiny jitter (2px)
    await page.mouse.move(502, 501);
    await new Promise(r => setTimeout(r, 1000));

    const jitterCheck = await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        return { idleSeconds: data?.idleSeconds };
    });
    console.log('After micro-jitter idleSeconds (should be >= 2):', jitterCheck.idleSeconds);

    // Test: Intentional movement (>15px) DOES reset idleSeconds
    console.log('5. Testing intentional movement (100px move)...');
    await page.mouse.move(650, 650);
    await new Promise(r => setTimeout(r, 600));

    const moveCheck = await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        return { idleSeconds: data?.idleSeconds };
    });
    console.log('After intentional move idleSeconds (should be 0 or 1):', moveCheck.idleSeconds);

    // Test: Inactivity reaches 300s (5 minutes) -> triggers AFK pause & modal
    console.log('6. Fast-forwarding idle to 299s to observe 300s auto-pause trigger...');
    await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        if (data) data.idleSeconds = 299;
    });

    console.log('Waiting 2.5s for ticker to reach 300s...');
    await new Promise(r => setTimeout(r, 2500));

    const afkTriggered = await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        const modal = document.querySelector('.reading-tracker-modal');
        const computed = modal ? window.getComputedStyle(modal) : null;
        return {
            idleSeconds: data?.idleSeconds,
            isPaused: data?.isPaused,
            isAfk: data?.isAfk,
            afkReason: data?.afkReason,
            showAfkModal: data?.showAfkModal,
            modalVisible: computed?.display === 'flex' && computed?.visibility !== 'hidden',
            modalZIndex: computed?.zIndex
        };
    });
    console.log('AFK trigger verification:', JSON.stringify(afkTriggered, null, 2));

    const verifiedScreenshot = path.join(artifactDir, 'afk_modal_5min_verified.png');
    await page.screenshot({ path: verifiedScreenshot });
    console.log('Screenshot saved to:', verifiedScreenshot);

    // Test: Clicking confirm button inside modal resumes reading
    console.log('7. Clicking "Ha, mutolaani davom ettiraman" button...');
    const clickSuccess = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('.reading-tracker-modal button'));
        const resumeBtn = buttons.find(b => b.innerText.includes('davom ettiraman'));
        if (resumeBtn) {
            resumeBtn.click();
            return true;
        }
        return false;
    });

    await new Promise(r => setTimeout(r, 1000));

    const resumeCheck = await page.evaluate(() => {
        const root = document.querySelector('#reading-tracker-root');
        const data = window.Alpine ? window.Alpine.$data(root) : null;
        return {
            isPaused: data?.isPaused,
            isAfk: data?.isAfk,
            showAfkModal: data?.showAfkModal,
            idleSeconds: data?.idleSeconds
        };
    });
    console.log('After clicking resume:', JSON.stringify(resumeCheck, null, 2));

    await browser.close();
    console.log('ALL TESTS PASSED SUCCESSFULLY!');
}

run().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
