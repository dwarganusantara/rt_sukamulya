<?php
$bars = function ($rows, $tone = '') {
    $max = max(1, max($rows ?: array(0)));
    $total = array_sum($rows);
    $html = '<ul class="ops-bars ' . ops_e($tone) . '">';
    foreach ($rows as $label => $n) {
        $pct = $total ? round($n * 100 / $total) : 0;
        $html .= '<li><span>' . ops_e($label) . '</span><div class="ops-bar" aria-hidden="true"><i style="width:' . round($n * 100 / $max, 1) . '%"></i></div><b>' . (int) $n . '<small> · ' . $pct . '%</small></b></li>';
    }
    return $html . '</ul>';
};
$months_id = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$used_types = array();
foreach ($months as $row) foreach ($row as $t => $n) $used_types[$t] = TRUE;
$dom_rows = array();
foreach ($domicile as $k => $n) $dom_rows[$domicile_labels[$k] ?? $k] = $n;
?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/penduduk') ?>">Data Penduduk</a><span>›</span><strong>Rekap Statistik</strong></nav>
<?= ops_hero('Kependudukan', 'Rekap Penduduk', 'Statistik agregat warga aktif dari KK terverifikasi, per ' . ops_date(date('Y-m-d')) . '. Tidak memuat identitas perorangan.', array(array('admin/penduduk', '← Data Penduduk', 'ghost')), 'chart') ?>
<?= ops_stats(array(
    array('KK Terdaftar', (int) $stats['families'], 'Keluarga terverifikasi', 'blue'),
    array('Warga Aktif', (int) $stats['aktif'], 'Laki-laki ' . (int) $stats['L'] . ' · Perempuan ' . (int) $stats['P'], 'green'),
    array('Lansia (60+)', (int) $stats['lansia'], 'Warga aktif', 'amber'),
    array('Pindah / Meninggal', (int) $stats['pindah'] . ' / ' . (int) $stats['meninggal'], 'Histori, tidak dihitung aktif', 'purple'),
)) ?>

<div class="ops-grid even">
 <section class="ops-card"><header><h2>Kelompok usia &amp; jenis kelamin</h2></header>
  <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Kelompok</th><th class="num">L</th><th class="num">P</th><th class="num">Jumlah</th></tr></thead><tbody>
  <?php $tl = $tp = $tq = 0; foreach ($age as $k => $g): $tl += $g['L']; $tp += $g['P']; $tq += $g['?']; if ($k === 'tidak_diketahui' && !array_sum($g)) continue; ?>
   <tr><td><?= ops_e($age_groups[$k]) ?></td><td class="num"><?= (int) $g['L'] ?></td><td class="num"><?= (int) $g['P'] ?></td><td class="num"><strong><?= (int) array_sum($g) ?></strong></td></tr>
  <?php endforeach; ?>
   <tr><td><strong>Total</strong></td><td class="num"><strong><?= $tl ?></strong></td><td class="num"><strong><?= $tp ?></strong></td><td class="num"><strong><?= $tl + $tp + $tq ?></strong></td></tr>
  </tbody></table></div>
  <?php if ($tq): ?><p class="ops-muted"><?= (int) $tq ?> warga belum memiliki data jenis kelamin.</p><?php endif; ?>
 </section>
 <section class="ops-card"><header><h2>Status domisili</h2><a href="<?= site_url('admin/penduduk?domisili=belum') ?>">Lihat yang belum diisi</a></header>
  <?= $bars($dom_rows) ?>
 </section>
 <section class="ops-card"><header><h2>Pekerjaan</h2></header><?= $jobs ? $bars($jobs, 'blue') : '<p class="ops-muted">Belum ada data.</p>' ?></section>
 <section class="ops-card"><header><h2>Pendidikan terakhir</h2></header><?= $education ? $bars($education, 'purple') : '<p class="ops-muted">Belum ada data.</p>' ?>
  <p class="ops-muted">Ditampilkan sesuai isian KK; ejaan yang berbeda dihitung terpisah.</p></section>
</div>

<section class="ops-card"><header><div><h2>Mutasi diterapkan — 12 bulan terakhir</h2><p>Dihitung dari tanggal mutasi disetujui &amp; diterapkan ke data KK.</p></div></header>
 <?php if (!$used_types): ?><p class="ops-muted">Belum ada mutasi yang diterapkan dalam 12 bulan terakhir.</p><?php else: ?>
 <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>Bulan</th><?php foreach ($used_types as $t => $_): ?><th class="num"><?= ops_e($mut_types[$t] ?? $t) ?></th><?php endforeach; ?><th class="num">Total</th></tr></thead><tbody>
 <?php foreach ($months as $ym => $row): if (!$row) continue; ?>
  <tr><td><?= $months_id[(int) substr($ym, 5, 2)] . ' ' . substr($ym, 0, 4) ?></td><?php foreach ($used_types as $t => $_): ?><td class="num"><?= (int) ($row[$t] ?? 0) ?></td><?php endforeach; ?><td class="num"><strong><?= (int) array_sum($row) ?></strong></td></tr>
 <?php endforeach; ?>
 </tbody></table></div>
 <?php endif; ?>
</section>
</div>
