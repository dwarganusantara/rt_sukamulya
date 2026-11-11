<?php
$bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$fmt = function ($dt) use ($bulan) {$t = strtotime($dt); return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y, H:i', $t);};
$ago = function ($dt) {
    $s = max(0, time() - strtotime($dt));
    if ($s < 60) return 'baru saja';
    if ($s < 3600) return floor($s / 60) . ' menit yang lalu';
    if ($s < 86400) return floor($s / 3600) . ' jam yang lalu';
    if ($s < 2592000) return floor($s / 86400) . ' hari yang lalu';
    return floor($s / 2592000) . ' bulan yang lalu';
};
$cat_style = array('Tampilan' => array('laptop', 'blue'), 'Pengaturan' => array('gear', 'green'), 'Pemulihan' => array('swap', 'purple'), 'Surat' => array('mail', 'orange'), 'Langganan' => array('crown', 'orange'), 'Notifikasi' => array('bell', 'blue'), 'Dashboard' => array('grid', 'purple'));
$cat_link = array('Tampilan' => 'admin/page-images', 'Surat' => 'admin/letter_settings', 'Langganan' => 'admin/langganan', 'Dashboard' => 'admin/fitur');
$is_image = function ($v) {return is_string($v) && preg_match('#^(uploads|assets)/[\w./-]+\.(jpe?g|png|webp|gif|svg)$#i', $v);};
$show_val = function ($v) use ($is_image) {
    $v = (string) $v;
    if ($v === '') return '<span class="sh-empty">(kosong / bawaan)</span>';
    if ($is_image($v)) return '<span class="sh-img"><img src="' . base_url(ops_e($v)) . '" alt="" loading="lazy"><code>' . ops_e(basename($v)) . '</code></span>';
    return ops_e(mb_strimwidth($v, 0, 160, '…'));
};
$base = site_url('admin/pengaturan/riwayat');
$qs = function ($over = array()) use ($filters) {$p = array_filter(array_merge(array('q' => $filters['q'], 'period' => $filters['period'], 'per' => $filters['per'] !== 10 ? $filters['per'] : NULL, 'page' => $filters['page'] > 1 ? $filters['page'] : NULL), $over), function ($v) {return $v !== NULL && $v !== '';}); return $p ? '?' . http_build_query($p) : '';};
$from = $total ? ($filters['page'] - 1) * $filters['per'] + 1 : 0; $to = min($total, $filters['page'] * $filters['per']);
$top_style = $cat_style[$stats['top']] ?? array('tag', 'purple');
?>
<div class="ops ops-settings-history sh">
<?= ops_hero('Pengaturan Sistem', 'Riwayat Pengaturan', 'Lihat seluruh riwayat perubahan konfigurasi aplikasi, fitur, dan tampilan dari waktu ke waktu.', array(), 'clock') ?>
<?= ops_flash() ?>

<section class="sh-stats" aria-label="Ringkasan riwayat">
 <div class="sh-stat blue"><span class="sh-stat-ic"><?= official_svg('layers') ?></span><div><small>Total Perubahan</small><strong><?= (int) $stats['total'] ?></strong></div></div>
 <div class="sh-stat orange"><span class="sh-stat-ic"><?= official_svg('calendar') ?></span><div><small>Perubahan Hari Ini</small><strong><?= (int) $stats['today'] ?></strong></div></div>
 <div class="sh-stat green"><span class="sh-stat-ic"><?= official_svg('person') ?></span><div><small>Dilakukan Oleh</small><strong><?= (int) $stats['users'] ?> Pengguna</strong></div></div>
 <div class="sh-stat purple"><span class="sh-stat-ic"><?= official_svg($stats['top'] === '-' ? 'tag' : 'tag') ?></span><div><small>Fitur Terbanyak</small><strong><?= ops_e($stats['top']) ?></strong></div></div>
</section>

<form class="sh-filter" method="get" action="<?= $base ?>" role="search">
 <label class="sh-search"><?= official_svg('search') ?><input type="search" name="q" value="<?= ops_e($filters['q']) ?>" placeholder="Cari perubahan (fitur, file, atau detail...)" aria-label="Cari perubahan"></label>
 <label class="sh-period"><?= official_svg('calendar') ?><select name="period" aria-label="Periode perubahan">
  <option value="">Semua Periode</option>
  <?php foreach (array('today' => 'Hari ini', '7' => '7 hari terakhir', '30' => '30 hari terakhir') as $val => $lab): ?><option value="<?= $val ?>"<?= $filters['period'] === (string) $val ? ' selected' : '' ?>><?= $lab ?></option><?php endforeach; ?>
  <?php foreach ($months as $m): ?><option value="<?= ops_e($m) ?>"<?= $filters['period'] === $m ? ' selected' : '' ?>><?= ops_e($bulan[(int) substr($m, 5, 2)] . ' ' . substr($m, 0, 4)) ?></option><?php endforeach; ?>
 </select></label>
 <?php if ($filters['per'] !== 10): ?><input type="hidden" name="per" value="<?= (int) $filters['per'] ?>"><?php endif; ?>
 <a class="sh-btn ghost" href="<?= $base ?>"><?= official_svg('swap') ?>Reset</a>
 <button class="sh-btn" type="submit"><?= official_svg('filter') ?>Terapkan Filter</button>
</form>

<section class="ops-card sh-card">
 <header class="sh-card-head"><span aria-hidden="true"><?= official_svg('list') ?></span><div><h2>Daftar Perubahan</h2><p><?= ($filters['q'] !== '' || $filters['period'] !== '') ? 'Ditemukan ' . (int) $total . ' perubahan sesuai filter.' : 'Menampilkan ' . (int) $total . ' perubahan pengaturan yang tercatat.' ?></p></div></header>
 <div class="sh-table-wrap"><table class="sh-table"><thead><tr><th>Waktu</th><th>Oleh</th><th>Perubahan</th><th>Detail</th><th>Nilai Sebelumnya</th><th>Aksi</th></tr></thead><tbody>
 <?php foreach ($rows as $r): $st = $cat_style[$r['category']] ?? array('gear', 'green'); $n = count($r['changes']); $hid = 'sh-d-' . (int) $r['id']; ?>
  <tr>
   <td data-label="Waktu"><div class="sh-when"><?= official_svg('calendar') ?><div><strong><?= ops_e($fmt($r['created_at'])) ?></strong><small title="<?= ops_e($r['created_at']) ?>"><?= ops_e($ago($r['created_at'])) ?></small></div></div></td>
   <td data-label="Oleh"><span class="sh-who"><span aria-hidden="true"><?= official_svg('person') ?></span><?= ops_e($r['actor']) ?></span></td>
   <td data-label="Perubahan"><span class="sh-chip <?= $st[1] ?>"><?= official_svg($st[0]) ?><?= ops_e($r['category']) ?></span></td>
   <td data-label="Detail" class="sh-detail"><?= ops_e($r['note']) ?></td>
   <td data-label="Nilai Sebelumnya"><button type="button" class="sh-count" data-sh-open="<?= $hid ?>" aria-label="Lihat <?= $n ?> nilai sebelumnya perubahan #<?= (int) $r['id'] ?>"><?= $n ?> field</button></td>
   <td data-label="Aksi"><div class="sh-actions">
    <form method="post" action="<?= $base . $qs() ?>"><?= ops_csrf() ?><input type="hidden" name="history_id" value="<?= (int) $r['id'] ?>"><button class="sh-restore" type="submit"<?= $n ? '' : ' disabled' ?> data-sh-confirm="Kembalikan <?= $n ?> field ke nilai sebelum perubahan ini? Nilai saat ini akan dicatat sebagai riwayat baru."><?= official_svg('back') ?>Kembalikan</button></form>
    <details class="ops-row-menu sh-more"><summary aria-label="Aksi lain perubahan #<?= (int) $r['id'] ?>"><?= official_svg('more-v') ?></summary><nav>
     <button type="button" data-sh-open="<?= $hid ?>">Lihat detail nilai</button>
     <a href="<?= site_url($cat_link[$r['category']] ?? 'admin/settings') ?>">Buka pengaturan terkait</a>
    </nav></details>
   </div></td>
  </tr>
 <?php endforeach; ?>
 <?php if (!$rows): ?><tr><td colspan="6" class="sh-none"><?= official_svg('clock') ?><strong><?= ($filters['q'] !== '' || $filters['period'] !== '') ? 'Tidak ada perubahan yang sesuai filter.' : 'Belum ada perubahan pengaturan yang tercatat.' ?></strong><?php if ($filters['q'] !== '' || $filters['period'] !== ''): ?><a href="<?= $base ?>">Hapus filter</a><?php endif; ?></td></tr><?php endif; ?>
 </tbody></table></div>
 <footer class="sh-foot">
  <span>Menampilkan <?= $from ?>–<?= $to ?> dari <?= (int) $total ?> perubahan</span>
  <div class="sh-pager">
   <nav aria-label="Halaman riwayat">
    <?php if ($filters['page'] > 1): ?><a href="<?= $base . $qs(array('page' => $filters['page'] - 1)) ?>" aria-label="Halaman sebelumnya"><?= official_svg('back') ?></a><?php else: ?><span class="off" aria-hidden="true"><?= official_svg('back') ?></span><?php endif; ?>
    <?php for ($i = max(1, $filters['page'] - 2); $i <= min($pages, $filters['page'] + 2); $i++): ?><?php if ($i === $filters['page']): ?><span class="cur" aria-current="page"><?= $i ?></span><?php else: ?><a href="<?= $base . $qs(array('page' => $i)) ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?>
    <?php if ($filters['page'] < $pages): ?><a href="<?= $base . $qs(array('page' => $filters['page'] + 1)) ?>" aria-label="Halaman berikutnya"><?= official_svg('chev-right') ?></a><?php else: ?><span class="off" aria-hidden="true"><?= official_svg('chev-right') ?></span><?php endif; ?>
   </nav>
   <form method="get" action="<?= $base ?>"><?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= ops_e($filters['q']) ?>"><?php endif; ?><?php if ($filters['period'] !== ''): ?><input type="hidden" name="period" value="<?= ops_e($filters['period']) ?>"><?php endif; ?>
    <select name="per" aria-label="Jumlah per halaman" data-sh-autosubmit><?php foreach (array(10, 25, 50) as $pp): ?><option value="<?= $pp ?>"<?= $filters['per'] === $pp ? ' selected' : '' ?>><?= $pp ?> / halaman</option><?php endforeach; ?></select>
    <noscript><button type="submit">OK</button></noscript>
   </form>
  </div>
 </footer>
</section>

<?php foreach ($rows as $r): $hid = 'sh-d-' . (int) $r['id']; ?>
<dialog class="sh-dialog" id="<?= $hid ?>" aria-labelledby="<?= $hid ?>-t">
 <header><div><h2 id="<?= $hid ?>-t">Detail Perubahan #<?= (int) $r['id'] ?></h2><p><?= ops_e($fmt($r['created_at'])) ?> · <?= ops_e($r['actor']) ?> · <?= ops_e($r['category']) ?></p></div><button type="button" class="sh-x" data-sh-close aria-label="Tutup">&times;</button></header>
 <?php if ($r['changes']): ?>
 <div class="sh-table-wrap"><table class="sh-table sh-diff"><thead><tr><th>Field</th><th>Nilai sebelumnya</th><th>Nilai sekarang</th></tr></thead><tbody>
  <?php foreach ($r['changes'] as $k => $old): $now = $current[$k] ?? ''; ?><tr><td><code><?= ops_e($k) ?></code></td><td><?= $show_val($old) ?></td><td class="<?= (string) $now === (string) $old ? 'same' : '' ?>"><?= $show_val($now) ?><?= (string) $now === (string) $old ? ' <em class="sh-same">sama</em>' : '' ?></td></tr><?php endforeach; ?>
 </tbody></table></div>
 <?php else: ?><p class="sh-empty">Tidak ada nilai sebelumnya yang tersimpan.</p><?php endif; ?>
 <footer><button type="button" class="sh-btn ghost" data-sh-close>Tutup</button>
  <?php if ($r['changes']): ?><form method="post" action="<?= $base . $qs() ?>"><?= ops_csrf() ?><input type="hidden" name="history_id" value="<?= (int) $r['id'] ?>"><button class="sh-btn" type="submit" data-sh-confirm="Kembalikan <?= count($r['changes']) ?> field ke nilai sebelum perubahan ini?"><?= official_svg('back') ?>Kembalikan</button></form><?php endif; ?>
 </footer>
</dialog>
<?php endforeach; ?>
</div>
<link rel="stylesheet" href="<?= base_url('assets/settings-history.css') ?>?v=<?= filemtime(FCPATH . 'assets/settings-history.css') ?>">
<script src="<?= base_url('assets/settings-history.js') ?>?v=<?= filemtime(FCPATH . 'assets/settings-history.js') ?>" defer></script>
