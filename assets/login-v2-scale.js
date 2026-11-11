// Menyamakan komposisi halaman login dengan mockup pada lebar layar berapa pun.
(function(){
 var root=document.documentElement,w=+root.dataset.loginW,h=+root.dataset.loginH;
 function apply(){
  var vw=root.clientWidth,vh=window.innerHeight,s;
  if(h){s=Math.min(Math.max(vw/w,vh/h),vw/1400,vh/790)}else{s=vw/w}
  root.style.setProperty('--s',s);
 }
 apply();window.addEventListener('resize',apply);
})();
