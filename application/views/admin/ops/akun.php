<?php
$v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; };
$edit = !empty($item['id']);
$form_open = $edit || $error;
$role_icons = array('admin' => 'crown', 'ketua' => 'star', 'sekretaris' => 'person', 'bendahara' => 'wallet', 'operator' => 'laptop', 'keamanan' => 'shield', 'inventaris' => 'box', 'sosial' => 'heart', 'auditor' => 'eye');
$group_icons = array('Utama' => 'home', 'Kependudukan' => 'people', 'Layanan Warga' => 'layers', 'Kegiatan & Informasi' => 'calendar', 'Keuangan' => 'wallet', 'Kegiatan & Keamanan' => 'shield', 'Operasional RT' => 'box', 'Pengaturan' => 'gear');
$base_of = function ($r) use ($menu_access) { return $menu_access->base_role($r); };
$role_icon = function ($r) use ($role_icons, $base_of) { return official_svg($role_icons[$base_of($r)] ?? 'person'); };
$role_desc = function ($r) use ($role_descriptions, $custom_roles, $base_roles) {
    if (isset($custom_roles[$r])) return trim($custom_roles[$r]['description']) ?: 'Turunan dari ' . ($base_roles[$custom_roles[$r]['base_role']] ?? $custom_roles[$r]['base_role']) . '.';
    return $role_descriptions[$r] ?? '';
};
$peran_url = function ($r, $extra = '') { return site_url('admin/akun') . '?tab=peran&role=' . rawurlencode($r) . $extra; };
$edit_role = $tab === 'peran' && $this->input->get('edit_role') && isset($custom_roles[$selected_role]);
$new_role = $tab === 'peran' && $this->input->get('new_role');
list($sel_ok, $sel_total) = $menu_access->summary($selected_role);
$sel_pct = $sel_total ? (int) round(100 * $sel_ok / $sel_total) : 0;
/** Menu ⋮ untuk sebuah peran (daftar kiri & header kanan). */
$role_menu = function ($r, $label) use ($custom_roles, $peran_url, $roles) {
    $h = '<details class="ops-row-menu akx-role-menu"><summary aria-label="Aksi peran ' . ops_e($label) . '">&bull;&bull;&bull;</summary><nav>';
    $h .= '<a href="' . $peran_url($r) . '#akx-role-detail">Atur hak akses menu</a>';
    if (isset($custom_roles[$r])) {
        $h .= '<a href="' . $peran_url($r, '&edit_role=1') . '">Ubah nama / deskripsi</a>';
        $h .= '<form method="post" action="' . site_url('admin/akun/peran') . '">' . ops_csrf() . '<input type="hidden" name="action" value="delete"><input type="hidden" name="role" value="' . ops_e($r) . '"><button type="submit" data-akx-confirm="Hapus peran ' . ops_e($label) . '? Peran hanya dapat dihapus bila tidak dipakai akun.">Hapus peran</button></form>';
    }
    $h .= '<form method="post" action="' . site_url('admin/akun/hak-akses') . '">' . ops_csrf() . '<input type="hidden" name="role" value="' . ops_e($r) . '"><input type="hidden" name="tab" value="peran"><input type="hidden" name="action" value="default"><button type="submit" data-akx-confirm="Pulihkan hak akses menu ' . ops_e($label) . ' ke bawaan peran?">Pulihkan ke bawaan</button></form>';
    return $h . '</nav></details>';
};
?>
<div class="ops ops-accounts akx akx-v2">
<?= ops_hero('Pengaturan Sistem', 'Akun Pengurus & Hak Akses', 'Kelola akun pengurus, peran (role), dan atur menu yang dapat diakses oleh setiap peran.', array(), 'people') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

