<?php
$this->load->helper(array('official_icons', 'page_ui'));
$list_url = function ($extra = array()) use ($q, $status, $sort) {
    $p = array_filter(array_merge(array('q' => $q, 'status' => $status, 'sort' => $sort === 'newest' ? '' : $sort), $extra), function ($v) {return $v !== '' && $v !== NULL && $v !== 1;});
    return site_url('admin/citizens') . ($p ? '?' . http_build_query($p) : '');
};
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$offset = ($page - 1) * $per_page;
?>
<div class="pg">
 <?= pg_crumb(array(array('Dashboard', 'admin'), array('Akun Warga', NULL))) ?>
 <section class="pg-hero"><span class="pg-hero-ic"><?= official_svg('people') ?></span><div class="pg-hero-tx"><h1>Akun Warga</h1><p>Buat akun untuk layanan warga dan lihat status aksesnya.</p></div></section>

 <section class="pg-stats" aria-label="Ringkasan akun">
  <a class="pg-stat" href="<?= site_url('admin/citizens') ?>"><span class="pg-stat-ic"><?= official_svg('people') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['total'] ?></strong><small>Total Akun Warga</small><em>Seluruh warga terdaftar</em></span><span class="pg-stat-mark"><?= official_svg('people') ?></span></a>
  <a class="pg-stat green" href="<?= site_url('admin/citizens?status=active') ?>"><span class="pg-stat-ic"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['active'] ?></strong><small>Akun Aktif</small><em>Dapat mengakses layanan</em></span><span class="pg-stat-mark"><?= official_svg('person') ?></span></a>
  <div class="pg-stat amber"><span class="pg-stat-ic"><?= official_svg('clock') ?></span><span class="pg-stat-tx"><strong>0</strong><small>Akun Menunggu</small><em>Akun dibuat langsung oleh pengurus</em></span><span class="pg-stat-mark"><?= official_svg('hourglass') ?></span></div>
  <a class="pg-stat red" href="<?= site_url('admin/citizens?status=inactive') ?>"><span class="pg-stat-ic"><?= official_svg('close') ?></span><span class="pg-stat-tx"><strong><?= (int) $counts['inactive'] ?></strong><small>Akun Nonaktif</small><em>Tidak dapat mengakses</em></span><span class="pg-stat-mark"><?= official_svg('person') ?></span></a>
 </section>

 <form class="pg-filter" method="get" action="<?= site_url('admin/citizens') ?>" role="search">
  <div class="pg-filter-row">
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari warga</span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari nama warga atau username..." maxlength="100"></label>
   <label class="pg-sel"><?= official_svg('filter') ?><span class="sr-only">Status</span><select name="status" onchange="this.form.submit()"><option value=""<?= $status === '' ? ' selected' : '' ?>>Semua Status</option><option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Aktif</option><option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Nonaktif</option></select></label>
   <label class="pg-sel"><?= official_svg('sort') ?><span class="sr-only">Urutan</span><select name="sort" onchange="this.form.submit()"><option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>>Terbaru</option><option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>>Terlama</option><option value="name"<?= $sort === 'name' ? ' selected' : '' ?>>Nama A–Z</option></select></label>
   <button class="pg-btn outline" type="submit"><?= official_svg('filter') ?>Filter</button>
   <a class="pg-btn solid" href="<?= site_url('admin/citizen') ?>"><?= official_svg('plus') ?>Tambah Warga</a>
  </div>
 </form>

 <section class="pg-card">
  <table class="pg-table"><thead><tr><th class="no">No</th><th>Nama Warga</th><th>Username</th><th>Status</th><th>Tanggal Daftar</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $i => $item): $t = strtotime($item['created_at']); $fid = $family_of[(int) $item['id']] ?? 0; ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td class="lead"><span class="pg-person"><span class="pg-av<?= ($item['gender'] ?? '') === 'male' ? ' male' : '' ?>" aria-hidden="true"><?= html_escape(mb_strtoupper(mb_substr($item['display_name'], 0, 1))) ?></span><span><strong><?= html_escape($item['display_name']) ?></strong></span></span></td>
    <td data-label="Username"><?= html_escape($item['username']) ?></td>
    <td data-label="Status"><span class="pg-pill <?= $item['is_active'] ? 'ok' : 'bad' ?>"><?= $item['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
    <td data-label="Tanggal Daftar"><span class="pg-date"><?= $t ? date('j', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '-' ?><small><?= $t ? date('H:i', $t) : '' ?></small></span></td>
    <td class="acts"><div class="pg-acts">
     <a class="pg-edit" href="<?= site_url('admin/citizen/' . (int) $item['id']) ?>"><?= official_svg('pencil') ?>Edit</a>
     <details class="pg-menu"><summary aria-label="Menu lain untuk <?= html_escape($item['display_name']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/citizen/' . (int) $item['id']) ?>"><?= official_svg('pencil') ?>Ubah data &amp; password</a>
      <?php if ($fid && $can_family): ?><a href="<?= site_url('admin/penduduk/keluarga/' . $fid) ?>"><?= official_svg('people') ?>Lihat data KK</a><?php endif; ?>
      <a href="<?= site_url('admin/citizen') ?>"><?= official_svg('plus') ?>Tambah warga baru</a>
     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if (!$items): ?><tr><td colspan="6" class="pg-empty">Tidak ada akun warga yang cocok dengan pencarian atau filter.</td></tr><?php endif; ?>
  </tbody></table>
 </section>

 <div class="pg-foot"><p>Menampilkan <b><?= count($items) ?></b> dari <b><?= (int) $matched ?></b> warga<?= $matched !== (int) $counts['total'] ? ' (total ' . (int) $counts['total'] . ')' : '' ?></p><?= pg_pager($page, $pages, function ($n) use ($list_url) {return $list_url(array('hal' => $n));}) ?></div>
</div>
