<?php
$this->load->helper('family_ui');
$code='KK-'.str_pad($request['id'],6,'0',STR_PAD_LEFT);
$status_info=array('pending'=>array('Menunggu Verifikasi','hourglass','is-pending'),'approved'=>array('Disetujui','check','is-approved'),'rejected'=>array('Ditolak','close','is-rejected'),'revision_requested'=>array('Perlu Perbaikan','edit','is-revision'));
$si=$status_info[$request['status']]??array($request['status'],'info','');
$labels=array('nomor_kk'=>'Nomor KK','alamat'=>'Alamat','rt'=>'RT','rw'=>'RW','kelurahan'=>'Kelurahan / Desa','kecamatan'=>'Kecamatan','kota'=>'Kabupaten / Kota','provinsi'=>'Provinsi','kode_pos'=>'Kode Pos','nama_lengkap'=>'Nama Lengkap','nik'=>'NIK','jenis_kelamin'=>'JK','tempat_lahir'=>'Tempat Lahir','tanggal_lahir'=>'Tanggal Lahir','agama'=>'Agama','pendidikan'=>'Pendidikan','pekerjaan'=>'Pekerjaan','golongan_darah'=>'Gol. Darah','hubungan_keluarga'=>'Hubungan');
$family_icons=array('nomor_kk'=>'card','alamat'=>'home','rt'=>'home','rw'=>'hash','kelurahan'=>'building','kecamatan'=>'pin','kota'=>'building','provinsi'=>'map','kode_pos'=>'mail');
$v=function($x){$x=trim((string)$x);return $x!==''?$x:'-';};
$show=function($key,$value) use($v){ if($key==='tanggal_lahir'&&$value&&strtotime($value)) return date('d-m-Y',strtotime($value)); if($key==='jenis_kelamin') return $value?:'-'; return $v($value); };
$fmt=function($d){return $d?date('d M Y, H:i',strtotime($d)):'-';};
$name=$request['full_name']?:$request['display_name'];
$is_change=(bool)$existing;
$old_family=$existing['keluarga']??array();
$old_members=array(); foreach(($existing['anggota']??array()) as $m) if(!empty($m['nik'])) $old_members[$m['nik']]=$m;
$proposed_niks=array_column($fields['anggota'],'nik');
$removed=array_filter($old_members,function($m) use($proposed_niks){return !in_array($m['nik'],$proposed_niks,TRUE);});
$diff=function($k) use($is_change,$old_family,$fields){return $is_change && (string)($old_family[$k]??'')!==(string)($fields['keluarga'][$k]??'');};
$changed_family=0; foreach($family_fields as $k) if($diff($k)) $changed_family++;
$count=count($fields['anggota']);
$people=array('id'=>(int)$request['id'],'code'=>$code,'name'=>$name);
$done=$request['status']!=='pending';
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-kk.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-kk.css') ?>">
<div class="akk akk-detail">
<nav class="akk-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>"><?= family_icon('home') ?>Beranda</a><?= family_icon('chevron-right') ?><a href="<?= site_url('admin/kk/pengajuan') ?>">Verifikasi KK</a><?= family_icon('chevron-right') ?><span aria-current="page"><?= $code ?></span></nav>
<?php if($msg=$this->session->flashdata('kk_notice')): ?><div class="akk-flash is-ok" role="status"><?= family_icon('check') ?><span><?= html_escape($msg) ?></span></div><?php endif; ?>
<?php if($msg=$this->session->flashdata('kk_error')): ?><div class="akk-flash is-err" role="alert"><?= family_icon('info') ?><span><?= html_escape($msg) ?></span></div><?php endif; ?>

<header class="akk-card akd-head">
 <div class="akd-title">
  <span class="akk-panel-icon is-lg"><?= family_icon('file') ?></span>
  <div><h1>Detail Pengajuan Kartu Keluarga</h1><p><?= $is_change?'Perubahan data Kartu Keluarga':'Pendaftaran data Kartu Keluarga baru' ?> <i aria-hidden="true">·</i> Diajukan pada <?= $fmt($request['created_at']) ?></p></div>
  <span class="akk-badge <?= $si[2] ?> akd-status"><?= family_icon($si[1]) ?><?= $si[0] ?></span>
 </div>
 <dl class="akd-meta">
  <div><span class="akd-meta-ic is-avatar"><?= family_icon('person') ?></span><span><dt>Pemohon</dt><dd><?= html_escape($name) ?></dd><small>@<?= html_escape($request['username']) ?></small></span></div>
  <div><span class="akd-meta-ic"><?= family_icon('phone') ?></span><span><dt>No. HP</dt><dd><?php if(!empty($request['phone'])): ?><a href="tel:<?= html_escape(preg_replace('/[^\d+]/','',$request['phone'])) ?>"><?= html_escape($request['phone']) ?></a><?php else: ?>-<?php endif; ?></dd></span></div>
  <div><span class="akd-meta-ic"><?= family_icon('file') ?></span><span><dt>Jenis Pengajuan</dt><dd><?= $is_change?'Perubahan Data':'Pendaftaran Baru' ?></dd></span></div>
  <div><span class="akd-meta-ic"><?= family_icon('people') ?></span><span><dt>Jumlah Anggota</dt><dd><?= $count ?> Orang</dd><?php if($is_change): ?><small>sebelumnya <?= count($existing['anggota']) ?> orang</small><?php endif; ?></span></div>
  <div><span class="akd-meta-ic"><?= family_icon('calendar') ?></span><span><dt>Tanggal Pengajuan</dt><dd><?= $fmt($request['created_at']) ?></dd></span></div>
 </dl>
