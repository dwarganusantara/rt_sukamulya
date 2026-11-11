<?php
$this->load->helper(array('official_icons', 'page_ui'));
$step = (!$family || !$type) ? 1 : 2;
$steps = array(1 => 'Pilih Keluarga', 2 => 'Isi Data Mutasi', 3 => 'Konfirmasi');
?>
<div class="pg">
 <?= pg_crumb(array(array('Mutasi Warga', 'admin/mutasi'), array('Catat Mutasi', NULL))) ?>
 <section class="pg-hero pg-hero-care pg-hero-note"><span class="pg-hero-ic"><?= official_svg('swap') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Kependudukan</span><h1>Catat Mutasi oleh Pengurus</h1><p>Gunakan untuk mencatat perubahan data warga (masuk, pindah, meninggal, atau koreksi data) yang dilaporkan langsung ke pengurus. Mutasi tetap melalui persetujuan sebelum data KK berubah.</p></div><span class="pg-note-art" aria-hidden="true"></span></section>

 <ol class="pg-steps" aria-label="Langkah">
  <?php foreach ($steps as $n => $label): ?><li class="<?= $n < $step ? 'done' : ($n === $step ? 'now' : '') ?>" id="pg-step-<?= $n ?>"<?= $n === $step ? ' aria-current="step"' : '' ?>><span><?= $n ?></span><?= $label ?></li><?php endforeach; ?>
 </ol>
 <?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

 <?php if ($step === 1): ?>
 <form method="get" action="<?= site_url('admin/mutasi/baru') ?>" class="pg-card pg-form">
  <div class="pg-form-head"><span class="pg-form-ic"><?= official_svg('people') ?></span><div><h2>1. Pilih Keluarga yang Terverifikasi</h2><p>Hanya keluarga yang memiliki KK terverifikasi yang dapat diproses mutasinya.</p></div></div>
  <?php if ($family): ?><input type="hidden" name="keluarga" value="<?= (int) $family['id'] ?>"><?php endif; ?>
  <div class="pg-grid">
   <div class="pg-field"><label for="f-fam">Keluarga <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('people') ?>
    <?php if ($family): ?><input id="f-fam" value="KK <?= ops_e(rt_mask_id($family['nomor_kk'])) ?> — <?= ops_e(mb_substr($family['fields']['alamat'] ?? '', 0, 50)) ?>" readonly>
    <?php else: ?><select id="f-fam" name="keluarga" required><option value="">Pilih keluarga</option><?php foreach ($families as $id => $label): ?><option value="<?= (int) $id ?>"><?= ops_e($label) ?></option><?php endforeach; ?></select><?php endif; ?></span></div>
   <div class="pg-field"><label for="f-type">Jenis Mutasi <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('swap') ?><select id="f-type" name="jenis" required><option value="">Pilih jenis mutasi</option><?php foreach ($types as $k => $label): ?><option value="<?= ops_e($k) ?>"<?= $k === $type ? ' selected' : '' ?>><?= ops_e($label) ?></option><?php endforeach; ?></select></span></div>
  </div>
  <?php if (!$families && !$family): ?><p class="ops-note">Belum ada keluarga terverifikasi. Verifikasi data KK warga terlebih dahulu di menu Verifikasi KK.</p><?php endif; ?>
  <div class="pg-form-actions"><a class="pg-btn ghost" href="<?= site_url('admin/mutasi') ?>"><?= official_svg('close') ?>Batal</a><button class="pg-btn solid" type="submit"<?= (!$families && !$family) ? ' disabled' : '' ?>>Lanjutkan<?= official_svg('arrow-right') ?></button></div>
 </form>

 <?php else: ?>
 <section class="pg-card pg-form">
  <div class="pg-form-head"><span class="pg-form-ic"><?= official_svg(array('masuk' => 'people', 'pindah' => 'swap', 'meninggal' => 'heart', 'ubah_data' => 'pencil', 'ubah_alamat' => 'home')[$type] ?? 'pencil') ?></span><div><h2>2. <?= ops_e($types[$type]) ?></h2><p>KK <?= ops_e(rt_mask_id($family['nomor_kk'])) ?> · <?= ops_e($family['fields']['alamat'] ?? '') ?></p></div>
   <a class="pg-btn ghost small pg-form-head-act" href="<?= site_url('admin/mutasi/baru?keluarga=' . (int) $family['id']) ?>"><?= official_svg('swap') ?>Ganti jenis</a><a class="pg-btn ghost small" href="<?= site_url('admin/mutasi/baru') ?>"><?= official_svg('people') ?>Ganti keluarga</a></div>
  <form method="post" id="mut-form" action="<?= site_url('admin/mutasi/baru?keluarga=' . (int) $family['id'] . '&jenis=' . $type) ?>" data-type="<?= ops_e($types[$type]) ?>"><?= ops_csrf() ?><input type="hidden" name="family_id" value="<?= (int) $family['id'] ?>">
   <div class="pg-ops-fields"><?php $this->load->view('shared/mutasi_fields', compact('type', 'family', 'values', 'relationships', 'domicile', 'member_edit', 'family_edit')); ?></div>
   <div class="pg-form-actions"><a class="pg-btn ghost" href="<?= site_url('admin/mutasi') ?>"><?= official_svg('close') ?>Batal</a><button class="pg-btn solid" type="submit"><?= official_svg('save') ?>Simpan untuk Persetujuan</button></div>
  </form>
 </section>
 <script>
 (function(){
  // Langkah 3: konfirmasi sebelum dikirim (data master baru berubah setelah mutasi disetujui).
  var f=document.getElementById('mut-form'),s2=document.getElementById('pg-step-2'),s3=document.getElementById('pg-step-3');
  if(!f)return;
  var r=document.getElementById('f-reason');
  if(r){var cnt=document.createElement('small');cnt.className='pg-reason-count';cnt.setAttribute('aria-live','polite');var upd=function(){cnt.textContent=r.value.length+' / '+(r.maxLength>0?r.maxLength:1000)};upd();r.insertAdjacentElement('afterend',cnt);r.addEventListener('input',upd);}
  f.addEventListener('submit',function(e){
   if(!f.checkValidity())return;
   s2.className='done';s3.className='now';
   if(!confirm('Konfirmasi: simpan mutasi "'+f.dataset.type+'" untuk persetujuan? Data KK baru berubah setelah mutasi disetujui.')){e.preventDefault();s2.className='now';s3.className='';}
  });
 })();
 </script>
 <?php endif; ?>
</div>
