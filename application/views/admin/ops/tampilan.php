<?php
$base = site_url('admin/tampilan');
$cur = $work['themeId'];
$activeTheme = $active['config']['themeId'];
$dirty = $has_draft && $work != $active['config'];
$labels = array('primary' => 'Primer', 'secondary' => 'Sekunder', 'accent' => 'Aksen (tombol & menu aktif)', 'background' => 'Latar', 'surface' => 'Kartu / Surface', 'text' => 'Teks utama');
$tabIcons = array('tema' => 'image', 'dashboard' => 'grid', 'formulir' => 'doc', 'tata-letak' => 'layers', 'logo' => 'star', 'riwayat' => 'clock');
$csrf = ops_csrf();
$mini = function ($p, $big = FALSE) {
    $c = $p['colors']; $dark = $p['dark'];
    $style = sprintf('--m-acc:%s;--m-soft:%s;--m-bg:%s;--m-card:%s;--m-text:%s;--m-line:%s', $c['accent'], $dark ? '#1E293B' : $c['secondary'], $c['background'], $c['surface'], $c['text'], $dark ? '#24324d' : '#e6eaf2');
    return '<div class="tm-mini' . ($big ? ' big' : '') . ($dark ? ' dark' : '') . '" style="' . $style . '" aria-hidden="true">'
        . '<div class="tm-side"><i class="logo"></i><i class="act"></i><i></i><i></i><i></i><i></i><b></b></div>'
        . '<div class="tm-body"><div class="tm-top"><i></i><span></span></div><div class="tm-hero"><i></i><i class="s"></i></div>'
        . '<div class="tm-stats"><span><i></i></span><span><i></i></span><span><i></i></span><span><i></i></span></div>'
        . '<div class="tm-charts"><span class="line"><svg viewBox="0 0 100 40" preserveAspectRatio="none"><path d="M0 34 L18 28 L36 22 L54 23 L72 15 L100 4 L100 40 L0 40Z" class="area"/><path d="M0 34 L18 28 L36 22 L54 23 L72 15 L100 4" class="ln"/></svg></span><span class="donut"><i></i></span></div></div></div>';
};
?>
<div class="ops tm">
<?= ops_hero('Pengaturan', 'Tema & Tampilan', 'Ubah tampilan aplikasi tanpa mengubah data atau logika bisnis. Setiap perubahan dapat dipratinjau, dibatalkan, dan dikembalikan ke bawaan.', array(), 'image') ?>
<?= ops_flash() ?>
<?php if ($this->session->flashdata('notice')): ?><?php endif; ?>

<section class="tm-status">
 <div><small>Tema aktif untuk semua pengguna</small><strong><span class="tm-dot" style="background:<?= ops_e($presets[$activeTheme]['colors']['accent']) ?>"></span><?= ops_e($presets[$activeTheme]['name']) ?></strong><em><?= $active['id'] ? 'Versi #' . (int) $active['id'] : 'Desain bawaan' ?></em></div>
 <?php if ($dirty): ?><span class="tm-badge draft">Draft belum diterapkan</span><?php endif; ?>
 <?php if ($previewing): ?><span class="tm-badge preview">Mode pratinjau aktif</span><?php endif; ?>
 <a class="tm-link" href="<?= $base ?>?tab=<?= ops_e($tab) ?>&amp;aman=1" title="Buka halaman ini dengan gaya bawaan bila warna membuat tampilan sulit dibaca">Pulihkan Tampilan (mode aman)</a>
 <button class="tm-btn outline" type="submit" form="tm-form" name="go" value="preview"<?= $tab === 'riwayat' ? ' disabled' : '' ?>><?= official_svg('eye') ?>Pratinjau Penuh</button>
</section>

<nav class="tm-tabs" aria-label="Bagian Tema & Tampilan"><?php foreach ($tabs as $k => $t): ?><a href="<?= $base ?>?tab=<?= $k ?>"<?= $tab === $k ? ' class="active" aria-current="page"' : '' ?>><?= official_svg($tabIcons[$k]) ?><?= ops_e($t) ?></a><?php endforeach; ?></nav>

<?php if ($errors && $tab !== 'riwayat'): ?><div class="tm-warn" role="alert"><?= official_svg('alert') ?><div><b>Periksa kontras sebelum menerapkan:</b><?php foreach ($errors as $e): ?><span><?= ops_e($e) ?></span><?php endforeach; ?></div></div><?php endif; ?>

