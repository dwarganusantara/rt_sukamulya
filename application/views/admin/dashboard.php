<?php
$role=$this->session->userdata('admin_role');
$paid=(float)($dues_summary['paid_total']??0);$due=(float)($dues_summary['due_total']??0);$percent=$due>0?(int)round(min(100,100*$paid/$due)):0;
$bulan=array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
$short_month=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$month_label=$bulan[(int)date('n')].' '.date('Y');
$citizen_before=(int)$citizen_count-(int)$citizen_new;
$citizen_trend=$citizen_before>0?'+'.(int)round(100*$citizen_new/$citizen_before).'% dari bulan lalu':'+'.(int)$citizen_new.' warga bulan ini';
$money=function($v){return 'Rp '.number_format((float)$v,0,',','.');};
$when=function($d)use($short_month){$t=strtotime((string)$d);return $t?date('j',$t).' '.$short_month[(int)date('n',$t)].' '.date('Y',$t):'-';};
$admin_name=ucfirst($admin_user['username']??'Admin');$fon=function($c)use($feature_on){return !isset($feature_on[$c])||$feature_on[$c];};
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-dashboard-v4.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-dashboard-v4.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin-dashboard-v5.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-dashboard-v5.css') ?>">
<div class="dbh">
<section class="dbh-hero dbh-hero-v5">
 <?php /* Foto header sebagai <img> setinggi hero dengan lebar otomatis: tidak pernah terpotong, berapa pun rasionya. */ ?>
 <img class="dbh-hero-img" src="<?= base_url(html_escape($settings['dashboard_page_image']??'assets/mockup/home-hero-v4.png')) ?>" alt="" aria-hidden="true" decoding="async">
 <div class="dbh-hero-copy"><h1>Selamat Datang, <span><?= html_escape($admin_name) ?></span>! <i aria-hidden="true">👋</i></h1><p>Kelola informasi dan layanan RT 009/008 Suka Mulya IV<br>dengan mudah, cepat, dan terintegrasi.</p><div class="dbh-pills"><span class="green"><?= official_svg('leaf') ?>Lingkungan Aman</span><span class="orange"><?= official_svg('people') ?>Warga Harmonis</span><span class="blue"><?= official_svg('shield') ?>Informasi Transparan</span></div></div>
</section>


<section class="dbh-stats" aria-label="Ringkasan dashboard">
 <?php if(!empty($citizens_locked)): ?><a class="dbh-stat orange dbh-stat-locked" href="<?= site_url('admin/citizens') ?>" data-sub-feature="admin/citizens" data-sub-label="Data Warga"><span class="dbh-stat-ic"><?= official_svg('people') ?></span><span class="dbh-stat-tx"><small>Total Warga</small><strong class="dbh-locked-value"><?= official_svg('crown') ?>Premium</strong><em>Berlangganan untuk melihat data</em></span><span class="dbh-mark"><?= official_svg('people') ?></span></a>
 <?php else: ?><a class="dbh-stat orange" href="<?= site_url('admin/citizens') ?>"><span class="dbh-stat-ic"><?= official_svg('people') ?></span><span class="dbh-stat-tx"><small>Total Warga</small><strong><?= (int)$citizen_count ?></strong><em class="up"><?= official_svg('up') ?><?= html_escape($citizen_trend) ?></em></span><span class="dbh-mark"><?= official_svg('people') ?></span></a><?php endif; ?>
 <?php if($fon('events')): ?><a class="dbh-stat blue" href="<?= site_url('admin/posts/agenda') ?>"><span class="dbh-stat-ic"><?= official_svg('calendar') ?></span><span class="dbh-stat-tx"><small>Agenda Bulan Ini</small><strong><?= (int)$agenda_count ?></strong><em><?= $month_label ?></em></span><span class="dbh-mark"><?= official_svg('calendar') ?></span></a><?php endif; ?>
 <?php if($fon('announcements')): ?><a class="dbh-stat green" href="<?= site_url('admin/posts/pengumuman') ?>"><span class="dbh-stat-ic"><?= official_svg('megaphone') ?></span><span class="dbh-stat-tx"><small>Pengumuman</small><strong><?= (int)$announcement_count ?></strong><em class="up"><?= official_svg('up') ?>+<?= (int)$announcement_new ?> baru</em></span><span class="dbh-mark"><?= official_svg('megaphone') ?></span></a><?php endif; ?>
 <?php if($show_dues): ?><a class="dbh-stat purple" href="<?= site_url('admin/dues') ?>"><span class="dbh-stat-ic"><?= official_svg('wallet') ?></span><span class="dbh-stat-tx"><small>Total Iuran Bulan Ini</small><strong><?= $money($paid) ?></strong><em><span class="dbh-progress" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Iuran terbayar"><i style="width:<?= $percent ?>%"></i></span><?= $percent ?>% sudah dibayar</em></span><span class="dbh-info" title="Persentase nominal iuran periode <?= html_escape($month_label) ?> yang sudah dibayar dari total tagihan." aria-label="Keterangan"><?= official_svg('alert') ?></span></a><?php endif; ?>
 <?php if($show_cash && !empty($ops)): ?><a class="dbh-stat amber" href="<?= site_url('admin/transactions') ?>"><span class="dbh-stat-ic"><?= official_svg('database') ?></span><span class="dbh-stat-tx"><small>Saldo Kas RT</small><strong><?= $money($ops['cash_balance']) ?></strong><em>Jurnal terverifikasi</em></span><span class="dbh-mark"><?= official_svg('database') ?></span></a><?php endif; ?>
