<?php
$can_publish=in_array($this->session->userdata('admin_role'),array('admin','ketua'),TRUE);
$action=$post?'Edit':'Tambah';
$is_agenda=$type==='agenda';$is_galeri=$type==='galeri';$is_news=$type==='pengumuman';
$conf=array(
 'pengumuman'=>array('label'=>'Pengumuman','parent'=>'Pengumuman','icon'=>'megaphone','sub'=>$post?'Ubah pengumuman yang disampaikan kepada warga RT.':'Buat pengumuman baru untuk disampaikan kepada warga RT.','info'=>'Lengkapi informasi pengumuman yang akan dipublikasikan.','placeholder'=>'Contoh: Kerja Bakti Lingkungan','body'=>'Tulis isi pengumuman di sini...','photo'=>'Unggah gambar untuk memperjelas pengumuman. JPG, PNG, atau WebP maksimal 4 MB.','status'=>'Pilih status pengumuman.'),
 'agenda'=>array('label'=>'Agenda','parent'=>'Agenda Kegiatan','icon'=>'calendar','sub'=>$post?'Ubah agenda kegiatan yang diinformasikan kepada warga RT.':'Buat agenda kegiatan untuk diinformasikan kepada warga RT.','info'=>'Lengkapi informasi agenda kegiatan yang akan ditampilkan.','placeholder'=>'Contoh: Kerja Bakti Lingkungan','body'=>'Tulis detail agenda di sini...','photo'=>'Unggah gambar untuk memperjelas agenda. JPG, PNG, atau WebP maksimal 4 MB.','status'=>'Pilih status agenda. Jika diterbitkan, agenda akan tampil di halaman utama.'),
 'galeri'=>array('label'=>'Galeri','parent'=>'Galeri Kegiatan','icon'=>'image','sub'=>$post?'Ubah foto atau poster dokumentasi kegiatan RT.':'Unggah foto atau poster untuk dokumentasi kegiatan RT.','info'=>'Lengkapi informasi galeri kegiatan yang akan ditampilkan di website RT.','placeholder'=>'Contoh: Kerja Bakti Lingkungan','body'=>'Tulis keterangan foto atau informasi kegiatan di sini...','photo'=>'Unggah foto kegiatan. Format JPG, PNG, atau WebP. Maksimal 4 MB per file.','status'=>'Pilih status publikasi galeri.'),
);
$c=$conf[$type];
$title_value=$post['title']??'';$body_value=$post['body']??'';$image=!empty($post['image'])?base_url(html_escape($post['image'])):'';
$limit=cfx_text_limit($body_value,1000);
$current_status=$post['status']??'draft';
$title_field='<input id="post-title" name="title" required maxlength="180" placeholder="'.$c['placeholder'].'" value="'.html_escape($title_value).'" data-preview-text="#preview-title" data-empty="Judul '.$c['label'].'">';
$body_field='<textarea id="post-body" name="body" rows="'.($is_news?4:3).'" maxlength="'.$limit.'" placeholder="'.$c['body'].'" data-preview-text="#preview-body" data-empty="Isi '.strtolower($c['label']).' akan ditampilkan di sini setelah disimpan."'.($is_agenda?' required':'').'>'.html_escape($body_value).'</textarea>';
$status_options='<option value="draft"'.($current_status==='draft'?' selected':'').'>Draf</option><option value="pending"'.($current_status==='pending'?' selected':'').'>Menunggu persetujuan</option>'.($can_publish?'<option value="published"'.($current_status==='published'?' selected':'').'>Terbit</option>':'');
$status_field='<select id="post-status" name="status">'.$status_options.'</select>';
$upload_variant=$is_galeri?'inside':'outside';
$upload=cfx_upload($upload_variant,'image','orange','Klik atau seret gambar ke sini','JPG, PNG, atau WebP (maks. 4 MB)','Pilih Gambar',(bool)$image,$is_galeri&&!$post);
$consent=$is_galeri?'<label class="cfx-consent"><input type="checkbox" name="photo_consent" value="1"><span>Saya sudah memeriksa izin publikasi foto ini.</span></label>':'';
$tips=array(
 'pengumuman'=>cfx_tips('Tips Pengumuman','',array('Gunakan judul yang singkat dan jelas.','Sampaikan informasi dengan bahasa yang mudah dipahami.','Tambahkan gambar atau poster agar lebih menarik.','Pastikan informasi yang dipublikasikan sudah benar.')),
 'agenda'=>cfx_tips('Tips Agenda','',array('Gunakan judul yang singkat dan jelas.','Cantumkan tanggal, waktu, dan lokasi dengan benar.','Tambahkan gambar atau poster agar lebih menarik.','Informasi yang lengkap memudahkan warga untuk hadir.')),
 'galeri'=>cfx_tips('Tips Mengunggah Galeri','Agar tampilan galeri lebih menarik, perhatikan hal berikut:',array('Gunakan gambar dengan kualitas baik dan jelas.','Pilih foto yang relevan dengan kegiatan RT.','Ukuran gambar maksimal 4 MB per file.','Format yang didukung: JPG, PNG, atau WebP.','Isi judul dan keterangan agar warga mudah memahami.')),
);
$media='<span class="cfx-media-empty" data-image-empty'.($image?' hidden':'').'>'.official_svg('image').'</span><img data-image-preview src="'.$image.'" alt="Pratinjau gambar"'.($image?'':' hidden').'>';
$agenda_time=substr((string)($post['event_time']??''),0,5);
?>
<?= cfx_assets() ?>
<?= cfx_hero($type,$c['icon'],$action.' '.$c['label'],$c['sub'],array(array('Beranda','admin'),array($c['parent'],'admin/posts/'.$type),array($action.' '.$c['label'],'')),$is_agenda?'admin/posts/agenda':'') ?>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="cfx-layout" data-content-form>
<form class="cfx-card cfx-form" method="post" enctype="multipart/form-data">
 <?= cfx_head('doc','blue','Informasi '.$c['label'],$c['info']) ?>
 <?php if($is_news): ?>
  <?= cfx_side('doc','blue',cfx_label('post-title','Judul Pengumuman').cfx_control('image',$title_field).'<small class="cfx-hint">Tulis judul yang singkat dan jelas.</small>') ?>
  <?= cfx_side('pencil','blue',cfx_label('post-body','Isi / Keterangan').$body_field.'<div class="cfx-under"><small class="cfx-hint">Sampaikan detail informasi dengan jelas dan lengkap.</small>'.cfx_counter('post-body',$body_value,$limit).'</div>') ?>
  <?= cfx_side('image','blue','<div class="cfx-label">Foto / Poster <span>(Opsional)</span></div><small class="cfx-hint cfx-hint-top">'.$c['photo'].'</small>'.$upload) ?>
  <?= cfx_side('list','blue',cfx_label('post-status','Status').$status_field.'<small class="cfx-hint">'.$c['status'].'</small>') ?>
 <?php else: ?>
  <div class="cfx-field"><?= cfx_label('post-title',$is_galeri?'Judul':'Judul Agenda',TRUE) ?><?= cfx_control($is_galeri?'T':'doc',$title_field) ?></div>
  <?php if($is_agenda): ?>
   <div class="cfx-field"><?= cfx_label('post-date','Tanggal Acara',TRUE) ?><?= cfx_control('calendar','<input id="post-date" type="date" name="event_date" required value="'.html_escape($post['event_date']??'').'">') ?></div>
   <div class="cfx-grid">
    <div class="cfx-field"><?= cfx_label('post-time','Waktu Acara',TRUE) ?><?= cfx_control('clock','<input id="post-time" type="time" name="event_time" required value="'.html_escape($agenda_time).'">') ?></div>
    <div class="cfx-field"><?= cfx_label('post-location','Lokasi Acara',TRUE) ?><?= cfx_control('pin','<input id="post-location" name="event_location" required maxlength="160" placeholder="Contoh: Lapangan RT 009/008" value="'.html_escape($post['event_location']??'').'" data-preview-text="#preview-location" data-empty="Lokasi acara">') ?></div>
   </div>
  <?php endif; ?>
  <div class="cfx-field"><?= cfx_label('post-body','Isi / Keterangan',$is_agenda) ?><?= cfx_control('pencil',$body_field,TRUE) ?><div class="cfx-under cfx-under-end"><?= cfx_counter('post-body',$body_value,$limit) ?></div></div>
  <?php if($is_galeri): ?><hr class="cfx-rule"><?= cfx_head('image','purple','Foto / Poster <em class="cfx-star">*</em>',$c['photo']) ?><?= $upload ?><?= $consent ?>
  <div class="cfx-field cfx-field-after"><?= cfx_label('post-status','Status',TRUE) ?><?= cfx_control('tag',$status_field) ?><small class="cfx-hint"><?= $c['status'] ?></small></div>
  <?php else: ?>
   <?= cfx_side('image','purple','<div class="cfx-label">Foto / Poster <span>(Opsional)</span></div><small class="cfx-hint cfx-hint-top">'.$c['photo'].'</small>'.$upload) ?>
   <?= cfx_side('tag','blue',cfx_label('post-status','Status').$status_field.'<small class="cfx-hint">'.$c['status'].'</small>') ?>
  <?php endif; ?>
 <?php endif; ?>
 <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
 <?= cfx_actions('Simpan '.$c['label'],'admin/posts/'.$type) ?>
