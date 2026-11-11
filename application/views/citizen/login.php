<?php
/*
 * Masuk Portal Warga — desain sesuai mockup: latar ilustrasi lingkungan layar penuh, kartu login oranye di tengah
 * dengan tab identitas RT di atasnya dan dekorasi rumah di bawah. Latar dapat diganti dari Admin → Gambar Halaman
 * ("Login Warga"). Form & perilaku tetap: username/password, "Ingat saya", lihat password (assets/login-v2.js), CSRF.
 */
$this->load->helper('official_icons');
$site=$settings['site_name']??'RT Digital Warga Nusantara';
$rt_label=trim((string)($settings['brand_label']??'RT 009/008'));
$rt_area=trim((string)($settings['brand_subtitle']??'Suka Mulya IV'));
$logo=$settings['logo']??'assets/mockup/logo.png';
$bg=trim((string)($settings['citizen_login_page_image']??''));
if($bg===''||$bg==='assets/mockup/citizen-login-bg.jpg'||!is_file(FCPATH.$bg))$bg='assets/mockup/login-neighborhood.png';
$words=preg_split('/\s+/',mb_strtoupper($site));$half=(int)ceil(count($words)/2);
$line1=implode(' ',array_slice($words,0,$half));$line2=implode(' ',array_slice($words,$half));
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk Warga | <?= html_escape($site) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/login-warga-v4.css') ?>?v=<?= filemtime(FCPATH.'assets/login-warga-v4.css') ?>">
</head>
<body class="lw4">
<div class="lw4-bg" style="background-image:url('<?= base_url(html_escape($bg)) ?>')" aria-hidden="true"></div>
<main class="lw4-main">
 <div class="lw4-stack">
  <a class="lw4-brand" href="<?= site_url() ?>">
   <img src="<?= base_url(html_escape($logo)) ?>" alt="">
   <span><b><?= html_escape($line1) ?></b><?php if($line2!==''): ?><b><?= html_escape($line2) ?></b><?php endif; ?><small><?= html_escape($rt_label.' '.$rt_area) ?></small></span>
  </a>
  <section class="lw4-card" aria-labelledby="lw4-title">
   <header class="lw4-head">
    <div><h1 id="lw4-title">Masuk ke <span>Portal Warga<svg class="lw4-spark" viewBox="0 0 30 22" aria-hidden="true"><path d="M8 10 4 2M15 9l2-8M20 14l8-3"/></svg></span></h1>
    <p>Akses layanan dan informasi lingkungan RT dengan mudah, cepat, dan terintegrasi.</p></div>
    <svg class="lw4-leaf" viewBox="0 0 64 64" aria-hidden="true"><path d="M34 52C14 50 8 30 16 14c12 10 26 16 24 38z" fill="#6cc04a"/><path d="M36 54c18-6 22-24 16-36-12 8-22 14-16 36z" fill="#4caf3a"/><path d="M34 52 20 22M36 54l12-30" stroke="#2f8a2a" stroke-width="2" fill="none"/></svg>
   </header>
   <?php if($error): ?><div class="lw4-alert bad" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
   <?php if($this->session->flashdata('notice')): ?><div class="lw4-alert good" role="status"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
   <form method="post" data-remember="sukamulya.login.warga" class="lw4-form">
    <label for="lw4-user"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c.6-4.4 3.8-7 8-7s7.4 2.6 8 7z"/></svg>Username</label>
    <span class="lw4-input"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 2.8-6.5 7-6.5s7 2.5 7 6.5"/></svg><input id="lw4-user" name="username" maxlength="80" autocomplete="username" placeholder="Masukkan username" required></span>
    <label for="citizen-login-password"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>Password</label>
    <span class="lw4-input"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg><input id="citizen-login-password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required><button type="button" class="lw4-eye" data-toggle-password="citizen-login-password" aria-label="Tampilkan password" aria-pressed="false"><?= official_svg('eye') ?></button></span>
    <div class="lw4-options"><label><input type="checkbox" name="remember" value="1">Ingat saya</label><a href="<?= site_url('kontak') ?>">Lupa password?</a></div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <button class="lw4-submit" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>Masuk</button>
   </form>
   <p class="lw4-switch">Belum memiliki akun? <a href="<?= site_url('warga/daftar') ?>">Daftar di sini</a></p>
   <svg class="lw4-deco" viewBox="0 0 440 110" preserveAspectRatio="xMidYMax slice" aria-hidden="true"><path d="M0 70c80-30 170-36 250-22s140 6 190-14v76H0z" fill="#ffe2bf"/><path d="M0 88c90-18 190-20 280-8s120 2 160-8v38H0z" fill="#ffd09a"/><g fill="#ff9a2e"><circle cx="70" cy="44" r="16"/><rect x="68" y="56" width="4" height="22"/><circle cx="104" cy="58" r="9" opacity=".7"/><rect x="103" y="64" width="2" height="12" opacity=".7"/><circle cx="378" cy="42" r="17"/><rect x="376" y="55" width="4" height="23"/></g><g fill="#ff7a00"><path d="M172 48l32-26 32 26v36h-64z"/><path d="M226 56l26-20 26 20v28h-52z"/></g><g fill="#fff3e3"><rect x="184" y="58" width="12" height="11"/><rect x="210" y="58" width="12" height="11"/><rect x="246" y="62" width="10" height="9"/></g><path d="M166 50l38-31 38 31M222 58l30-24 30 24" stroke="#e65f00" stroke-width="5" fill="none" stroke-linejoin="round"/></svg>
  </section>
 </div>
</main>
<script src="<?= base_url('assets/login-v2.js') ?>?v=<?= filemtime(FCPATH.'assets/login-v2.js') ?>"></script>
</body></html>