</section>

<?php if($waiting_requests && $fon('letters')): ?><a class="dbh-alert" href="<?= site_url('admin/requests') ?>"><?= official_svg('alert') ?><span><b><?= (int)$waiting_requests ?> pengajuan warga</b> menunggu untuk diperiksa.</span><strong>Periksa sekarang <?= official_svg('arrow-right') ?></strong></a><?php endif; ?>

<div class="dbh-row dbh-row-a">
 <?php if($resident_stats): $pts=$resident_stats['points']; $peak=1; foreach($pts as $p)$peak=max($peak,$p['members'],$p['families']);
  $step=0; foreach(array(1,2,5,10,20,25,50,100,200,250,500,1000,2000,5000) as $s) if($s*5>=$peak){$step=$s;break;} if(!$step)$step=(int)ceil($peak/5);
  $ymax=$step*5; $cw=300; $ch=170; $left=28; $top=8; $bottom=22; $plot_h=$ch-$top-$bottom; $plot_w=$cw-$left-6; $n=count($pts); $slot=$plot_w/$n; $bw=min(26,$slot*.58);
  $y=function($v)use($ymax,$plot_h,$top){return round($top+$plot_h-($v/$ymax)*$plot_h,1);};
  $line=array(); ?>
 <section class="dbh-card dbh-c-stat"><header><h2><span class="dbh-hic orange"><?= official_svg('chart') ?></span>Statistik Warga</h2>
  <form method="get" action="<?= site_url('admin') ?>" class="dbh-select"><label class="sr-only" for="dbh-span">Rentang statistik</label><select id="dbh-span" name="statistik" onchange="this.form.submit()"><option value="6"<?= $stat_span===6?' selected':'' ?>>6 Bulan Terakhir</option><option value="12"<?= $stat_span===12?' selected':'' ?>>12 Bulan Terakhir</option></select><noscript><button type="submit">Terapkan</button></noscript></form></header>
  <div class="dbh-chart">
   <svg viewBox="0 0 <?= $cw ?> <?= $ch ?>" role="img" aria-labelledby="dbh-chart-t"><title id="dbh-chart-t">Jumlah keluarga aktif dan warga terdaftar <?= $n ?> bulan terakhir</title>
    <?php for($k=0;$k<=5;$k++): $gy=$y($step*$k); ?><line x1="<?= $left ?>" x2="<?= $cw-4 ?>" y1="<?= $gy ?>" y2="<?= $gy ?>" class="g"/><text x="<?= $left-6 ?>" y="<?= $gy+3 ?>" class="ya"><?= $step*$k ?></text><?php endfor; ?>
    <?php foreach($pts as $idx=>$p): $cx=round($left+$slot*$idx+$slot/2,1); $line[]=$cx.','.$y($p['members']); ?>
    <g><title><?= html_escape($p['label']) ?>: <?= (int)$p['families'] ?> keluarga aktif, <?= (int)$p['members'] ?> warga terdaftar</title>
     <rect x="<?= $cx-$bw/2 ?>" y="<?= $y($p['members']) ?>" width="<?= $bw ?>" height="<?= max(0,$top+$plot_h-$y($p['members'])) ?>" rx="3" class="bm"/>
     <rect x="<?= $cx-$bw/2 ?>" y="<?= $y($p['families']) ?>" width="<?= $bw ?>" height="<?= max(0,$top+$plot_h-$y($p['families'])) ?>" rx="3" class="bf"/>
     <text x="<?= $cx ?>" y="<?= $ch-6 ?>" class="xa"><?= html_escape($p['label']) ?></text></g>
    <?php endforeach; ?>
    <polyline points="<?= implode(' ',$line) ?>" class="ln"/>
    <?php foreach($line as $pt): list($px,$py)=explode(',',$pt); ?><circle cx="<?= $px ?>" cy="<?= $py ?>" r="3.2" class="dt"/><?php endforeach; ?>
   </svg>
   <ul class="dbh-legend">
    <li><i class="sq bf"></i><span>Keluarga Aktif<b><?= (int)$resident_stats['families'] ?></b></span></li>
    <li><i class="sq bm"></i><span>Warga Terdaftar<b><?= (int)$resident_stats['members'] ?></b></span></li>
    <li><i class="ring"></i><span>Perubahan<b><?= html_escape($resident_stats['change']) ?></b></span></li>
   </ul>
  </div>
  <p class="dbh-foot-note">Dihitung dari data KK terverifikasi per akhir bulan.</p>
 </section>
 <?php endif; ?>

 <?php if($show_dues): $dtotal=(int)($dues_summary['total']??0); $dpaid=(int)($dues_summary['paid_count']??0); $lunas=$dtotal>0?(int)round(100*$dpaid/$dtotal):0; $circ=2*M_PI*48; ?>
 <section class="dbh-card dbh-c-dues"><header><h2><span class="dbh-hic orange"><?= official_svg('chart') ?></span>Iuran Warga <span class="muted">(Bulan Ini)</span></h2><a class="dbh-link" href="<?= site_url('admin/dues') ?>">Lihat Detail<?= official_svg('arrow-right') ?></a></header>
  <div class="dbh-dues">
   <div class="dbh-donut-wrap"><svg class="dbh-donut" viewBox="0 0 120 120" role="img" aria-label="<?= $lunas ?>% tagihan lunas"><circle cx="60" cy="60" r="48" class="trk"/><?php if($lunas>0): ?><circle cx="60" cy="60" r="48" class="val" stroke-dasharray="<?= round($circ*$lunas/100,2) ?> <?= round($circ,2) ?>" transform="rotate(-90 60 60)"/><?php endif; ?><text x="60" y="60" class="pc"><?= $lunas ?>%</text><text x="60" y="77" class="lb">Lunas</text></svg>
    <svg class="dbh-coins" viewBox="0 0 80 64" aria-hidden="true"><g fill="#f7b500" stroke="#d98a00" stroke-width="1.4"><ellipse cx="22" cy="52" rx="18" ry="6"/><path d="M4 44v8c0 3 8 6 18 6s18-3 18-6v-8"/><ellipse cx="22" cy="44" rx="18" ry="6"/><path d="M4 36v8c0 3 8 6 18 6s18-3 18-6v-8"/><ellipse cx="22" cy="36" rx="18" ry="6"/><path d="M4 28v8c0 3 8 6 18 6s18-3 18-6v-8"/><ellipse cx="22" cy="28" rx="18" ry="6"/><ellipse cx="56" cy="54" rx="18" ry="6"/><path d="M38 46v8c0 3 8 6 18 6s18-3 18-6v-8"/><ellipse cx="56" cy="46" rx="18" ry="6"/></g></svg></div>
   <ul><li><i class="dot green"></i><span>Sudah Bayar<b><?= $dpaid ?> warga</b></span></li><li><i class="dot orange"></i><span>Belum Bayar<b><?= max(0,$dtotal-$dpaid) ?> warga</b></span></li><li><i class="dot gray"></i><span>Total Tagihan<b><?= $money($due) ?></b></span></li></ul>
  </div>
  <?php if(!$dtotal): ?><p class="dbh-foot-note">Belum ada tagihan untuk periode <?= html_escape($month_label) ?>.</p><?php endif; ?>
 </section>
 <?php endif; ?>
