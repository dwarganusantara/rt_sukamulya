(()=>{'use strict';
/* Dialog (Tambah Akun / Tambah Peran / Ubah Peran) */
const modal=d=>{if(typeof d.showModal!=='function')return;if(d.open&&!d.matches(':modal'))d.close();if(!d.open)d.showModal();const f=d.querySelector('input:not([type=hidden]),select');if(f)f.focus();};
document.querySelectorAll('dialog[data-akx-autoopen]').forEach(modal);
document.querySelectorAll('[data-akx-open]').forEach(a=>a.addEventListener('click',e=>{const d=document.getElementById(a.dataset.akxOpen||'akun-form');if(!d||typeof d.showModal!=='function')return;e.preventDefault();modal(d);}));
// Dialog yang dibuka dari URL (edit akun / ubah peran) ditutup dengan kembali ke halaman bersih.
const fromUrl=/\/akun\/\d+/.test(location.pathname)||/[?&](edit_role|new_role)=/.test(location.search);
document.querySelectorAll('dialog.akx-dialog').forEach(d=>{
 d.querySelectorAll('[data-akx-close]').forEach(a=>a.addEventListener('click',e=>{if(fromUrl)return;e.preventDefault();d.close();}));
 d.addEventListener('cancel',e=>{if(fromUrl){e.preventDefault();location.assign(d.querySelector('[data-akx-close]').href);}});
});
/* Konfirmasi aksi berisiko */
document.querySelectorAll('[data-akx-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.akxConfirm))e.preventDefault();}));
document.querySelectorAll('[data-akx-autosubmit]').forEach(s=>s.addEventListener('change',()=>s.form.submit()));
/* Hak akses menu: checkbox grup ↔ item, Aktifkan/Nonaktifkan Semua, ringkasan langsung */
const form=document.querySelector('[data-akx-perm]');
if(form){
 const groups=[...form.querySelectorAll('[data-akx-group]')];
 const bar=document.querySelector('[data-akx-bar]'),pct=document.querySelector('[data-akx-pct]'),okEl=document.querySelector('[data-akx-sum-ok]'),total=+(document.querySelector('[data-akx-total]')?.dataset.akxTotal||0);
 const sync=g=>{const t=g.querySelector('[data-akx-group-toggle]');if(!t)return;const all=[...g.querySelectorAll('.akx-gitems input[type=checkbox]')].filter(i=>!i.closest('li').classList.contains('is-na'));const on=all.filter(i=>i.checked).length;if(!all.length){t.checked=false;t.indeterminate=false;return;}t.checked=on===all.length;t.indeterminate=on>0&&on<all.length;};
 const summary=()=>{const on=[...form.querySelectorAll('.akx-gitems input[type=checkbox]')].filter(i=>i.checked&&!i.closest('li').classList.contains('is-na')).length;const p=total?Math.round(100*on/total):0;if(okEl)okEl.textContent=on;if(bar)bar.style.width=p+'%';if(pct)pct.textContent=p+'%';bar?.parentElement.setAttribute('aria-valuenow',p);};
 const refresh=()=>{groups.forEach(sync);summary();};
 groups.forEach(g=>{const t=g.querySelector('[data-akx-group-toggle]');
  if(t){t.addEventListener('click',e=>e.stopPropagation());t.addEventListener('change',()=>{g.querySelectorAll('[data-akx-item]').forEach(i=>{i.checked=t.checked;});refresh();});}
  g.querySelectorAll('[data-akx-item]').forEach(i=>i.addEventListener('change',refresh));});
 document.querySelectorAll('[data-akx-all]').forEach(b=>b.addEventListener('click',()=>{const v=b.dataset.akxAll==='1';form.querySelectorAll('[data-akx-item]').forEach(i=>{i.checked=v;});refresh();}));
 form.addEventListener('reset',()=>setTimeout(refresh,0));
 refresh();
}
})();
