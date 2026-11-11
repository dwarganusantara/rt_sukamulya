<?php $by_date = array(); foreach ($items as $it) $by_date[$it['patrol_date']][] = $it; $days = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'); ?>
<div class="ops">
<?= ops_tabs(array('admin/program' => 'Program Kerja', 'admin/notulen' => 'Catatan & Notulen', 'admin/ronda' => 'Jadwal Ronda', 'admin/posts/agenda' => 'Agenda'), 'admin/ronda') ?>
<?= ops_hero('Keamanan', 'Jadwal Ronda & Absensi', 'Atur piket keamanan, catat kehadiran, dan laporan kejadian. Warga yang terhubung ke jadwal dapat check-in sendiri dari portal warga.', array(), 'shield') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<?= ops_stats(array(
    array('Hadir bulan ini', (int) ($recap['hadir'] ?? 0), 'Tepat waktu', 'green'),
    array('Terlambat', (int) ($recap['terlambat'] ?? 0), 'Check-in > 15 menit', 'amber'),
    array('Tidak hadir', (int) ($recap['tidak_hadir'] ?? 0), 'Perlu ditinjau pengurus', 'red'),
    array('Izin / digantikan', (int) ($recap['izin'] ?? 0) + (int) ($recap['diganti'] ?? 0), 'Bulan ' . date('m/Y'), 'purple'),
)) ?>
<div class="ops-grid">
 <section class="ops-card">
  <header><div><h2>Jadwal <?= ops_date($from) ?> – <?= ops_date($to) ?></h2></div>
   <div class="ops-actions"><a class="ops-btn ghost small" href="<?= site_url('admin/ronda?dari=' . date('Y-m-d', strtotime($from . ' -14 days'))) ?>">‹ Sebelumnya</a><a class="ops-btn ghost small" href="<?= site_url('admin/ronda') ?>">Minggu ini</a><a class="ops-btn ghost small" href="<?= site_url('admin/ronda?dari=' . date('Y-m-d', strtotime($from . ' +14 days'))) ?>">Berikutnya ›</a></div></header>
  <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Tanggal</th><th>Shift / Pos</th><th>Petugas</th><th>Kehadiran</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($by_date as $date => $rows): foreach ($rows as $i => $it): ?>
   <tr><td data-label="Tanggal"><?= $i === 0 ? '<strong>' . $days[(int) date('w', strtotime($date))] . '</strong><small>' . ops_date($date) . '</small>' : '' ?></td>
    <td data-label="Shift"><?= ops_e($shifts[$it['shift']] ?? $it['shift']) ?><small><?= ops_e($it['post_location']) ?></small></td>
    <td data-label="Petugas"><?= ops_e($it['officer_name']) ?><?= $it['citizen_id'] ? '<small>Akun warga terhubung</small>' : '' ?><?= $it['substitute_name'] ? '<small>Pengganti: ' . ops_e($it['substitute_name']) . '</small>' : '' ?></td>
    <td data-label="Kehadiran"><?= ops_badge($it['attendance'], $attendance, $attendance_tones) ?><?= $it['check_in'] ? '<small>Masuk ' . date('H:i', strtotime($it['check_in'])) . ($it['check_out'] ? ' · Keluar ' . date('H:i', strtotime($it['check_out'])) : '') . '</small>' : '' ?><?= $it['attendance_note'] ? '<small>' . ops_e($it['attendance_note']) . '</small>' : '' ?></td>
    <td data-label="Aksi"><?php if ($can('ronda.write')): ?><details class="ops-more"><summary>Ubah kehadiran</summary>
     <form method="post" class="ops-form one" style="margin-top:8px"><?= ops_csrf() ?><input type="hidden" name="act" value="attendance"><input type="hidden" name="schedule_id" value="<?= (int) $it['id'] ?>">
      <?= ops_field('attendance', 'Status', 'select', $it['attendance'], array('options' => $attendance)) ?><?= ops_field('substitute_name', 'Pengganti (jika digantikan)', 'text', $it['substitute_name'], array('maxlength' => 140)) ?>
      <?= ops_field('attendance_note', 'Alasan perubahan manual', 'text', '', array('required' => TRUE, 'maxlength' => 255)) ?><button class="ops-btn small" type="submit">Simpan</button></form>
     <?php if ($it['attendance'] === 'scheduled' && $it['patrol_date'] >= date('Y-m-d')): ?><form method="post" style="margin-top:6px" onsubmit="return confirm('Hapus jadwal ini?')"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="schedule_id" value="<?= (int) $it['id'] ?>"><button class="ops-btn red outline small" type="submit">Hapus jadwal</button></form><?php endif; ?>
    </details><?php endif; ?></td></tr>
  <?php endforeach; endforeach; if (!$items): ?><tr><td colspan="5" class="ops-empty">Belum ada jadwal pada rentang ini.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
 <div>
  <?php if ($can('ronda.write')): ?>
  <section class="ops-card"><h2>Tambah jadwal</h2>
   <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="add">
    <?= ops_field('patrol_date', 'Tanggal', 'date', date('Y-m-d'), array('required' => TRUE)) ?>
    <?= ops_field('shift', 'Shift', 'select', 'shift_1', array('options' => $shifts)) ?>
    <?= ops_field('citizen_id', 'Akun warga (opsional, untuk check-in mandiri)', 'select', '', array('options' => $citizens, 'placeholder_option' => 'Tidak terhubung')) ?>
    <?= ops_field('officer_name', 'Nama petugas', 'text', '', array('maxlength' => 140, 'help' => 'Kosongkan jika memilih akun warga; nama akan diambil otomatis.')) ?>
    <?= ops_field('post_location', 'Pos / lokasi', 'text', 'Pos Ronda', array('maxlength' => 120)) ?>
    <div class="ops-form-actions"><button class="ops-btn" type="submit">Tambah Jadwal</button></div>
   </form>
  </section>
  <section class="ops-card"><h2>Catat kejadian</h2>
   <form method="post" action="<?= site_url('admin/ronda/kejadian') ?>" class="ops-form one"><?= ops_csrf() ?>
    <?= ops_field('incident_at', 'Waktu kejadian', 'datetime-local', date('Y-m-d\TH:i'), array('required' => TRUE)) ?>
    <?= ops_field('category', 'Jenis', 'select', 'lainnya', array('options' => $incident_categories)) ?>
    <?= ops_field('location', 'Lokasi', 'text', '', array('maxlength' => 160)) ?>
    <?= ops_field('description', 'Uraian kejadian', 'textarea', '', array('required' => TRUE, 'rows' => 3)) ?>
    <?= ops_field('action_taken', 'Tindakan yang diambil', 'textarea', '', array('rows' => 2)) ?>
    <div class="ops-form-actions"><button class="ops-btn" type="submit">Simpan Laporan</button></div>
   </form>
  </section>
  <?php endif; ?>
  <section class="ops-card"><h2>Kejadian terbaru</h2>
   <ul class="ops-timeline"><?php foreach ($incidents as $inc): ?><li><strong><?= ops_e($incident_categories[$inc['category']] ?? $inc['category']) ?></strong><small><?= ops_date($inc['incident_at'], TRUE) ?><?= $inc['location'] ? ' · ' . ops_e($inc['location']) : '' ?></small><p><?= ops_e($inc['description']) ?><?= $inc['action_taken'] ? "\nTindakan: " . ops_e($inc['action_taken']) : '' ?></p></li><?php endforeach; ?></ul>
   <?php if (!$incidents): ?><p class="ops-muted">Belum ada laporan kejadian.</p><?php endif; ?>
  </section>
 </div>
</div>
</div>
