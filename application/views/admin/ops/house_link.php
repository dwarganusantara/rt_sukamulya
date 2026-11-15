<?php $this->load->helper(array('official_icons','page_ui')); ?>
<link rel="stylesheet" href="<?= base_url('assets/admin-master-kk.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-master-kk.css') ?>">
<div class="pg mkk house-page">
 <?= pg_crumb(array(array('Kependudukan',NULL),array('Rumah & Domisili','admin/rumah'),array('Hubungkan KK',NULL))) ?>
 <section class="mkk-hero small"><span class="mkk-hero-ic"><?= official_svg('link') ?></span><div class="mkk-hero-tx"><h1>Hubungkan KK ke Rumah</h1><p>Alamat domisili disimpan terpisah dari alamat pada dokumen KK. Memindahkan keluarga akan menutup domisili lama dan mempertahankan riwayat.</p></div></section>
 <?php if($error): ?><div class="ops-note red" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
 <?php if(!$houses): ?>
 <div class="ops-note amber house-link-empty" role="status"><span><?= official_svg('alert') ?><strong>Belum ada rumah yang dapat dipilih.</strong> Tambahkan data rumah terlebih dahulu, lalu Anda akan kembali ke halaman ini untuk menghubungkan KK.</span><?php if($can_create_house): ?><a class="pg-btn solid" href="<?= site_url('admin/rumah/baru'.($family_id?'?keluarga='.(int)$family_id:'')) ?>"><?= official_svg('plus') ?>Tambah Rumah</a><?php endif; ?></div>
 <?php endif; ?>
 <form method="post" class="pg-card mkk-section house-link-form"><?= ops_csrf() ?>
  <div class="mkk-grid">
   <label class="mkk-field"><span>Kartu Keluarga *</span><select name="family_id" required><?= ops_options($families,$family_id,'Pilih KK') ?></select></label>
   <label class="mkk-field"><span>Rumah *</span><select name="house_id" required<?= !$houses?' disabled':'' ?>><?= ops_options($houses,$house_id,'Pilih rumah') ?></select></label>
   <label class="mkk-field"><span>Status hunian *</span><select name="occupancy_relation"><?= ops_options($relations,$relation) ?></select></label>
   <label class="mkk-field"><span>Mulai tinggal *</span><input type="date" name="start_date" value="<?= ops_e($start) ?>" required></label>
  </div>
  <?php if($can_verify): ?><label class="mkk-check"><input type="checkbox" name="verify" value="1"<?= $verify?' checked':'' ?>>Verifikasi domisili sekarang (saya sudah memeriksa kecocokan KK, rumah, dan penghuni)</label><?php endif; ?>
  <div class="mkk-buttons"><a class="pg-btn ghost" href="<?= site_url('admin/rumah') ?>">Batal</a><button class="pg-btn solid" type="submit"<?= !$houses?' disabled aria-disabled="true" title="Tambahkan rumah terlebih dahulu"':'' ?>><?= official_svg('link') ?>Hubungkan ke Rumah</button></div>
 </form>
</div>