<form id="tm-form" class="tm-card" method="post" action="<?= $base ?>" enctype="multipart/form-data"><?= $csrf ?><input type="hidden" name="tab" value="<?= ops_e($tab) ?>">
<?php if ($tab === 'tema'): ?>
 <header class="tm-card-head"><div><h2>Pilih Tema</h2><p>Pilih salah satu tema. Pratinjau hanya terlihat oleh Anda sampai <b>Simpan &amp; Terapkan</b>.</p></div><button class="tm-btn ghost small" type="submit" name="go" value="reset_section"><?= official_svg('swap') ?>Reset Bagian Ini</button></header>
 <div class="tm-themes">
 <?php foreach ($presets as $id => $p): ?>
  <label class="tm-theme<?= $cur === $id ? ' selected' : '' ?>">
   <input type="radio" name="themeId" value="<?= ops_e($id) ?>"<?= $cur === $id ? ' checked' : '' ?> data-tm-theme>
   <?= $mini($p) ?>
   <span class="tm-theme-foot"><span class="tm-radio" aria-hidden="true"></span><span class="tm-theme-text"><strong><?= ops_e($p['name']) ?><?php if (!empty($p['default'])): ?> <em class="tm-default">Default</em><?php endif; ?><?php if ($activeTheme === $id): ?> <em class="tm-on">Aktif</em><?php endif; ?></strong><small><?= ops_e($p['desc']) ?></small></span>
    <span class="tm-swatches"><?php foreach ($p['swatches'] as $sw): ?><i style="background:<?= ops_e($sw) ?>"></i><?php endforeach; ?></span></span>
  </label>
 <?php endforeach; ?>
 </div>
 <details class="tm-custom"<?= $work['colors'] != $presets[$cur]['colors'] ? ' open' : '' ?>><summary><?= official_svg('gear') ?>Kustomisasi warna (lanjutan)</summary>
  <p>Ubah warna tema terpilih. Kontras diperiksa otomatis (WCAG AA) sebelum diterapkan.</p>
  <label class="tm-check"><input type="checkbox" name="custom_colors" value="1"<?= $work['colors'] != $presets[$cur]['colors'] ? ' checked' : '' ?>> Gunakan warna kustom di bawah</label>
  <div class="tm-colors"><?php foreach ($labels as $k => $lab): ?><label><span><?= ops_e($lab) ?></span><span class="tm-color"><input type="color" value="<?= ops_e(strtolower($work['colors'][$k])) ?>" data-tm-sync="c-<?= $k ?>" aria-label="<?= ops_e($lab) ?>"><input type="text" id="c-<?= $k ?>" name="color_<?= $k ?>" value="<?= ops_e($work['colors'][$k]) ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}"></span></label><?php endforeach; ?></div>
 </details>

<?php elseif ($tab === 'dashboard'): ?>
 <header class="tm-card-head"><div><h2>Konten Dashboard</h2><p>Atur widget yang tampil dan urutannya. Widget yang disembunyikan hanya hilang dari tampilan; datanya tidak dihapus. Akses Cepat tidak ditampilkan pada dashboard default.</p></div><button class="tm-btn ghost small" type="submit" name="go" value="reset_section"><?= official_svg('swap') ?>Reset Bagian Ini</button></header>
 <ol class="tm-widgets" data-tm-sortable>
 <?php foreach ($work['dashboard']['order'] as $i => $w): ?>
  <li data-key="<?= ops_e($w) ?>"><span class="tm-grip" aria-hidden="true">⋮⋮</span>
   <label class="tm-switch"><input type="checkbox" name="show[]" value="<?= ops_e($w) ?>"<?= in_array($w, $work['dashboard']['hidden'], TRUE) ? '' : ' checked' ?>><i aria-hidden="true"></i><span><?= ops_e($widgets[$w]) ?></span></label>
   <span class="tm-move"><button type="button" data-tm-up aria-label="Naikkan <?= ops_e($widgets[$w]) ?>">▲</button><button type="button" data-tm-down aria-label="Turunkan <?= ops_e($widgets[$w]) ?>">▼</button></span>
   <input type="hidden" name="order[<?= ops_e($w) ?>]" value="<?= $i ?>" data-tm-order>
  </li>
 <?php endforeach; ?>
 </ol>
 <h3 class="tm-sub">Kartu Ringkasan</h3>
 <div class="tm-chips"><?php foreach ($stat_cards as $k => $lab): ?><label class="tm-check chip"><input type="checkbox" name="stats[]" value="<?= ops_e($k) ?>"<?= in_array($k, $work['dashboard']['stats_hidden'], TRUE) ? '' : ' checked' ?>> <?= ops_e($lab) ?></label><?php endforeach; ?></div>
 <p class="tm-note">Visibilitas kartu tetap mengikuti hak akses peran dan status langganan fitur; pengaturan ini tidak membuka data yang dibatasi.</p>

