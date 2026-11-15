<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Jadwal Ronda', '')))); ?>
 <section class="cdx-hero cpx-warm crd-hero"><span class="crd-hero-art crd-hero-art--scene" aria-hidden="true"><img src="<?= base_url('assets/img/dashboard/footer-banner-v2.jpg') ?>" alt=""></span><span class="cdx-hero-icon"><?= official_svg('shield') ?></span><div><h1>Jadwal <b>Ronda</b></h1><p>Lihat jadwal piket keamanan lingkungan. Jika Anda bertugas, lakukan check-in saat tiba di pos dan check-out setelah selesai.</p></div></section>
 <?php $this->load->view('citizen/ops/_flash'); ?>
 <section class="cdx-card">
  <header class="cdx-head"><span class="red"><?= official_svg('person') ?></span><div><h2>Jadwal saya</h2><p>Check-in kehadiran tersedia mulai 1 jam sebelum shift dimulai.</p></div></header>
  <div class="cdx-scroll"><table class="cdx-table ops-table stack"><thead><tr><th>Tanggal</th><th>Shift</th><th>Pos</th><th>Kehadiran</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($mine as $s): ?>
   <tr><td data-label="Tanggal"><?= ops_date($s['patrol_date']) ?></td><td data-label="Shift"><?= html_escape($shifts[$s['shift']] ?? '') ?></td><td data-label="Pos"><?= html_escape($s['post_location']) ?></td>
    <td data-label="Kehadiran"><?= ops_badge($s['attendance'], $attendance, $attendance_tones) ?><?= $s['check_in'] ? '<small>Masuk ' . date('H:i', strtotime($s['check_in'])) . ($s['check_out'] ? ' · Keluar ' . date('H:i', strtotime($s['check_out'])) : '') . '</small>' : '' ?></td>
    <td data-label="Aksi"><?php if (!$s['check_in']): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="schedule_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="act" value="checkin"><button class="ops-btn small" type="submit">Check-in</button></form>
     <?php elseif (!$s['check_out']): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="schedule_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="act" value="checkout"><button class="ops-btn outline small" type="submit">Check-out</button></form><?php else: ?>Selesai<?php endif; ?></td></tr>
  <?php endforeach; if (!$mine): ?><tr><td colspan="5" class="ops-empty">Anda tidak memiliki jadwal ronda dalam waktu dekat.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
 <section class="cdx-card">
  <header class="cdx-head"><span class="red"><?= official_svg('calendar') ?></span><div><h2>Jadwal 7 hari ke depan</h2><p>Petugas ronda lingkungan RT.</p></div></header>
  <div class="cdx-scroll"><table class="cdx-table ops-table stack"><thead><tr><th>Tanggal</th><th>Shift</th><th>Pos</th><th>Petugas</th></tr></thead><tbody>
  <?php foreach ($all as $s): ?><tr><td data-label="Tanggal"><?= ops_date($s['patrol_date']) ?></td><td data-label="Shift"><?= html_escape($shifts[$s['shift']] ?? '') ?></td><td data-label="Pos"><?= html_escape($s['post_location']) ?></td><td data-label="Petugas"><?= html_escape($s['officer_name']) ?></td></tr><?php endforeach; ?>
  <?php if (!$all): ?><tr><td colspan="4" class="ops-empty">Jadwal belum tersedia.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
