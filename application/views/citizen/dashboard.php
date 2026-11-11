<?php
/*
 * Dashboard Warga (RT Digital) — halaman mandiri: header dua tingkat, banner sambutan, Menu Favorit,
 * menu heksagon, footer. Semua menu berasal dari Dashboard_menu_model (tersaring Manajemen Fitur & Menu).
 */
$this->load->helper(array('dashboard_ui', 'official_icons'));
$this->config->load('dashboard', TRUE);
$name = trim((string) ($citizen['display_name'] ?? $this->session->userdata('citizen_name'))) ?: 'Warga';
$app = dui_title_case($settings['site_name'] ?? 'RT Digital Warga Nusantara');
$rt_label = trim((string) ($settings['brand_label'] ?? 'RT 009/008'));
$rt_area = trim((string) ($settings['brand_subtitle'] ?? 'Suka Mulya IV'));
$slogan = trim((string) ($settings['slogan'] ?? '')) ?: (string) $this->config->item('dashboard_slogan_default', 'dashboard');
$slogan_parts = preg_split('/,\s*(?=[^,]*$)/', $slogan, 2); // "Satu Warga, Satu Keluarga," / "Untuk Lingkungan ..."
$logo = trim((string) ($settings['logo'] ?? '')); if ($logo === '' || !is_file(FCPATH . $logo)) $logo = 'assets/mockup/logo.png';
$quote = trim((string) ($settings['citizen_hero_quote'] ?? '')) ?: (string) $this->config->item('dashboard_quote_default', 'dashboard');
// Gambar bawaan (lama maupun baru) memakai tata letak "scene": papan gapura & kartu kutipan diisi teks dinamis.
$hero_default = (string) $this->config->item('dashboard_hero_default', 'dashboard');
$banner = trim((string) ($settings['citizen_dashboard_page_image'] ?? ''));
$custom_banner = $banner !== '' && is_file(FCPATH . $banner) && !in_array($banner, array($hero_default, 'assets/mockup/portal-hero.png'), TRUE);
if (!$custom_banner) $banner = $hero_default;
// Header & footer: citizen/shell_header + citizen/shell_footer (data dari helper citizen_shell).
$css = 'assets/dashboard-icons.css';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#8d0022">
<title>Portal Warga | <?= html_escape($app) ?></title>
<link rel="icon" href="<?= base_url(html_escape($logo)) ?>">
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<link rel="stylesheet" href="<?= base_url('assets/chat.css') ?>?v=<?= filemtime(FCPATH . 'assets/chat.css') ?>">
<?php $this->load->helper('theme'); echo theme_style($settings); ?>
<link rel="stylesheet" href="<?= base_url('assets/dashboard-mobile.css') ?>?v=<?= filemtime(FCPATH.'assets/dashboard-mobile.css') ?>">
</head>
<body class="dwb dm-enabled">
<a class="dwb-skip" href="#dwb-main">Lewati ke konten</a>
<?php $this->load->view('citizen/shell_header', array('shell_active' => 'home')); ?>

<?php $this->load->view('citizen/dashboard_mobile',compact('settings','logo','rt_label','rt_area','name','notify','hero_default','dashboard_items','mobile_summary')); ?>
<main id="dwb-main" class="dwb-main dwb-wrap">
 <?php if ($this->session->flashdata('notice')): ?><div class="dwb-notice" role="status"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
 <section class="dwhero<?= $custom_banner ? '' : ' dwhero--scene' ?>" aria-label="Selamat datang">
  <?php if ($custom_banner): ?>
  <img class="dwhero-art custom" src="<?= base_url(html_escape($banner)) ?>" alt="" decoding="async" fetchpriority="high">
  <?php else: ?>
  <div class="dwhero-scene">
   <div class="dwhero-stage">
    <img class="dwhero-stage-art" src="<?= base_url(html_escape($banner)) ?>" alt="" decoding="async" fetchpriority="high">
    <p class="dwhero-gate" aria-hidden="true"><b class="dw-fit"><?= html_escape($rt_label) ?></b><span class="dw-fit"><?= html_escape($rt_area) ?></span></p>
    <blockquote class="dwhero-quote"><p><?= html_escape($quote) ?></p></blockquote>
   </div>
  </div>
  <?php endif; ?>
  <div class="dwhero-copy">
   <p class="dwhero-hi">Selamat Datang, <span aria-hidden="true">&#128075;</span></p>
   <h1><?= html_escape($name) ?></h1>
   <p class="dwhero-sub">di Portal Warga Digital</p>
   <p class="dwhero-chip"><?= html_escape(trim($rt_label . ' ' . $rt_area)) ?></p>
   <p class="dwhero-slogan"><?= html_escape($slogan_parts[0] . (isset($slogan_parts[1]) ? ',' : '')) ?><?php if (isset($slogan_parts[1])): ?><br><?= html_escape($slogan_parts[1]) ?><?php endif; ?></p>
  </div>
  <svg class="dwhero-wave" viewBox="0 0 1400 80" preserveAspectRatio="none" aria-hidden="true"><path fill="#fff" d="M0 48C230 92 420 30 690 50s470 34 710-18v48H0Z"/><path fill="#e3062f" d="M0 58C260 96 440 38 700 58s450 30 700-22v10C1150 98 940 76 700 68S250 104 0 70Z"/></svg>
 </section>

 <?php $this->load->view('citizen/dashboard_favorites', compact('dashboard_items', 'dashboard_favorites')); ?>

 <h2 class="dwb-sr">Semua Layanan</h2>
 <?php $this->load->view('citizen/dashboard_tiles', array('dashboard_items' => $dashboard_items, 'dashboard_layout' => 'honey')); ?>
</main>

<?php $this->load->view('citizen/shell_footer'); ?>
<?php $this->load->view('site/chat_widget'); ?>
<?php if ($this->session->userdata('citizen_id')) { try { $this->load->view('site/decision_popup'); } catch (Throwable $e) { log_message('error', 'decision_popup: ' . $e->getMessage()); } } ?>
<script>
(function(){
 // Teks papan gapura mengecil otomatis bila nama RT/wilayah panjang.
 function fit(){document.querySelectorAll('.dw-fit').forEach(function(el){el.style.fontSize='';var n=0,fs=parseFloat(getComputedStyle(el).fontSize);while(el.scrollWidth>el.clientWidth+1&&n++<14){fs*=.93;el.style.fontSize=fs+'px'}})}
 fit();window.addEventListener('resize',fit);if(document.fonts&&document.fonts.ready)document.fonts.ready.then(fit);
})();
</script>
</body>
</html>
