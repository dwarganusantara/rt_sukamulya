<?php $tabs = array('admin/inventaris' => 'Daftar Barang', 'admin/inventaris/pinjam' => 'Peminjaman', 'admin/belanja' => 'Belanja & Penerimaan'); ?>
<div class="ops">
<div class="ops-toolbar"><?= ops_tabs($tabs, 'admin/belanja') ?><?php if($can('belanja.write')): ?><div class="ops-toolbar-actions"><a class="ops-btn" href="<?= site_url('admin/belanja/baru') ?>">+ Permintaan Baru</a></div><?php endif; ?></div>
<?= ops_hero('Operasional RT', 'Belanja & Penerimaan Barang', 'Permintaan → persetujuan (bukan oleh pemohon) → pemesanan → penerimaan → posting pengeluaran kas. Barang inventaris masuk stok hanya setelah diterima.', array(), 'briefcase') ?>
<?= ops_flash() ?>
<?= ops_stats(array(
    array('Menunggu Persetujuan', (int) ($counts['submitted'] ?? 0), 'Perlu keputusan', 'orange', 'admin/belanja?status=submitted'),
    array('Dalam Proses', (int) ($counts['approved'] ?? 0) + (int) ($counts['ordered'] ?? 0) + (int) ($counts['partially_received'] ?? 0), 'Disetujui / dipesan', 'blue'),
    array('Diterima', (int) ($counts['received'] ?? 0), 'Siap ditutup', 'green', 'admin/belanja?status=received'),
    array('Belanja Bulan Ini', ops_money($spent), 'Sudah diposting ke kas', 'purple'),
)) ?>
<form class="ops-filter" data-ops-filter="ops-purchase-table" method="get" action="<?= site_url('admin/belanja') ?>"><input type="search" data-ops-search placeholder="Cari dalam daftar: kode, permintaan, pemohon..." aria-label="Cari dalam daftar belanja"><select name="status" aria-label="Status"><?= ops_options($labels, $status, 'Semua Status') ?></select><button class="ops-btn" type="submit">Filter</button></form>
<section class="ops-card"><div class="ops-table-wrap"><table class="ops-table stack" id="ops-purchase-table"><thead><tr><th>Kode</th><th>Permintaan</th><th>Pemohon</th><th class="num">Estimasi</th><th class="num">Realisasi</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($items as $it): ?>
 <tr><td data-label="Kode"><strong>BLJ-<?= str_pad((int) $it['id'], 5, '0', STR_PAD_LEFT) ?></strong><small><?= ops_date($it['created_at']) ?></small></td>
  <td data-label="Permintaan"><?= ops_e($it['title']) ?><small><?= ops_e($it['program_title'] ?: '') ?><?= $it['is_donation'] ? ' · Donasi' : '' ?></small></td><td data-label="Pemohon"><?= ops_e($it['requester'] ?: '-') ?></td>
  <td data-label="Estimasi" class="num"><?= ops_money($it['estimated_total']) ?></td><td data-label="Realisasi" class="num"><?= $it['actual_total'] !== NULL ? ops_money($it['actual_total']) : '—' ?></td>
  <td data-label="Status"><?= ops_badge($it['status'], $labels, $tones) ?></td><td data-label=""><a href="<?= site_url('admin/belanja/' . (int) $it['id']) ?>">Buka</a></td></tr>
<?php endforeach; if (!$items): ?><tr><td colspan="7" class="ops-empty"><?= ops_empty_state('Belum ada permintaan belanja','Tambahkan permintaan belanja untuk mulai mengelola pengeluaran barang.','cart',$can('belanja.write')?'admin/belanja/baru':NULL,'Permintaan Baru') ?></td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
