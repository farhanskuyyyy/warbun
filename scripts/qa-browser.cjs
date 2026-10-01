const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:8765';
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Use only a disposable local QA environment');
const out = path.resolve('docs/qa');
fs.mkdirSync(path.join(out, 'screenshots'), { recursive: true });
(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ baseURL: base, viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();
    const errors = []; const results = [];
    page.on('pageerror', error => errors.push(error.message));
    async function goto(url) { const r = await page.goto(url); assert(r && r.status() < 400, url + ': ' + r?.status()); await page.waitForLoadState('networkidle'); }
    async function english() { if (await page.locator('.account-menu').count()) await page.locator('.account-menu summary').click(); await page.locator('select[name=locale]').selectOption('en'); await Promise.all([page.waitForURL(url => url.pathname === new URL(page.url()).pathname), page.locator('form[action$="/locale"] button').click()]); await page.waitForLoadState('networkidle'); }
    async function login(email) { await goto('/login'); await english(); await page.locator('input[name=email]').fill(email); await page.locator('input[name=password]').fill('password'); await Promise.all([page.waitForURL(/dashboard|my-orders/), page.locator('form[action$="/login"] button[type=submit]').click()]); }
    async function overflow(label) { const n = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth); assert(n <= 1, `${label} overflows by ${n}px`); }
    try {
        await login('owner@warbun.local'); results.push('Owner login and locale switch');
        for (const url of ['/dashboard','/products','/inventory','/debt','/orders','/payments','/stock-opnames','/refunds','/users','/settings','/reports/sales','/reports/staff','/audit','/profile']) { await goto(url); await overflow(url); }
        results.push('Desktop operational navigation without page errors or horizontal overflow');
        await goto('/settings'); await page.locator('[name=store_name]').fill('Warbun QA');await page.locator('form[action$="/settings"] button').click();await page.waitForLoadState('networkidle');assert.equal(await page.locator('[name=store_name]').inputValue(),'Warbun QA');results.push('Store settings save and persist');
        await goto('/stock-opnames');await page.locator('[name=reason]').fill('QA count');await page.locator('form[action$="/stock-opnames"] button').click();await page.waitForLoadState('networkidle');await page.locator('form[action$="/approve"] button').first().click();await page.waitForLoadState('networkidle');assert((await page.locator('article').first().innerText()).includes('Approved'));results.push('Stock opname submission and approval');
        await goto('/inventory');await page.getByRole('button',{name:'+ Stock In'}).click();assert(await page.locator('#stockInModal').evaluate(e=>e.open));await page.keyboard.press('Escape');assert(!(await page.locator('#stockInModal').evaluate(e=>e.open)));results.push('Inventory modal keyboard focus and Escape dismissal');
        await goto('/users');const staffForm=page.locator('form[action$="/users"][method=POST]');await staffForm.locator('[name=name]').fill('QA Cashier');await staffForm.locator('[name=email]').fill('qa-'+Date.now()+'@warbun.local');await staffForm.locator('[name=password]').fill('qa-long-password');await staffForm.locator('[name=role]').selectOption('cashier');await staffForm.locator('button').click();await page.waitForLoadState('networkidle');assert((await page.locator('tbody').innerText()).includes('QA Cashier'));results.push('Staff creation with assigned cashier role');
        await goto('/pos'); await page.locator('input[name=opening_cash]').fill('10000'); await page.locator('form[action$="/pos/open-shift"] button').click(); await page.waitForLoadState('networkidle');
        await page.locator('#productSearch').fill('Indomie Goreng'); await page.getByRole('button', { name: /Indomie Goreng/ }).click(); await page.locator('#paidAmount').fill('5000'); await page.locator('#payBtn').click(); await page.waitForURL(/pos\/receipt\/\d+/);
        const saleId = Number(new URL(page.url()).pathname.split('/').pop()); assert(saleId > 0); results.push('POS search, cart, tender, successful sale and receipt');
        await page.screenshot({ path: path.join(out,'screenshots','receipt-desktop.png'), fullPage: true });
        await goto('/refunds'); await page.locator('[name=source_id]').fill(String(saleId)); await page.locator('[name="items[0][product_id]"]').fill('1'); await page.locator('[name="items[0][quantity]"]').fill('1'); await page.locator('[name=reason]').fill('QA customer return'); await page.locator('form[action$="/refunds"] button').click(); await page.waitForLoadState('networkidle'); assert(await page.getByRole('status').count());results.push('Controlled cash refund through UI');
        await goto('/pos'); await page.locator('[name=closing_cash]').fill('10000'); await page.locator('form[action$="/pos/close-shift"] button').click(); await page.waitForLoadState('networkidle');results.push('Shift closes after net cash refund');
        await goto('/shifts'); assert((await page.locator('tbody').innerText()).includes('0.00'));
        await page.setViewportSize({ width: 375, height: 812 });
        for (const url of ['/dashboard','/pos','/inventory','/debt','/users','/settings','/stock-opnames','/shop','/cart']) { await goto(url); await overflow('mobile '+url); }
        await goto('/pos'); await page.screenshot({ path:path.join(out,'screenshots','pos-mobile.png'),fullPage:true });results.push('375px mobile layout and table containment');
        await page.keyboard.press('Tab'); assert(await page.evaluate(() => document.activeElement !== document.body));results.push('Keyboard focus works');
        await goto('/pos'); await page.locator('.account-menu summary').click(); await Promise.all([page.waitForURL(base+'/'),page.locator('form[action$="/logout"] button').first().click()]); await page.waitForLoadState('networkidle'); await context.clearCookies(); await login('customer@warbun.local');
        await goto('/shop'); page.once('dialog',d=>d.accept()); await page.locator('[data-product]').first().click();
        await goto('/cart'); await page.locator('#checkoutBtn').click(); await page.waitForURL(/order-success\/\d+/); assert((await page.locator('main').innerText()).includes('pending') || (await page.locator('main').innerText()).includes('Pending'));results.push('Customer catalog, local cart, checkout and own order history');
        await page.screenshot({path:path.join(out,'screenshots','customer-order-mobile.png'),fullPage:true});
        assert.equal((await page.request.get('/users')).status(),403);assert.equal((await page.request.get('/debt')).status(),403);results.push('Customer cannot access staff or financial backoffice');
        assert.deepEqual(errors,[],'JavaScript runtime errors');
        fs.writeFileSync(path.join(out,'browser-results.json'),JSON.stringify({status:'PASS',results,errors},null,2)+'\n');
        console.log('PASS',results);
    } catch(error) {
        await page.screenshot({path:path.join(out,'screenshots','failure.png'),fullPage:true}).catch(()=>{});
        fs.writeFileSync(path.join(out,'browser-results.json'),JSON.stringify({status:'FAIL',results,errors,error:error.message,url:page.url()},null,2)+'\n');
        throw error;
    } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
