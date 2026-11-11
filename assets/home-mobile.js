(() => {
 const open = document.getElementById('mh-services-open'), dialog = document.getElementById('mh-services');
 if (!open || !dialog) return;
 open.addEventListener('click', () => dialog.showModal());
 document.getElementById('mh-services-close').addEventListener('click', () => dialog.close());
 dialog.addEventListener('click', e => { const r=dialog.getBoundingClientRect(); if(e.target===dialog&&(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom))dialog.close(); });
 window.matchMedia('(max-width:600px)').addEventListener('change', e => {if(!e.matches&&dialog.open)dialog.close();});
})();
