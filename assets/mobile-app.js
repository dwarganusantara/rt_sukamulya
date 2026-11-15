(function(){
 document.addEventListener('click',function(event){document.querySelectorAll('details.citizen-account[open],details.cdx-account[open]').forEach(function(menu){if(!menu.contains(event.target))menu.open=false})});
 var sheet=document.getElementById('mbx-sheet'),open=document.getElementById('mbx-more-open'),close=document.getElementById('mbx-more-close');
 if(sheet&&sheet.showModal){
  open.addEventListener('click',function(){sheet.showModal()});
  close.addEventListener('click',function(){sheet.close()});
  // Klik di area gelap menutup menu.
  sheet.addEventListener('click',function(event){if(event.target===sheet)sheet.close()});
  // Chat dibuka di widget halaman ini bila tersedia.
  sheet.querySelectorAll('a[href$="#live-chat"]').forEach(function(link){link.addEventListener('click',function(event){var toggle=document.getElementById('live-chat-toggle');if(toggle){event.preventDefault();sheet.close();if(toggle.getAttribute('aria-expanded')!=='true')toggle.click()}})});
 }else if(open){open.addEventListener('click',function(){location.href=document.querySelector('.mbx-sheet-grid a').href})}
 // Judul kolom untuk tampilan kartu pada tabel warga di HP.
 document.querySelectorAll('.cdx-table').forEach(function(table){
  var heads=[].map.call(table.querySelectorAll('thead th'),function(th){return th.textContent.trim()});
  table.querySelectorAll('tbody tr').forEach(function(row){
   if(row.cells.length!==heads.length)return;
   [].forEach.call(row.cells,function(cell,i){cell.setAttribute('data-label',heads[i])});
  });
 });
})();
