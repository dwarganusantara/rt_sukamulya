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
<link rel="stylesheet" href="<?= base_url('assets/mobile-warm.css') ?>?v=<?= filemtime(FCPATH.'assets/mobile-warm.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-dues-mobile.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-mobile.css') ?>">
<?php
/* ---- Data ringkasan versi HP (desain 14 Nov 2026). Status per tagihan sama persis dengan tabel di bawah. ---- */
$idm_short=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$idm_dues=array();$idm_period=array();$idm_pay_target=0;$idm_proof=NULL;
foreach($dues as $due){
 $id=(int)$due['id'];$history=$by_due[$id]??array();$last=$history?$history[0]:NULL;$remaining=max(0,(float)$due['amount_due']-(float)$due['amount_paid']);
 $key=$remaining<=0?'lunas':(($last&&$last['status']==='pending')?'menunggu':(($last&&$last['status']==='returned')?'dikembalikan':((float)$due['amount_paid']>0?'sebagian':'belum')));
 $idm_dues[]=array('id'=>$id,'period'=>$due['period'],'cat'=>$due['category'],'amount'=>(float)$due['amount_due'],'key'=>$key);
 $p=&$idm_period[$due['period']];if(!$p)$p=array('amount'=>0,'paid'=>0,'pending'=>FALSE,'open'=>FALSE);
 $p['amount']+=(float)$due['amount_due'];$p['paid']+=(float)$due['amount_paid'];if($key==='menunggu')$p['pending']=TRUE;if($remaining>0)$p['open']=TRUE;unset($p);
 if(!$idm_pay_target&&$remaining>0&&$key!=='menunggu')$idm_pay_target=$id;
}
foreach($payments as $pay){if(!empty($pay['proof'])){$idm_proof=$pay;break;}}
krsort($idm_period);
$idm_now=date('Y-m');$idm_cur=isset($idm_period[$idm_now])?$idm_now:(key($idm_period)?:$idm_now);
$idm_state=function($p){return !$p?'none':($p['open']?($p['pending']?'pending':'unpaid'):'paid');};
$idm_cur_p=$idm_period[$idm_cur]??NULL;$idm_cur_state=$idm_state($idm_cur_p);
$idm_state_label=array('paid'=>'Sudah Dibayar','unpaid'=>'Belum Dibayar','pending'=>'Menunggu Verifikasi','none'=>'Belum ada tagihan');
$idm_months=array();$t=strtotime($idm_cur.'-01');for($i=5;$i>=0;$i--){$m=date('Y-m',strtotime("-$i month",$t));$idm_months[$m]=$idm_state($idm_period[$m]??NULL);}
$idm_recent=array_slice($idm_period,0,2,TRUE);
$idm_badge=array('paid'=>array('Lunas','paid'),'unpaid'=>array('Belum','unpaid'),'pending'=>array('Menunggu','pending'));
?>
<main class="letter-page cdx-page crd-page crd-dues mw-page">
 <header class="mw-header mw-only"><?php $this->load->view('site/mobile_brand_header',array('settings'=>$settings,'mw_title'=>'Iuran Saya')); ?></header>
 <section class="idm mw-only" aria-label="Iuran saya">
  <div class="idm-hero"><img src="<?= base_url('assets/img/letters/mw-letters-hero.jpg') ?>" alt="" aria-hidden="true" width="941" height="250"><a class="idm-back" href="<?= site_url('warga') ?>"><?= official_svg('back') ?>Kembali</a></div>
  <?php if($this->session->flashdata('notice')): ?><div class="portal-success idm-flash"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
  <?php if($this->session->flashdata('due_error')): ?><div class="error idm-flash" role="alert"><?= html_escape($this->session->flashdata('due_error')) ?></div><?php endif; ?>
  <div class="idm-metrics" aria-label="Ringkasan iuran">
   <div class="m-total"><span><?= official_svg('doc') ?></span><p><small>Total Tagihan</small><b><?= $format($total) ?></b></p></div>
   <div class="m-paid"><span><?= official_svg('check-circle') ?></span><p><small>Sudah Dibayar</small><b><?= $format($paid) ?></b></p></div>
   <div class="m-left"><span><?= official_svg('clock') ?></span><p><small>Sisa Tagihan</small><b><?= $format(max(0,$total-$paid)) ?></b></p></div>
  </div>
  <div class="idm-tabs" role="tablist" aria-label="Tampilan iuran">
   <button type="button" role="tab" aria-selected="true" data-idm-tab="ringkasan"><?= official_svg('chart') ?>Ringkasan</button>
   <button type="button" role="tab" aria-selected="false" data-idm-tab="riwayat"><?= official_svg('list') ?>Riwayat</button>
   <button type="button" role="tab" aria-selected="false" data-idm-tab="tagihan"><?= official_svg('calendar') ?>Tagihan</button>
  </div>
  <?php /* ---- Ringkasan ---- */ ?>
  <div class="idm-panel" data-idm-panel="ringkasan" role="tabpanel">
   <div class="idm-card">
    <div class="idm-month state-<?= $idm_cur_state ?>"><span class="idm-month-ic" aria-hidden="true"><?= official_svg('calendar') ?></span><div class="idm-month-tx"><strong>Iuran Bulan Ini</strong><small><?= html_escape($periodLabel($idm_cur)) ?></small></div><div class="idm-month-val"><b><?= $idm_cur_p?$format($idm_cur_p['amount']):'—' ?></b><em><?= $idm_cur_state==='paid'?official_svg('check-circle'):'' ?><?= $idm_state_label[$idm_cur_state] ?></em></div></div>
    <ol class="idm-strip" aria-label="Status iuran 6 bulan terakhir"><?php foreach($idm_months as $m=>$st): $mm=(int)substr($m,5,2); ?><li class="st-<?= $st ?><?= $m===$idm_cur?' is-current':'' ?>" title="<?= html_escape($periodLabel($m).': '.$idm_state_label[$st]) ?>"><span><?= $idm_short[$mm] ?></span><i aria-hidden="true"><?= $st==='paid'?'&#10003;':($st==='pending'?'&#8987;':($st==='unpaid'?'!':'&ndash;')) ?></i><span class="sr-only"><?= $idm_state_label[$st] ?></span></li><?php endforeach; ?></ol>
    <div class="idm-actions"><?php if($idm_pay_target): ?><button type="button" class="idm-pay" data-idm-pay="<?= $idm_pay_target ?>"><?= official_svg('wallet') ?>Bayar Iuran</button><?php else: ?><span class="idm-pay is-off"><?= official_svg('check-circle') ?>Semua Lunas</span><?php endif; ?><?php if($idm_proof): ?><a class="idm-proof" href="<?= site_url('warga/bukti-iuran/'.(int)$idm_proof['id']) ?>" target="_blank" rel="noopener"><?= official_svg('download') ?>Unduh Bukti</a><?php else: ?><span class="idm-proof is-off" title="Belum ada bukti pembayaran"><?= official_svg('download') ?>Unduh Bukti</span><?php endif; ?></div>
    <p class="idm-sum"><?= (int)$unpaid_bills ?> tagihan belum lunas · <?= (int)$paid_bills ?> tagihan lunas · <?= count($dues) ?> tagihan tercatat</p>
   </div>
   <section class="idm-recent" aria-labelledby="idm-recent-title"><header><h2 id="idm-recent-title">Iuran Terakhir</h2><button type="button" data-idm-tab="tagihan">Lihat Semua <?= official_svg('arrow-right') ?></button></header>
    <?php if($idm_recent): ?><ul><?php foreach($idm_recent as $m=>$p): $st=$idm_state($p); ?><li><?= official_svg('calendar') ?><strong><?= html_escape($periodLabel($m)) ?></strong><b><?= $format($p['amount']) ?></b><em class="b-<?= $idm_badge[$st][1] ?>"><?= $st==='paid'?'&#10003; ':'' ?><?= $idm_badge[$st][0] ?></em></li><?php endforeach; ?></ul><?php else: ?><p class="idm-empty">Belum ada tagihan untuk akun Anda.</p><?php endif; ?>
   </section>
  </div>
  <?php /* ---- Riwayat pembayaran (filter periode, status, cari) ---- */
   $idm_due_by_id=array();foreach($dues as $due)$idm_due_by_id[(int)$due['id']]=$due;
   $idm_pay_periods=array();foreach($payments as $pay){$d=$idm_due_by_id[(int)$pay['due_id']]??NULL;if($d)$idm_pay_periods[$d['period']]=TRUE;}krsort($idm_pay_periods);
   $idm_cat_icon=function($c){$c=mb_strtolower((string)$c);foreach(array('bersih'=>'trash','sampah'=>'trash','aman'=>'shield','ronda'=>'shield','sosial'=>'heart','duka'=>'heart','operasional'=>'people','kas'=>'wallet') as $k=>$i)if(strpos($c,$k)!==FALSE)return $i;return 'wallet';};
   $idm_pay_status=array('verified'=>array('Lunas','ok','check-circle'),'pending'=>array('Menunggu Verifikasi','wait','clock'),'returned'=>array('Dikembalikan','bad','alert')); ?>
  <div class="idm-panel" data-idm-panel="riwayat" role="tabpanel" hidden>
   <div class="idm-filters">
    <label class="idm-sel"><?= official_svg('calendar') ?><span class="sr-only">Periode</span><select id="idm-f-period"><option value="">Semua</option><?php foreach(array_keys($idm_pay_periods) as $m): ?><option value="<?= html_escape($m) ?>"><?= $idm_short[(int)substr($m,5,2)].' '.substr($m,0,4) ?></option><?php endforeach; ?></select></label>
    <label class="idm-sel"><?= official_svg('tag') ?><span class="sr-only">Status</span><select id="idm-f-status"><option value="">Semua Status</option><?php foreach($idm_pay_status as $k=>$s): ?><option value="<?= $k ?>"><?= $s[0] ?></option><?php endforeach; ?></select></label>
    <button type="button" class="idm-find" id="idm-f-find" aria-label="Cari pembayaran" aria-expanded="false" aria-controls="idm-f-q"><?= official_svg('search') ?></button>
   </div>
   <label class="idm-q" hidden id="idm-f-qwrap"><?= official_svg('search') ?><span class="sr-only">Cari</span><input type="search" id="idm-f-q" placeholder="Cari jenis iuran atau metode..."></label>
   <?php if($idm_pay_periods): ?><div class="idm-chips" role="group" aria-label="Pilih periode cepat"><?php foreach(array_slice(array_keys($idm_pay_periods),0,3) as $m): ?><button type="button" data-idm-chip="<?= html_escape($m) ?>"><?= $idm_short[(int)substr($m,5,2)].' '.substr($m,0,4) ?></button><?php endforeach; ?></div><?php endif; ?>
   <section class="idm-history" aria-labelledby="idm-hist-title"><header><h2 id="idm-hist-title"><?= official_svg('list') ?>Riwayat Pembayaran</h2><button type="button" data-idm-reset>Lihat Semua <?= official_svg('arrow-right') ?></button></header>
    <?php if($payments): ?><ul class="idm-log"><?php foreach($payments as $pay): $d=$idm_due_by_id[(int)$pay['due_id']]??array('category'=>'Iuran','period'=>'','id'=>0); $ps=$idm_pay_status[$pay['status']]??array(ucfirst($pay['status']),'wait','clock'); $per=$d['period']?$idm_short[(int)substr($d['period'],5,2)].' '.substr($d['period'],0,4):'—'; ?>
     <li data-period="<?= html_escape($d['period']) ?>" data-status="<?= html_escape($pay['status']) ?>" data-search="<?= html_escape(mb_strtolower($d['category'].' '.($pay['method']==='tunai'?'tunai':'transfer'))) ?>">
      <span class="idm-log-ic" aria-hidden="true"><?= official_svg($idm_cat_icon($d['category'])) ?></span>
      <div class="idm-log-main"><strong><?= html_escape($d['category']) ?></strong><small><?= official_svg('calendar') ?><?= cfx_date($pay['created_at']) ?></small><small><?= official_svg('tag') ?>Periode <?= html_escape($per) ?></small></div>
      <div class="idm-log-meta"><p><span>Nominal</span>: <b><?= $format($pay['amount']) ?></b></p><p><span>Metode</span>: <?= $pay['method']==='tunai'?'Tunai':'Transfer' ?></p><em class="s-<?= $ps[1] ?>"><?= official_svg($ps[2]) ?><?= $ps[0] ?></em></div>
      <?php if(!empty($pay['proof'])): ?><a class="idm-log-go" href="<?= site_url('warga/bukti-iuran/'.(int)$pay['id']) ?>" target="_blank" rel="noopener">Lihat Bukti <?= official_svg('chev-right') ?></a><?php else: ?><button type="button" class="idm-log-go" data-idm-detail="<?= (int)$d['id'] ?>">Detail <?= official_svg('chev-right') ?></button><?php endif; ?>
     </li><?php endforeach; ?></ul>
     <p class="idm-empty" id="idm-log-none" hidden>Tidak ada pembayaran yang cocok dengan filter.</p>
    <?php else: ?><p class="idm-empty">Belum ada pembayaran yang dikirim.</p><?php endif; ?>
   </section>
  </div>
  <div class="idm-panel" data-idm-panel="tagihan" role="tabpanel" hidden><p class="idm-hint">Ketuk <b>Bayar</b> pada tagihan untuk mengirim pembayaran, atau <b>Detail</b> untuk melihat riwayatnya.</p></div>
 </section>
<div class="cdx-wrap">
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
<script src="<?= base_url('assets/citizen-dues-mobile.js') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-mobile.js') ?>" defer></script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
