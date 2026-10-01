const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    const page = await browser.newPage({viewport: {width: 1440, height: 1000}});
    const results = [];
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    async function ready() { await page.waitForFunction(() => document.getElementById('productList').getAttribute('aria-busy') === 'false'); }
    async function choose(id) { await page.locator(`[data-pos-category="${id}"]`).click(); await ready(); }
    const names = () => page.locator('#productList strong').allTextContents();
    try {
        await page.goto(base + '/login');
        await page.locator('[name=locale]').selectOption('en');
        await page.locator('form[action$="/locale"] button').click();
        await page.waitForLoadState('networkidle');
        await page.locator('[name=email]').fill('kasir@warbun.local');
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
        await page.goto(base + '/pos');
        if (await page.locator('[name=opening_cash]').count()) {
            await page.locator('[name=opening_cash]').fill('10000');
            await page.locator('form[action$="/pos/open-shift"] button').click();
        }
        await page.waitForLoadState('networkidle'); await ready();
        const categories = await page.locator('[data-pos-category]').evaluateAll(buttons => buttons.map(button => ({id: button.dataset.posCategory, name: button.textContent})));
        const datasets = [];
        for (const category of categories.filter(category => category.id)) {
            const response = await page.request.get(base + '/pos/products?category_id=' + category.id);
            assert.equal(response.status(), 200);
            datasets.push({...category, products: await response.json()});
        }
        const available = datasets.filter(category => category.products.length);
        assert.ok(available.length >= 2);
        const first = available[0]; const other = available[1];
        await choose(first.id);
        assert.deepEqual(await names(), first.products.map(product => product.name));
        assert.equal(await page.locator('[data-pos-category][aria-pressed="true"]').count(), 1);
        await page.locator('#productSearch').fill(first.products[0].sku);
        await page.waitForFunction(() => document.querySelectorAll('#productList button').length === 1);
        await choose(other.id);
        assert.equal(await page.locator('#productList button').count(), 0);
        assert.equal(await page.locator('#productState').textContent(), 'No products found');
        await choose('');
        assert.deepEqual(await names(), [first.products[0].name]);
        await page.locator('#productList button').click();
        await choose(other.id);
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '1');
        results.push('Category and search combine; All retains search; empty feedback and cart survive category changes');
        let scanUrl;
        await page.route('**/pos/barcode?*', route => {
            scanUrl = route.request().url();
            return route.fulfill({json: {...first.products[0], barcode: 'category-qa'}});
        });
        await page.locator('#barcodeInput').fill('category-qa');
        await page.locator('#barcodeInput').press('Enter');
        await page.waitForFunction(() => document.querySelector('[data-pos-quantity]')?.textContent === '2');
        assert.equal(new URL(scanUrl).searchParams.has('category_id'), false);
        await page.unroute('**/pos/barcode?*');
        results.push('Mocked scan of product outside selected category adds quantity; barcode request has no category restriction');
        await page.locator('#productSearch').fill('');
        await choose(first.id);
        await page.route('**/pos/products?*', async route => {
            if (new URL(route.request().url()).searchParams.get('category_id') === first.id) await new Promise(resolve => setTimeout(resolve, 250));
            try { await route.continue(); }
            catch (error) { if (!error.message.includes('already handled')) throw error; }
        });
        await page.locator(`[data-pos-category="${first.id}"]`).click();
        await page.locator(`[data-pos-category="${other.id}"]`).click(); await ready();
        assert.deepEqual(await names(), other.products.map(product => product.name));
        await page.unrouteAll({behavior: 'wait'});
        results.push('Fast category changes discard stale lookup results');
        for (const width of [320,375,768,1024,1440]) {
            await page.setViewportSize({width, height: 1000});
            await page.evaluate(() => new Promise(resolve => { window.scrollTo(0,0); requestAnimationFrame(() => requestAnimationFrame(resolve)); }));
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
            const geometry = await page.locator('[data-pos-category]').evaluateAll(buttons => buttons.map(button => ({top: button.getBoundingClientRect().top, height: button.getBoundingClientRect().height})));
            assert.ok(geometry.every(button => Math.abs(button.top - geometry[0].top) < 1 && button.height >= 44));
            if (width <= 375) {
                await page.locator('.pos-categories').evaluate(element => {element.scrollLeft = 0;});
                await page.locator('.pos-categories').hover();
                await page.mouse.wheel(400,0);
                await page.waitForFunction(() => document.querySelector('.pos-categories').scrollLeft > 0);
            }
            if ([375,1440].includes(width)) await page.screenshot({path: `docs/qa/screenshots/pos-categories-${width}.png`, fullPage: true});
        }
        results.push('One row, horizontal scrolling and 44px targets at 320/375/768/1024/1440px with no document overflow');
        await page.locator('[data-pos-category=""]').focus();
        await page.keyboard.press('Enter'); await ready();
        assert.equal(await page.locator('[data-pos-category=""]').getAttribute('aria-pressed'), 'true');
        assert.deepEqual(errors, []);
        results.push('Keyboard activation, selected aria state and loading aria-busy work without runtime errors');
        fs.writeFileSync('docs/qa/pos-category-results.json', JSON.stringify({status: 'PASS', results, errors}, null, 2) + '\n');
        console.log(JSON.stringify({status: 'PASS', results, errors}, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
