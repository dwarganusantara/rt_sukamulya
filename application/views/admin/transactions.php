<link rel="stylesheet" href="<?= base_url('assets/admin-transactions.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-transactions.css') ?>">
<?php
$income=(float)($summary['income']??0); $expense=(float)($summary['expense']??0);
$incomeCount=(int)($summary['income_count']??0); $expenseCount=(int)($summary['expense_count']??0);
$monthLabel=date('F Y',strtotime($period.'-01'));
$months=array('January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember');
$monthLabel=strtr($monthLabel,$months);
$svg=function($name){$paths=array(
 'wallet'=>'<path d="M4 8.5h20v15H4zM4 8.5V6h15v2.5M19 14h7v5h-7a2.5 2.5 0 0 1 0-5z"/>',
 'up'=>'<path d="M16 25V7m0 0-7 7m7-7 7 7"/>','down'=>'<path d="M16 7v18m0 0-7-7m7 7 7-7"/>',
 'cash'=>'<path d="M7 5h18v22H7zM11 10h10M11 15h10M11 20h6"/>',
 'search'=>'<circle cx="14" cy="14" r="8"/><path d="m20 20 7 7"/>',
 'calendar'=>'<path d="M6 8h20v19H6zM6 13h20M11 4v7M21 4v7"/>',
 'filter'=>'<path d="M5 7h22l-8 9v9l-6 3V16z"/>',
 'reset'=>'<path d="M7 11a10 10 0 1 1-1 9M7 11V4m0 7h7"/>',
 'receipt'=>'<path d="M8 4h16v24l-4-3-4 3-4-3-4 3zM12 10h8M12 15h8M12 20h5"/>',
 'reverse'=>'<path d="M11 9 5 15l6 6M6 15h11a8 8 0 0 1 8 8"/>'
 ); return '<svg viewBox="0 0 32 32" aria-hidden="true">'.$paths[$name].'</svg>';};
// ---- Grafik (SVG dari data asli, tanpa pustaka) ----
$daily=$daily??array('masuk'=>array(),'keluar'=>array());$dim=count($daily['masuk'])?:(int)date('t',strtotime($period.'-01'));
$fmtShort=function($v){return $v>=1000000?'Rp '.rtrim(rtrim(number_format($v/1000000,1,',','.'),'0'),',').' jt':'Rp '.number_format($v,0,',','.');};
// Kurva halus melalui titik (Catmull-Rom -> Bezier).
$smooth=function($pts){$n=count($pts);if($n<2)return '';$dx=array();$m=array();for($i=0;$i<$n-1;$i++){$dx[$i]=$pts[$i+1][0]-$pts[$i][0];$m[$i]=$dx[$i]?($pts[$i+1][1]-$pts[$i][1])/$dx[$i]:0;}
 $t=array($m[0]);for($i=1;$i<$n-1;$i++)$t[$i]=($m[$i-1]*$m[$i]<=0)?0:($m[$i-1]+$m[$i])/2;$t[$n-1]=$m[$n-2];
 for($i=0;$i<$n-1;$i++){if($m[$i]==0){$t[$i]=0;$t[$i+1]=0;continue;}$a=$t[$i]/$m[$i];$b=$t[$i+1]/$m[$i];$s=$a*$a+$b*$b;if($s>9){$k=3/sqrt($s);$t[$i]=$k*$a*$m[$i];$t[$i+1]=$k*$b*$m[$i];}}
 $d='M'.round($pts[0][0],1).' '.round($pts[0][1],1);for($i=0;$i<$n-1;$i++){$h=$dx[$i]/3;$d.=' C'.round($pts[$i][0]+$h,1).' '.round($pts[$i][1]+$t[$i]*$h,1).' '.round($pts[$i+1][0]-$h,1).' '.round($pts[$i+1][1]-$t[$i+1]*$h,1).' '.round($pts[$i+1][0],1).' '.round($pts[$i+1][1],1);}return $d;};
// Sparkline kumulatif untuk kartu ringkasan.
$spark=function($series,$cls)use($smooth){$vals=array_values($series);$run=0;$cum=array();foreach($vals as $v){$run+=$v;$cum[]=$run;}$max=max(1,max($cum?:array(0)));$n=max(2,count($cum));$pts=array();
 foreach($cum as $i=>$v)$pts[]=array(4+$i*(112/($n-1)),40-($v/$max)*32);$line=$smooth($pts);if(!$line)return '';$last=end($pts);
 return '<svg class="cash-spark '.$cls.'" viewBox="0 0 120 46" aria-hidden="true"><defs><linearGradient id="sg-'.$cls.'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" class="s0"/><stop offset="1" class="s1"/></linearGradient></defs><path class="area" d="'.$line.' L'.round($last[0],1).' 44 L4 44 Z" fill="url(#sg-'.$cls.')"/><path class="line" d="'.$line.'"/><circle cx="'.round($last[0],1).'" cy="'.round($last[1],1).'" r="3.2"/></svg>';};
