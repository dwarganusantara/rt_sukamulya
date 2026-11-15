<?php
$field=function($name,$label,$icon,$tint,$default='',$required=FALSE)use($settings){
 return '<div class="lsv-field"><label for="'.$name.'">'.$label.'</label><div class="lsv-control tint-'.$tint.'"><span>'.official_svg($icon).'</span><input id="'.$name.'" name="'.$name.'" data-preview maxlength="'.($name==='letter_postal'?20:180).'"'.($required?' required':'').' value="'.html_escape($settings[$name]??$default).'"></div></div>';
};
$assets=array(
 'letter_logo'=>array('Logo pemerintah / RT','Logo','logo'),
 'letter_signature'=>array('Tanda tangan','Tanda Tangan','signature'),
 'letter_stamp'=>array('Stempel','Stempel','stamp'),
);
$asset_url=function($key)use($settings){return empty($settings['letter_'.$key])?'':site_url('warga/aset-surat/'.$key);};
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-officials-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-officials-v2.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin-letter-settings-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-letter-settings-v2.css') ?>">
<section class="off-hero lsv-hero"><div class="off-hero-copy"><nav class="off-crumb"><span class="lsv-crumb-root">Pengelolaan Sistem</span><i>›</i><span>Pengaturan Surat</span><i>›</i><b>Pengaturan Surat RT</b></nav><div class="off-hero-title"><span class="off-hero-icon"><?= official_svg('doc') ?></span><div><h1>Pengaturan Surat RT</h1><p>Kelola kop surat, tanda tangan, stempel, dan identitas pada surat warga.</p></div></div></div></section>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="lsv-layout">
<form id="letter-settings-form" class="lsv-main" method="post" enctype="multipart/form-data">
 <section class="lsv-card">
  <header class="off-card-head"><span class="off-tile off-tile-large tint-blue"><?= official_svg('home') ?></span><div><h2>1. Identitas Kop Surat</h2><p>Data ini akan ditampilkan pada kop surat.</p></div></header>
  <div class="lsv-grid">
   <?= $field('letter_rt_name','Nama wilayah / RT','building','blue','RUKUN TETANGGA 009 / RUKUN WARGA 008',TRUE) ?>
   <?= $field('letter_environment','Nama lingkungan','map','blue','SUKA MULYA IV') ?>
   <?= $field('letter_region','Kota','pin','red') ?>
   <?= $field('letter_district','Kecamatan','building','blue') ?>
   <?= $field('letter_village','Kelurahan','building','blue') ?>
   <?= $field('letter_postal','Kode pos','mail','blue') ?>
   <div class="lsv-field lsv-wide"><label for="letter_address">Alamat pada kop</label><div class="lsv-control tint-red"><span><?= official_svg('pin') ?></span><textarea id="letter_address" name="letter_address" data-preview rows="2" maxlength="180"><?= html_escape($settings['letter_address']??($settings['address']??'')) ?></textarea></div></div>
  </div>
 </section>
 <section class="lsv-card">
  <header class="off-card-head"><span class="off-tile off-tile-large tint-green"><?= official_svg('image') ?></span><div><h2>2. Logo, Tanda Tangan &amp; Stempel</h2><p>Unggah logo pemerintah, tanda tangan, dan stempel untuk kop surat.</p></div></header>
  <div class="lsv-assets">
   <?php foreach($assets as $name=>$asset): $url=$asset_url($asset[2]); ?>
   <div class="lsv-asset" data-asset="<?= $asset[2] ?>">
    <b><?= $asset[0] ?></b><small>PNG, JPG, atau WebP (maks. 2 MB)</small>
    <div class="lsv-asset-box">
     <div class="lsv-asset-image"><img src="<?= $url ?>" alt="<?= $asset[0] ?>" <?= $url?'':'hidden' ?>><span class="lsv-asset-empty" <?= $url?'hidden':'' ?>><?= official_svg('image') ?>Belum ada gambar</span></div>
     <?php if($url): ?><label class="lsv-asset-remove" title="Hapus <?= $asset[0] ?>"><input type="checkbox" name="remove_<?= $name ?>" value="1" aria-label="Hapus <?= $asset[0] ?>"><?= official_svg('trash') ?></label><?php endif; ?>
     <label class="lsv-asset-pick"><input type="file" name="<?= $name ?>" accept="image/png,image/jpeg,image/webp"><?= official_svg('upload') ?><?= $url?'Ganti':'Unggah' ?> <?= $asset[1] ?></label>
    </div>
   </div>
   <?php endforeach; ?>
  </div>
 </section>
 <section class="lsv-card">
  <header class="off-card-head"><span class="off-tile off-tile-large tint-purple"><?= official_svg('doc') ?></span><div><h2>3. Informasi Penandatangan</h2><p>Nama dan jabatan yang akan ditampilkan di bawah tanda tangan.</p></div></header>
  <div class="lsv-grid">
   <?= $field('letter_signer_name','Nama penanda tangan','person','blue','',TRUE) ?>
   <?= $field('letter_signer_title','Jabatan','briefcase','blue','Ketua RT 009 / RW 008') ?>
  </div>
  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
  <div class="off-form-actions"><button class="off-submit" type="submit"><?= official_svg('save') ?>Simpan Pengaturan Surat</button><a class="off-cancel" href="<?= site_url('admin/letter_settings') ?>"><?= official_svg('close') ?>Batal</a></div>
 </section>