<nav class="akx-tabs akx-tabs-2" aria-label="Bagian Akun & Hak Akses">
 <a href="<?= site_url('admin/akun') ?>?tab=akun"<?= $tab === 'akun' ? ' class="active" aria-current="page"' : '' ?>><?= official_svg('person') ?>Daftar Akun</a>
 <a href="<?= $peran_url($selected_role) ?>"<?= $tab !== 'akun' ? ' class="active" aria-current="page"' : '' ?>><?= official_svg('layers') ?>Hak Akses Peran</a>
</nav>

<?php if ($tab === 'akun'): ?>
<section class="ops-card akx-card">
 <header class="akx-card-head"><span class="akx-head-icon plain" aria-hidden="true"><?= official_svg('person') ?></span><div><h2>Daftar Akun Pengurus</h2><p>Semua akun pengurus RT/RW beserta perannya.</p></div><a class="ops-btn akx-add" href="<?= site_url('admin/akun') ?>#akun-form" data-akx-open="akun-form"><?= official_svg('plus') ?>Tambah Akun</a></header>
 <div class="ops-table-wrap"><table class="ops-table stack akx-table"><thead><tr><th>Nama</th><th>Peran</th><th>Status</th><th class="akx-th-action">Aksi</th></tr></thead><tbody>
 <?php foreach ($items as $a): $name = trim((string) ($a['full_name'] ?? '')) ?: $a['username']; $initial = preg_replace('/[^\p{L}\p{N}]/u', '', $name) ?: $a['username']; $rk = $a['role_key']; ?>
  <tr><td data-label="Nama"><div class="ops-account-cell"><span class="ops-account-initial akx-av-<?= ops_e($a['role']) ?>" aria-hidden="true"><?= ops_e(mb_strtoupper(mb_substr($initial, 0, 2))) ?></span><div><strong><?= ops_e($name) ?></strong><small><?= ops_e($a['username']) ?></small></div></div></td>
   <td data-label="Peran"><span class="ops-role-tag akx-role-<?= ops_e($a['role']) ?>"><?= $role_icon($rk) ?><?= ops_e($roles[$rk] ?? $a['role']) ?></span></td>
   <td data-label="Status"><?= ops_badge((int) ($a['is_active'] ?? 1) ? 'on' : 'off', array('on' => 'Aktif', 'off' => 'Dicabut'), array('on' => 'ok', 'off' => 'muted')) ?></td>
   <td data-label="Aksi" class="akx-td-action"><details class="ops-row-menu"><summary aria-label="Aksi akun <?= ops_e($a['username']) ?>">&bull;&bull;&bull;</summary><nav><a href="<?= site_url('admin/akun/' . (int) $a['id']) ?>">Ubah akun / peran</a><a href="<?= site_url('admin/akun/' . (int) $a['id']) ?>#f-password">Reset password</a><a href="<?= $peran_url($rk) ?>">Atur hak akses peran</a></nav></details></td></tr>
 <?php endforeach; ?>
 <?php if (!$items): ?><tr><td colspan="4" class="ops-muted">Belum ada akun pengurus.</td></tr><?php endif; ?>
 </tbody></table></div>
</section>

