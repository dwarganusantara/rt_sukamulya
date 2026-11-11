(() => {
 document.querySelectorAll('[data-ops-password]').forEach(button=>button.addEventListener('click',()=>{
  const input=document.getElementById(button.dataset.opsPassword);if(!input)return;const show=input.type==='password';input.type=show?'text':'password';input.toggleAttribute('data-visible-password',show);button.setAttribute('aria-pressed',String(show));button.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');
 }));
 document.querySelectorAll('[data-ops-filter]').forEach(group=>{
  const table=document.getElementById(group.dataset.opsFilter);if(!table)return;
  const search=group.querySelector('[data-ops-search]'),category=group.querySelector('[data-ops-category]');
  const rows=[...table.querySelectorAll('tbody tr')].filter(row=>!row.querySelector('.ops-empty'));
  const count=document.querySelector('[data-ops-count="'+table.id+'"]');
  const empty=document.createElement('p');empty.className='ops-local-empty';empty.textContent='Tidak ada data yang sesuai pencarian.';empty.hidden=true;table.closest('.ops-table-wrap').after(empty);
  const filter=()=>{const q=(search?.value||'').trim().toLocaleLowerCase('id');let total=0;rows.forEach(row=>{const visible=(!q||row.textContent.toLocaleLowerCase('id').includes(q))&&(!category?.value||row.dataset.category===category.value);row.hidden=!visible;if(visible)total++;});empty.hidden=!rows.length||total>0;if(count)count.textContent='Menampilkan '+total+' dari '+rows.length+' data';};
  search?.addEventListener('input',filter);category?.addEventListener('change',filter);filter();
 });
 document.querySelectorAll('[data-ops-focus]').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById(button.dataset.opsFocus);if(input){input.scrollIntoView({behavior:'smooth',block:'center'});input.focus({preventScroll:true});}}));
})();

document.querySelectorAll('[data-ops-dismiss]').forEach(button=>button.addEventListener('click',()=>{button.closest('.ops-dismissible').hidden=true;}));

document.querySelectorAll('[data-status-tabs]').forEach(tabs=>{
 const table=document.getElementById(tabs.dataset.statusTabs);if(!table)return;const rows=[...table.querySelectorAll('tbody tr[data-status]')];const empty=document.createElement('p');empty.className='ops-status-empty';empty.hidden=true;empty.textContent='Tidak ada field pada status ini.';table.closest('.ops-table-wrap').after(empty);
 tabs.querySelectorAll('[data-status]').forEach(button=>button.addEventListener('click',()=>{let count=0;const status=button.dataset.status;rows.forEach(r=>{r.hidden=status!=='all'&&r.dataset.status!==status;if(!r.hidden)count++});tabs.querySelectorAll('button').forEach(b=>{b.classList.toggle('is-active',b===button);b.setAttribute('aria-pressed',String(b===button))});empty.hidden=!rows.length||count>0;}));
});
document.querySelectorAll('[data-handover-period]').forEach(select=>{const table=document.getElementById(select.dataset.handoverPeriod);if(!table)return;const rows=[...table.querySelectorAll('tbody tr[data-period]')];const empty=document.createElement('p');empty.className='ops-status-empty';empty.textContent='Tidak ada sesi pada periode ini.';empty.hidden=true;table.closest('.ops-table-wrap').after(empty);select.addEventListener('change',()=>{let count=0;rows.forEach(r=>{r.hidden=!!select.value&&r.dataset.period!==select.value;if(!r.hidden)count++});empty.hidden=!rows.length||count>0;});});
const settingsFilter=document.querySelector('[data-settings-history-filter]');if(settingsFilter){const rows=[...document.querySelectorAll('#ops-settings-history-table tbody tr[data-period]')],q=settingsFilter.querySelector('[data-history-search]'),period=settingsFilter.querySelector('[data-history-period]'),count=document.querySelector('[data-history-count]');const empty=document.createElement('p');empty.className='ops-status-empty';empty.hidden=true;empty.textContent='Tidak ada perubahan yang cocok.';document.querySelector('#ops-settings-history-table').closest('.ops-table-wrap').after(empty);const apply=()=>{let n=0;rows.forEach(r=>{r.hidden=!!((q.value.trim()&&!r.textContent.toLocaleLowerCase('id').includes(q.value.trim().toLocaleLowerCase('id')))||(period.value&&r.dataset.period!==period.value));if(!r.hidden)n++});count.textContent='Total '+n+' perubahan';empty.hidden=!rows.length||n>0;};settingsFilter.querySelector('[data-history-apply]').addEventListener('click',apply);settingsFilter.querySelector('[data-history-reset]').addEventListener('click',()=>{q.value='';period.value='';apply()});q.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();apply()}});}

// Pop-up aksi tabel ditempatkan di viewport agar tidak terpotong wadah tabel.
(() => {
 const menus=[...document.querySelectorAll('.ops-row-menu')];
 const closeAll=except=>menus.forEach(m=>{if(m!==except)m.open=false});
 menus.forEach(menu=>menu.addEventListener('toggle',()=>{if(!menu.open)return;closeAll(menu);const box=menu.querySelector('summary').getBoundingClientRect(),nav=menu.querySelector('nav');const width=200;nav.style.left=Math.max(8,Math.min(innerWidth-width-8,box.right-width))+'px';nav.style.right='auto';nav.style.width=width+'px';nav.style.top=Math.max(8,Math.min(innerHeight-nav.getBoundingClientRect().height-8,box.bottom+5))+'px';}));
 document.addEventListener('click',e=>{if(!e.target.closest('.ops-row-menu'))closeAll()});
 document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAll()});
 addEventListener('resize',()=>closeAll());addEventListener('scroll',()=>closeAll(),true);
})();
