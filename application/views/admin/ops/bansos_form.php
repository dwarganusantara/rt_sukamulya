<?php
$this->load->helper(array('official_icons', 'page_ui'));
$v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; };
$edit = !empty($item['id']);
$aid_types = array('Sembako', 'Uang Tunai', 'Kesehatan', 'Pendidikan', 'Pembangunan / Bedah Rumah', 'Santunan', 'Lainnya');
$criteria = (string) $v('criteria');
?>
<div class="pg">
 <?= pg_crumb(array(array('Bantuan Sosial', 'admin/bansos'), array($edit ? 'Ubah Program' : 'Program Baru', NULL))) ?>
 <section class="pg-hero pg-hero-care"><span class="pg-hero-ic"><?= official_svg('heart') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Bantuan Sosial</span><h1><?= $edit ? 'Ubah Program Bantuan' : 'Program Bantuan Baru' ?></h1><p>Tentukan jenis program, nilai per penerima, sumber dana, kriteria, dan kuota penerima.</p></div><span class="pg-care-art" aria-hidden="true"></span></section>
 <?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

 <form method="post" class="pg-card pg-form"><?= ops_csrf() ?>
  <h2 class="pg-form-title"><?= official_svg('doc') ?>Informasi Program</h2>
  <div class="pg-grid">
   <div class="pg-field wide"><label for="f-name">Nama Program <b aria-hidden="true">*</b></label><span class="pg-input"><?= official_svg('tag') ?><input id="f-name" name="name" value="<?= ops_e($v('name')) ?>" maxlength="160" required placeholder="Masukkan nama program bantuan..."></span></div>
   <div class="pg-field"><label for="f-type">Jenis Bantuan <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('list') ?><input id="f-type" name="aid_type" value="<?= ops_e($v('aid_type')) ?>" maxlength="60" required list="aid-types" placeholder="Pilih atau ketik jenis bantuan" autocomplete="off"></span>
    <datalist id="aid-types"><?php foreach ($aid_types as $t): ?><option value="<?= ops_e($t) ?>"><?php endforeach; ?></datalist></div>
   <div class="pg-field"><label for="f-period">Periode <b aria-hidden="true">*</b></label><span class="pg-input"><?= official_svg('calendar') ?><input id="f-period" name="period" value="<?= ops_e($v('period')) ?>" maxlength="40" required placeholder="Pilih periode (contoh: Desember 2026)"></span></div>
   <div class="pg-field"><label for="f-unit">Nilai per Penerima (Rp) <b aria-hidden="true">*</b></label><span class="pg-input"><span class="pg-rp">Rp</span><input id="f-unit" name="unit_value" type="number" min="0" step="1" inputmode="numeric" value="<?= (int) $v('unit_value', 0) ?>" required></span></div>
   <div class="pg-field"><label for="f-quota">Kuota Penerima <b aria-hidden="true">*</b></label><span class="pg-input"><?= official_svg('people') ?><input id="f-quota" name="quota" type="number" min="0" step="1" inputmode="numeric" value="<?= (int) $v('quota', 0) ?>" required aria-describedby="f-quota-h"></span><small id="f-quota-h">Isi 0 jika tidak ada batas kuota</small></div>
   <div class="pg-field"><label for="f-source">Sumber Dana / Barang</label><span class="pg-input"><?= official_svg('database') ?><input id="f-source" name="source" value="<?= ops_e($v('source')) ?>" maxlength="120" placeholder="Contoh: Kas RT, Donasi Warga, CSR, dll..."></span></div>
   <div class="pg-field wide"><label for="f-criteria" class="pg-label-lg"><?= official_svg('lines') ?>Kriteria Kelayakan</label><span class="pg-textarea"><textarea id="f-criteria" name="criteria" rows="4" maxlength="3000" placeholder="Tuliskan kriteria atau syarat kelayakan penerima bantuan..." data-counter="f-criteria-c"><?= ops_e($criteria) ?></textarea><small id="f-criteria-c" aria-live="polite"><?= mb_strlen($criteria) ?> / 3000</small></span>
    <small>Jangan cantumkan data kesehatan, penghasilan rinci, atau data rentan lain yang tidak diperlukan.</small></div>
  </div>
  <div class="pg-form-actions"><a class="pg-btn ghost" href="<?= site_url($edit ? 'admin/bansos/' . (int) $item['id'] : 'admin/bansos') ?>"><?= official_svg('close') ?>Batal</a><button class="pg-btn solid" type="submit"><?= official_svg('save') ?>Simpan Program</button></div>
 </form>
</div>
<script>
(function(){
 var t=document.getElementById('f-criteria'),c=document.getElementById('f-criteria-c');
 if(t&&c)t.addEventListener('input',function(){c.textContent=t.value.length+' / 3000'});
})();
</script>
