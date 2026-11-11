<?php
$this->load->helper(array('official_icons', 'page_ui'));
$bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$day = function ($d) use ($short) {$t = strtotime((string) $d); return $t ? date('d', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '—';};
$url = function ($extra = array()) use ($status, $type, $q, $period) {
    $p = array_filter(array_merge(array('status' => $status === 'open' ? '' : $status, 'jenis' => $type, 'q' => $q, 'periode' => $period), $extra), function ($v) {return $v !== '' && $v !== NULL;});
    return site_url('admin/mutasi') . ($p ? '?' . http_build_query($p) : '');
};
$tabs = array('' => array('Semua Mutasi', 'lines'), 'masuk' => array('Warga Masuk', 'person'), 'pindah' => array('Pindah Keluar', 'swap'), 'meninggal' => array('Meninggal', 'heart'), 'perubahan' => array('Perubahan Data', 'pencil'));
$type_ui = array('masuk' => array('Warga Masuk', 'person', 'ok'), 'pindah' => array('Pindah Keluar', 'swap', 'bad'), 'meninggal' => array('Meninggal', 'heart', 'purple'), 'ubah_data' => array('Perubahan Data', 'pencil', 'info'), 'ubah_alamat' => array('Perubahan Alamat', 'home', 'info'));
$tone_map = array('ok' => 'ok', 'bad' => 'bad', 'pending' => 'warn', 'info' => 'info', 'purple' => 'purple', 'muted' => 'muted');
$can_review = $can('mutasi.review');
?>
<div class="pg">
 <?= pg_crumb(array(array('Kependudukan', NULL), array('Mutasi Warga', NULL))) ?>
 <section class="pg-hero"><span class="pg-hero-ic"><?= official_svg('swap') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Kependudukan</span><h1>Mutasi Warga</h1><p>Pengajuan masuk, pindah, meninggal, koreksi data anggota, dan perubahan alamat. Data KK master diperbarui hanya setelah disetujui.</p></div></section>
 <?= ops_flash() ?>

 <section class="pg-stats" aria-label="Ringkasan mutasi">
  <a class="pg-stat" href="<?= site_url('admin/mutasi?status=submitted') ?>"><span class="pg-stat-ic"><?= official_svg('doc') ?></span><span class="pg-stat-tx"><small>Diajukan</small><strong><?= (int) ($counts['submitted'] ?? 0) ?></strong><em>Menunggu diproses</em></span><span class="pg-stat-mark"><?= official_svg('doc') ?></span></a>
  <a class="pg-stat blue" href="<?= site_url('admin/mutasi?status=in_review') ?>"><span class="pg-stat-ic"><?= official_svg('gear') ?></span><span class="pg-stat-tx"><small>Diperiksa</small><strong><?= (int) ($counts['in_review'] ?? 0) ?></strong><em>Dalam pemeriksaan</em></span><span class="pg-stat-mark"><?= official_svg('search') ?></span></a>
  <a class="pg-stat purple" href="<?= site_url('admin/mutasi?status=revision_requested') ?>"><span class="pg-stat-ic"><?= official_svg('wrench') ?></span><span class="pg-stat-tx"><small>Perlu Perbaikan</small><strong><?= (int) ($counts['revision_requested'] ?? 0) ?></strong><em>Dikembalikan ke warga</em></span><span class="pg-stat-mark"><?= official_svg('wrench') ?></span></a>
  <a class="pg-stat green" href="<?= site_url('admin/mutasi?status=approved') ?>"><span class="pg-stat-ic"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><small>Disetujui</small><strong><?= (int) ($counts['approved'] ?? 0) ?></strong><em>Sudah diterapkan</em></span><span class="pg-stat-mark"><?= official_svg('check-circle') ?></span></a>
 </section>

 <div class="pg-bar">
 <nav class="pg-tabs pg-tabs-solid" aria-label="Jenis mutasi">
  <?php foreach ($tabs as $key => $t): ?><a class="pg-tab" href="<?= ops_e($url(array('jenis' => $key, 'hal' => ''))) ?>"<?= $type === $key ? ' aria-current="page"' : '' ?>><?= official_svg($t[1]) ?><?= ops_e($t[0]) ?></a><?php endforeach; ?>
 </nav>
 <?php if ($can_review): ?><div class="pg-actions"><a class="pg-btn solid" href="<?= site_url('admin/mutasi/baru') ?>"><?= official_svg('plus') ?>Catat Mutasi</a></div><?php endif; ?>
 </div>

 <form class="pg-filter" method="get" action="<?= site_url('admin/mutasi') ?>" role="search">
  <div class="pg-filter-row">
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari mutasi</span><input type="search" name="q" value="<?= ops_e($q) ?>" maxlength="80" placeholder="Cari nama pemohon, nama anggota, atau alasan..."></label>
   <label class="pg-sel"><?= official_svg('filter') ?><span class="sr-only">Status</span><select name="status"><?= ops_options(array('open' => 'Perlu Tindakan', 'all' => 'Semua Status') + $labels, $status) ?></select></label>
   <label class="pg-sel"><?= official_svg('list') ?><span class="sr-only">Jenis</span><select name="jenis"><?= ops_options(array('perubahan' => 'Perubahan Data (semua)') + $types, $type, 'Semua Jenis') ?></select></label>
   <label class="pg-sel"><?= official_svg('calendar') ?><span class="sr-only">Periode pengajuan</span><select name="periode"><option value="">Periode Semua</option><?php foreach ($periods as $p): $pt = strtotime($p . '-01'); ?><option value="<?= ops_e($p) ?>"<?= $p === $period ? ' selected' : '' ?>><?= $bulan[(int) date('n', $pt)] . ' ' . date('Y', $pt) ?></option><?php endforeach; ?></select></label>
   <button class="pg-btn solid" type="submit"><?= official_svg('filter') ?>Filter</button>
   <a class="pg-btn ghost" href="<?= site_url('admin/mutasi') ?>"><?= official_svg('sort') ?>Reset</a>
  </div>
 </form>

 <section class="pg-card">
  <table class="pg-table"><thead><tr><th>Kode</th><th>Jenis Mutasi</th><th>Anggota / Keluarga</th><th>Pemohon</th><th>Tanggal Pengajuan</th><th>Efektif Mulai</th><th>Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $it):
   $fam = json_decode((string) $it['family_json'], TRUE) ?: array(); $prop = json_decode($it['proposed_values_json'], TRUE) ?: array();
   $ui = $type_ui[$it['mutation_type']] ?? array($types[$it['mutation_type']] ?? $it['mutation_type'], 'doc', 'muted');
   $fid = (int) $it['family_id']; $member = $members[(int) $it['member_id']] ?? ($prop['nama_lengkap'] ?? '');
   $head = $family_head[$fid] ?? ''; $open = in_array($it['status'], array('submitted', 'in_review'), TRUE);
   $title = $member !== '' ? $member : ($head !== '' ? 'Keluarga ' . $head : 'Data keluarga');
   $sub = $member !== '' ? ($head !== '' && $head !== $member ? 'Keluarga ' . $head : 'Anggota keluarga') : (isset($family_size[$fid]) ? $family_size[$fid] . ' anggota keluarga' : 'KK ' . rt_mask_id($it['nomor_kk'] ?? ''));
   $t = strtotime((string) $it['created_at']); ?>
   <tr>
    <td class="lead"><strong class="pg-mono">MUT-<?= str_pad((int) $it['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
    <td data-label="Jenis"><span class="pg-type <?= $ui[2] ?>"><?= official_svg($ui[1]) ?><?= ops_e($ui[0]) ?></span></td>
    <td data-label="Anggota / Keluarga"><span class="pg-stack"><strong><?= ops_e($title) ?></strong><small><?= ops_e($sub) ?></small></span></td>
    <td data-label="Pemohon"><?= ops_e($it['display_name'] ?: 'Dicatat pengurus') ?></td>
    <td data-label="Tanggal Pengajuan"><span class="pg-date"><?= $day($it['created_at']) ?><small><?= $t ? date('H:i', $t) : '' ?></small></span></td>
    <td data-label="Efektif Mulai"><?= $it['effective_date'] ? $day($it['effective_date']) : '—' ?></td>
    <td data-label="Status"><span class="pg-pill <?= $tone_map[$tones[$it['status']] ?? 'muted'] ?? 'muted' ?>"><?= ops_e($labels[$it['status']] ?? $it['status']) ?></span></td>
    <td class="acts"><div class="pg-acts">
     <?php if ($can_review && $open): ?><a class="pg-edit icon" href="<?= site_url('admin/mutasi/' . (int) $it['id']) ?>" title="Periksa &amp; putuskan" aria-label="Periksa mutasi MUT-<?= (int) $it['id'] ?>"><?= official_svg('pencil') ?></a><?php endif; ?>
     <a class="pg-view" href="<?= site_url('admin/mutasi/' . (int) $it['id']) ?>" title="Lihat detail" aria-label="Lihat detail mutasi MUT-<?= (int) $it['id'] ?>"><?= official_svg('eye') ?></a>
     <details class="pg-menu"><summary aria-label="Menu lain"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/mutasi/' . (int) $it['id']) ?>"><?= official_svg('eye') ?>Lihat detail &amp; riwayat</a>
      <?php if ($fid && $can('penduduk.read')): ?><a href="<?= site_url('admin/penduduk/keluarga/' . $fid) ?>"><?= official_svg('people') ?>Lihat data keluarga</a><?php endif; ?>
      <?php if ($fid && $can_review): ?><a href="<?= site_url('admin/mutasi/baru?keluarga=' . $fid) ?>"><?= official_svg('plus') ?>Catat mutasi keluarga ini</a><?php endif; ?>
     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if (!$items): ?><tr><td colspan="8" class="pg-empty">Tidak ada pengajuan mutasi untuk filter ini.</td></tr><?php endif; ?>
  </tbody></table>
  <div class="pg-foot pg-foot-in"><p>Menampilkan <b><?= count($items) ?></b> dari <b><?= (int) $matched ?></b> data mutasi</p><?= pg_pager($page, $pages, function ($n) use ($url) {return $url(array('hal' => $n));}) ?></div>
 </section>
</div>
