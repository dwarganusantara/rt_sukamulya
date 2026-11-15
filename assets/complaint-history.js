document.addEventListener('DOMContentLoaded',function(){
 var root=document.querySelector('.pgh');
 if(!root)return;
 var search=root.querySelector('#pgh-search');
 var filter=root.querySelector('#pgh-filter');
 var rows=Array.prototype.slice.call(root.querySelectorAll('.pgh-list>li'));
 var empty=root.querySelector('#pgh-empty');
 function apply(){
  var query=(search.value||'').toLocaleLowerCase('id').trim();
  var status=filter.value;
  var visible=0;
  rows.forEach(function(row){
   var show=(status==='all'||row.dataset.pghStatus===status)&&(!query||(row.dataset.pghSearch||'').indexOf(query)!==-1);
   row.hidden=!show;if(show)visible++;
  });
  empty.hidden=visible!==0;
  if(!visible&&rows.length)empty.textContent='Tidak ada pengaduan yang cocok.';
 }
 search.addEventListener('input',apply);
 filter.addEventListener('change',apply);
});