</form>
<aside class="cfx-side-col">
 <?php if(!$is_galeri): ?><?= $tips[$type] ?><?php endif; ?>
 <section class="cfx-card">
  <?php if($is_galeri): ?>
   <?= cfx_head('eye','green','Preview Gambar','Gambar yang diunggah akan ditampilkan di sini.') ?>
   <div class="cfx-preview-gallery"><img data-image-preview src="<?= $image ?>" alt="Pratinjau gambar"<?= $image?'':' hidden' ?>><div class="cfx-gallery-empty" data-image-empty<?= $image?' hidden':'' ?>><span><?= official_svg('image') ?></span><b>Belum ada gambar</b><small>Upload gambar untuk melihat pratinjau.</small></div></div>
  <?php elseif($is_agenda): ?>
   <?= cfx_head('eye','green','Preview Agenda','Berikut contoh tampilan agenda di halaman beranda.') ?>
   <div class="cfx-preview-box"><div class="cfx-agenda-top"><div class="cfx-agenda-date"><b id="preview-day">--</b><small id="preview-month">Bulan</small></div><div class="cfx-agenda-info"><h3 id="preview-title"><?= html_escape($title_value?:'Judul Agenda') ?></h3><p><?= official_svg('clock') ?><span id="preview-time">--:--</span></p><p class="cfx-pin"><?= official_svg('pin') ?><span id="preview-location"><?= html_escape(($post['event_location']??'')?:'Lokasi acara') ?></span></p></div><div class="cfx-agenda-media"><?= $media ?></div></div><p class="cfx-preview-body" id="preview-body"><?= html_escape($body_value?:'Isi agenda akan ditampilkan di sini setelah disimpan.') ?></p></div>
  <?php else: ?>
   <?= cfx_head('eye','green','Preview Pengumuman','Berikut contoh tampilan pengumuman di halaman beranda.') ?>
   <div class="cfx-preview-box cfx-news"><div class="cfx-news-top"><div class="cfx-news-media"><?= $media ?></div><div class="cfx-news-info"><span class="cfx-pill">PENGUMUMAN</span><h3 id="preview-title"><?= html_escape($title_value?:'Judul Pengumuman') ?></h3><p class="cfx-preview-body" id="preview-body"><?= html_escape($body_value?:'Isi pengumuman akan ditampilkan di sini setelah disimpan.') ?></p></div></div><div class="cfx-news-foot"><span><?= official_svg('calendar') ?><?= cfx_date($post['created_at']??'') ?></span><b>Baca selengkapnya →</b></div></div>
  <?php endif; ?>
 </section>
 <?php if($is_galeri): ?><?= $tips[$type] ?><?php endif; ?>
</aside>
</div>
<?= cfx_script() ?>
