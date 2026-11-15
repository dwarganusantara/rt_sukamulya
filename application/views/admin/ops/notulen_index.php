<div class="ops">
<?= ops_tabs(array('admin/program' => 'Program Kerja', 'admin/notulen' => 'Catatan & Notulen', 'admin/ronda' => 'Jadwal Ronda', 'admin/posts/agenda' => 'Agenda'), 'admin/notulen') ?>
<?= ops_hero('Kegiatan', 'Catatan Kegiatan & Notulen', 'Dokumentasi rapat dan kegiatan: peserta, ringkasan, dan keputusan. Notulen langsung terbit setelah disimpan; setiap perubahan tersimpan sebagai versi.', $can('notulen.write') ? array(array('admin/notulen/baru', '+ Notulen Baru')) : array(), 'lines') ?>
<?= ops_flash() ?>
<?php /* REVISI: daftar tindak lanjut terlambat dihapus. */ ?>
<form class="ops-filter" method="get" action="<?= site_url('admin/notulen') ?>">
 <input type="search" name="q" value="<?= ops_e($q) ?>" placeholder="Cari judul, ringkasan, atau keputusan..." aria-label="Cari notulen">
 <select name="status" aria-label="Status"><?= ops_options($labels, $status, 'Semua Status') ?></select>
 <button class="ops-btn" type="submit">Filter</button>
</form>
<section class="ops-card"><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Terkait</th><th class="num">Peserta</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($items as $it): ?>
 <tr><td data-label="Tanggal"><?= ops_date($it['note_date']) ?></td><td data-label="Kegiatan"><strong><?= ops_e($it['title']) ?></strong><small><?= ops_e($it['location']) ?> · v<?= (int) $it['version'] ?></small></td>
  <td data-label="Terkait"><?= ops_e($it['agenda_title'] ?: '—') ?><small><?= ops_e($it['program_title'] ?: '') ?></small></td><td data-label="Peserta" class="num"><?= (int) $it['participant_count'] ?></td>
  <td data-label="Status"><?= ops_badge($it['status'], $labels, $tones) ?></td><td data-label=""><a href="<?= site_url('admin/notulen/' . (int) $it['id']) ?>">Buka</a></td></tr>
<?php endforeach; if (!$items): ?><tr><td colspan="6" class="ops-empty">Belum ada notulen.</td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
