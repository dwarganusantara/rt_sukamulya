<?php
$title='Iuran Saya | '.($settings['site_name']??'Sukamulya');
$citizen_letter_layout=TRUE; $citizen_shell=TRUE; $shell_active='dues';
$this->load->view('site/header',compact('title','settings','menu','citizen_letter_layout','citizen_shell','shell_active'));
$this->load->view('citizen/sidebar',compact('settings'));
$total=0;$paid=0;$unpaid_bills=0;$paid_bills=0;
foreach($dues as $due){$total+=(float)$due['amount_due'];$paid+=(float)$due['amount_paid'];if((float)$due['amount_paid']<(float)$due['amount_due'])$unpaid_bills++;else $paid_bills++;}
$format=function($n){return 'Rp '.number_format($n,0,',','.');};
$typeLabel=array('rutin'=>'Rutin','non_rutin'=>'Nonrutin','sosial'=>'Sosial');
$months=array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
$periodLabel=function($value)use($months){$p=explode('-',(string)$value);return count($p)===2&&isset($months[(int)$p[1]])?$months[(int)$p[1]].' '.$p[0]:$value;};
// Pembayaran diurutkan terbaru lebih dulu, jadi elemen pertama per tagihan adalah yang terakhir dikirim.
$by_due=array();foreach($payments as $payment)$by_due[(int)$payment['due_id']][]=$payment;
$statuses=array('lunas'=>array('Lunas','verified'),'menunggu'=>array('Menunggu verifikasi','pending'),'dikembalikan'=>array('Dikembalikan','unpaid'),'sebagian'=>array('Sebagian','pending'),'belum'=>array('Belum dibayar','unpaid'));
$payLabel=array('pending'=>'Menunggu','verified'=>'Terverifikasi','returned'=>'Dikembalikan');
$years=array();foreach($dues as $due)$years[substr($due['period'],0,4)]=TRUE;krsort($years);
$csrf='<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'">';
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-dues-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-pages-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-pages-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="letter-page cdx-page crd-page crd-dues"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar',array('crumbs'=>array(array('Beranda','warga'),array('Layanan Warga','warga/layanan'),array('Iuran Saya','')))); ?>
 <section class="cdx-hero cpx-warm"><img src="<?= base_url('assets/mockup/citizen-dues-hero.png') ?>" alt=""><span class="cdx-hero-icon"><?= official_svg('wallet') ?></span><div><h1>Iuran <b>Saya</b></h1><p>Lihat tagihan, kirim pembayaran, dan pantau status verifikasi dengan mudah.</p></div></section>
 <?php if($this->session->flashdata('notice')): ?><div class="portal-success cdx-flash"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
 <?php if($this->session->flashdata('due_error')): ?><div class="error cdx-flash" role="alert"><?= html_escape($this->session->flashdata('due_error')) ?></div><?php endif; ?>
 <section class="cdx-metrics" aria-label="Ringkasan iuran">
  <article class="red"><span class="cdx-icon"><?= official_svg('doc') ?></span><div><small>Total Tagihan</small><strong><?= $format($total) ?></strong><em><?= count($dues) ?> tagihan tercatat</em></div><i class="cdx-mark"><?= official_svg('chart') ?></i></article>
  <article class="green"><span class="cdx-icon"><?= official_svg('check-circle') ?></span><div><small>Sudah Dibayar</small><strong><?= $format($paid) ?></strong><em><?= $paid_bills ?> tagihan lunas</em></div><i class="cdx-mark"><?= official_svg('chart') ?></i></article>
  <article class="orange"><span class="cdx-icon"><?= official_svg('alert') ?></span><div><small>Sisa Tagihan</small><strong><?= $format(max(0,$total-$paid)) ?></strong><em><?= (int)$unpaid_bills ?> tagihan belum dibayar</em></div><i class="cdx-mark"><?= official_svg('chart') ?></i></article>
 </section>
 <section class="cdx-card" id="tagihan">
  <header class="cdx-head"><span class="red"><?= official_svg('doc') ?></span><div><h2>Tagihan Saya</h2><p>Pilih metode pembayaran. Bukti transfer akan diperiksa pengurus sebelum tagihan dinyatakan lunas.</p></div><a class="cdx-report" href="<?= site_url('keuangan') ?>"><?= official_svg('chart') ?>Laporan Kas RT</a></header>
  <div class="cpx-filters plain" data-table-filter="cpx-dues">
   <label class="cpx-search"><?= official_svg('search') ?><input data-filter="search" type="search" placeholder="Cari tagihan..." aria-label="Cari tagihan"></label>
   <select data-filter="status" aria-label="Filter status"><option value="">Semua Status</option><?php foreach($statuses as $key=>$status): ?><option value="<?= $key ?>"><?= $status[0] ?></option><?php endforeach; ?></select>
   <select data-filter="year" aria-label="Filter tahun"><option value="">Semua Tahun</option><?php foreach(array_keys($years) as $year): ?><option value="<?= (int)$year ?>"><?= (int)$year ?></option><?php endforeach; ?></select>
   <div class="cpx-range"><?= official_svg('calendar') ?><input data-filter="from" type="date" aria-label="Dari tanggal"><span>–</span><input data-filter="to" type="date" aria-label="Sampai tanggal"></div>
  </div>
  <div class="cdx-scroll"><table class="cdx-table"><thead><tr><th>No</th><th>Tanggal</th><th>Periode</th><th>Jenis Iuran</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Bukti / Catatan</th><th>Aksi</th></tr></thead><tbody id="cpx-dues">
  <?php foreach($dues as $n=>$due):
   $id=(int)$due['id'];$history=$by_due[$id]??array();$last=$history?$history[0]:NULL;$remaining=max(0,(float)$due['amount_due']-(float)$due['amount_paid']);
   $key=$remaining<=0?'lunas':(($last&&$last['status']==='pending')?'menunggu':(($last&&$last['status']==='returned')?'dikembalikan':((float)$due['amount_paid']>0?'sebagian':'belum')));
   $can_pay=$remaining>0&&$key!=='menunggu';
   $date=$last?$last['created_at']:$due['updated_at']; ?>
   <tr data-row data-search="<?= html_escape(mb_strtolower($due['category'].' '.$periodLabel($due['period']).' '.($typeLabel[$due['due_type']]??''))) ?>" data-status="<?= $key ?>" data-year="<?= html_escape(substr($due['period'],0,4)) ?>" data-date="<?= date('Y-m-d',strtotime($date)) ?>"><td><?= $n+1 ?></td><td><?= cfx_date($date) ?></td><td><?= html_escape($periodLabel($due['period'])) ?></td><td><strong class="cpx-strong"><?= html_escape($due['category']) ?></strong><small><?= $typeLabel[$due['due_type']]??'Rutin' ?></small></td><td><?= $format($due['amount_due']) ?><small>Sisa <?= $format($remaining) ?></small></td><td><?= $last?($last['method']==='tunai'?'Tunai':'Transfer'):'—' ?></td><td><span class="cdx-badge <?= $statuses[$key][1] ?>"><?= $statuses[$key][0] ?></span></td><td class="cdx-note"><?php if($last&&$last['proof']): ?><a href="<?= site_url('warga/bukti-iuran/'.(int)$last['id']) ?>" target="_blank" rel="noopener">Lihat bukti</a><?php endif; ?><?php if($last&&$last['review_note']): ?><small><?= html_escape($last['review_note']) ?></small><?php elseif(!($last&&$last['proof'])): ?>—<?php endif; ?></td><td><button type="button" class="cpx-detail<?= $can_pay?' primary':'' ?>" data-toggle="detail-<?= $id ?>" aria-expanded="false" aria-controls="detail-<?= $id ?>"><?= $key==='dikembalikan'?'Bayar Ulang':($can_pay?'Bayar':'Detail') ?></button></td></tr>
   <tr data-detail id="detail-<?= $id ?>" hidden><td colspan="9"><div class="cpx-panel">
    <div class="cpx-paylog"><h3>Riwayat Pembayaran</h3><?php foreach($history as $payment): ?><p><span><?= cfx_date($payment['created_at']) ?> · <?= $format($payment['amount']) ?> · <?= $payment['method']==='tunai'?'Tunai':'Transfer' ?></span><em class="cdx-badge <?= html_escape($payment['status']) ?>"><?= $payLabel[$payment['status']]??'' ?></em></p><?php endforeach; ?><?php if(!$history): ?><p class="none">Belum ada pembayaran untuk tagihan ini.</p><?php endif; ?><?php if($due['note']): ?><p class="none">Catatan: <?= html_escape($due['note']) ?></p><?php endif; ?></div>
    <?php if($can_pay): ?><form class="cdx-pay" method="post" enctype="multipart/form-data" action="<?= site_url('warga/iuran/'.$id.'/bayar') ?>"><?= $csrf ?><h3>Kirim Pembayaran</h3><div class="cdx-pay-row"><label>Jumlah bayar (Rp)<input type="number" name="amount" min="1" max="<?= (int)$remaining ?>" value="<?= (int)$remaining ?>" required></label><label>Metode<select name="method" class="cdx-method"><option value="transfer">Transfer</option><option value="tunai">Tunai ke pengurus</option></select></label></div><label class="cdx-proof">Bukti transfer<input type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf" required><small>JPG, PNG, WebP, atau PDF maksimal 5 MB.</small></label><button type="submit"><?= official_svg('send') ?>Kirim Pembayaran</button></form>
    <?php elseif($key==='menunggu'): ?><p class="cdx-wait"><?= official_svg('hourglass') ?>Pembayaran Anda sedang diperiksa pengurus.</p>
    <?php else: ?><p class="cdx-done"><?= official_svg('check-circle') ?>Tagihan ini sudah lunas.</p><?php endif; ?>
   </div></td></tr>
  <?php endforeach; ?>
   <tr data-empty-row<?= $dues?' hidden':'' ?>><td colspan="9" class="cpx-empty"><?php if($dues): ?><p>Tidak ada tagihan yang cocok dengan filter.</p><?php else: ?><img src="<?= base_url('assets/mockup/citizen-dues-empty.png') ?>" alt=""><strong>Belum ada tagihan untuk akun Anda.</strong><p>Tagihan iuran akan muncul di sini setelah dibuat oleh pengurus RT.</p><a class="cdx-report" href="<?= site_url('keuangan') ?>"><?= official_svg('chart') ?>Lihat Laporan Kas</a><?php endif; ?></td></tr>
  </tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
<script src="<?= base_url('assets/citizen-table-filter.js') ?>?v=<?= filemtime(FCPATH.'assets/citizen-table-filter.js') ?>"></script>
<script>
(function(){
 document.querySelectorAll('.cdx-method').forEach(function(select){
  function sync(){var proof=select.closest('form').querySelector('.cdx-proof');proof.hidden=select.value==='tunai';proof.querySelector('input').required=!proof.hidden}
  select.addEventListener('change',sync);sync();
 });
 document.querySelectorAll('.cdx-pay').forEach(function(form){form.addEventListener('submit',function(){var b=form.querySelector('button[type=submit]');b.disabled=true;b.lastChild.textContent='Mengirim...'})});
 document.querySelectorAll('[data-toggle]').forEach(function(button){
  button.addEventListener('click',function(){var row=document.getElementById(button.dataset.toggle);row.hidden=!row.hidden;button.setAttribute('aria-expanded',row.hidden?'false':'true')});
 });
})();
</script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
