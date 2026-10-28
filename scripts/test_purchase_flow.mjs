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

    console.log('3. Navigating to /shop...');
    await page.goto('https://kitobxon.elitehost.uz/shop', { waitUntil: 'networkidle2', timeout: 30000 });

    // Step A: Test purchasing Streak Muzlatgich (Item ID 8)
    console.log('4. Testing Streak Muzlatgich purchase modal...');
    await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click*="openPurchaseModal(8)"]');
        if (btn) btn.click();
    });

    await new Promise(r => setTimeout(r, 2000));
    const modal1Screenshot = path.join(artifactDir, 'admin_shop_step2_modal_streak_freeze.png');
    await page.screenshot({ path: modal1Screenshot });
    console.log('Modal 1 screenshot saved:', modal1Screenshot);

    // Click confirm purchase in modal
    console.log('5. Clicking confirm purchase in modal...');
    const confirmed1 = await page.evaluate(() => {
        const confirmBtn = document.querySelector('button[wire\\:click*="confirmPurchase"]');
        if (confirmBtn) {
            confirmBtn.click();
            return true;
        }
        return false;
    });
    console.log('Confirm button clicked:', confirmed1);

    await new Promise(r => setTimeout(r, 3000));
    const afterBuy1Screenshot = path.join(artifactDir, 'admin_shop_step3_after_buy_freeze.png');
    await page.screenshot({ path: afterBuy1Screenshot });
    console.log('After buy 1 screenshot saved:', afterBuy1Screenshot);

    // Step B: Test purchasing Tanga kuchaytirgich (Item ID 9)
    console.log('6. Testing Tanga kuchaytirgich purchase modal (Item 9)...');
    await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click*="openPurchaseModal(9)"]');
        if (btn) btn.click();
    });

    await new Promise(r => setTimeout(r, 2000));
    const modal2Screenshot = path.join(artifactDir, 'admin_shop_step4_modal_booster.png');
    await page.screenshot({ path: modal2Screenshot });
    console.log('Modal 2 screenshot saved:', modal2Screenshot);

    console.log('7. Confirming Tanga kuchaytirgich purchase...');
    await page.evaluate(() => {
        const confirmBtn = document.querySelector('button[wire\\:click*="confirmPurchase"]');
        if (confirmBtn) confirmBtn.click();
    });

    await new Promise(r => setTimeout(r, 3000));
    const afterBuy2Screenshot = path.join(artifactDir, 'admin_shop_step5_after_buy_booster.png');
    await page.screenshot({ path: afterBuy2Screenshot });
    console.log('After buy 2 screenshot saved:', afterBuy2Screenshot);

    // Step C: Test purchasing Viktorina qayta urinish chiptasi (Item ID 10)
    console.log('8. Testing Viktorina qayta urinish chiptasi purchase modal (Item 10)...');
    await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click*="openPurchaseModal(10)"]');
        if (btn) btn.click();
    });

    await new Promise(r => setTimeout(r, 2000));
    const modal3Screenshot = path.join(artifactDir, 'admin_shop_step6_modal_quiz_retry.png');
    await page.screenshot({ path: modal3Screenshot });
    console.log('Modal 3 screenshot saved:', modal3Screenshot);

    console.log('9. Confirming Viktorina qayta urinish purchase...');
    await page.evaluate(() => {
        const confirmBtn = document.querySelector('button[wire\\:click*="confirmPurchase"]');
        if (confirmBtn) confirmBtn.click();
    });

    await new Promise(r => setTimeout(r, 3000));
    const afterBuy3Screenshot = path.join(artifactDir, 'admin_shop_step7_after_buy_quiz_retry.png');
    await page.screenshot({ path: afterBuy3Screenshot });
    console.log('After buy 3 screenshot saved:', afterBuy3Screenshot);

    // Step D: Navigate to "Mening buyumlarim" (Inventory) tab
    console.log('10. Navigating to Inventory tab...');
    await page.evaluate(() => {
        const invBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Mening buyumlarim') || b.getAttribute('wire:click')?.includes('inventory'));
        if (invBtn) invBtn.click();
    });

    await new Promise(r => setTimeout(r, 2500));
    const inventoryScreenshot = path.join(artifactDir, 'admin_shop_step8_inventory.png');
    await page.screenshot({ path: inventoryScreenshot, fullPage: true });
    console.log('Inventory screenshot saved:', inventoryScreenshot);

    await browser.close();
    console.log('All browser test steps completed successfully!');
}

run().catch(e => {
    console.error('Test execution error:', e);
    process.exit(1);
});
