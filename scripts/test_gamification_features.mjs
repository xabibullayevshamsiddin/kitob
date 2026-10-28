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

    // Test Feature 4: Omad Sandig'i
    console.log('3. Navigating to /shop?tab=chest...');
    await page.goto('https://kitobxon.elitehost.uz/shop?tab=chest', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const chestScreen = path.join(artifactDir, 'test_feature4_chest_tab.png');
    await page.screenshot({ path: chestScreen });
    console.log('Saved chest tab screenshot:', chestScreen);

    console.log('4. Clicking open mystery chest...');
    const chestOpened = await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click="openMysteryChest"]');
        if (btn) {
            btn.click();
            return true;
        }
        return false;
    });
    console.log('Open chest button clicked:', chestOpened);

    await new Promise(r => setTimeout(r, 4000));
    const chestResultScreen = path.join(artifactDir, 'test_feature4_chest_reward_modal.png');
    await page.screenshot({ path: chestResultScreen });
    console.log('Saved chest result modal screenshot:', chestResultScreen);

    // Test Feature 1: Profile Level Badge & Progress Bar
    console.log('5. Navigating to /profile...');
    await page.goto('https://kitobxon.elitehost.uz/profile', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const profileScreen = path.join(artifactDir, 'test_feature1_profile_level.png');
    await page.screenshot({ path: profileScreen });
    console.log('Saved profile level screenshot:', profileScreen);

    // Test Feature 5: Group creation point requirement
    console.log('6. Navigating to /groups...');
    await page.goto('https://kitobxon.elitehost.uz/groups', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click="openCreateModal"]');
        if (btn) btn.click();
    });

    await new Promise(r => setTimeout(r, 2000));
    const groupModalScreen = path.join(artifactDir, 'test_feature5_group_modal.png');
    await page.screenshot({ path: groupModalScreen });
    console.log('Saved group modal screenshot:', groupModalScreen);

    await browser.close();
    console.log('All tests completed successfully!');
}

run().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
