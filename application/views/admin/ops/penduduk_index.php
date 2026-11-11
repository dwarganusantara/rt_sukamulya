<?php
$this->load->helper(array('official_icons', 'page_ui'));
$base = 'admin/penduduk';
$link = function ($extra) use ($filters, $view) {return site_url('admin/penduduk') . '?' . http_build_query(array_filter(array_merge($filters, array('tampil' => $view), $extra), function ($v) {return $v !== '' && $v !== NULL;}));};
$tone_map = array('ok' => 'ok', 'bad' => 'bad', 'pending' => 'warn', 'info' => 'info', 'purple' => 'purple', 'muted' => 'muted');
$can_mut = $can('mutasi.review');
$is_family = $view === 'keluarga';
?>
<div class="pg">
 <?= pg_crumb(array(array('Kependudukan', NULL), array('Data Penduduk', NULL))) ?>
 <section class="pg-hero"><span class="pg-hero-ic"><?= official_svg('people') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Kependudukan</span><h1>Data Penduduk</h1><p>Daftar KK dan anggota keluarga yang sudah diverifikasi. Perubahan data dilakukan lewat Verifikasi KK atau Mutasi Warga, bukan diedit langsung di sini.</p></div></section>
 <?= ops_flash() ?>

 <section class="pg-stats" aria-label="Ringkasan penduduk">
  <a class="pg-stat" href="<?= site_url($base . '?tampil=keluarga') ?>"><span class="pg-stat-ic"><?= official_svg('people') ?></span><span class="pg-stat-tx"><small>KK Terdaftar</small><strong><?= (int) $stats['families'] ?></strong><em>Keluarga terverifikasi</em></span><span class="pg-stat-mark"><?= official_svg('people') ?></span></a>
  <a class="pg-stat green" href="<?= site_url($base) ?>"><span class="pg-stat-ic"><?= official_svg('person') ?></span><span class="pg-stat-tx"><small>Warga Aktif</small><strong><?= (int) $stats['aktif'] ?></strong><em>L: <?= (int) $stats['L'] ?> &nbsp; P: <?= (int) $stats['P'] ?></em></span><span class="pg-stat-mark"><?= official_svg('check-circle') ?></span></a>
  <a class="pg-stat amber" href="<?= site_url($base . '?domisili=belum') ?>"><span class="pg-stat-ic"><?= official_svg('home') ?></span><span class="pg-stat-tx"><small>Domisili Belum Diisi</small><strong><?= (int) $stats['no_domicile'] ?></strong><em>Perlu dilengkapi lewat mutasi</em></span><span class="pg-stat-mark"><?= official_svg('alert') ?></span></a>
  <a class="pg-stat purple" href="<?= site_url($base . '?status=semua') ?>"><span class="pg-stat-ic"><?= official_svg('swap') ?></span><span class="pg-stat-tx"><small>Pindah / Meninggal</small><strong><?= (int) $stats['pindah'] ?> / <?= (int) $stats['meninggal'] ?></strong><em>Tersimpan sebagai histori</em></span><span class="pg-stat-mark"><?= official_svg('swap') ?></span></a>
 </section>

 <div class="pg-bar">
  <nav class="pg-tabs" aria-label="Tampilan data">
   <a class="pg-tab" href="<?= site_url($base) ?>"<?= $is_family ? '' : ' aria-current="page"' ?>><?= official_svg('people') ?>Per Anggota</a>
   <a class="pg-tab" href="<?= site_url($base . '?tampil=keluarga') ?>"<?= $is_family ? ' aria-current="page"' : '' ?>><?= official_svg('home') ?>Per Keluarga</a>
  </nav>
  <div class="pg-actions">
   <a class="pg-btn ghost" href="<?= site_url('admin/penduduk/rekap') ?>"><?= official_svg('chart') ?>Rekap Statistik</a>
   <?php if ($can_mut): ?><a class="pg-btn solid" href="<?= site_url('admin/mutasi/baru') ?>"><?= official_svg('plus') ?>Catat Mutasi</a><?php endif; ?>
  </div>
 </div>

 <form class="pg-filter" method="get" action="<?= site_url($base) ?>" role="search">
  <input type="hidden" name="tampil" value="<?= ops_e($view) ?>">
  <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari penduduk</span><input type="search" name="q" value="<?= ops_e($filters['q']) ?>" maxlength="100" placeholder="<?= $is_family ? 'Cari kepala keluarga, nama anggota, atau alamat...' : 'Cari nama, alamat, atau pekerjaan...' ?>"></label>
  <div class="pg-filter-row">
   <?php if (!$is_family): ?>
   <label class="pg-sel ok"><?= official_svg('check-circle') ?><span>Status:</span><select name="status" aria-label="Status anggota"><?= ops_options($status_labels + array('semua' => 'Semua'), $filters['status']) ?></select></label>
   <label class="pg-sel"><?= official_svg('home') ?><span>Domisili:</span><select name="domisili" aria-label="Status domisili"><?= ops_options($domicile + array('belum' => 'Belum diisi'), $filters['domisili'], 'Semua') ?></select></label>
   <label class="pg-sel"><?= official_svg('people') ?><span>Jenis Kelamin:</span><select name="jk" aria-label="Jenis kelamin"><?= ops_options(array('L' => 'Laki-laki', 'P' => 'Perempuan'), $filters['jk'], 'L/P') ?></select></label>
   <label class="pg-sel"><?= official_svg('calendar') ?><span>Usia:</span><select name="usia" aria-label="Kelompok usia"><?= ops_options($age_groups, $filters['usia'], 'Semua Usia') ?></select></label>
   <?php endif; ?>
   <button class="pg-btn solid" type="submit"><?= official_svg('filter') ?>Filter</button>
   <?php if (!$is_family && $can('penduduk.export')): ?><a class="pg-btn ghost" href="<?= site_url('admin/penduduk/ekspor') . '?' . ops_e($query) ?>" title="NIK dan nomor KK dimasking"><?= official_svg('download') ?>Ekspor CSV</a><?php endif; ?>
  </div>
 </form>

 <?php if ($is_family): ?>
 <section class="pg-card"><header><h2><?= official_svg('home') ?>Keluarga</h2><span class="pg-meta"><?= official_svg('people') ?><?= (int) $total ?> KK</span></header>
  <table class="pg-table"><thead><tr><th>Kepala Keluarga / No. KK</th><th>Alamat</th><th class="num">Anggota Aktif</th><th>Akun Pengaju</th><th>Diperbarui</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
   <tr>
    <td class="lead"><span class="pg-person"><span class="pg-av" aria-hidden="true"><?= ops_e(mb_strtoupper(mb_substr($r['head'] ?: 'K', 0, 1))) ?></span><span><strong><?= ops_e($r['head'] ?: '—') ?></strong><small class="pg-mono"><?= ops_e(rt_mask_id($r['nomor_kk'])) ?></small></span></span></td>    <td data-label="Alamat"><?= ops_e($r['alamat'] ?: '—') ?><?php if ($r['rt'] !== '' || $r['rw'] !== ''): ?><br><small class="pg-meta">RT <?= ops_e($r['rt'] ?: '-') ?> / RW <?= ops_e($r['rw'] ?: '-') ?></small><?php endif; ?></td>
    <td data-label="Anggota Aktif" class="num"><?= (int) $r['active'] ?><?php if ($r['total'] > $r['active']): ?> <small class="pg-meta">dari <?= (int) $r['total'] ?></small><?php endif; ?></td>
    <td data-label="Akun Pengaju"><?= ops_e($r['owner'] ?: '—') ?></td>
    <td data-label="Diperbarui"><?= ops_date($r['updated_at']) ?></td>
    <td class="acts"><div class="pg-acts"><a class="pg-edit" href="<?= site_url('admin/penduduk/keluarga/' . (int) $r['id']) ?>"><?= official_svg('eye') ?>Detail</a>
     <?php if ($can_mut): ?><details class="pg-menu"><summary aria-label="Menu keluarga <?= ops_e($r['head']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/mutasi/baru?keluarga=' . (int) $r['id'] . '&jenis=masuk') ?>"><?= official_svg('plus') ?>Catat anggota masuk</a>
      <a href="<?= site_url('admin/mutasi/baru?keluarga=' . (int) $r['id']) ?>"><?= official_svg('swap') ?>Catat mutasi lain</a>
     </div></details><?php endif; ?></div></td>
   </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="pg-empty">Belum ada KK terverifikasi<?= $filters['q'] !== '' ? ' yang cocok dengan pencarian' : '' ?>. KK masuk ke sini setelah pengajuan di Verifikasi KK disetujui.</td></tr><?php endif; ?>
  </tbody></table>
 </section>
 <?php else: $offset = ($page - 1) * 50; ?>
 <section class="pg-card"><header><h2><?= official_svg('people') ?>Anggota Keluarga</h2><span class="pg-meta"><?= official_svg('people') ?><?= (int) $total ?> orang sesuai filter</span></header>
  <table class="pg-table"><thead><tr><th class="no">No</th><th>Nama</th><th>NIK</th><th class="c">L/P</th><th class="num">Umur</th><th>Hubungan</th><th class="c">Domisili</th><th class="c">Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $m): $active = $m['status'] === 'aktif'; $mut = site_url('admin/mutasi/baru') . '?keluarga=' . (int) $m['family_id'] . '&anggota=' . (int) $m['id'] . '&jenis='; ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td class="lead"><span class="pg-person"><span class="pg-av<?= $m['jk'] === 'L' ? ' male' : '' ?>" aria-hidden="true"><?= ops_e(mb_strtoupper(mb_substr($m['nama'] ?: '?', 0, 1))) ?></span><span><strong><?= ops_e(mb_strtoupper($m['nama'] ?: '—')) ?></strong><small><?= ops_e(mb_substr($m['alamat'], 0, 48)) ?></small></span></span></td>
    <td data-label="NIK"><span class="pg-mono"><?= ops_e(rt_mask_id($m['nik'])) ?></span></td>
    <td data-label="L/P" class="c"><span class="pg-sex <?= ops_e($m['jk']) ?>"><?= ops_e($m['jk'] ?: '—') ?></span></td>
    <td data-label="Umur" class="num"><?= $m['age'] === NULL ? '—' : (int) $m['age'] ?></td>
    <td data-label="Hubungan"><?= ops_e($m['hubungan'] ?: '—') ?></td>
    <td data-label="Domisili" class="c"><?= $m['domicile_status'] !== '' ? '<span class="pg-chip set">' . ops_e($domicile[$m['domicile_status']] ?? $m['domicile_status']) . '</span>' : '<span class="pg-chip">Belum diisi</span>' ?></td>
    <td data-label="Status" class="c"><span class="pg-pill <?= $tone_map[$status_tones[$m['status']] ?? 'muted'] ?? 'muted' ?>"><?= ops_e($status_labels[$m['status']] ?? $m['status']) ?></span></td>
    <td class="acts"><div class="pg-acts">
     <?php if ($can_mut && $active): ?><a class="pg-edit icon" href="<?= $mut ?>ubah_data" title="Koreksi data lewat mutasi" aria-label="Koreksi data <?= ops_e($m['nama']) ?>"><?= official_svg('pencil') ?></a><?php endif; ?>
     <details class="pg-menu"><summary aria-label="Menu lain untuk <?= ops_e($m['nama']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/penduduk/keluarga/' . (int) $m['family_id']) ?>"><?= official_svg('eye') ?>Lihat keluarga</a>
      <?php if ($can_mut && $active): ?>
      <a href="<?= $mut ?>ubah_data"><?= official_svg('pencil') ?>Koreksi data</a>
      <a href="<?= $mut ?>pindah"><?= official_svg('swap') ?>Catat pindah keluar</a>
      <a href="<?= $mut ?>meninggal"><?= official_svg('heart') ?>Catat meninggal</a>
      <?php endif; ?>
     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="9" class="pg-empty">Tidak ada anggota keluarga untuk filter ini.</td></tr><?php endif; ?>
  </tbody></table>
 </section>
 <?php endif; ?>

 <?php if ($pages > 1): ?><div class="pg-foot"><p>Halaman <b><?= (int) $page ?></b> dari <b><?= (int) $pages ?></b></p><?= pg_pager($page, $pages, function ($n) use ($link) {return $link(array('hal' => $n));}) ?></div><?php endif; ?>

 <?php if (!$is_family && $can('penduduk.export_full')): ?>
 <details class="pg-more"><summary><?= official_svg('download') ?>Ekspor dengan NIK &amp; nomor KK lengkap</summary><div>
  <p class="ops-note red">Berkas berisi data pribadi lengkap. Gunakan hanya untuk keperluan resmi (mis. pelaporan ke kelurahan), jangan dibagikan di grup, dan hapus setelah selesai. Alasan dan jumlah baris dicatat di Audit Aktivitas.</p>
  <form method="post" action="<?= site_url('admin/penduduk/ekspor') ?>" class="ops-inline"><?= ops_csrf() ?>
   <input type="hidden" name="full" value="1">
   <?php foreach ($filters as $k => $v): ?><input type="hidden" name="<?= ops_e($k) ?>" value="<?= ops_e($v) ?>"><?php endforeach; ?>
   <?= ops_field('reason', 'Alasan ekspor', 'text', '', array('required' => TRUE, 'maxlength' => 200, 'placeholder' => 'Contoh: Rekap data penduduk untuk kelurahan, Nov 2026')) ?>
   <button class="ops-btn red outline" type="submit" onclick="return confirm('Unduh data penduduk dengan NIK lengkap? Tindakan ini dicatat.')">Unduh CSV Lengkap</button>
  </form>
 </div></details>
 <?php endif; ?>
</div>
