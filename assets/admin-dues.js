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

 var more=$('dux-pay-more');
 if(more)more.addEventListener('click',function(){
  var open=more.getAttribute('aria-expanded')==='true';
  document.querySelectorAll('#dux-pay-body .dux-extra').forEach(function(row){row.hidden=open});
  more.setAttribute('aria-expanded',open?'false':'true');
  more.querySelector('span').textContent=open?more.dataset.more:more.dataset.less;
 });

 var periodFilter=$('dux-filter-period'),search=$('dux-filter-search'),emptyRow=$('dux-due-empty');
 var rows=Array.prototype.slice.call(document.querySelectorAll('#dux-due-body tr[data-period]'));
 function applyFilter(){
  var period=periodFilter.value,q=search.value.trim().toLowerCase(),visible=0;
  rows.forEach(function(row){var show=(!period||row.dataset.period===period)&&(!q||row.dataset.name.indexOf(q)>=0);row.hidden=!show;if(show)visible++});
  if(rows.length)emptyRow.hidden=visible>0;
 }
 periodFilter.addEventListener('change',applyFilter);search.addEventListener('input',applyFilter);

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
