<?php $v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; }; ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/program') ?>">Program Kerja</a><span>›</span><strong><?= $item && !empty($item['id']) ? 'Ubah Program' : 'Program Baru' ?></strong></nav>
<?= ops_hero('Kegiatan', $item && !empty($item['id']) ? 'Ubah Program Kerja' : 'Program Kerja Baru', 'Program baru tersimpan sebagai draf, lalu diajukan untuk disetujui Ketua RT sebelum aktif.', array(), 'briefcase') ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<section class="ops-card"><form method="post" class="ops-form"><?= ops_csrf() ?>
 <?= ops_field('title', 'Nama program', 'text', $v('title'), array('required' => TRUE, 'maxlength' => 160, 'wide' => TRUE)) ?>
 <?= ops_field('category', 'Kategori', 'select', $v('category', 'lingkungan'), array('options' => $categories)) ?>
 <?= ops_field('pic', 'Penanggung jawab (PIC)', 'text', $v('pic'), array('maxlength' => 120)) ?>
 <?= ops_field('period_start', 'Mulai', 'date', $v('period_start'), array('required' => TRUE)) ?>
 <?= ops_field('period_end', 'Selesai', 'date', $v('period_end'), array('required' => TRUE)) ?>
 <?= ops_field('target', 'Target', 'text', $v('target'), array('maxlength' => 255, 'placeholder' => 'Contoh: 100% saluran air bersih dari sampah')) ?>
 <?= ops_field('indicator', 'Indikator keberhasilan', 'text', $v('indicator'), array('maxlength' => 255)) ?>
 <?= ops_field('budget', 'Estimasi biaya (Rp)', 'number', (int) $v('budget', 0), array('min' => 0, 'step' => 1)) ?>
 <?= ops_field('realization', 'Realisasi manual (Rp)', 'number', (int) $v('realization', 0), array('min' => 0, 'step' => 1, 'help' => 'Belanja yang terhubung dihitung otomatis di halaman detail.')) ?>
 <?= ops_field('description', 'Deskripsi', 'textarea', $v('description'), array('rows' => 4, 'wide' => TRUE)) ?>
 <?= ops_field('obstacles', 'Hambatan', 'textarea', $v('obstacles'), array('rows' => 2, 'wide' => TRUE)) ?>
 <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url(!empty($item['id']) ? 'admin/program/' . (int) $item['id'] : 'admin/program') ?>">Batal</a><button class="ops-btn" type="submit">Simpan</button></div>
</form></section>
</div>
