<?php
/* REVISI: Manajemen Fitur & Menu disederhanakan sesuai mockup — tab kategori, filter status, tabel penuh, ringkasan di bawah. */
$this->load->helper(array('official_icons', 'page_ui'));
$css = 'assets/admin-fitur.css';
$cat_tone = array('Kependudukan' => 'blue', 'Layanan Warga' => 'purple', 'Keuangan' => 'orange', 'Informasi' => 'green', 'Operasional' => 'teal');
$cat_icon = array('Kependudukan' => 'people', 'Layanan Warga' => 'layers', 'Keuangan' => 'database', 'Informasi' => 'megaphone', 'Operasional' => 'shield');
$return = '?' . http_build_query(array_filter(array('tab' => $tab === 'daftar' ? '' : $tab, 'q' => $q, 'kategori' => $category, 'status' => $status), 'strlen'));
$return = $return === '?' ? '' : $return;
$csrf = ops_csrf();
$url = function ($over) use ($q, $category, $status) { $p = array_filter(array_merge(array('q' => $q, 'kategori' => $category, 'status' => $status), $over), 'strlen'); return site_url('admin/fitur') . ($p ? '?' . http_build_query($p) : ''); };
$inactive = $total - $active;
$sub = array('akses' => 'Hak Akses per Fitur', 'dashboard' => 'Pengaturan Dashboard Warga', 'riwayat' => 'Riwayat Perubahan');
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<div class="pg pg-fitur ftx">
 <nav class="ftx-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>" aria-label="Dashboard"><?= official_svg('home') ?></a><span><?= official_svg('chev-right') ?></span><span class="tx">Pengaturan</span><span><?= official_svg('chev-right') ?></span><?php if ($tab === 'daftar'): ?><strong>Manajemen Fitur &amp; Menu</strong><?php else: ?><a class="tx" href="<?= site_url('admin/fitur') ?>">Manajemen Fitur &amp; Menu</a><span><?= official_svg('chev-right') ?></span><strong><?= $sub[$tab] ?></strong><?php endif; ?></nav>
 <section class="ftx-hero">
  <span class="ftx-hero-ic" aria-hidden="true"><?= official_svg('gear') ?></span>
  <div class="ftx-hero-tx"><span>Pengaturan</span><h1>Manajemen Fitur &amp; Menu</h1><p>Atur menu dan fitur yang ditampilkan di portal warga dan pengurus RT.</p></div>
  <div class="ftx-art" aria-hidden="true"><b class="win"><i></i><i></i><i></i><em></em><em></em><em></em></b><s class="leaf l1"></s><s class="leaf l2"></s></div>
 </section>
 <?= ops_flash() ?>

 <?php if ($tab === 'daftar'): ?>
 <nav class="ftx-cats" aria-label="Kategori fitur">
  <a href="<?= $url(array('kategori' => '')) ?>"<?= $category === '' ? ' aria-current="page"' : '' ?>><?= official_svg('grid') ?>Semua Fitur</a>
  <?php foreach ($categories as $cat): ?><a href="<?= $url(array('kategori' => $cat)) ?>"<?= $category === $cat ? ' aria-current="page"' : '' ?>><?= official_svg($cat_icon[$cat] ?? 'grid') ?><?= ops_e($cat) ?></a><?php endforeach; ?>
 </nav>

 <section class="ftx-card">
  <form class="ftx-filter" method="get" action="<?= site_url('admin/fitur') ?>" role="search">
   <label class="ftx-search"><?= official_svg('search') ?><span class="sr-only">Cari fitur</span><input type="search" name="q" value="<?= ops_e($q) ?>" maxlength="60" placeholder="Cari fitur atau menu..."></label>
   <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= ops_e($status) ?>"><?php endif; ?>
   <label class="ftx-sel"><span class="sr-only">Kategori</span><select name="kategori" onchange="this.form.submit()"><option value="">Semua Kategori</option><?php foreach ($categories as $cat): ?><option value="<?= ops_e($cat) ?>"<?= $category === $cat ? ' selected' : '' ?>><?= ops_e($cat) ?></option><?php endforeach; ?></select></label>
   <a class="ftx-add" href="<?= site_url('admin/langganan?tab=fitur') ?>"><b aria-hidden="true">+</b> Tambah Fitur</a>
   <noscript><button class="ftx-btn" type="submit">Terapkan</button></noscript>
  </form>
  <div class="ftx-table-wrap"><table class="ftx-table"><thead><tr><th>Fitur / Menu</th><th>Kategori</th><th>Akses</th><th class="c">Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($filtered as $code => $r):
   $parent_off = !empty($r['parent']) && $r['enabled'] && !$r['effective'];
   $child_names = array_map(function ($c) use ($rows) {return $rows[$c]['name'];}, $r['children']); ?>
   <tr id="fitur-<?= ops_e($code) ?>"<?= $r['effective'] ? '' : ' class="is-off"' ?>>
    <td class="lead"><span class="ftx-feat"><span class="ftx-tile <?= ops_e($r['tone'] ?? 'orange') ?>" aria-hidden="true"><?= official_svg($r['icon'] ?? 'grid') ?></span><span><strong><?= ops_e($r['name']) ?></strong><small><?= ops_e($r['desc']) ?><?= $r['parent_name'] ? ' · bagian dari ' . ops_e($r['parent_name']) : '' ?></small></span></span></td>
    <td data-label="Kategori"><span class="ftx-cat <?= $cat_tone[$r['category']] ?? 'muted' ?>"><?= ops_e($r['category']) ?></span></td>
    <?php // Ringkasan akses pada tombol dropdown: satu nama, "2 Akses", atau "Tidak ada akses" (logika izin tetap di server).
     $acc_on = array();
     if ($r['has_resident'] && $r['visible_to_residents']) $acc_on[] = array('people', 'Warga');
     if ($r['visible_to_staff']) $acc_on[] = array('shield', 'Pengurus');
     $acc_label = count($acc_on) > 1 ? count($acc_on) . ' Akses' : ($acc_on ? $acc_on[0][1] : 'Tidak ada akses');
     $acc_icon = count($acc_on) > 1 ? 'people' : ($acc_on ? $acc_on[0][0] : 'close'); ?>
    <td data-label="Akses"><details class="ftx-access<?= count($acc_on) > 1 ? ' is-multi' : '' ?>"><summary aria-label="Akses <?= ops_e($r['name']) ?>: <?= ops_e(implode(' dan ', array_column($acc_on, 1)) ?: 'tidak ada') ?>. Klik untuk mengubah." title="<?= ops_e(implode(' + ', array_column($acc_on, 1)) ?: 'Tidak ada akses') ?>"><?= official_svg($acc_icon) ?><span><?= ops_e($acc_label) ?></span><i aria-hidden="true"></i></summary><div role="group" aria-label="Pilih akses <?= ops_e($r['name']) ?>">
     <?php foreach (array('visible_to_residents' => array('people','Warga'), 'visible_to_staff' => array('shield','Pengurus')) as $fk => $access): if ($fk === 'visible_to_residents' && !$r['has_resident']) continue; ?>
      <form method="post" action="<?= site_url('admin/fitur/update') ?>" class="ftx-access-option"><?= $csrf ?><input type="hidden" name="code" value="<?= ops_e($code) ?>"><input type="hidden" name="field" value="<?= $fk ?>"><input type="hidden" name="value" value="<?= $r[$fk] ? 0 : 1 ?>"><input type="hidden" name="version" value="<?= ops_e($r['version']) ?>"><input type="hidden" name="return" value="<?= ops_e($return) ?>"><button type="submit" role="menuitemcheckbox" aria-checked="<?= $r[$fk] ? 'true' : 'false' ?>"><b class="<?= $r[$fk] ? 'on' : '' ?>" aria-hidden="true"></b><?= official_svg($access[0]) ?><span><?= $access[1] ?></span></button></form>
     <?php endforeach; ?>
     <?php if (!$r['has_resident']): ?><p class="ftx-access-note">Fitur ini hanya memiliki menu pengurus.</p><?php endif; ?>
    </div></details></td>
    <td data-label="Status" class="c">
     <form method="post" action="<?= site_url('admin/fitur/update') ?>" class="ft-toggle" data-name="<?= ops_e($r['name']) ?>" data-turn="<?= $r['enabled'] ? 'off' : 'on' ?>" data-open="<?= (int) ($r['open'] ?? 0) ?>" data-children="<?= ops_e(implode(', ', $child_names)) ?>" data-parent-off="<?= !empty($r['parent']) && !$rows[$r['parent']]['effective'] ? ops_e($r['parent_name']) : '' ?>">
      <?= $csrf ?><input type="hidden" name="code" value="<?= ops_e($code) ?>"><input type="hidden" name="field" value="enabled"><input type="hidden" name="value" value="<?= $r['enabled'] ? 0 : 1 ?>">
      <input type="hidden" name="version" value="<?= ops_e($r['version']) ?>"><input type="hidden" name="return" value="<?= ops_e($return) ?>"><input type="hidden" name="reason" value="">
      <button type="submit" class="ftx-switch<?= $r['enabled'] ? ' on' : '' ?><?= $parent_off ? ' dim' : '' ?>" role="switch" aria-checked="<?= $r['enabled'] ? 'true' : 'false' ?>" aria-label="<?= $r['enabled'] ? 'Nonaktifkan' : 'Aktifkan' ?> <?= ops_e($r['name']) ?>" title="<?= $r['effective'] ? 'Aktif' : ($parent_off ? 'Induk nonaktif' : 'Nonaktif') ?>"><span></span></button>
      <?php if ($parent_off): ?><small class="ftx-state">Induk nonaktif</small><?php endif; ?>
     </form>
    </td>
    <td class="c">
     <details class="pg-menu ft-edit ftx-edit"><summary aria-label="Atur <?= ops_e($r['name']) ?>" title="Atur akses warga / pengurus"><?= official_svg('pencil') ?></summary><div>
      <p class="ft-edit-title"><?= ops_e($r['name']) ?></p>
      <?php foreach (array('visible_to_residents' => 'Tampil di portal warga', 'visible_to_staff' => 'Tampil untuk pengurus') as $fk => $fl): if ($fk === 'visible_to_residents' && !$r['has_resident']) continue; ?>
      <form method="post" action="<?= site_url('admin/fitur/update') ?>" class="ft-vis"><?= $csrf ?><input type="hidden" name="code" value="<?= ops_e($code) ?>"><input type="hidden" name="field" value="<?= $fk ?>"><input type="hidden" name="value" value="<?= $r[$fk] ? 0 : 1 ?>"><input type="hidden" name="version" value="<?= ops_e($r['version']) ?>"><input type="hidden" name="return" value="<?= ops_e($return) ?>">
       <span><?= $fl ?></span><button type="submit" class="ftx-switch sm<?= $r[$fk] ? ' on' : '' ?>" role="switch" aria-checked="<?= $r[$fk] ? 'true' : 'false' ?>" aria-label="<?= $fl ?>: <?= $r[$fk] ? 'matikan' : 'nyalakan' ?>"><span></span></button></form>
      <?php endforeach; ?>
      <?php if (!$r['has_resident']): ?><p class="ft-note">Hanya untuk pengurus (tidak ada menu di portal warga).</p><?php endif; ?>
      <?php if ($r['parent_name']): ?><p class="ft-note">Induk: <b><?= ops_e($r['parent_name']) ?></b>. Bila induk nonaktif, fitur ini ikut tidak aktif.</p><?php endif; ?>
      <?php if ($child_names): ?><p class="ft-note">Turunan: <?= ops_e(implode(', ', $child_names)) ?>.</p><?php endif; ?>
      <?php if (!empty($r['open'])): ?><p class="ft-note warn"><?= (int) $r['open'] ?> transaksi belum selesai.</p><?php endif; ?>
      <?php if ($r['updated_at']): ?><p class="ft-note">Diubah <?= ops_date($r['updated_at'], TRUE) ?></p><?php endif; ?>
     </div></details>
    </td>
   </tr>
  <?php endforeach; if (!$filtered): ?><tr><td colspan="5" class="ftx-empty">Tidak ada fitur yang cocok.<?php if ($q !== '' || $status !== '' || $category !== ''): ?> <a href="<?= site_url('admin/fitur') ?>">Atur ulang filter</a><?php endif; ?></td></tr><?php endif; ?>
  </tbody></table></div>
 </section>

 <section class="ftx-sum" aria-label="Ringkasan fitur">
  <div class="ftx-num blue"><span><?= official_svg('grid') ?></span><div><strong><?= (int) $total ?></strong><small>Total Fitur</small></div></div>
  <div class="ftx-num green"><span><?= official_svg('check') ?></span><div><strong><?= (int) $active ?></strong><small>Aktif</small></div></div>
  <div class="ftx-num red"><span><?= official_svg('close') ?></span><div><strong><?= (int) $inactive ?></strong><small>Nonaktif</small></div></div>
  <div class="ftx-links">
   <a href="<?= site_url('admin/fitur?tab=akses') ?>"><?= official_svg('chart') ?>Lihat Laporan Akses</a>
   <a href="<?= site_url('admin/fitur?tab=dashboard') ?>"><?= official_svg('grid') ?>Dashboard Warga</a>
   <a href="<?= site_url('admin/fitur?tab=riwayat') ?>"><?= official_svg('clock') ?>Riwayat</a>
  </div>
 </section>
 <?php else: ?>
 <a class="ftx-back" href="<?= site_url('admin/fitur') ?>"><?= official_svg('back') ?>Kembali ke daftar fitur</a>
 <section class="ftx-card pg-panel">
  <?php if ($tab === 'dashboard'): ?>
