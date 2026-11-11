(() => {
 const dialog=document.getElementById('dm-more'),open=document.getElementById('dm-more-open'),account=document.querySelector('.dm-account');
 if(!dialog||!open)return;
 open.addEventListener('click',()=>dialog.showModal());
 document.getElementById('dm-more-close').addEventListener('click',()=>dialog.close());
 dialog.addEventListener('click',e=>{const r=dialog.getBoundingClientRect();if(e.target===dialog&&(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom))dialog.close();});
 dialog.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>dialog.close()));
 document.addEventListener('click',e=>{if(account&&!account.contains(e.target))account.open=false;});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&account)account.open=false;});
 const media=matchMedia('(max-width:1023px)');
 const sync=()=>{if(!media.matches&&dialog.open)dialog.close();const skip=document.querySelector('.dwb-skip');if(skip)skip.href=media.matches?'#dm-main':'#dwb-main';};
 media.addEventListener('change',sync);sync();
 // The existing chat route opens the existing widget. Avoid page reload when already on this dashboard.
 document.querySelectorAll('.dm-dashboard a[href$="warga/chat"]').forEach(a=>a.addEventListener('click',e=>{const toggle=document.getElementById('live-chat-toggle');if(!toggle)return;e.preventDefault();if(dialog.open)dialog.close();if(toggle.getAttribute('aria-expanded')!=='true')toggle.click();}));
})();
