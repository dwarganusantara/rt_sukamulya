<?php
$this->load->helper(array('official_icons', 'page_ui'));
$list_url = function ($extra = array()) use ($q, $status, $sort, $kk) {
    $p = array_filter(array_merge(array('q' => $q, 'status' => $status, 'kk' => $kk, 'sort' => $sort === 'newest' ? '' : $sort), $extra), function ($v) {return $v !== '' && $v !== NULL && $v !== 1;});
    return site_url('admin/citizens') . ($p ? '?' . http_build_query($p) : '');
};
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$offset = ($page - 1) * $per_page;
$fmt_date = function ($v) use ($short) {$t = strtotime((string) $v); return $t ? date('j', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';};
$reason_label = array('nama' => 'Nama sama', 'telepon' => 'Telepon sama', 'email' => 'Email sama');
?>
<div class="pg">
 <?= pg_crumb(array(array('Dashboard', 'admin'), array('Akun Portal Warga', NULL))) ?>
 <section class="pg-hero"><span class="pg-hero-ic"><?= official_svg('people') ?></span><div class="pg-hero-tx"><h1>Akun Portal Warga</h1><p>Akun login warga untuk layanan online. Data keluarga ada di Data Keluarga / KK, data individu di Data Penduduk.</p></div></section>

 <section class="pg-stats" aria-label="Ringkasan akun">
  <a class="pg-stat" href="<?= site_url('admin/citizens') ?>"><span class="pg-stat-ic"><?= official_svg('people') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['total'] ?></strong><small>Total Akun</small><em>Seluruh akun portal warga</em></span><span class="pg-stat-mark"><?= official_svg('people') ?></span></a>
  <a class="pg-stat green" href="<?= site_url('admin/citizens?status=active') ?>"><span class="pg-stat-ic"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['active'] ?></strong><small>Akun Aktif</small><em>Dapat mengakses layanan</em></span><span class="pg-stat-mark"><?= official_svg('person') ?></span></a>
  <?php if ($has_kk): ?><a class="pg-stat amber" href="<?= site_url('admin/citizens?status=active&kk=belum') ?>"><span class="pg-stat-ic"><?= official_svg('clock') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['no_kk'] ?></strong><small>Belum Verifikasi KK</small><em>Akun aktif belum tertaut ke KK</em></span><span class="pg-stat-mark"><?= official_svg('hourglass') ?></span></a><?php endif; ?>
  <a class="pg-stat red" href="<?= site_url('admin/citizens?status=inactive') ?>"><span class="pg-stat-ic"><?= official_svg('close') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['inactive'] ?></strong><small>Akun Nonaktif</small><em>Tidak dapat mengakses</em></span><span class="pg-stat-mark"><?= official_svg('person') ?></span></a>
 </section>

 <?php if ($duplicates && !$show_dupes): ?>
 <section class="pg-card"><p class="pg-note"><b><?= count($duplicates) ?> kelompok kemungkinan akun ganda</b> terdeteksi (nama, telepon, atau email sama). <a href="<?= $list_url(array('tampil' => 'duplikat', 'hal' => NULL)) ?>">Tinjau sekarang</a></p></section>
 <?php endif; ?>

 <?php if ($show_dupes): ?>
 <section class="pg-card" aria-labelledby="dupe-title">
  <h2 id="dupe-title" class="pg-sub" style="border-top:0;padding-top:0">Deteksi Akun Ganda</h2>
  <p class="pg-note">Kesamaan nama belum cukup untuk menyatakan duplikat. Verifikasi username, nomor telepon, dan identitas pemilik lebih dulu. Akun yang terbukti ganda <b>dinonaktifkan</b> lewat Edit (bukan dihapus), dan akun yang sudah tertaut ke KK dipertahankan. <a href="<?= $list_url(array('tampil' => NULL)) ?>">Tutup</a></p>
  <?php if (!$duplicates): ?><p class="pg-empty">Tidak ada kemungkinan akun ganda.</p><?php endif; ?>
  <?php foreach ($duplicates as $gi => $group): ?>
  <table class="pg-table" style="margin-top:12px"><caption class="pg-note" style="text-align:left;caption-side:top">Kelompok <?= $gi + 1 ?></caption><thead><tr><th>Nama</th><th>Username</th><th>Telepon</th><th>Email</th><th>Status</th><th>KK</th><th>Dibuat</th><th>Kecocokan</th><th>Aksi</th></tr></thead><tbody>
   <?php foreach ($group as $d): ?>
   <tr>
    <td class="lead"><strong><?= html_escape($d['name']) ?></strong></td>
    <td data-label="Username"><?= html_escape($d['username']) ?></td>
    <td data-label="Telepon"><?= html_escape($d['phone'] ?: '-') ?></td>
    <td data-label="Email"><?= html_escape($d['email'] ?: '-') ?></td>
    <td data-label="Status"><span class="pg-pill <?= $d['active'] ? 'ok' : 'bad' ?>"><?= $d['active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
    <td data-label="KK"><span class="pg-pill <?= $d['has_kk'] ? 'ok' : 'muted' ?>"><?= $d['has_kk'] ? 'Tertaut KK' : 'Belum' ?></span></td>
    <td data-label="Dibuat"><?= $fmt_date($d['created_at']) ?></td>
    <td data-label="Kecocokan"><?= html_escape(implode(', ', array_map(function ($r) use ($reason_label) {return $reason_label[$r] ?? $r;}, $d['reasons']))) ?></td>
    <td class="acts"><a class="pg-edit" href="<?= site_url('admin/citizen/' . (int) $d['id']) ?>"><?= official_svg('pencil') ?>Periksa</a></td>
   </tr>
   <?php endforeach; ?>
  </tbody></table>
  <?php endforeach; ?>
 </section>
 <?php endif; ?>

 <form class="pg-filter" method="get" action="<?= site_url('admin/citizens') ?>" role="search">
  <div class="pg-filter-row">
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari warga</span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari nama warga atau username..." maxlength="100"></label>
   <label class="pg-sel"><?= official_svg('filter') ?><span class="sr-only">Status</span><select name="status" onchange="this.form.submit()"><option value=""<?= $status === '' ? ' selected' : '' ?>>Semua Status</option><option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Aktif</option><option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Nonaktif</option></select></label>
   <?php if ($has_kk): ?><label class="pg-sel"><?= official_svg('filter') ?><span class="sr-only">Status KK</span><select name="kk" onchange="this.form.submit()"><option value=""<?= $kk === '' ? ' selected' : '' ?>>Semua Status KK</option><option value="belum"<?= $kk === 'belum' ? ' selected' : '' ?>>Belum verifikasi KK</option><option value="terhubung"<?= $kk === 'terhubung' ? ' selected' : '' ?>>Tertaut KK</option></select></label><?php endif; ?>
   <label class="pg-sel"><?= official_svg('sort') ?><span class="sr-only">Urutan</span><select name="sort" onchange="this.form.submit()"><option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>>Terbaru</option><option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>>Terlama</option><option value="name"<?= $sort === 'name' ? ' selected' : '' ?>>Nama A–Z</option></select></label>
   <button class="pg-btn outline" type="submit"><?= official_svg('filter') ?>Filter</button>
   <a class="pg-btn outline" href="<?= $list_url(array('tampil' => 'duplikat', 'hal' => NULL)) ?>"><?= official_svg('people') ?>Deteksi Akun Ganda</a>
   <a class="pg-btn solid" href="<?= site_url('admin/citizen') ?>"><?= official_svg('plus') ?>Tambah Akun</a>
  </div>
 </form>

 <section class="pg-card">
  <table class="pg-table"><thead><tr><th class="no">No</th><th>Nama Warga</th><th>Username</th><th>Status</th><?php if ($has_kk): ?><th>Status KK</th><?php endif; ?><th>Tanggal Daftar</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $i => $item): $t = strtotime($item['created_at']); $cid = (int) $item['id']; $fid = $family_of[$cid] ?? 0; ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td class="lead"><span class="pg-person"><span class="pg-av<?= ($item['gender'] ?? '') === 'male' ? ' male' : '' ?>" aria-hidden="true"><?= html_escape(mb_strtoupper(mb_substr($item['display_name'], 0, 1))) ?></span><span><strong><?= html_escape($item['display_name']) ?></strong></span></span></td>
    <td data-label="Username"><?= html_escape($item['username']) ?></td>
    <td data-label="Status"><span class="pg-pill <?= $item['is_active'] ? 'ok' : 'bad' ?>"><?= $item['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
    <?php if ($has_kk): ?><td data-label="Status KK"><?php if ($fid): ?><span class="pg-pill ok" title="<?= isset($member_of[$cid]) ? 'Akun tertaut ke anggota penduduk' : 'Akun pemilik KK; anggota penduduk belum ditautkan' ?>">Tertaut KK<?= isset($member_of[$cid]) ? '' : ' · anggota belum dipilih' ?></span><?php elseif (isset($pending_of[$cid])): ?><span class="pg-pill info">Menunggu verifikasi KK</span><?php else: ?><span class="pg-pill warn">Belum verifikasi KK</span><?php endif; ?></td><?php endif; ?>
    <td data-label="Tanggal Daftar"><span class="pg-date"><?= $t ? date('j', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '-' ?><small><?= $t ? date('H:i', $t) : '' ?></small></span></td>
    <td class="acts"><div class="pg-acts">
     <a class="pg-edit" href="<?= site_url('admin/citizen/' . $cid) ?>"><?= official_svg('pencil') ?>Edit</a>
     <details class="pg-menu"><summary aria-label="Menu lain untuk <?= html_escape($item['display_name']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/citizen/' . $cid) ?>"><?= official_svg('pencil') ?>Ubah data &amp; password</a>
      <?php if ($fid && $can_family): ?><a href="<?= site_url('admin/penduduk/keluarga/' . $fid) ?>"><?= official_svg('people') ?>Lihat data KK</a><?php endif; ?>
      <a href="<?= site_url('admin/citizen') ?>"><?= official_svg('plus') ?>Tambah akun baru</a>
     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if (!$items): ?><tr><td colspan="<?= $has_kk ? 7 : 6 ?>" class="pg-empty">Tidak ada akun warga yang cocok dengan pencarian atau filter.</td></tr><?php endif; ?>
  </tbody></table>
 </section>

 <div class="pg-foot"><p>Menampilkan <b><?= count($items) ?></b> dari <b><?= (int) $matched ?></b> akun<?= $matched !== (int) $counts['total'] ? ' (total ' . (int) $counts['total'] . ')' : '' ?></p><?= pg_pager($page, $pages, function ($n) use ($list_url) {return $list_url(array('hal' => $n));}) ?></div>
</div>
