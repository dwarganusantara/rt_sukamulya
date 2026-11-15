<?php
$title='Aktivasi Akun Warga | '.($settings['site_name']??'Sukamulya');
$citizen_register_layout=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_register_layout'));
$lock='<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>';
?>
<main class="citizen-shell citizen-register-shell"><div class="citizen-login-card citizen-register-card">
<img class="register-card-illustration" src="<?= base_url('assets/mockup/login-logo.png') ?>" alt="" aria-hidden="true"><span class="register-eyebrow">PORTAL WARGA <i></i></span>
<?php if($invalid): ?>
<h1>Tautan Tidak Berlaku</h1>
<?php if($error): ?><div class="portal-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
<p>Tautan aktivasi ini sudah dipakai, kedaluwarsa, atau dinonaktifkan. Hubungi pengurus RT untuk meminta tautan aktivasi baru.</p>
<div class="citizen-auth-switch"><span>Sudah punya akun?</span><a href="<?= site_url('warga/login') ?>">Masuk di sini</a></div>
<?php else: ?>
<h1>Aktivasi Akun Warga</h1><p>Data keluarga Anda sudah terdaftar di RT. Buktikan bahwa Anda anggota keluarga ini, lalu buat username dan password sendiri.</p>
<?php if($error): ?><div class="portal-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
<form method="post" action="<?= site_url('warga/aktivasi/'.$token) ?>" autocomplete="off">
<label>NIK Anda (sesuai KK)<span class="register-input"><b aria-hidden="true">#</b><input name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" placeholder="16 digit NIK" required autofocus></span></label>
<label>Tanggal lahir (sesuai KK)<span class="register-input"><b aria-hidden="true">&#128197;</b><input name="tanggal_lahir" type="date" required></span></label>
<label>Username<span class="register-input"><b aria-hidden="true">@</b><input name="username" autocomplete="username" placeholder="Buat username" maxlength="80" value="<?= html_escape($this->input->post('username',TRUE)?:'') ?>" required></span></label>
<label>Password<span class="register-input"><?= $lock ?><input name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" minlength="8" required></span></label>
<label>Ulangi password<span class="register-input"><?= $lock ?><input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Masukkan ulang password" required></span></label>
<label class="activate-consent" style="display:flex;gap:10px;align-items:flex-start;font-weight:600"><input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:0 0 auto"><span>Saya adalah anggota keluarga yang tercantum pada KK ini dan berusia minimal 17 tahun.</span></label>
<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><button class="portal-button register-submit" type="submit">Aktifkan Akun <span aria-hidden="true">&rarr;</span></button></form>
<p style="margin-top:14px;font-size:13px;color:#5b6b82">NIK dan tanggal lahir hanya dicocokkan dengan data KK, tidak disimpan ulang. Maksimal 5 kali percobaan.</p>
<?php endif; ?>
</div></main>
<?php $this->load->view('site/footer',compact('settings')); ?>
