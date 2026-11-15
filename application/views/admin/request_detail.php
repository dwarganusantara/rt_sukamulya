<?php
$this->load->helper('official_icons');
$extra = json_decode($item['extra_data'] ?? '', TRUE) ?: array();
$canApprove = in_array($this->session->userdata('admin_role'), array('admin', 'ketua'), TRUE);
$title = $catalog[$item['type']][0] ?? $item['subject'];
$statusLabels = array('diajukan' => 'Diajukan', 'diproses' => 'Diproses', 'disetujui' => 'Disetujui', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak');
$statusIcons = array('diajukan' => 'clock', 'diproses' => 'hourglass', 'disetujui' => 'check-circle', 'selesai' => 'check-circle', 'ditolak' => 'close');
$statusLabel = $statusLabels[$item['status']] ?? ucfirst($item['status']);
$statusIcon = $statusIcons[$item['status']] ?? 'clock';
$applicant = $item['applicant_name'] ?: $item['display_name'];
$detailRows = array(
    array('Akun Warga', $item['display_name'], 'person'),
    array('Nama Pemohon', $applicant, 'person'),
    array('NIK', $extra['applicant_nik'] ?? '-', 'doc'),
    array('No. HP', $extra['applicant_phone'] ?? '-', 'phone'),
    array('Alamat', $item['address'] ?: '-', 'pin'),
    array('Keperluan', $item['purpose'] ?: '-', 'list'),
);
$citizenRows = array(
    array('Tempat Lahir', $extra['applicant_birth_place'] ?? '-', 'pin'),
    array('Tanggal Lahir', $extra['applicant_birth_date'] ?? '-', 'calendar'),
    array('Jenis Kelamin', $extra['applicant_gender'] ?? '-', 'people'),
    array('Agama', $extra['applicant_religion'] ?? '-', 'home'),
    array('Pekerjaan', $extra['applicant_occupation'] ?? '-', 'briefcase'),
    array('Alamat Sesuai KTP', $extra['applicant_ktp_address'] ?? '-', 'home'),
);
$css = 'assets/admin-request-detail-v2.css';
// Ikon field khusus dipilih dari nama/label field (hanya tampilan).
$fieldIcon = function ($field, $label) {
    $t = strtolower($field . ' ' . $label);
    if (preg_match('/tanggal|sejak|berlaku|mulai|lahir|waktu|periode|tahun/', $t)) return 'calendar';
    if (preg_match('/alamat|tempat tinggal|status_tempat|status tempat|rumah|domisili|hunian/', $t)) return 'home';
    if (preg_match('/nama|kepala|pemilik|ahli|ayah|ibu|anak|pasangan|saksi/', $t)) return 'people';
    if (preg_match('/instansi|perusahaan|sekolah|kantor|usaha|lembaga/', $t)) return 'building';
    if (preg_match('/tujuan|keperluan|jenis|urusan|keterangan/', $t)) return 'list';
    return 'doc';
};
$letterUrl = site_url('warga/surat/' . $item['id']);
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">

<div class="ard">
 <nav class="ard-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>"><?= official_svg('home') ?></a><span><?= official_svg('chev-right') ?></span><a href="<?= site_url('admin/requests') ?>">Pengajuan Warga</a><span><?= official_svg('chev-right') ?></span><strong><?= html_escape($title) ?></strong></nav>

 <section class="ard-hero">
  <span class="ard-hero-icon" aria-hidden="true"><?= official_svg('doc') ?></span>
  <div class="ard-hero-copy"><span>Pengajuan Warga</span><h1><?= html_escape($title) ?></h1><p>Pengajuan #<?= (int) $item['id'] ?> <i></i> <?= official_svg('calendar') ?><?= date('d M Y, H:i', strtotime($item['created_at'])) ?></p></div>
  <div class="ard-hero-art" aria-hidden="true"><span><?= official_svg('doc') ?></span><b><?= official_svg('check-circle') ?></b></div>
  <a class="ard-back" href="<?= site_url('admin/requests') ?>"><?= official_svg('back') ?>Daftar Pengajuan</a>
 </section>

 <div class="ard-layout">
  <main class="ard-left">
   <section class="ard-card">
    <header class="ard-card-head navy"><h2><?= official_svg('person') ?>Data Pemohon</h2></header>
    <div class="ard-data-grid">
     <?php foreach ($detailRows as $row): ?><div class="ard-data"><span><?= official_svg($row[2]) ?></span><p><small><?= html_escape($row[0]) ?></small><?php $v = (string) ($row[1] !== '' ? $row[1] : '-'); ?><strong title="<?= html_escape($v) ?>"><?= html_escape($v) ?></strong></p></div><?php endforeach; ?>
    </div>
   </section>

   <section class="ard-card ard-card-tinted green">
    <header class="ard-card-head green"><h2><?= official_svg('people') ?>Data Warga untuk Surat</h2></header>
    <div class="ard-data-grid">
     <?php foreach ($citizenRows as $row): ?><div class="ard-data"><span><?= official_svg($row[2]) ?></span><p><small><?= html_escape($row[0]) ?></small><?php $v = (string) ($row[1] !== '' ? $row[1] : '-'); ?><strong title="<?= html_escape($v) ?>"><?= html_escape($v) ?></strong></p></div><?php endforeach; ?>
    </div>
   </section>

   <?php if ($type_fields): ?><section class="ard-card ard-card-tinted orange">
    <header class="ard-card-head orange"><h2><?= official_svg('doc') ?>Data Khusus <?= html_escape($title) ?></h2></header>
    <div class="ard-special-row" style="--ard-cols:<?= min(4, max(1, count($type_fields))) ?>">
     <?php foreach ($type_fields as $definition): list($field, $label) = $definition; ?><div class="ard-data"><span><?= official_svg($fieldIcon($field, $label)) ?></span><p><small><?= html_escape($label) ?></small><strong><?= html_escape((string) (($extra[$field] ?? '') ?: '—')) ?></strong></p></div><?php endforeach; ?>
    </div>
   </section><?php endif; ?>

   <div class="ard-bottom-grid">
    <section class="ard-mini-card green"><h2><?= official_svg('alert') ?>Keterangan Pengajuan</h2><p><?= nl2br(html_escape($item['description'] ?: '—')) ?></p></section>
    <section class="ard-mini-card purple"><h2><?= official_svg('clip') ?>Dokumen Pendukung</h2><div class="ard-documents">
     <?php foreach ($documents as $document): ?><a href="<?= site_url('warga/dokumen/' . $document['id']) ?>" target="_blank" rel="noopener"><?= official_svg('doc') ?><span><?= html_escape($document['label']) ?></span><?= official_svg('arrow-right') ?></a><?php endforeach; ?>
     <?php if ($item['attachment']): ?><a href="<?= site_url('warga/lampiran/' . $item['id']) ?>" target="_blank" rel="noopener"><?= official_svg('clip') ?><span>Lampiran tambahan</span><?= official_svg('arrow-right') ?></a><?php endif; ?>
     <?php if (!$documents && !$item['attachment']): ?><p>Belum ada dokumen yang dilampirkan.</p><?php endif; ?>
    </div></section>
   </div>
  </main>

  <aside class="ard-card ard-review">
   <header class="ard-review-head"><span><?= official_svg('check-circle') ?></span><h2>Tinjau Pengajuan</h2></header>
   <div class="ard-current <?= html_escape($item['status']) ?>"><strong>Status Saat Ini</strong><span><?= official_svg($statusIcon) ?><?= html_escape($statusLabel) ?></span></div>
   <form method="post" class="ard-review-form">
    <label for="review-status">Ubah Status</label>
    <span class="ard-select" data-state="<?= html_escape($item['status']) ?>"><?= official_svg($statusIcon) ?><select name="status" id="review-status"><?php foreach (array('diproses' => 'Diproses', 'ditolak' => 'Ditolak', 'disetujui' => 'Disetujui') as $status => $label): if (in_array($status, array('disetujui', 'ditolak', 'selesai'), TRUE) && !$canApprove) continue; ?><option value="<?= $status ?>"<?= $item['status'] === $status ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></span>

    <label for="letter-number">Nomor Surat <small>Wajib sebelum persetujuan. Gunakan nomor surat resmi.</small></label>
    <span class="ard-input"><?= official_svg('doc') ?><input name="letter_number" id="letter-number" maxlength="100" value="<?= html_escape($item['letter_number'] ?? '') ?>" placeholder="Masukkan nomor surat"></span>

    <label for="admin-note">Catatan Pengurus</label>
    <span class="ard-textarea"><?= official_svg('pencil') ?><textarea name="admin_note" id="admin-note" rows="6" maxlength="500" placeholder="Tuliskan catatan atau alasan penolakan..."><?= html_escape($item['admin_note'] ?? '') ?></textarea><small><b id="admin-note-count"><?= mb_strlen((string) ($item['admin_note'] ?? '')) ?></b> / 500</small></span>

    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <button class="ard-submit" type="submit"><?= official_svg('save') ?>Simpan Status</button>
    <p class="ard-help"><?= official_svg('info') ?><span>Jika status berubah, warga akan menerima popup pemberitahuan saat membuka Portal Warga.</span></p>
   </form>
   <?php if ($item['status'] === 'disetujui' && $item['letter_number']): ?><details class="ard-print-menu">
    <summary class="ard-print"><?= official_svg('print') ?>Lihat / Cetak Surat</summary>
    <div role="menu">
     <a role="menuitem" href="<?= $letterUrl ?>" target="_blank" rel="noopener"><?= official_svg('eye') ?><span>Lihat Surat<small>Buka di tab baru</small></span></a>
     <a role="menuitem" href="<?= $letterUrl ?>" target="_blank" rel="noopener" data-print><?= official_svg('print') ?><span>Cetak / Simpan PDF<small>Buka lalu tampilkan dialog cetak</small></span></a>
    </div>
   </details><?php endif; ?>
   <p class="ard-help"><?= official_svg('alert') ?><span>Kop surat dan identitas penanda tangan dapat diubah di <a href="<?= site_url('admin/letter_settings') ?>">Pengaturan Surat RT</a>.</span></p>
  </aside>
 </div>
</div>

<script>
(function(){
 var status=document.getElementById('review-status'),number=document.getElementById('letter-number'),select=status&&status.closest('.ard-select'),note=document.getElementById('admin-note'),counter=document.getElementById('admin-note-count');
 function updateStatus(){if(!status)return;number.required=status.value==='disetujui'&&<?= $item['type'] === 'aduan' ? 'false' : 'true' ?>;if(select)select.dataset.state=status.value;}
 function updateCount(){if(note&&counter)counter.textContent=note.value.length;}
 if(status)status.addEventListener('change',updateStatus);if(note)note.addEventListener('input',updateCount);updateStatus();updateCount();
 // Menu Lihat / Cetak Surat: tutup saat klik di luar atau Esc; "Cetak" membuka surat lalu memanggil dialog cetak (same-origin).
 var menu=document.querySelector('.ard-print-menu');
 if(menu){
  document.addEventListener('click',function(e){if(menu.open&&!menu.contains(e.target))menu.open=false;});
  menu.addEventListener('keydown',function(e){if(e.key==='Escape'){menu.open=false;menu.querySelector('summary').focus();}});
  var pr=menu.querySelector('[data-print]');
  if(pr)pr.addEventListener('click',function(e){var w=window.open(pr.href,'_blank');if(!w)return;e.preventDefault();menu.open=false;w.addEventListener('load',function(){setTimeout(function(){w.focus();w.print();},300);});});
 }
})();
</script>
