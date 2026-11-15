(()=>{'use strict';
document.querySelectorAll('[data-sh-open]').forEach(b=>b.addEventListener('click',()=>{const d=document.getElementById(b.dataset.shOpen);if(!d)return;b.closest('details')?.removeAttribute('open');d.showModal();}));
document.querySelectorAll('.sh-dialog').forEach(d=>{d.querySelectorAll('[data-sh-close]').forEach(x=>x.addEventListener('click',()=>d.close()));d.addEventListener('click',e=>{if(e.target===d)d.close();});});
document.querySelectorAll('[data-sh-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.shConfirm))e.preventDefault();}));
document.querySelectorAll('[data-sh-autosubmit]').forEach(s=>s.addEventListener('change',()=>s.form.submit()));
})();
