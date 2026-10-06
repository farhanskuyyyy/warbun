const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';
const tile = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"><rect width="256" height="256" fill="#eeeae2"/><path d="M0 90H256M110 0V256" stroke="white" stroke-width="20"/><text x="14" y="35" fill="#57534e" font-size="14">Synthetic QA tile</text></svg>';

(async () => {
    const browser = await chromium.launch({channel:'chrome', headless:true});
    const errors = []; const results = [];
    async function page() {
        const context = await browser.newContext({viewport:{width:1440,height:1000}});
        const page = await context.newPage();
        page.on('pageerror', error => errors.push(error.stack));
        await page.route('https://tile.openstreetmap.org/**', route => route.fulfill({contentType:'image/svg+xml',body:tile}));
        await page.addInitScript(() => Object.defineProperty(navigator,'geolocation',{value:{getCurrentPosition(success){success({coords:{latitude:-6.2,longitude:106.8}});}}}));
        return page;
    }
    async function go(page,path) {
        const response = await page.goto(base+path); assert.equal(response.status(),200,path);
        await page.waitForLoadState('networkidle');
    }
    async function login(page,email) {
        await go(page,'/login');
        await page.locator('[name=locale]').selectOption('en'); await page.locator('form[action$="/locale"] button').click();
        await page.locator('[name=email]').fill(email); await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click(); await page.waitForURL(url=>url.pathname!='/login');
        await page.waitForLoadState('networkidle');
    }
    async function seedCart(page) {
        await page.evaluate(()=>localStorage.setItem('warbun_cart',JSON.stringify([{id:1,name:'QA product',price:2000,quantity:1}])));
    }
    const point = async (page,id) => [await page.locator('#'+id+'-latitude').inputValue(),await page.locator('#'+id+'-longitude').inputValue()];
    async function add(page,label,address,pin=false) {
        await page.locator('[data-address-add]').click();
        const form = page.locator('#addressForm');
        await form.locator('[name=label]').fill(label);
        await form.locator('[name=recipient_name]').fill('QA '+label);
        await form.locator('[name=phone]').fill('0812334455');
        await form.locator('[name=address]').fill(address);
        if(pin) {
            await page.locator('[data-location-open=addressBookPoint]').click();
            await page.waitForFunction(()=>!document.getElementById('locateDeliveryMap').disabled);
            await page.locator('#locateDeliveryMap').click();
            await page.locator('#confirmDeliveryMap').click();
            assert.equal(await page.locator('#addressDialog').evaluate(dialog=>dialog.open),true);
        }
        await page.locator('#saveAddressBtn').click();
        await page.waitForFunction(()=>!document.getElementById('addressDialog').open);
        return page.locator('#shippingAddressId').count().then(async count=>count?await page.locator('#shippingAddressId').inputValue():null);
    }
    try {
        const customer = await page(); await go(customer,'/shop'); await seedCart(customer); await go(customer,'/cart');
        await customer.locator('#delivery-type').selectOption('delivery');
        await customer.locator('[data-address-add]').click(); assert.equal(await customer.locator('#addressForm').count(),0);
        await customer.locator('#addressDialog a').click(); await customer.waitForURL(/\/login/);
        await login(customer,'customer@warbun.local'); await customer.waitForURL(/\/cart/);
        assert.equal(await customer.locator('#delivery-type').inputValue(),'delivery');
        assert.equal(await customer.locator('#shippingAddressId option').count(),1);
        assert.equal(await customer.locator('#checkoutBtn').isDisabled(),true);
        results.push('Guest is invited to sign in; cart and delivery choice survive. Empty address book blocks delivery checkout.');

        await customer.locator('[data-address-add]').click();
        await customer.locator('#closeAddressDialog').press('Enter');
        assert.equal(await customer.locator('[data-address-add]').evaluate(button=>button===document.activeElement),true);
        const home = await add(customer,'Home','Home 12, blue gate',true);
        assert.deepEqual(await point(customer,'cartDelivery'),['-6.2000000','106.8000000']);
        const office = await add(customer,'Office','Office floor 2');
        assert.notEqual(home,office); assert.deepEqual(await point(customer,'cartDelivery'),['','']);
        assert.match(await customer.locator('[data-address-summary]').textContent(),/QA Office.*0812334455\nOffice floor 2/);
        await go(customer,'/cart'); assert.equal(await customer.locator('#shippingAddressId').inputValue(),office);
        await customer.locator('#shippingAddressId').selectOption(home); assert.deepEqual(await point(customer,'cartDelivery'),['-6.2000000','106.8000000']);
        await customer.locator('#delivery-type').selectOption('pickup'); assert.equal(await customer.locator('#shippingAddressId').isDisabled(),true);
        await customer.locator('#delivery-type').selectOption('delivery'); assert.equal(await customer.locator('#shippingAddressId').inputValue(),home);
        await customer.setViewportSize({width:375,height:812}); await customer.screenshot({path:'docs/qa/screenshots/address-cart-selected-375.png',fullPage:true}); await customer.setViewportSize({width:1440,height:1000});
        results.push('Add-modal/map nesting works; two destinations select their own recipient/address/pin, draft restores selection and pickup/delivery retains it.');

        await customer.waitForFunction(()=>!document.getElementById('checkoutBtn').disabled);
        await customer.locator('#checkoutBtn').click(); await customer.waitForURL(/\/order-success\/\d+/);
        const orderPath = new URL(customer.url()).pathname;
        assert.match(await customer.locator('.delivery-point-link').getAttribute('href'),/mlat=-6.2000000/);
        assert.match(await customer.locator('main').textContent(),/Home 12, blue gate/);
        await go(customer,'/my-addresses');
        for(const width of [375,1440]) {await customer.setViewportSize({width,height:1000});await customer.screenshot({path:'docs/qa/screenshots/address-book-page-'+width+'.png',fullPage:true});}
        const homeCard = customer.locator('.address-card').filter({has:customer.locator('h2',{hasText:'Home'})});
        await homeCard.locator('[data-edit]').click(); await customer.locator('#addressForm [name=address]').fill('Home moved 99');
        await customer.locator('[data-location-clear]').click(); await customer.locator('#saveAddressBtn').click(); await customer.waitForFunction(()=>!document.getElementById('addressDialog').open);
        assert.equal(await homeCard.locator('[data-map]').isVisible(),false);
        const officeCard = customer.locator('.address-card').filter({has:customer.locator('h2',{hasText:'Office'})});
        await officeCard.locator('[data-set-default]').click(); await customer.waitForFunction(()=>document.querySelectorAll('.address-default-label:not([hidden])').length===1);
        await officeCard.locator('[data-default]').waitFor({state:'visible'});
        customer.on('dialog',dialog=>dialog.accept());
        await officeCard.locator('[data-delete]').click(); await customer.waitForFunction(()=>document.querySelectorAll('.address-card').length===1);
        await homeCard.locator('[data-default]').waitFor({state:'visible'});
        await homeCard.locator('[data-delete]').click(); await customer.waitForFunction(()=>document.querySelectorAll('.address-card').length===0);
        await go(customer,orderPath); assert.match(await customer.locator('main').textContent(),/Home 12, blue gate/);
        assert.match(await customer.locator('.delivery-point-link').getAttribute('href'),/mlat=-6.2000000/);
        await seedCart(customer); await go(customer,'/cart'); await customer.locator('#delivery-type').selectOption('delivery');
        assert.equal(await customer.locator('#checkoutBtn').isDisabled(),true);
        results.push('Online order snapshots survive address edits/deletion; management supports edit/remove/default, default promotion and empty-after-delete.');

        const admin = await page(); await login(admin,'owner@warbun.local'); await go(admin,'/pos');
        if(await admin.locator('[name=opening_cash]').count()) {
            await admin.locator('[name=opening_cash]').fill('0'); await admin.locator('form[action$="/pos/open-shift"] button').click(); await admin.waitForLoadState('networkidle');
        }
        await admin.locator('[name=fulfillment_type][value=delivery]').check();
        assert.equal(await admin.locator('[data-address-add]').isDisabled(),true);
        const customers = await admin.locator('#pos-config').evaluate(el=>JSON.parse(el.textContent).customers);
        const target = customers.find(customer=>customer.name==='QA Customer');
        assert.ok(target);
        await admin.locator('#customerId').selectOption(String(target.id));
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        assert.equal(await admin.locator('#shippingAddressId option').count(),1);
        const posHome = await add(admin,'POS Home','POS home 77');
        const posOffice = await add(admin,'POS Office','POS office 55',true);
        assert.deepEqual(await point(admin,'posDelivery'),['-6.2000000','106.8000000']);
        await admin.locator('#shippingAddressId').selectOption(posHome); assert.deepEqual(await point(admin,'posDelivery'),['','']);
        await admin.locator('#shippingAddressId').selectOption(posOffice);
        await admin.setViewportSize({width:375,height:812});await admin.locator('[data-address-summary]').scrollIntoViewIfNeeded();await admin.screenshot({path:'docs/qa/screenshots/address-pos-selected-375.png'});await admin.setViewportSize({width:1440,height:1000});
        results.push('POS requires a customer and saved destination, supports modal add for empty/multiple books and clears stale coordinates when choosing an unpinned address.');

        const other = customers.find(customer=>customer.id!==target.id);
        await admin.route('**/pos/customers/'+target.id+'/addresses',async route=>{
            await new Promise(resolve=>setTimeout(resolve,400)); await route.continue().catch(()=>{});
        });
        await admin.locator('#customerId').selectOption('');
        const pending = admin.waitForRequest(request=>request.url().includes('/pos/customers/'+target.id+'/addresses'));
        await admin.locator('#customerId').selectOption(String(target.id)); await pending;
        await admin.locator('#customerId').selectOption(String(other.id));
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        await admin.waitForTimeout(450);
        assert.doesNotMatch(await admin.locator('[data-address-summary]').textContent(),/POS home 77|POS office 55/);
        await admin.unroute('**/pos/customers/'+target.id+'/addresses');
        await admin.locator('#customerId').selectOption(String(target.id));
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        results.push('A delayed address response after switching customers cannot replace the current customer destination.');

        let failSave = true;
        await admin.route('**/pos/customers/*/addresses',async route=>{
            if(route.request().method()==='POST'&&failSave) {
                await new Promise(resolve=>setTimeout(resolve,200));
                await route.fulfill({status:503,contentType:'application/json',body:JSON.stringify({message:'QA unavailable'})});
            } else await route.continue();
        });
        await admin.locator('[data-address-add]').click();
        await admin.locator('#addressForm [name=label]').fill('Retry address');
        await admin.locator('#addressForm [name=address]').fill('Retry house 9');
        await admin.locator('#saveAddressBtn').click();
        await admin.waitForFunction(()=>document.getElementById('saveAddressBtn').disabled);
        await admin.keyboard.press('Escape');
        assert.equal(await admin.locator('#addressDialog').evaluate(dialog=>dialog.open),true);
        await admin.locator('#addressFormError').filter({hasText:/Unable to save/}).waitFor();
        assert.equal(await admin.locator('#addressForm [name=address]').inputValue(),'Retry house 9');
        failSave=false; await admin.locator('#saveAddressBtn').click(); await admin.waitForFunction(()=>!document.getElementById('addressDialog').open);
        assert.match(await admin.locator('[data-address-summary]').textContent(),/Retry house 9/);
        await admin.unroute('**/pos/customers/*/addresses');
        results.push('Save failure keeps entered fields; pending save disables repeated submission/close and retry saves/selects the address.');

        let failList = true;
        await admin.route('**/pos/customers/*/addresses', route=>route.request().method()==='GET'&&failList?route.fulfill({status:503,body:'unavailable'}):route.continue());
        await admin.locator('#customerId').selectOption('');
        await admin.locator('#customerId').selectOption(String(target.id));
        await admin.locator('[data-address-retry]').waitFor({state:'visible'});
        assert.equal(await admin.locator('#shippingAddressId').inputValue(),'');
        assert.equal(await admin.locator('#payBtn').isDisabled(),true);
        failList = false; await admin.locator('[data-address-retry]').click();
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false'&&document.getElementById('shippingAddressId').value!=='');
        await admin.unroute('**/pos/customers/*/addresses');
        await admin.locator('#shippingAddressId').selectOption(posOffice);
        await admin.locator('#productList button').first().click(); await admin.locator('#addProductBtn').click();
        await admin.locator('#paidAmount').fill('50000'); await admin.locator('#printReceipt').uncheck();
        await admin.locator('#payBtn').click(); await admin.waitForURL(/\/pos\/receipt\/\d+/);
        assert.match(await admin.locator('body').textContent(),/POS office 55/);
        assert.match(await admin.locator('.delivery-point-link').getAttribute('href'),/mlat=-6.2000000/);
        results.push('Failed POS address load cannot retain the old customer destination; retry recovers and sale receipt records the chosen address/pin.');

        await go(admin,'/pos'); await admin.locator('[name=fulfillment_type][value=delivery]').check();
        await admin.locator('#newCustomerBtn').click();
        await admin.locator('#customerForm [name=name]').fill('QA book new customer');
        await admin.locator('#customerForm [name=phone]').fill('082765432111');
        await admin.locator('#customerForm [name=address]').fill('New customer house 8');
        await admin.locator('#saveCustomerBtn').click(); await admin.waitForFunction(()=>!document.getElementById('customerDialog').open);
        await admin.waitForFunction(()=>Boolean(document.getElementById('shippingAddressId').value));
        assert.match(await admin.locator('[data-address-summary]').textContent(),/New customer house 8/);
        const newCustomer = await admin.locator('#customerId').inputValue();
        await admin.locator('#customerId').selectOption(String(target.id)); await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        assert.match(await admin.locator('[data-address-summary]').textContent(),/POS home 77/);
        await admin.locator('#customerId').selectOption(newCustomer); await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        assert.match(await admin.locator('[data-address-summary]').textContent(),/New customer house 8/);
        results.push('Creating a POS customer imports their first address and selects it; switching customers reloads only that customer book.');

        await admin.locator('[data-address-add]').click();
        await admin.locator('#addressForm [name=label]').fill('Invalid');
        await admin.locator('#addressForm [name=address]').fill('Validation house');
        await admin.locator('#addressBookPoint-latitude').evaluate(el=>el.value='91');
        await admin.locator('#addressBookPoint-longitude').evaluate(el=>el.value='106');
        await admin.locator('#saveAddressBtn').click(); await admin.locator('#addressFormError').filter({hasText:/latitude/}).waitFor();
        assert.equal(await admin.locator('#addressForm [name=address]').inputValue(),'Validation house');
        await admin.locator('#cancelAddressDialog').click();
        results.push('Server validation errors retain the modal fields and allow cancel/retry.');

        for(const [p,path] of [[customer,'/cart'],[admin,'/pos'],[customer,'/my-addresses']]) {
            await go(p,path);
            if(path==='/cart') await p.locator('#delivery-type').selectOption('delivery');
            if(path==='/pos') {
                await p.locator('[name=fulfillment_type][value=delivery]').check(); await p.locator('#customerId').selectOption(String(target.id));
                await p.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
            }
            for(const size of [{width:320,height:568},{width:375,height:812},{width:768,height:900},{width:1024,height:900},{width:1440,height:1000},{width:812,height:375}]) {
                await p.setViewportSize(size); await p.locator('[data-address-add]').click();
                assert.equal(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
                for(const selector of ['#closeAddressDialog','#cancelAddressDialog','#saveAddressBtn']) {
                    const box=await p.locator(selector).boundingBox(); assert.ok(box.y>=0&&box.y+box.height<=size.height,selector+JSON.stringify(size));
                }
                if([375,1440].includes(size.width)) await p.screenshot({path:'docs/qa/screenshots/address-'+path.slice(1)+'-'+size.width+'.png'});
                await p.keyboard.press('Escape');
                assert.equal(await p.locator('#addressDialog').evaluate(dialog=>dialog.open),false);
            }
        }
        results.push('Cart/POS/address management and modal controls fit six narrow/wide/short viewports; Escape and focus restoration work.');
        assert.deepEqual(errors,[]);
        const report={status:'PASS',results,errors,scope:'Isolated SQLite, Chrome; synthetic OSM tiles and mocked GPS, no physical-device verification.'};
        fs.writeFileSync('docs/qa/address-book-results.json',JSON.stringify(report,null,2)+'\n');
        console.log(JSON.stringify(report,null,2));
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
