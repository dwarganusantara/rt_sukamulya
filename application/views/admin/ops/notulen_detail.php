<?php $locked = $item['status'] === 'disahkan'; ?>
<div class="ops ops-notulen-detail">
<nav class="ops-crumb"><a href="<?= site_url('admin/notulen') ?>">Notulen</a><span>›</span><strong><?= ops_e($item['title']) ?></strong></nav>
<?= ops_hero('Notulen · versi ' . (int) $item['version'], $item['title'], ops_date($item['note_date']) . ($item['location'] ? ' · ' . $item['location'] : '') . ' · ' . (int) $item['participant_count'] . ' peserta', $can('notulen.write') ? array(array('admin/notulen/' . (int) $item['id'] . '/ubah', 'Ubah', 'ghost')) : array(), 'lines') ?>
<?= ops_flash() ?>
<div class="ops-grid notulen-layout">
 <div class="notulen-main">
  <section class="ops-card notulen-content"><header><h2><span class="notulen-heading-icon" aria-hidden="true"><?= official_svg('doc') ?></span>Isi notulen</h2><?= ops_badge($item['status'], $labels, $tones) ?></header>
   <dl class="ops-dl">
    <dt>Agenda</dt><dd><?= $item['agenda_id'] ? '<a href="' . site_url('admin/post/agenda/' . (int) $item['agenda_id']) . '">' . ops_e($item['agenda_title']) . '</a>' : '—' ?></dd>
    <dt>Program</dt><dd><?= $item['program_id'] ? '<a href="' . site_url('admin/program/' . (int) $item['program_id']) . '">' . ops_e($item['program_title']) . '</a>' : '—' ?></dd>
    <dt>Peserta</dt><dd><?= ops_e($item['participants'] ?: '—') ?></dd>
    <dt>Ringkasan</dt><dd><?= ops_e($item['summary'] ?: '—') ?></dd>
    <dt>Keputusan</dt><dd><?= ops_e($item['decisions'] ?: '—') ?></dd>
    <dt>Kendala</dt><dd><?= ops_e($item['obstacles'] ?: '—') ?></dd>
    <dt>Dibuat</dt><dd><?= ops_e($item['creator'] ?: '-') ?> · <?= ops_date($item['created_at'], TRUE) ?></dd>
    <?php if ($item['approved_at']): ?><dt>Diterbitkan</dt><dd><?= ops_e($item['approver']) ?> · <?= ops_date($item['approved_at'], TRUE) ?></dd><?php endif; ?>
   </dl>
  </section>
  <?php /* REVISI: bagian Tindak lanjut dihapus — notulen langsung selesai setelah disimpan (data lama tetap di database). */ ?>
 </div>
 <div class="notulen-side">
  <?php /* REVISI: tanpa pengesahan — draf / data lama "Menunggu Pengesahan" cukup ditekan "Terbitkan". */ ?>
  <?php if (!$locked && $can('notulen.write')): ?>
  <section class="ops-card notulen-publish"><h2><span class="notulen-heading-icon" aria-hidden="true"><?= official_svg('alert') ?></span>Belum terbit</h2>
   <p class="ops-muted"><?= $item['status'] === 'final' ? 'Notulen ini dibuat dengan alur lama (menunggu pengesahan).' : 'Notulen masih draf.' ?> Terbitkan agar berstatus Terbit.</p>
   <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="approve"><button class="ops-btn green" type="submit">Terbitkan Notulen</button></form>
  </section>
  <?php endif; ?>
  <section class="ops-card notulen-history"><h2><span class="notulen-heading-icon" aria-hidden="true"><?= official_svg('clock') ?></span>Riwayat versi</h2>
   <ul class="ops-timeline"><li class="is-current"><strong>Versi <?= (int) $item['version'] ?> (saat ini)</strong><small><?= ops_date($item['updated_at'] ?: $item['created_at'], TRUE) ?></small></li>
   <?php foreach ($versions as $ver): ?><li><strong>Versi <?= (int) $ver['version'] ?></strong><small>Diganti oleh <?= ops_e($ver['username'] ?: '-') ?> · <?= ops_date($ver['created_at'], TRUE) ?></small></li><?php endforeach; ?></ul>
  </section>
 </div>
</div>
</div>