<?php elseif ($tab === 'peran'): ?>
<div class="akx-rp">
 <section class="ops-card akx-card akx-rl-card">
  <header class="akx-rl-head"><div><h2>Daftar Peran (Role)</h2><p>Kelola peran dan hak akses menu.</p></div><a class="akx-outline-btn" href="<?= $peran_url($selected_role, '&new_role=1') ?>" data-akx-open="peran-form"><?= official_svg('plus') ?>Tambah Peran</a></header>
  <ul class="akx-rl">
  <?php foreach ($roles as $r => $label): list($ok, $total) = $menu_access->summary($r); $base = $base_of($r); ?>
   <li class="akx-rl-item akx-tone-<?= ops_e($base) ?><?= $r === $selected_role ? ' active' : '' ?>">
    <a class="akx-rl-link" href="<?= $peran_url($r) ?>#akx-role-detail"<?= $r === $selected_role ? ' aria-current="true"' : '' ?>>
     <span class="akx-rl-icon" aria-hidden="true"><?= $role_icon($r) ?></span>
     <span class="akx-rl-text"><strong><?= ops_e($label) ?></strong><small><?= (int) $ok ?> dari <?= (int) $total ?> menu aktif<?= isset($custom_roles[$r]) ? ' · turunan ' . ops_e($base_roles[$base] ?? $base) : '' ?></small></span>
     <span class="akx-status<?= $ok ? '' : ' off' ?>"><i></i><?= $ok ? 'Aktif' : 'Nonaktif' ?></span>
    </a>
    <?= $role_menu($r, $label) ?>
   </li>
  <?php endforeach; ?>
  </ul>
  <a class="akx-matrix-link" href="<?= site_url('admin/akun') ?>?tab=menu&role=<?= rawurlencode($selected_role) ?>"><?= official_svg('grid') ?>Lihat matriks semua peran</a>
 </section>

 <section class="ops-card akx-card akx-rd" id="akx-role-detail">
  <header class="akx-rd-head akx-tone-<?= ops_e($base_of($selected_role)) ?>">
   <span class="akx-rl-icon big" aria-hidden="true"><?= $role_icon($selected_role) ?></span>
   <div><h2><?= ops_e($roles[$selected_role]) ?></h2><p><?= ops_e($role_desc($selected_role)) ?></p></div>
   <span class="akx-status<?= $sel_ok ? '' : ' off' ?>"><i></i><?= $sel_ok ? 'Aktif' : 'Nonaktif' ?></span>
   <?= $role_menu($selected_role, $roles[$selected_role]) ?>
  </header>
  <div class="akx-summary">
   <span class="akx-summary-icon" aria-hidden="true"><?= official_svg('layers') ?></span>
   <div><small>Ringkasan Akses</small><strong><b data-akx-sum-ok><?= (int) $sel_ok ?></b> dari <?= (int) $sel_total ?> <span>menu aktif</span></strong></div>
   <div class="akx-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $sel_pct ?>" aria-label="Persentase menu aktif" data-akx-total="<?= (int) $sel_total ?>"><i style="width:<?= $sel_pct ?>%" data-akx-bar></i></div>
   <em data-akx-pct><?= $sel_pct ?>%</em>
  </div>
  <form method="post" action="<?= site_url('admin/akun/hak-akses') ?>" class="akx-perm-form" data-akx-perm>
   <?= ops_csrf() ?><input type="hidden" name="role" value="<?= ops_e($selected_role) ?>"><input type="hidden" name="tab" value="peran">
   <div class="akx-am-head"><h3>Pengaturan Akses Menu</h3><div><button type="button" class="akx-mini-btn" data-akx-all="1"><?= official_svg('check') ?>Aktifkan Semua</button><button type="button" class="akx-mini-btn muted" data-akx-all="0"><?= official_svg('close') ?>Nonaktifkan Semua</button></div></div>
   <div class="akx-glist">
   <?php foreach ($menu_access->groups() as $gname => $gdef):
       $rows = array(); $allowed_n = 0; $active_n = 0; $premium = FALSE;
       foreach ((array) $gdef[1] as $mi) {
           if (!is_array($mi) || !isset($mi[0], $mi[2])) continue;
           $rbac = $menu_access->rbac_allows($selected_role, $mi[0]);
           $locked = $rbac && $menu_access->is_locked($selected_role, $mi[0]);
           $on = $rbac && $menu_access->allowed($selected_role, $mi[0]);
           $sub = $menu_access->commercial($mi[0]);
           if ($sub === 'SUBSCRIPTION_REQUIRED') $premium = TRUE;
           $rows[] = array($mi[0], $mi[2], $rbac, $locked, $on, $sub);
           if ($rbac) {$allowed_n++; if ($on) $active_n++;}
       }
       if (!$rows) continue;
   ?>
    <details class="akx-g<?= $allowed_n ? '' : ' is-na' ?>" data-akx-group>
     <summary>
      <input type="checkbox" class="akx-check" data-akx-group-toggle aria-label="Pilih semua menu <?= ops_e($gname) ?>"<?= $allowed_n ? '' : ' disabled' ?><?= $allowed_n && $active_n === $allowed_n ? ' checked' : '' ?>>
      <span class="akx-g-icon" aria-hidden="true"><?= official_svg($group_icons[$gname] ?? 'grid') ?></span>
      <strong><?= ops_e($gname === 'Utama' ? 'Dashboard' : $gname) ?></strong>
      <small data-akx-count><?= count($rows) ?> menu</small>
      <?php if ($premium): ?><span class="akx-need-sub"><?= official_svg('key') ?>Perlu berlangganan</span><?php endif; ?>
      <?php if (!$allowed_n): ?><span class="akx-g-na">Tidak tersedia untuk peran ini</span><?php endif; ?>
      <span class="akx-chev" aria-hidden="true"><?= official_svg('chev-right') ?></span>
     </summary>
     <ul class="akx-gitems">
     <?php foreach ($rows as $row): list($path, $label, $rbac, $locked, $on, $sub) = $row; ?>
      <li class="<?= $rbac ? '' : 'is-na' ?>"><label>
       <input type="checkbox" class="akx-check" name="menus[]" value="<?= ops_e($path) ?>"<?= ($rbac && !$locked) ? ' data-akx-item' : '' ?><?= $on ? ' checked' : '' ?><?= (!$rbac || $locked) ? ' disabled' : '' ?>>
       <span><?= ops_e($label) ?></span>
       <?php if ($sub === 'SUBSCRIPTION_REQUIRED'): ?><em class="akx-chip premium"><?= official_svg('crown') ?>Premium</em><?php elseif ($sub === 'FEATURE_DISABLED'): ?><em class="akx-chip off">Nonaktif</em><?php endif; ?>
       <?php if ($locked): ?><em class="akx-chip lock">Wajib</em><?php endif; ?>
       <?php if (!$rbac): ?><em class="akx-chip na">Tidak diizinkan peran</em><?php endif; ?>
      </label></li>
     <?php endforeach; ?>
     </ul>
    </details>
   <?php endforeach; ?>
   </div>
   <p class="akx-note">Izin dasar mengikuti peran<?= isset($custom_roles[$selected_role]) ? ' dasar (' . ops_e($base_roles[$base_of($selected_role)] ?? '') . ')' : '' ?>. Pengaturan ini hanya mempersempit menu dan ditegakkan di server. Menu <b>Premium</b> tetap memerlukan langganan aktif RT/RW.</p>
   <div class="akx-actions">
    <button class="ops-btn ghost" type="reset"><?= official_svg('swap') ?>Reset</button>
    <button class="ops-btn" type="submit"><?= official_svg('save') ?>Simpan Perubahan</button>
   </div>
  </form>
 </section>