</div>

<div class="dbh-row dbh-row-b">
 <?php if($fon('events')): ?><section class="dbh-card dbh-c-agenda"><header><h2><span class="dbh-hic orange"><?= official_svg('calendar') ?></span>Agenda Kegiatan</h2><a class="dbh-btn" href="<?= site_url('admin/post/agenda') ?>"><?= official_svg('plus') ?>Tambah Agenda</a></header>
  <div class="dbh-agenda"><?php foreach(array_slice((array)$agendas,0,3) as $item): $t=strtotime($item['event_date']); ?><a href="<?= site_url('admin/post/agenda/'.$item['id']) ?>"><span class="dbh-date"><b><?= date('d',$t) ?></b><small><?= $short_month[(int)date('n',$t)] ?></small></span><span class="dbh-list-tx"><strong><?= html_escape($item['title']) ?></strong><small class="row"><span><?= official_svg('clock') ?><?= $item['event_time']?html_escape(substr($item['event_time'],0,5)).' WIB':'Waktu belum diisi' ?></span><span class="pin"><?= official_svg('pin') ?><?= html_escape($item['event_location']?:'Lokasi belum diisi') ?></span></small></span><span class="dbh-chev"><?= official_svg('chev-right') ?></span></a><?php endforeach; ?>
  <?php if(!$agendas): ?><p class="dbh-empty">Belum ada agenda mendatang. <a href="<?= site_url('admin/post/agenda') ?>">Tambah agenda</a>.</p><?php endif; ?></div>
 </section><?php endif; ?>
 <?php if($letters_latest!==NULL):
  $l_state=array('diajukan'=>array('wait','Menunggu'),'diproses'=>array('proc','Diproses'),'disetujui'=>array('done','Disetujui'),'selesai'=>array('done','Selesai'),'ditolak'=>array('bad','Ditolak')); ?>
 <section class="dbh-card dbh-c-letters"><header><h2><span class="dbh-hic orange"><?= official_svg('doc') ?></span>Pengajuan Surat Terbaru</h2><a class="dbh-link" href="<?= site_url('admin/requests') ?>">Lihat Semua<?= official_svg('arrow-right') ?></a></header>
  <div class="dbh-table"><?php foreach($letters_latest as $l): $st=$l_state[$l['status']]??array('proc',ucfirst($l['status'])); ?><a href="<?= site_url('admin/request_detail/'.(int)$l['id']) ?>"><b class="code">#SR-<?= str_pad((string)(int)$l['id'],4,'0',STR_PAD_LEFT) ?></b><span class="type"><?= html_escape($l['letter_title']?:$l['subject']) ?></span><span class="name"><?= html_escape($l['display_name']?:'Warga') ?></span><span class="date">Dibuat: <?= $when($l['created_at']) ?></span><span class="dbh-pill <?= $st[0] ?>"><?= html_escape($st[1]) ?></span><span class="dbh-chev"><?= official_svg('chev-right') ?></span></a><?php endforeach; ?>
  <?php if(!$letters_latest): ?><p class="dbh-empty">Belum ada pengajuan surat.</p><?php endif; ?></div>
 </section>
 <?php endif; ?>
