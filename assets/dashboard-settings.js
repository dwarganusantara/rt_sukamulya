(function(){
'use strict';
var form=document.getElementById('dashboard-settings-form');if(!form)return;
var box=document.getElementById('dws-rows'),preview=document.getElementById('dws-preview'),grid=preview.querySelector('.dwi-grid');
var svg=JSON.parse(document.getElementById('dws-svg-data').textContent),images=JSON.parse(document.getElementById('dws-image-data').textContent);
function rows(){return Array.from(box.querySelectorAll('.dws-row'));}
function field(row,name){return row.querySelector('[data-field="'+name+'"]');}
function update(){
 grid.replaceChildren();var list=rows().sort(function(a,b){return Number(field(a,'sort_order').value)-Number(field(b,'sort_order').value)||a.dataset.key.localeCompare(b.dataset.key);});
 list.forEach(function(row){if(row.dataset.allowed!=='1'||!field(row,'show_on_dashboard').checked)return;
  var a=document.createElement('a'),icon=document.createElement('span'),label=document.createElement('strong'),key=field(row,'icon').value;
  a.href=row.dataset.route;a.className='dwi-tile tone-'+field(row,'color').value;a.dataset.menuKey=row.dataset.key;a.dataset.icon=key;icon.className='dwi-icon';icon.setAttribute('aria-hidden','true');
  var tpl=document.getElementById('dwi-hex-tpl');if(tpl)a.append(tpl.content.cloneNode(true));
  if(images[key]){var img=document.createElement('img');img.src=images[key];img.alt='';icon.append(img);}else{icon.innerHTML=svg[key]||'';}
  label.textContent=field(row,'label').value;a.setAttribute('aria-label',label.textContent);a.append(icon,label);grid.append(a);
 });
 if(!grid.children.length){var p=document.createElement('p');p.className='dwi-empty';p.textContent='Belum ada layanan yang tersedia. Hubungi pengurus RT.';grid.append(p);}
}
function reorder(){rows().forEach(function(row,i){field(row,'sort_order').value=(i+1)*10;});update();}
box.addEventListener('click',function(e){var b=e.target.closest('[data-move]');if(!b)return;var row=b.closest('.dws-row'),all=rows(),index=all.indexOf(row),target=all[index+(b.dataset.move==='up'?-1:1)];if(!target)return;if(b.dataset.move==='up')box.insertBefore(row,target);else box.insertBefore(target,row);reorder();b.focus();});
var dragged=null;box.addEventListener('dragstart',function(e){var handle=e.target.closest('.dws-drag');if(!handle)return;dragged=handle.closest('.dws-row');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',dragged.dataset.key);});box.addEventListener('dragover',function(e){if(dragged&&e.target.closest('.dws-row'))e.preventDefault();});box.addEventListener('drop',function(e){var target=e.target.closest('.dws-row');if(!dragged||!target||target===dragged)return;e.preventDefault();var rect=target.getBoundingClientRect();box.insertBefore(dragged,e.clientY>rect.top+rect.height/2?target.nextSibling:target);dragged=null;reorder();});box.addEventListener('dragend',function(){dragged=null;});
form.addEventListener('input',update);form.addEventListener('change',update);
document.getElementById('dws-reset').addEventListener('click',function(e){if(!confirm('Kembalikan label, ikon, warna, urutan, dan visibilitas dashboard ke default? Status fitur dan data warga tetap tersimpan.'))e.preventDefault();});
function filter(){var q=document.getElementById('dws-search').value.toLocaleLowerCase('id'),category=document.getElementById('dws-category').value;rows().forEach(function(row){row.hidden=!(field(row,'label').value.toLocaleLowerCase('id').includes(q)&&(!category||field(row,'category').value===category));});}
document.getElementById('dws-search').addEventListener('input',filter);document.getElementById('dws-category').addEventListener('change',filter);
document.querySelectorAll('[data-preview]').forEach(function(b){b.addEventListener('click',function(){preview.classList.toggle('is-mobile',b.dataset.preview==='mobile');document.querySelectorAll('[data-preview]').forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false');});});});
update();
})();