const fs=require('fs');const{chromium}=require('C:/Users/chendy/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{const creds=JSON.parse(fs.readFileSync('tmp/adminqa/sub-browser.json','utf8'));const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
for(const width of[1672,1024,768,390,320]){
 const p=await browser.newPage({viewport:{width,height:941}});const errors=[];p.on('pageerror',e=>errors.push(e.message));
 await p.goto('http://127.0.0.1:8097/admin/login');await p.locator('[name=username]').fill(creds.username);await p.locator('[name=password]').fill(creds.password);await Promise.all([p.waitForURL('**/admin'),p.locator('button[type=submit]').first().click()]);
 await p.goto('http://127.0.0.1:8097/admin/langganan');await p.evaluate(async()=>{document.querySelectorAll('dialog[open]').forEach(d=>d.close());await document.fonts.ready;document.querySelector('.ad-account-name strong').textContent='Admin';});
 if(await p.locator('.sp-plan').count()!==3)throw Error('Dynamic package cards missing');if(await p.locator('.sp-stat strong').nth(2).textContent()==='5 dari 12 Fitur')throw Error('Static example statistics');
 const wa=await p.locator('.sp-pro .sp-plan-choose').getAttribute('href');if(!wa.includes('6285697578958')||!decodeURIComponent(wa).includes('Pro'))throw Error('Wrong dynamic package contact');
 if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Page overflow '+width);
 await p.screenshot({path:`tmp/family-preview/subscription-page-${width}.png`,fullPage:width<1000});
 await p.getByRole('button',{name:'? Panduan'}).click();if(!await p.locator('#sp-help').evaluate(d=>d.open))throw Error('Guide not working');await p.keyboard.press('Escape');
 // Exercise real locked feature entitlement endpoint/modal and navigation.
 await p.locator('.sp-feature-locked[data-sub-feature="family_card"]').click();await p.locator('#subscription-modal').waitFor({state:'visible'});if(!await p.locator('[data-sub-message]').textContent().then(t=>t.includes('Verifikasi KK')))throw Error('Wrong feature in modal');await p.keyboard.press('Escape');
 await p.locator('.sp-tabs a').nth(1).click();await p.locator('[data-sp-search]').fill('mutasi');if(await p.locator('[data-sp-row]:visible').count()!==1)throw Error('Feature search '+JSON.stringify(await p.locator('[data-sp-row]').evaluateAll(es=>es.map(e=>({text:e.dataset.spText,hidden:e.hidden,display:getComputedStyle(e).display}))))+' errors='+errors.join(','));await p.locator('[data-sp-search]').fill('');await p.locator('[data-sp-filter]').selectOption('premium');if(await p.locator('[data-sp-row]:visible').count()<3)throw Error('Status filter');
 await Promise.all([p.waitForURL('**/admin/langganan?tab=riwayat'),p.locator('.sp-tabs a').nth(2).click()]);await p.getByRole('heading',{name:'Belum ada riwayat langganan'}).waitFor();if(!await p.getByRole('heading',{name:'Belum ada riwayat langganan'}).isVisible())throw Error('History empty state');if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('History overflow');
 if(errors.length)throw Error(errors.join(','));console.log('PASS packages, summary, guide, locked access, tabs, search, history, responsive',width);await p.close();
}await browser.close();})();
