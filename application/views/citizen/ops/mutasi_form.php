<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Mutasi Data', 'warga/mutasi'), array('Ajukan', '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('pencil') ?></span><div><h1><?= $type ? html_escape($types[$type]) : 'Ajukan Mutasi' ?></h1><p>KK <?= html_escape(rt_mask_id($family['nomor_kk'])) ?> · <?= html_escape($family['fields']['alamat'] ?? '') ?></p></div></section>
 <?php $this->load->view('citizen/ops/_flash', array('error' => $error)); ?>
 <?php if ($existing && $existing['review_note']): ?><p class="ops-note"><strong>Catatan pengurus:</strong> <?= html_escape($existing['review_note']) ?></p><?php endif; ?>
 <?php if (!$type): ?>
  <section class="cdx-card"><div class="cpo-types"><?php foreach ($types as $k => $label): ?><a class="cpo-type" href="<?= site_url('warga/mutasi/ajukan?jenis=' . $k) ?>"><strong><?= html_escape($label) ?></strong></a><?php endforeach; ?></div></section>
 <?php else: ?>
  <section class="cdx-card">
   <form method="post" action="<?= $action ?>"><?= ops_csrf() ?>
    <?php $this->load->view('shared/mutasi_fields', compact('type', 'family', 'values', 'relationships', 'domicile', 'member_edit', 'family_edit')); ?>
    <p class="ops-note blue" style="margin-top:12px">Dengan mengirim, Anda menyatakan data benar dan Anda berwenang mewakili keluarga ini. Siapkan dokumen pendukung bila diminta pengurus.</p>
    <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url('warga/mutasi') ?>">Batal</a><button class="ops-btn" type="submit"><?= $existing ? 'Kirim Perbaikan' : 'Kirim Pengajuan' ?></button></div>
   </form>
  </section>
 <?php endif; ?>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