</header>

<div class="akd-layout">
 <aside class="akd-side">
  <div class="akk-card akd-nav">
   <div class="akd-doc"><div class="akd-doc-art" aria-hidden="true"><?= family_document_art() ?><span><?= family_icon('check') ?></span></div><strong>Kartu Keluarga</strong><p>Data hasil pembacaan OCR dari dokumen KK<?= $is_change?', dibandingkan dengan data terverifikasi.':'.' ?></p></div>
   <ol class="akd-steps" id="akd-steps">
    <li><a href="#data-kk" class="is-active"><b>1</b><span><strong>Data KK</strong><small>Data identitas Kartu Keluarga</small></span></a></li>
    <li><a href="#anggota"><b>2</b><span><strong>Anggota Keluarga</strong><small>Data seluruh anggota (<?= $count ?> orang)</small></span></a></li>
    <li><a href="#keputusan" class="<?= $done?'is-done':'' ?>"><b><?= $done?family_icon('check'):'3' ?></b><span><strong>Keputusan</strong><small><?= $done?'Sudah '.strtolower($si[0]):'Setujui, tolak, atau minta perbaikan' ?></small></span></a></li>
   </ol>
  </div>
 </aside>

 <div class="akd-main">
  <section class="akk-card akd-section" id="data-kk">
   <header class="akd-section-head"><h2><span class="akk-orb"><?= family_icon('card') ?></span>Data Kartu Keluarga</h2><?php if($is_change): ?><span class="akd-chip <?= $changed_family?'is-warn':'is-ok' ?>"><?= family_icon($changed_family?'edit':'check') ?><?= $changed_family?$changed_family.' field berubah':'Tidak ada perubahan' ?></span><?php endif; ?></header>
   <div class="akd-fields">
    <?php foreach($family_fields as $k): $val=$fields['keluarga'][$k]??''; $d=$diff($k); ?>
    <div class="akd-field f-<?= $k ?> <?= $d?'is-diff':'' ?> <?= ($val===''&&in_array($k,array('nomor_kk','alamat'),TRUE))?'is-empty':'' ?>">
     <span class="akd-field-ic"><?= family_icon($family_icons[$k]) ?></span>
     <span><small><?= $labels[$k] ?></small><strong class="<?= in_array($k,array('nomor_kk','kode_pos'),TRUE)?'akk-mono':'' ?>"><?= html_escape($v($val)) ?></strong><?php if($d): ?><em>Sebelumnya: <?= html_escape($v($old_family[$k]??'')) ?></em><?php endif; ?></span>
    </div>
    <?php endforeach; ?>
   </div>
  </section>

  <section class="akk-card akd-section" id="anggota">
   <header class="akd-section-head"><h2><span class="akk-orb"><?= family_icon('people') ?></span>Daftar Anggota Keluarga (<?= $count ?> Orang)</h2><span class="akd-chip is-ok"><?= family_icon('check') ?>Data diambil dari hasil OCR dan dapat diperiksa kembali.</span></header>
   <div class="akk-table-wrap"><table class="akk-table akd-members">
    <thead><tr><th>No.</th><th>Nama Lengkap</th><th>NIK</th><th class="akk-c">JK</th><th>Tempat Lahir</th><th>Tanggal Lahir</th><th>Agama</th><th>Pekerjaan</th><th>Hubungan</th><?php if($is_change): ?><th>Keterangan</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach($fields['anggota'] as $i=>$m): $old=$old_members[$m['nik']]??NULL; $changes=array(); if($old) foreach($member_fields as $k) if((string)($old[$k]??'')!==(string)($m[$k]??'')) $changes[]=$k;
     $cell=function($k,$inner) use($changes,$old,$show){$is=in_array($k,$changes,TRUE);return '<td class="'.($is?'is-diff':'').'"'.($is?' title="Sebelumnya: '.html_escape($show($k,$old[$k]??'')).'"':'').'>'.$inner.'</td>';};
     $missing=$m['fields_need_review']??array(); ?>
     <tr class="<?= $is_change&&!$old?'is-new':'' ?>">
      <td><?= $i+1 ?></td>
      <?= $cell('nama_lengkap','<strong>'.html_escape($v($m['nama_lengkap'])).'</strong>') ?>
      <?= $cell('nik','<span class="akk-mono">'.html_escape($v($m['nik'])).'</span>') ?>
      <td class="akk-c"><span class="akd-jk <?= $m['jenis_kelamin']==='P'?'is-p':'' ?>"><?= html_escape($show('jenis_kelamin',$m['jenis_kelamin'])) ?></span></td>
      <?= $cell('tempat_lahir',html_escape($v($m['tempat_lahir']))) ?>
      <?= $cell('tanggal_lahir','<span class="akk-date">'.family_icon('calendar').html_escape($show('tanggal_lahir',$m['tanggal_lahir'])).'</span>') ?>
      <?= $cell('agama',html_escape($v($m['agama'])).'<small>Gol. darah: '.html_escape($v($m['golongan_darah'])).'</small>') ?>
      <?= $cell('pekerjaan',html_escape($v($m['pekerjaan'])).'<small>'.html_escape($v($m['pendidikan'])).'</small>') ?>
      <?= $cell('hubungan_keluarga','<strong>'.html_escape($v($m['hubungan_keluarga'])).'</strong>') ?>
      <?php if($is_change): ?><td><?php if(!$old): ?><span class="akk-tag is-new">Anggota baru</span><?php elseif($changes): ?><span class="akk-tag is-diff"><?= count($changes) ?> field berubah</span><?php else: ?><span class="akk-tag is-same">Tetap</span><?php endif; ?></td><?php endif; ?>
     </tr>
     <?php if($missing): ?><tr class="akd-missing"><td></td><td colspan="<?= $is_change?9:8 ?>"><?= family_icon('info') ?>Belum terisi: <?= html_escape(implode(', ',array_map(function($k) use($labels){return $labels[$k]??$k;},$missing))) ?></td></tr><?php endif; ?>
    <?php endforeach; ?>
    </tbody>
   </table></div>
   <?php if($removed): ?><div class="akk-removed"><?= family_icon('info') ?><div><strong><?= count($removed) ?> anggota terdaftar tidak ada di usulan ini</strong><p><?= html_escape(implode(', ',array_map(function($m){return $m['nama_lengkap'];},$removed))) ?>. Data mereka tetap tersimpan; penghapusan anggota memerlukan proses mutasi terpisah.</p></div></div><?php endif; ?>
   <p class="akd-legend">Baris kecil di bawah Agama dan Pekerjaan berisi golongan darah dan pendidikan.<?= $is_change?' Sel kuning = berubah (arahkan kursor untuk melihat data sebelumnya); baris hijau = anggota baru.':'' ?></p>
  </section>
 </div>
