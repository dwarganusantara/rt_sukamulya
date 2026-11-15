<?php
/* REVISI: Jadwal Ronda disederhanakan sesuai mockup — ringkasan pekan ini, jadwal, tambah jadwal (3 isian), rekap kehadiran, kejadian terbaru. */
$this->load->helper('official_icons');
$css = 'assets/admin-ronda.css';
$by_date = array(); foreach ($items as $it) $by_date[$it['patrol_date']][] = $it;
$days = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
$canWrite = $can('ronda.write');
$tone = array('scheduled' => 'muted', 'hadir' => 'ok', 'terlambat' => 'warn', 'tidak_hadir' => 'bad', 'izin' => 'purple', 'diganti' => 'info');
$quick = array('hadir' => 'Hadir', 'tidak_hadir' => 'Tidak hadir', 'scheduled' => 'Terjadwal');
$circ = 2 * M_PI * 46; $lenHadir = $week['total'] ? $circ * $week['hadir'] / $week['total'] : 0; $lenTidak = $week['total'] ? $circ * $week['tidak_hadir'] / $week['total'] : 0;
$pDate = function ($d) { return date('j M Y', strtotime($d)); };
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<div class="rd">
 <nav class="rd-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>" aria-label="Dashboard"><?= official_svg('home') ?></a><span><?= official_svg('chev-right') ?></span><span class="rd-crumb-tx">Keamanan</span><span><?= official_svg('chev-right') ?></span><strong>Jadwal Ronda</strong></nav>

 <section class="rd-hero">
  <span class="rd-hero-ic" aria-hidden="true"><?= official_svg('shield') ?></span>
  <div class="rd-hero-tx"><h1>Jadwal Ronda &amp; Absensi</h1><p>Atur jadwal keamanan, catat kehadiran, dan lihat rekap ronda.</p></div>
  <div class="rd-art" aria-hidden="true"><b class="cal"><i></i><i></i></b><em class="shield"></em><s class="leaf l1"></s><s class="leaf l2"></s></div>
 </section>

 <?= ops_flash() ?>
 <?php if ($error): ?><div class="rd-error" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

 <section class="rd-stats rd-stats-accent" aria-label="Ringkasan pekan ini (<?= $pDate($week['from']) ?> – <?= $pDate($week['to']) ?>)">
  <article class="green"><span><?= official_svg('calendar') ?></span><div><strong><?= (int) $week['total'] ?></strong><b>Jadwal aktif</b><small>Pekan ini</small></div></article>
  <article class="orange"><span><?= official_svg('people') ?></span><div><strong><?= (int) $week['officers'] ?></strong><b>Petugas ronda</b><small>Terjadwal</small></div></article>
  <article class="teal"><span><?= official_svg('check-circle') ?></span><div><strong><?= (int) $week['hadir'] ?></strong><b>Hadir</b><small><?= (int) $week['pct'] ?>% kehadiran</small></div></article>
  <article class="red"><span><?= official_svg('alert') ?></span><div><strong><?= (int) $week['tidak_hadir'] ?></strong><b>Tidak hadir</b><small>Pekan ini</small></div></article>
 </section>

 <div class="rd-grid">
  <section class="rd-card rd-schedule">
   <header class="rd-card-head">
    <span class="rd-ic orange" aria-hidden="true"><?= official_svg('calendar') ?></span>
    <div><h2>Jadwal Ronda</h2><p><?= $pDate($from) ?> – <?= $pDate($to) ?></p></div>
    <nav class="rd-range" aria-label="Pindah rentang jadwal">
     <a href="<?= site_url('admin/ronda?dari=' . date('Y-m-d', strtotime($from . ' -14 days'))) ?>" aria-label="2 minggu sebelumnya" title="Sebelumnya">‹</a>
     <a class="now" href="<?= site_url('admin/ronda') ?>"><?= official_svg('calendar') ?>Minggu ini</a>
     <a href="<?= site_url('admin/ronda?dari=' . date('Y-m-d', strtotime($from . ' +14 days'))) ?>" aria-label="2 minggu berikutnya" title="Berikutnya">›</a>
    </nav>
   </header>
   <div class="rd-table-wrap"><table class="rd-table"><thead><tr><th>Tanggal</th><th>Shift / Pos</th><th>Petugas</th><th>Kehadiran</th></tr></thead><tbody>
   <?php foreach ($by_date as $date => $rows): foreach ($rows as $i => $it): $st = $it['attendance']; ?>
    <tr<?= $i === 0 ? ' class="first"' : '' ?>>
     <td data-label="Tanggal"><?php if ($i === 0): ?><strong><?= $days[(int) date('w', strtotime($date))] ?></strong><small><?= $pDate($date) ?></small><?php endif; ?></td>
     <td data-label="Shift / Pos"><?= ops_e($shifts[$it['shift']] ?? $it['shift']) ?><small><?= ops_e($it['post_location']) ?></small></td>
     <td data-label="Petugas"><?= ops_e($it['officer_name']) ?><?= $it['substitute_name'] && $st === 'diganti' ? '<small>Pengganti: ' . ops_e($it['substitute_name']) . '</small>' : '' ?><?= $it['check_in'] ? '<small>Masuk ' . date('H:i', strtotime($it['check_in'])) . ($it['check_out'] ? ' · Keluar ' . date('H:i', strtotime($it['check_out'])) : '') . '</small>' : '' ?></td>
     <td data-label="Kehadiran">
      <?php if ($canWrite): ?>
      <details class="rd-att">
       <summary class="rd-pill <?= $tone[$st] ?? 'muted' ?>" aria-label="Ubah kehadiran <?= ops_e($it['officer_name']) ?>"><i></i><?= ops_e($attendance[$st] ?? $st) ?></summary>
       <div class="rd-att-menu" role="menu">
        <?php foreach ($quick as $qk => $ql): if ($qk === $st) continue; ?>
        <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="attendance"><input type="hidden" name="schedule_id" value="<?= (int) $it['id'] ?>"><input type="hidden" name="attendance" value="<?= $qk ?>"><button type="submit" role="menuitem" class="opt <?= $tone[$qk] ?>"><i></i>Tandai <?= $ql ?></button></form>
        <?php endforeach; ?>
        <?php if ($st === 'scheduled' && $it['patrol_date'] >= date('Y-m-d')): ?><form method="post" onsubmit="return confirm('Hapus jadwal ronda ini?')"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="schedule_id" value="<?= (int) $it['id'] ?>"><button type="submit" role="menuitem" class="opt del"><?= official_svg('trash') ?>Hapus jadwal</button></form><?php endif; ?>
       </div>
      </details>
      <?php else: ?><span class="rd-pill <?= $tone[$st] ?? 'muted' ?>"><i></i><?= ops_e($attendance[$st] ?? $st) ?></span><?php endif; ?>
     </td>
    </tr>
   <?php endforeach; endforeach; if (!$items): ?><tr><td colspan="4" class="rd-empty">Belum ada jadwal pada rentang ini.<?= $canWrite ? ' Tambahkan lewat form <b>Tambah Jadwal</b>.' : '' ?></td></tr><?php endif; ?>
   </tbody></table></div>
  </section>

  <?php if ($canWrite): ?>
  <section class="rd-card rd-add">
   <header class="rd-card-head"><span class="rd-ic orange soft" aria-hidden="true"><?= official_svg('plus') ?></span><div><h2>Tambah Jadwal</h2></div></header>
   <form method="post" class="rd-form rd-inline" id="rd-add-form"><?= ops_csrf() ?><input type="hidden" name="act" value="add"><input type="hidden" name="post_location" value="Pos Ronda">
    <label class="rd-field"><span>Tanggal <b>*</b></span><span class="rd-ctl"><?= official_svg('calendar') ?><input type="date" name="patrol_date" required value="<?= date('Y-m-d') ?>"></span></label>
    <label class="rd-field"><span>Shift</span><span class="rd-ctl sel"><select name="shift"><?php foreach ($shifts as $sk => $sl): ?><option value="<?= $sk ?>"<?= $sk === 'shift_1' ? ' selected' : '' ?>><?= ops_e($sl) ?></option><?php endforeach; ?></select></span></label>
    <label class="rd-field"><span>Petugas <b>*</b></span><span class="rd-ctl sel"><select name="citizen_id" id="rd-officer" required><option value="">Pilih petugas</option><?php foreach ($citizens as $cid => $cn): ?><option value="<?= (int) $cid ?>"><?= ops_e($cn) ?></option><?php endforeach; ?><option value="lain">Nama lain (bukan akun warga)…</option></select></span></label>
    <label class="rd-field" id="rd-other" hidden><span>Nama petugas <b>*</b></span><span class="rd-ctl"><?= official_svg('person') ?><input name="officer_name" id="rd-other-name" maxlength="140" placeholder="Tulis nama petugas"></span></label>
    <button class="rd-submit" type="submit"><?= official_svg('calendar') ?>Tambah Jadwal</button>
    <p class="rd-hint">Petugas yang punya akun warga bisa check-in sendiri dari portal warga.</p>
   </form>
  </section>
  <?php endif; ?>

  <section class="rd-card rd-recap">
   <header class="rd-card-head"><span class="rd-bars" aria-hidden="true"><i></i><i></i><i></i></span><div><h2>Rekap Kehadiran <small>(Pekan ini)</small></h2></div></header>
   <div class="rd-recap-body">
    <svg class="rd-donut" viewBox="0 0 120 120" role="img" aria-label="<?= (int) $week['pct'] ?>% kehadiran pekan ini">
     <circle cx="60" cy="60" r="46" class="track"/>
     <?php if ($lenHadir): ?><circle cx="60" cy="60" r="46" class="seg ok" stroke-dasharray="<?= round($lenHadir, 2) ?> <?= round($circ, 2) ?>" transform="rotate(-90 60 60)"/><?php endif; ?>
     <?php if ($lenTidak): ?><circle cx="60" cy="60" r="46" class="seg bad" stroke-dasharray="<?= round($lenTidak, 2) ?> <?= round($circ, 2) ?>" stroke-dashoffset="<?= round(-$lenHadir, 2) ?>" transform="rotate(-90 60 60)"/><?php endif; ?>
     <text x="60" y="60" class="n"><?= (int) $week['pct'] ?>%</text><text x="60" y="78" class="l">Kehadiran</text>
    </svg>
    <ul class="rd-legend">
     <li class="ok"><b>Hadir</b><span><?= (int) $week['hadir'] ?></span></li>
     <li class="bad"><b>Tidak hadir</b><span><?= (int) $week['tidak_hadir'] ?></span></li>
     <li class="muted"><b>Terjadwal</b><span><?= (int) $week['scheduled'] ?></span></li>
     <?php if ($week['lain']): ?><li class="purple"><b>Izin / digantikan</b><span><?= (int) $week['lain'] ?></span></li><?php endif; ?>
    </ul>
   </div>
  </section>

  <section class="rd-card rd-incidents">
   <header class="rd-card-head"><span class="rd-ic orange plain" aria-hidden="true"><?= official_svg('doc') ?></span><div><h2>Kejadian Terbaru</h2></div></header>
   <div class="rd-inc-body">
   <?php if ($incidents): ?><ul class="rd-timeline"><?php foreach ($incidents as $inc): ?><li><strong><?= ops_e($incident_categories[$inc['category']] ?? $inc['category']) ?></strong><small><?= date('j M Y, H:i', strtotime($inc['incident_at'])) ?><?= $inc['location'] ? ' · ' . ops_e($inc['location']) : '' ?></small><?php if ($inc['description']): ?><p><?= ops_e($inc['description']) ?></p><?php endif; ?><?php if ($inc['action_taken']): ?><p class="act"><b>Tindakan:</b> <?= ops_e($inc['action_taken']) ?></p><?php endif; ?></li><?php endforeach; ?></ul>
   <?php else: ?><p class="rd-empty-note">Belum ada laporan kejadian.</p><?php endif; ?>
   <?php if ($canWrite): ?>
   <details class="rd-inc-add">
    <summary><?= official_svg('plus') ?>Catat Kejadian</summary>
    <form method="post" action="<?= site_url('admin/ronda/kejadian') ?>" class="rd-form"><?= ops_csrf() ?>
     <div class="rd-two">
      <label class="rd-field"><span>Jenis</span><span class="rd-ctl sel"><select name="category"><?php foreach ($incident_categories as $ik => $il): ?><option value="<?= $ik ?>"<?= $ik === 'lainnya' ? ' selected' : '' ?>><?= ops_e($il) ?></option><?php endforeach; ?></select></span></label>
      <label class="rd-field"><span>Waktu <b>*</b></span><span class="rd-ctl"><input type="datetime-local" name="incident_at" required value="<?= date('Y-m-d\TH:i') ?>"></span></label>
     </div>
     <label class="rd-field"><span>Uraian kejadian <b>*</b></span><span class="rd-ctl area"><textarea name="description" rows="2" required minlength="5" maxlength="3000" placeholder="Apa yang terjadi?"></textarea></span></label>
     <label class="rd-field"><span>Tindakan <em>(opsional)</em></span><span class="rd-ctl"><input name="action_taken" maxlength="3000" placeholder="Tindakan yang diambil"></span></label>
     <input type="hidden" name="location" value="">
     <button class="rd-submit small" type="submit"><?= official_svg('save') ?>Simpan Kejadian</button>
    </form>
   </details>
   <?php endif; ?>
   </div>
  </section>
 </div>
</div>
<script>
(function(){
 var sel=document.getElementById('rd-officer'),other=document.getElementById('rd-other'),name=document.getElementById('rd-other-name');
 if(sel){function sync(){var o=sel.value==='lain';other.hidden=!o;name.required=o;if(o)name.focus();}sel.addEventListener('change',sync);sync();
  // "Nama lain" dikirim sebagai citizen_id kosong; nama diambil dari isian Nama petugas.
  document.getElementById('rd-add-form').addEventListener('submit',function(){if(sel.value==='lain'){sel.disabled=true;var h=document.createElement('input');h.type='hidden';h.name='citizen_id';h.value='';this.appendChild(h);}});}
 var menus=[].slice.call(document.querySelectorAll('.rd-att'));
 document.addEventListener('click',function(e){menus.forEach(function(m){if(m.open&&!m.contains(e.target))m.open=false;});});
 document.addEventListener('keydown',function(e){if(e.key==='Escape')menus.forEach(function(m){m.open=false;});});
 menus.forEach(function(m){m.addEventListener('toggle',function(){if(m.open)menus.forEach(function(x){if(x!==m)x.open=false;});});});
})();
</script>
