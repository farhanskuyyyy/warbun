const playwright = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const base = process.env.QA_BASE_URL;
assert(base && ['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Set a disposable local QA_BASE_URL');
assert(process.env.APP_ENV === 'testing' && (process.env.DB_DATABASE || '').includes('warbun-qa-'), 'Use the disposable browser QA environment');
const output = process.env.QA_OUTPUT || '/tmp/warbun-qa-final-results';
assert(output.includes('warbun-qa-'), 'Use a QA output directory');
fs.mkdirSync(output, { recursive: true });
const paths = ['/dashboard','/products','/products/create','/products/1','/products/1/edit','/categories','/categories/create','/categories/1/edit','/product-types','/product-types/create','/product-types/1/edit','/brands','/brands/create','/brands/1/edit','/units','/units/create','/units/1/edit','/suppliers','/suppliers/create','/suppliers/1/edit','/inventory','/stock-opnames','/customers','/customers/create','/customers/1','/customers/1/edit','/pos','/pos/history','/debt','/debt/create','/debt/dashboard','/orders','/orders/1','/orders/monitor','/payments','/payments/create','/payments/1','/refunds','/shifts','/reports','/reports/sales','/reports/inventory','/reports/payments','/reports/staff','/reports/debt','/users','/settings','/audit','/audit/1','/profile','/shelves','/','/shop','/cart'];
(async () => {
    const engine=process.env.QA_BROWSER||'chromium';
    assert(['chromium','webkit','firefox'].includes(engine));
    const launch={headless:true};
    if(process.env.QA_BROWSER_EXECUTABLE)launch.executablePath=process.env.QA_BROWSER_EXECUTABLE;else if(engine==='chromium')launch.channel='chrome';
    const browser = await playwright[engine].launch(launch);
    const context = await browser.newContext({ baseURL: base, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = [], results = [];
    page.on('pageerror', e => errors.push(e.message));
    const go = async url => { await page.waitForFunction(()=>!(history.state?.warbunOverlay && !document.querySelector('#store-overlay')?.open)); const r = await page.goto(url); assert.equal(r.status(), 200, url); await page.waitForLoadState('networkidle'); };
    const login = async email => {
        await context.clearCookies(); await go('/login');
        await page.locator('[name=email]').fill(email);
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard|my-orders|profile/);
    };
    try {
        await login('owner@warbun.local');
        for (const width of (process.env.QA_INTERACTIONS_ONLY ? [] : [390, 834, 1440])) {
            await page.setViewportSize({ width, height: 1000 });
            for (const theme of ['light', 'dark']) {
                await page.evaluate(t => localStorage.setItem('warbun_theme', t), theme);
                for (let i = 0; i < paths.length; i++) {
                    await go(paths[i]);
                    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), true, `${paths[i]} ${width} ${theme}`);
                    assert.equal(await page.locator('html').getAttribute('data-theme'), theme);
                    await page.screenshot({ path: path.join(output, `${width}-${theme}-${i}.png`), animations: 'disabled' });
                }
                results.push(`${paths.length} owner pages: ${width}px ${theme}`);
            }
        }
        await page.setViewportSize({ width: 390, height: 844 });
        await go('/profile');
        const deleteTrigger = page.locator('[data-open-named-modal]');
        await deleteTrigger.click();
        const confirmation = page.locator('[data-modal-name]');
        await assert.doesNotReject(() => confirmation.waitFor({ state: 'visible' }));
        await page.keyboard.press('Tab');
        assert(await confirmation.evaluate(e => e.contains(document.activeElement)));
        await page.keyboard.press('Escape');
        await confirmation.waitFor({ state: 'hidden' });
        assert(await deleteTrigger.evaluate(e => e === document.activeElement));
        results.push('Trusted pointer and keyboard: modal open, focus containment, Escape and focus return');

        await go('/shop');
        await page.evaluate(() => { localStorage.removeItem('warbun_cart'); window.dispatchEvent(new Event('warbun:cartchange')); });
        await page.locator('.catalog-product-image').first().click();
        const overlay = page.locator('#store-overlay');
        await overlay.waitFor({ state: 'visible' });
        await page.locator('#store-overlay footer button').click();
        assert(await page.locator('[data-dock-count]').textContent());
        await page.screenshot({path:path.join(output,'cart-sheet.png')});
        await page.locator('[data-store-close]').click();
        await page.locator('[data-open-cart]').click();
        await overlay.waitFor({state:'visible'});
        assert.equal(await overlay.evaluate(e => e.open), true);
        await page.keyboard.press('Escape');
        await page.waitForTimeout(100);
        await page.locator('[data-open-cart]').click();
        await page.locator('[data-checkout-nav]').click();
        await page.waitForURL('**/cart');
        results.push('Product picker → add → cart dock → cart sheet');

        await page.setViewportSize({ width: 1440, height: 1000 });
        await go('/shelves');
        await page.locator('[data-layout-edit]').click();
        const previousSavedY=Number(await page.locator('[data-layout-field=y]').inputValue());
        for(const [key,value] of [['width','2'],['height','2'],['x','0']]){await page.locator('[data-layout-field='+key+']').fill(value);await page.locator('[data-layout-field='+key+']').dispatchEvent('change');}
        await page.locator('[data-layout-field=y]').fill('0');
        await page.locator('[data-layout-field=y]').dispatchEvent('change');
        const first = page.locator('.floor-rack').first();
        const box = await first.boundingBox();
        const initialY = Number(await page.locator('[data-layout-field=y]').inputValue());
        await page.mouse.move(box.x + 10, box.y + 10);
        await page.mouse.down();
        await page.mouse.move(box.x + 10, box.y + (previousSavedY===4 ? 250 : 170), { steps: 10 });
        await page.mouse.up();
        await page.waitForTimeout(100);
        assert.notEqual(Number(await page.locator('[data-layout-field=y]').inputValue()), initialY, 'Pointer drag changes logical geometry');
        await page.locator('.floor-rack').first().focus();
        assert(await page.locator('.floor-rack').first().evaluate(e=>e===document.activeElement));
        await page.keyboard.press('ArrowRight');
        assert.equal(await page.locator('[data-layout-field=x]').inputValue(),'1');
        await page.keyboard.press('Shift+ArrowRight');
        assert.equal(await page.locator('[data-layout-field=width]').inputValue(),'3');
        await page.screenshot({path:path.join(output,'shelf-editor.png')});
        await page.locator('[data-layout-save]').click();
        await page.waitForFunction(() => document.querySelector('[data-layout-save]').disabled);
        await page.reload(); await page.waitForLoadState('networkidle');
        assert(await page.locator('.floor-rack').count());
        await page.locator('.floor-rack').first().click();
        await page.locator('#shelf-products-dialog').waitFor({ state: 'visible' });
        await page.waitForFunction(() => document.querySelector('[data-shelf-detail]').textContent.includes('IND-'));
        await page.keyboard.press('Escape');
        results.push('Shelf drag, save/reload and live product detail');

        await login('customer@warbun.local');
        await go('/cart');
        await page.waitForFunction(() => !document.querySelector('#checkoutBtn').disabled);
        await page.locator('#checkoutBtn').click();
        await page.waitForURL(/order-success\/\d+/);
        await go('/my-orders');
        await page.locator('[data-order-receipt]').first().click();
        await page.screenshot({path:path.join(output,'receipt-sheet.png')});
        assert(await page.locator('#store-overlay').evaluate(e => e.open));
        await page.keyboard.press('Escape');
        assert.equal((await page.request.get('/users')).status(),403);
        results.push('Customer checkout, saved receipt and CMS denial');
        await go('/shop');
        await page.locator('.catalog-product-image').first().click();
        await page.locator('#store-overlay footer button').click();
        await page.locator('[data-store-close]').click();
        await go('/cart');
        await page.route('**/cart/quote', route=>route.abort());
        await page.reload(); await page.waitForLoadState('networkidle');
        assert(await page.locator('#quote-retry').isVisible());
        assert(await page.locator('#checkoutBtn').isDisabled());
        await page.unroute('**/cart/quote'); await page.locator('#quote-retry').click();
        await page.waitForFunction(()=>!document.querySelector('#checkoutBtn').disabled);
        results.push('Quote network failure disables checkout; retry recovers');
        await login('owner@warbun.local');
        await page.setViewportSize({width:1440,height:1000});
        await go('/pos');
        if(await page.locator('[name=opening_cash]').count()){
            await page.locator('[name=opening_cash]').fill('10000');
            await page.locator('form[action$="/pos/open-shift"] button').click();
            await page.waitForLoadState('networkidle');
        }
        await page.locator('#productSearch').fill('Indomie Goreng');
        await page.waitForFunction(()=>document.querySelectorAll('#productList button').length===1);
        await page.locator('#productList button').first().click();
        await page.locator('#addProductBtn').click();
        await page.locator('[data-pos-checkout-open]').click();
        await page.locator('#paidAmount').fill('5000');
        await page.locator('#printReceipt').uncheck();
        await page.locator('#payBtn').click();
        await page.waitForURL(/pos\/receipt\/\d+/);
        assert((await page.locator('body').innerText()).includes('1.500'));
        results.push('POS product dialog, payment modal, sale and change receipt');
        await page.setViewportSize({width:390,height:844});
        await go('/orders/monitor');
        await page.locator('[data-monitor-select]').first().click();
        assert(await page.locator('#monitor-detail').evaluate(e=>e.open));
        await page.keyboard.press('Escape');
        results.push('Mobile monitor detail overlay');
        assert.deepEqual(errors, []);
        fs.writeFileSync(path.join(output,'results.json'), JSON.stringify({ status:'PASS', results, errors }, null,2));
        console.log(JSON.stringify({ status:'PASS', results }));
    } catch(error) {
        await page.screenshot({ path:path.join(output,'failure.png') }).catch(()=>{});
        fs.writeFileSync(path.join(output,'results.json'),JSON.stringify({status:'FAIL',results,errors,error:error.message,url:page.url()},null,2));
        throw error;
    } finally { await browser.close(); }
})().catch(error => { console.error(error.message); process.exitCode=1; });
