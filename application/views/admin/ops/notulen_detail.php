<?php $locked = $item['status'] === 'disahkan'; ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/notulen') ?>">Notulen</a><span>›</span><strong><?= ops_e($item['title']) ?></strong></nav>
<?= ops_hero('Notulen · versi ' . (int) $item['version'], $item['title'], ops_date($item['note_date']) . ($item['location'] ? ' · ' . $item['location'] : '') . ' · ' . (int) $item['participant_count'] . ' peserta', !$locked && $can('notulen.write') ? array(array('admin/notulen/' . (int) $item['id'] . '/ubah', 'Ubah', 'ghost')) : array(), 'lines') ?>
<?= ops_flash() ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><header><h2>Isi notulen</h2><?= ops_badge($item['status'], $labels, $tones) ?></header>
   <dl class="ops-dl">
    <dt>Agenda</dt><dd><?= $item['agenda_id'] ? '<a href="' . site_url('admin/post/agenda/' . (int) $item['agenda_id']) . '">' . ops_e($item['agenda_title']) . '</a>' : '—' ?></dd>
    <dt>Program</dt><dd><?= $item['program_id'] ? '<a href="' . site_url('admin/program/' . (int) $item['program_id']) . '">' . ops_e($item['program_title']) . '</a>' : '—' ?></dd>
    <dt>Peserta</dt><dd><?= ops_e($item['participants'] ?: '—') ?></dd>
    <dt>Ringkasan</dt><dd><?= ops_e($item['summary'] ?: '—') ?></dd>
    <dt>Keputusan</dt><dd><?= ops_e($item['decisions'] ?: '—') ?></dd>
    <dt>Kendala</dt><dd><?= ops_e($item['obstacles'] ?: '—') ?></dd>
    <dt>Dibuat</dt><dd><?= ops_e($item['creator'] ?: '-') ?> · <?= ops_date($item['created_at'], TRUE) ?></dd>
    <?php if ($item['approved_at']): ?><dt>Disahkan</dt><dd><?= ops_e($item['approver']) ?> · <?= ops_date($item['approved_at'], TRUE) ?></dd><?php endif; ?>
   </dl>
  </section>
  <section class="ops-card"><h2>Tindak lanjut</h2>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Uraian</th><th>PIC</th><th>Tenggat</th><th>Status</th><th>Perbarui</th></tr></thead><tbody>
   <?php foreach ($actions as $a): $late = in_array($a['status'], array('open', 'in_progress'), TRUE) && $a['due_date'] && $a['due_date'] < date('Y-m-d'); ?>
    <tr><td data-label="Uraian"><?= ops_e($a['title']) ?><?= $a['progress_note'] ? '<small>' . ops_e($a['progress_note']) . '</small>' : '' ?></td><td data-label="PIC"><?= ops_e($a['pic'] ?: '—') ?></td>
     <td data-label="Tenggat"><?= ops_date($a['due_date']) ?><?= $late ? ' <span class="ops-badge bad">Terlambat</span>' : '' ?></td><td data-label="Status"><?= ops_badge($a['status'], $action_labels, $action_tones) ?></td>
     <td data-label="Perbarui"><?php if ($can('notulen.write')): ?><form method="post" class="ops-actions"><?= ops_csrf() ?><input type="hidden" name="act" value="action_update"><input type="hidden" name="action_id" value="<?= (int) $a['id'] ?>">
      <select name="a_status" aria-label="Status tindak lanjut"><?= ops_options($action_labels, $a['status']) ?></select><input name="a_note" value="<?= ops_e($a['progress_note']) ?>" placeholder="Catatan progres" aria-label="Catatan progres" maxlength="255"><button class="ops-btn ghost small" type="submit">Simpan</button></form><?php endif; ?></td></tr>
   <?php endforeach; if (!$actions): ?><tr><td colspan="5" class="ops-empty">Belum ada tindak lanjut.</td></tr><?php endif; ?>
   </tbody></table></div>
   <?php if ($can('notulen.write')): ?>
   <form method="post" class="ops-inline" style="margin-top:10px"><?= ops_csrf() ?><input type="hidden" name="act" value="action_add">
    <?= ops_field('a_title', 'Tindak lanjut baru', 'text', '', array('required' => TRUE, 'maxlength' => 190)) ?><?= ops_field('a_pic', 'PIC', 'text', '', array('maxlength' => 120)) ?><?= ops_field('a_due', 'Tenggat', 'date', '') ?>
    <button class="ops-btn" type="submit">Tambah</button>
   </form>
   <?php endif; ?>
  </section>
 </div>
 <div>
  <?php if ($can('notulen.approve')): ?>
  <section class="ops-card"><h2>Pengesahan</h2>
   <?php if ($item['status'] === 'final'): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="approve"><button class="ops-btn green" type="submit" onclick="return confirm('Sahkan notulen ini? Notulen akan dikunci.')">Sahkan Notulen</button></form>
   <?php elseif ($locked): ?><p class="ops-muted">Notulen terkunci. Membuka revisi akan mengembalikannya ke draf; isi versi ini tetap tersimpan di riwayat.</p><form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="reopen"><button class="ops-btn outline" type="submit" onclick="return confirm('Buka revisi notulen ini?')">Buka Revisi</button></form>
   <?php else: ?><p class="ops-muted">Notulen masih draf. Penyusun perlu mengajukannya untuk pengesahan.</p><?php endif; ?>
  </section>
  <?php endif; ?>
  <section class="ops-card"><h2>Riwayat versi</h2>
   <ul class="ops-timeline"><li><strong>Versi <?= (int) $item['version'] ?> (saat ini)</strong><small><?= ops_date($item['updated_at'] ?: $item['created_at'], TRUE) ?></small></li>
   <?php foreach ($versions as $ver): ?><li><strong>Versi <?= (int) $ver['version'] ?></strong><small>Diganti oleh <?= ops_e($ver['username'] ?: '-') ?> · <?= ops_date($ver['created_at'], TRUE) ?></small></li><?php endforeach; ?></ul>
  </section>
 </div>
</div>
</div>
