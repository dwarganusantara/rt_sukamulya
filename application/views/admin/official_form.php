<?php
$catalog=official_icon_catalog();
$current_icon=$item['icon']??'👤';
if(!isset($catalog[$current_icon]))$current_icon='👤';
$current_meta=$catalog[$current_icon];
$can_delete_photo=!empty($item['id'])&&!empty($item['photo'])&&in_array($this->session->userdata('admin_role'),array('admin','ketua'),TRUE);
$photo_url=base_url(!empty($item['photo'])?html_escape($item['photo']):'assets/mockup/official-avatar.svg');
$csrf_field='<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'">';
$description=$item['description']??'';
$head=function($icon,$tint,$title,$hint='')
{
 return '<span class="off-tile tint-'.$tint.'">'.official_svg($icon).'</span><div class="off-field-label"><b>'.$title.'</b>'.($hint?'<small>'.$hint.'</small>':'').'</div>';
};
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-officials-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-officials-v2.css') ?>">
<section class="off-hero off-hero-form"><div class="off-hero-copy"><nav class="off-crumb"><a href="<?= site_url('admin/officials') ?>">Manajemen Pengurus</a><i>›</i><a href="<?= site_url('admin/officials') ?>">Struktur Organisasi</a><i>›</i><b><?= $item?'Edit':'Tambah' ?> Posisi</b></nav><div class="off-hero-title"><span class="off-hero-icon"><?= official_svg('pencil') ?></span><div><h1><?= $item?'Edit':'Tambah' ?> Posisi Organisasi</h1><p><?= $item?'Ubah informasi jabatan pengurus RT sesuai kebutuhan.':'Tambahkan jabatan pengurus RT baru ke struktur organisasi.' ?></p></div></div></div><a class="off-back" href="<?= site_url('admin/officials') ?>"><?= official_svg('back') ?>Kembali</a></section>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="off-form-layout">
<form class="off-form-card" method="post" enctype="multipart/form-data">
 <header class="off-card-head"><span class="off-tile off-tile-large tint-orange"><?= official_svg('person') ?></span><div><h2>Informasi Dasar</h2><p>Lengkapi data posisi organisasi sesuai dengan peran dan tanggung jawabnya.</p></div></header>
 <div class="off-form-row">
  <div class="off-field"><?= $head('person-gear','blue','Nama personel') ?><input id="official-name" name="name" required value="<?= html_escape($item['name']??'') ?>"></div>
  <div class="off-field"><?= $head('crown','orange','Nama posisi / jabatan') ?><input id="official-position" name="position" required value="<?= html_escape($item['position']??'') ?>"></div>
 </div>
 <div class="off-field"><?= $head('leaf','green','Ikon jabatan','Pilih ikon yang sesuai untuk jabatan ini.') ?><div class="off-select-icon"><span id="official-icon-current" class="tint-<?= $current_meta[2] ?>"><?= official_svg($current_meta[1]) ?></span><select id="official-icon" name="icon"><?php foreach($catalog as $value=>$meta): ?><option value="<?= $value ?>" <?= $current_icon===$value?'selected':'' ?>><?= html_escape($meta[0]) ?></option><?php endforeach; ?></select></div></div>
 <div class="off-field"><?= $head('doc','blue','Deskripsi tugas','Jelaskan tugas dan tanggung jawab posisi ini.') ?><textarea id="official-description" name="description" rows="3" maxlength="500" placeholder="Jelaskan tanggung jawab posisi ini..."><?= html_escape($description) ?></textarea><small class="off-counter"><span id="official-description-count"><?= mb_strlen($description) ?></span>/500</small></div>
 <div class="off-field"><?= $head('org','blue','Atasan langsung','Pilih posisi yang menjadi atasan langsung (opsional).') ?><select name="parent_id"><option value="">— Posisi utama / tanpa atasan —</option><?php foreach($parents as $parent): ?><option value="<?= (int)$parent['id'] ?>" <?= (int)($item['parent_id']??0)===(int)$parent['id']?'selected':'' ?>><?= html_escape($parent['position'].' — '.$parent['name']) ?></option><?php endforeach; ?></select></div>
 <div class="off-photo-box"><div class="off-photo-head"><?= $head('camera','orange','Foto personel','Format JPG, PNG, WebP, GIF, BMP, atau AVIF. Maksimal 2 MB.') ?></div><div class="off-photo-row"><img id="official-photo-current" src="<?= $photo_url ?>" alt="Foto personel"><label class="off-dropzone" id="official-dropzone"><input id="official-photo-input" type="file" name="photo" accept="<?= image_accept() ?>"><?= official_svg('upload') ?><b>Klik untuk unggah foto</b><span>atau drag &amp; drop file di sini</span><small>JPG, PNG, WebP (maks. 2 MB)</small></label><button id="official-photo-remove" class="off-btn-delete off-photo-remove" type="submit" form="official-photo-delete" <?= $can_delete_photo?'':'hidden' ?>><?= official_svg('trash') ?>Hapus Foto</button></div></div>
 <div class="off-form-row off-form-row-3">
  <div class="off-field"><?= $head('whatsapp','green','Nomor WhatsApp publik','Kosongkan bila tidak ingin ditampilkan.') ?><input name="contact" value="<?= html_escape($item['contact']??'') ?>"></div>
  <div class="off-field"><?= $head('calendar','blue','Mulai menjabat') ?><input type="number" name="term_start" min="1900" max="2100" value="<?= html_escape($item['term_start']??'') ?>"></div>
  <div class="off-field"><?= $head('calendar','blue','Akhir masa jabatan') ?><input type="number" name="term_end" min="1900" max="2100" value="<?= html_escape($item['term_end']??'') ?>"></div>
 </div>
 <div class="off-form-row off-form-row-end">
  <div class="off-field"><?= $head('sort','blue','Urutan bagan','Urutan 0 menjadi posisi utama/ketua.') ?><input type="number" name="sort_order" value="<?= (int)($item['sort_order']??0) ?>"></div>
  <label class="off-switch"><input type="checkbox" name="is_active" value="1" <?= !isset($item['is_active'])||$item['is_active']?'checked':'' ?>><i></i><span><b>Aktif dan tampil di halaman warga</b><small>Posisi ini akan ditampilkan pada struktur organisasi.</small></span></label>
 </div>
 <?= $csrf_field ?>
 <div class="off-form-actions"><button class="off-submit" type="submit"><?= official_svg('save') ?><?= $item?'Simpan Perubahan':'Simpan Posisi' ?></button><a class="off-cancel" href="<?= site_url('admin/officials') ?>"><?= official_svg('close') ?>Batal</a></div>
