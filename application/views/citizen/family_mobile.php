<?php
$this->load->helper('official_icons');
$fk_updated=!empty($family_updated_at)&&strtotime($family_updated_at)?date('d M Y, H:i',strtotime($family_updated_at)):'Belum tersedia';
?>
<link rel="stylesheet" href="<?= base_url('assets/family-mobile.css') ?>?v=<?= filemtime(FCPATH.'assets/family-mobile.css') ?>">
<div class="fkm-home">
 <a class="fkm-back" href="<?= site_url('warga') ?>"><?= family_icon('back') ?>Kembali</a>
 <header class="fkm-hero"><div><h1>Data Kartu Keluarga Saya</h1><p>Kelola data Kartu Keluarga dan anggota keluarga Anda dengan mudah dan aman.</p></div><span class="fkm-art" aria-hidden="true"><?= str_replace('kk-paper','fkm-paper',family_document_art()) ?><b><?= family_icon('check') ?></b></span></header>
 <nav class="fkm-actions" aria-label="Kelola Kartu Keluarga"><a href="<?= $manual ?>"><?= family_icon('refresh') ?><span>Perbarui Data</span></a><a class="is-primary" href="<?= $scan ?>"><?= family_icon('camera') ?><span>Scan / Upload KK</span></a></nav>
 <section class="fkm-card" aria-labelledby="fkm-title"><h2 id="fkm-title"><span><?= family_icon('file') ?></span>Kartu Keluarga</h2><div class="fkm-status <?= $family?'is-ready':'' ?>"><span class="fkm-status-icon"><?= family_icon($family?'check':'card') ?></span><div><strong><?= $family?'Data Tersedia':'Data Belum Tersedia' ?></strong><p><?= $family?'Data KK Anda sudah tersimpan di sistem.':'Daftarkan KK melalui scan atau isi manual.' ?></p></div><?php if($family): ?><button type="button" data-fkm-target="informasi"><?= family_icon('eye') ?>Lihat KK</button><?php endif; ?></div>
 <div class="fkm-meta"><div><span class="fkm-meta-icon"><?= family_icon('people') ?></span><div><small>Anggota Keluarga</small><strong><?= $family?count($family['anggota']):0 ?> Orang</strong></div></div><div><span class="fkm-meta-icon"><?= family_icon('calendar') ?></span><div><small>Terakhir Diperbarui</small><strong><?= html_escape($fk_updated) ?></strong></div></div></div>
 <a class="fkm-primary" href="<?= $family?$manual:$scan ?>"><?= family_icon($family?'edit':'plus') ?><span><?= $family?'Ajukan Pembaruan KK':'Daftarkan Kartu Keluarga' ?></span></a></section>
 <nav class="fkm-shortcuts" aria-label="Akses data keluarga"><button type="button" data-fkm-target="anggota"><?= family_icon('people') ?><span>Daftar<br>Anggota KK</span><b aria-hidden="true">&rsaquo;</b></button><button type="button" data-fkm-target="riwayat"><?= family_icon('file') ?><span>Riwayat<br>Pengajuan KK</span><b aria-hidden="true">&rsaquo;</b></button><a href="<?= $manual.'#anggota' ?>"><?= family_icon('plus') ?><span>Tambah<br>Anggota</span><b aria-hidden="true">&rsaquo;</b></a></nav>
 <div class="fkm-bottom-art" aria-hidden="true"><img src="<?= base_url('assets/img/dashboard/footer-banner-v2.jpg') ?>" alt="" loading="lazy" decoding="async"></div>
</div>
<script src="<?= base_url('assets/family-mobile.js') ?>?v=<?= filemtime(FCPATH.'assets/family-mobile.js') ?>" defer></script>
