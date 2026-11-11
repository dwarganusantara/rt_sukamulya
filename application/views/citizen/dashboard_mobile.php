<?php
// Reuse the menu registry, theme helpers, active citizen, and controller-owned notification data.
$dm_ok=function($route){return $this->feature_gate->link_ok($route,'warga');};
$dm_all=$this->Dashboard_menu_model->all();
$dm_summary=$mobile_summary??array('residents'=>NULL,'heads'=>NULL,'processing'=>NULL);
$dm_notice=(int)($notify['chat']??0);
$dm_banner=trim((string)($settings['citizen_dashboard_page_image']??''));
$dm_custom=$dm_banner!==''&&is_file(FCPATH.$dm_banner)&&!in_array($dm_banner,array($hero_default,'assets/mockup/portal-hero.png'),TRUE);
if(!$dm_custom)$dm_banner='assets/img/dashboard/mobile-gateway.webp';
$dm_stats=array(array('residents','contact_rt','Warga Terdaftar'),array('heads','home','Kepala Keluarga'),array('processing','letters','Surat Saya Diproses'));
?>
<div class="dm-dashboard">
 <header class="dm-header"><a class="dm-brand" href="<?= site_url('warga') ?>"><img src="<?= base_url(html_escape($logo)) ?>" alt="Logo aplikasi"><span><b><?= html_escape($rt_label) ?></b><strong><?= html_escape($rt_area) ?></strong><small><?= html_escape($name) ?></small></span></a><div class="dm-header-actions"><a class="dm-bell" href="<?= site_url($notify['target']??'warga') ?>" aria-label="<?= $dm_notice?$dm_notice.' pesan belum dibaca':'Tidak ada pesan belum dibaca' ?>"><?= dui_line('bell',24) ?><?php if($dm_notice): ?><b><?= $dm_notice>99?'99+':$dm_notice ?></b><?php endif; ?></a><details class="dm-account"><summary aria-label="Profil dan pengaturan akun"><?= dui_line('profile',24) ?></summary><nav aria-label="Pengaturan akun"><?php foreach(array(array('warga/profil','Profil Saya'),array('warga/password','Pengaturan Akun')) as $dm_link):if(!$dm_ok($dm_link[0]))continue; ?><a href="<?= site_url($dm_link[0]) ?>"><?= $dm_link[1] ?></a><?php endforeach; ?><a href="<?= site_url('warga/logout') ?>">Keluar</a></nav></details></div><span class="dm-gold-swoosh" aria-hidden="true"></span></header>
 <main class="dm-main" id="dm-main">
  <section class="dm-banner" aria-label="Lingkungan <?= html_escape($rt_label.' '.$rt_area) ?>"><img src="<?= base_url(html_escape($dm_banner)) ?>" alt="Ilustrasi lingkungan RT" width="1100" height="458" fetchpriority="high" decoding="async"><?php if(!$dm_custom): ?><div class="dm-sign"><b><?= html_escape($rt_label) ?></b><span><?= html_escape($rt_area) ?></span></div><?php endif; ?></section>
  <section class="dm-stats" aria-label="Ringkasan data warga"><?php foreach($dm_stats as $stat): ?><div><span class="dm-stat-icon"><?= dui_line($stat[1],24) ?></span><div><b><?= $dm_summary[$stat[0]]===NULL?'&mdash;':number_format($dm_summary[$stat[0]],0,',','.') ?></b><span><?= $stat[2] ?></span></div></div><?php endforeach; ?></section>
  <?php if($msg=$this->session->flashdata('notice')): ?><p class="dm-notice" role="status"><?= html_escape($msg) ?></p><?php endif; ?>
  <nav class="dm-menu-grid" aria-label="Layanan warga mobile"><?php foreach($dashboard_items as $dm_key=>$dm_item): ?><a href="<?= site_url($dm_item['route']) ?>" data-mobile-menu="<?= html_escape($dm_key) ?>"><span class="dm-menu-icon" aria-hidden="true"><?= dui_icon($dm_item['icon']) ?></span><strong><?= html_escape($dm_item['label']) ?></strong></a><?php endforeach; if(!$dashboard_items): ?><p class="dm-empty">Belum ada layanan yang tersedia. Hubungi pengurus RT.</p><?php endif; ?></nav>
  <div class="dm-decoration" aria-hidden="true"><img src="<?= base_url('assets/img/dashboard/footer-banner-v2.jpg') ?>" alt="" loading="lazy" decoding="async"><span></span></div>
 </main>