<?php elseif ($tab === 'formulir'): ?>
 <header class="tm-card-head"><div><h2>Formulir &amp; Field</h2><p>Gaya isian formulir di seluruh modul. Field inti proses bisnis tidak dapat disembunyikan atau dijadikan opsional dari sini.</p></div><button class="tm-btn ghost small" type="submit" name="go" value="reset_section"><?= official_svg('swap') ?>Reset Bagian Ini</button></header>
 <fieldset class="tm-options"><legend>Tinggi input</legend>
  <?php foreach (array('standard' => array('Standar', 'Nyaman untuk layar sentuh.'), 'compact' => array('Ringkas', 'Lebih banyak isian dalam satu layar.')) as $v => $o): ?><label class="tm-option<?= $work['components']['inputHeight'] === $v ? ' selected' : '' ?>"><input type="radio" name="inputHeight" value="<?= $v ?>"<?= $work['components']['inputHeight'] === $v ? ' checked' : '' ?>><b><?= $o[0] ?></b><small><?= $o[1] ?></small><span class="tm-demo-input<?= $v === 'compact' ? ' compact' : '' ?>">Contoh isian</span></label><?php endforeach; ?>
 </fieldset>
 <a class="tm-linkcard" href="<?= site_url('admin/field-tambahan') ?>"><?= official_svg('grid') ?><span><b>Field Tambahan Warga</b><small><?= (int) $field_count ?> field terdaftar · tambah field text, number, date, pilihan, atau checkbox dengan validasi & hak isi.</small></span><?= official_svg('chev-right') ?></a>
 <p class="tm-note">Penggantian label, placeholder, dan urutan per formulir belum tersedia di versi ini. Perubahan label tidak akan pernah mengubah nama kolom basis data, key API, validasi server, atau isi dokumen resmi.</p>

<?php elseif ($tab === 'tata-letak'): ?>
 <header class="tm-card-head"><div><h2>Tata Letak</h2><p>Sidebar, bentuk komponen, ukuran huruf, kepadatan tabel, dan elemen header.</p></div><button class="tm-btn ghost small" type="submit" name="go" value="reset_section"><?= official_svg('swap') ?>Reset Bagian Ini</button></header>
 <fieldset class="tm-options"><legend>Sidebar</legend>
  <?php foreach (array('standard' => array('Standar', 'Lebar normal dengan grup menu.'), 'compact' => array('Ringkas', 'Sidebar lebih sempit.'), 'icons' => array('Ikon saja', 'Awalnya diperkecil; bisa dibuka dengan tombol menu.')) as $v => $o): ?><label class="tm-option<?= $work['sidebar']['variant'] === $v ? ' selected' : '' ?>"><input type="radio" name="sidebar" value="<?= $v ?>"<?= $work['sidebar']['variant'] === $v ? ' checked' : '' ?>><span class="tm-sb tm-sb-<?= $v ?>" aria-hidden="true"><i></i><i></i><i></i></span><b><?= $o[0] ?></b><small><?= $o[1] ?></small></label><?php endforeach; ?>
 </fieldset>
 <div class="tm-grid3">
  <fieldset class="tm-seg"><legend>Sudut kartu</legend><?php foreach (array('square' => 'Kotak', 'medium' => 'Sedang', 'round' => 'Membulat') as $v => $t): ?><label><input type="radio" name="radius" value="<?= $v ?>"<?= $work['components']['radius'] === $v ? ' checked' : '' ?>><span><?= $t ?></span></label><?php endforeach; ?></fieldset>
  <fieldset class="tm-seg"><legend>Ukuran huruf</legend><?php foreach (array('small' => 'Kecil', 'medium' => 'Sedang', 'large' => 'Besar') as $v => $t): ?><label><input type="radio" name="fontSize" value="<?= $v ?>"<?= $work['components']['fontSize'] === $v ? ' checked' : '' ?>><span><?= $t ?></span></label><?php endforeach; ?></fieldset>
  <fieldset class="tm-seg"><legend>Kepadatan tabel</legend><?php foreach (array('comfortable' => 'Nyaman', 'compact' => 'Ringkas') as $v => $t): ?><label><input type="radio" name="density" value="<?= $v ?>"<?= $work['components']['density'] === $v ? ' checked' : '' ?>><span><?= $t ?></span></label><?php endforeach; ?></fieldset>
 </div>
 <fieldset class="tm-toggles"><legend>Header</legend>
  <?php foreach (array('search' => 'Kolom pencarian', 'notifications' => 'Ikon notifikasi', 'banner' => 'Ilustrasi banner halaman') as $k => $t): ?><label class="tm-switch"><input type="checkbox" name="header_<?= $k ?>" value="1"<?= $work['header'][$k] ? ' checked' : '' ?>><i aria-hidden="true"></i><span><?= $t ?></span></label><?php endforeach; ?>
 </fieldset>

