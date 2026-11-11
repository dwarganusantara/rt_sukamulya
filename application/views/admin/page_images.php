<?php
$looks=array(
 'dashboard_page_image'=>array('grid','orange'),'home_banner_jpg'=>array('home','blue'),'profile_page_image'=>array('person','purple'),
 'announcement_page_image'=>array('megaphone','red'),'agenda_page_image'=>array('calendar','green'),'information_page_image'=>array('doc','blue'),
 'gallery_page_image'=>array('image','purple'),'contact_page_image'=>array('mail','green'),'citizen_login_page_image'=>array('back','orange'),
 'structure_page_image'=>array('org','blue'),'footer_banner_jpg'=>array('lines','red'),
);
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-page-images-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-page-images-v2.css') ?>">
<section class="pgx-hero">
 <div class="pgx-copy"><nav class="pgx-crumb"><span>Pengelolaan Sistem</span><i>›</i><b>Gambar Halaman</b></nav><h1><span><?= official_svg('image') ?></span>Gambar Semua Halaman</h1><p>Kelola gambar utama setiap halaman. Perubahan langsung diterapkan tanpa mengubah kode.</p></div>
 <aside class="pgx-info"><span><?= official_svg('image') ?></span><p>Format: JPG, PNG, WebP, GIF, BMP, atau AVIF<br>Rekomendasi: 1920 × 600 px<br>Maksimal ukuran: 4 MB</p></aside>
</section>
<?php if($error): ?><div class="error pgx-error"><?= html_escape($error) ?></div><?php endif; ?>
<section class="pgx-grid">
 <?php foreach($images as $key=>$meta): $current=$settings[$key]??$meta[2];$look=$looks[$key]??array('image','blue'); ?>
 <article class="pgx-card">
  <header><span class="tone-<?= $look[1] ?>"><?= official_svg($look[0]) ?></span><div><h2><?= html_escape($meta[0]) ?></h2><p><?= html_escape($meta[1]) ?></p></div></header>
  <img class="pgx-preview" src="<?= base_url(html_escape($current)) ?>" alt="Gambar <?= html_escape($meta[0]) ?> saat ini">
  <form method="post" enctype="multipart/form-data">
   <label class="pgx-file"><strong>Pilih gambar baru</strong><input type="file" name="page_image" accept="<?= image_accept() ?>" required></label>
   <input type="hidden" name="image_key" value="<?= html_escape($key) ?>"><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
   <button type="submit"><?= official_svg('check-circle') ?>Terapkan</button>
  </form>
  <p class="pgx-pending" hidden></p>
 </article>
 <?php endforeach; ?>
</section>
<script>
(function(){
 document.querySelectorAll('.pgx-card').forEach(function(card){
  var input=card.querySelector('input[type=file]'),img=card.querySelector('.pgx-preview'),note=card.querySelector('.pgx-pending'),saved=img.src,pending=null;
  function reset(message){if(pending)URL.revokeObjectURL(pending);pending=null;img.src=saved;input.value='';note.hidden=!message;note.textContent=message||'';note.classList.toggle('bad',!!message)}
  input.addEventListener('change',function(){
   var file=input.files[0];
   if(!file)return reset();
   if(['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/avif'].indexOf(file.type)<0)return reset('Format gambar harus JPG, PNG, WebP, GIF, BMP, atau AVIF.');
   if(file.size>4*1024*1024)return reset('Ukuran gambar maksimal 4 MB.');
   if(pending)URL.revokeObjectURL(pending);
   pending=URL.createObjectURL(file);img.src=pending;note.hidden=false;note.classList.remove('bad');note.textContent='Pratinjau, belum tersimpan. Klik Terapkan untuk memakai gambar ini.';
  });
 });
})();
</script>