</div>

<?php else: ?>
<section class="ops-card akx-card">
 <header class="akx-card-head"><span class="akx-head-icon" aria-hidden="true"><?= official_svg('grid') ?></span><div><h2>Matriks Hak Akses Menu</h2><p>Ringkasan menu yang dapat diakses setiap peran. Klik nama peran untuk mengubah.</p></div><a class="akx-outline-btn" href="<?= $peran_url($selected_role) ?>"><?= official_svg('layers') ?>Kembali ke Hak Akses Peran</a></header>
 <div class="akx-legend"><span><i class="akx-dot on"></i>Aktif</span><span><i class="akx-dot off"></i>Dimatikan</span><span><i class="akx-dot lock"></i>Wajib</span><span><i class="akx-dot na"></i>Tidak diizinkan peran</span></div>
 <div class="ops-table-wrap"><table class="ops-table akx-matrix"><thead><tr><th scope="col">Menu</th><?php foreach ($roles as $r => $label): ?><th scope="col"><a href="<?= $peran_url($r) ?>"><?= ops_e($label) ?></a></th><?php endforeach; ?></tr></thead><tbody>
 <?php foreach ($menu_access->groups() as $gname => $gdef): ?>
  <tr class="akx-matrix-group"><th colspan="<?= count($roles) + 1 ?>" scope="colgroup"><?= official_svg($group_icons[$gname] ?? 'grid') ?><?= ops_e($gname) ?></th></tr>
  <?php foreach ((array) $gdef[1] as $mi): if (!is_array($mi) || !isset($mi[0], $mi[2])) continue; ?>
   <tr><th scope="row"><?= ops_e($mi[2]) ?></th>
   <?php foreach ($roles as $r => $label):
       if (!$menu_access->rbac_allows($r, $mi[0])) {$s = 'na'; $t = 'Tidak diizinkan peran';}
       elseif ($menu_access->is_locked($r, $mi[0])) {$s = 'lock'; $t = 'Wajib';}
       elseif ($menu_access->allowed($r, $mi[0])) {$s = 'on'; $t = 'Aktif';}
       else {$s = 'off'; $t = 'Dimatikan';} ?>
    <td><i class="akx-dot <?= $s ?>" title="<?= ops_e($t) ?>"></i><span class="sr-only"><?= ops_e($t) ?></span></td>
   <?php endforeach; ?></tr>
  <?php endforeach; ?>
 <?php endforeach; ?>
 </tbody></table></div>
