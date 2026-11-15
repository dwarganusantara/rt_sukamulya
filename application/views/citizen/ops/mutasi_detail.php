<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Mutasi Data', 'warga/mutasi'), array($code, '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('doc') ?></span><div><h1><?= html_escape($code) ?></h1><p><?= html_escape($types[$item['mutation_type']] ?? '') ?> · diajukan <?= ops_date($item['created_at'], TRUE) ?></p></div></section>
 <?php $this->load->view('citizen/ops/_flash'); ?>
 <section class="cdx-card">
  <header class="cdx-head"><span class="orange"><?= official_svg('list') ?></span><div><h2>Status: <?= ops_badge($item['status'], $labels, $tones) ?></h2><p>Tanggal efektif <?= ops_date($item['effective_date']) ?></p></div></header>
  <?php if ($item['review_note']): ?><p class="ops-note"><strong>Catatan pengurus:</strong> <?= html_escape($item['review_note']) ?></p><?php endif; ?>
  <?php $mask = FALSE; $this->load->view('shared/mutasi_changes', compact('item', 'family', 'mask')); ?>
  <dl class="ops-dl" style="margin-top:12px"><dt>Alasan</dt><dd><?= html_escape($item['reason']) ?></dd></dl>
  <div class="ops-form-actions">
   <?php if ($item['status'] === 'revision_requested'): ?><a class="ops-btn" href="<?= site_url('warga/mutasi/ajukan?ubah=' . (int) $item['id']) ?>">Perbaiki &amp; Kirim Ulang</a><?php endif; ?>
   <?php if (in_array($item['status'], array('submitted', 'revision_requested'), TRUE)): ?>
    <form method="post" action="<?= site_url('warga/mutasi/' . (int) $item['id'] . '/batal') ?>" onsubmit="return confirm('Batalkan pengajuan mutasi ini?')"><?= ops_csrf() ?><button class="ops-btn red outline" type="submit">Batalkan</button></form>
   <?php endif; ?>
   <a class="ops-btn ghost" href="<?= site_url('warga/mutasi') ?>">Kembali</a>
  </div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
