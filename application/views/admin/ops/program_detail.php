<?php
$closed = in_array($item['status'], array('selesai', 'dibatalkan'), TRUE);
$btn = array('diajukan' => array('Ajukan Persetujuan', ''), 'aktif' => array($item['status'] === 'ditunda' ? 'Lanjutkan Program' : 'Setujui & Aktifkan', 'green'), 'draft' => array('Kembalikan ke Draf', 'ghost'),
    'ditunda' => array('Tunda', 'ghost'), 'dibatalkan' => array('Batalkan Program', 'red outline'), 'selesai' => array('Tandai Selesai', 'green'));
$approve_needed = function ($to) use ($item) { return in_array($to, array('aktif', 'selesai', 'dibatalkan'), TRUE) || ($item['status'] === 'diajukan' && $to === 'draft'); };
?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/program') ?>">Program Kerja</a><span>›</span><strong><?= ops_e($item['title']) ?></strong></nav>
<?= ops_hero($categories[$item['category']] ?? 'Program', $item['title'], ops_date($item['period_start']) . ' s.d. ' . ops_date($item['period_end']) . ' · PIC: ' . ($item['pic'] ?: '-'), !$closed && $can('program.write') ? array(array('admin/program/' . (int) $item['id'] . '/ubah', 'Ubah Data', 'ghost')) : array(), 'briefcase') ?>
<?= ops_flash() ?>
<?= ops_stats(array(
    array('Status', $labels[$item['status']], $approver ? 'Disetujui: ' . $approver : '', 'blue'),
    array('Progres', (int) $item['progress'] . '%', count($milestones) . ' milestone', 'green'),
    array('Anggaran', ops_money($item['budget']), 'Estimasi', 'purple'),
    array('Realisasi', ops_money((float) $item['realization'] + $spent), 'Manual + belanja diterima', 'orange'),
)) ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><h2>Rincian</h2>
   <dl class="ops-dl"><dt>Target</dt><dd><?= ops_e($item['target'] ?: '—') ?></dd><dt>Indikator</dt><dd><?= ops_e($item['indicator'] ?: '—') ?></dd>
    <dt>Deskripsi</dt><dd><?= ops_e($item['description'] ?: '—') ?></dd><dt>Hambatan</dt><dd><?= ops_e($item['obstacles'] ?: '—') ?></dd>
    <?php if ($item['evaluation']): ?><dt>Evaluasi</dt><dd><?= ops_e($item['evaluation']) ?></dd><?php endif; ?></dl>
  </section>
  <section class="ops-card"><header><div><h2>Milestone</h2><p>Progres dihitung dari bobot milestone yang selesai.</p></div></header>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Milestone</th><th>Tenggat</th><th class="num">Bobot</th><th>Status</th><th></th></tr></thead><tbody>
   <?php foreach ($milestones as $m): ?><tr><td data-label="Milestone"><?= ops_e($m['title']) ?></td><td data-label="Tenggat"><?= ops_date($m['due_date']) ?></td><td data-label="Bobot" class="num"><?= (int) $m['weight'] ?></td>
    <td data-label="Status"><?= $m['is_done'] ? ops_badge('done', array('done' => 'Selesai'), array('done' => 'ok')) : ($m['due_date'] && $m['due_date'] < date('Y-m-d') ? ops_badge('late', array('late' => 'Terlambat'), array('late' => 'bad')) : ops_badge('open', array('open' => 'Belum'), array('open' => 'pending'))) ?></td>
    <td data-label=""><?php if (!$closed && $can('program.write')): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="milestone_toggle"><input type="hidden" name="milestone_id" value="<?= (int) $m['id'] ?>"><button class="ops-btn ghost small" type="submit"><?= $m['is_done'] ? 'Batalkan' : 'Tandai selesai' ?></button></form><?php endif; ?></td></tr>
   <?php endforeach; if (!$milestones): ?><tr><td colspan="5" class="ops-empty">Belum ada milestone.</td></tr><?php endif; ?>
   </tbody></table></div>
   <?php if (!$closed && $can('program.write')): ?>
   <form method="post" class="ops-inline" style="margin-top:10px"><?= ops_csrf() ?><input type="hidden" name="act" value="milestone_add">
    <?= ops_field('m_title', 'Milestone baru', 'text', '', array('required' => TRUE, 'maxlength' => 160)) ?><?= ops_field('m_due', 'Tenggat', 'date', '') ?><?= ops_field('m_weight', 'Bobot', 'number', 1, array('min' => 1, 'max' => 100)) ?>
    <button class="ops-btn" type="submit">Tambah</button>
   </form>
   <?php endif; ?>
  </section>
 </div>
 <div>
  <?php if ($next && ($can('program.write') || $can('program.approve'))): ?>
  <section class="ops-card"><h2>Ubah status</h2>
   <?php foreach ($next as $to): if ($approve_needed($to) && !$can('program.approve')) continue; ?>
    <form method="post" class="ops-form one" style="margin-bottom:8px"><?= ops_csrf() ?><input type="hidden" name="act" value="status"><input type="hidden" name="to" value="<?= ops_e($to) ?>">
     <?php if ($to === 'selesai'): ?><?= ops_field('evaluation', 'Evaluasi program', 'textarea', '', array('rows' => 2, 'required' => TRUE)) ?><?php endif; ?>
     <button class="ops-btn <?= ops_e($btn[$to][1] ?? '') ?>" type="submit"<?= in_array($to, array('dibatalkan'), TRUE) ? ' onclick="return confirm(\'Batalkan program ini?\')"' : '' ?>><?= ops_e($btn[$to][0] ?? $labels[$to]) ?></button>
    </form>
   <?php endforeach; ?>
   <?php if ($item['status'] === 'diajukan' && !$can('program.approve')): ?><p class="ops-muted">Menunggu persetujuan Ketua RT.</p><?php endif; ?>
  </section>
  <?php endif; ?>
  <section class="ops-card"><h2>Agenda terkait</h2>
   <ul class="ops-timeline"><?php foreach ($agendas as $a): ?><li><strong><a href="<?= site_url('admin/post/agenda/' . (int) $a['id']) ?>"><?= ops_e($a['title']) ?></a></strong><small><?= ops_date($a['event_date']) ?></small>
    <?php if (!$closed && $can('program.write')): ?><form method="post" style="margin-top:4px"><?= ops_csrf() ?><input type="hidden" name="act" value="unlink_agenda"><input type="hidden" name="post_id" value="<?= (int) $a['id'] ?>"><button class="ops-btn ghost small" type="submit">Lepas</button></form><?php endif; ?></li><?php endforeach; ?></ul>
   <?php if (!$agendas): ?><p class="ops-muted">Belum ada agenda terhubung.</p><?php endif; ?>
   <?php if (!$closed && $can('program.write') && $agenda_options): ?>
   <form method="post" class="ops-inline"><?= ops_csrf() ?><input type="hidden" name="act" value="link_agenda"><?= ops_field('post_id', 'Hubungkan agenda', 'select', '', array('options' => $agenda_options, 'placeholder_option' => 'Pilih agenda', 'required' => TRUE)) ?><button class="ops-btn ghost" type="submit">Hubungkan</button></form>
   <?php endif; ?>
  </section>
  <section class="ops-card"><header><h2>Notulen terkait</h2><?php if ($can('notulen.write')): ?><a class="ops-btn ghost small" href="<?= site_url('admin/notulen/baru') ?>">+ Notulen</a><?php endif; ?></header>
   <?php foreach ($notes as $n): ?><p style="margin:0 0 6px"><a href="<?= site_url('admin/notulen/' . (int) $n['id']) ?>"><?= ops_e($n['title']) ?></a> <small class="ops-muted"><?= ops_date($n['note_date']) ?></small></p><?php endforeach; ?>
   <?php if (!$notes): ?><p class="ops-muted">Belum ada notulen yang mengacu ke program ini.</p><?php endif; ?>
  </section>
  <section class="ops-card"><h2>Belanja terkait</h2>
   <?php foreach ($purchases as $p): ?><p style="margin:0 0 6px"><a href="<?= site_url('admin/belanja/' . (int) $p['id']) ?>"><?= ops_e($p['title']) ?></a> <small class="ops-muted"><?= ops_money($p['actual_total'] ?? $p['estimated_total']) ?> · <?= ops_e($p['status']) ?></small></p><?php endforeach; ?>
   <?php if (!$purchases): ?><p class="ops-muted">Belum ada permintaan belanja untuk program ini.</p><?php endif; ?>
  </section>
 </div>
</div>
</div>