</form>
<aside class="lsv-side">
 <section class="lsv-card">
  <header class="off-card-head"><span class="off-tile off-tile-large tint-green"><?= official_svg('doc') ?></span><div><h2>Preview Kop Surat</h2><p>Contoh tampilan kop surat sesuai dengan pengaturan Anda.</p></div></header>
  <div class="lsv-paper lsv-kop" id="letter-preview-kop"><?php $this->load->view('shared/letter_head',array('settings'=>$settings,'headClass'=>'lsv-kop-head','logoUrl'=>site_url('warga/aset-surat/logo'))); ?></div>
 </section>
 <section class="lsv-card">
  <header class="off-card-head"><span class="off-tile off-tile-large tint-orange"><?= official_svg('doc') ?></span><div><h2>Contoh Surat</h2><p>Identitas dan gambar di sini akan dipakai pada surat warga yang telah disetujui.</p></div></header>
  <div class="lsv-paper lsv-sign" id="letter-preview-sign">
   <?php $this->load->view('shared/letter_sign_heading',array('settings'=>$settings)); ?>
   <div class="lsv-sign-images"><img id="letter-preview-stamp" src="<?= $asset_url('stamp') ?>" alt="Stempel" <?= $asset_url('stamp')?'':'hidden' ?>><img id="letter-preview-signature" src="<?= $asset_url('signature') ?>" alt="Tanda tangan" <?= $asset_url('signature')?'':'hidden' ?>></div>
   <div class="lsv-sign-name"><strong id="letter-preview-signer"><?= html_escape(($settings['letter_signer_name']??'')?:'Nama penanda tangan') ?></strong><span id="letter-preview-title"><?= html_escape($settings['letter_signer_title']??'Ketua RT 009 / RW 008') ?></span></div>
  </div>
 </section>
 <section class="lsv-tips"><span><?= official_svg('bulb') ?></span><div><h3>Tips Penggunaan</h3><ul><li>Gunakan logo dengan latar transparan (PNG) agar hasil lebih rapi.</li><li>Pastikan ukuran logo, tanda tangan, dan stempel tidak melebihi 2 MB.</li><li>Perubahan akan langsung digunakan pada semua surat yang dibuat.</li></ul></div></section>
