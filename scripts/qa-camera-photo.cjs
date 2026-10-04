const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const {DecodeHintType} = require('@zxing/library');
const fs = require('node:fs');

(async () => {
    if (!process.env.QA_CAMERA_IMAGE) throw new Error('Set QA_CAMERA_IMAGE to a local photograph or screenshot.');
    const manifest = JSON.parse(fs.readFileSync('public/build/manifest.json', 'utf8'));
    const decoder = manifest['node_modules/@zxing/browser/esm/index.js'].file;
    const browser = await chromium.launch({channel:'chrome', headless:true});
    try {
        const page = await browser.newPage();
        await page.goto('http://127.0.0.1:8080/login');
        const results = await page.evaluate(async ({source, decoder, hint}) => {
            const {BrowserMultiFormatOneDReader} = await import('/build/' + decoder);
            const image = new Image(); image.src = source; await image.decode();
            const canvas = document.createElement('canvas');
            canvas.width = image.width; canvas.height = image.height;
            canvas.getContext('2d').drawImage(image, 0, 0);
            return [false, true].map(harder => {
                const reader = new BrowserMultiFormatOneDReader(new Map([[hint, harder]]));
                try { return {tryHarder:harder, decoded:true, barcode:reader.decodeFromCanvas(canvas).getText()}; }
                catch { return {tryHarder:harder, decoded:false}; }
            });
        }, {source:'data:image/png;base64,' + fs.readFileSync(process.env.QA_CAMERA_IMAGE).toString('base64'), decoder, hint:DecodeHintType.TRY_HARDER});
        console.log(JSON.stringify({scope:'Provided still image through production decoder; no physical camera test', results}, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
