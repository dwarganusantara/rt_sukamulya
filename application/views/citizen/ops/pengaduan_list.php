<?php $open_count = 0; foreach ($items as $it) if (in_array($it['status'], $open, TRUE)) $open_count++; ?>
<link rel="stylesheet" href="<?= base_url('assets/mobile-warm.css') ?>?v=<?= filemtime(FCPATH.'assets/mobile-warm.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/pengaduan-mobile.css') ?>?v=<?= filemtime(FCPATH.'assets/pengaduan-mobile.css') ?>">
<main class="letter-page cdx-page crd-page crd-ops mw-page pgm-page">
 <header class="mw-header mw-only"><?php $this->load->view('site/mobile_brand_header',array('settings'=>$settings??$this->Content_model->settings())); ?></header>
 <?php
 $pgh_group=function($status)use($open){
  if(in_array($status,$open??array(),TRUE))return 'process';
  if(in_array($status,array('resolved','closed'),TRUE))return 'done';
  if($status==='rejected')return 'rejected';
  return 'draft';
 };
 ?>
 <section class="pgh mw-only" aria-labelledby="pgh-title">
  <a class="pgh-back" href="<?= site_url('warga') ?>"><?= official_svg('back') ?><span>Kembali</span></a>
  <section class="pgh-hero">
   <span class="pgh-hero-icon" aria-hidden="true"><?= official_svg('megaphone') ?></span>
   <h1 id="pgh-title">Riwayat <b>Pengaduan</b></h1>
   <img src="<?= base_url('assets/img/letters/mw-req-scene.png') ?>" alt="" aria-hidden="true">
  </section>
  <div class="pgh-stats" aria-label="Ringkasan pengaduan">
   <article><span><?= official_svg('doc') ?></span><div><small>Total</small><strong><?= count($items) ?></strong></div></article>
   <article class="is-process"><span><?= official_svg('clock') ?></span><div><small>Diproses</small><strong><?= $open_count ?></strong></div></article>
   <article class="is-done"><span><?= official_svg('check-circle') ?></span><div><small>Selesai</small><strong><?= count($items)-$open_count ?></strong></div></article>
  </div>
  <div class="pgh-tools">
   <label class="pgh-search"><?= official_svg('search') ?><span class="sr-only">Cari pengaduan</span><input type="search" id="pgh-search" placeholder="Cari pengaduan..." autocomplete="off"></label>
   <label class="pgh-filter"><?= official_svg('filter') ?><span>Filter</span><select id="pgh-filter" aria-label="Filter status"><option value="all">Semua</option><option value="process">Diproses</option><option value="done">Selesai</option><option value="rejected">Ditolak</option></select></label>
  </div>
  <ol class="pgh-list" id="pgh-list">
   <?php foreach($items as $it): $pgh_status=$pgh_group($it['status']); ?>
   <li data-pgh-status="<?= $pgh_status ?>" data-pgh-search="<?= html_escape(mb_strtolower($it['ticket_no'].' '.$it['title'].' '.($categories[$it['category']]??''),'UTF-8')) ?>">
    <span class="pgh-ticket-icon" aria-hidden="true"><?= official_svg('doc') ?></span>
    <div class="pgh-ticket-copy"><strong><?= html_escape($it['ticket_no']) ?></strong><span><?= html_escape($it['title']) ?></span><time datetime="<?= html_escape(substr($it['created_at'],0,10)) ?>"><?= official_svg('calendar') ?><?= ops_date($it['created_at']) ?></time></div>
    <span class="pgh-badge is-<?= $pgh_status ?>"><?php if($pgh_status==='done'): ?><?= official_svg('check') ?><?php elseif($pgh_status==='process'): ?><?= official_svg('clock') ?><?php else: ?><?= official_svg('doc') ?><?php endif; ?><?= html_escape($labels[$it['status']]??$it['status']) ?></span>
    <a class="pgh-detail" href="<?= site_url('warga/pengaduan/'.(int)$it['id']) ?>" aria-label="Lihat <?= html_escape($it['ticket_no']) ?>"><?= official_svg('chev-right') ?></a>
   </li>
   <?php endforeach; ?>
  </ol>
  <p class="pgh-empty" id="pgh-empty"<?= $items?' hidden':'' ?>><?= $items?'Tidak ada pengaduan yang cocok.':'Belum ada pengaduan. Buat laporan pertama Anda.' ?></p>
  <a class="pgh-create" href="<?= site_url('warga/pengaduan/buat') ?>"><?= official_svg('plus') ?><span>Buat Pengaduan Baru</span></a>
 </section>
