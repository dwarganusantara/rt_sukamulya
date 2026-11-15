<?php
$this->load->helper(array('official_icons', 'page_ui'));
$labels = array('nomor_kk' => 'Nomor KK', 'alamat' => 'Alamat sesuai KK', 'rt' => 'RT', 'rw' => 'RW', 'kelurahan' => 'Kelurahan/Desa', 'kecamatan' => 'Kecamatan', 'kota' => 'Kota/Kabupaten', 'provinsi' => 'Provinsi', 'kode_pos' => 'Kode Pos',
    'nama_lengkap' => 'Nama Lengkap', 'nik' => 'NIK', 'jenis_kelamin' => 'Jenis Kelamin', 'tempat_lahir' => 'Tempat Lahir', 'tanggal_lahir' => 'Tanggal Lahir', 'agama' => 'Agama',
    'pendidikan' => 'Pendidikan', 'pekerjaan' => 'Pekerjaan', 'golongan_darah' => 'Gol. Darah', 'hubungan_keluarga' => 'Hubungan Keluarga');
$required = array('nomor_kk', 'alamat', 'nama_lengkap', 'nik', 'jenis_kelamin', 'hubungan_keluarga');
$step = $show_form ? 2 : 1;
$fam_review = (array) ($fields['keluarga']['fields_need_review'] ?? array());
$members = $fields['anggota'] ?: array(array('hubungan_keluarga' => 'Kepala Keluarga'));
$member_html = function ($i, $m, $tpl = FALSE) use ($member_fields, $labels, $required, $relationships, $scanned) {
    $needs = $scanned ? (array) ($m['fields_need_review'] ?? array()) : array();
    $name = function ($k) use ($i, $tpl) {return $tpl ? 'data-name="' . $k . '"' : 'name="anggota[' . $i . '][' . $k . ']"';};
    ob_start(); ?>
  <fieldset class="mkk-member">
   <legend><span class="mkk-member-no"><?= $tpl ? '' : $i + 1 ?></span>Anggota Keluarga</legend>
   <button type="button" class="mkk-remove" aria-label="Hapus anggota ini"><?= official_svg('trash') ?>Hapus</button>
   <div class="mkk-grid">
   <?php foreach ($member_fields as $k): $v = (string) ($m[$k] ?? ''); $req = in_array($k, $required, TRUE); ?>
    <label class="mkk-field<?= in_array($k, $needs, TRUE) ? ' needs-review' : '' ?> f-<?= $k ?>"><span><?= $labels[$k] ?><?= $req ? ' <b>*</b>' : '' ?></span>
    <?php if ($k === 'jenis_kelamin'): ?><select <?= $name($k) ?><?= $req ? ' required' : '' ?>><option value="">Pilih</option><option value="L"<?= $v === 'L' ? ' selected' : '' ?>>Laki-laki</option><option value="P"<?= $v === 'P' ? ' selected' : '' ?>>Perempuan</option></select>
    <?php elseif ($k === 'hubungan_keluarga'): ?><select <?= $name($k) ?> required><option value="">Pilih hubungan</option><?php foreach ($relationships as $o): ?><option value="<?= ops_e($o) ?>"<?= strcasecmp($v, $o) === 0 ? ' selected' : '' ?>><?= ops_e($o) ?></option><?php endforeach; ?></select>
    <?php elseif ($k === 'tanggal_lahir'): ?><input type="date" <?= $name($k) ?> value="<?= ops_e($v) ?>">
    <?php elseif ($k === 'nik'): ?><input <?= $name($k) ?> value="<?= ops_e($v) ?>" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" autocomplete="off" required title="NIK 16 digit angka">
    <?php else: ?><input <?= $name($k) ?> value="<?= ops_e($v) ?>" maxlength="<?= $k === 'golongan_darah' ? 5 : 140 ?>"<?= $req ? ' required' : '' ?>>
    <?php endif; ?>
    <?php if (in_array($k, $needs, TRUE)): ?><small>OCR kurang yakin, cocokkan dengan KK</small><?php endif; ?></label>
   <?php endforeach; ?>
   </div>
  </fieldset>
<?php return ob_get_clean(); };
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-master-kk.css') ?>?v=<?= filemtime(FCPATH . 'assets/admin-master-kk.css') ?>">
<div class="pg mkk mkk-form-page">
 <?= pg_crumb_back(array(array('Kependudukan', NULL), array('Data Keluarga / KK', 'admin/keluarga'), array($request_id ? 'Lanjutkan KK-' . str_pad($request_id, 6, '0', STR_PAD_LEFT) : 'Tambah KK', NULL)), 'admin/keluarga', 'Kembali ke Data Keluarga / KK') ?>
 <section class="mkk-hero small">
  <span class="mkk-hero-ic"><?= official_svg($mode === 'ocr' ? 'camera' : 'plus') ?></span>
  <div class="mkk-hero-tx"><h1><?= $mode === 'ocr' ? 'Tambah KK via Scan (OCR)' : 'Tambah KK Manual' ?></h1><p>KK yang diinput admin tidak membutuhkan akun warga. Status akun awal: <b>Belum punya akun</b>; undangan aktivasi bisa dikirim setelah KK terverifikasi.</p></div>
 </section>

 <ol class="mkk-stepper" aria-label="Tahapan">
  <?php foreach (array(1 => array('upload', $mode === 'ocr' ? 'Upload KK' : 'Pilih Cara Input'), 2 => array('pencil', $mode === 'ocr' ? 'Periksa OCR' : 'Isi Data KK'), 3 => array('shield', 'Verifikasi & Simpan')) as $n => $s): ?>
  <li class="<?= $n < $step ? 'done' : ($n === $step ? 'current' : '') ?>"<?= $n === $step ? ' aria-current="step"' : '' ?>><b><?= $n < $step ? official_svg('check') : $n ?></b><span><?= official_svg($s[0]) ?><?= ops_e($s[1]) ?></span></li>
  <?php endforeach; ?>
 </ol>

 <nav class="pg-tabs pg-tabs-solid mkk-mode" aria-label="Cara input">
  <a class="pg-tab" href="<?= site_url('admin/keluarga/baru?mode=ocr' . ($request_id ? '&draft=' . $request_id : '')) ?>"<?= $mode === 'ocr' ? ' aria-current="page"' : '' ?>><?= official_svg('camera') ?>Scan KK (OCR)</a>
  <a class="pg-tab" href="<?= site_url('admin/keluarga/baru?mode=manual' . ($request_id ? '&draft=' . $request_id : '')) ?>"<?= $mode === 'manual' ? ' aria-current="page"' : '' ?>><?= official_svg('pencil') ?>Input Manual</a>
 </nav>

 <?php if ($error): ?><div class="ops-note red mkk-note" role="alert"><?= official_svg('alert') ?><span><?= ops_e($error) ?></span></div><?php endif; ?>
 <?php if ($notice): ?><div class="ops-note amber mkk-note" role="status"><?= official_svg('bulb') ?><span><?= ops_e($notice) ?></span></div><?php endif; ?>

 <?php if ($mode === 'ocr'): ?>
 <section class="pg-card mkk-upload"<?= $show_form ? ' hidden' : '' ?> id="mkk-upload">
  <form method="post" enctype="multipart/form-data" id="mkk-scan-form"><?= ops_csrf() ?><input type="hidden" name="action" value="scan"><input type="hidden" name="mode" value="ocr"><?php if ($request_id): ?><input type="hidden" name="draft" value="<?= (int) $request_id ?>"><?php endif; ?>
   <label class="mkk-drop" for="mkk-doc"><span class="mkk-drop-ic"><?= official_svg('cloud') ?></span><strong>Pilih foto atau PDF Kartu Keluarga</strong><small>JPG, PNG, WEBP, HEIC, PDF (maks. 10 MB, PDF maks. 5 halaman)</small>
    <input id="mkk-doc" type="file" name="document" required accept="application/pdf,image/jpeg,image/png,image/webp,image/heic,image/heif,image/avif,image/bmp,image/tiff,image/gif,.pdf,.jpg,.jpeg,.png,.webp,.heic,.heif,.avif,.bmp,.tif,.tiff,.gif"><em id="mkk-doc-name"></em></label>
   <div class="mkk-upload-foot"><p><?= official_svg('shield') ?>Berkas hanya dipakai untuk pembacaan dan langsung dihapus dari server; tidak disimpan.</p><button class="pg-btn solid" type="submit" id="mkk-scan-btn"><?= official_svg('bolt') ?><span>Baca Data KK</span></button></div>
   <p class="mkk-progress" id="mkk-progress" role="status" hidden>Sedang membaca KK… proses dapat memakan waktu hingga 1–2 menit. Jangan menutup halaman.</p>
  </form>
 </section>
 <div class="mkk-loading" id="mkk-loading" hidden role="alertdialog" aria-modal="true" aria-labelledby="mkk-loading-title" aria-describedby="mkk-loading-text">
  <div class="mkk-loading-card" tabindex="-1">
   <div class="mkk-loading-doc" aria-hidden="true"><?= official_svg('doc') ?><span class="mkk-scan-line"></span><span class="mkk-loading-bolt"><?= official_svg('bolt') ?></span></div>
   <h2 id="mkk-loading-title">Sedang Membaca Data KK<span class="mkk-loading-dots"><i>.</i><i>.</i><i>.</i></span></h2>
   <p id="mkk-loading-text" aria-live="polite">Mengunggah file Kartu Keluarga</p>
   <div class="mkk-loading-bar"><span id="mkk-loading-bar"></span></div>
   <ol class="mkk-loading-steps" id="mkk-loading-steps"><li class="is-active"><b></b>Mengunggah file</li><li><b></b>Menyiapkan gambar</li><li><b></b>OCR membaca teks KK</li><li><b></b>Menyusun data anggota</li></ol>
   <div class="mkk-loading-time"><?= official_svg('clock') ?><span>Waktu berjalan: <strong id="mkk-loading-time">0 detik</strong></span></div>
   <small>Mohon jangan menutup atau memuat ulang halaman. Proses dapat memakan waktu hingga 1–2 menit.</small>
  </div>
 </div>
 <?php endif; ?>

 <?php if ($show_form): ?>
 <form method="post" class="mkk-form" id="mkk-form" novalidate><?= ops_csrf() ?>
  <input type="hidden" name="mode" value="<?= ops_e($mode) ?>"><?php if ($request_id): ?><input type="hidden" name="draft" value="<?= (int) $request_id ?>"><?php endif; ?>
  <section class="pg-card mkk-section mkk-fam-card" id="mkk-fam-card">
   <header><h2><?= official_svg('doc') ?>Data Kartu Keluarga</h2><?php if ($scanned): ?><span class="mkk-ocr-meta<?= count($fam_review) ? ' warn' : '' ?>"><?= official_svg('bolt') ?><?= count($fam_review) ? count($fam_review) . ' field keluarga perlu dicek' : 'Semua field keluarga terbaca' ?></span><?php endif; ?><button type="button" class="mkk-fold" id="mkk-fam-fold" aria-expanded="true" aria-controls="mkk-fam-grid" title="Sembunyikan data KK"><span class="sr-only">Buka/tutup Data Kartu Keluarga</span></button></header>
   <p class="mkk-fam-sum" id="mkk-fam-sum" hidden></p>
   <div class="mkk-grid fam" id="mkk-fam-grid">
   <?php foreach ($family_fields as $k): $req = in_array($k, $required, TRUE); ?>
    <label class="mkk-field<?= $scanned && in_array($k, $fam_review, TRUE) ? ' needs-review' : '' ?> f-<?= $k ?>"><span><?= $labels[$k] ?><?= $req ? ' <b>*</b>' : '' ?></span>
     <input name="keluarga[<?= $k ?>]" value="<?= ops_e($fields['keluarga'][$k] ?? '') ?>"<?= $k === 'nomor_kk' ? ' inputmode="numeric" pattern="[0-9]{16}" maxlength="16" autocomplete="off" title="Nomor KK 16 digit"' : (in_array($k, array('rt', 'rw'), TRUE) ? ' inputmode="numeric" maxlength="3"' : ' maxlength="140"') ?><?= $req ? ' required' : '' ?>>
     <?php if ($scanned && in_array($k, $fam_review, TRUE)): ?><small>OCR kurang yakin, cocokkan dengan KK</small><?php endif; ?></label>
   <?php endforeach; ?>
   </div>
  </section>

  <section class="pg-card mkk-section mkk-members-card">
   <header><h2><?= official_svg('people') ?>Anggota Keluarga (<span id="mkk-count"><?= count($members) ?></span>)</h2><span class="mkk-review-sum" id="mkk-review-sum" aria-live="polite"></span><button type="button" class="pg-btn outline small" id="mkk-add"><?= official_svg('plus') ?>Tambah Anggota</button></header>
   <div class="mkk-head-warn" id="mkk-head-warn" role="status" hidden></div>
   <div class="mkk-msum-wrap" id="mkk-msum-wrap" hidden>
    <table class="mkk-msum"><thead><tr><th class="no">No</th><th>Nama Lengkap</th><th>NIK</th><th>Hubungan Keluarga</th><th>Pemeriksaan</th><th class="act">Aksi</th></tr></thead><tbody id="mkk-msum-body"></tbody></table>
   </div>
   <div id="mkk-members" class="mkk-member-store"><?php foreach ($members as $i => $m) echo $member_html($i, $m); ?></div>
   <p class="mkk-hint">Isi sesuai yang tercantum pada KK. Harus ada tepat satu <b>Kepala Keluarga</b>. NIK yang sudah terdaftar di KK lain tidak bisa diinput ulang (gunakan Mutasi Warga). Status pemeriksaan hanya penanda kerja di halaman ini, bukan status verifikasi KK.</p>
  </section>

  <div class="mkk-drawer" id="mkk-drawer" hidden>
   <div class="mkk-drawer-backdrop" data-drawer="cancel"></div>
   <div class="mkk-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="mkk-drawer-title" tabindex="-1">
    <header class="mkk-drawer-head"><div><small id="mkk-drawer-pos"></small><h2 id="mkk-drawer-title">Periksa Data Anggota</h2></div><button type="button" class="mkk-drawer-x" data-drawer="cancel" aria-label="Tutup panel"><?= official_svg('close') ?></button></header>
    <p class="mkk-drawer-tip" id="mkk-drawer-tip" hidden><?= official_svg('alert') ?><span></span></p>
    <div class="mkk-drawer-body" id="mkk-drawer-slot"></div>
    <footer class="mkk-drawer-foot">
     <div class="mkk-drawer-nav"><button type="button" class="pg-btn ghost small" data-drawer="prev"><?= official_svg('back') ?>Sebelumnya</button><button type="button" class="pg-btn ghost small" data-drawer="next">Berikutnya<?= official_svg('chev-right') ?></button></div>
     <div class="mkk-drawer-main"><button type="button" class="pg-btn ghost" data-drawer="cancel">Batal</button><button type="button" class="pg-btn solid" data-drawer="save"><?= official_svg('check-circle') ?>Simpan Perubahan</button></div>
    </footer>
   </div>
  </div>

  <section class="pg-card mkk-section mkk-submit">
   <?php if (!empty($dupes['family'])): ?>
   <div class="ops-note amber"><?= official_svg('alert') ?><span><strong>Nomor KK sudah terdaftar di Master KK.</strong> Menyimpan akan <b>memperbarui</b> data KK tersebut (anggota yang tidak dicantumkan tidak dihapus). Pastikan memang KK yang sama.
    <label class="mkk-check"><input type="checkbox" name="update_existing" value="1"<?= $this->input->post('update_existing') ? ' checked' : '' ?>> Perbarui data KK yang sudah ada</label></span></div>
   <?php endif; ?>
   <label class="mkk-check"><input type="checkbox" name="checked" value="1"<?= $this->input->post('checked') ? ' checked' : '' ?>> Saya sudah mencocokkan seluruh data dan jumlah anggota dengan dokumen KK asli.</label>
   <div class="mkk-buttons">
    <a class="pg-btn ghost" href="<?= site_url('admin/keluarga') ?>">Batal</a>
    <button class="pg-btn outline" type="submit" name="action" value="draft" formnovalidate><?= official_svg('save') ?>Simpan Draft</button>
    <button class="pg-btn <?= $can_verify ? 'outline' : 'solid' ?>" type="submit" name="action" value="submit"><?= official_svg('send') ?>Kirim untuk Verifikasi</button>
    <?php if ($can_verify): ?><button class="pg-btn solid" type="submit" name="action" value="verify"><?= official_svg('check-circle') ?>Simpan &amp; Verifikasi</button><?php endif; ?>
   </div>
   <p class="mkk-hint"><?= $can_verify ? '<b>Simpan &amp; Verifikasi</b> langsung memasukkan KK ke Master. <b>Kirim untuk Verifikasi</b> meminta persetujuan verifikator lain.' : 'Peran Anda belum dapat memverifikasi. KK akan masuk antrean Verifikasi KK untuk disetujui Ketua RT / Admin.' ?></p>
  </section>
 </form>
 <template id="mkk-member-tpl"><?= $member_html(0, array(), TRUE) ?></template>
 <?php endif; ?>
