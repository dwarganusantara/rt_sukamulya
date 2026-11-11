(() => {
 const page=document.querySelector('.fui-family-overview');if(!page)return;
 const show=id=>{const section=document.getElementById(id);if(!section)return;section.classList.add('fkm-visible');if(section.tagName==='DETAILS')section.open=true;};
 page.querySelectorAll('[data-fkm-target]').forEach(button=>button.addEventListener('click',()=>{const id=button.dataset.fkmTarget;show(id);const section=document.getElementById(id);section.setAttribute('tabindex','-1');section.focus({preventScroll:true});section.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'instant':'smooth',block:'start'});}));
 const revealHash=()=>{if(['#informasi','#anggota','#riwayat'].includes(location.hash))show(location.hash.slice(1));};
 addEventListener('hashchange',revealHash);revealHash();
})();
