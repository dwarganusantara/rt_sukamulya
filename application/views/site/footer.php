<?php
$footer_wa = preg_replace('/[^0-9]/', '', (string) ($settings['whatsapp'] ?? ''));
?>
<?php
$show_landing_footer=uri_string()==='';
$footer_banner=trim((string)($settings['footer_banner_jpg']??''));
$has_footer_banner=$footer_banner!=='' && is_file(FCPATH.$footer_banner);
$header_banner=trim((string)($settings['home_banner_jpg']??'')) ?: 'assets/mockup/home-banner.jpg';
$header_size=is_file(FCPATH.$header_banner) ? @getimagesize(FCPATH.$header_banner) : FALSE;
$footer_ratio=$header_size && $header_size[0]>0 && $header_size[1]>0 ? $header_size[0].' / '.$header_size[1] : '3 / 1';
$footer_logo=$settings['logo']??'assets/mockup/logo.png';
$footer_brand=$settings['brand_label']??'RT 009/008';
$footer_subtitle=$settings['brand_subtitle']??'Suka Mulya IV';
$footer_site=$settings['site_name']??($footer_brand.' '.$footer_subtitle);
$footer_tagline=$settings['tagline']??'Website lingkungan sebagai media informasi, komunikasi, dan layanan warga.';
$footer_address=$settings['address']??'';
$footer_email=$settings['email']??'';
$footer_phone=$settings['phone']??'';
$footer_slogan=$settings['slogan']??'Bersama Warga, Lingkungan Lebih Baik';
?>
<?php
if(!isset($this->feature_gate)) $this->load->library('Feature_gate');
$footer_links=array(
 array('profil','Profil Lingkungan','M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M16 3a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0'),
 array('warga/layanan','Layanan Warga','M6 3h9l3 3v15H6zM9 10h6M9 14h6M9 18h4'),
 array('agenda','Agenda Kegiatan','M4 5h16v16H4zM8 3v4M16 3v4M4 11h16M8 15h2M14 15h2'),
 array('keuangan','Laporan & Transparansi','M4 21V13h4v8M10 21V8h4v13M16 21V3h4v18')
);
$footer_art=$has_footer_banner?$footer_banner:'assets/mockup/footer-banner.jpg';
?>
<?php if(!empty($citizen_shell)): ?><div class="dwb dwb-shell"><?php $this->load->view('citizen/shell_footer'); ?></div><?php endif; ?>
<?php if($show_landing_footer): ?>
<link rel="stylesheet" href="<?= base_url('assets/public-footer.css') ?>?v=<?= filemtime(FCPATH.'assets/public-footer.css') ?>">
<footer class="public-footer-banner<?= !empty($home_red)?' pfr':'' ?>" aria-label="Informasi aplikasi dan layanan lingkungan">
 <div class="public-footer-banner-inner">
  <div class="public-footer-art">
   <section class="footer-clear-copy"><span class="footer-clear-eyebrow">TENTANG APLIKASI</span><h2><a href="<?= site_url('profil') ?>"><?php $this->load->helper('dashboard_ui'); ?><?= html_escape(!empty($home_red)?dui_title_case($footer_site):$footer_site) ?></a></h2><p><?= html_escape($footer_tagline) ?></p><a class="footer-demo-link" href="<?= site_url('informasi') ?>">Jelajahi Aplikasi <span aria-hidden="true">&rarr;</span></a></section>
   <nav class="footer-clear-links" aria-label="Menu footer">
    <?php foreach($footer_links as $link): if(!$this->feature_gate->link_ok($link[0],'warga')) continue; ?>
    <a href="<?= site_url($link[0]) ?>"><span class="footer-menu-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $link[2] ?>"/></svg></span><span><?= html_escape($link[1]) ?></span></a>
    <?php endforeach; ?>
   </nav>
   <a class="footer-clear-illustration" href="<?= site_url('informasi') ?>" aria-label="Lihat informasi aplikasi"><img src="<?= base_url(html_escape($footer_art)) ?>" alt="Ilustrasi lingkungan <?= html_escape($footer_brand) ?>" loading="lazy" decoding="async"></a>
   <svg class="footer-ribbon" viewBox="0 0 900 100" preserveAspectRatio="none" aria-hidden="true"><path fill="#e81123" d="M0 0C170 110 220 85 380 67S640 42 900 100H0Z"/><path fill="#fff" d="M0 40C130 120 260 70 430 82S680 55 900 100H0Z"/><path fill="#e3d9d9" d="M0 78C170 45 210 120 400 91S690 88 900 100H0Z"/></svg>
  </div>
  <div class="public-footer-meta"><a href="<?= site_url() ?>">&copy; <?= date('Y') ?> <?= html_escape($footer_site) ?></a><a href="<?= site_url('kontak') ?>">Hubungi pengurus <span aria-hidden="true">&rarr;</span></a></div>
 </div>
</footer>
<?php endif; ?>
<?php if ($footer_wa): ?><a class="wa-float" href="https://wa.me/<?= $footer_wa ?>" target="_blank" rel="noopener" aria-label="WhatsApp RT">WA</a><?php endif; ?>
<?php $this->load->view('site/chat_widget'); ?>
<?php if ($this->session->userdata('citizen_id')) { try { $this->load->view('site/decision_popup'); } catch (Throwable $e) { log_message('error', 'decision_popup: '.$e->getMessage()); } } ?>
<?php $this->load->view('site/mobile_nav'); ?>
</body></html>
