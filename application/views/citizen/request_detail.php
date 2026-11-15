<?php
$title='Detail Pengajuan | '.($settings['site_name']??'Sukamulya');
$this->load->view('site/header',compact('title','settings','menu'));
$statusLabels=array('draft'=>'Draf','diajukan'=>'Diajukan','diproses'=>'Dalam Proses','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai');
?>
<main class="letter-request-detail-page"><div class="wrap">
<div class="request-detail-top"><div><span class="portal-eyebrow">LAYANAN WARGA</span><h1><?= html_escape($item['subject']) ?></h1><p>Pengajuan #<?= (int)$item['id'] ?></p></div><a href="<?= site_url('warga/riwayat') ?>">&larr; Riwayat Pengajuan</a></div>
<div class="request-detail-toolbar"><span>Status pengajuan: <strong class="citizen-status <?= html_escape($item['status']) ?>"><?= html_escape($statusLabels[$item['status']]??ucfirst($item['status'])) ?></strong></span><?php if($item['status']==='disetujui'&&$item['letter_number']): ?><a class="portal-button" href="<?= site_url('warga/surat/'.$item['id']) ?>" target="_blank" rel="noopener">Lihat / Cetak Surat &rarr;</a><?php endif; ?></div>
<section class="request-document request-document-exact" aria-label="Pratinjau surat">
<iframe class="request-letter-frame" title="Pratinjau <?= html_escape($item['subject']) ?>" src="<?= site_url('warga/surat/'.$item['id']) ?>?preview=1" onload="this.style.height=Math.max(780,this.contentDocument.documentElement.scrollHeight+32)+'px'"></iframe>
</section>
<?php if($documents||$item['attachment']): ?><section class="request-detail-extra"><h2>Dokumen Pendukung</h2><div class="request-document-links"><?php foreach($documents as $document): ?><a href="<?= site_url('warga/dokumen/'.$document['id']) ?>" target="_blank" rel="noopener"><?= html_escape($document['label']) ?> &nearr;</a><?php endforeach; ?><?php if($item['attachment']): ?><a href="<?= site_url('warga/lampiran/'.$item['id']) ?>" target="_blank" rel="noopener">Lampiran tambahan &nearr;</a><?php endif; ?></div></section><?php endif; ?>
<?php if($item['admin_note']): ?><section class="request-detail-extra"><h2>Catatan Pengurus</h2><p><?= nl2br(html_escape($item['admin_note'])) ?></p></section><?php endif; ?>
</div></main>
<?php $this->load->view('site/footer',compact('settings')); ?>
