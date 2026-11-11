</main></div></div><div class="ad-overlay" onclick="document.body.classList.remove('ad-open')"></div>
<?php $this->load->helper(array('admin_alert','family_ui')); $alert_data=$this->session->userdata('admin_id')?admin_alert_data():NULL; if($alert_data!==NULL): ?>
<link rel="stylesheet" href="<?= base_url('assets/admin-alerts.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-alerts.css') ?>">
<dialog class="aal" id="aal" aria-labelledby="aal-title" aria-describedby="aal-sub"
 data-endpoint="<?= site_url('admin/alerts') ?>" data-admin="<?= (int)$this->session->userdata('admin_id') ?>" data-page="<?= html_escape(uri_string()) ?>"
 data-initial="<?= html_escape(json_encode($alert_data,JSON_UNESCAPED_UNICODE)) ?>">
 <div class="aal-glow" aria-hidden="true"></div>
 <header class="aal-head">
  <span class="aal-bell" aria-hidden="true"><?= family_icon('bell') ?><i></i></span>
  <div><p class="aal-kicker">Peringatan untuk Admin</p><h2 id="aal-title">Ada Pengajuan Baru!</h2><p id="aal-sub">Warga menunggu verifikasi Anda.</p></div>
  <button type="button" class="aal-x" data-aal-snooze aria-label="Tutup peringatan"><?= family_icon('close') ?></button>
 </header>
 <div class="aal-grid">
  <section class="aal-box is-kk" data-aal="kk">
   <header><span class="aal-ic"><?= family_icon('people') ?></span><div><h3>Verifikasi Data KK</h3><p><b data-aal-count>0</b> pengajuan menunggu</p></div><span class="aal-new" data-aal-new hidden>BARU</span></header>
   <ul data-aal-list></ul>
   <a class="aal-go" data-aal-go href="#"><?= family_icon('check') ?>Tinjau Verifikasi KK<?= family_icon('arrow') ?></a>
  </section>
  <section class="aal-box is-letter" data-aal="letters">
   <header><span class="aal-ic"><?= family_icon('mail') ?></span><div><h3>Pengajuan Surat</h3><p><b data-aal-count>0</b> pengajuan menunggu</p></div><span class="aal-new" data-aal-new hidden>BARU</span></header>
   <ul data-aal-list></ul>
   <a class="aal-go" data-aal-go href="#"><?= family_icon('file') ?>Tinjau Pengajuan Surat<?= family_icon('arrow') ?></a>
  </section>
 </div>
 <footer class="aal-foot"><span><?= family_icon('clock') ?>Diperbarui otomatis setiap 30 detik</span><button type="button" class="aal-later" data-aal-snooze>Ingatkan 15 menit lagi</button></footer>
</dialog>
<script src="<?= base_url('assets/admin-alerts.js') ?>?v=<?= filemtime(FCPATH.'assets/admin-alerts.js') ?>" defer></script>
<?php endif; ?>
<?php $this->load->view('admin/chat_widget'); ?>
<script>(function(){
 // Hamburger: di layar kecil membuka laci menu; di desktop menyembunyikan sidebar (diingat per browser).
 var burger=document.getElementById('ad-hamburger'),ckey='sukamulya.sidebar.collapsed';
 try{if(innerWidth>950&&localStorage.getItem(ckey)==='1')document.body.classList.add('ad-collapsed')}catch(e){}
 function syncBurger(){burger&&burger.setAttribute('aria-expanded',innerWidth<=950?String(document.body.classList.contains('ad-open')):String(!document.body.classList.contains('ad-collapsed')))}
 if(burger){burger.addEventListener('click',function(){if(innerWidth<=950){document.body.classList.toggle('ad-open')}else{var c=document.body.classList.toggle('ad-collapsed');try{localStorage.setItem(ckey,c?'1':'0')}catch(e){}}syncBurger()});syncBurger()}
 document.querySelectorAll('.sbx-nav a').forEach(function(link){link.addEventListener('click',function(){if(innerWidth<=950)document.body.classList.remove('ad-open')})});
 document.addEventListener('keydown',function(event){if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){var search=document.getElementById('ad-search-input');if(search){event.preventDefault();search.focus();search.select()}}});
 const account=document.querySelector('.ad-account');
 if(account){document.addEventListener('click',function(event){if(!account.contains(event.target))account.open=false});document.addEventListener('keydown',function(event){if(event.key==='Escape')account.open=false})}
})();</script>
<?php $site_settings=$this->Content_model->settings(); $this->load->view('subscription/modal',array('site_settings'=>$site_settings)); ?>
</body></html>
