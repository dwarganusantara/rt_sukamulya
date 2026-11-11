(function(){
 var nav=document.getElementById('sbx-nav'),favorites=document.getElementById('sbx-favorites'),hint=document.getElementById('sbx-favorites-hint'),key=nav.dataset.storage,state={pins:[],closed:[]};
 // Penyimpanan bisa diblokir browser; menu tetap berfungsi tanpa ingatan.
 try{var saved=JSON.parse(localStorage.getItem(key));if(saved){state.pins=saved.pins||[];state.closed=saved.closed||[]}}catch(e){}
 function save(){try{localStorage.setItem(key,JSON.stringify(state))}catch(e){}}
 var lines={};
 nav.querySelectorAll('.sbx-group[data-group] .sbx-line').forEach(function(line){lines[line.dataset.key]=line});
 function setPin(line,pinned){var pin=line.querySelector('.sbx-pin'),label=line.querySelector('.sbx-label').textContent;line.classList.toggle('pinned',pinned);pin.setAttribute('aria-pressed',pinned?'true':'false');pin.setAttribute('aria-label',(pinned?'Lepas sematan ':'Sematkan ')+label);pin.title=pinned?'Lepas dari Favorit':'Sematkan ke Favorit'}
 function renderPins(){
  favorites.textContent='';
  Object.keys(lines).forEach(function(k){setPin(lines[k],state.pins.indexOf(k)>=0)});
  state.pins.forEach(function(k){if(lines[k])favorites.appendChild(lines[k].cloneNode(true))});
  hint.hidden=favorites.children.length>0;
 }
 nav.addEventListener('click',function(event){
  var pin=event.target.closest('.sbx-pin');
  if(pin){var k=pin.closest('.sbx-line').dataset.key,at=state.pins.indexOf(k);if(at>=0)state.pins.splice(at,1);else state.pins.push(k);save();renderPins();return}
  var head=event.target.closest('.sbx-head');
  if(head){var group=head.closest('.sbx-group'),name=group.dataset.group,open=head.getAttribute('aria-expanded')!=='true',i=state.closed.indexOf(name);head.setAttribute('aria-expanded',open?'true':'false');group.classList.toggle('closed',!open);if(open&&i>=0)state.closed.splice(i,1);if(!open&&i<0)state.closed.push(name);save()}
 });
 nav.querySelectorAll('.sbx-group[data-group]').forEach(function(group){
  if(state.closed.indexOf(group.dataset.group)>=0&&!group.querySelector('.sbx-line.active')){group.classList.add('closed');group.querySelector('.sbx-head').setAttribute('aria-expanded','false')}
 });
 renderPins();
 var current=nav.querySelector('.sbx-group[data-group] .sbx-line.active');
 if(current&&!favorites.querySelector('.active'))nav.scrollTop=Math.max(0,current.offsetTop-nav.clientHeight/2);
})();
