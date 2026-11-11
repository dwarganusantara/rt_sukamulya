(() => {
 const form=document.getElementById('recon-form'),dialog=document.getElementById('recon-confirm');
 const steps=document.querySelectorAll('.recon-steps li');let confirmed=false;
 const stage=n=>steps.forEach((el,i)=>{el.classList.toggle('is-current',i===n-1);el.classList.toggle('is-done',i<n-1)});
 if(form&&dialog){
 form.addEventListener('submit',e=>{if(confirmed){form.querySelector('[type=submit]').disabled=true;return;}if(!dialog.showModal)return;e.preventDefault();if(!form.reportValidity())return;const date=form.elements.recon_date.value;document.getElementById('recon-confirm-date').textContent=new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(date+'T12:00:00'));document.getElementById('recon-confirm-balance').textContent=new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:2}).format(Number(form.elements.bank_balance.value));document.getElementById('recon-confirm-note').textContent=form.elements.note.value.trim()||'Tidak ada catatan';stage(2);dialog.showModal();});
 dialog.querySelectorAll('[data-recon-close]').forEach(b=>b.addEventListener('click',()=>dialog.close()));dialog.addEventListener('close',()=>{if(!confirmed)stage(1)});
 document.getElementById('recon-confirm-save').addEventListener('click',()=>{if(confirmed)return;confirmed=true;dialog.close();form.requestSubmit();});
 }
 const history=document.querySelector('.recon-history');if(history){const rows=[...history.querySelectorAll('tbody tr')],link=history.querySelector('[data-recon-history]'),caption=history.querySelector('.recon-history-caption');const toggle=expanded=>{rows.forEach((r,i)=>r.hidden=!expanded&&i>=5);link.setAttribute('aria-expanded',String(expanded));link.textContent=expanded?'Ringkas riwayat':'Lihat Semua →';caption.hidden=!expanded;};toggle(false);link.hidden=rows.length<=5;link.addEventListener('click',e=>{e.preventDefault();toggle(link.getAttribute('aria-expanded')!=='true');});}
})();