?>
<div class="cash-page">
 <nav class="cash-breadcrumb"><a href="<?= site_url('admin') ?>">Beranda</a><span>›</span><a href="<?= site_url('admin/transactions') ?>">Kas RT</a><span>›</span><strong>Transaksi Kas</strong></nav>
 <section class="cash-hero">
  <div class="cash-hero-copy"><span class="cash-title-icon"><?= $svg('wallet') ?></span><div><h1>Transaksi Kas</h1><p>Transaksi langsung masuk ke laporan kas setelah disimpan. Bukti pembayaran bersifat opsional.</p></div></div>
  <?php if(in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><div class="cash-create"><a href="<?= site_url('admin/transaction/masuk') ?>"><span><?= $svg('up') ?></span>Pemasukan</a><a class="outline" href="<?= site_url('admin/transaction/keluar') ?>"><span><?= $svg('down') ?></span>Pengeluaran</a></div><?php endif; ?>
 </section>
 <section class="cash-stats" aria-label="Ringkasan transaksi">
  <article class="income"><span><?= $svg('up') ?></span><div><small>Total Pemasukan</small><strong>Rp <?= number_format($income,0,',','.') ?></strong><em><?= $incomeCount ?> transaksi</em></div><?= $spark($daily['masuk'],'in') ?></article>
  <article class="expense"><span><?= $svg('down') ?></span><div><small>Total Pengeluaran</small><strong>Rp <?= number_format($expense,0,',','.') ?></strong><em><?= $expenseCount ?> transaksi</em></div><?= $spark($daily['keluar'],'out') ?></article>
  <article class="balance"><span><?= $svg('cash') ?></span><div><small>Saldo Kas</small><strong>Rp <?= number_format($balance,0,',','.') ?></strong><em>Per <?= date('t',strtotime($period.'-01')).' '.$monthLabel ?></em></div><span class="cash-wallet-art" aria-hidden="true"><i></i></span></article>
 </section>
 <?php
 // Tren harian: sumbu X = tanggal 1..akhir bulan, sumbu Y = total harian (Rp). Garis halus + area untuk pemasukan.
 $W=640;$H=210;$L=78;$R=14;$T=14;$B=34;$pw=$W-$L-$R;$ph=$H-$T-$B;
 $cum=array('masuk'=>array(),'keluar'=>array());foreach(array('masuk','keluar') as $tk){$run=0;foreach(range(1,$dim) as $d){$run+=$daily[$tk][$d]??0;$cum[$tk][$d]=$run;}}
 $maxVal=max(1,$cum['masuk'][$dim],$cum['keluar'][$dim]);
 $mag=pow(10,floor(log10($maxVal)));$step=$mag;foreach(array(1,2,2.5,5,10) as $m){if($maxVal/($m*$mag)<=4){$step=$m*$mag;break;}}$top=ceil($maxVal/$step)*$step;$ticks=(int)round($top/$step);
 $x=function($day)use($L,$pw,$dim){return $L+($dim>1?($day-1)*$pw/($dim-1):0);};$y=function($v)use($T,$ph,$top){return $T+$ph-($v/$top)*$ph;};
 $ptsIn=array();$ptsOut=array();foreach(range(1,$dim) as $d){$ptsIn[]=array($x($d),$y($cum['masuk'][$d]));$ptsOut[]=array($x($d),$y($cum['keluar'][$d]));}
 $lineIn=$smooth($ptsIn);$lineOut=$smooth($ptsOut);$xTicks=array_unique(array_merge(array(1),range(5,$dim,5),array($dim)));
 $totalCount=$incomeCount+$expenseCount;$pctIn=$totalCount?round(100*$incomeCount/$totalCount):0;$pctOut=$totalCount?100-$pctIn:0;
 $circ=2*M_PI*46;$lenIn=$totalCount?$circ*$incomeCount/$totalCount:0;
 ?>
 <section class="cash-charts" aria-label="Grafik transaksi <?= html_escape($monthLabel) ?>">
  <article class="cash-chart-card cash-trend">
   <header><h2><span class="cash-bars" aria-hidden="true"><i></i><i></i><i></i></span>Tren Transaksi <small>(<?= html_escape($monthLabel) ?> · akumulasi harian)</small></h2><ul class="cash-legend"><li class="in">Pemasukan</li><li class="out">Pengeluaran</li></ul></header>
   <svg class="cash-trend-svg" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Tren harian <?= html_escape($monthLabel) ?>: pemasukan Rp <?= number_format($income,0,',','.') ?>, pengeluaran Rp <?= number_format($expense,0,',','.') ?>">
    <defs><linearGradient id="cash-area-in" x1="0" y1="0" x2="0" y2="1"><stop offset="0" class="s0"/><stop offset="1" class="s1"/></linearGradient></defs>
    <?php for($i=0;$i<=$ticks;$i++): $v=$step*$i;$yy=round($y($v),1); ?><line class="grid" x1="<?= $L ?>" x2="<?= $W-$R ?>" y1="<?= $yy ?>" y2="<?= $yy ?>"/><text class="ylab" x="<?= $L-8 ?>" y="<?= $yy+4 ?>"><?= html_escape($fmtShort($v)) ?></text><?php endfor; ?>
    <?php foreach($xTicks as $d): $xx=round($x($d),1); ?><line class="vgrid" x1="<?= $xx ?>" x2="<?= $xx ?>" y1="<?= $T ?>" y2="<?= $T+$ph ?>"/><text class="xlab<?= $d===$dim?' end':'' ?>" x="<?= $xx ?>" y="<?= $H-10 ?>"><?= $d ?> <?= html_escape(mb_substr($monthLabel,0,3)) ?></text><?php endforeach; ?>
    <?php if($lineIn): ?><path class="area-in" d="<?= $lineIn ?> L<?= round($x($dim),1) ?> <?= $T+$ph ?> L<?= $L ?> <?= $T+$ph ?> Z" fill="url(#cash-area-in)"/><?php endif; ?>
    <path class="line-out" d="<?= $lineOut ?>"/><path class="line-in" d="<?= $lineIn ?>"/>
    <?php foreach(range(1,$dim) as $d): foreach(array('masuk'=>'in','keluar'=>'out') as $tk=>$cls): $v=$daily[$tk][$d]??0; if($v<=0)continue; ?><circle class="pt-<?= $cls ?>" cx="<?= round($x($d),1) ?>" cy="<?= round($y($cum[$tk][$d]),1) ?>" r="4.5"><title><?= $d.' '.html_escape($monthLabel) ?> — <?= $tk==='masuk'?'Pemasukan':'Pengeluaran' ?> Rp <?= number_format($v,0,',','.') ?> (total s.d. hari ini Rp <?= number_format($cum[$tk][$d],0,',','.') ?>)</title></circle><?php endforeach; endforeach; ?>
   </svg>
   <?php if(!$totalCount): ?><p class="cash-chart-empty">Belum ada transaksi pada <?= html_escape($monthLabel) ?>.</p><?php endif; ?>
  </article>
  <article class="cash-chart-card cash-dist">
   <header><h2><span class="cash-bars warm" aria-hidden="true"><i></i><i></i><i></i></span>Distribusi Transaksi</h2></header>
   <div class="cash-dist-body">
    <svg class="cash-donut" viewBox="0 0 120 120" role="img" aria-label="<?= $totalCount ?> transaksi: <?= $incomeCount ?> pemasukan, <?= $expenseCount ?> pengeluaran">
     <circle cx="60" cy="60" r="46" class="track"/>
     <?php if($totalCount): ?><circle cx="60" cy="60" r="46" class="seg-out" stroke-dasharray="<?= round($circ,2) ?> <?= round($circ,2) ?>"/><?php if($incomeCount): ?><circle cx="60" cy="60" r="46" class="seg-in" stroke-dasharray="<?= round($lenIn,2) ?> <?= round($circ,2) ?>" transform="rotate(-90 60 60)"/><?php endif; ?><?php endif; ?>
     <text x="60" y="60" class="n"><?= $totalCount ?></text><text x="60" y="78" class="l">Transaksi</text>
    </svg>
    <ul class="cash-dist-legend"><li class="in"><b>Pemasukan</b><span><?= $incomeCount ?> (<?= $pctIn ?>%)</span></li><li class="out"><b>Pengeluaran</b><span><?= $expenseCount ?> (<?= $pctOut ?>%)</span></li></ul>
   </div>
  </article>
 </section>
 <form class="cash-filter" method="get" action="<?= site_url('admin/transactions') ?>">
  <label><span><?= $svg('calendar') ?></span><div><small>Pilih Periode</small><input type="month" name="period" value="<?= html_escape($period) ?>"></div></label>
  <label><div><small>Jenis Transaksi</small><select name="type"><option value="" <?= $type===''?'selected':'' ?>>Semua</option><option value="masuk" <?= $type==='masuk'?'selected':'' ?>>Pemasukan</option><option value="keluar" <?= $type==='keluar'?'selected':'' ?>>Pengeluaran</option></select></div></label>
  <label class="search"><span><?= $svg('search') ?></span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari transaksi..."></label>
  <?php if(($sort??'tanggal')!=='tanggal'||($dir??'DESC')!=='DESC'): ?><input type="hidden" name="urut" value="<?= html_escape($sort) ?>"><input type="hidden" name="arah" value="<?= strtolower($dir) ?>"><?php endif; ?><a class="cash-reset" href="<?= site_url('admin/transactions') ?>"><?= $svg('reset') ?><span>Reset</span></a><button type="submit"><?= $svg('filter') ?><span>Filter</span></button>
 </form>
 <section class="cash-table-card"><div class="cash-table-scroll"><table><?php $sortLink=function($key,$label)use($period,$type,$q,$sort,$dir){$next=($sort===$key&&$dir==='DESC')?'asc':'desc';if($sort!==$key)$next=$key==='jenis'?'asc':'desc';$qs=array_filter(array('period'=>$period,'type'=>$type,'q'=>$q,'urut'=>$key,'arah'=>$next),'strlen');$state=$sort===$key?($dir==='ASC'?'asc':'desc'):'';return '<a class="cash-sort'.($state?' is-'.$state:'').'" href="'.html_escape(site_url('admin/transactions').'?'.http_build_query($qs)).'" aria-label="Urutkan menurut '.$label.'">'.$label.'<i aria-hidden="true"></i></a>';}; ?><thead><tr><th>ID</th><th aria-sort="<?= $sort==='tanggal'?($dir==='ASC'?'ascending':'descending'):'none' ?>"><?= $sortLink('tanggal','Tanggal') ?></th><th aria-sort="<?= $sort==='jenis'?($dir==='ASC'?'ascending':'descending'):'none' ?>"><?= $sortLink('jenis','Jenis') ?></th><th>Uraian</th><th aria-sort="<?= $sort==='jumlah'?($dir==='ASC'?'ascending':'descending'):'none' ?>"><?= $sortLink('jumlah','Jumlah') ?></th><th class="action-head">Bukti / Aksi</th></tr></thead><tbody>
 <?php foreach($items as $item): ?><tr><td>#<?= (int)$item['id'] ?></td><td><?= date('d-m-Y',strtotime($item['tx_date'])) ?></td><td><span class="cash-kind <?= $item['type']==='masuk'?'in':'out' ?>"><i><?= $svg($item['type']==='masuk'?'up':'down') ?></i><?= $item['type']==='masuk'?'Pemasukan':'Pengeluaran' ?></span></td><td><strong class="cash-description"><?= html_escape($item['description']) ?></strong><small><?= html_escape($item['category']) ?></small></td><td><b class="cash-amount <?= $item['type']==='masuk'?'in':'out' ?>">Rp <?= number_format($item['amount'],0,',','.') ?></b></td><td><div class="cash-actions"><?php if(!empty($item['proof'])): ?><a href="<?= site_url('bukti/'.$item['id']) ?>" target="_blank" title="Lihat bukti" aria-label="Lihat bukti"><?= $svg('receipt') ?></a><?php endif; ?><?php if(in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><a class="reverse" href="<?= site_url('admin/transaction/'.($item['type']==='masuk'?'keluar':'masuk').'?reverse='.$item['id']) ?>" title="Buat transaksi pembalik" aria-label="Buat transaksi pembalik"><?= $svg('reverse') ?></a><?php endif; ?><?php if(empty($item['proof'])&&!in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><span>-</span><?php endif; ?></div></td></tr><?php endforeach; ?>
 <?php if(!$items): ?><tr><td colspan="6" class="cash-empty">Tidak ada transaksi pada periode atau filter ini.</td></tr><?php endif; ?>
 </tbody></table></div><footer>Menampilkan <?= count($items) ?> transaksi untuk <?= html_escape($monthLabel) ?></footer></section>
</div>
