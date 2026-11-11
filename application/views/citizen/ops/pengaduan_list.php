<?php $open_count = 0; foreach ($items as $it) if (in_array($it['status'], $open, TRUE)) $open_count++; ?>
<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
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
