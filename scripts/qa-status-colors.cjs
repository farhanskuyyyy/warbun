const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.QA_BASE || 'http://127.0.0.1:8765';
assert.ok(['127.0.0.1','localhost'].includes(new URL(base).hostname));
const palette = {
    pending:['rgb(133, 77, 14)','rgb(254, 243, 199)'],
    confirmed:['rgb(30, 64, 175)','rgb(219, 234, 254)'],
    preparing:['rgb(107, 33, 168)','rgb(243, 232, 255)'],
    ready:['rgb(17, 94, 89)','rgb(204, 251, 241)'],
    completed:['rgb(22, 101, 52)','rgb(220, 252, 231)'],
    paid:['rgb(22, 101, 52)','rgb(220, 252, 231)'],
    cancelled:['rgb(153, 27, 27)','rgb(254, 226, 226)'],
    refunded:['rgb(71, 85, 105)','rgb(226, 232, 240)'],
    debt:['rgb(154, 52, 18)','rgb(255, 237, 213)'],
    partial:['rgb(154, 52, 18)','rgb(255, 237, 213)'],
};
(async()=>{
    const browser=await chromium.launch({channel:'chrome',headless:true});
    const page=await browser.newPage(); const errors=[]; const results=[];
    page.on('pageerror',error=>errors.push(error.message));
    try {
        await page.goto(base+'/login');
        await page.locator('[name=email]').fill('admin@warbun.local');
        await page.locator('[name=password]').fill('password');
        await page.locator('form[action$="/login"] button[type=submit]').click();
        await page.waitForURL(/dashboard/);
        for(const path of ['/orders/monitor','/orders','/pos/history']) {
            assert.equal((await page.goto(base+path)).status(),200);
            await page.waitForLoadState('networkidle');
            const badges=await page.locator('[data-transaction-status]').evaluateAll(elements=>elements.map(el=>({status:el.dataset.transactionStatus,label:el.textContent.trim(),summary:!!el.closest('.monitor-stages'),color:getComputedStyle(el).color,background:getComputedStyle(el).backgroundColor})));
            assert.ok(badges.length>0);
            for(const badge of badges) {assert.ok(badge.label);assert.equal(badge.color,palette[badge.status][0]);assert.equal(badge.background,badge.summary?'rgba(0, 0, 0, 0)':palette[badge.status][1]);}
            for(const width of [320,375,768,1024,1440]) {
                await page.setViewportSize({width,height:1000});
                await page.evaluate(()=>new Promise(r=>{scrollTo(0,0);requestAnimationFrame(()=>requestAnimationFrame(r));}));
                assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,path+' '+width);
                if(path==='/orders/monitor' && [375,1440].includes(width))await page.screenshot({path:`docs/qa/screenshots/status-monitor-${width}.png`,fullPage:true});
            }
            results.push(path+': actual status badges and five responsive widths PASS');
        }
        await page.goto(base+'/orders/monitor');
        const details=await page.locator('[data-monitor-source=online] a[href*="/orders/"]').first().getAttribute('href');
        await page.goto(details);
        assert.equal(await page.locator('.order-detail-layout [data-transaction-status]').count(),2);
        results.push('Order detail displays separate colored fulfillment and payment statuses');
        await page.goto(base+'/orders/monitor');
        const pending=page.locator('.monitor-stages a.status-tone-pending');
        await pending.focus(); await page.keyboard.press('Enter');
        await page.waitForURL(/status=pending/);
        assert.equal(await page.locator('.monitor-stages a.status-tone-pending').getAttribute('aria-current'),'page');
        const color=await page.locator('.monitor-stages a.status-tone-pending').evaluate(el=>getComputedStyle(el).color);
        assert.equal(color,palette.pending[0]);
        results.push('Keyboard status filter and selected semantic color work');
        const swatches=await page.evaluate(statuses=>statuses.map(status=>{
            const el=document.createElement('span');el.className='transaction-status status-tone-'+status;el.textContent=status;document.body.append(el);
            const style=getComputedStyle(el);const result={status,color:style.color,background:style.backgroundColor};el.remove();return result;
        }),Object.keys(palette));
        for(const swatch of swatches)assert.deepEqual([swatch.color,swatch.background],palette[swatch.status]);
        results.push('All ten status variants render distinct semantic palette groups (temporary DOM swatches only)');
        await page.goto(base+'/orders/monitor?status=refunded');
        const refunds=page.locator('[data-monitor-source=pos] .monitor-payment [data-transaction-status]');
        if(await refunds.count())assert.ok((await refunds.evaluateAll(items=>items.map(el=>el.dataset.transactionStatus))).every(status=>status==='refunded'));
        assert.deepEqual(errors,[]);
        fs.writeFileSync('docs/qa/status-color-results.json',JSON.stringify({status:'PASS',results,errors},null,2)+'\n');
        console.log(JSON.stringify({status:'PASS',results,errors},null,2));
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
