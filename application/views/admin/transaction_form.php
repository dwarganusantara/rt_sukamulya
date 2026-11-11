<?php
$item=$item??array();$reversal=$reversal??NULL;$income=$type==='masuk';
$title=$income?'Tambah Pemasukan':'Tambah Pengeluaran';
$formTitle=$income?'Form Pemasukan':'Form Pengeluaran';
$descriptionHint=$income?'Contoh: Iuran keamanan bulan Oktober 2026...':'Contoh: Pembelian alat kebersihan lingkungan...';
$categories=$income?array('Iuran keamanan','Iuran kebersihan','Iuran sosial','Donasi','Lainnya'):array('Kebersihan','Keamanan','Acara','Perbaikan','Sosial','Administrasi','Lainnya');
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-transaction-form-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-transaction-form-v2.css') ?>">
<div class="tx-form-v2 <?= $income?'income':'expense' ?>">
 <nav class="tx-breadcrumb"><a href="<?= site_url('admin') ?>">Beranda</a><span>›</span><a href="<?= site_url('admin/transactions') ?>">Kas RT</a><span>›</span><strong><?= $title ?></strong></nav>
 <header class="tx-page-head"><div><h1><?= $title ?></h1><p>Catat setiap <?= $income?'pemasukan':'pengeluaran' ?> kas agar laporan keuangan RT tetap rapi dan transparan.</p></div><a href="<?= site_url('admin/transactions') ?>">← &nbsp; Kembali</a></header>
 <?php if($error): ?><div class="error tx-error"><?= html_escape($error) ?></div><?php endif; ?>
 <?php if($reversal): ?><div class="notice tx-notice">Transaksi pembalik untuk #<?= (int)$reversal['id'] ?>. Jumlah harus sama dan jelaskan alasan koreksi pada uraian.</div><?php endif; ?>
 <div class="tx-layout-v2">
  <form class="tx-card-v2" method="post" enctype="multipart/form-data">
   <header><span><?= $income?'＋':'−' ?></span><div><h2><?= $formTitle ?></h2><p>Lengkapi data <?= $income?'pemasukan':'pengeluaran' ?> kas RT sesuai kebutuhan.</p></div></header>
   <div class="tx-grid-v2"><label><strong>Tanggal transaksi</strong><span class="tx-control-v2"><i>▦</i><input type="date" name="tx_date" required value="<?= html_escape($this->input->post('tx_date')?:($item['tx_date']??date('Y-m-d'))) ?>"></span></label><label><strong>Kategori</strong><span class="tx-control-v2"><i>◇</i><select name="category" required><?php foreach($categories as $category): ?><option value="<?= html_escape($category) ?>" <?= ($this->input->post('category')?:($item['category']??''))===$category?'selected':'' ?>><?= html_escape($category) ?></option><?php endforeach; ?></select></span></label></div>
   <label><strong>Uraian</strong><small>Uraian <?= $income?'pemasukan':'pengeluaran' ?> dicatat per periode, tanpa nama warga.</small><span class="tx-control-v2 textarea"><textarea name="description" required maxlength="255" rows="3" placeholder="<?= $descriptionHint ?>"><?= html_escape($this->input->post('description')?:($item['description']??($reversal?'Pembalik transaksi #'.$reversal['id'].': ':''))) ?></textarea></span></label>
   <div class="tx-grid-v2"><label><strong>Jumlah (Rp)</strong><span class="tx-control-v2"><i>Rp</i><input type="number" name="amount" min="1" step="1" required placeholder="Contoh: <?= $income?'500000':'100000' ?>" value="<?= html_escape($this->input->post('amount')?:($item['amount']??($reversal?$reversal['amount']:''))) ?>" <?= $reversal?'readonly':'' ?>></span></label><label><strong><?= $income?'Sumber umum':'Penerima / toko' ?> <em>(Opsional)</em></strong><span class="tx-control-v2"><i><?= $income?'♜':'▣' ?></i><input name="counterparty" placeholder="<?= $income?'Contoh: Iuran warga, donatur, dll':'Contoh: Toko Makmur' ?>" value="<?= html_escape($this->input->post('counterparty')?:($item['counterparty']??'')) ?>"></span></label></div>
   <label><strong>Bukti / bon <em>(Opsional)</em></strong><small>JPG, PNG, WebP, atau PDF; maksimal 5 MB.</small><span class="tx-upload-v2"><i>⌕</i><span>Pilih file bukti pembayaran</span><input type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf"></span></label>
   <?php if($item && !empty($item['proof'])): ?><a class="tx-current-proof" href="<?= site_url('bukti/'.$item['id']) ?>" target="_blank">Lihat bukti saat ini ↗</a><?php endif; ?>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><button class="tx-save-v2" type="submit">▣ &nbsp; Simpan Transaksi</button>
  </form>
  <aside class="tx-side-v2"><div class="tx-art-v2" aria-hidden="true"><i class="leaf left"></i><i class="leaf right"></i><i class="board"><b></b><b></b><b></b><em>Rp</em></i><i class="coin one"></i><i class="coin two"></i><i class="calculator"><b></b><b></b><b></b><b></b><b></b><b></b></i></div><section class="tx-tips-v2"><h2>◉ &nbsp; Tips <?= $income?'Pemasukan':'Pengisian' ?></h2><ul><li>Pilih tanggal transaksi dengan benar.</li><li>Gunakan kategori yang sesuai.</li><li>Isi uraian secara jelas dan singkat.</li><li>Lampirkan bukti pembayaran jika ada.</li><li>Pastikan nominal yang diinput sesuai.</li></ul></section></aside>
 </div>
</div>
