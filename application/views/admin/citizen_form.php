<?php $label=$editing?'Edit':'Tambah'; ?>
<div class="acf-page">
 <nav class="acf-breadcrumb"><a href="<?= site_url('admin/citizens') ?>">Data Warga</a><span>›</span><a href="<?= site_url('admin/citizens') ?>">Akun Warga</a><span>›</span><b><?= $label ?> Akun</b></nav>
 <header class="acf-hero"><span class="acf-hero-icon">♟<i>+</i></span><div><h1><?= $label ?> Akun Warga</h1><p><?= $editing?'Perbarui informasi akun warga yang terdaftar.':'Buat akun untuk warga agar dapat mengakses layanan dan informasi RT secara online.' ?></p></div></header>
 <?php if($error): ?><div class="error acf-error"><?= html_escape($error) ?></div><?php endif; ?>
 <form class="acf-layout" method="post" enctype="multipart/form-data" id="citizen-account-form">
  <section class="acf-card acf-main-card">
   <header class="acf-card-head"><span>▣</span><div><h2>Informasi Akun</h2><p>Lengkapi data berikut untuk <?= $editing?'memperbarui':'membuat' ?> akun warga.</p></div></header>
   <div class="acf-grid">
    <label><strong>Nama lengkap</strong><span class="acf-control"><i>♙</i><input name="full_name" maxlength="140" placeholder="Masukkan nama lengkap warga" value="<?= html_escape($item['full_name']??$item['display_name']??'') ?>" required></span></label>
    <label><strong>Nama tampilan</strong><span class="acf-control"><i>▣</i><input name="display_name" maxlength="140" placeholder="Nama yang ditampilkan di sistem" value="<?= html_escape($item['display_name']??'') ?>" required></span></label>
    <label><strong>Jenis kelamin</strong><span class="acf-control"><i>♙</i><select name="gender" required><option value="female" <?= ($item['gender']??'female')==='female'?'selected':'' ?>>Wanita</option><option value="male" <?= ($item['gender']??'')==='male'?'selected':'' ?>>Pria</option></select></span></label>
    <label><strong>Username</strong><span class="acf-control"><i>@</i><input name="username" maxlength="80" placeholder="Masukkan username" value="<?= html_escape($item['username']??'') ?>" required></span></label>
    <label><strong>Password <?= $editing?'baru':'awal' ?></strong><span class="acf-control"><i>♙</i><input id="acf-password" type="password" name="password" minlength="8" placeholder="<?= $editing?'Kosongkan bila tidak diubah':'Masukkan password awal' ?>" <?= $editing?'':'required' ?>><button type="button" id="acf-password-toggle" aria-label="Tampilkan password">◉</button></span><small>Minimal 8 karakter, gunakan kombinasi huruf dan angka</small></label>
    <label><strong>No. WhatsApp <em>(opsional)</em></strong><span class="acf-control"><i>◉</i><input name="phone" maxlength="25" inputmode="tel" placeholder="Contoh: 081234567890" value="<?= html_escape($item['phone']??'') ?>"></span></label>
    <label><strong>Email <em>(opsional)</em></strong><span class="acf-control"><i>✉</i><input name="email" type="email" maxlength="190" placeholder="Contoh: nama@gmail.com" value="<?= html_escape($item['email']??'') ?>"></span></label>
    <label><strong>Alamat <em>(opsional)</em></strong><span class="acf-control"><i>⌖</i><input name="address" maxlength="255" placeholder="Masukkan alamat warga" value="<?= html_escape($item['address']??'') ?>"></span></label>
    <label><strong>Status akun</strong><span class="acf-status"><input id="acf-active" type="checkbox" name="is_active" value="1" <?= !isset($item['is_active'])||$item['is_active']?'checked':'' ?>><i></i><span><b>Akun aktif</b><small>Warga dapat login ke sistem</small></span></span></label>
    <?= $custom_fields_html ?? '' ?>
    <label class="acf-wide"><strong>Keterangan <em>(opsional)</em></strong><span class="acf-control acf-textarea"><i>▤</i><textarea name="notes" maxlength="1000" rows="3" placeholder="Tambahkan keterangan jika diperlukan..."><?= html_escape($item['notes']??'') ?></textarea></span></label>
   </div>
   <div class="acf-actions"><button class="acf-submit" type="submit">➤ &nbsp; Simpan akun</button><button class="acf-reset" type="reset">↻ &nbsp; Reset</button></div>
  </section>
  <aside class="acf-side">
   <section class="acf-card acf-photo-card"><h2><span>▣</span> Foto Profil <small>(opsional)</small></h2><label class="acf-photo-drop" for="acf-photo"><span class="acf-photo-preview"><?php if($profile_photo): ?><img id="acf-preview" src="<?= base_url(html_escape($profile_photo)) ?>" alt="Foto profil"><?php else: ?><img id="acf-preview" alt="" hidden><b id="acf-placeholder">♟</b><?php endif; ?><i>▣</i></span><strong>Klik untuk upload foto</strong><small>JPG, PNG, WebP, GIF, BMP, AVIF (Maks. 2MB)</small></label><input id="acf-photo" type="file" name="profile_photo" accept="<?= image_accept() ?>"></section>
   <section class="acf-card acf-info-card"><h2><span>i</span> Informasi</h2><ul><li><i class="blue">♟</i>Akun warga digunakan untuk login ke website RT.</li><li><i class="green">♟</i>Pastikan password yang diberikan aman.</li><li><i class="orange">♟</i>Warga dengan akun aktif dapat mengajukan surat dan melihat informasi.</li><li><i class="purple">✉</i>Email dan WhatsApp bersifat opsional namun sangat membantu.</li><li><i class="red">◆</i>Data warga akan disimpan dengan aman.</li></ul></section>
  </aside>
  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
 </form>
</div>
<script>(function(){const input=document.getElementById('acf-photo'),preview=document.getElementById('acf-preview'),placeholder=document.getElementById('acf-placeholder'),pass=document.getElementById('acf-password'),toggle=document.getElementById('acf-password-toggle');input.addEventListener('change',function(){const f=this.files[0];if(!f)return;if(f.size>2*1024*1024){alert('Ukuran foto maksimal 2 MB.');this.value='';return}preview.src=URL.createObjectURL(f);preview.hidden=false;if(placeholder)placeholder.hidden=true});toggle.addEventListener('click',function(){pass.type=pass.type==='password'?'text':'password';this.textContent=pass.type==='password'?'◉':'×'});})();</script>
