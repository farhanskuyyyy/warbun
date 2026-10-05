const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8080';

(async () => {
    const browser = await chromium.launch({channel:'chrome', headless:true});
    const page = await browser.newPage({viewport:{width:1440,height:1000}});
    const errors = [];
    const results = [];
    page.on('pageerror', error => errors.push(error.message));
    const go = async path => {
        const response = await page.goto(base + path);
        assert.equal(response.status(), 200, path);
        await page.waitForLoadState('networkidle');
    };
    try {
        await go('/login');
        await page.locator('[name=locale]').selectOption('en');
        await page.locator('form[action$="/locale"] button').click();
        await page.locator('[name=email]').fill('owner@warbun.local');
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
        const paths = ['/products','/categories','/brands','/product-types','/units','/suppliers','/customers','/orders','/payments','/audit'];
        for (const path of paths) {
            await go(path);
            const actions = page.locator('.crud-action');
            assert.ok(await actions.count() > 0, path + ' populated actions');
            const data = await actions.evaluateAll(elements => elements.map(element => ({
                label:element.getAttribute('aria-label'), title:element.title,
                icon:element.querySelector('use')?.getAttribute('href'),
                text:element.textContent.trim(),
                width:element.getBoundingClientRect().width,
                height:element.getBoundingClientRect().height,
                glyphWidth:element.querySelector('svg').getBBox().width,
                glyphHeight:element.querySelector('svg').getBBox().height,
            })));
            for (const action of data) {
                assert.ok(action.label && action.label === action.title);
                assert.match(action.icon, /#(eye|pen-to-square|trash-can)$/);
                assert.equal(action.text, '');
                assert.ok(action.glyphWidth > 0 && action.glyphHeight > 0, path + ' rendered SVG');
                assert.ok(action.width >= 44 && action.height >= 44, path + ' touch target');
            }
            for (const action of ['view','edit']) {
                const link = page.locator('a.crud-action-' + action).first();
                if (await link.count()) {
                    const href = await link.getAttribute('href');
                    await link.focus();
                    assert.equal(await link.evaluate(element => document.activeElement === element), true);
                    await page.keyboard.press('Enter');
                    await page.waitForURL(href);
                    assert.equal((await page.request.get(href)).status(), 200);
                    await go(path);
                }
            }
            const remove = page.locator('button.crud-action-delete').first();
            if (await remove.count()) {
                let confirmation = false;
                page.once('dialog', async dialog => {
                    confirmation = dialog.type() === 'confirm';
                    await dialog.dismiss();
                });
                await remove.click();
                assert.equal(confirmation, true, path + ' original delete confirmation');
                assert.equal(new URL(page.url()).pathname, path);
            }
            results.push(path + ': local icons, labels/tooltips, 44px targets, keyboard destinations and applicable delete cancellation PASS');
        }
        for (const path of ['/products/1','/customers/1']) {
            await go(path);
            const edit = page.locator('a.crud-action-edit');
            assert.equal(await edit.count(), 1);
            await edit.click();
            await page.waitForURL(/\/edit$/);
            results.push(path + ': detail-page edit icon navigates PASS');
        }
        await go('/shelves');
        await page.getByText('Manage shelves', {exact:true}).click();
        await page.locator('.shelf-settings .shelf-settings summary').first().click();
        const shelfDelete = page.locator('button.crud-action-delete').first();
        assert.equal(await shelfDelete.isDisabled(), true);
        assert.equal(await shelfDelete.getAttribute('title'), 'Delete empty shelf');
        results.push('Shelves: local delete icon retains populated-shelf disabled guard PASS');
        for (const width of [320,375,768,1024,1440]) {
            await page.setViewportSize({width,height:900});
            for (const path of paths) {
                await go(path);
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, path + ' viewport ' + width);
            }
            if ([375,1440].includes(width)) {
                await go('/products');
                await page.screenshot({path:'docs/qa/screenshots/crud-icons-products-' + width + '.png'});
            }
        }
        results.push('All ten indexes fit five viewports with scroll confined to tables PASS');
        await page.setViewportSize({width:1440,height:1000});
        await page.locator('.account-menu > summary').click();
        await page.locator('.language-settings [name=locale]').selectOption('id');
        await page.locator('.language-settings button').click();
        assert.equal(await page.locator('.crud-action-view').first().getAttribute('aria-label'), 'Lihat');
        assert.equal(await page.locator('.crud-action-edit').first().getAttribute('title'), 'Ubah');
        assert.equal(await page.locator('.crud-action-delete').first().getAttribute('aria-label'), 'Hapus');
        results.push('Indonesian labels/tooltips PASS');
        assert.deepEqual(errors, []);
        const report = {status:'PASS',scope:'Read-only preview checks; delete confirmations dismissed, no records mutated',results,errors};
        fs.writeFileSync('docs/qa/crud-icons-results.json', JSON.stringify(report,null,2) + '\n');
        console.log(JSON.stringify(report,null,2));
    } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exitCode=1;});
