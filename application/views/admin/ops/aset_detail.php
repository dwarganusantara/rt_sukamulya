<?php $removed = $item['status'] === 'dihapus'; ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/inventaris') ?>">Inventaris</a><span>›</span><strong><?= ops_e($item['code']) ?></strong></nav>
<?= ops_hero($kinds[$item['kind']] ?? 'Barang', $item['name'], $item['code'] . ' · ' . ($categories[$item['category']] ?? '') . ($item['location'] ? ' · ' . $item['location'] : ''), !$removed && $can('aset.write') ? array(array('admin/inventaris/' . (int) $item['id'] . '/ubah', 'Ubah Data', 'ghost')) : array(), 'grid') ?>
<?= ops_flash() ?>
<?php if ($removed): ?><p class="ops-note red">Barang ini sudah dihapus dari daftar aktif. Riwayat tetap tersimpan.</p><?php elseif ($item['status'] === 'diusulkan_hapus'): ?><p class="ops-note">Barang diusulkan untuk dihapus dan menunggu persetujuan Ketua RT.</p><?php endif; ?>
<?= ops_stats(array(array('Total', (int) $item['quantity'] . ' ' . $item['unit'], 'Seluruh stok', 'blue'), array('Tersedia', (int) $item['qty_available'], 'Siap dipinjam/dipakai', 'green'),
    array('Kondisi', $conditions[$item['condition_status']] ?? '-', '', 'purple'), array('Nilai', $item['value'] !== NULL ? ops_money($item['value']) : '—', $item['source'] ?: '', 'orange'))) ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><h2>Riwayat stok</h2>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Waktu</th><th>Jenis</th><th class="num">Jumlah</th><th>Keterangan</th><th>Oleh</th></tr></thead><tbody>
   <?php foreach ($movements as $mv): ?><tr><td data-label="Waktu"><?= ops_date($mv['created_at'], TRUE) ?></td><td data-label="Jenis"><?= ops_e($movement_labels[$mv['movement']] ?? $mv['movement']) ?></td><td data-label="Jumlah" class="num"><?= (int) $mv['qty'] ?></td><td data-label="Keterangan"><?= ops_e($mv['note']) ?></td><td data-label="Oleh"><?= ops_e($mv['username'] ?: '—') ?></td></tr><?php endforeach; ?>
   <?php if (!$movements): ?><tr><td colspan="5" class="ops-empty">Belum ada pergerakan stok.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>
  <section class="ops-card"><h2>Riwayat peminjaman</h2>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Peminjam</th><th class="num">Jumlah</th><th>Periode</th><th>Status</th></tr></thead><tbody>
   <?php foreach ($loans as $l): ?><tr><td data-label="Peminjam"><?= ops_e($l['borrower_name']) ?></td><td data-label="Jumlah" class="num"><?= (int) $l['qty'] ?></td><td data-label="Periode"><?= ops_date($l['loan_date']) ?> – <?= ops_date($l['due_date']) ?></td><td data-label="Status"><?= ops_badge($l['status'], $loan_labels, $loan_tones) ?></td></tr><?php endforeach; ?>
   <?php if (!$loans): ?><tr><td colspan="4" class="ops-empty">Belum pernah dipinjam.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>
 </div>
 <div>
  <?php if (!$removed && $can('aset.write')): ?>
  <section class="ops-card"><h2>Penyesuaian stok</h2>
   <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="stock">
    <?php $mv_opts = array('in' => 'Tambah stok (masuk)', 'damaged' => 'Tandai rusak (keluar dari tersedia)', 'lost' => 'Hilang', 'adjust' => 'Koreksi (+/-)'); if ($item['kind'] === 'habis_pakai') $mv_opts = array('out' => 'Pemakaian (keluar)') + $mv_opts; ?>
    <?= ops_field('movement', 'Jenis', 'select', '', array('options' => $mv_opts)) ?>
    <?= ops_field('qty', 'Jumlah', 'number', 1, array('required' => TRUE, 'help' => 'Untuk koreksi, gunakan angka negatif bila mengurangi.')) ?>
    <?= ops_field('note', 'Alasan', 'text', '', array('required' => TRUE, 'maxlength' => 255)) ?>
    <div class="ops-form-actions"><button class="ops-btn" type="submit">Simpan</button></div>
   </form>
  </section>
  <?php if ($item['status'] === 'aktif'): ?>
  <section class="ops-card"><h2>Usulkan penghapusan</h2>
   <form method="post" class="ops-form one" onsubmit="return confirm('Usulkan penghapusan barang ini?')"><?= ops_csrf() ?><input type="hidden" name="act" value="propose_delete">
    <?= ops_field('note', 'Alasan (rusak total, hilang, dihibahkan...)', 'text', '', array('required' => TRUE, 'maxlength' => 255)) ?>
    <div class="ops-form-actions"><button class="ops-btn red outline" type="submit">Usulkan Hapus</button></div>
   </form>
  </section>
  <?php endif; endif; ?>
  <?php if ($item['status'] === 'diusulkan_hapus' && $can('aset.approve')): ?>
  <section class="ops-card"><h2>Keputusan penghapusan</h2>
   <div class="ops-actions">
    <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="approve_delete"><button class="ops-btn red" type="submit" onclick="return confirm('Setujui penghapusan?')">Setujui Hapus</button></form>
    <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="cancel_delete"><button class="ops-btn ghost" type="submit">Tolak Usulan</button></form>
   </div>
  </section>
  <?php endif; ?>
  <section class="ops-card"><h2>Informasi</h2><dl class="ops-dl"><dt>Penanggung jawab</dt><dd><?= ops_e($item['pic'] ?: '—') ?></dd><dt>Catatan</dt><dd><?= ops_e($item['note'] ?: '—') ?></dd><dt>Dicatat</dt><dd><?= ops_date($item['created_at'], TRUE) ?></dd></dl></section>
 </div>
</div>
</div>
