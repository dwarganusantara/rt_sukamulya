<?php $tabs = array('admin/inventaris' => 'Daftar Barang', 'admin/inventaris/pinjam' => 'Peminjaman', 'admin/belanja' => 'Belanja & Penerimaan'); ?>
<div class="ops">
<div class="ops-toolbar"><?= ops_tabs($tabs, 'admin/inventaris') ?><?php if($can('aset.write')): ?><div class="ops-toolbar-actions"><a class="ops-btn" href="<?= site_url('admin/inventaris/baru') ?>">+ Tambah Barang</a><a class="ops-btn outline" href="<?= site_url('admin/inventaris/pinjam') ?>">Catat Peminjaman</a></div><?php endif; ?></div>
<?= ops_hero('Operasional RT', 'Aset & Inventaris', 'Kelola aset, barang inventaris, kondisi, lokasi, dan peminjamannya dengan mudah.', array(), 'grid') ?>
<?= ops_flash() ?>
<?= ops_stats(array(
    array('Jenis Barang', (int) $stats['total'], 'Aktif terdaftar', 'blue'),
    array('Sedang Dipinjam', (int) $stats['out_qty'], 'Unit aset di luar', 'purple', 'admin/inventaris/pinjam'),
    array('Kondisi Rusak', (int) $stats['damaged'], 'Perlu perbaikan', 'red'),
    array('Usulan Hapus', (int) $stats['proposed'], 'Menunggu persetujuan', 'orange'),
)) ?>
<form class="ops-filter" method="get" action="<?= site_url('admin/inventaris') ?>">
 <input type="search" name="q" value="<?= ops_e($q) ?>" placeholder="Cari kode, nama, atau lokasi..." aria-label="Cari barang">
 <select name="jenis" aria-label="Jenis"><?= ops_options($kinds, $kind, 'Semua Jenis') ?></select>
 <button class="ops-btn" type="submit">Filter</button>
</form>
<section class="ops-card"><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th class="num">Total</th><th class="num">Tersedia</th><th>Kondisi</th><th></th></tr></thead><tbody>
<?php foreach ($items as $it): ?>
 <tr><td data-label="Kode"><strong><?= ops_e($it['code']) ?></strong></td><td data-label="Nama"><?= ops_e($it['name']) ?><small><?= ops_e($categories[$it['category']] ?? $it['category']) ?> · <?= ops_e($kinds[$it['kind']] ?? '') ?></small></td>
  <td data-label="Lokasi"><?= ops_e($it['location'] ?: '—') ?></td><td data-label="Total" class="num"><?= (int) $it['quantity'] ?> <?= ops_e($it['unit']) ?></td><td data-label="Tersedia" class="num"><?= (int) $it['qty_available'] ?></td>
  <td data-label="Kondisi"><?= ops_badge($it['condition_status'], $conditions, array('baik' => 'ok', 'rusak_ringan' => 'pending', 'rusak_berat' => 'bad', 'hilang' => 'bad')) ?><?= $it['status'] === 'diusulkan_hapus' ? ' <span class="ops-badge pending">Usul hapus</span>' : '' ?></td>
  <td data-label=""><a href="<?= site_url('admin/inventaris/' . (int) $it['id']) ?>">Detail</a></td></tr>
<?php endforeach; if (!$items): ?><tr><td colspan="7" class="ops-empty"><?= ops_empty_state('Belum ada barang inventaris','Tambahkan data barang untuk mulai mengelola inventaris.','box',$can('aset.write')?'admin/inventaris/baru':NULL,'Tambah Barang') ?></td></tr><?php endif; ?>
</tbody></table></div></section>
<?php if ($loans): ?>
<section class="ops-card"><header><h2>Peminjaman aktif</h2><a class="ops-btn ghost small" href="<?= site_url('admin/inventaris/pinjam') ?>">Kelola</a></header>
 <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Barang</th><th>Peminjam</th><th class="num">Jumlah</th><th>Kembali</th><th>Status</th></tr></thead><tbody>
 <?php foreach ($loans as $l): ?><tr><td data-label="Barang"><?= ops_e($l['asset_name']) ?></td><td data-label="Peminjam"><?= ops_e($l['borrower_name']) ?></td><td data-label="Jumlah" class="num"><?= (int) $l['qty'] ?> <?= ops_e($l['unit']) ?></td><td data-label="Kembali"><?= ops_date($l['due_date']) ?><?= $l['status'] === 'borrowed' && $l['due_date'] < date('Y-m-d') ? ' <span class="ops-badge bad">Terlambat</span>' : '' ?></td><td data-label="Status"><?= ops_badge($l['status'], $loan_labels, $loan_tones) ?></td></tr><?php endforeach; ?>
 </tbody></table></div>
</section>
<?php endif; ?>
</div>
