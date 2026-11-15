<?php
/* REVISI: form Notulen disederhanakan sesuai mockup — 5 isian utama; isian lain di "Detail tambahan (opsional)". */
$this->load->helper('official_icons');
$v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; };
$edit = !empty($item['id']);
$css = 'assets/admin-notulen-form.css';
$saveAs = $this->input->post('save_as') ?: (($v('status') === 'draft' && $edit) ? 'draft' : 'final');
$extraOpen = $v('location') !== '' || $v('agenda_id') || $v('program_id') || $v('participants') !== '' || $v('decisions') !== '' || $v('obstacles') !== '';
$summary = (string) $v('summary');
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<div class="nf">
 <nav class="nf-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>" aria-label="Dashboard"><?= official_svg('home') ?></a><span><?= official_svg('chev-right') ?></span><a href="<?= site_url('admin/notulen') ?>">Notulen</a><span><?= official_svg('chev-right') ?></span><strong><?= $edit ? 'Ubah Notulen' : 'Notulen Baru' ?></strong></nav>

 <section class="nf-hero">
  <span class="nf-hero-ic" aria-hidden="true"><?= official_svg('doc') ?></span>
  <div class="nf-hero-tx"><span>Kegiatan</span><h1><?= $edit ? 'Ubah Notulen' : 'Notulen Baru' ?></h1><p><?= $edit ? 'Perubahan disimpan sebagai versi baru; versi sebelumnya tetap tersimpan untuk audit.' : 'Catat hasil kegiatan / rapat warga dengan cepat dan ringkas.' ?></p></div>
  <div class="nf-art" aria-hidden="true"><b class="board"><i></i><i></i><i></i></b><em class="pen"></em><s class="leaf"></s></div>
 </section>

 <?php if ($error): ?><div class="nf-error" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

 <form method="post" class="nf-card" id="nf-form"><?= ops_csrf() ?>
  <label class="nf-field"><span><?= official_svg('calendar') ?>Tanggal Kegiatan <b>*</b></span>
   <span class="nf-ctl"><input type="date" name="note_date" required value="<?= ops_e($v('note_date', date('Y-m-d'))) ?>"></span></label>
  <label class="nf-field"><span><?= official_svg('doc') ?>Judul Kegiatan / Rapat <b>*</b></span>
   <span class="nf-ctl has-ic"><i class="nf-ic orange"><?= official_svg('doc') ?></i><input name="title" required minlength="3" maxlength="160" placeholder="Contoh: Rapat Keamanan Lingkungan" value="<?= ops_e($v('title')) ?>"></span></label>

  <label class="nf-field"><span><?= official_svg('people') ?>Jumlah Peserta <b>*</b></span>
   <span class="nf-ctl has-ic"><i class="nf-ic orange"><?= official_svg('people') ?></i><input type="number" name="participant_count" min="0" step="1" inputmode="numeric" required value="<?= (int) $v('participant_count', 0) ?: '' ?>" placeholder="0"><em class="nf-suffix">Peserta</em></span></label>
  <label class="nf-field nf-status-field"><span>Status Notulen</span>
   <span class="nf-ctl nf-status <?= $saveAs === 'final' ? 'is-final' : 'is-draft' ?>" id="nf-status"><i class="nf-dot" aria-hidden="true"></i><select name="save_as" id="nf-save-as">
    <option value="final"<?= $saveAs === 'final' ? ' selected' : '' ?>>Simpan sebagai Notulen</option>
    <option value="draft"<?= $saveAs === 'draft' ? ' selected' : '' ?>>Simpan sebagai Draf</option>
   </select></span>
   <small id="nf-status-help"><?= $saveAs === 'final' ? 'Notulen langsung terbit setelah disimpan.' : 'Draf belum diajukan; bisa dilengkapi nanti.' ?></small></label>

  <label class="nf-field nf-wide"><span><?= official_svg('pencil') ?>Ringkasan Hasil Kegiatan <b>*</b></span>
   <span class="nf-ctl nf-area"><textarea name="summary" id="nf-summary" rows="4" maxlength="8000" required placeholder="Contoh: Pembahasan penguatan keamanan lingkungan, penambahan jadwal ronda, dan evaluasi kegiatan bulan berjalan."><?= ops_e($summary) ?></textarea><small class="nf-count"><b id="nf-count"><?= mb_strlen($summary) ?></b> / 8000</small></span></label>

  <details class="nf-more nf-wide"<?= $extraOpen ? ' open' : '' ?>>
   <summary><span>Detail tambahan <em>(opsional)</em></span><small>Lokasi, agenda & program terkait, nama peserta, keputusan, kendala</small></summary>
   <div class="nf-more-body">
    <?= ops_field('location', 'Lokasi', 'text', $v('location'), array('maxlength' => 160, 'placeholder' => 'Contoh: Balai RT')) ?>
    <?= ops_field('participants', 'Nama / unsur peserta', 'text', $v('participants'), array('maxlength' => 1000, 'placeholder' => 'Pisahkan dengan koma')) ?>
    <?= ops_field('agenda_id', 'Agenda terkait', 'select', (string) $v('agenda_id'), array('options' => $agendas, 'placeholder_option' => 'Tidak terkait agenda')) ?>
    <?= ops_field('program_id', 'Program kerja terkait', 'select', (string) $v('program_id'), array('options' => $programs, 'placeholder_option' => 'Tidak terkait program')) ?>
    <?= ops_field('decisions', 'Keputusan', 'textarea', $v('decisions'), array('rows' => 3, 'wide' => TRUE)) ?>
    <?= ops_field('obstacles', 'Kendala', 'textarea', $v('obstacles'), array('rows' => 2, 'wide' => TRUE)) ?>
   </div>
  </details>

  <div class="nf-actions nf-wide">
   <a class="nf-cancel" href="<?= site_url($edit ? 'admin/notulen/' . (int) $item['id'] : 'admin/notulen') ?>">Batal</a>
   <button class="nf-save" type="submit" id="nf-save"><?= official_svg('save') ?><span><?= $saveAs === 'final' ? 'Simpan Notulen' : 'Simpan Draf' ?></span></button>
  </div>
 </form>
</div>
<script>
(function(){
 var f=document.getElementById('nf-form');if(!f)return;
 var sel=document.getElementById('nf-save-as'),box=document.getElementById('nf-status'),help=document.getElementById('nf-status-help'),btn=document.getElementById('nf-save');
 var sum=document.getElementById('nf-summary'),cnt=document.getElementById('nf-count'),count=f.querySelector('[name="participant_count"]');
 // Draf boleh belum lengkap; notulen wajib jumlah peserta & ringkasan (aturan yang sama dicek di server).
 function sync(){var fin=sel.value==='final';box.classList.toggle('is-final',fin);box.classList.toggle('is-draft',!fin);
  help.textContent=fin?'Notulen langsung terbit setelah disimpan.':'Draf belum diajukan; bisa dilengkapi nanti.';
  btn.querySelector('span').textContent=fin?'Simpan Notulen':'Simpan Draf';sum.required=fin;count.required=fin;count.min=fin?1:0;}
 sel.addEventListener('change',sync);sync();
 sum.addEventListener('input',function(){cnt.textContent=sum.value.length;});
 var sent=false;f.addEventListener('submit',function(e){if(sent){e.preventDefault();return;}sent=true;setTimeout(function(){btn.disabled=true;},0);});
})();
</script>
