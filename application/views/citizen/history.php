<?php
$title='Riwayat Pengajuan | '.($settings['site_name']??'Sukamulya');
$citizen_letter_layout=TRUE; $citizen_shell=TRUE; $shell_active='letters';
$this->load->view('site/header',compact('title','settings','menu','citizen_letter_layout','citizen_shell','shell_active'));
$this->load->view('citizen/sidebar',compact('settings'));
$labels=array('draft'=>'Draf','diajukan'=>'Diajukan','diproses'=>'Diproses','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai');
$tones=array('draft'=>'draft','diajukan'=>'pending','diproses'=>'pending','disetujui'=>'verified','selesai'=>'verified','ditolak'=>'unpaid');
$process=0;$approved=0;$rejected=0;
foreach($requests as $item){if(in_array($item['status'],array('diajukan','diproses'),TRUE))$process++;elseif(in_array($item['status'],array('disetujui','selesai'),TRUE))$approved++;elseif($item['status']==='ditolak')$rejected++;}
$csrf='<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'">';
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-dues-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-pages-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-pages-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="letter-page cdx-page crd-page crd-history"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar',array('crumbs'=>array(array('Beranda','warga'),array('Layanan Warga','warga/layanan'),array('Riwayat Pengajuan','')))); ?>
 <section class="cdx-hero cpx-warm"><img src="<?= base_url('assets/mockup/citizen-history-hero.png') ?>" alt=""><span class="cdx-hero-icon"><?= official_svg('clock') ?></span><div><h1>Riwayat <b>Pengajuan</b></h1><p>Pantau status dan kelola pengajuan surat Anda dengan mudah.</p></div></section>
 <?php if($this->session->flashdata('notice')): ?><div class="portal-success cdx-flash"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
 <section class="cdx-metrics four" aria-label="Ringkasan pengajuan">
  <article class="blue"><span class="cdx-icon"><?= official_svg('doc') ?></span><div><small>Total Pengajuan</small><strong><?= count($requests) ?></strong><em>Semua pengajuan</em></div></article>
  <article class="orange"><span class="cdx-icon"><?= official_svg('clock') ?></span><div><small>Dalam Proses</small><strong><?= $process ?></strong><em>Sedang diproses</em></div></article>
  <article class="green"><span class="cdx-icon"><?= official_svg('check-circle') ?></span><div><small>Disetujui</small><strong><?= $approved ?></strong><em>Pengajuan disetujui</em></div></article>
  <article class="red"><span class="cdx-icon"><?= official_svg('close') ?></span><div><small>Ditolak</small><strong><?= $rejected ?></strong><em>Pengajuan ditolak</em></div></article>
 </section>
 <section class="cdx-card cpx-filters" data-table-filter="cpx-history">
  <label class="cpx-search"><?= official_svg('search') ?><input data-filter="search" type="search" placeholder="Cari pengajuan surat..." aria-label="Cari pengajuan surat"></label>
  <select data-filter="jenis" aria-label="Filter jenis surat"><option value="">Semua Jenis</option><?php foreach($catalog as $key=>$letter): ?><option value="<?= html_escape($key) ?>"><?= html_escape($letter[0]) ?></option><?php endforeach; ?></select>
  <select data-filter="status" aria-label="Filter status"><option value="">Semua Status</option><?php foreach($labels as $key=>$label): ?><option value="<?= $key ?>"><?= $label ?></option><?php endforeach; ?></select>
  <div class="cpx-range"><?= official_svg('calendar') ?><input data-filter="from" type="date" aria-label="Dari tanggal"><span>–</span><input data-filter="to" type="date" aria-label="Sampai tanggal"></div>
  <a class="cdx-report crd-btn-solid" href="<?= site_url('warga/layanan') ?>"><?= official_svg('plus') ?>Buat Pengajuan Baru</a>
 </section>
 <section class="cdx-card">
  <header class="cdx-head"><span class="red"><?= official_svg('doc') ?></span><div><h2>Daftar Riwayat Pengajuan</h2><p>Berikut adalah daftar pengajuan surat yang pernah Anda ajukan.</p></div></header>
  <div class="cdx-scroll"><table class="cdx-table"><thead><tr><th>No</th><th>Tanggal</th><th>Jenis Surat</th><th>Keperluan</th><th>Status</th><th>Bukti / Catatan</th><th>Aksi</th></tr></thead><tbody id="cpx-history">
  <?php foreach($requests as $n=>$item): $name=$catalog[$item['type']][0]??$item['subject']; ?>
   <tr data-row data-search="<?= html_escape(mb_strtolower($name.' '.$item['purpose'])) ?>" data-jenis="<?= html_escape(mb_strtolower($item['type'])) ?>" data-status="<?= html_escape($item['status']) ?>" data-date="<?= date('Y-m-d',strtotime($item['created_at'])) ?>"><td><?= $n+1 ?></td><td><?= cfx_date($item['created_at']) ?></td><td><strong class="cpx-strong"><?= html_escape($name) ?></strong></td><td><?= html_escape($item['purpose']?:'—') ?></td><td><span class="cdx-badge <?= $tones[$item['status']]??'pending' ?>"><?= $labels[$item['status']]??html_escape(ucfirst($item['status'])) ?></span></td><td class="cdx-note"><?php if($item['status']==='disetujui'&&$item['letter_number']): ?><a href="<?= site_url('warga/surat/'.(int)$item['id']) ?>" target="_blank" rel="noopener">Lihat Surat</a><?php endif; ?><?php if($item['admin_note']): ?><small><?= html_escape($item['admin_note']) ?></small><?php elseif(!($item['status']==='disetujui'&&$item['letter_number'])): ?>—<?php endif; ?></td><td><span class="cpx-actions"><a class="cpx-detail" href="<?= site_url($item['status']==='draft'?'warga/draft/'.(int)$item['id']:'warga/pengajuan/'.(int)$item['id']) ?>"><?= $item['status']==='draft'?'Lanjutkan':'Detail' ?></a><form method="post" action="<?= site_url('warga/pengajuan/'.(int)$item['id'].'/hapus') ?>" onsubmit="return confirm('Hapus pengajuan ini beserta dokumen terkait? Tindakan ini tidak dapat dibatalkan.')"><?= $csrf ?><button type="submit" class="cpx-delete" aria-label="Hapus pengajuan <?= html_escape($name) ?>">Hapus</button></form></span></td></tr>
  <?php endforeach; ?>
   <tr data-empty-row<?= $requests?' hidden':'' ?>><td colspan="7" class="cpx-empty"><?php if($requests): ?><p>Tidak ada pengajuan yang cocok dengan filter.</p><?php else: ?><img src="<?= base_url('assets/mockup/citizen-history-empty.png') ?>" alt=""><strong>Belum ada pengajuan surat.</strong><p>Ajukan surat yang Anda butuhkan melalui menu Pengajuan Surat.</p><a class="cdx-report" href="<?= site_url('warga/layanan') ?>"><?= official_svg('send') ?>Buat Pengajuan Pertama</a><?php endif; ?></td></tr>
  </tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
<script src="<?= base_url('assets/citizen-table-filter.js') ?>?v=<?= filemtime(FCPATH.'assets/citizen-table-filter.js') ?>"></script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
