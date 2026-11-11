<?php $v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; }; $edit = !empty($item['id']); ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/inventaris') ?>">Inventaris</a><span>›</span><strong><?= $edit ? 'Ubah Barang' : 'Tambah Barang' ?></strong></nav>
<?= ops_hero('Operasional RT', $edit ? 'Ubah Data Barang' : 'Tambah Barang', $edit ? 'Jumlah stok diubah melalui "Penyesuaian stok" di halaman detail agar tercatat.' : 'Aset dapat dipinjamkan; barang habis pakai dikurangi melalui pencatatan pemakaian.', array(), 'grid') ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<section class="ops-card"><form method="post" class="ops-form"><?= ops_csrf() ?>
 <?= ops_field('code', 'Kode barang', 'text', $v('code'), array('required' => TRUE, 'maxlength' => 40, 'placeholder' => 'Contoh: TND-001')) ?>
 <?= ops_field('name', 'Nama barang', 'text', $v('name'), array('required' => TRUE, 'maxlength' => 160)) ?>
 <?php if (!$edit): ?><?= ops_field('kind', 'Jenis', 'select', $v('kind', 'aset'), array('options' => $kinds)) ?><?= ops_field('quantity', 'Jumlah awal', 'number', (int) $v('quantity', 1), array('min' => 0, 'required' => TRUE)) ?><?php endif; ?>
 <?= ops_field('category', 'Kategori', 'select', $v('category', 'peralatan'), array('options' => $categories)) ?>
 <?= ops_field('unit', 'Satuan', 'text', $v('unit', 'unit'), array('maxlength' => 30)) ?>
 <?= ops_field('condition_status', 'Kondisi', 'select', $v('condition_status', 'baik'), array('options' => $conditions)) ?>
 <?= ops_field('location', 'Lokasi penyimpanan', 'text', $v('location'), array('maxlength' => 160)) ?>
 <?= ops_field('source', 'Sumber perolehan', 'text', $v('source'), array('maxlength' => 120, 'placeholder' => 'Kas RT, donasi, hibah...')) ?>
 <?= ops_field('pic', 'Penanggung jawab', 'text', $v('pic'), array('maxlength' => 120)) ?>
 <?= ops_field('value', 'Nilai perolehan (Rp, opsional)', 'number', $v('value') === NULL ? '' : (int) $v('value'), array('min' => 0)) ?>
 <?= ops_field('note', 'Catatan', 'textarea', $v('note'), array('rows' => 2, 'wide' => TRUE, 'maxlength' => 500)) ?>
 <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url($edit ? 'admin/inventaris/' . (int) $item['id'] : 'admin/inventaris') ?>">Batal</a><button class="ops-btn" type="submit">Simpan</button></div>
</form></section>
</div>
