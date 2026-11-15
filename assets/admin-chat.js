(function(){
 var cfg=window.CHX_CONFIG,$=function(id){return document.getElementById(id)};
 var MONTHS=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
 var people=$('chx-people'),search=$('chx-search'),sort=$('chx-sort'),none=$('chx-none'),tab='all';

 function rows(){return Array.prototype.slice.call(people.querySelectorAll('.chx-person'))}
 function applyList(){
  var q=search.value.toLocaleLowerCase('id').trim(),visible=0;
  rows().forEach(function(row){
   var show=(!q||row.dataset.name.indexOf(q)>=0)&&(tab!=='online'||row.dataset.online==='1')&&(tab!=='unread'||Number(row.dataset.unread)>0);
   row.hidden=!show;if(show)visible++;
  });
  if(rows().length){none.hidden=visible>0;none.textContent='Tidak ada warga yang cocok.'}
 }
 search.addEventListener('input',applyList);
 document.querySelectorAll('.chx-tabs button').forEach(function(button){
  button.addEventListener('click',function(){
   tab=button.dataset.tab;
   document.querySelectorAll('.chx-tabs button').forEach(function(other){var on=other===button;other.classList.toggle('active',on);other.setAttribute('aria-selected',on?'true':'false')});
   applyList();
  });
 });
 var order=rows().map(function(row){return row.dataset.id});
 function applySort(){
  var byName=sort.dataset.mode==='name',list=rows();
  list.sort(byName?function(a,b){return a.dataset.name.localeCompare(b.dataset.name,'id')}:function(a,b){return order.indexOf(a.dataset.id)-order.indexOf(b.dataset.id)});
  list.forEach(function(row){people.insertBefore(row,none)});
 }
 sort.addEventListener('click',function(){
  var toName=sort.dataset.mode!=='name';
  sort.dataset.mode=toName?'name':'recent';sort.classList.toggle('active',toName);
  sort.title=toName?'Urutan: nama. Klik untuk urut terbaru':'Urutan: terbaru. Klik untuk urut nama';
  applySort();
 });

 function timeLabel(at){
  if(!at)return '';
  var day=at.slice(0,10),yesterday=new Date(new Date(cfg.today+'T00:00:00Z').getTime()-864e5).toISOString().slice(0,10);
  if(day===cfg.today)return at.slice(11,16);
  if(day===yesterday)return 'Kemarin';
  return parseInt(day.slice(8),10)+' '+MONTHS[parseInt(day.slice(5,7),10)-1];
 }
 function setBadge(value){
  document.querySelectorAll('.sbx-line[data-key="admin/chat"] .sbx-row').forEach(function(row){
   var badge=row.querySelector('.sbx-badge');
   if(!value){if(badge)badge.remove();return}
   if(!badge){badge=document.createElement('span');badge.className='sbx-badge';row.appendChild(badge)}
   badge.textContent=value;
  });
 }
 function setStatus(online){
  document.querySelectorAll('.chx-window-head .chx-dot,.chx-profile .chx-dot,.chx-row .chx-dot').forEach(function(dot){dot.classList.toggle('on',online)});
  document.querySelectorAll('[data-status]').forEach(function(el){el.classList.toggle('on',online);el.querySelector('span').textContent=online?'Online':'Sedang offline'});
 }
 function updateConversations(list){
  var known={},online=0,unreadRows=0,unreadTotal=0,unknown=false;
  rows().forEach(function(row){known[row.dataset.id]=row});
  list.forEach(function(item){
   var row=known[item.id],isOnline=Number(item.is_online)===1,unread=Number(item.unread)||0;
   if(isOnline)online++;if(unread)unreadRows++;unreadTotal+=unread;
   if(!row){unknown=true;return}
   row.dataset.online=isOnline?'1':'0';row.dataset.unread=unread;
   row.querySelector('.chx-dot').classList.toggle('on',isOnline);
   var text=item.last_message||'Belum ada pesan';
   row.querySelector('[data-preview]').textContent=text.length>42?text.slice(0,41)+'…':text;
   row.querySelector('[data-time]').textContent=timeLabel(item.last_message_time);
   var badge=row.querySelector('[data-badge]');badge.hidden=!unread;badge.textContent=unread;
   if(Number(item.id)===cfg.selected)setStatus(isOnline);
  });
  order=list.map(function(item){return String(item.id)});
  $('chx-stat-online').textContent=online;$('chx-online-pill').textContent=online;$('chx-tab-online').textContent=online;
  $('chx-stat-total').textContent=list.length;$('chx-tab-all').textContent=list.length;
  $('chx-stat-unread').textContent=unreadTotal;$('chx-tab-unread').textContent=unreadRows;
  setBadge(unreadTotal);
  applySort();applyList();
  // Warga baru memulai chat: muat ulang bila admin tidak sedang mengetik.
  var input=document.querySelector('#chx-form textarea');
  if(unknown&&(!input||!input.value))location.reload();
 }

 if(!cfg.selected)return;
 var box=$('chx-messages'),form=$('chx-form'),input=form.elements.message,start=$('chx-start'),sendError=$('chx-send-error'),last=cfg.last,busy=false;
 function nearBottom(){return box.scrollHeight-box.scrollTop-box.clientHeight<120}
 function bottom(){box.scrollTop=box.scrollHeight}
 function ensureDay(day){
  var days=box.querySelectorAll('.chx-day'),latest=days.length?days[days.length-1].dataset.day:'';
  if(latest===day)return;
  var el=document.createElement('div'),label=document.createElement('span');
  el.className='chx-day';el.dataset.day=day;
  label.textContent=day===cfg.today?'Hari ini':parseInt(day.slice(8),10)+' '+MONTHS[parseInt(day.slice(5,7),10)-1]+' '+day.slice(0,4);
  el.append(label);box.insertBefore(el,start);
 }
 function miniAvatar(){
  var a=document.createElement('span');a.className='chx-avatar mini tone-'+cfg.citizenTone;
  if(cfg.citizenAvatar){var img=document.createElement('img');img.src=cfg.citizenAvatar;img.alt='';a.append(img)}else a.textContent=cfg.citizenInitial;
  var dot=document.createElement('i');dot.className='chx-dot';a.append(dot);
  return a;
 }
 function addMessage(m){
  if(box.querySelector('[data-id="'+m.id+'"]'))return;
  var stick=nearBottom()||m.sender_type==='admin',mine=m.sender_type==='admin',row=document.createElement('div'),bubble=document.createElement('div'),p=document.createElement('p'),t=document.createElement('time');
  ensureDay(m.created_at.slice(0,10));
  row.className='chx-row '+(mine?'mine':'theirs');row.dataset.id=m.id;
  if(!mine)row.append(miniAvatar());
  bubble.className='chx-bubble';p.textContent=m.message;t.textContent=m.created_at.slice(11,16);
  if(mine){var tick=document.createElement('span'),read=Number(m.is_read)===1;tick.className='chx-tick'+(read?' read':'');tick.textContent=read?'✓✓':'✓';t.append(tick)}
  bubble.append(p,t);row.append(bubble);box.insertBefore(row,start);
  start.hidden=true;last=Math.max(last,Number(m.id));
  if(stick)bottom();
 }
 function markRead(upTo){
  box.querySelectorAll('.chx-row.mine').forEach(function(row){
   if(Number(row.dataset.id)<=upTo){var tick=row.querySelector('.chx-tick');tick.classList.add('read');tick.textContent='✓✓'}
  });
 }
 async function poll(){
  if(busy)return;
  try{
   var response=await fetch(cfg.pollUrl+'?after='+last,{headers:{'X-Requested-With':'XMLHttpRequest'}});
   if(!response.ok)return;
   var data=await response.json();
   data.messages.forEach(addMessage);
   markRead(data.read_up_to);
   updateConversations(data.conversations);
   form.elements[cfg.csrfName].value=data.csrf;
  }catch(e){}
 }
 function autosize(){input.style.height='auto';input.style.height=Math.min(input.scrollHeight,120)+'px'}
 input.addEventListener('input',autosize);
 form.addEventListener('submit',async function(event){
  event.preventDefault();
  if(busy||!input.value.trim())return;
  busy=true;sendError.hidden=true;
  try{
   var response=await fetch(cfg.sendUrl,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}});
   if(!response.ok)throw new Error();
   var data=await response.json();
   form.elements[cfg.csrfName].value=data.csrf;input.value='';autosize();
  }catch(e){
   sendError.textContent='Balasan belum terkirim. Periksa koneksi lalu coba lagi.';sendError.hidden=false;
  }finally{busy=false;poll()}
 });
 input.addEventListener('keydown',function(event){if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();form.requestSubmit()}});

 var emoji=form.querySelector('.chx-emoji');
 emoji.addEventListener('click',function(event){
  var button=event.target.closest('[data-emoji]');
  if(!button)return;
  var from=input.selectionStart,to=input.selectionEnd,text=button.dataset.emoji;
  input.value=input.value.slice(0,from)+text+input.value.slice(to);
  input.focus();input.setSelectionRange(from+text.length,from+text.length);emoji.open=false;autosize();
 });
 var menu=document.querySelector('.chx-menu');
 document.addEventListener('click',function(event){
  if(!event.target.closest('.chx-emoji'))emoji.open=false;
  if(menu&&!event.target.closest('.chx-menu'))menu.open=false;
 });

 // Catatan warga
 var note=$('chx-note'),noteText=$('chx-note-text'),noteEmpty=$('chx-note-empty'),noteEdit=$('chx-note-edit'),noteInput=$('chx-note-input'),noteAdd=$('chx-note-add');
 function showEditor(open){noteEdit.hidden=!open;noteAdd.hidden=open;noteText.hidden=open||!noteText.textContent;noteEmpty.hidden=open||!!noteText.textContent;if(open)noteInput.focus()}
 noteAdd.addEventListener('click',function(){showEditor(true)});
 $('chx-note-cancel').addEventListener('click',function(){noteInput.value=noteText.textContent;showEditor(false)});
 $('chx-note-save').addEventListener('click',async function(){
  var button=this,body=new FormData();
  button.disabled=true;busy=true;
  body.append(cfg.csrfName,form.elements[cfg.csrfName].value);body.append('note',noteInput.value);
  try{
   var response=await fetch(note.dataset.url,{method:'POST',body:body,headers:{'X-Requested-With':'XMLHttpRequest'}});
   if(!response.ok)throw new Error();
   var data=await response.json();
   form.elements[cfg.csrfName].value=data.csrf;noteText.textContent=data.note;noteInput.value=data.note;
   noteAdd.textContent=data.note?'Ubah Catatan':'+  Tambah Catatan';showEditor(false);
  }catch(e){noteEmpty.hidden=false;noteEmpty.textContent='Catatan belum tersimpan. Coba lagi.'}
  finally{button.disabled=false;busy=false}
 });

 bottom();setInterval(poll,3000);
})();
