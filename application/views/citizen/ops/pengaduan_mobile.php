<?php
/*
 * Pengaduan & Aspirasi — tampilan HP (desain 14 Nov 2026). Dipakai pengaduan_list & pengaduan_form.
 * Form dikirim ke warga/pengaduan/buat (validasi & penyimpanan tetap di controller Pengaduan::buat).
 * Variabel: $items, $categories, $labels, $tones, $open, $values, $emergency, $anonymous_allowed, $pgm_tab, $error (opsional).
 */
$values = $values ?? array(); $items = $items ?? array(); $emergency = $emergency ?? array();
$pgm_open = 0; foreach ($items as $it) if (in_array($it['status'], $open ?? array(), TRUE)) $pgm_open++;
$pgm_tone = array('pending' => 'wait', 'info' => 'proc', 'purple' => 'wait', 'ok' => 'done', 'muted' => 'done', 'bad' => 'bad');
$pgm_ticket = function ($it) use ($labels, $tones, $pgm_tone) {
    $t = $pgm_tone[$tones[$it['status']] ?? 'info'] ?? 'proc';
    return '<li><span class="pgm-tk-ic" aria-hidden="true">' . official_svg('doc') . '</span><div class="pgm-tk-tx"><strong>' . html_escape($it['ticket_no']) . '</strong><small>' . html_escape($it['title']) . '</small></div><em class="st-' . $t . '">' . html_escape($labels[$it['status']] ?? $it['status']) . '</em><a href="' . site_url('warga/pengaduan/' . (int) $it['id']) . '">Detail</a></li>';
};
$pgm_cat_icon = function_exists('official_svg') ? official_svg('grid') : '';
?>
<section class="pgm mw-only" aria-labelledby="pgm-title">
 <div class="pgm-title"><span class="pgm-title-ic" aria-hidden="true"><?= official_svg('megaphone') ?></span><div><h1 id="pgm-title">Pengaduan &amp; Aspirasi</h1><p>Laporkan masalah di lingkungan</p></div><img src="<?= base_url('assets/img/letters/mw-req-scene.png') ?>" alt="" aria-hidden="true"></div>
 <div class="pgm-tabs" role="tablist" aria-label="Pengaduan">
  <button type="button" role="tab" data-pgm-tab="ringkasan" aria-selected="false"><?= official_svg('chart') ?>Ringkasan</button>
  <button type="button" role="tab" data-pgm-tab="tiket" aria-selected="false"><?= official_svg('list') ?>Tiket Saya</button>
  <button type="button" role="tab" data-pgm-tab="buat" aria-selected="false" class="is-cta"><?= official_svg('plus') ?>Buat Pengaduan</button>
 </div>
 <?php if (!empty($error)): ?><div class="pgm-alert" role="alert"><?= official_svg('alert') ?><span><?= html_escape($error) ?></span></div><?php endif; ?>
 <?php if ($this->session->flashdata('notice')): ?><div class="pgm-ok" role="status"><?= official_svg('check-circle') ?><span><?= html_escape($this->session->flashdata('notice')) ?></span></div><?php endif; ?>
 <div class="pgm-stats" aria-label="Ringkasan pengaduan">
  <div class="s-total"><span><?= official_svg('doc') ?></span><p><small>Total</small><b><?= count($items) ?></b></p></div>
  <div class="s-proc"><span><?= official_svg('clock') ?></span><p><small>Diproses</small><b><?= $pgm_open ?></b></p></div>
  <div class="s-done"><span><?= official_svg('check-circle') ?></span><p><small>Selesai</small><b><?= count($items) - $pgm_open ?></b></p></div>
 </div>

 <div class="pgm-panel" data-pgm-panel="ringkasan" role="tabpanel" hidden>
  <a class="pgm-sos" href="<?= site_url('warga/kontak-penting') ?>"><span aria-hidden="true"><?= official_svg('alert') ?></span><div><strong>Keadaan darurat?</strong><small>Hubungi Polisi 110, Pemadam 113, Ambulans 118/119 atau kontak penting RT.</small></div><?= official_svg('chev-right') ?></a>
  <section class="pgm-list"><header><h2>Tiket Terbaru</h2><button type="button" data-pgm-tab="tiket">Lihat Semua <?= official_svg('arrow-right') ?></button></header><?php if ($items): ?><ul><?php foreach (array_slice($items, 0, 3) as $it) echo $pgm_ticket($it); ?></ul><?php else: ?><p class="pgm-empty">Belum ada pengaduan. Ketuk <b>Buat Pengaduan</b> untuk melapor.</p><?php endif; ?></section>
 </div>

 <div class="pgm-panel" data-pgm-panel="tiket" role="tabpanel" hidden>
  <section class="pgm-list"><header><h2>Tiket Saya</h2><small><?= count($items) ?> laporan</small></header><?php if ($items): ?><ul><?php foreach ($items as $it) echo $pgm_ticket($it); ?></ul><?php else: ?><p class="pgm-empty">Belum ada pengaduan.</p><?php endif; ?></section>
 </div>

 <div class="pgm-panel" data-pgm-panel="buat" role="tabpanel" hidden>
  <form method="post" enctype="multipart/form-data" action="<?= site_url('warga/pengaduan/buat') ?>" id="pgm-form" novalidate><?= ops_csrf() ?>
   <section class="pgm-card is-open" id="pgm-c-data"><button type="button" class="pgm-card-head" aria-expanded="true"><span class="pgm-card-ic"><?= official_svg('doc') ?></span><span>Data Laporan</span><svg class="pgm-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>
    <div class="pgm-body">
     <label class="pgm-f"><span>Kategori <b>*</b></span><span class="pgm-in"><?= official_svg('grid') ?><select name="category" id="pgm-category" required data-label="Kategori"><option value="">Pilih kategori</option><?php foreach ($categories as $k => $l): ?><option value="<?= html_escape($k) ?>"<?= ($values['category'] ?? '') === $k ? ' selected' : '' ?>><?= html_escape($l) ?></option><?php endforeach; ?></select></span></label>
     <label class="pgm-f"><span>Judul Singkat <b>*</b></span><span class="pgm-in"><?= official_svg('pencil') ?><input name="title" id="pgm-title-in" required minlength="5" maxlength="160" value="<?= html_escape($values['title'] ?? '') ?>" placeholder="Contoh: Jalan berlubang" data-label="Judul singkat" data-min="5"></span></label>
     <label class="pgm-f"><span>Lokasi</span><span class="pgm-in"><?= official_svg('pin') ?><input name="location" id="pgm-location" maxlength="190" value="<?= html_escape($values['location'] ?? '') ?>" placeholder="Pilih atau ketik lokasi" list="pgm-locations" autocomplete="off"></span></label>
     <datalist id="pgm-locations"><?php foreach (array('Depan pos ronda', 'Jalan utama RT', 'Gang 1', 'Gang 2', 'Gang 3', 'Taman / lapangan warga', 'Saluran air / got', 'Depan rumah saya') as $loc): ?><option value="<?= $loc ?>"><?php endforeach; ?></datalist>
     <label class="pgm-f"><span>Isi Laporan <b>*</b></span><span class="pgm-in is-area"><?= official_svg('pencil') ?><textarea name="description" id="pgm-desc" required minlength="10" maxlength="5000" rows="4" placeholder="Tulis laporan Anda..." data-label="Isi laporan" data-min="10"><?= html_escape($values['description'] ?? '') ?></textarea></span></label>
    </div>
   </section>
   <section class="pgm-card" id="pgm-c-file"><button type="button" class="pgm-card-head" aria-expanded="false"><span class="pgm-card-ic"><?= official_svg('image') ?></span><span>Lampiran <small>(Opsional)</small></span><svg class="pgm-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>
    <div class="pgm-body">
     <label class="pgm-file"><input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf" id="pgm-file"><span class="pgm-file-ic"><?= official_svg('upload') ?></span><strong id="pgm-file-name">Pilih foto atau dokumen</strong><small>JPG, PNG, WebP, atau PDF maksimal 5 MB. Hindari memotret wajah orang lain tanpa izin.</small></label>
     <?php if (!empty($anonymous_allowed)): ?><label class="pgm-anon"><input type="checkbox" name="is_anonymous" value="1"<?= !empty($values['is_anonymous']) ? ' checked' : '' ?>><span>Sembunyikan identitas saya dari petugas</span></label><?php endif; ?>
    </div>
   </section>
   <section class="pgm-prio"><span class="pgm-prio-ic" aria-hidden="true"><?= official_svg('alert') ?></span><strong>Prioritas</strong><div role="radiogroup" aria-label="Prioritas"><button type="button" role="radio" data-pgm-prio="normal" aria-checked="true"><?= official_svg('check-circle') ?>Normal</button><button type="button" role="radio" data-pgm-prio="darurat" aria-checked="false"><?= official_svg('alert') ?>Darurat</button></div></section>
   <div class="pgm-sos-note" id="pgm-sos-note" hidden><strong>Keadaan darurat?</strong> Pengaduan di aplikasi tidak menjamin respons langsung. Segera hubungi Polisi 110, Pemadam 113, Ambulans 118/119<?php foreach ($emergency as $c): ?> · <?= html_escape($c['name']) ?>: <a href="tel:<?= html_escape(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= html_escape($c['phone']) ?></a><?php endforeach; ?>.</div>
   <p class="pgm-msg" id="pgm-msg" role="alert" hidden></p>
   <div class="pgm-actions"><button type="button" class="pgm-draft" id="pgm-draft"><?= official_svg('doc') ?>Simpan Draft</button><button type="submit" class="pgm-send" id="pgm-send"><?= official_svg('send') ?>Kirim Pengaduan</button></div>
  </form>
  <?php if ($items): ?><section class="pgm-list is-compact"><ul><?php foreach (array_slice($items, 0, 2) as $it) echo $pgm_ticket($it); ?></ul></section><?php endif; ?>
 </div>
 <script type="application/json" id="pgm-cfg"><?= json_encode(array('tab' => $pgm_tab ?? 'buat', 'key' => 'sukamulya.pengaduan.draft.' . (int) $this->session->userdata('citizen_id'), 'sent' => (bool) $this->session->flashdata('notice')), JSON_HEX_TAG) ?></script>
</section>
<script src="<?= base_url('assets/pengaduan-mobile.js') ?>?v=<?= filemtime(FCPATH . 'assets/pengaduan-mobile.js') ?>" defer></script>
