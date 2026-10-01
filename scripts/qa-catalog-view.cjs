const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const results = [];
    try {
        for (const width of [320, 375, 390, 479, 480, 600, 767]) {
            await page.setViewportSize({ width, height: 850 });
            await page.goto('http://127.0.0.1:8080/shop');
            await page.evaluate(() => localStorage.removeItem('warbun_catalog_view'));
            await page.reload();
            const grid = page.locator('#shop-products');
            await page.waitForFunction(() => document.querySelector('[data-catalog-view="grid"]').getAttribute('aria-pressed') === 'true');
            for (const view of ['grid', 'list']) {
                await page.locator(`[data-catalog-view="${view}"]`).click();
                assert.equal(await grid.getAttribute('data-view'), view);
                const columns = await grid.evaluate(element => getComputedStyle(element).gridTemplateColumns.split(' ').length);
                assert.equal(columns, view === 'grid' ? 2 : 1);
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
                const targets = await page.locator('[data-catalog-view], [data-product]').evaluateAll(elements => elements.map(element => ({width: element.getBoundingClientRect().width, height: element.getBoundingClientRect().height})));
                assert.ok(targets.every(target => target.width >= 44 && target.height >= 44));
                if (width === 375) await page.screenshot({path: `docs/qa/screenshots/shop-mobile-${view}.png`, fullPage: true});
                await page.reload();
                await page.waitForFunction(expected => document.getElementById('shop-products').dataset.view === expected, view);
            }
            results.push(`${width}px: two-column grid, single-column list, persistence, no overflow and 44px targets`);
        }
        await page.locator('[data-catalog-view="grid"]').click();
        await page.locator('[data-product]:not([disabled])').first().click();
        await page.waitForFunction(() => JSON.parse(localStorage.getItem('warbun_cart') || '[]').length > 0);
        await page.locator('[data-catalog-view="list"]').click();
        assert.ok(await page.locator('[data-cart-bar]').isVisible());
        assert.ok((await page.locator('[data-product-quantity]').first().textContent()).length > 0);
        await page.locator('[data-catalog-view="grid"]').focus();
        await page.keyboard.press('Enter');
        assert.equal(await page.locator('[data-catalog-view="grid"]').getAttribute('aria-pressed'), 'true');
        await page.locator('#product-search').fill('no-match-catalog-qa');
        await page.locator('.catalog-filters button').click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('#shop-products').getAttribute('data-view'), 'grid');
        assert.ok(await page.locator('.catalog-empty').isVisible());
        results.push('Cart survives view switch; keyboard Enter works; filtering retains preference and empty state');
        for (const width of [768, 1024, 1440]) {
            await page.setViewportSize({width, height: 900});
            await page.goto('http://127.0.0.1:8080/shop');
            assert.equal(await page.locator('.catalog-view-switch').isVisible(), false);
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
        }
        results.push('Desktop catalog preserved at 768/1024/1440px');
        const blocked = await browser.newContext({viewport: {width: 375, height: 850}});
        await blocked.addInitScript(() => { Storage.prototype.getItem = () => { throw new Error('Storage disabled'); }; Storage.prototype.setItem = () => { throw new Error('Storage disabled'); }; });
        const blockedPage = await blocked.newPage();
        await blockedPage.goto('http://127.0.0.1:8080/shop');
        await blockedPage.locator('[data-catalog-view="list"]').click();
        assert.equal(await blockedPage.locator('#shop-products').getAttribute('data-view'), 'list');
        results.push('Layout switching still works with browser storage blocked');
        await blocked.close();
        assert.deepEqual(errors, []);
        fs.writeFileSync('docs/qa/catalog-view-results.json', JSON.stringify({status: 'PASS', results, errors}, null, 2) + '\n');
        console.log(JSON.stringify({status: 'PASS', results, errors}, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
