<?php
$title='Laporan Kas RT | '.($settings['site_name']??'Sukamulya');
$is_citizen=(bool)$this->session->userdata('citizen_id');
$citizen_letter_layout=$is_citizen; $citizen_shell=TRUE; $shell_active=''; // header merah (warga & tamu)
$this->load->view('site/header',compact('title','settings','menu','citizen_letter_layout','citizen_shell','shell_active'));
if($is_citizen)$this->load->view('citizen/sidebar',compact('settings'));
$this->load->helper(array('official_icons','content_form'));
$income=0;$expense=0;$income_count=0;$expense_count=0;
foreach($items as $item){if($item['type']==='masuk'){$income+=(float)$item['amount'];$income_count++;}else{$expense+=(float)$item['amount'];$expense_count++;}}
$format=function($n){return ($n<0?'- ':'').'Rp '.number_format(abs($n),0,',','.');};
$short=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
// Skala grafik dibulatkan ke atas agar garis bantu berupa angka rapi.
$peak=0;foreach($trend as $month)$peak=max($peak,$month['income'],$month['expense']);
$axis=4;
if($peak>0){$unit=pow(10,floor(log10($peak)));foreach(array(1,2,4,5,10) as $mult){if($mult*$unit>=$peak){$axis=$mult*$unit;break;}}}
$prev=date('Y-m',strtotime($period.'-01 -1 month'));
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-dues-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-pages-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-pages-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="<?= $is_citizen?'letter-page ':'' ?>cdx-page fnx-page crd-page crd-finance"><div class="cdx-wrap">
 <?php if($is_citizen)$this->load->view('citizen/topbar',array('crumbs'=>array(array('Beranda','warga'),array('Keuangan',''),array('Laporan Kas RT','')))); ?>
 <section class="fnx-hero"><img src="<?= base_url('assets/mockup/citizen-finance-hero.png') ?>" alt=""><div class="fnx-copy"><small>TRANSPARANSI KAS RT</small><h1>Laporan <b>Kas RT</b></h1><p>Ringkasan pemasukan dan pengeluaran yang tercatat. Data pribadi warga tidak ditampilkan.</p></div><form method="get" class="fnx-period"><label for="finance-period">Periode laporan</label><input id="finance-period" type="month" name="periode" value="<?= html_escape($period) ?>" required><button type="submit">Lihat Laporan <?= official_svg('arrow-right') ?></button></form></section>
 <section class="cdx-metrics four" aria-label="Ringkasan kas">
  <a class="red" href="<?= site_url('keuangan') ?>?periode=<?= $prev ?>" title="Buka laporan periode sebelumnya"><span class="cdx-icon"><?= official_svg('wallet') ?></span><div><small>Saldo Awal</small><strong><?= $format($opening) ?></strong><em>Saldo periode sebelumnya</em></div><i class="cdx-mark low"><?= official_svg('chev-right') ?></i></a>
  <a class="green" href="#rincian" data-type="masuk" title="Tampilkan pemasukan"><span class="cdx-icon"><?= official_svg('up') ?></span><div><small>Total Pemasukan</small><strong><?= $format($income) ?></strong><em><?= $income_count ?> transaksi</em></div><i class="cdx-mark low"><?= official_svg('chev-right') ?></i></a>
  <a class="red" href="#rincian" data-type="keluar" title="Tampilkan pengeluaran"><span class="cdx-icon"><?= official_svg('down') ?></span><div><small>Total Pengeluaran</small><strong><?= $format($expense) ?></strong><em><?= $expense_count ?> transaksi</em></div><i class="cdx-mark low"><?= official_svg('chev-right') ?></i></a>
  <a class="orange" href="#rincian" data-type="" title="Tampilkan semua transaksi"><span class="cdx-icon"><?= official_svg('money') ?></span><div><small>Saldo Akhir</small><strong><?= $format($opening+$income-$expense) ?></strong><em>Sisa saldo periode ini</em></div><i class="cdx-mark low"><?= official_svg('chev-right') ?></i></a>
 </section>
 <div class="fnx-grid">
  <section class="cdx-card"><header class="cdx-head"><span class="red"><?= official_svg('chart') ?></span><div><h2>Grafik Pergerakan Kas</h2><p>Perbandingan pemasukan dan pengeluaran selama 6 bulan sampai periode ini.</p></div><div class="fnx-legend"><span><i class="in"></i>Pemasukan</span><span><i class="out"></i>Pengeluaran</span></div></header>
   <div class="fnx-chart" role="img" aria-label="Grafik pemasukan dan pengeluaran enam bulan">
    <div class="fnx-grid-lines"><?php for($i=4;$i>=0;$i--): ?><span><b><?= number_format($axis*$i/4,0,',','.') ?></b></span><?php endfor; ?></div>
    <div class="fnx-bars"><?php foreach($trend as $month): $parts=explode('-',$month['period']); ?><div><p><i class="in" style="height:<?= round(100*$month['income']/$axis,2) ?>%" title="Pemasukan <?= $format($month['income']) ?>"></i><i class="out" style="height:<?= round(100*$month['expense']/$axis,2) ?>%" title="Pengeluaran <?= $format($month['expense']) ?>"></i></p><small><?= $short[(int)$parts[1]].' '.substr($parts[0],2) ?></small></div><?php endforeach; ?></div>
   </div>
  </section>
  <section class="cdx-card fnx-explain"><header class="cdx-head"><span class="red"><?= official_svg('bulb') ?></span><div><h2>Cara Membaca Laporan</h2></div></header>
   <ul><li><span class="green"><?= official_svg('up') ?></span><p><b>Pemasukan</b> berasal dari iuran, donasi, dan pemasukan lain yang dicatat.</p></li><li><span class="red"><?= official_svg('down') ?></span><p><b>Pengeluaran</b> langsung tercatat saat disimpan oleh pengurus.</p></li><li><span class="amber"><?= official_svg('doc') ?></span><p><b>Saldo akhir</b> adalah saldo awal ditambah pemasukan, lalu dikurangi pengeluaran.</p></li></ul>
   <p class="fnx-privacy"><?= official_svg('alert') ?>Data yang ditampilkan hanya ringkasan transaksi. Data pribadi warga tidak ditampilkan.</p>
  </section>
 </div>
 <section class="cdx-card" id="rincian"><header class="cdx-head"><span class="red"><?= official_svg('doc') ?></span><div><h2>Rincian Transaksi</h2><p>Hanya transaksi terverifikasi pada periode ini.</p></div><div class="fnx-tools" data-table-filter="fnx-items"><select id="fnx-type" data-filter="type" aria-label="Filter jenis transaksi"><option value="">Semua Jenis</option><option value="masuk">Pemasukan</option><option value="keluar">Pengeluaran</option></select><button class="cdx-report fnx-print" type="button" onclick="window.print()"><?= official_svg('download') ?>Cetak / PDF</button></div></header>
  <div class="cdx-scroll"><table class="cdx-table"><thead><tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Uraian</th><th>Jumlah</th><th>Bukti</th></tr></thead><tbody id="fnx-items"><?php foreach($items as $n=>$item): $in=$item['type']==='masuk'; ?><tr data-row data-type="<?= $in?'masuk':'keluar' ?>"><td><?= $n+1 ?></td><td><?= cfx_date($item['tx_date']) ?></td><td><span class="cdx-badge <?= $in?'verified':'unpaid' ?>"><?= $in?'Masuk':'Keluar' ?></span></td><td><?= html_escape($item['category']) ?></td><td><?= html_escape($item['description']) ?></td><td class="fnx-amount <?= $in?'in':'out' ?>"><?= $in?'+':'−' ?> <?= $format($item['amount']) ?></td><td class="cdx-note"><?php if($item['proof']): ?><a href="<?= site_url('bukti/'.(int)$item['id']) ?>" target="_blank" rel="noopener">Lihat</a><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?><tr data-empty-row<?= $items?' hidden':'' ?>><td colspan="7" class="cpx-empty"><p><?= $items?'Tidak ada transaksi jenis ini.':'Belum ada transaksi terverifikasi pada periode ini.' ?></p></td></tr></tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
<script src="<?= base_url('assets/citizen-table-filter.js') ?>?v=<?= filemtime(FCPATH.'assets/citizen-table-filter.js') ?>"></script>
<script>document.querySelectorAll('a[data-type]').forEach(function(link){link.addEventListener('click',function(){var select=document.getElementById('fnx-type');select.value=link.dataset.type;select.dispatchEvent(new Event('change'))})});</script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
