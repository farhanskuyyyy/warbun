const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';
const tile = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"><rect width="256" height="256" fill="#eeeae2"/><path d="M0 90H256M110 0V256" stroke="#fff" stroke-width="20"/><path d="M0 90H256M110 0V256" stroke="#c9c2b6" stroke-width="1"/><text x="14" y="35" fill="#57534e" font-size="14">Synthetic QA tile</text></svg>';

(async()=>{
    const browser = await chromium.launch({channel:'chrome',headless:true});
    const errors=[];const results=[];let failTiles=false;let tileRequests=0;
    async function createPage() {
        const context=await browser.newContext({viewport:{width:1440,height:1000}});
        const page=await context.newPage();page.on('pageerror',error=>errors.push(error.stack));
        await page.route('https://tile.openstreetmap.org/**', route=>{
            tileRequests++;
            return route.fulfill({status:failTiles?503:200,contentType:'image/svg+xml',body:failTiles?'':tile});
        });
        await page.addInitScript(()=>{
            window.__geoMode='success';window.__geoCalls=0;window.__geoPoint={latitude:-6.2,longitude:106.8,accuracy:20};
            Object.defineProperty(navigator,'geolocation',{configurable:true,value:{
                getCurrentPosition(success,error) {
                    window.__geoCalls++;
                    if(window.__geoMode==='late') {window.__resolveGeo=()=>success({coords:window.__geoPoint});return;}
                    if(window.__geoMode==='denied') error({code:1});
                    else if(window.__geoMode==='timeout') error({code:3});
                    else if(window.__geoMode==='unavailable') error({code:2});
                    else success({coords:window.__geoPoint});
                },
            }});
        });
        return page;
    }
    const go=async(page,path)=>{const response=await page.goto(base+path);assert.equal(response.status(),200,path);await page.waitForLoadState('networkidle');};
    const login=async(page,email)=>{
        await go(page,'/login');
        await page.locator('[name=locale]').selectOption('en');await page.locator('form[action$="/locale"] button').click();
        await page.locator('[name=email]').fill(email);await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(url=>url.pathname!='/login');
    };
    const open=async(page,id)=>{
        await page.locator('[data-location-open="'+id+'"]').click();
        await page.waitForFunction(()=>!document.getElementById('centerDeliveryMap').disabled);
    };
    const locate=async page=>{await page.locator('#locateDeliveryMap').click();await page.waitForFunction(()=>!document.getElementById('confirmDeliveryMap').disabled);};
    const confirm=async page=>{await page.locator('#confirmDeliveryMap').click();await page.waitForFunction(()=>!document.getElementById('deliveryMapDialog').open);};
    const point=async(page,id)=>[await page.locator('#'+id+'-latitude').inputValue(),await page.locator('#'+id+'-longitude').inputValue()];
    try {
        const admin=await createPage();await login(admin,'owner@warbun.local');await go(admin,'/pos');
        assert.equal(tileRequests,0);
        assert.equal(await admin.evaluate(()=>window.__geoCalls),0);
        assert.equal(await admin.evaluate(()=>performance.getEntriesByType('resource').some(resource=>/leaflet-src.*\.js/.test(resource.name))),false);
        if(await admin.locator('[name=opening_cash]').count()) {
            await admin.locator('[name=opening_cash]').fill('0');await admin.locator('form[action$="/pos/open-shift"] button').click();await admin.waitForLoadState('networkidle');
        }
        await admin.locator('[name=fulfillment_type][value=delivery]').check();
        const qaCustomer = await admin.locator('#pos-config').evaluate(el=>JSON.parse(el.textContent).customers.find(customer=>customer.name==='QA Customer').id);
        await admin.locator('#customerId').selectOption(String(qaCustomer));
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
        await admin.locator('[data-address-add]').click();
        await admin.locator('#addressForm [name=address]').fill('Maps QA house 7, blue gate');
        assert.equal(await admin.locator('[data-delivery-point="addressBookPoint"] [data-location-clear]').isVisible(),false);
        await open(admin,'addressBookPoint');
        assert.equal(await admin.locator('#retryDeliveryMap').isVisible(),false);
        await locate(admin);await confirm(admin);
        assert.equal(await admin.locator('[data-delivery-point="addressBookPoint"] [data-location-clear]').isVisible(),true);
        assert.deepEqual(await point(admin,'addressBookPoint'),['-6.2000000','106.8000000']);
        assert.equal(await admin.locator('#addressForm [name=address]').inputValue(),'Maps QA house 7, blue gate');
        results.push('Lazy map loading and explicit geolocation; selected pin fills coordinates without overwriting the written address');

        await open(admin,'addressBookPoint');
        await admin.locator('#deliveryMapCanvas').click({position:{x:220,y:120}});
        await admin.locator('#cancelDeliveryMap').click();
        assert.deepEqual(await point(admin,'addressBookPoint'),['-6.2000000','106.8000000']);
        assert.equal(await admin.locator('[data-location-open=addressBookPoint]').evaluate(element=>element===document.activeElement),true);
        await open(admin,'addressBookPoint');await admin.locator('#deliveryMapCanvas').focus();await admin.keyboard.press('ArrowRight');await admin.waitForTimeout(400);
        await admin.locator('#centerDeliveryMap').click();await confirm(admin);
        assert.notDeepEqual(await point(admin,'addressBookPoint'),['-6.2000000','106.8000000']);
        await open(admin,'addressBookPoint');
        const pin=await admin.locator('.delivery-map-marker').boundingBox();
        await admin.mouse.move(pin.x+22,pin.y+22);await admin.mouse.down();await admin.mouse.move(pin.x+62,pin.y+32,{steps:10});await admin.mouse.up();
        const beforeDrag=await point(admin,'addressBookPoint');await confirm(admin);
        assert.notDeepEqual(await point(admin,'addressBookPoint'),beforeDrag);
        await admin.locator('[data-delivery-point=addressBookPoint] [data-location-clear]').click();
        assert.deepEqual(await point(admin,'addressBookPoint'),['','']);
        results.push('Map tap/cancel preserve saved point, close restores focus, keyboard pan plus Select center works, drag changes point and Remove clears both fields');

        for(const [mode,message]of [['denied','was denied'],['timeout','timed out'],['unavailable','is unavailable']]) {
            await open(admin,'addressBookPoint');await admin.evaluate(value=>window.__geoMode=value,mode);
            await admin.locator('#locateDeliveryMap').click();
            assert.match(await admin.locator('#deliveryMapError').textContent(),new RegExp(message));
            await admin.locator('#centerDeliveryMap').click();await confirm(admin);
            assert.ok((await point(admin,'addressBookPoint'))[0]);
        }
        await open(admin,'addressBookPoint');await admin.evaluate(()=>Object.defineProperty(window,'isSecureContext',{configurable:true,value:false}));
        await admin.locator('#locateDeliveryMap').click();
        assert.match(await admin.locator('#deliveryMapError').textContent(),/unavailable on this browser/);
        await admin.evaluate(()=>Object.defineProperty(window,'isSecureContext',{configurable:true,value:true}));await admin.keyboard.press('Escape');
        await open(admin,'addressBookPoint');await admin.evaluate(()=>window.__geoMode='late');await admin.locator('#locateDeliveryMap').click();await admin.locator('#closeDeliveryMap').click();
        const saved=await point(admin,'addressBookPoint');await admin.evaluate(()=>window.__resolveGeo());assert.deepEqual(await point(admin,'addressBookPoint'),saved);
        results.push('Denied/timeout/unavailable/insecure location has manual fallback; late GPS response after close cannot mutate saved fields');

        await open(admin,'addressBookPoint');await admin.evaluate(()=>window.__geoMode='late');
        await admin.locator('#locateDeliveryMap').click();await admin.locator('#centerDeliveryMap').click();
        const manual=await point(admin,'addressBookPoint');await admin.evaluate(()=>window.__resolveGeo());
        await confirm(admin);assert.deepEqual(await point(admin,'addressBookPoint'),manual);
        results.push('Picking manually while GPS is pending prevents a delayed GPS result from overwriting the chosen destination');

        failTiles=true;await open(admin,'addressBookPoint');await admin.locator('.leaflet-control-zoom-in').click();await admin.waitForFunction(()=>document.getElementById('deliveryMapError').textContent.includes('tiles'));
        failTiles=false;await admin.locator('#retryDeliveryMap').click();await admin.waitForFunction(()=>!document.getElementById('centerDeliveryMap').disabled);
        await admin.waitForLoadState('networkidle');assert.equal(await admin.locator('#deliveryMapError').textContent(),'');await admin.locator('#cancelDeliveryMap').click();
        results.push('Tile failure reports retry/manual fallback; Retry recovers using synthetic tiles without calling the real OSM service');

        await admin.locator('#cancelAddressDialog').click();

        await admin.locator('#newCustomerBtn').click();
        await admin.locator('#customerForm [name=name]').fill('Maps QA recipient');await admin.locator('#customerForm [name=phone]').fill('082987654321');
        await admin.locator('#customerForm [name=address]').fill('Maps QA new customer home');
        await admin.evaluate(()=>{window.__geoMode='success';window.__geoPoint={latitude:-6.3,longitude:107.8,accuracy:20};});
        await open(admin,'newCustomerDelivery');await locate(admin);await confirm(admin);
        assert.equal(await admin.locator('#customerDialog').evaluate(dialog=>dialog.open),true);
        await admin.locator('#saveCustomerBtn').click();await admin.waitForFunction(()=>!document.getElementById('customerDialog').open);
        await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false'&&Boolean(document.getElementById('shippingAddressId').value));
        assert.deepEqual(await point(admin,'posDelivery'),['-6.3000000','107.8000000']);
        assert.match(await admin.locator('#shippingAddress').inputValue(),/Maps QA new customer home/);
        const newCustomer=await admin.locator('#customerId').inputValue();
        await admin.locator('#customerId').selectOption(String(qaCustomer));await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');assert.deepEqual(await point(admin,'posDelivery'),['','']);
        await admin.locator('#customerId').selectOption(newCustomer);await admin.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');assert.deepEqual(await point(admin,'posDelivery'),['-6.3000000','107.8000000']);
        results.push('Nested map dialog creates a customer with a pin, restores customer modal and carries saved point into delivery; changing customers clears/replaces stale point');
        await admin.locator('#productList button').first().click();await admin.locator('#addProductBtn').click();
        await admin.locator('#paidAmount').fill('50000');await admin.locator('#printReceipt').uncheck();
        await admin.locator('#payBtn').click();await admin.waitForURL(/\/pos\/receipt\/\d+/);
        assert.match(await admin.locator('.delivery-point-link').getAttribute('href'),/mlat=-6.3000000.*mlon=107.8000000/);
        await go(admin,'/orders/monitor?search=Maps%20QA%20recipient');
        assert.match(await admin.locator('.monitor-order .delivery-point-link').getAttribute('href'),/mlat=-6.3000000/);
        results.push('Cashier delivery checkout persists snapshot into receipt and monitoring with a provider-fixed map link');

        const customer=await createPage();await login(customer,'customer@warbun.local');
        for(const [page,path]of [[admin,'/pos'],[customer,'/my-addresses']]) {
            await go(page,path);
            if(path==='/pos') {
                await page.locator('[name=fulfillment_type][value=delivery]').check();
                await page.locator('#customerId').selectOption(newCustomer);
                await page.waitForFunction(()=>document.getElementById('shippingAddressId').dataset.busy==='false');
            }
            for(const size of [{width:320,height:568},{width:375,height:812},{width:768,height:900},{width:1024,height:900},{width:1440,height:1000},{width:812,height:375}]) {
                await page.setViewportSize(size);await page.locator('[data-address-add]').click();await open(page,'addressBookPoint');
                assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
                for(const selector of ['#closeDeliveryMap','#cancelDeliveryMap','#confirmDeliveryMap','.delivery-map-attribution']) {
                    const box=await page.locator(selector).boundingBox();assert.ok(box.y>=0&&box.y+box.height<=size.height,selector+' '+JSON.stringify(size));
                }
                if([375,1440].includes(size.width)) await page.screenshot({path:'docs/qa/screenshots/maps-'+(path==='/pos'?'pos':'addresses')+'-'+size.width+'.png'});
                await page.locator('#cancelDeliveryMap').click();await page.locator('#cancelAddressDialog').click();
            }
        }
        results.push('Map nested in address dialog fits six narrow/wide/short viewports on POS/address management; close/cancel/confirm and attribution stay visible.');
        assert.deepEqual(errors,[]);
        const report={status:'PASS',results,errors,scope:'Isolated SQLite; real Leaflet with synthetic tiles and mocked browser GPS. No real OSM tile traffic or physical-device GPS testing.'};
        fs.writeFileSync('docs/qa/delivery-maps-results.json',JSON.stringify(report,null,2)+'\n');console.log(JSON.stringify(report,null,2));
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
