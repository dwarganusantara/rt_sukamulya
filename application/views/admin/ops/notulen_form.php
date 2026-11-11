<?php $v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; }; $edit = !empty($item['id']); ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/notulen') ?>">Notulen</a><span>›</span><strong><?= $edit ? 'Ubah Notulen' : 'Notulen Baru' ?></strong></nav>
<?= ops_hero('Kegiatan', $edit ? 'Ubah Notulen (versi ' . ((int) $v('version', 1) + 1) . ')' : 'Notulen Baru', 'Simpan sebagai draf atau ajukan untuk disahkan. Versi sebelumnya tetap tersimpan untuk audit.', array(), 'lines') ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<section class="ops-card"><form method="post" class="ops-form"><?= ops_csrf() ?>
 <?= ops_field('title', 'Judul kegiatan / rapat', 'text', $v('title'), array('required' => TRUE, 'maxlength' => 160, 'wide' => TRUE)) ?>
 <?= ops_field('note_date', 'Tanggal', 'date', $v('note_date', date('Y-m-d')), array('required' => TRUE)) ?>
 <?= ops_field('location', 'Lokasi', 'text', $v('location'), array('maxlength' => 160)) ?>
 <?= ops_field('agenda_id', 'Agenda terkait', 'select', (string) $v('agenda_id'), array('options' => $agendas, 'placeholder_option' => 'Tidak terkait agenda')) ?>
 <?= ops_field('program_id', 'Program kerja terkait', 'select', (string) $v('program_id'), array('options' => $programs, 'placeholder_option' => 'Tidak terkait program')) ?>
 <?= ops_field('participants', 'Peserta', 'text', $v('participants'), array('maxlength' => 1000, 'placeholder' => 'Nama atau unsur peserta, pisahkan dengan koma')) ?>
 <?= ops_field('participant_count', 'Jumlah peserta', 'number', (int) $v('participant_count', 0), array('min' => 0)) ?>
 <?= ops_field('summary', 'Ringkasan', 'textarea', $v('summary'), array('rows' => 4, 'wide' => TRUE)) ?>
 <?= ops_field('decisions', 'Keputusan', 'textarea', $v('decisions'), array('rows' => 4, 'wide' => TRUE)) ?>
 <?= ops_field('obstacles', 'Kendala', 'textarea', $v('obstacles'), array('rows' => 2, 'wide' => TRUE)) ?>
 <?= ops_field('finalize', 'Ajukan untuk pengesahan (status: Menunggu Pengesahan)', 'checkbox', $v('status') === 'final') ?>
 <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url($edit ? 'admin/notulen/' . (int) $item['id'] : 'admin/notulen') ?>">Batal</a><button class="ops-btn" type="submit">Simpan</button></div>
</form></section>
<p class="ops-muted">Tindak lanjut (action item) ditambahkan di halaman detail setelah notulen disimpan.</p>
</div>