</div>


<?php if (!empty($ops)): $can_ops=function($p)use($rt_perm,$role){return in_array($role,$rt_perm[$p]??array(),TRUE);};
 $ops_cards=array(
  array('KK Terverifikasi',(int)$ops['kk_active'],(int)$ops['members_active'].' anggota aktif','blue','people','admin/penduduk',$can_ops('penduduk.read')),
  array('Mutasi Menunggu',(int)$ops['mutations_pending'],'Perlu diperiksa','orange','swap','admin/mutasi',$can_ops('mutasi.read')),
  array('Pengaduan Terbuka',(int)$ops['complaints_open'],'Belum selesai','red','alert','admin/pengaduan',$can_ops('pengaduan.read')),
  array('Tunggakan Iuran',$money($ops['arrears_total']),(int)$ops['arrears_count'].' tagihan','purple','money','admin/tunggakan',$can_ops('iuran.report')),
  array('Tindak Lanjut',(int)$ops['actions_overdue'],'Terlambat, dari notulen','sky','check-circle','admin/notulen',$can_ops('notulen.read')),
  array('Peminjaman Terlambat',(int)$ops['loans_overdue'],'Aset belum kembali','amber','box','admin/inventaris/pinjam',$can_ops('aset.read')),
 );
 // Kartu menu yang belum berlangganan/nonaktif tidak menampilkan angkanya (data mengikuti proses langganan).
 if(!isset($this->menu_access))$this->load->library('Menu_access');
 $ops_cards=array_values(array_filter($ops_cards,function($c){$p=strtok($c[5],'?');$m=$this->menu_access->match($p);return $c[6]&&$this->feature_gate->link_ok($p,'admin')&&(!$m||$this->menu_access->commercial($m)==='ALLOW');}));
 $backup_old=!$ops['last_backup']||strtotime($ops['last_backup']['created_at'])<strtotime('-7 days');
 if($ops_cards || ($role==='admin'&&$backup_old)): ?>
<section class="dbh-card dbh-c-ops"><header><h2><span class="dbh-hic orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12h4l3-8 4 16 3-8h6"/></svg></span>Monitoring Operasional</h2></header>
 <?php if($ops_cards): ?><div class="dbh-ops"><?php foreach($ops_cards as $c): ?><a class="dbh-op <?= $c[3] ?>" href="<?= site_url($c[5]) ?>"><span class="dbh-op-ic"><?= official_svg($c[4]) ?></span><span class="dbh-op-tx"><small><?= html_escape($c[0]) ?></small><strong><?= html_escape($c[1]) ?></strong><em><?= html_escape($c[2]) ?></em></span></a><?php endforeach; ?></div><?php endif; ?>
 <?php if($role==='admin'&&$backup_old): ?><form class="dbh-backup dbh-backup-v5" method="post" action="<?= site_url('admin/backup') ?>"><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><input type="hidden" name="act" value="run"><span class="dbh-backup-ic" aria-hidden="true">!</span><span>Belum ada backup berhasil dalam 7 hari terakhir. Jalankan backup sekarang.</span><button type="submit"><?= official_svg('cloud') ?>Backup Sekarang</button></form><?php endif; ?>
</section>
<?php endif; endif; ?>
</div>
