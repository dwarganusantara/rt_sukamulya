<?php
$this->load->helper(array('official_icons', 'page_ui'));
$tabs = array('daftar' => array('Daftar Fitur', 'grid'), 'akses' => array('Hak Akses', 'person'), 'dashboard' => array('Dashboard Warga', 'grid'), 'riwayat' => array('Riwayat Perubahan', 'clock'));
$cat_tone = array('Kependudukan' => 'blue', 'Layanan Warga' => 'info', 'Keuangan' => 'orange', 'Informasi' => 'orange', 'Operasional' => 'muted');
$return = '?' . http_build_query(array_filter(array('tab' => $tab === 'daftar' ? '' : $tab, 'q' => $q, 'kategori' => $category), 'strlen'));
$return = $return === '?' ? '' : $return;
$csrf = ops_csrf();
?>
<div class="pg pg-fitur">
 <?= pg_crumb(array(array('Pengaturan', NULL), array('Manajemen Fitur & Menu', NULL))) ?>
 <section class="pg-hero pg-hero-care pg-hero-switch"><span class="pg-hero-ic"><?= official_svg('gear') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Pengaturan</span><h1>Manajemen Fitur &amp; Menu</h1><p>Atur menu dan fitur yang digunakan di portal warga dan panel pengurus. Fitur nonaktif disembunyikan dan aksesnya ditolak server; datanya tidak dihapus.</p></div><span class="pg-switch-art" aria-hidden="true"></span></section>
 <?= ops_flash() ?>

 <nav class="pg-tabs pg-tabs-solid" aria-label="Bagian manajemen fitur"><?php foreach ($tabs as $k => $t): ?><a class="pg-tab" href="<?= site_url('admin/fitur' . ($k === 'daftar' ? '' : '?tab=' . $k)) ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= official_svg($t[1]) ?><?= $t[0] ?></a><?php endforeach; ?></nav>

 <div class="pg-fitur-grid">
  <section class="pg-card pg-panel">
  <?php if ($tab === 'daftar'): ?>
   <form class="pg-filter-row pg-filter-flat" method="get" action="<?= site_url('admin/fitur') ?>" role="search">
    <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari fitur</span><input type="search" name="q" value="<?= ops_e($q) ?>" maxlength="60" placeholder="Cari fitur atau menu..."></label>
    <label class="pg-sel"><?= official_svg('list') ?><span class="sr-only">Kategori</span><select name="kategori" onchange="this.form.submit()"><option value="">Semua Kategori</option><?php foreach ($categories as $cat): ?><option value="<?= ops_e($cat) ?>"<?= $cat === $category ? ' selected' : '' ?>><?= ops_e($cat) ?></option><?php endforeach; ?></select></label>
    <noscript><button class="pg-btn outline" type="submit">Terapkan</button></noscript>
   </form>
   <table class="pg-table pg-ft-table"><thead><tr><th>Fitur / Menu</th><th>Kategori</th><th>Akses</th><th>Status</th><th class="c">Aksi</th></tr></thead><tbody>
   <?php foreach ($filtered as $code => $r):
    $parent_off = !empty($r['parent']) && $r['enabled'] && !$r['effective'];
    $child_names = array_map(function ($c) use ($rows) {return $rows[$c]['name'];}, $r['children']); ?>
    <tr id="fitur-<?= ops_e($code) ?>">
     <td class="lead"><span class="pg-person"><span class="pg-tile <?= ops_e($r['tone'] ?? 'orange') ?>" aria-hidden="true"><?= official_svg($r['icon'] ?? 'grid') ?></span><span><strong><?= ops_e($r['name']) ?></strong><small class="pg-plain"><?= ops_e($r['desc']) ?><?= $r['parent_name'] ? ' · bagian dari ' . ops_e($r['parent_name']) : '' ?></small></span></span></td>
     <td data-label="Kategori"><span class="pg-cat <?= $cat_tone[$r['category']] ?? 'muted' ?>"><?= ops_e($r['category']) ?></span></td>
     <td data-label="Akses"><span class="pg-chips"><?php if ($r['has_resident'] && $r['visible_to_residents']): ?><span class="pg-chip-ic"><?= official_svg('people') ?>Warga</span><?php endif; ?><?php if ($r['visible_to_staff']): ?><span class="pg-chip-ic"><?= official_svg('shield') ?>Pengurus</span><?php endif; ?><?php if (!$r['visible_to_staff'] && !($r['has_resident'] && $r['visible_to_residents'])): ?><span class="pg-chip">Tersembunyi</span><?php endif; ?></span></td>
     <td data-label="Status">
      <form method="post" action="<?= site_url('admin/fitur/update') ?>" class="ft-toggle" data-name="<?= ops_e($r['name']) ?>" data-turn="<?= $r['enabled'] ? 'off' : 'on' ?>" data-open="<?= (int) ($r['open'] ?? 0) ?>" data-children="<?= ops_e(implode(', ', $child_names)) ?>" data-parent-off="<?= !empty($r['parent']) && !$rows[$r['parent']]['effective'] ? ops_e($r['parent_name']) : '' ?>">
       <?= $csrf ?><input type="hidden" name="code" value="<?= ops_e($code) ?>"><input type="hidden" name="field" value="enabled"><input type="hidden" name="value" value="<?= $r['enabled'] ? 0 : 1 ?>">
       <input type="hidden" name="version" value="<?= ops_e($r['version']) ?>"><input type="hidden" name="return" value="<?= ops_e($return) ?>"><input type="hidden" name="reason" value="">
       <button type="submit" class="ft-switch<?= $r['enabled'] ? ' on' : '' ?><?= $parent_off ? ' dim' : '' ?>" role="switch" aria-checked="<?= $r['enabled'] ? 'true' : 'false' ?>" aria-label="<?= $r['enabled'] ? 'Nonaktifkan' : 'Aktifkan' ?> <?= ops_e($r['name']) ?>"><span></span></button>
       <span class="ft-state<?= $r['effective'] ? ' on' : '' ?>"><?= $r['effective'] ? 'Aktif' : ($parent_off ? 'Induk nonaktif' : 'Nonaktif') ?></span>
      </form>
     </td>
     <td class="acts"><div class="pg-acts">
      <details class="pg-menu ft-edit"><summary aria-label="Atur <?= ops_e($r['name']) ?>" title="Atur visibilitas"><?= official_svg('pencil') ?></summary><div>
       <p class="ft-edit-title"><?= ops_e($r['name']) ?></p>
       <?php foreach (array('visible_to_residents' => 'Tampil di portal warga', 'visible_to_staff' => 'Tampil untuk pengurus') as $fk => $fl): if ($fk === 'visible_to_residents' && !$r['has_resident']) continue; ?>
       <form method="post" action="<?= site_url('admin/fitur/update') ?>" class="ft-vis"><?= $csrf ?><input type="hidden" name="code" value="<?= ops_e($code) ?>"><input type="hidden" name="field" value="<?= $fk ?>"><input type="hidden" name="value" value="<?= $r[$fk] ? 0 : 1 ?>"><input type="hidden" name="version" value="<?= ops_e($r['version']) ?>"><input type="hidden" name="return" value="<?= ops_e($return) ?>">
        <span><?= $fl ?></span><button type="submit" class="ft-switch sm<?= $r[$fk] ? ' on' : '' ?>" role="switch" aria-checked="<?= $r[$fk] ? 'true' : 'false' ?>" aria-label="<?= $fl ?>: <?= $r[$fk] ? 'matikan' : 'nyalakan' ?>"><span></span></button></form>
       <?php endforeach; ?>
       <?php if (!$r['has_resident']): ?><p class="ft-note">Fitur ini hanya digunakan pengurus (tidak ada menu di portal warga).</p><?php endif; ?>
       <?php if ($r['parent_name']): ?><p class="ft-note">Induk: <b><?= ops_e($r['parent_name']) ?></b>. Bila induk nonaktif, fitur ini ikut tidak aktif.</p><?php endif; ?>
       <?php if ($child_names): ?><p class="ft-note">Turunan: <?= ops_e(implode(', ', $child_names)) ?>.</p><?php endif; ?>
       <?php if (!empty($r['open'])): ?><p class="ft-note warn"><?= (int) $r['open'] ?> transaksi belum selesai.</p><?php endif; ?>
       <?php if ($r['updated_at']): ?><p class="ft-note">Diubah <?= ops_date($r['updated_at'], TRUE) ?></p><?php endif; ?>
      </div></details>
     </div></td>
    </tr>
   <?php endforeach; if (!$filtered): ?><tr><td colspan="5" class="pg-empty">Tidak ada fitur yang cocok dengan pencarian.</td></tr><?php endif; ?>
   </tbody></table>
   <div class="pg-foot pg-foot-in"><p>Menampilkan <b><?= count($filtered) ?></b> dari <b><?= (int) $total ?></b> fitur</p><?php if ($q !== '' || $category !== ''): ?><a class="pg-btn ghost small" href="<?= site_url('admin/fitur') ?>">Reset filter</a><?php endif; ?></div>

  <?php elseif ($tab === 'dashboard'): ?>
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

  <aside class="pg-fitur-side">
   <section class="pg-card pg-panel ft-how"><h2><span class="ft-i" aria-hidden="true">i</span>Cara Kerja</h2><ol>
    <li>Pilih menu/fitur yang ingin diatur.</li><li>Aktifkan untuk menampilkan menu di portal warga dan panel pengurus.</li>
    <li>Nonaktifkan untuk menyembunyikan menu; akses URL langsung ikut ditolak dan data tetap tersimpan.</li><li>Atur siapa yang melihat (warga/pengurus) lewat tombol pensil.</li></ol></section>
   <section class="pg-card pg-panel"><div class="ft-prev-head"><div><h2><?= official_svg('eye') ?>Pratinjau Menu Portal Warga</h2><p>Menu yang terlihat oleh warga saat ini</p></div><a href="<?= site_url('warga') ?>" target="_blank" rel="noopener" title="Buka portal warga" aria-label="Buka portal warga"><?= official_svg('arrow-right') ?></a></div>
    <?php $preview = array_filter($rows, function ($r) {return $r['has_resident'] && $r['residents'];}); ?>
    <div class="ft-prev"><?php foreach ($preview as $r): ?><span class="ft-prev-tile <?= ops_e($r['tone'] ?? 'orange') ?>"><?= official_svg($r['icon'] ?? 'grid') ?><b><?= ops_e($r['name']) ?></b></span><?php endforeach; ?><?php if (!$preview): ?><p class="pg-note">Tidak ada fitur yang tampil di portal warga.</p><?php endif; ?></div>
    <p class="pg-note">Menu inti (Beranda, Profil Saya, Kontak RT, Ganti Password) selalu tampil.</p></section>
   <section class="pg-card ft-sum"><span><?= official_svg('check-circle') ?></span><div><b><?= (int) $active ?> dari <?= (int) $total ?> fitur aktif</b><small>Menu yang aktif tampil sesuai hak akses peran masing-masing.</small></div></section>
  </aside>
 </div>
</div>
<script>
(function(){
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