</form>
<aside class="off-form-side">
 <section class="off-side-card"><header class="off-card-head"><span class="off-tile off-tile-large tint-blue"><?= official_svg('eye') ?></span><div><h2>Preview Kartu Posisi</h2><p>Tampilan yang akan muncul di struktur organisasi.</p></div></header><div class="off-preview"><span id="official-preview-icon" class="off-preview-badge tint-<?= $current_meta[2] ?>"><?= official_svg($current_meta[1]) ?></span><img id="official-photo-preview" src="<?= $photo_url ?>" alt="Preview foto"><h3 id="official-preview-position"><?= html_escape($item['position']??'Nama Jabatan') ?></h3><strong id="official-preview-name"><?= html_escape($item['name']??'Nama Personel') ?></strong><p id="official-preview-description"><?= html_escape($description?:'Deskripsi tugas akan tampil di sini.') ?></p></div></section>
 <section class="off-side-card"><header class="off-card-head"><span class="off-tile off-tile-large tint-red"><?= official_svg('star') ?></span><div><h2>Contoh Ikon Jabatan</h2><p>Pilih ikon yang paling sesuai dengan peran jabatan.</p></div></header><div class="off-icon-grid"><?php foreach($catalog as $value=>$meta): ?><button type="button" class="tint-<?= $meta[2] ?><?= $current_icon===$value?' selected':'' ?>" data-icon="<?= $value ?>" data-tint="<?= $meta[2] ?>" title="<?= html_escape($meta[0]) ?>" aria-label="<?= html_escape($meta[0]) ?>"><?= official_svg($meta[1]) ?></button><?php endforeach; ?></div><div class="off-tips"><span><?= official_svg('bulb') ?></span><div><b>Tips</b><p>Gunakan deskripsi yang jelas dan singkat agar warga memahami tugas dan peran dari posisi ini.</p></div></div></section>