<?php elseif ($tab === 'logo'): ?>
 <header class="tm-card-head"><div><h2>Ikon &amp; Logo</h2><p>Logo tampil di sidebar dan halaman publik. Gunakan PNG, WebP, atau JPG (maks. 1 MB, 32–2000 px). SVG tidak diterima demi keamanan.</p></div><button class="tm-btn ghost small" type="submit" name="go" value="reset_section"><?= official_svg('swap') ?>Reset Bagian Ini</button></header>
 <?php $logo = $work['brand']['logo'] ?: ($this->Content_model->settings()['logo'] ?? 'assets/mockup/logo.png'); ?>
 <div class="tm-logo">
  <span class="tm-logo-preview"><img src="<?= base_url(ops_e($logo)) ?>" alt="Logo saat ini" data-tm-logo-img></span>
  <div><label class="tm-file"><?= official_svg('upload') ?><span>Pilih logo baru</span><input type="file" name="logo" accept="image/png,image/webp,image/jpeg" data-tm-logo></label>
   <label class="tm-check"><input type="checkbox" name="logo_default" value="1"> Gunakan logo yang berlaku saat ini (abaikan logo draft)</label>
   <p class="tm-note">Logo baru disimpan pada draft; berlaku untuk semua pengguna setelah Simpan &amp; Terapkan. Ikon menu memakai set ikon vektor bawaan agar tetap konsisten dan tajam.</p></div>
 </div>

<?php else: ?>
 <header class="tm-card-head"><div><h2>Riwayat Perubahan</h2><p>Setiap penerapan, reset, dan rollback tercatat sebagai versi. Pilih versi untuk mengembalikan tampilan sebelumnya (tercatat sebagai versi baru).</p></div></header>
 <div class="tm-table-wrap"><table class="tm-table"><thead><tr><th>Versi</th><th>Waktu</th><th>Oleh</th><th>Aksi</th><th>Tema</th><th></th></tr></thead><tbody>
 <?php foreach ($versions as $v): $cfg = json_decode($v['config_json'], TRUE) ?: array(); $tid = $cfg['themeId'] ?? 'original-orange'; ?>
  <tr><td>#<?= (int) $v['id'] ?><?= (int) $v['id'] === $active['id'] ? ' <em class="tm-on">Aktif</em>' : '' ?></td><td><?= ops_e(ops_date($v['created_at'], TRUE)) ?></td><td><?= ops_e($v['created_by_name'] ?: '-') ?></td>
   <td><span class="tm-act <?= ops_e($v['action']) ?>"><?= ops_e(array('apply' => 'Diterapkan', 'reset' => 'Reset ke Default', 'rollback' => 'Rollback')[$v['action']] ?? $v['action']) ?></span><small><?= ops_e($v['note']) ?></small></td>
   <td><span class="tm-dot" style="background:<?= ops_e($presets[$tid]['colors']['accent'] ?? '#F97316') ?>"></span><?= ops_e($presets[$tid]['name'] ?? $tid) ?></td>
   <td><?php if ((int) $v['id'] !== $active['id']): ?><button class="tm-btn small outline" type="submit" form="tm-rb-<?= (int) $v['id'] ?>" data-tm-confirm="Kembalikan tampilan ke versi #<?= (int) $v['id'] ?>?">Kembalikan</button><?php endif; ?></td></tr>
 <?php endforeach; ?>
 <?php if (!$versions): ?><tr><td colspan="6" class="tm-empty">Belum ada perubahan. Aplikasi memakai desain bawaan Oranye Harmoni.</td></tr><?php endif; ?>
 </tbody></table></div>
<?php endif; ?>
</form>
<?php foreach ($versions as $v): ?><form id="tm-rb-<?= (int) $v['id'] ?>" method="post" action="<?= $base ?>" hidden><?= $csrf ?><input type="hidden" name="tab" value="riwayat"><input type="hidden" name="go" value="rollback"><input type="hidden" name="version_id" value="<?= (int) $v['id'] ?>"></form><?php endforeach; ?>

