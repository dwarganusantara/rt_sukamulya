<?php $v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; }; $edit = !empty($item['id']); ?>
<div class="ops">
<?= ops_hero('Informasi', 'Kontak Penting', 'Direktori layanan lingkungan. Hanya kontak bertanda "Publik" dan aktif yang tampil di portal warga. Periksa ulang nomor darurat secara berkala.', array(), 'phone') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<div class="ops-grid">
 <section class="ops-card"><div class="ops-filter" data-ops-filter="ops-contacts-table"><input type="search" data-ops-search placeholder="Cari layanan atau nomor..." aria-label="Cari kontak"><select data-ops-category aria-label="Kategori kontak"><?= ops_options($categories,'','Semua Kategori') ?></select></div><div class="ops-table-wrap"><table class="ops-table stack" id="ops-contacts-table"><thead><tr><th>Layanan</th><th>Telepon</th><th>Visibilitas</th><th>Diperiksa</th><th>Aksi</th></tr></thead><tbody>
 <?php foreach ($items as $c): $stale = !$c['verified_at'] || $c['verified_at'] < date('Y-m-d', strtotime('-6 months')); ?>
  <tr data-category="<?= ops_e($c['category']) ?>"><td data-label="Layanan"><strong><?= ops_e($c['name']) ?></strong><?= $c['is_emergency'] ? ' <span class="ops-badge bad">Darurat</span>' : '' ?><small><?= ops_e($categories[$c['category']] ?? $c['category']) ?><?= $c['service_hours'] ? ' · ' . ops_e($c['service_hours']) : '' ?></small></td>
   <td data-label="Telepon"><a href="tel:<?= ops_e(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= ops_e($c['phone']) ?></a></td>
   <td data-label="Visibilitas"><?= $c['is_public'] ? ops_badge('pub', array('pub' => 'Publik'), array('pub' => 'info')) : ops_badge('int', array('int' => 'Internal pengurus'), array('int' => 'purple')) ?> <?= $c['is_active'] ? '' : ops_badge('off', array('off' => 'Nonaktif'), array('off' => 'muted')) ?></td>
   <td data-label="Diperiksa"><?= ops_date($c['verified_at']) ?><?= $stale ? '<small>Perlu dicek ulang</small>' : '' ?></td>
   <td data-label="Aksi"><?php if ($can('kontak.write')): ?><div class="ops-actions"><a class="ops-btn ghost small" href="<?= site_url('admin/kontak/' . (int) $c['id']) ?>">Ubah</a>
    <form method="post" action="<?= site_url('admin/kontak') ?>"><?= ops_csrf() ?><input type="hidden" name="contact_id" value="<?= (int) $c['id'] ?>"><button class="ops-btn ghost small" name="act" value="verify">Sudah dicek</button></form>
    <form method="post" action="<?= site_url('admin/kontak') ?>"><?= ops_csrf() ?><input type="hidden" name="contact_id" value="<?= (int) $c['id'] ?>"><button class="ops-btn ghost small" name="act" value="toggle"><?= $c['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></div><?php endif; ?></td></tr>
 <?php endforeach; if (!$items): ?><tr><td colspan="5" class="ops-empty">Belum ada kontak penting.</td></tr><?php endif; ?>
 </tbody></table></div><p class="ops-local-count" data-ops-count="ops-contacts-table" aria-live="polite"></p></section>
 <?php if ($can('kontak.write')): ?>
 <section class="ops-card"><header><h2><?= $edit ? 'Ubah kontak' : 'Tambah kontak' ?></h2><?php if ($edit): ?><a class="ops-btn ghost small" href="<?= site_url('admin/kontak') ?>">+ Baru</a><?php endif; ?></header>
  <form method="post" action="<?= site_url('admin/kontak' . ($edit ? '/' . (int) $item['id'] : '')) ?>" class="ops-form one"><?= ops_csrf() ?>
   <?= ops_field('name', 'Nama layanan', 'text', $v('name'), array('required' => TRUE, 'maxlength' => 140)) ?>
   <?= ops_field('category', 'Kategori', 'select', $v('category', 'umum'), array('options' => $categories)) ?>
   <?= ops_field('phone', 'Nomor telepon', 'tel', $v('phone'), array('required' => TRUE, 'maxlength' => 30)) ?>
   <?= ops_field('address', 'Alamat', 'text', $v('address'), array('maxlength' => 255)) ?>
   <?= ops_field('service_hours', 'Jam layanan', 'text', $v('service_hours'), array('maxlength' => 100, 'placeholder' => '24 jam / Senin–Jumat 08.00–16.00')) ?>
   <?= ops_field('note', 'Keterangan', 'text', $v('note'), array('maxlength' => 255)) ?>
   <?= ops_field('sort_order', 'Urutan', 'number', (int) $v('sort_order', 0)) ?>
   <?= ops_field('is_public', 'Tampilkan di portal warga (publik)', 'checkbox', $edit ? $v('is_public') : 1) ?>
   <?= ops_field('is_emergency', 'Kontak darurat', 'checkbox', $v('is_emergency')) ?>
   <?= ops_field('is_active', 'Aktif', 'checkbox', $edit ? $v('is_active') : 1) ?>
   <?= ops_field('verified_now', 'Nomor sudah saya periksa hari ini', 'checkbox', 0) ?>
   <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url('admin/kontak') ?>">Batal</a><button class="ops-btn" type="submit">Simpan Kontak</button></div>
  </form>
 </section>
 <?php endif; ?>
</div>
</div>