<?php
$dm_nav_icon=function($key){
 $art=array(
 'home'=>'<path d="M5 14 16 4l11 10v13a2 2 0 0 1-2 2h-6v-9h-6v9H7a2 2 0 0 1-2-2z" fill="url(#dm-nav-home)"/><path d="M3 15 16 3l13 12" fill="none" stroke="#a9002a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 13 7-6 7 6" fill="none" stroke="#fff" stroke-opacity=".65" stroke-width="1.5"/>',
 'letters'=>'<rect x="7" y="5" width="20" height="25" rx="4" fill="#eac77a"/><path d="M8 2h11l7 7v17a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3z" fill="url(#dm-nav-letters)" stroke="#9c3451" stroke-width="1.2"/><path d="M18 3v7h7" fill="#ffd5df"/><path d="M10 15h11M10 20h11M10 25h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/>',
 'dues'=>'<ellipse cx="13" cy="7" rx="9" ry="4" fill="#ffdf8a" stroke="#bf851e" stroke-width="1.2"/><path d="M4 7v8c0 5 18 5 18 0V7c0 5-18 5-18 0z" fill="#e5b755" stroke="#bf851e" stroke-width="1.2"/><rect x="8" y="12" width="22" height="17" rx="4" fill="url(#dm-nav-dues)" stroke="#8f2040" stroke-width="1.2"/><path d="M24 18h6v6h-6a3 3 0 0 1 0-6" fill="#ffe6a3"/><circle cx="25" cy="21" r="1.2" fill="#98641e"/><path d="M12 16h9" stroke="#fff" stroke-opacity=".6" stroke-width="1.5" stroke-linecap="round"/>',
 'more'=>'<rect x="3" y="3" width="11" height="11" rx="3.5" fill="url(#dm-nav-more)"/><rect x="18" y="3" width="11" height="11" rx="3.5" fill="#eecb7e"/><rect x="3" y="18" width="11" height="11" rx="3.5" fill="#eecb7e"/><rect x="18" y="18" width="11" height="11" rx="3.5" fill="url(#dm-nav-more)"/><path d="M6 6h5M21 21h5" stroke="#fff" stroke-opacity=".65" stroke-width="1.5" stroke-linecap="round"/>'
 );
 return '<span class="dm-nav-icon" aria-hidden="true"><svg viewBox="0 0 32 32" focusable="false"><defs><linearGradient id="dm-nav-'.$key.'" x1="0" y1="0" x2=".6" y2="1"><stop stop-color="#f74c6f"/><stop offset="1" stop-color="#a4002a"/></linearGradient></defs>'.$art[$key].'</svg></span>';
};
?>
 <nav class="dm-bottom" aria-label="Navigasi dashboard mobile"><a href="<?= site_url('warga') ?>" class="is-active" aria-current="page"><?= $dm_nav_icon('home') ?><span>Beranda</span></a><?php if($dm_ok('warga/layanan')): ?><a href="<?= site_url('warga/layanan') ?>"><?= $dm_nav_icon('letters') ?><span>Surat</span></a><?php endif; ?><?php if($dm_ok('warga/iuran')): ?><a href="<?= site_url('warga/iuran') ?>"><?= $dm_nav_icon('dues') ?><span>Iuran</span></a><?php endif; ?><button type="button" id="dm-more-open" aria-haspopup="dialog" aria-controls="dm-more"><?= $dm_nav_icon('more') ?><span>Lainnya</span></button></nav>
 <dialog class="dm-more" id="dm-more" aria-labelledby="dm-more-title"><header><h2 id="dm-more-title">Menu Warga</h2><button type="button" id="dm-more-close" aria-label="Tutup menu">&times;</button></header><nav><?php foreach($dm_all as $dm_key=>$dm_item): if(!$dm_item['allowed']||$dm_key==='home')continue; ?><a href="<?= site_url($dm_item['route']) ?>"><?= official_svg($dm_item['icon']) ?><span><?= html_escape($dm_item['label']) ?></span></a><?php endforeach; ?></nav></dialog>
</div>
<script src="<?= base_url('assets/dashboard-mobile.js') ?>?v=<?= filemtime(FCPATH.'assets/dashboard-mobile.js') ?>" defer></script>
