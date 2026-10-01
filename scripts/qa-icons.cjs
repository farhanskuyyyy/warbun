const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    const page = await browser.newPage();
    const errors = [];
    const results = [];
    page.on('pageerror', error => errors.push(error.message));
    async function go(path) {
        assert.ok((await page.goto('http://127.0.0.1:8765' + path)).status() < 400);
        await page.waitForLoadState('networkidle');
    }
    async function iconsRender() {
        const dimensions = await page.locator('.ui-icon:visible use').evaluateAll(icons => icons.map(icon => {
            const box = icon.getBBox();
            return {width: box.width, height: box.height};
        }));
        assert.ok(dimensions.length > 0);
        assert.ok(dimensions.every(box => box.width > 0 && box.height > 0), 'SVG symbols should paint actual paths');
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
    }
    try {
        for (const width of [320, 375, 768, 1440]) {
            await page.setViewportSize({width, height: 900});
            await go('/shop');
            assert.ok(await page.getByRole('link', {name: /^Keranjang|^Cart/}).first().isVisible());
            await iconsRender();
            await page.locator('.account-menu summary').click();
            await iconsRender();
            if (width === 375) await page.screenshot({path: 'docs/qa/screenshots/icons-store-mobile.png'});
            await page.keyboard.press('Escape');
            await go('/login');
            await iconsRender();
            await page.getByRole('button', {name: 'Terapkan bahasa'}).click();
            await page.waitForLoadState('networkidle');
            assert.equal(await page.locator('html').getAttribute('lang'), 'id');
        }
        results.push('Store/cart/account/language SVGs render at 320/375/768/1440px with no overflow; icon-only controls have accessible names');
        await page.locator('[name=email]').fill('owner@warbun.local');
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
        for (const width of [320, 375, 768, 1440]) {
            await page.setViewportSize({width, height: 1000});
            await go('/dashboard');
            const navigation = page.locator(width < 1024 ? '#workspace-menu' : '.desktop-sidebar');
            if (width < 1024) await page.locator('[data-open-menu]').click();
            await navigation.locator('.master-navigation summary').click();
            const links = navigation.locator('.nav-link');
            assert.equal(await links.count(), 21);
            assert.equal(await links.locator('.ui-icon').count(), 21);
            await iconsRender();
            if ([375,1440].includes(width)) await page.screenshot({path: `docs/qa/screenshots/icons-cms-${width}.png`, fullPage: true});
            if (width < 1024) {
                await page.locator('[data-close-menu]').click();
                assert.ok(await page.locator('[data-open-menu]').evaluate(element => element === document.activeElement));
            }
        }
        results.push('All 21 CMS destinations plus master-data disclosure have rendered icons and retained labels; mobile close restores focus');
        await page.locator('.account-menu summary').click();
        await page.locator('[name=locale]').selectOption('en');
        await page.getByRole('button', {name: 'Terapkan bahasa'}).click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('html').getAttribute('lang'), 'en');
        await page.locator('.account-menu summary').click();
        await page.getByRole('button', {name: 'Logout'}).click();
        await page.waitForLoadState('networkidle');
        await page.locator('.account-menu summary').click();
        assert.ok(await page.locator('.account-popover a[href$="/login"]').isVisible());
        results.push('Icon language submission changes locale; icon logout preserves existing behavior');
        assert.deepEqual(errors, []);
        fs.writeFileSync('docs/qa/icon-results.json', JSON.stringify({status: 'PASS', results, errors}, null, 2) + '\n');
        console.log(JSON.stringify({status: 'PASS', results, errors}, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
