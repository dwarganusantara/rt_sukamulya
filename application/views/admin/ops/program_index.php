<div class="ops">
<?= ops_tabs(array('admin/program' => 'Program Kerja', 'admin/notulen' => 'Catatan & Notulen', 'admin/ronda' => 'Jadwal Ronda', 'admin/posts/agenda' => 'Agenda'), 'admin/program') ?>
<?= ops_hero('Kegiatan', 'Program Kerja RT', 'Rencana kegiatan beserta target, PIC, anggaran, milestone, dan progres. Program dapat dihubungkan dengan agenda, notulen, dan belanja.', $can('program.write') ? array(array('admin/program/baru', '+ Program Baru')) : array(), 'briefcase') ?>
<?= ops_flash() ?>
<?= ops_stats(array(
    array('Aktif', (int) ($counts['aktif'] ?? 0), 'Sedang berjalan', 'blue', 'admin/program?status=aktif'),
    array('Menunggu Persetujuan', (int) ($counts['diajukan'] ?? 0), 'Perlu keputusan ketua', 'orange', 'admin/program?status=diajukan'),
    array('Draf', (int) ($counts['draft'] ?? 0), 'Belum diajukan', 'purple', 'admin/program?status=draft'),
    array('Selesai', (int) ($counts['selesai'] ?? 0), 'Sudah dievaluasi', 'green', 'admin/program?status=selesai'),
)) ?>
<form class="ops-filter" method="get" action="<?= site_url('admin/program') ?>">
 <input type="search" name="q" value="<?= ops_e($q) ?>" placeholder="Cari nama program, PIC, atau deskripsi..." aria-label="Cari program">
 <select name="status" aria-label="Status"><?= ops_options($labels, $status, 'Semua Status') ?></select>
 <button class="ops-btn" type="submit">Filter</button>
</form>
<section class="ops-card"><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Program</th><th>Periode</th><th>PIC</th><th class="num">Anggaran</th><th>Progres</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($items as $it): ?>
 <tr>
  <td data-label="Program"><strong><?= ops_e($it['title']) ?></strong><small><?= ops_e($categories[$it['category']] ?? $it['category']) ?></small></td>
  <td data-label="Periode"><?= ops_date($it['period_start']) ?><small>s.d. <?= ops_date($it['period_end']) ?></small></td>
  <td data-label="PIC"><?= ops_e($it['pic'] ?: '—') ?></td>
  <td data-label="Anggaran" class="num"><?= ops_money($it['budget']) ?></td>
  <td data-label="Progres"><div class="ops-progress" role="progressbar" aria-valuenow="<?= (int) $it['progress'] ?>" aria-valuemin="0" aria-valuemax="100"><i style="width:<?= (int) $it['progress'] ?>%"></i></div><small><?= (int) $it['progress'] ?>%</small></td>
  <td data-label="Status"><?= ops_badge($it['status'], $labels, $tones) ?></td>
  <td data-label=""><a href="<?= site_url('admin/program/' . (int) $it['id']) ?>">Buka</a></td>
 </tr>
<?php endforeach; if (!$items): ?><tr><td colspan="7" class="ops-empty">Belum ada program kerja.</td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
