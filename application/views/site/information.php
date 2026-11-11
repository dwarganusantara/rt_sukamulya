<?php
$title='Informasi | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='info'; // header merah (warga & tamu)
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$rt_label=trim((string)($settings['brand_label']??'RT 009/008'));
$rt_area=trim((string)($settings['brand_subtitle']??'Suka Mulya IV'));
?>
<link rel="stylesheet" href="<?= base_url('assets/information-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/information-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<?php
$directory=array(
  array('keuangan','info-card-kas.png','cash','Laporan Kas RT','Pemasukan, pengeluaran, dan bukti transaksi kas RT.'),
  array('warga/iuran','info-card-iuran.png','dues','Iuran Warga','Masuk untuk melihat status iuran dan riwayat pembayaran Anda.'),
  array('agenda','info-card-agenda.png','agenda','Agenda Kegiatan','Jadwal kegiatan dan acara lingkungan RT.'),
  $pages
    ? array('halaman/'.$pages[0]['slug'],'info-card-profil.png','profile',$pages[0]['title'],'Informasi lengkap struktur, wilayah, dan data warga.')
    : array('profil','info-card-profil.png','profile','Profil Lingkungan','Informasi lengkap struktur, wilayah, dan data warga.'),
);
?>
<main class="inf-page crd-page crd-info">
  <section class="inf-hero">
    <img class="inf-leaf" src="<?= base_url('assets/mockup/info-hero-leaf.png') ?>" alt="">
    <img class="inf-art" src="<?= base_url(html_escape($settings['information_page_image']??'assets/mockup/info-hero-art.png')) ?>" alt="Warga <?= html_escape($rt_label.' '.$rt_area) ?> menyapa di depan gerbang lingkungan">
    <div class="inf-copy">
      <span>INFORMASI LINGKUNGAN</span>
      <h1>Informasi Warga</h1>
      <i class="inf-rule" aria-hidden="true"></i>
      <p>Temukan berbagai informasi penting seputar lingkungan, layanan warga, keuangan RT, agenda kegiatan, dan profil wilayah dalam satu tempat yang mudah diakses.</p>
    </div>
  </section>
  <section class="inf-cards" aria-label="Pilihan informasi">
    <?php foreach($directory as $item): ?>
    <a class="inf-card <?= $item[2] ?>" href="<?= site_url($item[0]) ?>">
      <img src="<?= base_url('assets/mockup/'.$item[1]) ?>" alt="">
      <strong><?= html_escape($item[3]) ?></strong>
      <small><?= html_escape($item[4]) ?></small>
      <span class="inf-arrow" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16M13 5l7 7-7 7"></path></svg></span>
    </a>
    <?php endforeach; ?>
  </section>
  <?php $this->load->view('citizen/red_deco'); ?>
</main>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
