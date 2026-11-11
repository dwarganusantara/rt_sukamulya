<?php
$this->load->helper(array('official_icons', 'page_ui'));
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$day = function ($d) use ($short) {$t = strtotime((string) $d); return $t ? date('d', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '—';};
$c = function ($k) use ($counts) {return (int) ($counts[$k] ?? 0);};
$waiting = $c('submitted') + $c('reopened');
$process = $c('triaged') + $c('assigned') + $c('in_progress') + $c('waiting_resident');
$done = $c('resolved') + $c('closed');
$all = array_sum(array_map('intval', $counts));
$tone_map = array('ok' => 'ok', 'bad' => 'bad', 'pending' => 'warn', 'info' => 'info', 'purple' => 'purple', 'muted' => 'muted');
$prio_tone = array('mendesak' => 'bad', 'tinggi' => 'warn', 'normal' => 'info', 'rendah' => 'muted');
$url = function ($extra = array()) use ($status, $category, $q) {
    $p = array_filter(array_merge(array('status' => $status === 'open' ? '' : $status, 'kategori' => $category, 'q' => $q), $extra), function ($v) {return $v !== '' && $v !== NULL;});
    return site_url('admin/pengaduan') . ($p ? '?' . http_build_query($p) : '');
};
$filtered = $q !== '' || $category !== '' || $status !== 'open';
$offset = ($page - 1) * 15;
?>
<div class="pg">
 <?= pg_crumb(array(array('Layanan Warga', NULL), array('Pengaduan & Aspirasi', NULL))) ?>
 <section class="pg-hero pg-hero-care pg-hero-note"><span class="pg-hero-ic"><?= official_svg('megaphone') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Layanan Warga</span><h1>Pengaduan &amp; Aspirasi</h1><p>Tiket laporan, keluhan, atau usulan warga terkait lingkungan, fasilitas, maupun pelayanan. Tentukan prioritas dan PIC, lalu tanggapi hingga selesai.</p></div><span class="pg-note-art chat" aria-hidden="true"></span></section>
 <?= ops_flash() ?>

 <section class="pg-stats" aria-label="Ringkasan pengaduan">
  <a class="pg-stat pg-stat-line" href="<?= site_url('admin/pengaduan?status=submitted') ?>"><span class="pg-stat-ic soft"><?= official_svg('tag') ?></span><span class="pg-stat-tx"><strong><?= $waiting ?></strong><small>Tiket Terbuka</small><em>Perlu ditindaklanjuti</em></span></a>
  <a class="pg-stat pg-stat-line blue" href="<?= site_url('admin/pengaduan') ?>"><span class="pg-stat-ic soft"><?= official_svg('clock') ?></span><span class="pg-stat-tx"><strong><?= $process ?></strong><small>Dalam Proses</small><em>Sedang ditangani</em></span></a>
  <a class="pg-stat pg-stat-line purple" href="<?= site_url('admin/pengaduan?status=resolved') ?>"><span class="pg-stat-ic soft"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><strong><?= $done ?></strong><small>Selesai</small><em>Ditangani / ditutup</em></span></a>
  <a class="pg-stat pg-stat-line green" href="<?= site_url('admin/pengaduan?status=all') ?>"><span class="pg-stat-ic soft"><?= official_svg('plus') ?></span><span class="pg-stat-tx"><strong><?= $all ?></strong><small>Total Laporan</small><em>Seluruh pengaduan</em></span></a>
 </section>

 <form class="pg-filter" method="get" action="<?= site_url('admin/pengaduan') ?>" role="search">
  <div class="pg-filter-row">
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari tiket</span><input type="search" name="q" value="<?= ops_e($q) ?>" maxlength="80" placeholder="Cari nomor tiket, judul, lokasi, atau nama pelapor..."></label>
   <label class="pg-sel"><?= official_svg('list') ?><span class="sr-only">Kategori</span><select name="kategori" onchange="this.form.submit()"><?= ops_options($categories, $category, 'Semua Kategori') ?></select></label>
   <label class="pg-sel"><?= official_svg('chart') ?><span class="sr-only">Status</span><select name="status" onchange="this.form.submit()"><?= ops_options(array('open' => 'Terbuka (belum selesai)', 'all' => 'Semua Status') + $labels, $status) ?></select></label>
   <button class="pg-btn solid" type="submit"><?= official_svg('filter') ?>Filter</button>
  </div>
 </form>

 <section class="pg-card">
  <table class="pg-table"><thead><tr><th class="no">No</th><th>Nomor Tiket</th><th>Judul Laporan</th><th>Kategori</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal</th><th>Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $i => $it): $t = strtotime((string) $it['created_at']); ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td data-label="Nomor Tiket"><strong class="pg-mono"><?= ops_e($it['ticket_no']) ?></strong></td>
    <td class="lead"><span class="pg-stack"><strong><?= ops_e($it['title']) ?></strong><?php if ($it['location']): ?><small><?= ops_e($it['location']) ?></small><?php endif; ?></span></td>
    <td data-label="Kategori"><?= ops_e($categories[$it['category']] ?? $it['category']) ?></td>
    <td data-label="Prioritas"><span class="pg-pill <?= $prio_tone[$it['priority']] ?? 'muted' ?>"><?= ops_e($priorities[$it['priority']] ?? $it['priority']) ?></span></td>
    <td data-label="Pelapor"><span class="pg-stack"><span><?= ops_e($it['display_name'] ?: '-') ?></span><?php if ($it['pic']): ?><small>PIC: <?= ops_e($it['pic']) ?></small><?php endif; ?></span></td>
    <td data-label="Tanggal"><span class="pg-date"><?= $day($it['created_at']) ?><small><?= $t ? date('H:i', $t) : '' ?></small></span></td>
    <td data-label="Status"><span class="pg-pill <?= $tone_map[$tones[$it['status']] ?? 'muted'] ?? 'muted' ?>"><?= ops_e($labels[$it['status']] ?? $it['status']) ?></span></td>
    <td class="acts"><div class="pg-acts"><a class="pg-edit" href="<?= site_url('admin/pengaduan/' . (int) $it['id']) ?>"><?= official_svg(in_array($it['status'], $open, TRUE) ? 'pencil' : 'eye') ?><?= in_array($it['status'], $open, TRUE) ? 'Tangani' : 'Lihat' ?></a></div></td>
   </tr>
  <?php endforeach; if (!$items): ?>
   <tr><td colspan="9" class="pg-empty"><div class="pg-empty-box">
    <span class="pg-empty-art" aria-hidden="true"></span>
    <?php if ($filtered && $all): ?><strong>Tidak ada tiket untuk filter ini</strong><p>Ubah kata kunci, kategori, atau status untuk melihat tiket lain.</p><a class="pg-btn ghost" href="<?= site_url('admin/pengaduan?status=all') ?>"><?= official_svg('list') ?>Lihat Semua Tiket</a>
    <?php elseif ($all): ?><strong>Tidak ada tiket terbuka</strong><p>Semua pengaduan sudah ditangani. Tiket baru akan muncul di sini.</p><a class="pg-btn ghost" href="<?= site_url('admin/pengaduan?status=all') ?>"><?= official_svg('list') ?>Lihat Riwayat Tiket</a>
    <?php else: ?><strong>Belum ada pengaduan</strong><p>Tiket pengaduan akan muncul di sini setelah warga mengirimkan laporan.</p><a class="pg-btn solid" href="<?= site_url('warga/pengaduan/buat') ?>" target="_blank" rel="noopener" title="Formulir pengaduan di portal warga (perlu login warga)"><?= official_svg('plus') ?>Buat Pengaduan Baru</a><?php endif; ?>
   </div></td></tr>
  <?php endif; ?>
  </tbody></table>
  <?php if ($items): ?><div class="pg-foot pg-foot-in"><p>Menampilkan <b><?= count($items) ?></b> dari <b><?= (int) $matched ?></b> tiket</p><?= pg_pager($page, $pages, function ($n) use ($url) {return $url(array('hal' => $n));}) ?></div><?php endif; ?>
 </section>
</div>
