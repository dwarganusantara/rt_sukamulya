<?php
$this->load->helper(array('official_icons', 'page_ui'));
$v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; };
$edit = !empty($item['id']);
$aid_types = Sosial::$aid_types;
$type_now = (string) $v('aid_type');
$type_match = '';
foreach ($aid_types as $t) if (mb_strtolower($t) === mb_strtolower($type_now)) $type_match = $t;
$type_sel = $type_now === '' ? '' : ($type_match !== '' ? $type_match : 'Lainnya');
$type_other = $type_sel === 'Lainnya' && $type_match === '' ? $type_now : '';
$months = Sosial::$months;
list($p_month, $p_year) = Sosial::parse_period((string) $v('period'));
if (!$edit && $p_month === '') { $p_month = $months[(int) date('n') - 1]; $p_year = date('Y'); }
$years = range((int) date('Y') - 2, (int) date('Y') + 3);
if ($p_year !== '' && !in_array((int) $p_year, $years, TRUE)) { $years[] = (int) $p_year; sort($years); }
$period_now = $p_month !== '' && $p_year !== '' ? $p_month . '|' . $p_year : '';
$sources = array('Kas RT', 'Donasi Warga', 'CSR', 'Bantuan Pemerintah', 'Swadaya Masyarakat', 'Sponsor', 'Barang Hibah', 'Lainnya');
$source_now = (string) $v('source');
$source_sel = in_array($source_now, $sources, TRUE) ? $source_now : ($source_now !== '' ? 'Lainnya' : '');
$source_other = $source_sel === 'Lainnya' && $source_now !== 'Lainnya' ? $source_now : '';
$criteria = (string) $v('criteria');
?>
<div class="pg">
 <?= pg_crumb(array(array('Bantuan Sosial', 'admin/bansos'), array($edit ? 'Ubah Program' : 'Program Baru', NULL))) ?>
 <section class="pg-hero pg-hero-care"><span class="pg-hero-ic"><?= official_svg('heart') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Bantuan Sosial</span><h1><?= $edit ? 'Ubah Program Bantuan' : 'Program Bantuan Baru' ?></h1><p>Tentukan jenis program, nilai per penerima, sumber dana, kriteria, dan kuota penerima.</p></div><span class="pg-care-art" aria-hidden="true"></span></section>
 <?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

 <form method="post" class="pg-card pg-form baf-form"><?= ops_csrf() ?>
  <h2 class="pg-form-title baf-form-title"><span><?= official_svg('doc') ?></span>Informasi Program</h2>
  <div class="baf-grid">
   <div class="pg-field baf-name"><label for="f-name">Nama Program <b aria-hidden="true">*</b></label><span class="pg-input"><?= official_svg('tag') ?><input id="f-name" name="name" value="<?= ops_e($v('name')) ?>" maxlength="160" required placeholder="Masukkan nama program bantuan"></span></div>
   <div class="pg-field baf-quota"><label for="f-quota">Kuota Penerima <b aria-hidden="true">*</b></label><span class="pg-input"><?= official_svg('people') ?><input id="f-quota" name="quota" type="number" min="0" step="1" inputmode="numeric" value="<?= $edit ? (int) $v('quota', 0) : '' ?>" required placeholder="Contoh: 100" aria-describedby="f-quota-h"></span><small id="f-quota-h" class="sr-only">Isi 0 jika tidak ada batas kuota</small></div>
   <div class="baf-type-period">
    <div class="pg-field"><label for="f-type">Jenis Bantuan <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('box') ?><select id="f-type" name="aid_type" required>
     <option value="" disabled<?= $type_sel === '' ? ' selected' : '' ?>>Pilih jenis bantuan</option>
     <?php foreach ($aid_types as $t): ?><option value="<?= ops_e($t) ?>"<?= $type_sel === $t ? ' selected' : '' ?>><?= ops_e($t) ?></option><?php endforeach; ?>
    </select></span>
    <span class="pg-input" id="f-type-other-wrap"<?= $type_sel === 'Lainnya' ? '' : ' hidden' ?>><?= official_svg('tag') ?><input id="f-type-other" name="aid_type_other" value="<?= ops_e($type_other) ?>" maxlength="60" placeholder="Sebutkan jenis bantuan lainnya" aria-label="Jenis bantuan lainnya"></span></div>
    <div class="pg-field"><label for="f-period">Periode <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('calendar') ?><select id="f-period" name="period_value" required><option value="" disabled<?= $period_now === '' ? ' selected' : '' ?>>Pilih periode</option><?php foreach ($years as $y): ?><?php foreach ($months as $m): $pv = $m . '|' . $y; ?><option value="<?= ops_e($pv) ?>"<?= $period_now === $pv ? ' selected' : '' ?>><?= ops_e($m . ' ' . $y) ?></option><?php endforeach; ?><?php endforeach; ?></select></span></div>
   </div>
   <div class="pg-field baf-source"><label for="f-source">Sumber Dana / Barang <b aria-hidden="true">*</b></label><span class="pg-input pg-input-sel"><?= official_svg('database') ?><select id="f-source" name="source" required><option value="" disabled<?= $source_sel === '' ? ' selected' : '' ?>>Pilih sumber dana/barang</option><?php foreach ($sources as $source): ?><option value="<?= ops_e($source) ?>"<?= $source_sel === $source ? ' selected' : '' ?>><?= ops_e($source) ?></option><?php endforeach; ?></select></span><span class="pg-input" id="f-source-other-wrap"<?= $source_sel === 'Lainnya' ? '' : ' hidden' ?>><?= official_svg('tag') ?><input id="f-source-other" name="source_other" value="<?= ops_e($source_other) ?>" maxlength="120" placeholder="Sebutkan sumber dana atau barang lainnya" aria-label="Sumber dana atau barang lainnya"></span></div>
   <div class="pg-field baf-unit"><label for="f-unit">Nilai per Penerima (Rp) <b aria-hidden="true">*</b></label><span class="pg-input"><span class="pg-rp">Rp</span><input id="f-unit" name="unit_value" type="number" min="0" step="1" inputmode="numeric" value="<?= $edit ? (int) $v('unit_value', 0) : '' ?>" required placeholder="Contoh: 300000"></span></div>
   <div class="pg-field baf-criteria"><label for="f-criteria">Kriteria Kelayakan</label><span class="pg-textarea baf-textarea"><?= official_svg('lines') ?><textarea id="f-criteria" name="criteria" rows="4" maxlength="1000" placeholder="Masukkan kriteria kelayakan penerima..." data-counter="f-criteria-c"><?= ops_e($criteria) ?></textarea><small id="f-criteria-c" aria-live="polite"><?= mb_strlen($criteria) ?> / 1000</small></span><small>Jangan cantumkan data kesehatan, penghasilan rinci, atau data rentan lain yang tidak diperlukan.</small></div>
  </div>
  <div class="pg-form-actions baf-actions"><a class="pg-btn ghost" href="<?= site_url($edit ? 'admin/bansos/' . (int) $item['id'] : 'admin/bansos') ?>"><?= official_svg('close') ?>Batal</a><button class="pg-btn solid" type="submit"><?= official_svg('save') ?>Simpan Program</button></div>
 </form>
</div>
<script>
(function(){
 var t=document.getElementById('f-criteria'),c=document.getElementById('f-criteria-c');
 if(t&&c)t.addEventListener('input',function(){c.textContent=t.value.length+' / 1000'});
 var s=document.getElementById('f-type'),w=document.getElementById('f-type-other-wrap'),o=document.getElementById('f-type-other');
 function sync(){var on=s.value==='Lainnya';w.hidden=!on;o.required=on;if(on&&document.activeElement===s)o.focus();}
 if(s&&w&&o){s.addEventListener('change',sync);sync();}
 var ss=document.getElementById('f-source'),sw=document.getElementById('f-source-other-wrap'),so=document.getElementById('f-source-other');
 function syncSource(){var on=ss.value==='Lainnya';sw.hidden=!on;so.required=on;if(on&&document.activeElement===ss)so.focus();}
 if(ss&&sw&&so){ss.addEventListener('change',syncSource);syncSource();}
})();
</script>
