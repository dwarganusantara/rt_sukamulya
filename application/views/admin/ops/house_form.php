<?php $this->load->helper(array('official_icons','page_ui')); ?>
<link rel="stylesheet" href="<?= base_url('assets/admin-master-kk.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-master-kk.css') ?>">
<div class="pg mkk house-page">
 <?= pg_crumb(array(array('Kependudukan',NULL),array('Rumah & Domisili','admin/rumah'),array($id?'Ubah Rumah':'Tambah Rumah',NULL))) ?>
 <section class="mkk-hero small"><span class="mkk-hero-ic"><?= official_svg('home') ?></span><div class="mkk-hero-tx"><h1><?= $id?'Ubah Data Rumah':'Tambah Rumah Baru' ?></h1><p>Nomor rumah boleh kosong, tetapi kode rumah dan alamat domisili wajib unik serta jelas.</p></div></section>
 <?php if($error): ?><div class="ops-note red"><?= ops_e($error) ?></div><?php endif; ?>
 <form method="post" class="pg-card mkk-section house-form"><?= ops_csrf() ?>
  <?php if(!$id && $return_family): ?><input type="hidden" name="return_family" value="<?= (int)$return_family ?>"><?php endif; ?>
  <div class="mkk-grid">
   <label class="mkk-field"><span>Kode rumah *</span><input name="house_code" value="<?= ops_e($item['house_code']??'') ?>" maxlength="40" required placeholder="R-009-001"></label>
   <label class="mkk-field"><span>Nomor rumah</span><input name="house_number" value="<?= ops_e($item['house_number']??'') ?>" maxlength="30" placeholder="12A"></label>
   <label class="mkk-field"><span>Blok / gang</span><input name="block_name" value="<?= ops_e($item['block_name']??'') ?>" maxlength="100" placeholder="Gang Melati"></label>
   <label class="mkk-field"><span>Jenis bangunan</span><select name="building_type"><?= ops_options($types,$item['building_type']??'rumah') ?></select></label>
   <label class="mkk-field"><span>Status hunian</span><select name="occupancy_status"><?= ops_options($statuses,$item['occupancy_status']??'dihuni') ?></select></label>
   <label class="mkk-field f-alamat"><span>Alamat domisili *</span><input name="address_text" value="<?= ops_e($item['address_text']??'') ?>" maxlength="500" required placeholder="Alamat aktual rumah"></label>
   <label class="mkk-field"><span>Patokan lokasi</span><input name="landmark" value="<?= ops_e($item['landmark']??'') ?>" maxlength="190"></label>
   <label class="mkk-field"><span>Keterangan</span><input name="notes" value="<?= ops_e($item['notes']??'') ?>" maxlength="500"></label>
  </div>
  <div class="mkk-buttons"><a class="pg-btn ghost" href="<?= site_url(!$id && $return_family?'admin/rumah/tautkan?keluarga='.(int)$return_family:'admin/rumah') ?>">Batal</a><button class="pg-btn solid" type="submit"><?= official_svg('check') ?>Simpan Rumah</button></div>
 </form>
</div>
