<div class="ops">
<?= ops_hero('Informasi & Arsip', 'Dokumen & Arsip Digital', 'Berkas disimpan di penyimpanan privat (bukan folder publik), dengan checksum SHA-256, versi, dan izin akses. Unduhan selalu melalui pemeriksaan hak akses dan tercatat di audit.', array(), 'doc') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<div class="ops-grid">
 <div>
  <form class="ops-filter" method="get" action="<?= site_url('admin/arsip') ?>"><input type="search" name="q" value="<?= ops_e($q) ?>" placeholder="Cari judul, nomor, atau periode..." aria-label="Cari dokumen"><select name="kategori" aria-label="Kategori"><?= ops_options($categories, $cat, 'Semua Kategori') ?></select><button class="ops-btn" type="submit">Cari</button></form>
  <section class="ops-card"><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Dokumen</th><th>Kategori</th><th>Tanggal</th><th>Akses</th><th>Versi</th><th></th></tr></thead><tbody>
  <?php foreach ($items as $d): ?><tr><td data-label="Dokumen"><strong><?= ops_e($d['title']) ?></strong><small><?= ops_e($d['doc_number'] ?: '') ?><?= $d['period'] ? ' · ' . ops_e($d['period']) : '' ?></small></td><td data-label="Kategori"><?= ops_e($categories[$d['category']] ?? $d['category']) ?></td>
   <td data-label="Tanggal"><?= ops_date($d['doc_date'] ?: $d['created_at']) ?></td><td data-label="Akses"><?= ops_badge($d['confidentiality'], array('umum' => 'Umum', 'internal' => 'Internal', 'rahasia' => 'Rahasia'), array('umum' => 'info', 'internal' => 'purple', 'rahasia' => 'bad')) ?></td>
   <td data-label="Versi">v<?= (int) $d['current_version'] ?></td><td data-label=""><a href="<?= site_url('admin/arsip/' . (int) $d['id']) ?>">Detail</a></td></tr><?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="6" class="ops-empty"><?= ops_empty_state('Belum ada dokumen','Unggah dokumen pertama untuk mulai mengelola arsip.','doc',NULL,'Unggah Dokumen',$can('arsip.write')?'f-title':NULL) ?></td></tr><?php endif; ?>
  </tbody></table></div></section>
 </div>
 <?php if ($can('arsip.write')): ?>
 <section class="ops-card"><h2>Unggah Dokumen</h2>
  <form method="post" enctype="multipart/form-data" class="ops-form one"><?= ops_csrf() ?>
   <?= ops_field('title', 'Judul', 'text', '', array('required' => TRUE, 'maxlength' => 190)) ?>
   <?= ops_field('category', 'Kategori', 'select', 'umum', array('options' => $categories)) ?>
   <?= ops_field('doc_number', 'Nomor dokumen', 'text', '', array('maxlength' => 100)) ?>
   <?= ops_field('doc_date', 'Tanggal dokumen', 'date', '') ?>
   <?= ops_field('period', 'Periode', 'text', '', array('maxlength' => 40, 'placeholder' => '2026 / Semester 2')) ?>
   <?= ops_field('confidentiality', 'Tingkat kerahasiaan', 'select', 'internal', array('options' => $can('arsip.confidential') ? $levels : array_diff_key($levels, array('rahasia' => 1)))) ?>
   <?= ops_field('retention_until', 'Simpan hingga (retensi)', 'date', '') ?>
   <?= ops_field('document', 'Berkas', 'file', '', array('required' => TRUE, 'help' => 'PDF, gambar, Word, Excel, TXT/CSV. Maksimal 10 MB.')) ?>
   <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url('admin/arsip') ?>">Batal</a><button class="ops-btn" type="submit">Simpan Dokumen</button></div>
  </form>
 </section>
 <?php endif; ?>
</div>
</div>
