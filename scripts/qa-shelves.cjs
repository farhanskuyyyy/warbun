const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';

(async () => {
    const browser = await chromium.launch({channel:'chrome', headless:true});
    const page = await browser.newPage({viewport:{width:1440,height:1000}});
    const errors = []; const results = [];
    page.on('pageerror', error => errors.push(error.message));
    const ready = () => page.waitForFunction(() => document.getElementById('productList')?.getAttribute('aria-busy') === 'false');
    try {
        await page.goto(base + '/login');
        await page.locator('[name=locale]').selectOption('en');
        await page.locator('form[action$="/locale"] button').click();
        await page.waitForLoadState('networkidle');
        await page.locator('[name=email]').fill('owner@warbun.local');
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
        await page.goto(base + '/shelves');
        const addForm = page.locator('form[action$="/shelves"][method="POST"]');
        await page.locator('.shelf-settings').first().locator('summary').first().click();
        await addForm.locator('[name=number]').fill('77');
        await addForm.locator('[name=name]').fill('QA shelf by the entrance');
        await addForm.locator('button').click();
        await page.waitForLoadState('networkidle');
        await page.locator('[name=search]').fill('IND-001');
        await page.locator('.shelf-search button').click();
        await page.waitForLoadState('networkidle');
        const row = page.locator('[data-placement-product]').first();
        const productId = await row.getAttribute('data-placement-product');
        await row.locator('summary').click();
        const option = await row.locator('option').filter({hasText:'QA shelf by the entrance'}).getAttribute('value');
        await row.locator('[name=shelf_id]').selectOption(option);
        await row.locator('[name=shelf_position]').fill('Top left');
        await row.locator('button').click();
        await page.waitForLoadState('networkidle');
        assert.match(await page.locator('.product-location').first().textContent(), /Shelf 77.*Top left/);
        results.push('Owner creates a shelf and assigns a product with position; search and redirect retain the product');

        await page.goto(base + '/shelves');
        await page.locator('.shelf-settings > summary').first().click();
        const settings = page.locator('.shelf-settings .shelf-settings').filter({hasText:'QA shelf by the entrance'});
        await settings.locator('summary').click();
        await settings.locator('[name=name]').fill('QA renamed shelf');
        await settings.locator('form').first().locator('button').click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('.shelf-map').getByText('QA renamed shelf', {exact:true}).count(), 1);
        await page.locator('.shelf-map a').filter({hasText:'QA renamed shelf'}).click();
        assert.equal(await page.locator('[data-placement-product]').count(), 1);
        results.push('Rename saves the shelf without losing placement; management shelf filter shows its assigned product');

        await page.goto(base + '/pos');
        if (await page.locator('[name=opening_cash]').count()) {
            await page.locator('[name=opening_cash]').fill('10000');
            await page.locator('form[action$="/pos/open-shift"] button').click();
        }
        await ready();
        assert.equal(await page.locator('#moreProductsBtn').isVisible(), false);
        await page.locator('[data-pos-category]').nth(1).click(); await ready();
        await page.locator('#productSearch').fill('does-not-match');
        await page.locator(`[data-pos-shelf="${option}"]`).click(); await ready();
        assert.equal(await page.locator('#productSearch').inputValue(), '');
        assert.equal(await page.locator('[data-pos-category=""]').getAttribute('aria-pressed'), 'true');
        assert.equal(await page.locator('#productList button').count(), 1);
        await page.locator('#productList button').click();
        assert.equal(await page.locator('#productDialog').evaluate(e=>e.open), true);
        assert.match(await page.locator('#productDialogLocation').textContent(), /Shelf 77.*Top left/);
        assert.equal(await page.locator('[data-pos-quantity]').count(), 0);
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('#productDialog').evaluate(e=>e.open), false);
        assert.equal(await page.locator('#productList button').evaluate(e=>e===document.activeElement), true);
        await page.locator('#productList button').click();
        await page.locator('#addProductBtn').click();
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '1');
        results.push('Shelf selection resets old filters; product dialog displays location, Escape restores focus and Add changes cart once');

        await page.route('**/pos/products?*', route => route.fulfill({status:500,json:{message:'QA failure'}}));
        await page.locator('[data-pos-shelf=""]').click(); await ready();
        assert.match(await page.locator('#productState').textContent(), /Unable to load/);
        assert.equal(await page.locator('[data-pos-quantity]').textContent(), '1');
        await page.unroute('**/pos/products?*');
        await page.locator(`[data-pos-shelf="${option}"]`).click(); await ready();
        results.push('Failed product lookup clears stale product tiles and preserves cart; next shelf selection recovers');

        const response = await page.request.get(base + '/pos/products?shelf_id=' + option);
        const product = (await response.json())[0];
        let lookupPage;
        await page.route('**/pos/products?*', route => {
            lookupPage = new URL(route.request().url()).searchParams.get('page');
            return route.fulfill({headers:{'X-Has-More':lookupPage==='1'?'1':'0'},json:[{...product,id:lookupPage==='1'?product.id:999999,name:lookupPage==='1'?product.name:'Second page QA product'}]});
        });
        await page.locator(`[data-pos-shelf="${option}"]`).click(); await ready();
        assert.equal(await page.locator('#moreProductsBtn').isVisible(), true);
        await page.locator('#moreProductsBtn').click(); await ready();
        assert.equal(lookupPage, '2');
        assert.equal(await page.locator('#productList button').count(), 2);
        assert.equal(await page.locator('#moreProductsBtn').isVisible(), false);
        await page.unroute('**/pos/products?*');
        await page.locator(`[data-pos-shelf="${option}"]`).click(); await ready();
        results.push('Mocked two-page response appends products and hides Load more when complete; backend pagination tested separately');

        for (const width of [320,375,768,1024,1440]) {
            await page.setViewportSize({width,height:1000});
            assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth > innerWidth), false, 'POS overflow '+width);
            assert.ok(await page.locator('[data-pos-shelf]').evaluateAll(es=>es.every(e=>e.getBoundingClientRect().height>=44)));
            await page.locator('#productList button').click();
            assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth > innerWidth), false, 'Dialog overflow '+width);
            assert.ok(await page.locator('#addProductBtn').isVisible());
            if ([375,1440].includes(width)) await page.screenshot({path:`docs/qa/screenshots/shelves-dialog-${width}.png`});
            await page.keyboard.press('Escape');
            if ([375,1440].includes(width)) await page.screenshot({path:`docs/qa/screenshots/shelves-pos-${width}.png`,fullPage:true});
            await page.goto(base + '/shelves');
            assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth > innerWidth), false, 'Management overflow '+width);
            if ([375,1440].includes(width)) await page.screenshot({path:`docs/qa/screenshots/shelves-management-${width}.png`,fullPage:true});
            await page.goto(base + '/pos'); await ready();
            await page.locator(`[data-pos-shelf="${option}"]`).click(); await ready();
        }
        results.push('POS, product dialog and placement at 320/375/768/1024/1440px: no overflow, usable controls, mobile and desktop screenshots');

        await page.goto(base + '/shelves?search=IND-001');
        await page.locator('[data-placement-product] summary').click();
        await page.locator('[name=shelf_id]').selectOption('');
        await page.locator('.shelf-placement-form button').click();
        await page.waitForLoadState('networkidle');
        assert.match(await page.locator('.product-location').textContent(), /Location not assigned/);
        await page.goto(base + '/pos'); await ready();
        await page.locator('[data-pos-shelf="unassigned"]').focus();
        await page.keyboard.press('Enter'); await ready();
        assert.ok(await page.locator('#productList button').filter({hasText:'Indomie Goreng'}).count());
        assert.equal(await page.locator('[data-pos-shelf="unassigned"]').getAttribute('aria-pressed'), 'true');
        results.push('Clearing placement removes the old position and product appears in Unassigned; keyboard shelf filter works');

        await page.goto(base + '/orders/monitor');
        await page.locator('.monitor-order details summary').first().click();
        assert.ok(await page.locator('.monitor-order').first().locator('.product-location').count());
        await page.locator('.monitor-order').first().locator('a[href*="/orders/"]').click();
        assert.ok(await page.locator('.order-detail-layout .product-location').count());
        results.push('Online monitoring item list and order detail display actual seeded shelf locations for picking');

        await page.goto(base + '/shelves');
        await page.locator('.shelf-settings > summary').first().click();
        const emptyShelf = page.locator('.shelf-settings .shelf-settings').filter({hasText:'QA renamed shelf'});
        await emptyShelf.locator('summary').click();
        await emptyShelf.locator('form').last().locator('button').click();
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('.shelf-map').getByText('QA renamed shelf', {exact:true}).count(), 0);
        results.push('Empty shelf can be deleted after its product is moved; assigned-shelf protection is covered in backend tests');
        assert.deepEqual(errors, []);
        fs.writeFileSync('docs/qa/shelves-results.json', JSON.stringify({status:'PASS',results,errors},null,2)+'\n');
        console.log(JSON.stringify({status:'PASS',results,errors},null,2));
    } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
