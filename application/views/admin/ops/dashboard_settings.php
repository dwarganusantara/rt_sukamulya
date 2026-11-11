<link rel="stylesheet" href="<?= base_url('assets/dashboard-icons.css') ?>?v=<?= filemtime(FCPATH.'assets/dashboard-icons.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/dashboard-settings.css') ?>?v=<?= filemtime(FCPATH.'assets/dashboard-settings.css') ?>">
<div class="dws">
 <header><h2>Dashboard Warga</h2><p>Atur kartu ikon tanpa mengubah URL layanan. Status aktif dan hak akses tetap diatur pada tab Daftar Fitur.</p><p><a href="<?= site_url('admin/page-images') ?>">Ubah ilustrasi banner melalui Gambar Halaman &rarr;</a></p></header>
 <form method="post" action="<?= site_url('admin/fitur/dashboard-save') ?>" id="dashboard-settings-form">
 <?= ops_csrf() ?><input type="hidden" name="version" value="<?= ops_e($dashboard_version) ?>">
 <div class="dws-toolbar"><label>Cari menu<input type="search" id="dws-search" placeholder="Nama atau kategori"></label><label>Kategori<select id="dws-category"><option value="">Semua kategori</option><?php foreach($dashboard_categories as $category_option): ?><option><?= ops_e($category_option) ?></option><?php endforeach; ?></select></label></div>
 <div id="dws-rows">
 <?php foreach($dashboard_settings as $key=>$item): ?>
 <fieldset class="dws-row" data-key="<?= ops_e($key) ?>" data-allowed="<?= $item['allowed']?'1':'0' ?>" data-route="<?= ops_e(site_url($item['route'])) ?>">
  <legend><?= ops_e($item['label']) ?></legend>
  <div class="dws-order"><span class="dws-drag" draggable="true" title="Seret untuk mengurutkan" aria-label="Seret untuk mengurutkan">&#8942;&#8942;</span><button type="button" data-move="up" aria-label="Naikkan <?= ops_e($item['label']) ?>">&uarr;</button><button type="button" data-move="down" aria-label="Turunkan <?= ops_e($item['label']) ?>">&darr;</button></div>
  <label>Nama menu<input name="items[<?= ops_e($key) ?>][label]" data-field="label" value="<?= ops_e($item['label']) ?>" maxlength="60" required></label>
  <label>Ikon<select name="items[<?= ops_e($key) ?>][icon]" data-field="icon"><?php foreach($dashboard_icons as $value=>$label): ?><option value="<?= ops_e($value) ?>"<?= $value===$item['icon']?' selected':'' ?>><?= ops_e($label) ?></option><?php endforeach; ?></select></label>
  <label>Warna<select name="items[<?= ops_e($key) ?>][color]" data-field="color"><?php foreach($dashboard_colors as $value=>$label): ?><option value="<?= ops_e($value) ?>"<?= $value===$item['color']?' selected':'' ?>><?= ops_e($label) ?></option><?php endforeach; ?></select></label>
  <label>Kategori<select name="items[<?= ops_e($key) ?>][category]" data-field="category"><?php foreach($dashboard_categories as $value): ?><option<?= $value===$item['category']?' selected':'' ?>><?= ops_e($value) ?></option><?php endforeach; ?></select></label>
  <label>Urutan<input type="number" name="items[<?= ops_e($key) ?>][sort_order]" data-field="sort_order" value="<?= (int)$item['sort_order'] ?>" min="0" max="9999" required></label>
  <label class="dws-toggle"><input type="checkbox" name="items[<?= ops_e($key) ?>][show_on_dashboard]" data-field="show_on_dashboard" value="1"<?= $item['show_on_dashboard']?' checked':'' ?>>Tampil di dashboard</label>
  <label class="dws-toggle"><input type="checkbox" name="items[<?= ops_e($key) ?>][show_in_header]" data-field="show_in_header" value="1"<?= $item['show_in_header']?' checked':'' ?>>Tampil di header</label>
  <label class="dws-toggle"><input type="checkbox" name="items[<?= ops_e($key) ?>][is_default_favorite]" data-field="is_default_favorite" value="1"<?= $item['is_default_favorite']?' checked':'' ?>>Favorit awal warga</label>
  <p class="dws-status"><?= $item['allowed']?'Akses warga aktif':'Disembunyikan: fitur nonaktif atau akses warga tidak diizinkan' ?><br><small><?= ops_e($item['route']) ?></small></p>
 </fieldset>
 <?php endforeach; ?>
 </div>
 <div class="dws-save"><button class="pg-btn primary" type="submit" name="action" value="save">Simpan Perubahan</button><a class="pg-btn outline" href="<?= site_url('admin/fitur?tab=dashboard') ?>">Batalkan</a><button class="pg-btn outline" type="submit" name="action" value="reset" formnovalidate id="dws-reset">Reset ke Default</button></div>
 </form>
 <section class="dws-preview-section"><h3>Pratinjau Dashboard Warga</h3><p>Pratinjau mengikuti perubahan di atas. Tekan Simpan Perubahan untuk menerapkan.</p><div class="dws-preview-modes"><button type="button" data-preview="desktop" aria-pressed="true">Desktop</button><button type="button" data-preview="mobile" aria-pressed="false">Ponsel</button></div><div class="dwi-preview" id="dws-preview"><?php $this->load->view('citizen/dashboard_tiles',compact('dashboard_items','dashboard_images')); ?></div></section>
</div>
<?php $this->load->helper('dashboard_ui'); $svg_options=array();foreach($dashboard_icons as $key=>$label)$svg_options[$key]=dui_icon($key); ?>
<script type="application/json" id="dws-svg-data"><?= json_encode($svg_options,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script type="application/json" id="dws-image-data"><?= json_encode(new stdClass(),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= base_url('assets/dashboard-settings.js') ?>?v=<?= filemtime(FCPATH.'assets/dashboard-settings.js') ?>" defer></script>