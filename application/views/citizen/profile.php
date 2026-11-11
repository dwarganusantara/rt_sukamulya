<?php
$title='Profil Saya | '.($settings['site_name']??'Sukamulya');
$citizen_dashboard=TRUE; $citizen_shell=TRUE; $shell_active='profile';
$this->load->view('site/header',compact('title','settings','menu','citizen_dashboard','citizen_shell','shell_active'));
$name=$citizen['display_name']??'-';
$fullName=($citizen['full_name']??'')?:$name;
$months=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$dateLabel=function($value)use($months){$t=strtotime((string)$value);return $t?date('d',$t).' '.$months[(int)date('n',$t)].' '.date('Y',$t):'-';};
$profilePhotoUrl=base_url(html_escape($profile_photo));
$csrf='<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'">';
$svg=function($path,$size=20,$fill=TRUE){return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="'.($fill?'currentColor':'none').'" stroke="'.($fill?'none':'currentColor').'" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$path.'"></path></svg>';};
$p=array('user'=>'M12 12c2.210 0 4-1.790 4-4s-1.790-4-4-4-4 1.790-4 4 1.790 4 4 4zm0 2c-2.670 0-8 1.340-8 4v2h16v-2c0-2.660-5.330-4-8-4z','users'=>'M16 11c1.660 0 2.990-1.340 2.990-3S17.660 5 16 5c-1.660 0-3 1.340-3 3s1.340 3 3 3zm-8 0c1.660 0 2.990-1.340 2.990-3S9.660 5 8 5C6.340 5 5 6.340 5 8s1.340 3 3 3zm0 2c-2.330 0-7 1.170-7 3.500V19h14v-2.500c0-2.330-4.670-3.500-7-3.500zm8 0c-.29 0-.62.02-.97.05 1.160.84 1.970 1.970 1.970 3.450V19h6v-2.500c0-2.330-4.670-3.500-7-3.500z','calendar'=>'M19 4h-1V2h-2v2H8V2H6v2H5c-1.100 0-2 .9-2 2v14c0 1.100.9 2 2 2h14c1.100 0 2-.9 2-2V6c0-1.100-.9-2-2-2zm0 16H5V9h14v11zM7 11h4v4H7z','shield'=>'M12 1 3 5v6c0 5.550 3.840 10.740 9 12 5.160-1.260 9-6.450 9-12V5l-9-4zm-2 16-4-4 1.410-1.410L10 14.170l6.590-6.590L18 9l-8 8z','doc'=>'M14 2H6c-1.100 0-2 .9-2 2v16c0 1.100.9 2 2 2h12c1.100 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.500L18.500 9H13z','wallet'=>'M20 7V5a2 2 0 0 0-2-2H5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h15a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 5h13v2H5a1 1 0 0 1 0-2zm12 10a2 2 0 1 1 0-4 2 2 0 0 1 0 4z','image'=>'M21 19V5c0-1.100-.9-2-2-2H5c-1.100 0-2 .9-2 2v14c0 1.100.9 2 2 2h14c1.100 0 2-.9 2-2zM8.500 13.500l2.500 3.010L14.500 12l4.500 6H5l3.500-4.500z','pencil'=>'M3 17.250V21h3.750L17.810 9.940l-3.750-3.750L3 17.250zM20.710 7.040a1 1 0 0 0 0-1.410l-2.340-2.340a1 1 0 0 0-1.410 0l-1.830 1.830 3.750 3.750 1.830-1.830z','pin'=>'M12 2C8.130 2 5 5.130 5 9c0 5.250 7 13 7 13s7-7.750 7-13c0-3.870-3.130-7-7-7zm0 9.500a2.500 2.500 0 1 1 0-5 2.500 2.500 0 0 1 0 5z','phone'=>'M6.600 10.800a15 15 0 0 0 6.600 6.600l2.200-2.200a1 1 0 0 1 1-.25 11.400 11.400 0 0 0 3.600.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.500a1 1 0 0 1 1 1c0 1.250.2 2.450.57 3.600a1 1 0 0 1-.25 1z','mail'=>'M20 4H4c-1.100 0-2 .9-2 2v12c0 1.100.9 2 2 2h16c1.100 0 2-.9 2-2V6c0-1.100-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z','camera'=>'M9 2 7.170 4H4c-1.100 0-2 .9-2 2v12c0 1.100.9 2 2 2h16c1.100 0 2-.9 2-2V6c0-1.100-.9-2-2-2h-3.170L15 2H9zm3 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6z','upload'=>'M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z','arrow'=>'M4 12h16M13 5l7 7-7 7','alert'=>'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z','grid'=>'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z','mega'=>'M4 9c-1.100 0-2 .9-2 2v2c0 1.100.9 2 2 2h1v4h2v-4h1l5 3V6L8 9H4zm11.500 3c0-1.330-.58-2.530-1.500-3.350v6.690c.92-.81 1.500-2.010 1.500-3.340zM18 11v2h4v-2h-4z','gear'=>'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM12 3v3M12 18v3M3 12h3M18 12h3M5.600 5.600l2.100 2.100M16.300 16.300l2.100 2.100M5.600 18.400l2.100-2.100M16.300 7.700l2.100-2.100');
$stats=array(
 array('doc',$profile_counts['requests'],'Surat Saya',$profile_counts['requests']?'Pengajuan surat tercatat':'Belum ada pengajuan surat','warga/riwayat','orange'),
 array('wallet',$profile_counts['paid_dues'],'Iuran Terbayar',$profile_counts['paid_dues']?'Tagihan sudah lunas':'Belum ada pembayaran','warga/iuran','blue'),
 array('calendar',$profile_counts['agendas'],'Agenda Mendatang','Kegiatan yang akan datang','agenda','green'),
 array('image',$profile_counts['gallery'],'Galeri Kegiatan','Foto kegiatan lingkungan','galeri','purple'),
);
$rows=array(
 array('user','orange','Nama Lengkap',$fullName),
 array('phone','amber','Nomor HP',$citizen['phone']??''),
 array('@','blue','Username',$citizen['username']??'-'),
 array('mail','pink','Email',$citizen['email']??''),
 array('shield','green','Status','status'),
 array('calendar','purple','Tanggal Terdaftar',$dateLabel($citizen['created_at']??'')),
 array('pin','purple','Alamat',$citizen['address']??''),
 array('users','blue','Role','Warga'),
);
$shortcuts=array(
 array('users','mint','Data Keluarga (KK)','Lihat dan daftarkan anggota KK','warga/keluarga'),
 array('doc','rose','Ajukan Surat','Pengajuan surat ke RT','warga/layanan'),
 array('wallet','sky','Lihat Iuran','Tagihan dan pembayaran','warga/iuran'),
 array('mega','peach','Pengumuman','Informasi terbaru dari RT','pengumuman'),
 array('calendar','mint','Agenda Kegiatan','Jadwal dan acara lingkungan','agenda'),
 array('image','lilac','Galeri Kegiatan','Foto kegiatan warga','galeri'),
 array('users','sky','Data Saya','Ubah profil dan akun warga','warga/edit-profil'),
 array('gear','rose','Pengaturan','Ubah password akun','warga/password'),
);
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="pfx-page pfy-page crd-page">
 <section class="pfy-hero"><img src="<?= base_url('assets/mockup/profile-hero-v2.png') ?>" alt=""><div class="pfx-wrap"><nav class="pfx-crumb" aria-label="Breadcrumb"><a href="<?= site_url('warga') ?>"><?= $svg('M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z',14) ?>Beranda</a><i>›</i><b>Profil Saya</b></nav><h1>Profil <b>Saya</b></h1><p>Informasi akun warga yang tersimpan di portal.</p></div></section>
 <div class="pfx-wrap pfy-content">
  <?php if($this->session->flashdata('profile_notice')): ?><div class="portal-success pfx-feedback"><?= html_escape($this->session->flashdata('profile_notice')) ?></div><?php endif; ?>
  <?php if($this->session->flashdata('profile_error')): ?><div class="error pfx-feedback"><?= html_escape($this->session->flashdata('profile_error')) ?></div><?php endif; ?>
  <p class="error pfx-feedback" id="pfx-photo-error" role="alert" hidden></p>
  <section class="pfy-stats" aria-label="Ringkasan akun"><?php foreach($stats as $s): ?><a class="<?= $s[5] ?>" href="<?= site_url($s[4]) ?>"><span><?= $svg($p[$s[0]],28) ?></span><div><strong><?= $s[2] ?></strong><b><?= (int)$s[1] ?></b><small><?= $s[3] ?></small></div><i><?= $svg($p['arrow'],17,FALSE) ?></i></a><?php endforeach; ?></section>
  <div class="pfy-columns">
   <section class="pfx-card">
    <header class="pfx-head"><span class="orange"><?= $svg($p['user'],25) ?></span><div><h2>Informasi Pribadi</h2><p>Berikut adalah informasi akun warga yang terdaftar di sistem.</p></div><a class="pfx-edit-main" href="<?= site_url('warga/edit-profil') ?>"><?= $svg($p['pencil'],16) ?>Edit Profil</a></header>
    <div class="pfy-rows"><?php foreach($rows as $r): ?><div class="pfy-row"><span class="pfx-row-icon <?= $r[1] ?>"><?= $r[0]==='@'?'<b>@</b>':$svg($p[$r[0]],20) ?></span><span class="pfx-row-label"><?= $r[2] ?></span><div class="pfx-row-value"><?php if($r[3]==='status'): ?><span class="pfx-badge"><i></i>Warga Aktif</span><?php elseif($r[3]!==''): ?><strong><?= html_escape($r[3]) ?></strong><?php else: ?><em>Belum dilengkapi</em><?php endif; ?></div></div><?php endforeach; ?></div>
   </section>
   <section class="pfx-card pfx-photo">
    <header class="pfx-head"><span class="blue"><?= $svg($p['camera'],24) ?></span><div><h2>Foto Profil</h2><p>Foto profil akan ditampilkan pada halaman warga dan digunakan untuk identitas akun.</p></div></header>
    <div class="pfx-photo-body"><div class="pfx-photo-big"><img id="pfx-photo-preview" src="<?= $profilePhotoUrl ?>" alt="Foto profil <?= html_escape($name) ?>"><label for="pfx-photo" aria-label="Ganti foto profil"><?= $svg($p['camera'],21) ?></label></div>
     <div class="pfx-photo-tools"><form method="post" enctype="multipart/form-data" id="pfx-photo-form" action="<?= site_url('warga/profil') ?>"><?= $csrf ?><input id="pfx-photo" type="file" name="profile_photo" accept="<?= image_accept() ?>"><label class="pfx-drop" for="pfx-photo"><span><?= $svg($p['upload'],22) ?></span><strong id="pfx-photo-label">Upload / Ganti Foto</strong><small>JPG, PNG, WebP, GIF, BMP, AVIF (Maks. 2MB)</small></label></form>
      <p class="pfx-tip"><?= $svg($p['alert'],20) ?>Foto profil yang sopan dan jelas akan memudahkan pengurus mengenali Anda.</p>
      <?php if(!empty($has_profile_photo)): ?><form method="post" action="<?= site_url('warga/profil') ?>" onsubmit="return confirm('Hapus foto profil dan kembali ke avatar bawaan?')"><?= $csrf ?><input type="hidden" name="profile_action" value="delete"><button class="pfx-remove" type="submit">Hapus foto</button></form><?php endif; ?></div></div>
   </section>
  </div>
  <section class="pfx-card"><header class="pfx-head small"><span class="orange"><?= $svg($p['grid'],22) ?></span><div><h2>Akses Cepat</h2><p>Pilih layanan yang paling sering Anda gunakan.</p></div><a class="pfx-all" href="<?= site_url('warga/layanan') ?>">Lihat Semua Layanan <?= $svg($p['arrow'],14,FALSE) ?></a></header>
   <nav class="pfy-shortcuts" aria-label="Akses cepat"><?php foreach($shortcuts as $s): ?><a class="<?= $s[1] ?>" href="<?= site_url($s[4]) ?>"><span><?= $svg($p[$s[0]],26,$s[0]!=='gear') ?></span><div><strong><?= $s[2] ?></strong><small><?= $s[3] ?></small></div><i><?= $svg($p['arrow'],13,FALSE) ?></i></a><?php endforeach; ?></nav>
  </section>
 </div>
<?php $this->load->view('citizen/red_deco'); ?>
</main>
<script>
(function(){
 var input=document.getElementById('pfx-photo'),form=document.getElementById('pfx-photo-form'),preview=document.getElementById('pfx-photo-preview'),label=document.getElementById('pfx-photo-label'),error=document.getElementById('pfx-photo-error');
 input.addEventListener('change',function(){
  var file=input.files[0];error.hidden=true;
  if(!file)return;
  var message=['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/avif'].indexOf(file.type)<0?'Format foto harus JPG, PNG, WebP, GIF, BMP, atau AVIF.':(file.size>2*1024*1024?'Ukuran foto maksimal 2 MB.':'');
  if(message){input.value='';error.textContent=message;error.hidden=false;return}
  preview.src=URL.createObjectURL(file);label.textContent='Mengunggah foto...';form.submit();
 });
})();
</script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
