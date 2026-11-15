// Filter tabel di sisi browser. Kontrol: [data-filter=search|from|to|<kolom>]; baris: tr[data-row] dengan data-<kolom>.
(function(){
 document.querySelectorAll('[data-table-filter]').forEach(function(box){
  var body=document.getElementById(box.dataset.tableFilter),controls=[].slice.call(box.querySelectorAll('[data-filter]'));
  if(!body)return;
  var rows=[].slice.call(body.querySelectorAll('tr[data-row]')),empty=body.querySelector('[data-empty-row]');
  function apply(){
   var shown=0;
   rows.forEach(function(row){
    var show=controls.every(function(control){
     var key=control.dataset.filter,value=control.value.trim().toLocaleLowerCase('id');
     if(!value)return true;
     if(key==='search')return (row.dataset.search||'').indexOf(value)>=0;
     if(key==='from')return (row.dataset.date||'')>=value;
     if(key==='to')return (row.dataset.date||'')<=value;
     return row.dataset[key]===value;
    });
    row.hidden=!show;
    var detail=row.nextElementSibling;
    if(!show&&detail&&detail.hasAttribute('data-detail'))detail.hidden=true;
    if(show)shown++;
   });
   if(empty&&rows.length)empty.hidden=shown>0;
  }
  controls.forEach(function(control){control.addEventListener('input',apply);control.addEventListener('change',apply)});
 });
})();
