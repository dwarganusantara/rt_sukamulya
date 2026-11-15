<?php
$title='Ganti Password | '.($settings['site_name']??'Sukamulya');$citizen_dashboard=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_dashboard'));
$this->load->helper('official_icons');
$lock='<svg class="off-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14.500v2.500"></path></svg>';
$fields=array(array('old-password','old_password','Password lama','Masukkan password lama','current-password'),array('new-password','new_password','Password baru','Masukkan password baru','new-password'),array('confirm-password','password_confirmation','Konfirmasi password baru','Masukkan kembali password baru','new-password'));
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-password-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-password-v2.css') ?>">
<main class="pwx-page">
 <section class="pwx-hero"><img src="<?= base_url('assets/mockup/password-hero-v2.png') ?>" alt=""><div class="pwx-inner">
  <a class="pwx-back" href="<?= site_url('warga/profil') ?>"><?= official_svg('back') ?>Kembali</a>
  <nav class="pwx-crumb" aria-label="Breadcrumb"><a href="<?= site_url('warga') ?>"><?= official_svg('home') ?>Beranda</a><i>›</i><a href="<?= site_url('warga/profil') ?>">Profil Saya</a><i>›</i><b>Ganti Password</b></nav>
  <small>AKUN WARGA</small><h1>Ganti Password</h1><p>Perbarui password akun Anda agar tetap aman dan terlindungi.</p>
 </div></section>
 <section class="pwx-card">
  <aside class="pwx-tips"><span class="pwx-shield"><?= official_svg('shield') ?></span><h2>Jaga Keamanan Akun Anda</h2><p>Gunakan password yang kuat dan tidak mudah ditebak. Jangan bagikan password kepada orang lain.</p>
   <div class="pwx-tip-box"><h3><span><?= $lock ?></span>Tips password yang aman:</h3><ul><li><?= official_svg('check-circle') ?>Minimal 8 karakter</li><li><?= official_svg('check-circle') ?>Kombinasi huruf dan angka</li><li><?= official_svg('check-circle') ?>Gunakan karakter khusus (jika memungkinkan)</li></ul></div></aside>
  <form method="post" id="password-change-form" class="pwx-form">
   <?php if($error): ?><div class="pwx-alert bad" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
   <?php if($this->session->flashdata('notice')): ?><div class="pwx-alert good" role="status"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
   <?php foreach($fields as $n=>$f): ?>
   <div class="pwx-row"><b class="pwx-step"><?= $n+1 ?></b><span class="pwx-lock"><?= $lock ?></span><div class="pwx-field"><label for="<?= $f[0] ?>"><?= $f[2] ?></label><div class="pwx-input"><input id="<?= $f[0] ?>" type="password" name="<?= $f[1] ?>" placeholder="<?= $f[3] ?>" autocomplete="<?= $f[4] ?>"<?= $n?' minlength="8"':'' ?> required><button type="button" data-toggle-password="<?= $f[0] ?>" aria-label="Tampilkan password" aria-pressed="false"><?= official_svg('eye') ?></button></div><?php if($n===1): ?><p class="pwx-note"><span>i</span>Password harus minimal 8 karakter dan disarankan mengandung huruf, angka, dan karakter khusus.</p><?php endif; ?></div></div>
   <?php endforeach; ?>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
   <button class="pwx-submit" type="submit"><span><?= $lock ?><em>Simpan Password</em></span><?= official_svg('arrow-right') ?></button>
  </form>
 </section>
</main>
<script>
(function(){
 document.querySelectorAll('[data-toggle-password]').forEach(function(b){b.addEventListener('click',function(){var i=document.getElementById(b.dataset.togglePassword),v=i.type==='password';i.type=v?'text':'password';b.classList.toggle('on',v);b.setAttribute('aria-pressed',v?'true':'false');b.setAttribute('aria-label',v?'Sembunyikan password':'Tampilkan password')})});
 var f=document.getElementById('password-change-form'),n=document.getElementById('new-password'),c=document.getElementById('confirm-password');
 function match(){c.setCustomValidity(c.value&&c.value!==n.value?'Konfirmasi password baru tidak sama.':'')}
 n.addEventListener('input',match);c.addEventListener('input',match);
 f.addEventListener('submit',function(){var b=f.querySelector('.pwx-submit');b.disabled=true;b.querySelector('em').textContent='Menyimpan...'});
})();
</script>
<?php $this->load->view('site/footer',compact('settings')); ?>
