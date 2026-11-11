<?php
$can_publish=in_array($this->session->userdata('admin_role'),array('admin','ketua'),TRUE);
$action=$page?'Edit':'Tambah';
$body_value=$page['body']??'';$title_value=$page['title']??'';
$limit=cfx_text_limit($body_value,5000);
$image=!empty($page['image'])?base_url(html_escape($page['image'])):'';
?>
<?= cfx_assets() ?>
<?= cfx_hero('page','doc',$action.' Halaman',$page?'Ubah halaman yang menampilkan informasi penting di website RT.':'Buat halaman baru untuk menampilkan informasi penting di website RT.',array(array('Beranda','admin'),array('Kelola Konten','admin/pages'),array($action.' Halaman',''))) ?>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="cfx-layout" data-content-form>
<form class="cfx-card cfx-form" method="post" enctype="multipart/form-data">
 <?= cfx_head('doc','blue','Informasi Halaman','Lengkapi informasi dasar halaman yang akan dibuat.') ?>
 <div class="cfx-field"><?= cfx_label('page-title','Judul halaman',TRUE) ?><?= cfx_control('doc','<input id="page-title" name="title" maxlength="160" required placeholder="Contoh: Sejarah RT, Program Kerja, Layanan Warga" value="'.html_escape($title_value).'" data-preview-text="#preview-title" data-empty="Judul Halaman">') ?></div>
 <div class="cfx-field"><?= cfx_label('page-body','Isi halaman',TRUE) ?>
  <div class="cfx-editor" id="page-editor">
   <div class="cfx-toolbar" role="toolbar" aria-label="Format teks">
    <button type="button" data-format="bold" aria-label="Tebal" title="Tebal"><b>B</b></button>
    <button type="button" data-format="italic" aria-label="Miring" title="Miring"><i>I</i></button>
    <button type="button" data-format="ul" aria-label="Daftar berpoin" title="Daftar berpoin"><?= official_svg('list') ?></button>
    <button type="button" data-format="ol" aria-label="Daftar bernomor" title="Daftar bernomor"><?= official_svg('list-num') ?></button>
    <button type="button" data-format="link" aria-label="Tautan" title="Tautan"><?= official_svg('link') ?></button>
    <button type="button" class="cfx-toolbar-end" data-format="full" aria-label="Layar penuh" title="Layar penuh"><?= official_svg('expand') ?></button>
   </div>
   <div class="cfx-link-bar" id="page-link-bar" hidden><input id="page-link-url" type="url" placeholder="https://contoh.id" aria-label="Alamat tautan"><button type="button" id="page-link-add">Sisipkan</button><button type="button" id="page-link-cancel">Batal</button></div>
   <textarea id="page-body" name="body" rows="4" maxlength="<?= $limit ?>" required placeholder="Tulis isi halaman di sini..." data-preview-markup="#preview-body"><?= html_escape($body_value) ?></textarea>
  </div>
  <div class="cfx-under cfx-under-end"><?= cfx_counter('page-body',$body_value,$limit) ?></div>
 </div>
 <hr class="cfx-rule">
 <?= cfx_head('image','purple','Gambar halaman <span class="cfx-optional">(Opsional)</span>','Unggah gambar sampul untuk mempercantik tampilan halaman.') ?>
 <?= cfx_upload('thumb','image','orange','Klik atau seret gambar ke sini','JPG, PNG, atau WebP (maks. 4 MB)','Pilih Gambar',(bool)$image) ?>
 <hr class="cfx-rule">
 <?= cfx_head('list','purple','Pengaturan Tampilan','Atur urutan dan visibilitas halaman.') ?>
 <div class="cfx-grid cfx-grid-center">
  <div class="cfx-field"><?= cfx_label('page-order','Urutan menu') ?><?= cfx_control('#','<input id="page-order" type="number" name="sort_order" value="'.(int)($page['sort_order']??0).'">') ?></div>
  <div class="cfx-switches"><div class="cfx-label">Tampilkan di menu</div><?= cfx_switch('show_in_menu',!$page||!empty($page['show_in_menu']),'Tampil','Halaman akan muncul pada menu utama website.') ?></div>
 </div>
 <?php if($can_publish): ?><?= cfx_switch('is_published',!isset($page['is_published'])||$page['is_published'],'Terbitkan halaman','Warga dapat membuka halaman ini di website.') ?><?php else: ?><p class="cfx-note">Halaman disimpan sebagai draf sampai diterbitkan Ketua RT.</p><?php endif; ?>
 <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
 <?= cfx_actions('Simpan Halaman','admin/pages') ?>
</form>
<aside class="cfx-side-col">
 <?= cfx_tips('Tips Menulis Halaman','',array('Gunakan judul yang singkat dan jelas.','Tulis informasi dengan bahasa yang mudah dipahami.','Tambahkan gambar agar tampilan lebih menarik.','Atur urutan menu sesuai kebutuhan.','Pastikan halaman yang dibuat bermanfaat bagi warga.')) ?>
 <section class="cfx-card"><?= cfx_head('eye','green','Preview Halaman','Berikut contoh tampilan halaman di website.') ?><div class="cfx-preview-box cfx-page"><div class="cfx-page-media"><span class="cfx-media-empty" data-image-empty<?= $image?' hidden':'' ?>><?= official_svg('image') ?></span><img data-image-preview src="<?= $image ?>" alt="Pratinjau gambar"<?= $image?'':' hidden' ?>></div><h3 id="preview-title"><?= html_escape($title_value?:'Judul Halaman') ?></h3><div class="cfx-bars" id="preview-bars"<?= $body_value?' hidden':'' ?>><i></i><i></i><i></i></div><p class="cfx-preview-body" id="preview-body"<?= $body_value?'':' hidden' ?>><?= html_escape($body_value) ?></p></div></section>
</aside>
</div>
<?= cfx_script() ?>