</section>
<section class="ops-card"><details class="ops-more"><summary>Matriks izin modul (RBAC dasar)</summary>
 <div class="ops-table-wrap" style="margin-top:8px"><table class="ops-table"><thead><tr><th>Izin</th><?php foreach ($base_roles as $r => $label): ?><th title="<?= ops_e($label) ?>"><?= ops_e($label) ?></th><?php endforeach; ?></tr></thead><tbody>
 <?php foreach ($perms as $perm => $allowed): ?><tr><td><code><?= ops_e($perm) ?></code></td><?php foreach ($base_roles as $r => $label): ?><td><?= in_array($r, $allowed, TRUE) ? '✓<span class="sr-only"> ya</span>' : '<span class="ops-muted">—</span>' ?></td><?php endforeach; ?></tr><?php endforeach; ?>
 </tbody></table></div>
</details></section>
<?php endif; ?>

<dialog class="akx-dialog" id="akun-form" aria-labelledby="akun-form-title"<?= $form_open ? ' open data-akx-autoopen' : '' ?>>
 <header><h2 id="akun-form-title"><?= $edit ? 'Ubah akun ' . ops_e($item['username']) : 'Tambah Akun Pengurus' ?></h2><a class="akx-x" href="<?= site_url('admin/akun') ?>" data-akx-close aria-label="Tutup">&times;</a></header>
 <form method="post" action="<?= site_url('admin/akun' . ($edit ? '/' . (int) $item['id'] : '')) ?>" class="ops-form one" autocomplete="off"><?= ops_csrf() ?>
  <?php if (!$edit): ?><?= ops_field('username', 'Username', 'text', $v('username'), array('required' => TRUE, 'maxlength' => 80)) ?><?php endif; ?>
  <?= ops_field('full_name', 'Nama lengkap', 'text', $v('full_name'), array('maxlength' => 140)) ?>
  <?php $current_key = $v('custom_role') ?: $v('role', 'operator'); ?>
  <label for="f-role">Peran</label><select name="role" id="f-role" required>
  <option value="central_admin" disabled aria-disabled="true">Admin Pusat (Super Admin) — Terkunci</option>
  <optgroup label="Peran dasar"><?php foreach ($base_roles as $code => $label): ?><option value="<?= ops_e($code) ?>"<?= $current_key === $code ? ' selected' : '' ?>><?= ops_e($label) ?></option><?php endforeach; ?></optgroup>
  <?php if ($custom_roles): ?><optgroup label="Peran tambahan"><?php foreach ($custom_roles as $code => $cr): ?><option value="<?= ops_e($code) ?>"<?= $current_key === $code ? ' selected' : '' ?>><?= ops_e($cr['label']) ?> (turunan <?= ops_e($base_roles[$cr['base_role']] ?? $cr['base_role']) ?>)</option><?php endforeach; ?></optgroup><?php endif; ?>
  </select>
  <small aria-label="Role Admin Pusat terkunci">&#128274; Admin Pusat (Super Admin) terkunci. Administrator biasa tidak dapat memilih, membuat, atau mengubah akun ini.</small>
  <?= ops_field('password', $edit ? 'Reset password (kosongkan jika tidak diubah)' : 'Password awal', 'password', '', array('required' => !$edit, 'help' => 'Minimal 10 karakter. Sampaikan secara langsung, jangan lewat grup.')) ?>
  <?= ops_field('is_active', 'Akun aktif (hapus centang untuk mencabut akses)', 'checkbox', $edit ? (int) ($item['is_active'] ?? 1) : 1) ?>
  <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url('admin/akun') ?>" data-akx-close>Batal</a><button class="ops-btn" type="submit">Simpan Akun</button></div>
 </form>
