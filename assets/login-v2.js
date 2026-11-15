// Tombol lihat password dan "Ingat saya". Hanya username yang diingat, di browser ini.
(function(){
 document.querySelectorAll('[data-toggle-password]').forEach(function(button){
  button.addEventListener('click',function(){
   var input=document.getElementById(button.dataset.togglePassword),show=input.type==='password';
   input.type=show?'text':'password';
   button.setAttribute('aria-pressed',show?'true':'false');
   button.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');
  });
 });
 document.querySelectorAll('form[data-remember]').forEach(function(form){
  var key=form.dataset.remember,user=form.elements.username,check=form.elements.remember,saved='';
  try{saved=localStorage.getItem(key)||''}catch(e){}
  if(saved&&!user.value){user.value=saved;check.checked=true;form.elements.password.focus()}
  form.addEventListener('submit',function(){
   try{if(check.checked)localStorage.setItem(key,user.value.trim());else localStorage.removeItem(key)}catch(e){}
  });
 });
 document.querySelectorAll('[data-reveal]').forEach(function(button){
  button.addEventListener('click',function(){
   var target=document.getElementById(button.dataset.reveal);
   target.hidden=!target.hidden;
   button.setAttribute('aria-expanded',target.hidden?'false':'true');
  });
 });
})();
