(function(){
 var $=function(id){return document.getElementById(id)};

 // Banner pembayaran lama: tutup diingat sampai nominalnya berubah.
 var legacy=$('dux-legacy');
 if(legacy){
  var key='sukamulya.dues.legacy';
  try{if(localStorage.getItem(key)===legacy.dataset.key)legacy.hidden=true}catch(e){}
  $('dux-legacy-close').addEventListener('click',function(){legacy.hidden=true;try{localStorage.setItem(key,legacy.dataset.key)}catch(e){}});
 }

 var summaryPeriod=$('dux-summary-period');
 summaryPeriod.addEventListener('change',function(){location.href=summaryPeriod.dataset.url+'?period='+encodeURIComponent(summaryPeriod.value)});

 // Dua tabel utama ditampilkan sebagai tab agar halaman tetap ringkas.
 var tabs=Array.prototype.slice.call(document.querySelectorAll('[data-dux-tab]'));
 var panels=Array.prototype.slice.call(document.querySelectorAll('[data-dux-panel]'));
 function activateTab(name,updateUrl){
  if(name!=='tagihan')name='pembayaran';
  tabs.forEach(function(tab){
   var active=tab.dataset.duxTab===name;
   tab.setAttribute('aria-selected',active?'true':'false');
   tab.setAttribute('tabindex',active?'0':'-1');
  });
  panels.forEach(function(panel){panel.hidden=panel.dataset.duxPanel!==name});
  if(updateUrl&&history.replaceState)history.replaceState(null,'',location.pathname+location.search+'#'+name);
 }
 if(tabs.length&&panels.length){
  activateTab(location.hash==='#tagihan'?'tagihan':'pembayaran',false);
  tabs.forEach(function(tab,index){
   tab.addEventListener('click',function(event){event.preventDefault();activateTab(tab.dataset.duxTab,true)});
   tab.addEventListener('keydown',function(event){
    if(event.key!=='ArrowLeft'&&event.key!=='ArrowRight')return;
    event.preventDefault();var next=(index+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;
    activateTab(tabs[next].dataset.duxTab,true);tabs[next].focus();
   });
  });
  window.addEventListener('hashchange',function(){activateTab(location.hash==='#tagihan'?'tagihan':'pembayaran',false)});
 }

 // Filter kategori/periode/pencarian & halaman kini diproses server (lihat Admin::dues); tidak ada filter di browser.

 // Menu ⋮ memakai posisi fixed agar tidak terpotong oleh tabel yang bisa di-scroll.
 var menus=Array.prototype.slice.call(document.querySelectorAll('.dux-menu'));
 function closeAll(except){menus.forEach(function(menu){if(menu!==except)menu.open=false})}
 menus.forEach(function(menu){
  menu.addEventListener('toggle',function(){
   if(!menu.open)return;
   closeAll(menu);
   var box=menu.querySelector('.dux-menu-box'),rect=menu.querySelector('summary').getBoundingClientRect(),width=box.offsetWidth,height=box.offsetHeight;
   box.style.left=Math.max(8,Math.min(rect.right-width,innerWidth-width-8))+'px';
   box.style.top=(rect.bottom+height+8>innerHeight?Math.max(8,rect.top-height-6):rect.bottom+6)+'px';
  });
 });
 document.addEventListener('click',function(event){if(!event.target.closest('.dux-menu'))closeAll()});
 document.addEventListener('keydown',function(event){if(event.key==='Escape')closeAll()});
 window.addEventListener('resize',function(){closeAll()});
 window.addEventListener('scroll',function(){closeAll()},true);

 // Warga perlu tahu alasan pengembalian.
 document.querySelectorAll('[data-needs-note]').forEach(function(button){
  button.addEventListener('click',function(event){
   var note=button.form.querySelector('[name=review_note]');
   if(!note.value.trim()){event.preventDefault();note.setCustomValidity('Tulis alasan pengembalian untuk warga.');note.reportValidity();note.focus()}
  });
 });
 document.querySelectorAll('[name=review_note]').forEach(function(note){note.addEventListener('input',function(){note.setCustomValidity('')})});
})();

// Panel filter Daftar Tagihan: bisa dibuka/tutup (diingat per browser).
(function(){
 var fp=document.getElementById('dux-fp'),btn=document.getElementById('dux-fp-fold');if(!fp||!btn)return;
 var key='sukamulya.dues.filter.collapsed';
 function set(c,save){fp.classList.toggle('is-collapsed',c);btn.setAttribute('aria-expanded',c?'false':'true');btn.title=c?'Tampilkan filter':'Sembunyikan filter';if(save)try{localStorage.setItem(key,c?'1':'0')}catch(e){}}
 try{set(localStorage.getItem(key)==='1',false)}catch(e){}
 btn.addEventListener('click',function(){set(!fp.classList.contains('is-collapsed'),true)});
})();