</dialog>

<dialog class="akx-dialog" id="peran-form" aria-labelledby="peran-form-title"<?= ($edit_role || $new_role) ? ' open data-akx-autoopen' : '' ?>>
 <header><h2 id="peran-form-title"><?= $edit_role ? 'Ubah Peran' : 'Tambah Peran' ?></h2><a class="akx-x" href="<?= $peran_url($selected_role) ?>" data-akx-close aria-label="Tutup">&times;</a></header>
 <form method="post" action="<?= site_url('admin/akun/peran') ?>" class="ops-form one" autocomplete="off"><?= ops_csrf() ?>
  <input type="hidden" name="action" value="<?= $edit_role ? 'update' : 'create' ?>">
  <?php if ($edit_role): ?><input type="hidden" name="role" value="<?= ops_e($selected_role) ?>"><?php endif; ?>
  <?= ops_field('label', 'Nama peran', 'text', $edit_role ? $custom_roles[$selected_role]['label'] : '', array('required' => TRUE, 'maxlength' => 80, 'placeholder' => 'Contoh: Koordinator Kebersihan')) ?>
  <?= ops_field('description', 'Deskripsi singkat', 'text', $edit_role ? $custom_roles[$selected_role]['description'] : '', array('maxlength' => 255, 'placeholder' => 'Tugas utama peran ini (opsional)')) ?>
  <?php if (!$edit_role): ?>
   <label for="f-base-role">Peran dasar (izin modul)</label>
   <select id="f-base-role" name="base_role" required><?php foreach ($base_roles as $code => $label): ?><option value="<?= ops_e($code) ?>"<?= $code === 'operator' ? ' selected' : '' ?>><?= ops_e($label) ?></option><?php endforeach; ?></select>
   <small>Peran baru memakai izin modul peran dasar, lalu Anda dapat mempersempit menu yang boleh diakses. Peran baru tidak pernah mendapat izin melebihi peran dasarnya.</small>
  <?php else: ?>
   <small>Turunan dari <b><?= ops_e($base_roles[$custom_roles[$selected_role]['base_role']] ?? '') ?></b>. Peran dasar tidak dapat diubah setelah dibuat.</small>
  <?php endif; ?>
  <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= $peran_url($selected_role) ?>" data-akx-close>Batal</a><button class="ops-btn" type="submit"><?= $edit_role ? 'Simpan Peran' : 'Tambah Peran' ?></button></div>
 </form>
</dialog>
</div>
<link rel="stylesheet" href="<?= base_url('assets/admin-accounts.css') ?>?v=<?= filemtime(FCPATH . 'assets/admin-accounts.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin-roles.css') ?>?v=<?= filemtime(FCPATH . 'assets/admin-roles.css') ?>">
<script src="<?= base_url('assets/admin-accounts.js') ?>?v=<?= filemtime(FCPATH . 'assets/admin-accounts.js') ?>" defer></script>