<?php $this->load->view('admin/ops/dashboard_settings',compact('dashboard_settings','dashboard_items','dashboard_version','dashboard_icons','dashboard_colors','dashboard_categories','dashboard_images')); ?>
<?php elseif ($tab === 'akses'):
   $perm_of = array('family_card' => array('admin', 'operator', 'ketua'), 'resident_profile' => 'penduduk.read', 'resident_mutation' => 'mutasi.read', 'social_aid' => 'bansos.read',
    'letters' => array('admin', 'operator', 'ketua'), 'complaints' => 'pengaduan.read', 'live_chat' => array('admin', 'operator', 'ketua'), 'dues' => array('admin', 'bendahara', 'ketua'),
    'dues_arrears' => 'iuran.report', 'cash_report' => 'kas.report', 'cash_reconciliation' => 'kas.report', 'announcements' => array('admin', 'operator', 'ketua'), 'events' => array('admin', 'operator', 'ketua'),
    'gallery' => array('admin', 'operator', 'ketua'), 'contacts' => 'kontak.read', 'patrol' => 'ronda.read', 'work_program' => 'program.read', 'activity_notes' => 'notulen.read',
    'inventory' => 'aset.read', 'purchasing' => 'belanja.read', 'documents' => 'arsip.read'); ?>
   <div class="pg-form-head"><span class="pg-form-ic plain"><?= official_svg('person') ?></span><div><h2>Hak Akses per Fitur</h2><p>Akses diberikan hanya bila fitur <b>aktif</b> dan peran <b>berizin</b> (BR-04). Peran pengurus diatur di <a href="<?= site_url('admin/akun') ?>">Akun &amp; Hak Akses</a>.</p></div></div>
   <table class="pg-table"><thead><tr><th>Fitur</th><th>Status</th><th>Warga</th><th>Peran pengurus yang berizin</th></tr></thead><tbody>
   <?php foreach ($rows as $code => $r): $p = $perm_of[$code] ?? array('admin'); $allowed = is_array($p) ? $p : ($perm[$p] ?? array('admin')); ?>
    <tr><td class="lead"><span class="pg-person"><span class="pg-tile <?= ops_e($r['tone'] ?? 'orange') ?>" aria-hidden="true"><?= official_svg($r['icon'] ?? 'grid') ?></span><span><strong><?= ops_e($r['name']) ?></strong><small class="pg-plain"><?= ops_e($r['category']) ?></small></span></span></td>
     <td data-label="Status"><span class="pg-pill <?= $r['effective'] ? 'ok' : 'muted' ?>"><?= $r['effective'] ? 'Aktif' : 'Nonaktif' ?></span></td>
     <td data-label="Warga"><?= $r['has_resident'] ? ($r['residents'] ? '<span class="pg-chip set">Dapat mengakses</span>' : '<span class="pg-chip">Disembunyikan</span>') : '<span class="pg-meta">—</span>' ?></td>
     <td data-label="Peran"><span class="pg-chips"><?php foreach ($allowed as $role): ?><span class="pg-chip-ic"><?= official_svg('shield') ?><?= ops_e($roles[$role] ?? ucfirst($role)) ?></span><?php endforeach; ?></span></td></tr>
   <?php endforeach; ?>
   </tbody></table>

  <?php else: ?>
   <div class="pg-form-head"><span class="pg-form-ic plain"><?= official_svg('clock') ?></span><div><h2>Riwayat Perubahan</h2><p>100 perubahan terakhir: siapa, kapan, nilai sebelum/sesudah, dan alasan (BR-08).</p></div></div>
   <table class="pg-table"><thead><tr><th>Waktu</th><th>Fitur</th><th>Perubahan</th><th>Oleh</th><th>Alasan</th></tr></thead><tbody>
   <?php $labels = array('enabled' => 'Status', 'visible_to_residents' => 'Portal warga', 'visible_to_staff' => 'Pengurus');
   foreach ($audit as $a): $old = json_decode((string) $a['old_value'], TRUE) ?: array(); $new = json_decode((string) $a['new_value'], TRUE) ?: array(); $diff = array();
    foreach ($new as $k => $v) if (($old[$k] ?? NULL) !== $v) $diff[] = ($labels[$k] ?? $k) . ': ' . (isset($old[$k]) ? (in_array($k,array('enabled','visible_to_residents','visible_to_staff','show_on_dashboard','show_in_header','is_default_favorite'),TRUE)?($old[$k]?'aktif':'nonaktif'):(string)$old[$k]) . ' → ' : '') . (in_array($k,array('enabled','visible_to_residents','visible_to_staff','show_on_dashboard','show_in_header','is_default_favorite'),TRUE)?($v?'aktif':'nonaktif'):(string)$v); ?>
    <tr><td class="lead"><span class="pg-date"><?= ops_date($a['created_at'], TRUE) ?></span></td><td data-label="Fitur"><strong><?= ops_e($a['name'] ?: ($a['code'] ?: 'Dashboard Warga')) ?></strong></td><td data-label="Perubahan"><?= ops_e(implode('; ', $diff) ?: '—') ?></td><td data-label="Oleh"><?= ops_e($a['username'] ?: '#' . (int) $a['actor_user_id']) ?></td><td data-label="Alasan"><?= ops_e($a['reason'] ?: '—') ?></td></tr>
   <?php endforeach; if (!$audit): ?><tr><td colspan="5" class="pg-empty">Belum ada perubahan pengaturan fitur.</td></tr><?php endif; ?>
   </tbody></table>
  <?php endif; ?>
 </section>
 <?php endif; ?>
