<?php
$action=$item?'Edit':'Tambah';
$title_value=$item['title']??'';$subtitle_value=$item['subtitle']??'';
$image=!empty($item['icon_image'])?base_url(html_escape($item['icon_image'])):'';
?>
<?= cfx_assets() ?>
<?= cfx_hero('quick',$item?'pencil':'plus',$action.' Menu Cepat',$item?'Ubah menu akses cepat warga di halaman beranda.':'Buat menu akses cepat untuk kebutuhan warga di halaman beranda.',array(array('Beranda','admin'),array('Manajemen Pengurus','admin/officials'),array('Menu Cepat','admin/quick_links'),array($action.' Menu Cepat',''))) ?>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="cfx-layout" data-content-form>
<form class="cfx-card cfx-form" method="post" enctype="multipart/form-data">
 <?= cfx_head('doc','blue','Informasi Menu','Isi informasi dasar menu cepat yang akan ditampilkan.') ?>
 <div class="cfx-grid">
  <div class="cfx-field"><?= cfx_label('quick-title','Judul Menu') ?><?= cfx_control('A','<input id="quick-title" name="title" required maxlength="100" placeholder="Contoh: Pengumuman" value="'.html_escape($title_value).'" data-preview-text="#preview-title" data-empty="Judul Menu">') ?></div>
  <div class="cfx-field"><?= cfx_label('quick-subtitle','Subjudul') ?><?= cfx_control('lines','<input id="quick-subtitle" name="subtitle" maxlength="160" placeholder="Contoh: Info terbaru untuk warga" value="'.html_escape($subtitle_value).'" data-preview-text="#preview-subtitle" data-empty="Subjudul menu">') ?></div>
 </div>
 <?= cfx_side('link','blue',cfx_label('quick-path','Tujuan Menu').'<input id="quick-path" name="url_path" required placeholder="Path situs, contoh: pengumuman, agenda, profil, warga/iuran" value="'.html_escape($item['url_path']??'').'"><small class="cfx-hint">Tulis alamat halaman tujuan menu (slug/path).</small>') ?>
 <?= cfx_head('image','blue','Ikon Menu','Unggah gambar untuk menu cepat.') ?>
 <?= cfx_upload('thumb','icon_image','orange','Pilih gambar / ikon','JPG, PNG, atau WebP. Maksimal 4 MB.','Pilih File',(bool)$image) ?>
 <?= cfx_head('list','purple','Urutan &amp; Tampilan','Atur posisi menu dan tampilkan di halaman beranda.') ?>
 <div class="cfx-grid cfx-grid-center">
  <div class="cfx-field"><?= cfx_label('quick-order','Urutan') ?><?= cfx_control('#','<input id="quick-order" type="number" name="sort_order" value="'.(int)($item['sort_order']??0).'">') ?></div>
  <?= cfx_switch('is_active',!isset($item['is_active'])||$item['is_active'],'Tampilkan di beranda','Menu akan ditampilkan pada halaman utama.') ?>
 </div>
 <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
 <?= cfx_actions('Simpan Menu','admin/quick_links') ?>
</form>
<aside class="cfx-side-col">
 <?= cfx_tips('Tips Menu Cepat','Beberapa hal yang perlu diperhatikan.',array('Gunakan judul yang singkat dan jelas.','Pilih ikon yang mudah dikenali.','Pastikan path tujuan menu sudah benar.','Menu yang diaktifkan akan langsung tampil di halaman beranda.')) ?>
 <section class="cfx-card"><?= cfx_head('eye','green','Contoh Tampilan Menu','Berikut contoh tampilan menu di halaman beranda.') ?><div class="cfx-preview-box cfx-quick"><span class="cfx-quick-icon"><span class="cfx-media-empty" data-image-empty<?= $image?' hidden':'' ?>><?= official_svg('image') ?></span><img data-image-preview src="<?= $image ?>" alt="Pratinjau ikon"<?= $image?'':' hidden' ?>></span><div><h3 id="preview-title"><?= html_escape($title_value?:'Judul Menu') ?></h3><p id="preview-subtitle"><?= html_escape($subtitle_value?:'Subjudul menu') ?></p></div></div></section>
</aside>
</div>
<?= cfx_script() ?>
