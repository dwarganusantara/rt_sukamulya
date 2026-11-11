<?php
$title='Daftar Akun Warga | '.($settings['site_name']??'Sukamulya');
$citizen_register_layout=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_register_layout'));
?>
<main class="citizen-shell citizen-register-shell"><div class="citizen-login-card citizen-register-card">
<img class="register-card-illustration" src="<?= base_url('assets/mockup/login-logo.png') ?>" alt="" aria-hidden="true"><span class="register-eyebrow">PORTAL WARGA <i></i></span><h1>Daftar Akun Warga</h1><p>Buat akun untuk mengakses layanan, pengajuan surat, dan informasi iuran warga.</p>
<?php if($error): ?><div class="portal-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
<form method="post" action="<?= site_url('warga/daftar') ?>">
<label>Nama lengkap<span class="register-input"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 2.8-6.5 7-6.5s7 2.5 7 6.5z"/></svg><input name="display_name" maxlength="140" autocomplete="name" placeholder="Masukkan nama lengkap Anda" value="<?= html_escape($this->input->post('display_name',TRUE)?:'') ?>" required autofocus></span></label>
<label>Jenis kelamin<span class="register-input"><b aria-hidden="true">♙</b><select name="gender" required><option value="">Pilih jenis kelamin</option><option value="female" <?= $this->input->post('gender',TRUE)==='female'?'selected':'' ?>>Wanita</option><option value="male" <?= $this->input->post('gender',TRUE)==='male'?'selected':'' ?>>Pria</option></select></span></label>
<label>Username<span class="register-input"><b aria-hidden="true">@</b><input name="username" autocomplete="username" placeholder="Masukkan username" value="<?= html_escape($this->input->post('username',TRUE)?:'') ?>" required></span></label>
<label>Password<span class="register-input"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg><input name="password" type="password" autocomplete="new-password" placeholder="Masukkan password" required></span></label>
<label>Ulangi password<span class="register-input"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg><input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Masukkan ulang password" required></span></label>
<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><button class="portal-button register-submit" type="submit">Daftar Akun <span aria-hidden="true">&rarr;</span></button></form>
<div class="citizen-auth-switch"><span>Sudah memiliki akun?</span><a href="<?= site_url('warga/login') ?>">Masuk di sini</a></div></div></main>
<?php $this->load->view('site/footer',compact('settings')); ?>