</div>

<section class="akk-card akd-decision <?= $done?$si[2]:'' ?>" id="keputusan">
 <span class="akd-decision-ic"><?= family_icon($done?$si[1]:'info') ?></span>
 <?php if(!$done): ?>
 <div class="akd-decision-copy"><strong>Keputusan Verifikasi</strong><p>Pastikan data sesuai dengan Kartu Keluarga asli sebelum menyetujui.<br>Data yang disetujui langsung tampil di akun warga.</p></div>
 <div class="akd-decision-actions">
  <button type="button" class="akk-btn is-ghost" data-decide="revision_requested" data-id="<?= $people['id'] ?>" data-code="<?= $code ?>" data-name="<?= html_escape($name) ?>"><?= family_icon('edit') ?>Minta Perbaikan</button>
  <button type="button" class="akk-btn is-no" data-decide="rejected" data-id="<?= $people['id'] ?>" data-code="<?= $code ?>" data-name="<?= html_escape($name) ?>"><?= family_icon('close') ?>Tolak</button>
  <button type="button" class="akk-btn is-ok" data-decide="approved" data-id="<?= $people['id'] ?>" data-code="<?= $code ?>" data-name="<?= html_escape($name) ?>"><?= family_icon('check') ?>Setujui</button>
 </div>
 <?php else: ?>
 <div class="akd-decision-copy"><strong>Pengajuan ini sudah <?= strtolower($si[0]) ?><?= $request['reviewed_at']?' pada '.$fmt($request['reviewed_at']):'' ?><?= $reviewer?' oleh '.html_escape($reviewer):'' ?>.</strong><p><?= html_escape($request['review_notes']?:'Tidak ada catatan.') ?></p></div>
 <a class="akk-btn is-ghost" href="<?= site_url('admin/kk/pengajuan') ?>"><?= family_icon('back') ?>Kembali ke daftar</a>
 <?php endif; ?>
</section>
</div>
<?php $this->load->view('admin/kk_decision_dialog',array('return_to'=>'admin/kk/pengajuan/'.(int)$request['id'])); ?>
<script src="<?= base_url('assets/admin-kk.js') ?>?v=<?= filemtime(FCPATH.'assets/admin-kk.js') ?>" defer></script>