</aside>
</div>
<script>
(function(){
 var form=document.getElementById('letter-settings-form'),kop=document.getElementById('letter-preview-kop');
 function val(id){return document.getElementById(id).value.trim()}
 function line(cls,text,tag){var el=document.createElement(tag||'span');el.className=cls;el.textContent=text;return el}
 // Cermin dari aturan views/shared/letter_head.php
 function renderKop(){
  var box=kop.querySelector('.letter-head-text'),region=val('letter_region'),district=val('letter_district'),village=val('letter_village'),address=val('letter_address'),parts=[];
  if(!address){if(village)parts.push('Kel. '+village.replace(/^KELURAHAN\s+/i,''));if(district)parts.push('Kec. '+district.replace(/^KECAMATAN\s+/i,''));if(region)parts.push((/^KOTA\b/i.test(region)?'':'Kota ')+region);address=parts.join(', ')}
  address=(address+' '+val('letter_postal')).trim();
  box.textContent='';
  [[region,'KOTA'],[district,'KECAMATAN'],[village,'KELURAHAN']].forEach(function(row){if(row[0])box.appendChild(line('letter-head-region',(new RegExp('^'+row[1]+'\\b','i').test(row[0])?row[0]:row[1]+' '+row[0]).toUpperCase()))});
  box.appendChild(line('letter-head-rt',val('letter_rt_name').toUpperCase(),'strong'));
  box.appendChild(line('letter-head-environment',val('letter_environment').toUpperCase(),'strong'));
  if(address)box.appendChild(line('letter-head-address',address));
 }
 function renderSigner(){
  var heading=document.querySelector('#letter-preview-sign .letter-sign-heading'),first=heading.childNodes[0],title=val('letter_signer_title'),place=val('letter_environment').toLowerCase().replace(/(^|\s)\S/g,function(c){return c.toUpperCase()}).replace(/\bIv\b/,'IV');
  first.textContent=place+first.textContent.slice(first.textContent.indexOf(','));
  heading.childNodes[heading.childNodes.length-1].textContent=title;
  document.getElementById('letter-preview-title').textContent=title;
  document.getElementById('letter-preview-signer').textContent=val('letter_signer_name')||'Nama penanda tangan';
 }
 form.querySelectorAll('[data-preview]').forEach(function(input){input.addEventListener('input',function(){renderKop();renderSigner()})});
 function previewTarget(key){
  if(key!=='logo')return document.getElementById('letter-preview-'+key);
  var head=kop.querySelector('header'),img=head.querySelector('img');
  if(!img){img=document.createElement('img');img.alt='Logo kop surat';img.hidden=true;head.insertBefore(img,head.firstChild)}
  return img;
 }
 form.querySelectorAll('.lsv-asset').forEach(function(asset){
  var key=asset.dataset.asset,thumb=asset.querySelector('.lsv-asset-image img'),empty=asset.querySelector('.lsv-asset-empty'),file=asset.querySelector('input[type=file]'),remove=asset.querySelector('input[type=checkbox]'),savedSrc=thumb.getAttribute('src'),pending=null;
  function show(src){thumb.src=src||'';thumb.hidden=!src;empty.hidden=!!src;var target=previewTarget(key);target.src=src||'';target.hidden=!src;asset.classList.toggle('removing',!!(remove&&remove.checked&&!pending))}
  function refresh(){show(pending||(remove&&remove.checked?'':savedSrc))}
  file.addEventListener('change',function(){
   var picked=file.files[0];
   if(pending){URL.revokeObjectURL(pending);pending=null}
   if(picked&&['image/png','image/jpeg','image/webp'].indexOf(picked.type)<0){alert('Format gambar harus PNG, JPG, atau WebP.');file.value=''}
   else if(picked&&picked.size>2*1024*1024){alert('Ukuran gambar maksimal 2 MB.');file.value=''}
   else if(picked){pending=URL.createObjectURL(picked);if(remove)remove.checked=false}
   refresh();
  });
  if(remove)remove.addEventListener('change',function(){if(remove.checked&&pending){URL.revokeObjectURL(pending);pending=null;file.value=''}refresh()});
 });
})();
</script>
