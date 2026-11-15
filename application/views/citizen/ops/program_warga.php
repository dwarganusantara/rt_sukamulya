<main class="letter-page cdx-page crd-page crd-ops cwp-page"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Program Kerja', '')))); ?>
 <section class="cdx-hero cpx-warm cwp-hero"><span class="cdx-hero-icon"><?= official_svg('briefcase') ?></span><div><h1>Program Kerja</h1><p>Pantau rencana, pelaksanaan, dan perkembangan program kerja pengurus lingkungan.</p></div></section>
 <section class="cwp-stats" aria-label="Ringkasan program kerja">
  <div><span class="active"><?= official_svg('clock') ?></span><strong><?= (int) $counts['aktif'] ?></strong><small>Sedang berjalan</small></div>
  <div><span class="paused"><?= official_svg('clock') ?></span><strong><?= (int) $counts['ditunda'] ?></strong><small>Ditunda</small></div>
  <div><span class="done"><?= official_svg('check-circle') ?></span><strong><?= (int) $counts['selesai'] ?></strong><small>Selesai</small></div>
 </section>
 <section class="cwp-list" aria-label="Daftar program kerja">
 <?php foreach ($items as $item): $progress = max(0, min(100, (int) $item['progress'])); ?>
  <article class="cdx-card cwp-card">
   <header><span class="cwp-icon" aria-hidden="true"><?= official_svg('briefcase') ?></span><div><small><?= html_escape($categories[$item['category']] ?? $item['category']) ?></small><h2><?= html_escape($item['title']) ?></h2></div><?= ops_badge($item['status'], $labels, $tones) ?></header>
   <?php if ($item['description']): ?><p class="cwp-desc"><?= nl2br(html_escape($item['description'])) ?></p><?php endif; ?>
   <dl class="cwp-meta"><div><dt>Periode</dt><dd><?= ops_date($item['period_start']) ?> – <?= ops_date($item['period_end']) ?></dd></div><div><dt>Penanggung Jawab</dt><dd><?= html_escape($item['pic'] ?: 'Pengurus RT') ?></dd></div><?php if ($item['target']): ?><div><dt>Sasaran</dt><dd><?= html_escape($item['target']) ?></dd></div><?php endif; ?></dl>
   <div class="cwp-progress"><div><strong>Progres pelaksanaan</strong><b><?= $progress ?>%</b></div><span><i style="width:<?= $progress ?>%"></i></span></div>
   <?php if ((float) $item['budget'] > 0): ?><footer><span>Anggaran <strong><?= ops_money($item['budget']) ?></strong></span><span>Realisasi <strong><?= ops_money($item['realization']) ?></strong></span></footer><?php endif; ?>
  </article>
 <?php endforeach; if (!$items): ?>
  <div class="cdx-card cwp-empty"><?= official_svg('briefcase') ?><h2>Belum ada program kerja</h2><p>Program yang sudah aktif akan ditampilkan di halaman ini.</p></div>
 <?php endif; ?>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
