<?php $tabs = array('admin/inventaris' => 'Daftar Barang', 'admin/inventaris/pinjam' => 'Peminjaman', 'admin/belanja' => 'Belanja & Penerimaan'); ?>
<div class="ops">
<?= ops_tabs($tabs, 'admin/inventaris/pinjam') ?>
<?= ops_hero('Operasional RT', 'Peminjaman Aset', 'Alur: diajukan → disetujui (stok dikunci) → diserahkan → dikembalikan dengan pemeriksaan kondisi.', array(), 'grid') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<div class="ops-grid">
 <section class="ops-card">
  <header><h2>Daftar peminjaman</h2><form method="get" class="ops-actions"><select name="status" aria-label="Status" onchange="this.form.submit()"><?= ops_options(array('active' => 'Aktif', 'all' => 'Semua') + $labels, $status) ?></select><noscript><button class="ops-btn small">Tampilkan</button></noscript></form></header>
  <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Barang</th><th>Peminjam</th><th class="num">Jml</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($loans as $l): $late = $l['status'] === 'borrowed' && $l['due_date'] < date('Y-m-d'); ?>
   <tr><td data-label="Barang"><a href="<?= site_url('admin/inventaris/' . (int) $l['asset_id']) ?>"><?= ops_e($l['asset_name']) ?></a><small><?= ops_e($l['code']) ?></small></td>
    <td data-label="Peminjam"><?= ops_e($l['borrower_name']) ?><small><?= ops_e($l['purpose']) ?></small></td><td data-label="Jumlah" class="num"><?= (int) $l['qty'] ?></td>
    <td data-label="Periode"><?= ops_date($l['loan_date']) ?><small>s.d. <?= ops_date($l['due_date']) ?><?= $late ? ' — terlambat' : '' ?></small></td>
    <td data-label="Status"><?= ops_badge($l['status'], $labels, $tones) ?><?= $l['status'] === 'returned' && ($l['qty_damaged'] || $l['qty_lost']) ? '<small>Rusak ' . (int) $l['qty_damaged'] . ' · Hilang ' . (int) $l['qty_lost'] . '</small>' : '' ?></td>
    <td data-label="Aksi"><?php if ($can('aset.write')): ?><div class="ops-actions">
     <?php if ($l['status'] === 'requested' && $can('aset.approve')): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>"><button class="ops-btn green small" name="act" value="approve">Setujui</button></form><form method="post"><?= ops_csrf() ?><input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>"><button class="ops-btn red outline small" name="act" value="reject">Tolak</button></form><?php endif; ?>
     <?php if ($l['status'] === 'approved'): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>"><button class="ops-btn small" name="act" value="handover">Serahkan</button></form><form method="post"><?= ops_csrf() ?><input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>"><button class="ops-btn ghost small" name="act" value="cancel">Batal</button></form><?php endif; ?>
     <?php if ($l['status'] === 'borrowed'): ?><details class="ops-more"><summary>Catat pengembalian</summary><form method="post" class="ops-form one" style="margin-top:8px"><?= ops_csrf() ?><input type="hidden" name="loan_id" value="<?= (int) $l['id'] ?>"><input type="hidden" name="act" value="return">
      <?= ops_field('qty_good', 'Kembali baik', 'number', (int) $l['qty'], array('min' => 0)) ?><?= ops_field('qty_damaged', 'Rusak', 'number', 0, array('min' => 0)) ?><?= ops_field('qty_lost', 'Hilang', 'number', 0, array('min' => 0)) ?><?= ops_field('note', 'Catatan inspeksi', 'text', '', array('maxlength' => 500)) ?>
      <button class="ops-btn small" type="submit">Simpan Pengembalian</button></form></details><?php endif; ?>
    </div><?php endif; ?></td></tr>
  <?php endforeach; if (!$loans): ?><tr><td colspan="6" class="ops-empty">Tidak ada peminjaman.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
 <?php if ($can('aset.write')): ?>
 <section class="ops-card"><h2>Permohonan pinjam baru</h2>
  <?php if (!$assets): ?><p class="ops-muted">Tidak ada aset yang tersedia untuk dipinjam.</p><?php else: ?>
  <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="create">
   <?= ops_field('asset_id', 'Aset', 'select', '', array('options' => $assets, 'placeholder_option' => 'Pilih aset', 'required' => TRUE)) ?>
   <?= ops_field('citizen_id', 'Warga peminjam (opsional)', 'select', '', array('options' => $citizens, 'placeholder_option' => 'Bukan akun warga')) ?>
   <?= ops_field('borrower_name', 'Nama peminjam', 'text', '', array('required' => TRUE, 'maxlength' => 140)) ?>
   <?= ops_field('qty', 'Jumlah', 'number', 1, array('min' => 1, 'required' => TRUE)) ?>
   <?= ops_field('loan_date', 'Tanggal pinjam', 'date', date('Y-m-d'), array('required' => TRUE)) ?>
   <?= ops_field('due_date', 'Tanggal kembali', 'date', date('Y-m-d', strtotime('+2 days')), array('required' => TRUE)) ?>
   <?= ops_field('purpose', 'Keperluan', 'text', '', array('maxlength' => 255)) ?>
   <div class="ops-form-actions"><button class="ops-btn" type="submit">Catat Permohonan</button></div>
  </form>
  <?php endif; ?>
 </section>
 <?php endif; ?>
</div>
</div>
