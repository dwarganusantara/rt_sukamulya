<?php
/*
 * Profil RT (publik) — desain merah (assets/citizen-red.css, .prf-*). Header merah untuk warga & tamu.
 * Semua teks & angka dari Pengaturan admin (nama RT, wilayah, tagline, tentang, statistik, alamat).
 */
$title='Profil RT | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='profile';
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$rt_label=trim((string)($settings['brand_label']??'RT 009/008'));
$rt_area=trim((string)($settings['brand_subtitle']??'Suka Mulya IV'));
$site=trim((string)($settings['site_name']??'')) ?: $rt_label.' '.$rt_area;
$tagline=trim((string)($settings['tagline']??'')) ?: 'Website lingkungan '.$rt_label.' '.$rt_area.' sebagai pusat informasi dan layanan warga.';
$about_title=trim((string)($settings['about_title']??'')) ?: 'Tentang '.$rt_label;
$about_text=trim((string)($settings['about_text']??'')) ?: $rt_label.' '.$rt_area.' adalah bagian dari lingkungan masyarakat yang menjunjung tinggi nilai gotong royong, kebersamaan, dan kepedulian antarwarga.';
$hero=trim((string)($settings['profile_page_image']??'')) ?: 'assets/mockup/information-hero-v2.png';
// Foto lingkungan: gambar 'Tentang' dari Pengaturan; bila kosong/masih foto lama (berteks), pakai foto master lingkungan (hero_image).
$photo=trim((string)($settings['about_image']??''));
if($photo===''||$photo==='assets/mockup/about-neighborhood.png'||!is_file(FCPATH.$photo))$photo=trim((string)($settings['hero_image']??''))?:'assets/mockup/hero-neighborhood.png';
$ic=function($d,$s=22){return '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$d.'"/></svg>';};
$I=array(
 'home'=>'M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10','people'=>'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c.6-3.6 3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c2 .7 3.2 2.5 3.5 5.2',
 'person'=>'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c1-4 4-6 8-6s7 2 8 6','male'=>'M10 14a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM14 6l6-6M15 0h5v5','female'=>'M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM12 13v8M9 18h6',
 'info'=>'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 11v5M12 8h.01','phone'=>'M5 4h3l2 5-2.5 1.5a11 11 0 0 0 6 6L15 14l5 2v3a2 2 0 0 1-2 2A15 15 0 0 1 3 6a2 2 0 0 1 2-2z','arrow'=>'M5 12h14M13 6l6 6-6 6');
$stats=array(
 array('people',(int)($settings['households']??0),'Kepala Keluarga','red'),
 array('person',(int)($settings['residents']??0),'Jiwa','blue'),
 array('male',(int)($settings['male_residents']??0),'Laki-laki','green'),
 array('female',(int)($settings['female_residents']??0),'Perempuan','purple'),
);
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="profile-public-page prf-page crd-page">
 <section class="prf-hero">
  <span class="prf-hero-art" aria-hidden="true"><img src="<?= base_url(html_escape($hero)) ?>" alt=""></span>
  <svg class="prf-hero-wave" viewBox="0 0 1600 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V70C300 110 560 60 860 72S1360 120 1600 60v60Z" fill="#fff8fa"/><path d="M860 72C1100 80 1360 116 1600 56v8C1360 124 1100 92 860 80Z" fill="#ffc2cf"/></svg>
  <div class="prf-wrap prf-hero-copy">
   <nav class="prf-crumb" aria-label="Breadcrumb"><a href="<?= site_url($this->session->userdata('citizen_id')?'warga':'') ?>"><?= $ic($I['home'],16) ?>Beranda</a><i aria-hidden="true">›</i><span aria-current="page">Profil <?= html_escape($rt_label) ?></span></nav>
   <span class="anx-eyebrow prf-eyebrow">MENGENAL LINGKUNGAN</span>
   <h1>Profil <?= html_escape($rt_label) ?></h1>
   <h2><?= html_escape($rt_area) ?></h2>
   <p><?= html_escape($tagline) ?></p>
  </div>
 </section>
 <section class="prf-wrap prf-grid">
  <figure class="prf-photo"><img src="<?= base_url(html_escape($photo)) ?>" alt="Lingkungan <?= html_escape($rt_label.' '.$rt_area) ?>" loading="lazy"><figcaption><i aria-hidden="true"></i>“Lingkungan Bersih, Warga Sehat, Silaturahmi Erat”</figcaption></figure>
  <article class="prf-about">
   <h2><?= html_escape($about_title) ?></h2>
   <p><?= nl2br(html_escape($about_text)) ?></p>
   <div class="prf-stats"><?php foreach($stats as $st): ?><div class="prf-tone-<?= $st[3] ?>"><span><?= $ic($I[$st[0]],26) ?></span><p><strong><?= $st[1] ?></strong><small><?= $st[2] ?></small></p></div><?php endforeach; ?></div>
   <a class="prf-btn" href="<?= site_url('struktur') ?>"><?= $ic($I['arrow'],22) ?>Lihat Pengurus RT</a>
  </article>
 </section>
 <nav class="prf-wrap prf-links" aria-label="Navigasi profil">
  <a href="<?= site_url() ?>"><span><?= $ic($I['home'],24) ?></span><p><strong><?= html_escape($site) ?></strong><small><?= html_escape($tagline) ?></small></p></a>
  <a href="<?= site_url('informasi') ?>"><span><?= $ic($I['info'],24) ?></span><p><strong>Informasi</strong><small>Profil RT · Laporan Kas</small></p></a>
  <a href="<?= site_url('kontak') ?>"><span><?= $ic($I['phone'],24) ?></span><p><strong>Kontak</strong><small><?= html_escape(trim((string)($settings['address']??'')) ?: $rt_area) ?></small></p></a>
 </nav>
 <?php $this->load->view('citizen/red_deco'); ?>
</main>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