<div class="tm-actionbar" role="toolbar" aria-label="Aksi tema">
 <button class="tm-btn ghost danger" type="button" data-tm-open="tm-reset"><?= official_svg('swap') ?>Reset ke Default</button>
 <span class="tm-spacer"></span>
 <?php if ($tab !== 'riwayat'): ?>
 <button class="tm-btn ghost" type="submit" form="tm-form" name="go" value="cancel"<?= $has_draft ? '' : ' disabled' ?>>Batal</button>
 <button class="tm-btn outline" type="submit" form="tm-form" name="go" value="draft"><?= official_svg('save') ?>Simpan sebagai Draft</button>
 <button class="tm-btn" type="submit" form="tm-form" name="go" value="apply" data-tm-confirm="Terapkan tampilan ini untuk SEMUA pengguna?"><?= official_svg('check') ?>Simpan &amp; Terapkan</button>
 <?php endif; ?>
</div>

<dialog class="tm-dialog" id="tm-reset" aria-labelledby="tm-reset-t">
 <form method="post" action="<?= $base ?>"><?= $csrf ?><input type="hidden" name="tab" value="<?= ops_e($tab) ?>"><input type="hidden" name="go" value="reset_default">
  <header><h2 id="tm-reset-t">Reset ke Default?</h2><button type="button" class="tm-x" data-tm-close aria-label="Tutup">&times;</button></header>
  <p>Semua pilihan tampilan (warna, sidebar, ukuran huruf, tata letak, widget dashboard, logo, dan gaya formulir) dikembalikan ke desain asli <b>Oranye Harmoni</b> untuk semua pengguna.</p>
  <?= $mini($presets['original-orange'], TRUE) ?>
  <p class="tm-note">Data warga, arsip, transaksi, dan field tambahan beserta isinya <b>tidak dihapus</b>. Tindakan ini tercatat sebagai versi baru dan dapat di-rollback.</p>
  <label class="tm-check"><input type="checkbox" name="confirm" value="RESET" required> Saya mengerti dan ingin mereset tampilan.</label>
  <footer><button type="button" class="tm-btn ghost" data-tm-close>Batal</button><button class="tm-btn" type="submit"><?= official_svg('swap') ?>Reset Sekarang</button></footer>
 </form>
</dialog>
</div>
<link rel="stylesheet" href="<?= base_url('assets/tampilan.css') ?>?v=<?= filemtime(FCPATH . 'assets/tampilan.css') ?>">
<script>
(()=>{const q=(s,r=document)=>r.querySelector(s),qa=(s,r=document)=>[...r.querySelectorAll(s)];
qa('[data-tm-theme]').forEach(r=>r.addEventListener('change',()=>{qa('.tm-theme').forEach(l=>l.classList.toggle('selected',l.contains(r)&&r.checked));}));
qa('.tm-option input,.tm-seg input').forEach(r=>r.addEventListener('change',()=>{qa(`input[name="${r.name}"]`).forEach(o=>o.closest('label').classList.toggle('selected',o.checked));}));
qa('[data-tm-sync]').forEach(c=>{const t=document.getElementById(c.dataset.tmSync);c.addEventListener('input',()=>{t.value=c.value.toUpperCase();const cb=q('[name=custom_colors]');if(cb)cb.checked=true;});t.addEventListener('input',()=>{if(/^#[0-9a-f]{6}$/i.test(t.value))c.value=t.value.toLowerCase();});});
const list=q('[data-tm-sortable]');if(list){const renum=()=>qa('li',list).forEach((li,i)=>q('[data-tm-order]',li).value=i);
 list.addEventListener('click',e=>{const li=e.target.closest('li');if(!li)return;if(e.target.closest('[data-tm-up]')&&li.previousElementSibling)list.insertBefore(li,li.previousElementSibling);if(e.target.closest('[data-tm-down]')&&li.nextElementSibling)list.insertBefore(li.nextElementSibling,li);renum();});}
const logo=q('[data-tm-logo]');if(logo)logo.addEventListener('change',()=>{const f=logo.files[0];if(f&&/^image\//.test(f.type))q('[data-tm-logo-img]').src=URL.createObjectURL(f);});
qa('[data-tm-open]').forEach(b=>b.addEventListener('click',()=>document.getElementById(b.dataset.tmOpen).showModal()));
qa('.tm-dialog').forEach(d=>{qa('[data-tm-close]',d).forEach(x=>x.addEventListener('click',()=>d.close()));d.addEventListener('click',e=>{if(e.target===d)d.close();});});
qa('[data-tm-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.tmConfirm))e.preventDefault();}));})();
</script>