</div>
<script>
(function(){
 // Dropdown Akses/Atur: hanya satu terbuka; tutup dengan klik di luar atau Escape (sentuh & mouse).
 var drops=document.querySelectorAll('details.ftx-access,details.ftx-edit');
 drops.forEach(function(d){d.addEventListener('toggle',function(){if(d.open)drops.forEach(function(o){if(o!==d)o.open=false})})});
 document.addEventListener('click',function(e){drops.forEach(function(d){if(d.open&&!d.contains(e.target))d.open=false})});
 document.addEventListener('keydown',function(e){if(e.key==='Escape')drops.forEach(function(d){if(d.open){d.open=false;d.querySelector('summary').focus()}})});
 document.querySelectorAll('form.ft-toggle').forEach(function(f){
  f.addEventListener('submit',function(e){
   var d=f.dataset,msg,reason='';
   if(d.turn==='off'){
    msg='Nonaktifkan "'+d.name+'"?\n\n• Menu disembunyikan dari portal warga dan panel pengurus.\n• Akses lewat URL langsung ditolak.\n• Data tidak dihapus dan kembali tersedia saat diaktifkan.';
    if(d.children)msg+='\n• Fitur turunan ikut tidak aktif: '+d.children+'.';
    if(+d.open>0)msg+='\n\nPerhatian: ada '+d.open+' transaksi belum selesai. Administrator masih dapat membukanya untuk diselesaikan.';
   }else{
    msg='Aktifkan "'+d.name+'"? Menu akan tampil kembali sesuai hak akses.';
    if(d.parentOff)msg+='\n\nCatatan: belum efektif karena fitur induk "'+d.parentOff+'" masih nonaktif.';
   }
   if(!confirm(msg)){e.preventDefault();return}
   reason=prompt(+d.open>0&&d.turn==='off'?'Alasan perubahan (wajib, min. 5 karakter):':'Alasan perubahan (opsional):','');
   if(reason===null){e.preventDefault();return}
   if(+d.open>0&&d.turn==='off'&&reason.trim().length<5){alert('Alasan minimal 5 karakter karena masih ada transaksi berjalan.');e.preventDefault();return}
   f.querySelector('input[name="reason"]').value=reason.trim().slice(0,500);
  });
 });
})();
</script>
