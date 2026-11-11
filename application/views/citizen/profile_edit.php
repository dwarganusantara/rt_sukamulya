<?php
$title='Edit Informasi Pribadi | '.($settings['site_name']??'Sukamulya');
$citizen_dashboard=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_dashboard'));
$photoUrl=base_url(html_escape($profile_photo));
?>
<main class="pe-page">
 <section class="pe-hero">
  <div class="wrap pe-hero-inner">
   <nav class="pe-breadcrumb"><a href="<?= site_url('warga') ?>">⌂</a><span>›</span><a href="<?= site_url('warga') ?>">Beranda</a><span>›</span><a href="<?= site_url('warga/profil') ?>">Profil Saya</a><span>›</span><b>Edit Informasi Pribadi</b></nav>
   <div class="pe-hero-copy"><h1>Edit <b>Informasi Pribadi</b></h1><p>Perbarui data pribadi Anda agar informasi selalu<br>terbaru dan dapat digunakan untuk layanan RT.</p></div>
  </div>
 </section>
 <div class="wrap pe-content">
  <?php if($error): ?><div class="error pe-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="pe-layout" id="profile-edit-form">
   <section class="pe-card pe-form-card">
    <header class="pe-card-title"><span>♟</span><h2>Informasi Pribadi</h2></header>
    <div class="pe-grid">
     <label><strong>Nama Lengkap</strong><span class="pe-control"><i>♙</i><input name="full_name" maxlength="140" autocomplete="name" value="<?= html_escape($citizen['full_name']??$citizen['display_name']??'') ?>" required></span></label>
     <label><strong>Nama Tampilan</strong><span class="pe-control"><i>◇</i><input name="display_name" maxlength="140" value="<?= html_escape($citizen['display_name']??'') ?>" required></span><small>Nama ini akan ditampilkan di sistem.</small></label>
     <label><strong>Jenis Kelamin</strong><span class="pe-control"><i>♙</i><select name="gender" required><option value="female" <?= ($citizen['gender']??'female')==='female'?'selected':'' ?>>Wanita</option><option value="male" <?= ($citizen['gender']??'')==='male'?'selected':'' ?>>Pria</option></select></span><small>Menentukan avatar bawaan jika belum ada foto.</small></label>
     <label><strong>Username</strong><span class="pe-control disabled"><i>@</i><input value="<?= html_escape($citizen['username']??'') ?>" disabled></span><small>Username tidak dapat diubah dari halaman ini.</small></label>
     <label><strong>Alamat</strong><span class="pe-control pe-address"><i>⌖</i><textarea name="address" maxlength="255" rows="2" autocomplete="street-address"><?= html_escape($citizen['address']??'') ?></textarea></span></label>
     <label><strong>Nomor HP</strong><span class="pe-control"><i>☎</i><input name="phone" maxlength="25" inputmode="tel" autocomplete="tel" value="<?= html_escape($citizen['phone']??'') ?>"></span><small>Gunakan nomor WhatsApp yang aktif.</small></label>
     <label><strong>Email</strong><span class="pe-control"><i>✉</i><input name="email" type="email" maxlength="190" autocomplete="email" value="<?= html_escape($citizen['email']??'') ?>"></span><small>Email bersifat opsional.</small></label>
     <?php if (!empty($email_feature)): ?><label><strong>Notifikasi email</strong><span style="display:flex;gap:8px;align-items:flex-start;min-height:42px"><input type="checkbox" name="notify_email" value="1" <?= !empty($citizen['notify_email']) ? 'checked' : '' ?> style="width:18px;height:18px;margin-top:2px"><span>Kirim salinan pemberitahuan status layanan (surat, mutasi, pengaduan, iuran) ke email saya.</span></span><small>Opsional. Berlaku bila email diisi dan fitur diaktifkan pengurus.</small></label><?php endif; ?>
     <?= $custom_fields_html ?? '' ?>
    </div>
    <div class="pe-actions"><a href="<?= site_url('warga/profil') ?>">× &nbsp; Batal</a><button type="submit">▣ &nbsp; Simpan Perubahan</button></div>
   </section>
   <aside class="pe-side">
    <section class="pe-card pe-photo-card"><header><span>▧</span><h2>Foto Profil</h2></header><label for="pe-photo"><span class="pe-avatar"><img id="pe-preview" src="<?= $photoUrl ?>" alt="Foto profil"><i>▣</i></span><b>⇧ &nbsp; Klik untuk upload foto</b><small>JPG, PNG, WebP, GIF, BMP, AVIF (Maks. 2MB)</small></label><input id="pe-photo" type="file" name="profile_photo" accept="<?= image_accept() ?>"></section>
    <section class="pe-card pe-info-card"><header><span>i</span><h2>Informasi Akun</h2></header><ul><li><i class="green">♟</i><span>Akun ini digunakan untuk mengakses<br>layanan dan informasi RT.</span></li><li><i class="orange">♙</i><span>Pastikan data yang Anda masukkan<br>sudah benar dan aman.</span></li><li><i class="blue">◆</i><span>Data Anda hanya digunakan untuk<br>keperluan layanan RT 009/008.</span></li></ul></section>
   </aside>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
  </form>
 </div>
</main>
<script>(function(){const wanted=new URLSearchParams(location.search).get('field'),target=wanted&&document.querySelector('#profile-edit-form [name="'+wanted.replace(/[^a-z_]/g,'')+'"]');if(target)target.focus();const input=document.getElementById('pe-photo'),preview=document.getElementById('pe-preview'),form=document.getElementById('profile-edit-form');input.addEventListener('change',function(){const file=this.files&&this.files[0];if(!file)return;if(file.size>2*1024*1024){alert('Ukuran foto maksimal 2 MB.');this.value='';return}const url=URL.createObjectURL(file);preview.src=url;preview.onload=()=>URL.revokeObjectURL(url)});form.addEventListener('submit',function(){const button=form.querySelector('button[type=submit]');button.disabled=true;button.textContent='Menyimpan...'})})();</script>
<?php $this->load->view('site/footer',compact('settings')); ?>
