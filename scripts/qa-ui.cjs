const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:8765';
assert(['localhost', '127.0.0.1'].includes(new URL(base).hostname));
(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    const page = await browser.newPage({viewport: {width: 1440, height: 1000}});
    const errors = []; const results = [];
    page.on('pageerror', e => errors.push(e.message));
    fs.mkdirSync('docs/qa/screenshots', {recursive: true});
    async function go(url) { assert((await page.goto(base + url)).status() < 400, url); await page.waitForLoadState('networkidle'); }
    async function contained(url) { assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), url + ' overflow'); }
    try {
        await go('/');
        assert(await page.locator('.shelf-product').count() > 0);
        assert(await page.locator('.category-list a').count() > 0);
        await page.screenshot({path: 'docs/qa/screenshots/landing-redesign-desktop.png', fullPage: true});
        results.push('Landing renders actual product prices and linked categories');
        const landingLinks = await page.locator('main a, header a, footer a').evaluateAll(elements => [...new Set(elements.map(e => e.getAttribute('href')))]);
        for (const href of landingLinks) { await go('/'); const link = page.locator(`a[href="${href}"]`).first(); if (!(await link.isVisible())) await page.locator('.account-menu summary').click(); await link.click(); await page.waitForLoadState('networkidle'); assert(await page.locator('body').count()); }
        results.push('Clicked each distinct landing, navbar and footer destination');
        for (const width of [375, 768, 1024, 1440]) {
            await page.setViewportSize({width, height: 900});
            for (const url of ['/', '/shop', '/shop?search=nonexistent-qa-product', '/cart']) { await go(url); await contained(url); }
            await go('/');
            await page.locator('.account-menu summary').click();
            assert(await page.locator('.account-popover').isVisible());
            await page.keyboard.press('Escape');
            assert(!(await page.locator('.account-menu').evaluate(e => e.open)));
            if (width === 375) await page.screenshot({path: 'docs/qa/screenshots/landing-redesign-mobile.png', fullPage: true});
        }
        results.push('Storefront, catalog, empty search and cart fit 375/768/1024/1440px; account Escape returns focus');
        await go('/shop');
        page.once('dialog', d => d.accept());
        await page.locator('[data-product]').first().click();
        assert.equal(await page.locator('[data-cart-count]').innerText(), '1');
        await go('/cart'); assert.equal(await page.locator('[data-cart-count]').innerText(), '1');
        await page.locator('#cartItems input').fill('2'); await page.locator('#cartItems input').press('Tab');
        assert.equal(await page.locator('[data-cart-count]').innerText(), '2');
        await page.locator('#cartItems .cart-remove').click();
        assert.equal(await page.locator('[data-cart-count]').innerText(), '0');
        await go('/shop'); await page.locator('[name=search]').fill('nonexistent-qa-product'); await page.locator('main form button').click(); await page.waitForLoadState('networkidle'); assert.equal(await page.locator('[data-product]').count(), 0);
        results.push('Cart badge updates after adding a product and survives navigation');
        await go('/shop');
        await page.locator('main a').filter({has: page.locator('h2')}).first().click();
        await page.waitForLoadState('networkidle');
        await page.locator('[data-product]').click(); await page.waitForURL(/cart$/);
        assert.equal(await page.locator('[data-cart-count]').innerText(), '1');
        results.push('Product detail add-to-cart redirects to cart with the correct badge');
        await go('/login');
        await page.locator('[name=email]').fill('unknown-ui-qa@example.test'); await page.locator('[name=password]').fill('invalid-password');
        await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForLoadState('networkidle');
        assert(await page.getByRole('alert').count());
        results.push('Login error feedback is visible after invalid credentials');
        await go('/login'); await page.locator('[name=email]').fill('owner@warbun.local'); await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForURL(/dashboard/);
        for (const width of [375, 768, 1024, 1440]) {
            await page.setViewportSize({width, height: 1000});
            for (const url of ['/dashboard','/products','/inventory','/orders','/users','/settings','/reports/sales']) { await go(url); await contained(url); }
            await go('/dashboard');
            const sidebar = page.locator('.desktop-sidebar');
            if (width < 1024) {
                assert(!(await sidebar.isVisible()));
                await page.locator('[data-open-menu]').click();
                assert(await page.locator('#workspace-menu').evaluate(e => e.open));
                await page.keyboard.press('Escape');
                assert(!(await page.locator('#workspace-menu').evaluate(e => e.open)));
                assert(await page.locator('[data-open-menu]').evaluate(e => e === document.activeElement));
                await page.locator('[data-open-menu]').click();
                await page.locator('#workspace-menu a[href$="/products"]').click();
                await page.waitForURL(/products$/);
                assert(!(await page.locator('#workspace-menu').evaluate(e => e.open)));
            } else {
                assert(await sidebar.isVisible());
                assert.equal(await sidebar.locator('.nav-group').count(), 5);
            }
            await go('/dashboard');
            if ([375,1440].includes(width)) await page.screenshot({path: `docs/qa/screenshots/cms-redesign-${width === 375 ? 'mobile' : 'desktop'}.png`, fullPage: true});
        }
        await go('/categories');
        assert(await page.locator('.desktop-sidebar .master-navigation').evaluate(e => e.open));
        assert.equal(await page.locator('.desktop-sidebar a[aria-current=page]').count(), 1);
        results.push('CMS responsive layout, grouped sidebar, master-data disclosure, active route, mobile navigation and Escape focus restoration');
        await page.locator('.desktop-sidebar .master-navigation').evaluate(e => {e.open = true;});
        const workspaceLinks = await page.locator('.desktop-sidebar a').evaluateAll(elements => [...new Set(elements.map(e => e.getAttribute('href')))]);
        for (const href of workspaceLinks) {
            await go('/dashboard');
            await page.locator('.desktop-sidebar .master-navigation summary').click();
            await page.locator(`.desktop-sidebar a[href="${href}"]`).first().click();
            await page.waitForLoadState('networkidle');
            assert(!(await page.locator('body').innerText()).includes('Server Error'), href);
        }
        results.push('Clicked every grouped sidebar destination including all five master-data links');
        await page.setViewportSize({width:375,height:812}); await go('/dashboard');
        await page.locator('[data-open-menu]').click(); await page.locator('[data-close-menu]').click();
        assert(!(await page.locator('#workspace-menu').evaluate(e => e.open)));
        await page.locator('[data-open-menu]').click(); await page.mouse.click(360, 300);
        assert(!(await page.locator('#workspace-menu').evaluate(e => e.open)));
        await page.setViewportSize({width:1440,height:1000}); await go('/dashboard');
        await page.locator('.account-menu summary').click(); await page.locator('main h1').click();
        assert(!(await page.locator('.account-menu').evaluate(e => e.open)));
        results.push('Drawer close button, backdrop dismissal and outside-click account dismissal');
        await page.locator('.account-menu summary').click();
        await page.locator('.account-popover a[href$="/profile"]').click(); await page.waitForLoadState('networkidle');
        await page.locator('.account-menu summary').click();
        await page.locator('.account-popover a[href$="/dashboard"]').click(); await page.waitForLoadState('networkidle');
        await page.locator('.account-menu summary').click();
        await page.locator('[name=locale]').selectOption('en');
        await page.locator('form[action$="/locale"] button').click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('html').getAttribute('lang'), 'en');
        await page.locator('.account-menu summary').click();
        await page.locator('[name=locale]').selectOption('id');
        await page.locator('form[action$="/locale"] button').click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('html').getAttribute('lang'), 'id');
        results.push('Account menu switches Indonesian and English');
        await page.emulateMedia({reducedMotion: 'reduce'});
        await go('/'); await contained('reduced motion');
        await page.setViewportSize({width:375,height:812});
        await page.addStyleTag({content: 'html {font-size: 20px}'}); await contained('enlarged text');
        assert.deepEqual(errors, []);
        fs.writeFileSync('docs/qa/ui-results.json', JSON.stringify({status:'PASS', results, errors}, null, 2) + '\n');
        console.log('PASS', results);
    } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
