<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Kontak Penting', '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('phone') ?></span><div><h1>Kontak Penting</h1><p>Nomor layanan di sekitar lingkungan RT. Ketuk nomor untuk langsung menelepon dari ponsel.</p></div></section>
 <div class="ops-note red"><strong>Darurat nasional:</strong> Polisi <a href="tel:110">110</a> · Pemadam Kebakaran <a href="tel:113">113</a> · Ambulans <a href="tel:118">118</a> / <a href="tel:119">119</a></div>
 <?php $groups = array(); foreach ($items as $c) $groups[$c['is_emergency'] ? 'darurat' : $c['category']][] = $c; ?>
 <?php foreach ($groups as $cat => $rows): ?>
  <section class="cdx-card">
   <header class="cdx-head"><span class="<?= $cat === 'darurat' ? 'red' : 'blue' ?>"><?= official_svg($cat === 'darurat' ? 'alert' : 'phone') ?></span><div><h2><?= html_escape($categories[$cat] ?? ucfirst($cat)) ?></h2></div></header>
   <div class="cpo-contacts">
    <?php foreach ($rows as $c): ?><article class="cpo-contact<?= $c['is_emergency'] ? ' emergency' : '' ?>"><strong><?= html_escape($c['name']) ?></strong><?php if ($c['service_hours']): ?><small><?= html_escape($c['service_hours']) ?></small><?php endif; ?><?php if ($c['address']): ?><small><?= html_escape($c['address']) ?></small><?php endif; ?><?php if ($c['note']): ?><small><?= html_escape($c['note']) ?></small><?php endif; ?>
     <a class="ops-btn small" href="tel:<?= html_escape(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= official_svg('phone') ?> <?= html_escape($c['phone']) ?></a></article><?php endforeach; ?>
   </div>
  </section>
 <?php endforeach; ?>
 <?php if (!$items): ?><section class="cdx-card"><p class="ops-muted">Pengurus belum menambahkan kontak penting.</p></section><?php endif; ?>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
