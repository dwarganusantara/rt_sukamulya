<?php
$title='Daftar Akun Warga | '.($settings['site_name']??'Sukamulya');
$citizen_register_layout=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_register_layout'));
$this->load->helper('official_icons');
$register_logo=trim((string)($settings['logo']??''));
if($register_logo===''||!is_file(FCPATH.$register_logo))$register_logo='assets/mockup/logo.png';
$register_label=trim((string)($settings['brand_label']??'RT 009/008'));
$register_area=trim((string)($settings['brand_subtitle']??'Digital Warga Nusantara'));
?>
<link rel="stylesheet" href="<?= base_url('assets/register-warm.css') ?>?v=<?= filemtime(FCPATH.'assets/register-warm.css') ?>">
<main class="rgu-page">
 <section class="rgu-scene" aria-label="Identitas <?= html_escape($register_label) ?>">
  <div class="rgu-brand">
   <a class="rgu-logo" href="<?= site_url() ?>" aria-label="Kembali ke beranda"><img src="<?= base_url(html_escape($register_logo)) ?>" alt="Logo lingkungan"></a>
   <span class="rgu-brand-rule" aria-hidden="true"></span>
   <div><strong>RT/RW Digital</strong><b><?= html_escape($register_label) ?></b><small><?= html_escape($register_area) ?></small></div>
  </div>
  <img class="rgu-scene-art" src="<?= base_url('assets/img/dashboard/mobile-gateway.webp') ?>" alt="Ilustrasi lingkungan RT yang asri" width="1100" height="458">
 </section>
 <section class="rgu-card" aria-labelledby="rgu-title">
  <header class="rgu-heading"><div><span>PORTAL WARGA <i aria-hidden="true"></i></span><h1 id="rgu-title">Daftar Akun Warga</h1><p>Buat akun untuk mengakses layanan, pengajuan surat, dan informasi iuran warga.</p></div><img src="<?= base_url('assets/mockup/login-logo.png') ?>" alt="" aria-hidden="true"></header>
  <?php if($error): ?><div class="portal-error rgu-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
  <form method="post" action="<?= site_url('warga/daftar') ?>" class="rgu-form">
   <label>Nama lengkap<span class="rgu-input"><?= official_svg('person') ?><input name="display_name" maxlength="140" autocomplete="name" placeholder="Masukkan nama lengkap Anda" value="<?= html_escape($this->input->post('display_name',TRUE)?:'') ?>" required autofocus></span></label>
   <label>Jenis kelamin<span class="rgu-input"><?= official_svg('person') ?><select name="gender" required><option value="">Pilih jenis kelamin</option><option value="female" <?= $this->input->post('gender',TRUE)==='female'?'selected':'' ?>>Wanita</option><option value="male" <?= $this->input->post('gender',TRUE)==='male'?'selected':'' ?>>Pria</option></select></span></label>
   <label>Username<span class="rgu-input"><b aria-hidden="true">@</b><input name="username" autocomplete="username" maxlength="80" placeholder="Masukkan username" value="<?= html_escape($this->input->post('username',TRUE)?:'') ?>" required></span></label>
   <label>Password<span class="rgu-input"><?= official_svg('lock') ?><input name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" minlength="8" required></span></label>
   <label>Ulangi password<span class="rgu-input"><?= official_svg('lock') ?><input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Masukkan ulang password" required></span></label>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
   <button class="rgu-submit" type="submit"><span>Daftar Akun</span><?= official_svg('arrow-right') ?></button>
  </form>
  <div class="rgu-switch"><span>Sudah memiliki akun?</span><a href="<?= site_url('warga/login') ?>">Masuk di sini</a></div>
 </section>
 <div class="rgu-bottom" aria-hidden="true"><span></span><span></span></div>
</main>
<?php $this->load->view('site/footer',compact('settings')); ?>
