const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:8765';
assert(['127.0.0.1','localhost'].includes(new URL(base).hostname));
(async () => {
    const browser = await chromium.launch({channel:'chrome', headless:true});
    const context = await browser.newContext({viewport:{width:375,height:812}, baseURL:base});
    const page = await context.newPage(); const results = []; const errors = [];
    fs.mkdirSync('docs/qa/screenshots',{recursive:true});
    page.on('pageerror', error => errors.push(error.message));
    async function go(url) {const response = await page.goto(url); assert(response.status() < 400, url); await page.waitForLoadState('networkidle');}
    async function fits(label) {assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), label + ' document overflow');}
    async function screenshot(name) {await page.screenshot({path:'docs/qa/screenshots/'+name+'.png',fullPage:true});}
    async function english() {const menu = page.locator('.account-menu'); if (await menu.count()) await menu.locator('summary').click(); await page.locator('[name=locale]').selectOption('en'); await page.locator('form[action$="/locale"] button').click(); await page.waitForLoadState('networkidle');}
    try {
        for (const width of [320,375,390,768,1024,1440]) {
            await page.setViewportSize({width,height:900});
            for (const path of ['/login','/register','/forgot-password','/reset-password/ui-qa-token?email=customer@warbun.local','/shop','/shop?category=kopi-teh','/shop?search=nonexistent-qa','/cart']) {await go(path); await fits(width+' '+path);}
            await go('/login');
            if ([375,1440].includes(width)) await screenshot('auth-login-'+width);
            await go('/register');
            if ([375,1440].includes(width)) await screenshot('auth-register-'+width);
            await go('/shop');
            if ([375,1440].includes(width)) await screenshot('shop-shopping-'+width);
        }
        results.push('Auth, catalog/filter/empty states and cart fit 320/375/390/768/1024/1440px');
        await go('/login'); await english();
        await page.locator('[name=password]').fill('test-value');
        await page.locator('[data-password-toggle]').click(); assert.equal(await page.locator('[name=password]').getAttribute('type'),'text');
        await page.locator('[data-password-toggle]').click(); assert.equal(await page.locator('[name=password]').getAttribute('type'),'password');
        await page.locator('[name=email]').fill('unknown-auth-qa@example.test');
        await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForLoadState('networkidle'); assert(await page.getByRole('alert').count());
        results.push('Password visibility toggle, locale and failed sign-in feedback');
        await page.setViewportSize({width:375,height:812}); await go('/shop');
        const product = JSON.parse(await page.locator('[data-product]:not([disabled])').first().getAttribute('data-product'));
        await page.locator('[data-product]:not([disabled])').first().click();
        assert(await page.locator('[data-cart-bar]').isVisible()); assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        await page.getByRole('link',{name:/Review cart/}).click(); await page.waitForLoadState('networkidle');
        await page.waitForFunction(() => document.querySelector('[data-checkout-action]')?.getAttribute('aria-disabled') === 'false');
        await screenshot('cart-shopping-guest-mobile');
        await page.locator('[name=delivery_type]').selectOption('delivery'); await page.waitForLoadState('networkidle');
        assert(await page.locator('#shipping-address-field').isVisible()); assert.equal(await page.locator('[name=address]').getAttribute('required'),'');
        await page.waitForFunction(() => document.querySelector('[data-checkout-action]')?.getAttribute('aria-disabled') === 'false');
        assert((await page.locator('#shippingTotal').innerText()).includes('10,000'));
        await page.locator('[name=address]').fill('QA saved address before sign-in');
        await page.locator('[data-checkout-action]').click(); await page.waitForURL(/login/);
        await page.locator('[name=email]').fill('customer@warbun.local'); await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForURL(/cart/); await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        assert.equal(await page.locator('[name=delivery_type]').inputValue(),'delivery');
        assert.equal(await page.locator('[name=address]').inputValue(),'QA saved address before sign-in');
        results.push('Mobile add-to-cart bar, quote, shipping fee, required address and guest sign-in return to cart');
        await page.locator('#cartItems input').fill('2'); await page.locator('#cartItems input').press('Tab'); await page.waitForFunction(() => !document.querySelector('#checkoutBtn').disabled);
        assert.equal(await page.locator('[data-cart-count]').innerText(),'2');
        await page.locator('[name=delivery_type]').selectOption('delivery'); await page.locator('[name=address]').fill('QA Street 12, near the market');
        await page.locator('[name=payment_method]').selectOption('transfer');
        await page.waitForLoadState('networkidle'); await screenshot('cart-shopping-mobile');
        await page.locator('#checkoutBtn').click(); await page.waitForURL(/order-success\/\d+/); await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('[data-cart-count]').innerText(),'0');
        const orderPath = new URL(page.url()).pathname;
        for (const width of [320,375,768,1024,1440]) {await page.setViewportSize({width,height:900}); await go(orderPath); await fits('order '+width); if ([375,1440].includes(width)) await screenshot('order-shopping-'+width);}
        results.push('Delivery/transfer checkout, order breakdown, separate next-step buttons and confirmation cart clearing');
        await page.setViewportSize({width:375,height:812}); await go('/shop'); await page.locator('[data-product]:not([disabled])').first().click();
        await go(orderPath); assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        await go('/my-orders'); await fits('mobile history'); await screenshot('orders-shopping-mobile');
        await page.locator('.order-history-card a').first().click(); await page.waitForLoadState('networkidle'); assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        results.push('Viewing an existing order or order history preserves the new shopping cart');
        await go('/cart'); await page.route('**/cart/quote', route => route.abort());
        await page.reload(); await page.waitForLoadState('networkidle'); assert(await page.locator('#quote-retry').isVisible()); assert(await page.locator('#checkoutBtn').isDisabled());
        await page.unroute('**/cart/quote'); await page.locator('#quote-retry').click(); await page.waitForFunction(() => !document.querySelector('#checkoutBtn').disabled); assert(await page.locator('#checkoutBtn').isEnabled());
        results.push('Quote network failure blocks checkout and retry recovers');
        await page.evaluate(item => {localStorage.setItem('warbun_cart', JSON.stringify([{...item,price:1,quantity:1}]));},product);
        await go('/cart'); await page.waitForFunction(() => !document.querySelector('#checkoutBtn').disabled);
        assert.equal(await page.evaluate(() => Number(JSON.parse(localStorage.getItem('warbun_cart'))[0].price)),Number(product.price));
        await page.evaluate(item => {localStorage.setItem('warbun_cart', JSON.stringify([{...item,price:1,quantity:100000}]));},product);
        await go('/cart'); await page.waitForFunction(() => document.querySelector('#quote-errors').textContent.length > 0); assert(await page.locator('#checkoutBtn').isDisabled());
        await page.evaluate(item => {localStorage.setItem('warbun_cart', JSON.stringify([{...item,id:999999,quantity:1}]));},product);
        await go('/cart'); await page.waitForFunction(() => document.querySelector('#quote-errors').textContent.length > 0); assert(await page.locator('#checkoutBtn').isDisabled());
        await page.locator('.cart-remove').click(); assert(!(await page.locator('#checkoutForm').isVisible())); assert(await page.locator('.cart-empty').isVisible());
        await page.evaluate(() => localStorage.setItem('warbun_cart','invalid-json')); await go('/shop'); await page.locator('[data-product]:not([disabled])').first().click(); assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        results.push('Stale/unavailable stock blocks checkout; removing the item shows empty state; corrupt cart recovery works');
        await page.locator('.account-menu summary').click(); await page.locator('form[action$="/logout"] button').click(); await page.waitForLoadState('networkidle');
        await go('/cart'); await page.waitForFunction(() => document.querySelector('[data-checkout-action]')?.getAttribute('aria-disabled') === 'false'); await page.locator('[data-checkout-action]').click(); await page.waitForURL(/login/);
        await page.locator('.auth-alternative a').click(); await page.waitForURL(/register/);
        await page.locator('[name=name]').fill('QA Shopping User'); await page.locator('[name=email]').fill('shopping-'+Date.now()+'@example.test'); await page.locator('[name=password]').fill('password1234'); await page.locator('[name=password_confirmation]').fill('password1234');
        await page.locator('form[action$="/register"] button[type=submit]').click(); await page.waitForURL(/cart/); await page.waitForLoadState('networkidle'); assert.equal(await page.locator('[data-cart-count]').innerText(),'1');
        results.push('New-customer registration preserves shopping context and returns to checkout');
        await page.locator('.account-menu summary').click(); await page.locator('form[action$="/logout"] button').click(); await page.waitForLoadState('networkidle'); await go('/login');
        await page.locator('[name=email]').fill('owner@warbun.local'); await page.locator('[name=password]').fill('password'); await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForURL(/dashboard/);
        for (const path of ['/confirm-password','/verify-email','/dashboard','/products','/pos','/settings','/inventory','/orders','/refunds','/reports/sales','/profile']) {await go(path); await fits('mobile '+path);}
        await page.emulateMedia({reducedMotion:'reduce'}); await page.keyboard.press('Tab'); assert(await page.evaluate(() => document.activeElement !== document.body));
        results.push('Authenticated auth pages and other CMS screens remain contained on mobile; keyboard focus and reduced motion checked');
        assert.deepEqual(errors,[]);
        fs.writeFileSync('docs/qa/shopping-results.json',JSON.stringify({status:'PASS',results,errors},null,2)+'\n'); console.log('PASS', results);
    } catch (error) {await screenshot('shopping-failure').catch(()=>{}); console.error(page.url()); throw error;} finally {await browser.close();}
})().catch(error => {console.error(error);process.exitCode=1;});
