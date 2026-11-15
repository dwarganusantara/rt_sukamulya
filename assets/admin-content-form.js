(function(){
 var MONTHS=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
 var $=function(selector,root){return (root||document).querySelector(selector)};
 var $$=function(selector,root){return Array.prototype.slice.call((root||document).querySelectorAll(selector))};

 $$('[data-preview-text]').forEach(function(field){
  var target=$(field.dataset.previewText);
  if(!target)return;
  field.addEventListener('input',function(){target.textContent=field.value.trim()||field.dataset.empty||''});
 });

 $$('[data-counter]').forEach(function(counter){
  var field=document.getElementById(counter.dataset.counter),out=$('span',counter);
  if(!field)return;
  field.addEventListener('input',function(){out.textContent=field.value.length});
 });

 // Preview halaman memperlihatkan isi tanpa tanda format.
 var markup=$('[data-preview-markup]');
 if(markup){
  var body=$(markup.dataset.previewMarkup),bars=$('#preview-bars');
  var renderBody=function(){
   var text=markup.value.replace(/\[([^\]]+)\]\([^)]+\)/g,'$1').replace(/\*+/g,'').replace(/^\s*(-|\d+\.)\s+/gm,'').trim();
   body.textContent=text;body.hidden=!text;bars.hidden=!!text;
  };
  markup.addEventListener('input',renderBody);
  renderBody();
 }

 // Agenda
 var dateField=$('#post-date'),timeField=$('#post-time');
 if(dateField){
  var renderDate=function(){
   var parts=dateField.value.split('-');
   $('#preview-day').textContent=parts.length===3?parseInt(parts[2],10):'--';
   $('#preview-month').textContent=parts.length===3?MONTHS[parseInt(parts[1],10)-1]+' '+parts[0]:'Bulan';
  };
  dateField.addEventListener('input',renderDate);renderDate();
 }
 if(timeField){
  var renderTime=function(){$('#preview-time').textContent=timeField.value?timeField.value+' WIB':'--:--'};
  timeField.addEventListener('input',renderTime);renderTime();
 }

 // Unggah gambar
 $$('.cfx-upload').forEach(function(box){
  var input=$('input[type=file]',box),name=$('[data-file-name]',box),hint=name?name.textContent:'',images=$$('[data-image-preview]'),empties=$$('[data-image-empty]');
  var savedSrc=images.length?images[0].getAttribute('src'):'',pending=null;
  var consent=$('input[name=photo_consent]');
  function show(src){
   if(consent)consent.required=!!pending;
   images.forEach(function(img){img.hidden=!src;if(src)img.src=src;else img.removeAttribute('src')});
   empties.forEach(function(el){el.hidden=!!src});
  }
  function clear(message){
   if(pending)URL.revokeObjectURL(pending);
   pending=null;input.value='';show(savedSrc);name.textContent=hint;box.classList.remove('has-file');
   if(message)alert(message);
  }
  function take(){
   var file=input.files[0];
   if(!file)return clear();
   if(['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/avif'].indexOf(file.type)<0)return clear('Format gambar harus JPG, PNG, WebP, GIF, BMP, atau AVIF.');
   if(file.size>4*1024*1024)return clear('Ukuran gambar maksimal 4 MB.');
   if(pending)URL.revokeObjectURL(pending);
   pending=URL.createObjectURL(file);show(pending);name.textContent=file.name;box.classList.add('has-file');
  }
  input.addEventListener('change',take);
  ['dragenter','dragover'].forEach(function(type){box.addEventListener(type,function(event){event.preventDefault();box.classList.add('dragging')})});
  ['dragleave','drop'].forEach(function(type){box.addEventListener(type,function(event){event.preventDefault();box.classList.remove('dragging')})});
  box.addEventListener('drop',function(event){if(event.dataTransfer.files.length){input.files=event.dataTransfer.files;take()}});
 });

 // Toolbar editor halaman
 var editor=$('#page-editor');
 if(editor){
  var area=$('textarea',editor),linkBar=$('#page-link-bar'),linkUrl=$('#page-link-url'),range=[0,0];
  var changed=function(){area.dispatchEvent(new Event('input',{bubbles:true}))};
  var replace=function(start,end,text,selectFrom,selectTo){
   area.value=area.value.slice(0,start)+text+area.value.slice(end);
   area.focus();area.setSelectionRange(selectFrom,selectTo);changed();
  };
  var wrap=function(mark,fallback){
   var start=area.selectionStart,end=area.selectionEnd,text=area.value.slice(start,end)||fallback;
   replace(start,end,mark+text+mark,start+mark.length,start+mark.length+text.length);
  };
  var list=function(numbered){
   var start=area.value.lastIndexOf('\n',area.selectionStart-1)+1,end=area.selectionEnd,nl=area.value.indexOf('\n',end);
   end=nl<0?area.value.length:nl;
   var text=area.value.slice(start,end).split('\n').map(function(line,i){return (numbered?(i+1)+'. ':'- ')+line}).join('\n');
   replace(start,end,text,start,start+text.length);
  };
  var closeLink=function(){linkBar.hidden=true;linkUrl.value=''};
  var addLink=function(){
   var url=linkUrl.value.trim();
   if(!/^https?:\/\/\S+$/.test(url)){linkUrl.focus();linkUrl.setCustomValidity('Alamat harus diawali http:// atau https://');linkUrl.reportValidity();return}
   var label=area.value.slice(range[0],range[1])||'teks tautan',text='['+label+']('+url+')';
   closeLink();replace(range[0],range[1],text,range[0]+1,range[0]+1+label.length);
  };
  $('.cfx-toolbar',editor).addEventListener('click',function(event){
   var button=event.target.closest('button');
   if(!button)return;
   var action=button.dataset.format;
   if(action==='bold')wrap('**','teks tebal');
   if(action==='italic')wrap('*','teks miring');
   if(action==='ul')list(false);
   if(action==='ol')list(true);
   if(action==='link'){range=[area.selectionStart,area.selectionEnd];linkBar.hidden=false;linkUrl.focus()}
   if(action==='full')editor.classList.toggle('is-full');
  });
  linkUrl.addEventListener('input',function(){linkUrl.setCustomValidity('')});
  linkUrl.addEventListener('keydown',function(event){if(event.key==='Enter'){event.preventDefault();addLink()}if(event.key==='Escape')closeLink()});
  $('#page-link-add').addEventListener('click',addLink);
  $('#page-link-cancel').addEventListener('click',closeLink);
  document.addEventListener('keydown',function(event){if(event.key==='Escape')editor.classList.remove('is-full')});
 }
})();