</aside>
</div>
<?php if($can_delete_photo): ?><form id="official-photo-delete" method="post" action="<?= site_url('admin/official_photo_delete/'.$item['id']) ?>" onsubmit="return confirm('Hapus foto personel ini?')"><?= $csrf_field ?></form><?php endif; ?>
<script>
(function(){
 var input=document.getElementById('official-photo-input'),preview=document.getElementById('official-photo-preview'),current=document.getElementById('official-photo-current'),remove=document.getElementById('official-photo-remove'),zone=document.getElementById('official-dropzone'),select=document.getElementById('official-icon'),savedSrc=preview.src,canDeleteSaved=<?= $can_delete_photo?'true':'false' ?>,pendingUrl=null;
 function setPhoto(src){preview.src=src;current.src=src}
 function clearPending(){if(pendingUrl)URL.revokeObjectURL(pendingUrl);pendingUrl=null;input.value='';setPhoto(savedSrc);remove.hidden=!canDeleteSaved}
 function takeFile(){var file=input.files[0];if(!file)return clearPending();if(['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/avif'].indexOf(file.type)<0){alert('Format foto harus JPG, PNG, WebP, GIF, BMP, atau AVIF.');return clearPending()}if(file.size>2*1024*1024){alert('Ukuran foto maksimal 2 MB.');return clearPending()}if(pendingUrl)URL.revokeObjectURL(pendingUrl);pendingUrl=URL.createObjectURL(file);setPhoto(pendingUrl);remove.hidden=false}
 input.addEventListener('change',takeFile);
 remove.addEventListener('click',function(event){if(pendingUrl){event.preventDefault();clearPending()}});
 ['dragenter','dragover'].forEach(function(type){zone.addEventListener(type,function(event){event.preventDefault();zone.classList.add('dragging')})});
 ['dragleave','drop'].forEach(function(type){zone.addEventListener(type,function(event){event.preventDefault();zone.classList.remove('dragging')})});
 zone.addEventListener('drop',function(event){if(event.dataTransfer.files.length){input.files=event.dataTransfer.files;takeFile()}});
 function bindText(id,target,fallback){var field=document.getElementById(id),out=document.getElementById(target);field.addEventListener('input',function(){out.textContent=field.value.trim()||fallback})}
 bindText('official-name','official-preview-name','Nama Personel');
 bindText('official-position','official-preview-position','Nama Jabatan');
 bindText('official-description','official-preview-description','Deskripsi tugas akan tampil di sini.');
 var description=document.getElementById('official-description'),count=document.getElementById('official-description-count');
 description.addEventListener('input',function(){count.textContent=description.value.length});
 var tiles=document.querySelectorAll('.off-icon-grid button');
 function applyIcon(value){tiles.forEach(function(tile){var active=tile.dataset.icon===value;tile.classList.toggle('selected',active);if(active){['official-icon-current','official-preview-icon'].forEach(function(id){var el=document.getElementById(id);el.innerHTML=tile.innerHTML;el.className=el.className.replace(/tint-\w+/,'tint-'+tile.dataset.tint)})}})}
 select.addEventListener('change',function(){applyIcon(select.value)});
 tiles.forEach(function(tile){tile.addEventListener('click',function(){select.value=tile.dataset.icon;applyIcon(tile.dataset.icon)})});
})();
</script>
