const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const {Code128Reader,EAN13Reader} = require('@zxing/library');
const UPCEANReader = require('@zxing/library/cjs/core/oned/UPCEANReader').default;
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8765';
const code = '0012345678905';
const ean = '5901234123457';

function code128(value) {
    const codes = Array.from(value, char=>char.charCodeAt(0)-32);
    const checksum = (104+codes.reduce((sum,n,index)=>sum+n*(index+1),0))%103;
    return [104,...codes,checksum,106].flatMap(n=>Array.from(Code128Reader.CODE_PATTERNS[n]));
}
function ean13(value) {
    const parity = EAN13Reader.FIRST_DIGIT_ENCODINGS[Number(value[0])];
    const left = Array.from(value.slice(1,7),(char,index)=>Array.from(UPCEANReader.L_PATTERNS[Number(char)]));
    left.forEach((widths,index)=>{ if(parity & (1<<(5-index))) widths.reverse(); });
    return [1,1,1,...left.flat(),1,1,1,1,1,...Array.from(value.slice(7),char=>Array.from(UPCEANReader.L_PATTERNS[Number(char)])).flat(),1,1,1];
}

(async()=>{
    const browser = await chromium.launch({channel:'chrome',headless:true});
    const page = await browser.newPage({viewport:{width:1440,height:1000}});
    const errors=[]; const results=[];
    page.on('pageerror',e=>errors.push(e.message));
    await page.addInitScript(({codeWidths,eanWidths})=>{
        window.__cameraMode='blank'; window.__cameraTracks=[]; window.__cameraRequests=[];window.__focusRequests=[];
        function streamFor(constraints) {
            const canvas=document.createElement('canvas');canvas.width=1280;canvas.height=720;
            const ctx=canvas.getContext('2d');ctx.fillStyle='white';ctx.fillRect(0,0,1280,720);
            const widths=window.__cameraMode==='ean'?eanWidths:codeWidths;
            if(['code','ean','offset'].includes(window.__cameraMode)) {
                let x=(1280-widths.reduce((a,b)=>a+b,0)*4)/2;
                widths.forEach((width,index)=>{ if(index%2===0){ctx.fillStyle=window.__cameraMode==='offset'?'#2873a1':'black';ctx.fillRect(x,window.__cameraMode==='offset'?60:220,width*4,window.__cameraMode==='offset'?40:260);}x+=width*4; });
            }
            const stream=canvas.captureStream(15);
            setInterval(()=>{ctx.fillStyle='white';ctx.fillRect(0,0,1,1);},80);
            const track=stream.getVideoTracks()[0];
            track.getSettings=()=>({deviceId:constraints.video.deviceId?.exact||'back'});
            track.getCapabilities=()=>({focusMode:['manual','continuous']});
            track.applyConstraints=async value=>{window.__focusRequests.push(value);};
            window.__cameraTracks.push(track);
            return stream;
        }
        navigator.mediaDevices.getUserMedia=async constraints=>{
            window.__cameraRequests.push(constraints);
            if(window.__cameraMode==='denied') throw new DOMException('QA denied','NotAllowedError');
            if(window.__cameraMode==='missing') throw new DOMException('QA absent','NotFoundError');
            if(window.__cameraMode==='busy') throw new DOMException('QA busy','NotReadableError');
            if(window.__cameraMode==='late') return new Promise(resolve=>{window.__resolveCamera=()=>resolve(streamFor(constraints));});
            return streamFor(constraints);
        };
        navigator.mediaDevices.enumerateDevices=async()=>[{kind:'videoinput',deviceId:'back',label:'QA rear camera'},{kind:'videoinput',deviceId:'front',label:'QA front camera'}];
    },{codeWidths:code128(code),eanWidths:ean13(ean)});
    const go=async path=>{await page.goto(base+path);await page.waitForLoadState('networkidle');};
    const open=()=>page.locator('[data-camera-target]').click();
    const mode=value=>page.evaluate(value=>window.__cameraMode=value,value);
    const ended=()=>page.waitForFunction(()=>window.__cameraTracks.every(track=>track.readyState==='ended'));
    try {
        await go('/login');await page.locator('[name=locale]').selectOption('en');await page.locator('form[action$="/locale"] button').click();
        await page.locator('[name=email]').fill('owner@warbun.local');await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();await page.waitForURL(/dashboard/);
        await go('/products/1/edit');
        assert.equal(await page.evaluate(()=>window.__cameraRequests.length),0);
        assert.equal(await page.evaluate(()=>performance.getEntriesByType('resource').some(e=>/\/esm-.*\.js/.test(e.name))),false);
        await mode('blank');await open();
        await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
        const mirror = page.locator('#barcodeCameraMirror');
        assert.equal(await page.evaluate(()=>window.__focusRequests.at(-1).advanced[0].focusMode),'continuous');
        assert.equal(await mirror.isChecked(),false);
        const requestsBeforeMirror=await page.evaluate(()=>window.__cameraRequests.length);
        await mirror.focus();await page.keyboard.press('Space');
        assert.equal(await mirror.isChecked(),true);
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>new DOMMatrix(getComputedStyle(e).transform).a),-1);
        assert.equal(await page.evaluate(()=>window.__cameraRequests.length),requestsBeforeMirror);
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>e.srcObject.getVideoTracks()[0].readyState),'live');
        await mode('code');await page.locator('#retryBarcodeCamera').click();
        await page.waitForFunction(value=>document.getElementById('field-barcode').value===value,code);
        assert.equal(await page.locator('#barcodeCameraDialog').evaluate(e=>e.open),false);
        await ended();
        assert.equal(await page.locator('#field-barcode').evaluate(e=>e===document.activeElement),true);
        assert.ok(page.url().endsWith('/products/1/edit'));
        await page.getByRole('button',{name:'Update Product',exact:true}).click();await page.waitForURL(/products$/);
        await go('/products/1');assert.match(await page.locator('main').innerText(),new RegExp(code));
        results.push('Real Code 128 canvas/video decodes with leading zeroes, fills edit without auto-save, releases camera and saves through normal product validation');

        await go('/products/create');await mode('blank');await open();
        await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
        assert.equal(await mirror.isChecked(),true);
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>new DOMMatrix(getComputedStyle(e).transform).a),-1);
        await mode('ean');await page.locator('#retryBarcodeCamera').click();
        await page.waitForFunction(value=>document.getElementById('field-barcode').value===value,ean);
        await ended();assert.ok(page.url().endsWith('/products/create'));
        assert.equal(await mirror.isChecked(),true);
        results.push('Keyboard mirror toggle flips live preview without restarting camera; preference survives retry/navigation and real Code 128/EAN-13 decoding remains correct while mirrored');
        results.push('Real EAN-13 canvas/video decodes into create form without submitting or creating a product');

        await page.locator('#field-barcode').fill('');await mode('offset');await open();
        await page.waitForFunction(value=>document.getElementById('field-barcode').value===value,code);
        await ended();
        results.push('Blue Code 128 near the top edge of a real video frame decodes automatically with expanded row search');
        await page.locator('#field-barcode').fill(ean);

        for(const [scenario,expected] of [['denied','permission was denied'],['missing','No camera was found'],['busy','Camera is unavailable']]) {
            await mode(scenario);await open();await page.waitForFunction(()=>document.getElementById('barcodeCameraError').textContent.length>0);
            assert.match(await page.locator('#barcodeCameraError').textContent(),new RegExp(expected));
            await page.locator('#cancelBarcodeCamera').click();
            assert.equal(await page.locator('#field-barcode').inputValue(),ean);
        }
        await mode('denied');await open();await page.waitForFunction(()=>!document.getElementById('retryBarcodeCamera').disabled);
        await mode('code');await page.locator('#retryBarcodeCamera').click();
        await page.waitForFunction(value=>document.getElementById('field-barcode').value===value,code);await ended();
        results.push('Denied/missing/busy camera messages preserve input; granting permission then Retry recovers');

        await mode('blank');await open();await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
        await page.waitForFunction(()=>document.getElementById('barcodeCameraStatus').textContent.startsWith('Not read yet'),{},{timeout:10000});
        results.push('Unsuccessful scanning gives focus/distance/glare guidance while the camera continues scanning');
        assert.equal(await page.evaluate(()=>window.__cameraRequests.at(-1).audio),false);
        assert.equal(await page.evaluate(()=>window.__cameraRequests.at(-1).video.facingMode.ideal),'environment');
        const before=await page.evaluate(()=>window.__cameraTracks.length);
        await page.locator('#barcodeCameraDevice').selectOption('front');
        await page.waitForFunction(n=>window.__cameraTracks.length>n,before);
        assert.equal(await page.evaluate(()=>window.__cameraTracks[0].readyState),'ended');
        assert.equal(await page.evaluate(()=>window.__cameraRequests.at(-1).video.deviceId.exact),'front');
        assert.equal(await mirror.isChecked(),true);
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>new DOMMatrix(getComputedStyle(e).transform).a),-1);
        await mirror.uncheck();
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>getComputedStyle(e).transform),'none');
        await page.keyboard.press('Escape');await ended();
        assert.equal(await page.locator('[data-camera-target]').evaluate(e=>e===document.activeElement),true);
        results.push('Rear-camera preference, no audio, device switch stops old stream and Escape stops all streams with focus restoration');

        await mode('late');await open();await page.waitForFunction(()=>typeof window.__resolveCamera==='function');
        await page.locator('#closeBarcodeCamera').click();await page.evaluate(()=>window.__resolveCamera());await ended();
        assert.equal(await page.locator('#barcodeCameraDialog').evaluate(e=>e.open),false);
        results.push('Closing during pending permission stops the stream when permission resolves late');

        await page.evaluate(()=> {
            const play = HTMLMediaElement.prototype.play;
            let defer = true;
            HTMLMediaElement.prototype.play = function() {
                const playing = play.call(this);
                if (!defer) return playing;
                defer = false;
                playing.catch(()=>{});
                return new Promise(resolve=>{window.__releaseVideo = resolve;});
            };
        });
        await mode('blank');await open();await page.waitForFunction(()=>typeof window.__releaseVideo==='function');
        await page.locator('#closeBarcodeCamera').click();await open();
        await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
        await page.evaluate(()=>window.__releaseVideo());await page.waitForTimeout(300);
        assert.equal(await page.locator('#barcodeCameraVideo').evaluate(e=>e.srcObject?.getVideoTracks()[0].readyState),'live');
        await page.locator('#cancelBarcodeCamera').click();await ended();
        results.push('Closing/reopening during delayed video initialization isolates decoder previews and keeps the new camera stream alive');

        await mode('blank');await open();await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
        await page.evaluate(()=>{Object.defineProperty(document,'hidden',{configurable:true,value:true});document.dispatchEvent(new Event('visibilitychange'));});
        await ended();assert.equal(await page.locator('#barcodeCameraDialog').evaluate(e=>e.open),false);
        await page.evaluate(()=>delete document.hidden);
        results.push('Backgrounding the page closes the dialog and releases camera tracks');

        await page.locator('[data-barcode-focus]').click();await page.locator('#field-barcode').fill('00000000001');await page.locator('#field-barcode').press('Enter');
        assert.ok(page.url().endsWith('/products/create'));
        await page.locator('#field-name').fill('Camera QA product');await page.locator('#field-sku').fill('CAMERA-QA-1');
        for(const id of ['category_id','product_type_id','unit_id']) await page.locator('#field-'+id).selectOption({index:1});
        await page.locator('#field-cost_price').fill('1000');await page.locator('#field-selling_price').fill('1500');
        await page.locator('#field-current_stock').fill('5');await page.locator('#field-minimum_stock').fill('1');
        await page.locator('#field-barcode').fill(code);
        await page.getByRole('button',{name:'Save Product',exact:true}).click();await page.waitForLoadState('networkidle');
        assert.ok(page.url().endsWith('/products/create'));assert.match(await page.locator('main').innerText(),/barcode.*taken/i);
        await page.locator('#field-barcode').fill(ean);await page.getByRole('button',{name:'Save Product',exact:true}).click();await page.waitForURL(/products$/);
        results.push('Hardware Enter keeps the form open; duplicate scanned barcode is rejected; create succeeds with a unique EAN barcode');

        await go('/pos');assert.equal(await page.locator('[data-camera-target]').isDisabled(),true);
        await page.locator('[name=opening_cash]').fill('10000');await page.locator('form[action$="/pos/open-shift"] button').click();await page.waitForLoadState('networkidle');
        await mode('code');await open();await page.waitForFunction(()=>document.querySelector('[data-pos-quantity]')?.textContent==='1');await ended();
        assert.equal(await page.locator('#barcodeInput').inputValue(),'');
        assert.equal(await page.locator('#barcodeCameraDialog').evaluate(e=>e.open),false);
        await page.waitForTimeout(700);assert.equal(await page.locator('[data-pos-quantity]').textContent(),'1');
        await open();await page.waitForFunction(()=>document.querySelector('[data-pos-quantity]')?.textContent==='2');await ended();
        await page.locator('[data-barcode-focus]').click();await page.locator('#barcodeInput').fill(code);await page.locator('#barcodeInput').press('Enter');
        await page.waitForFunction(()=>document.querySelector('[data-pos-quantity]')?.textContent==='3');
        results.push('POS shift guard, one camera read adds once, reopening adds another unit, and hardware scan shares the same lookup/cart queue');

        await page.route('**/pos/barcode?*',route=>route.fulfill({status:404,json:{message:'Barcode not found QA'}}));
        await open();await page.waitForFunction(()=>document.getElementById('scanState').classList.contains('is-error'));await ended();
        assert.equal(await page.locator('[data-pos-quantity]').textContent(),'3');await page.unroute('**/pos/barcode?*');
        results.push('Unknown camera barcode shows server feedback and preserves existing cart');

        for(const path of ['/pos','/products/create','/products/1/edit']) {
            await go(path);await mode('blank');
            for(const width of [320,375,768,1024,1440]) {
                await page.setViewportSize({width,height:900});
                assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,path+' '+width);
                await open();await page.waitForFunction(()=>!document.getElementById('barcodeCameraDevice').disabled);
                assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,'dialog '+width);
                if([375,1440].includes(width)) await page.screenshot({path:'docs/qa/screenshots/camera-'+(path==='/pos'?'pos':path.includes('create')?'create':'edit')+'-'+width+'.png'});
                await page.locator('#cancelBarcodeCamera').click();await ended();
            }
        }
        results.push('POS/create/edit and camera dialog fit 320/375/768/1024/1440px, with mobile/desktop screenshots');
        const requestsBefore = await page.evaluate(()=>window.__cameraRequests.length);
        await page.evaluate(()=>Object.defineProperty(window,'isSecureContext',{configurable:true,value:false}));
        await open();await page.waitForFunction(()=>document.getElementById('barcodeCameraError').textContent.length>0);
        assert.match(await page.locator('#barcodeCameraError').textContent(),/HTTPS or localhost/);
        assert.equal(await page.evaluate(()=>window.__cameraRequests.length),requestsBefore);
        await page.locator('#cancelBarcodeCamera').click();
        await go('/products/create');
        await page.evaluate(()=>Object.defineProperty(navigator,'mediaDevices',{configurable:true,value:undefined}));
        await open();await page.waitForFunction(()=>document.getElementById('barcodeCameraError').textContent.length>0);
        assert.match(await page.locator('#barcodeCameraError').textContent(),/cannot access a camera/);
        await page.locator('#cancelBarcodeCamera').click();
        results.push('Insecure or unsupported browser has actionable fallback without camera requests; decoder is lazy-loaded only after opening camera');
        assert.deepEqual(errors,[]);
        fs.writeFileSync('docs/qa/camera-results.json',JSON.stringify({status:'PASS',results,errors,scope:'Synthetic MediaStream with real Code 128/EAN-13 decoding; physical camera not tested'},null,2)+'\n');
        console.log(JSON.stringify({status:'PASS',results,errors},null,2));
    } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exitCode=1;});
