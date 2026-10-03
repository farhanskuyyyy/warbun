const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    const context = await browser.newContext({viewport: {width: 1440, height: 1000}});
    await context.addInitScript(() => { window.print = () => { window.__printCalls = (window.__printCalls || 0) + 1; }; });
    const page = await context.newPage();
    const errors = [];
    const results = [];
    page.on('pageerror', error => errors.push(error.message));
    async function go(path) {
        assert.ok((await page.goto(base + path)).status() < 400);
        await page.waitForLoadState('networkidle');
    }
    async function login(email) {
        await go('/login');
        await page.locator('[name=locale]').selectOption('en');
        await page.locator('form[action$="/locale"] button').click();
        await page.waitForLoadState('networkidle');
        await page.locator('[name=email]').fill(email);
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
    }
    const barcode = '0012345678905';
    async function scan(code = barcode) {
        await page.locator('#barcodeInput').fill(code);
        await page.locator('#barcodeInput').press('Enter');
    }
    async function quantity(expected) {
        await page.waitForFunction(value => document.querySelector('[data-pos-quantity]')?.textContent === String(value), expected);
        await page.waitForFunction(() => !document.getElementById('payBtn').disabled);
    }
    try {
        await login('owner@warbun.local');
        await go('/products/1/edit');
        await page.locator('#field-barcode').fill(barcode);
        await page.locator('#field-barcode').press('Enter');
        assert.ok(page.url().endsWith('/products/1/edit'));
        await page.getByRole('button', {name: 'Update Product', exact: true}).click();
        await page.waitForURL(/products$/);
        await go('/products/1');
        assert.ok((await page.locator('main').innerText()).includes(barcode));
        results.push('Product barcode saves with leading zeroes; scanner Enter does not submit the product form');
        await context.clearCookies();
        await login('kasir@warbun.local');
        await go('/pos');
        assert.ok(await page.locator('#barcodeInput').isDisabled());
        await page.locator('[name=opening_cash]').fill('10000');
        await page.locator('form[action$="/pos/open-shift"] button').click();
        await page.waitForLoadState('networkidle');
        await scan(); await quantity(1);
        await scan(); await quantity(2);
        assert.equal(await page.locator('#barcodeInput').inputValue(), '');
        assert.ok(await page.locator('#barcodeInput').evaluate(element => element === document.activeElement));
        await scan(barcode.slice(1));
        await page.waitForFunction(() => document.getElementById('scanState').classList.contains('is-error'));
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '2');
        results.push('Shift gate, exact scan, repeated quantity, clear/refocus and unknown-barcode feedback');
        await page.route('**/pos/barcode?*', async route => {
            await new Promise(resolve => setTimeout(resolve, 150));
            await route.continue();
        });
        await scan();
        assert.ok(await page.locator('#payBtn').isDisabled());
        await scan(); await scan(); await quantity(5);
        await page.unroute('**/pos/barcode?*');
        results.push('Rapid scans queue without lost quantities; payment waits for pending lookups');
        const product = await (await page.request.get(base + '/pos/barcode?barcode=' + barcode)).json();
        await page.route('**/pos/barcode?*', route => route.fulfill({json: {...product, current_stock: 5}}));
        await scan();
        await page.waitForFunction(() => document.getElementById('scanState').classList.contains('is-error'));
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '5');
        await page.unroute('**/pos/barcode?*');
        await page.route('**/pos/barcode?*', route => route.abort());
        await scan();
        await page.waitForFunction(() => document.getElementById('scanState').textContent.includes('Try scanning again'));
        await page.unroute('**/pos/barcode?*');
        await page.locator('#barcodeInput').fill(barcode);
        await page.locator('#scanBtn').click(); await quantity(6);
        await page.getByRole('button', {name: 'Increase quantity Indomie Goreng', exact: true}).click(); await quantity(7);
        await page.getByRole('button', {name: 'Decrease quantity Indomie Goreng', exact: true}).click(); await quantity(6);
        await page.locator('#productSearch').fill('Indomie Goreng');
        await page.waitForFunction(() => document.querySelectorAll('#productList button').length === 1);
        await page.locator('#productList').getByRole('button', {name: /Indomie Goreng/}).click();
        await page.locator('#addProductBtn').click();
        await quantity(7);
        results.push('Stock limit and network failure preserve cart; next scan and manual search recover');
        for (const width of [320,375,768,1440]) {
            await page.setViewportSize({width, height: 1000});
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
        }
        await page.setViewportSize({width: 375, height: 900});
        await page.evaluate(() => new Promise(resolve => { window.scrollTo(0, 0); requestAnimationFrame(() => requestAnimationFrame(resolve)); }));
        await page.screenshot({path: 'docs/qa/screenshots/barcode-pos-mobile.png', fullPage: true});
        await page.setViewportSize({width: 1440, height: 1000});
        await page.evaluate(() => new Promise(resolve => { window.scrollTo(0, 0); requestAnimationFrame(() => requestAnimationFrame(resolve)); }));
        await page.screenshot({path: 'docs/qa/screenshots/barcode-pos-desktop.png', fullPage: true});
        await page.locator('#paidAmount').fill('1');
        await page.locator('#payBtn').click();
        await page.waitForFunction(() => document.getElementById('saleError').textContent.length > 0);
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '7');
        await page.locator('#paidAmount').fill('100000');
        await page.locator('#receiptPaper').selectOption('58');
        const retryKeys = [];
        let failFirst = true;
        await page.route('**/pos/process-sale', route => {
            retryKeys.push(route.request().postDataJSON().request_key);
            if (failFirst) { failFirst = false; return route.abort(); }
            return route.continue();
        });
        await page.locator('#payBtn').click();
        await page.waitForFunction(() => document.getElementById('saleError').textContent.includes('Retry with the same cart'));
        await page.locator('#payBtn').click();
        await page.waitForURL(/pos\/receipt\/\d+/);
        await page.waitForLoadState('networkidle');
        assert.equal(retryKeys.length, 2);
        assert.equal(retryKeys[0], retryKeys[1]);
        assert.equal(new URL(page.url()).searchParams.get('print'), '1');
        assert.equal(await page.evaluate(() => window.__printCalls), 1);
        assert.equal(await page.locator('body').getAttribute('data-paper'), '58');
        assert.ok((await page.locator('.receipt-items').innerText()).includes('7 ×'));
        results.push('Insufficient tender feedback; retry keeps idempotency key; successful scan sale opens print once');
        fs.mkdirSync('docs/qa/receipts', {recursive: true});
        for (const paper of ['58', '80']) {
            await page.locator('#receiptWidth').selectOption(paper);
            await page.setViewportSize({width: 375, height: 900});
            await page.screenshot({path: `docs/qa/screenshots/barcode-receipt-${paper}.png`, fullPage: true});
            await page.emulateMedia({media: 'print'});
            assert.equal(await page.locator('.receipt-toolbar').isVisible(), false);
            const width = await page.locator('.receipt-paper').evaluate(element => element.getBoundingClientRect().width);
            assert.ok(Math.abs(width - Number(paper) * 96 / 25.4) < 1);
            await page.pdf({path: `docs/qa/receipts/barcode-receipt-${paper}.pdf`, width: paper + 'mm', height: '200mm', margin: {top: 0,bottom: 0,left: 0,right: 0}});
            await page.emulateMedia({media: 'screen'});
        }
        await page.locator('#printReceiptButton').click();
        assert.equal(await page.evaluate(() => window.__printCalls), 2);
        await page.reload(); await page.waitForLoadState('networkidle');
        assert.equal(await page.evaluate(() => window.__printCalls || 0), 0);
        results.push('58/80mm print CSS and PDF export pass; controls excluded from print; manual reprint works; reload does not repeat auto print');
        await page.getByRole('link', {name: 'New sale', exact: true}).click();
        await page.waitForURL(/pos$/);
        assert.equal(await page.locator('#receiptPaper').inputValue(), '80');
        await scan(); await quantity(1);
        await page.locator('#cartItems').getByRole('button', {name: 'Remove', exact: true}).click();
        assert.equal(await page.locator('[data-pos-quantity]').count(), 0);
        assert.ok(await page.locator('#payBtn').isDisabled());
        await scan(); await quantity(1);
        await page.locator('#paidAmount').fill('5000');
        await page.locator('#printReceipt').uncheck();
        await page.locator('#payBtn').click();
        await page.waitForURL(/pos\/receipt\/\d+/); await page.waitForLoadState('networkidle');
        assert.equal(new URL(page.url()).searchParams.has('print'), false);
        assert.equal(await page.evaluate(() => window.__printCalls || 0), 0);
        results.push('New sale starts clean; automatic print can be disabled');
        assert.deepEqual(errors, []);
        fs.writeFileSync('docs/qa/barcode-results.json', JSON.stringify({status: 'PASS', results, errors}, null, 2) + '\n');
        console.log(JSON.stringify({status: 'PASS', results, errors}, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