<div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Layanan Warga', 'warga/layanan'), array('Pengaduan & Aspirasi', '')))); ?>
 <section class="cdx-hero cpx-warm crd-hero"><span class="crd-hero-art" aria-hidden="true"><img src="<?= base_url('assets/mockup/announcement-hero-v2.png') ?>" alt=""></span><span class="cdx-hero-icon"><?= official_svg('megaphone') ?></span><div><h1>Pengaduan &amp; <b>Aspirasi</b></h1><p>Laporkan masalah lingkungan atau sampaikan usulan. Setiap laporan mendapat nomor tiket yang bisa Anda pantau.</p></div></section>
 <?php $this->load->view('citizen/ops/_flash'); ?>
 <section class="cdx-metrics four" aria-label="Ringkasan pengaduan">
  <article class="blue"><span class="cdx-icon"><?= official_svg('doc') ?></span><div><small>Total Tiket</small><strong><?= count($items) ?></strong><em>Semua laporan</em></div></article>
  <article class="orange"><span class="cdx-icon"><?= official_svg('clock') ?></span><div><small>Masih Diproses</small><strong><?= $open_count ?></strong><em>Dalam proses</em></div></article>
  <article class="green"><span class="cdx-icon"><?= official_svg('check-circle') ?></span><div><small>Selesai</small><strong><?= count($items) - $open_count ?></strong><em>Selesai / ditutup</em></div></article>
  <article class="red"><span class="cdx-icon"><?= official_svg('alert') ?></span><div><small>Darurat?</small><strong style="font-size:15px">Hubungi layanan darurat</strong><em><a href="<?= site_url('warga/kontak-penting') ?>">Lihat kontak penting</a></em><a class="crd-metric-go" href="<?= site_url('warga/kontak-penting') ?>" aria-label="Lihat kontak penting">&rsaquo;</a></div></article>
 </section>
 <section class="cdx-card">
  <header class="cdx-head"><span class="red"><?= official_svg('list') ?></span><div><h2>Tiket saya</h2><p>Laporan yang pernah Anda kirim.</p></div><a class="cdx-report crd-btn-solid" href="<?= site_url('warga/pengaduan/buat') ?>"><?= official_svg('plus') ?>Buat Pengaduan</a></header>
  <div class="cdx-scroll"><table class="cdx-table ops-table stack"><thead><tr><th>Tiket</th><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $it): ?>
   <tr><td data-label="Tiket"><strong><?= html_escape($it['ticket_no']) ?></strong></td><td data-label="Tanggal"><?= ops_date($it['created_at']) ?></td><td data-label="Judul"><?= html_escape($it['title']) ?></td><td data-label="Kategori"><?= html_escape($categories[$it['category']] ?? '') ?></td><td data-label="Status"><?= ops_badge($it['status'], $labels, $tones) ?></td><td data-label="Aksi"><a class="cpx-detail" href="<?= site_url('warga/pengaduan/' . (int) $it['id']) ?>">Detail</a></td></tr>
  <?php endforeach; if (!$items): ?><tr><td colspan="6" class="ops-empty">Belum ada pengaduan. Gunakan tombol "Buat Pengaduan" untuk melapor.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
<script src="<?= base_url('assets/complaint-history.js') ?>?v=<?= filemtime(FCPATH.'assets/complaint-history.js') ?>" defer></script>
