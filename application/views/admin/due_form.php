<?php
$this->load->helper('official_icons');
$css = 'assets/admin-due-form.css';
$title = $item ? 'Edit Iuran Warga' : 'Tambah Iuran Warga';
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<div class="duf">
 <nav class="duf-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>" aria-label="Dashboard"><?= official_svg('home') ?></a><span><?= official_svg('chev-right') ?></span><a href="<?= site_url('admin/dues') ?>">Keuangan</a><span><?= official_svg('chev-right') ?></span><a href="<?= site_url('admin/dues') ?>">Iuran Warga</a><span><?= official_svg('chev-right') ?></span><strong><?= $title ?></strong></nav>

 <section class="duf-hero">
  <span class="duf-hero-ic" aria-hidden="true"><?= official_svg('database') ?></span>
  <h1><?= $item ? '<span>Edit</span> Iuran Warga' : '<span>Tambah</span> Iuran Warga' ?></h1>
  <div class="duf-art" aria-hidden="true"><i class="coin c1"></i><i class="coin c2"></i><i class="coin c3"></i><i class="coin c4"></i><b class="bill"><em></em><em></em><em></em></b></div>
  <a class="duf-back" href="<?= site_url('admin/dues') ?>"><?= official_svg('back') ?>Kembali</a>
 </section>

 <?php if ($error): ?><div class="duf-error" role="alert"><?= html_escape($error) ?></div><?php endif; ?>

 <form class="duf-card" method="post">
  <label class="duf-field duf-wide"><span>Warga</span>
   <select name="citizen_id" required><option value="">Pilih warga</option><?php foreach ($citizens as $citizen): ?><option value="<?= (int) $citizen['id'] ?>" <?= ($item['citizen_id'] ?? 0) == $citizen['id'] ? 'selected' : '' ?>><?= html_escape($citizen['display_name']) ?></option><?php endforeach; ?></select>
  </label>
  <label class="duf-field"><span>Periode</span><input type="month" name="period" required value="<?= html_escape($item['period'] ?? date('Y-m')) ?>"></label>
  <label class="duf-field"><span>Kategori</span><input name="category" required maxlength="100" value="<?= html_escape($item['category'] ?? 'Iuran lingkungan') ?>"></label>
  <label class="duf-field"><span>Tagihan (Rp)</span><input type="number" name="amount_due" min="0" step="1" required value="<?= html_escape(isset($item['amount_due']) ? (string) (0 + $item['amount_due']) : '0') ?>"></label>
  <label class="duf-field"><span>Sudah dibayar (Rp)</span><input type="number" value="<?= html_escape(isset($item['amount_paid']) ? (string) (0 + $item['amount_paid']) : '0') ?>" readonly aria-describedby="duf-paid-help"><small id="duf-paid-help">Pembayaran diperbarui melalui verifikasi di menu Iuran Warga.</small></label>
  <label class="duf-field duf-wide"><span>Catatan internal</span><textarea name="note" rows="2" maxlength="255"><?= html_escape($item['note'] ?? '') ?></textarea></label>
  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
  <div class="duf-actions"><button class="duf-save" type="submit"><?= official_svg('save') ?>Simpan Iuran</button></div>
 </form>
</div>
