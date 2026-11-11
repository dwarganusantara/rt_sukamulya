(() => {
 const dialog=document.getElementById('email-setup-guide');if(!dialog)return;
 document.querySelectorAll('[data-email-guide-open]').forEach(b=>b.addEventListener('click',()=>dialog.showModal()));
 dialog.querySelectorAll('[data-email-guide-close]').forEach(b=>b.addEventListener('click',()=>dialog.close()));
 document.querySelectorAll('[data-email-notice-close]').forEach(b=>b.addEventListener('click',()=>b.closest('.eq-notice').hidden=true));
})();