</div>
<script>
(function () {
  var doc = document.getElementById('mkk-doc');
  if (doc) doc.addEventListener('change', function () { document.getElementById('mkk-doc-name').textContent = doc.files[0] ? doc.files[0].name : ''; });
  var scan = document.getElementById('mkk-scan-form'), loadingTimer;
  function resetScanLoading() {
    var loading = document.getElementById('mkk-loading'), b = document.getElementById('mkk-scan-btn');
    if (loading) loading.hidden = true;
    document.body.classList.remove('mkk-no-scroll');
    clearInterval(loadingTimer);
    if (b) { b.disabled = false; b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); b.querySelector('span').textContent = 'Baca Data KK'; }
  }
  if (scan) scan.addEventListener('submit', function () {
    var progress = document.getElementById('mkk-progress'), b = document.getElementById('mkk-scan-btn');
    var loading = document.getElementById('mkk-loading'), steps = Array.prototype.slice.call(document.querySelectorAll('#mkk-loading-steps li'));
    var text = document.getElementById('mkk-loading-text'), bar = document.getElementById('mkk-loading-bar'), time = document.getElementById('mkk-loading-time');
    var plan = [[0, 0, 'Mengunggah file Kartu Keluarga'], [3, 1, 'Menyiapkan dan merapikan gambar KK'], [7, 2, 'OCR sedang membaca teks pada KK'], [20, 3, 'Menyusun data keluarga dan anggota'], [45, 3, 'Hampir selesai, mohon tunggu sebentar lagi']];
    var started = Date.now();
    progress.hidden = false;
    b.disabled = true; b.classList.add('is-loading'); b.setAttribute('aria-busy', 'true'); b.querySelector('span').textContent = 'Sedang Membaca…';
    loading.hidden = false; document.body.classList.add('mkk-no-scroll'); loading.querySelector('.mkk-loading-card').focus();
    function tick() {
      var elapsed = Math.floor((Date.now() - started) / 1000), current = plan[0];
      plan.forEach(function (item) { if (elapsed >= item[0]) current = item; });
      steps.forEach(function (step, i) { step.className = i < current[1] ? 'is-done' : (i === current[1] ? 'is-active' : ''); });
      text.textContent = current[2]; time.textContent = elapsed + ' detik'; bar.style.width = Math.min(94, 8 + elapsed * 1.35) + '%';
    }
    tick(); loadingTimer = setInterval(tick, 1000);
  });
  window.addEventListener('pageshow', function (event) { if (event.persisted) resetScanLoading(); });
  var box = document.getElementById('mkk-members'), tpl = document.getElementById('mkk-member-tpl'), form = document.getElementById('mkk-form');
  if (!box || !tpl) return;
  /*
   * Anggota: input asli tetap satu-satunya sumber data (name="anggota[i][field]") dan selalu berada di dalam form.
   * Daftar ringkas dibangun dari input tersebut; panel "Periksa / Edit" MEMINDAHKAN fieldset anggota ke drawer
   * (bukan menyalin), jadi tidak pernah ada dua input bernama sama. Status pemeriksaan hanya state UI.
   */
  var body = document.getElementById('mkk-msum-body'), drawer = document.getElementById('mkk-drawer'), slot = document.getElementById('mkk-drawer-slot');
  var panel = drawer.querySelector('.mkk-drawer-panel'), sum = document.getElementById('mkk-review-sum'), headWarn = document.getElementById('mkk-head-warn');
  var ICON = {edit: <?= json_encode(official_svg('pencil')) ?>, trash: <?= json_encode(official_svg('trash')) ?>, ok: <?= json_encode(official_svg('check-circle')) ?>, warn: <?= json_encode(official_svg('alert')) ?>};
  var open = null, snapshot = null, lastFocus = null;

  // Kartu "Data Kartu Keluarga" bisa dibuka/tutup. Input tetap di dalam form (hanya disembunyikan), jadi tetap terkirim.
  var famCard = document.getElementById('mkk-fam-card'), famFold = document.getElementById('mkk-fam-fold'), famSum = document.getElementById('mkk-fam-sum');
  function famVal(k) { var el = form.querySelector('[name="keluarga[' + k + ']"]'); return el ? el.value.trim() : ''; }
  function setFam(collapsed) {
    famCard.classList.toggle('is-collapsed', collapsed);
    famFold.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    famFold.title = collapsed ? 'Tampilkan data KK' : 'Sembunyikan data KK';
    if (collapsed) {
      var kk = famVal('nomor_kk'), rt = famVal('rt'), rw = famVal('rw');
      var bits = [kk ? 'No. KK ' + (kk.length > 8 ? kk.slice(0, 4) + '••••••••' + kk.slice(-4) : kk) : 'No. KK belum diisi', famVal('alamat') || 'Alamat belum diisi'];
      if (rt || rw) bits.push('RT ' + (rt || '-') + ' / RW ' + (rw || '-'));
      if (famVal('kelurahan')) bits.push(famVal('kelurahan'));
      famSum.textContent = bits.join(' • ');
    }
    famSum.hidden = !collapsed;
  }
  famFold.addEventListener('click', function () { setFam(!famCard.classList.contains('is-collapsed')); });
  form.classList.add('mkk-js'); document.getElementById('mkk-msum-wrap').hidden = false;

  function members() { return Array.prototype.slice.call(box.querySelectorAll('.mkk-member')).concat(open ? [open] : []).sort(function (a, b) { return (+a.dataset.idx) - (+b.dataset.idx); }); }
  function val(fs, k) { var el = fs.querySelector('[name$="[' + k + ']"],[data-name="' + k + '"]'); return el ? el.value.trim() : ''; }
  function inputs(fs) { return Array.prototype.slice.call(fs.querySelectorAll('input,select')); }
  function mask(n) { n = n.replace(/\s+/g, ''); return n.length > 8 ? n.slice(0, 4) + '••••••••' + n.slice(-4) : (n || '—'); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  // NIK ganda di dalam isian yang sama ditandai sebagai kesalahan (server tetap memvalidasi ulang).
  function markDuplicateNik(list) {
    var seen = {};
    list.forEach(function (fs) { var n = val(fs, 'nik'); if (n) seen[n] = (seen[n] || 0) + 1; });
    list.forEach(function (fs) { var el = fs.querySelector('[name$="[nik]"]'); if (el) el.setCustomValidity(el.value.trim() && seen[el.value.trim()] > 1 ? 'NIK ini dipakai lebih dari satu anggota.' : ''); });
  }
  function state(fs) {
    if (inputs(fs).some(function (el) { return !el.checkValidity(); })) return 'error';
    return fs.dataset.checked === '1' ? 'ok' : 'review';
  }
  function render() {
    var list = members(), counts = {ok: 0, review: 0, error: 0}, heads = 0;
    list.forEach(function (fs, i) {
      fs.dataset.idx = i;
      fs.querySelector('.mkk-member-no').textContent = i + 1;
      inputs(fs).forEach(function (el) {
        var key = el.getAttribute('data-name') || el.name.replace(/^anggota\[\d+\]\[(\w+)\]$/, '$1');
        el.name = 'anggota[' + i + '][' + key + ']'; el.removeAttribute('data-name');
      });
      if (/^kepala keluarga$/i.test(val(fs, 'hubungan_keluarga'))) heads++;
    });
    markDuplicateNik(list);
    body.innerHTML = '';
    list.forEach(function (fs, i) {
      var st = state(fs), flagged = fs.querySelectorAll('.needs-review').length, name = val(fs, 'nama_lengkap'), rel = val(fs, 'hubungan_keluarga');
      counts[st]++;
      var label = st === 'ok' ? 'Sudah diperiksa' : (st === 'error' ? 'Ada kesalahan' : 'Perlu diperiksa');
      var note = st === 'error' ? 'Field wajib kosong / format salah' : (st === 'review' && flagged ? flagged + ' field ditandai OCR' : '');
      var tr = document.createElement('tr'); tr.className = 'is-' + st; tr.dataset.idx = i;
      tr.innerHTML = '<td class="no" data-label="No">' + (i + 1) + '</td>' +
        '<td class="nm" data-label="Nama Lengkap">' + (name ? '<strong>' + esc(name) + '</strong>' : '<em>Nama belum diisi</em>') + '</td>' +
        '<td class="nik pg-mono" data-label="NIK">' + esc(mask(val(fs, 'nik'))) + '</td>' +
        '<td data-label="Hubungan">' + (rel ? esc(rel) : '<em>Belum dipilih</em>') + '</td>' +
        '<td data-label="Pemeriksaan"><span class="mkk-rv ' + st + '">' + (st === 'ok' ? ICON.ok : ICON.warn) + label + '</span>' + (note ? '<small>' + note + '</small>' : '') + '</td>' +
        '<td class="act"><button type="button" class="mkk-rv-btn" data-edit="' + i + '">' + ICON.edit + (st === 'ok' ? 'Lihat / Edit' : 'Periksa / Edit') + '</button>' +
        (list.length > 1 ? '<button type="button" class="mkk-rv-del" data-del="' + i + '" title="Hapus anggota" aria-label="Hapus anggota ' + (i + 1) + '">' + ICON.trash + '</button>' : '') + '</td>';
      body.appendChild(tr);
    });
    document.getElementById('mkk-count').textContent = list.length;
    var parts = [list.length + ' anggota'];
    if (counts.review) parts.push(counts.review + ' perlu diperiksa');
    if (counts.error) parts.push(counts.error + ' ada kesalahan');
    if (counts.ok) parts.push(counts.ok + ' sudah diperiksa');
    sum.textContent = parts.join(' • ');
    sum.className = 'mkk-review-sum' + (counts.error ? ' bad' : (counts.review ? ' warn' : ' ok'));
    headWarn.hidden = heads === 1;
    headWarn.innerHTML = ICON.warn + '<span>Harus ada tepat satu <b>Kepala Keluarga</b> (saat ini ' + heads + ').</span>';
    return counts;
  }

  function values(fs) { return inputs(fs).map(function (el) { return el.value; }); }
  function openMember(i, isNew) {
    var list = members(); if (!list[i]) return;
    if (open) putBack();
    open = list[i]; snapshot = values(open); open.dataset.isNew = isNew ? '1' : (open.dataset.isNew || '');
    lastFocus = lastFocus || document.activeElement;
    slot.appendChild(open);
    document.getElementById('mkk-drawer-pos').textContent = 'Anggota ' + (i + 1) + ' dari ' + list.length;
    document.getElementById('mkk-drawer-title').textContent = val(open, 'nama_lengkap') || 'Anggota baru';
    var flagged = open.querySelectorAll('.needs-review').length, tip = document.getElementById('mkk-drawer-tip');
    tip.hidden = !flagged; tip.querySelector('span').textContent = flagged + ' field ditandai kuning karena OCR kurang yakin. Cocokkan dengan KK asli.';
    drawer.querySelector('[data-drawer="prev"]').disabled = i === 0;
    drawer.querySelector('[data-drawer="next"]').disabled = i === list.length - 1;
    drawer.hidden = false; document.body.classList.add('mkk-no-scroll');
    slot.scrollTop = 0;
    var first = open.querySelector('.needs-review input,.needs-review select') || open.querySelector('input,select');
    setTimeout(function () { (first || panel).focus(); }, 30);
  }
  function putBack() {
    if (!open) return;
    var list = Array.prototype.slice.call(box.querySelectorAll('.mkk-member')), idx = +open.dataset.idx, after = null;
    list.forEach(function (fs) { if (!after && +fs.dataset.idx > idx) after = fs; });
    box.insertBefore(open, after); open = null; snapshot = null;
  }
  function closeDrawer() {
    putBack(); drawer.hidden = true; document.body.classList.remove('mkk-no-scroll'); render();
    if (lastFocus && document.contains(lastFocus)) lastFocus.focus(); lastFocus = null;
  }
  function validateOpen() {
    markDuplicateNik(members());
    var bad = inputs(open).filter(function (el) { return !el.checkValidity(); });
    if (bad.length) { bad[0].reportValidity(); return false; }
    return true;
  }
  function changed() { return snapshot && values(open).some(function (v, i) { return v !== snapshot[i]; }); }
  function cancel() {
    if (!open) return;
    if (open.dataset.isNew === '1' && !changed()) { open.remove(); open = null; closeDrawer(); return; }
    if (changed()) {
      if (!confirm('Perubahan pada anggota ini belum disimpan. Buang perubahan?')) return;
      inputs(open).forEach(function (el, i) { el.value = snapshot[i]; });
      if (open.dataset.isNew === '1') { open.remove(); open = null; }
    }
    closeDrawer();
  }
  function save() {
    if (!validateOpen()) return false;
    open.dataset.checked = '1'; open.dataset.isNew = '';
    return true;
  }
  function step(dir) {
    // Pindah anggota tanpa menandai "Sudah diperiksa"; perubahan yang diketik tetap dipertahankan.
    var i = +open.dataset.idx + dir; open.dataset.isNew = ''; putBack(); render(); openMember(i);
  }

  drawer.addEventListener('click', function (e) {
    var b = e.target.closest('[data-drawer]'); if (!b) return;
    var a = b.getAttribute('data-drawer');
    if (a === 'cancel') cancel();
    else if (a === 'save') { if (save()) closeDrawer(); }
    else if (a === 'prev') step(-1);
    else if (a === 'next') step(1);
  });
  drawer.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { e.preventDefault(); cancel(); return; }
    // Enter di dalam panel tidak boleh mengirim seluruh form.
    if (e.key === 'Enter' && /^(INPUT|SELECT)$/.test(e.target.tagName)) { e.preventDefault(); if (save()) closeDrawer(); return; }
    if (e.key === 'Tab') {
      var f = Array.prototype.slice.call(panel.querySelectorAll('button:not([disabled]),input,select')).filter(function (el) { return el.offsetParent !== null; });
      if (!f.length) return;
      if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
      else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
    }
  });
  body.addEventListener('click', function (e) {
    var ed = e.target.closest('[data-edit]'), del = e.target.closest('[data-del]');
    if (ed) { lastFocus = ed; openMember(+ed.getAttribute('data-edit')); }
    if (del) {
      var fs = members()[+del.getAttribute('data-del')], nm = val(fs, 'nama_lengkap');
      if (confirm('Hapus ' + (nm ? '"' + nm + '"' : 'anggota ini') + ' dari isian KK?')) { fs.remove(); render(); }
    }
  });
  document.getElementById('mkk-add').addEventListener('click', function () {
    var frag = tpl.content.cloneNode(true), fs = frag.querySelector('.mkk-member');
    fs.dataset.idx = 9999; box.appendChild(frag); render();
    lastFocus = this; openMember(members().length - 1, true);
  });
  // Fallback tanpa JS: tombol hapus bawaan fieldset (disembunyikan saat mode ringkas aktif).
  box.addEventListener('click', function (e) {
    var b = e.target.closest('.mkk-remove'); if (!b) return;
    if (confirm('Hapus anggota ini dari isian?')) { b.closest('.mkk-member').remove(); render(); }
  });

  var sending = false;
  form.addEventListener('submit', function (e) {
    var a = e.submitter ? e.submitter.value : 'submit';
    if (sending) { e.preventDefault(); return; }
    if (open) { e.preventDefault(); return; }
    if (a !== 'draft') {
      var counts = render();
      var fam = Array.prototype.slice.call(form.querySelectorAll('[name^="keluarga["]')).filter(function (el) { return !el.checkValidity(); });
      if (fam.length) { e.preventDefault(); setFam(false); fam[0].reportValidity(); return; }
      if (counts.error) {
        e.preventDefault();
        var bad = members().filter(function (fs) { return state(fs) === 'error'; })[0];
        openMember(+bad.dataset.idx); setTimeout(validateOpen, 60); return;
      }
      if (!headWarn.hidden) { e.preventDefault(); headWarn.scrollIntoView({behavior: 'smooth', block: 'center'}); return; }
      var ck = form.querySelector('[name="checked"]');
      if (ck && !ck.checked) { e.preventDefault(); ck.focus(); ck.closest('.mkk-check').classList.add('is-missing'); alert('Centang konfirmasi bahwa data sudah dicocokkan dengan dokumen KK asli.'); return; }
      if (counts.review && !confirm(counts.review + ' anggota belum ditandai "Sudah diperiksa". Tetap lanjutkan?')) { e.preventDefault(); return; }
      if (a === 'verify' && !confirm('Simpan dan verifikasi KK ini sekarang? Data langsung masuk Master KK.')) { e.preventDefault(); return; }
    }
    // Cegah simpan ganda; tombol dinonaktifkan setelah data form terkirim (nilai tombol pengirim tetap ikut).
    sending = true;
    var btn = e.submitter;
    setTimeout(function () {
      form.querySelectorAll('.mkk-buttons button').forEach(function (b) { b.disabled = true; });
      if (btn) { btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true'); }
    }, 0);
  });
  window.addEventListener('pageshow', function (ev) { if (ev.persisted) { sending = false; form.querySelectorAll('.mkk-buttons button').forEach(function (b) { b.disabled = false; b.classList.remove('is-loading'); }); } });
  form.addEventListener('change', function (e) { if (e.target.name === 'checked') e.target.closest('.mkk-check').classList.remove('is-missing'); });
  render();
})();
</script>
