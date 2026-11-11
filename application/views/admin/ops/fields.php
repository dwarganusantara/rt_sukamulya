<?php
$editing = !empty($item['id']);
$base = site_url('admin/field-tambahan');
$type_icons = array('text' => 'pencil', 'number' => 'list-num', 'date' => 'calendar', 'select' => 'list', 'checkbox' => 'check', 'textarea' => 'lines');
?>
<div class="ops ops-fields cf">
<?= ops_hero('Pengaturan Sistem', 'Field Tambahan Warga', 'Tambahkan informasi khusus warga sesuai kebutuhan (mis. nomor rumah, status kepemilikan rumah, kendaraan, dll).', array(), 'gear') ?>
<?= ops_flash() ?>

<div class="cf-bar">
 <nav class="cf-tabs" aria-label="Filter status field">
  <a href="<?= $base ?>"<?= $status === 'all' ? ' class="active" aria-current="page"' : '' ?>><?= official_svg('grid') ?>Semua Field <small><?= (int) $counts['all'] ?></small></a>
  <a href="<?= $base ?>?status=active"<?= $status === 'active' ? ' class="active" aria-current="page"' : '' ?>><i class="dot on"></i>Aktif <small><?= (int) $counts['active'] ?></small></a>
  <a href="<?= $base ?>?status=inactive"<?= $status === 'inactive' ? ' class="active" aria-current="page"' : '' ?>><i class="dot"></i>Nonaktif <small><?= (int) $counts['inactive'] ?></small></a>
 </nav>
 <a class="cf-btn" href="<?= $base ?>?baru=1" data-cf-open><?= official_svg('plus') ?>Tambah Field</a>
</div>

<section class="ops-card cf-card">
 <div class="cf-table-wrap"><table class="cf-table"><thead><tr><th>Label Field</th><th>Tipe Isian</th><th>Wajib</th><th class="num">Urutan</th><th>Status</th><th class="num">Aksi</th></tr></thead><tbody>
 <?php foreach ($items as $f): $on = (int) $f['is_active']; ?>
  <tr>
   <td data-label="Label Field"><strong><?= ops_e($f['label']) ?></strong><small><?= $f['help_text'] !== '' ? ops_e($f['help_text']) . ' · ' : '' ?><?= (int) $f['citizen_editable'] ? 'Diisi warga' : 'Hanya pengurus' ?> · terisi <?= (int) $f['filled'] ?> warga</small></td>
   <td data-label="Tipe Isian"><span class="cf-type"><?= official_svg($type_icons[$f['field_type']] ?? 'pencil') ?><?= ops_e($types[$f['field_type']] ?? $f['field_type']) ?></span></td>
   <td data-label="Wajib"><span class="cf-pill <?= (int) $f['is_required'] ? 'req' : '' ?>"><?= (int) $f['is_required'] ? 'Ya' : 'Tidak' ?></span></td>
   <td data-label="Urutan" class="num"><?= (int) $f['sort_order'] ?></td>
   <td data-label="Status"><span class="cf-status <?= $on ? 'on' : '' ?>"><i></i><?= $on ? 'Aktif' : 'Nonaktif' ?></span></td>
   <td data-label="Aksi" class="num"><details class="ops-row-menu cf-more"><summary aria-label="Aksi field <?= ops_e($f['label']) ?>"><?= official_svg('more-v') ?></summary><nav>
    <a href="<?= $base . '/' . (int) $f['id'] ?>">Ubah field</a>
    <form method="post" action="<?= $base ?>"><?= ops_csrf() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="field_id" value="<?= (int) $f['id'] ?>"><button type="submit"><?= $on ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
    <?php if (!(int) $f['filled']): ?><form method="post" action="<?= $base ?>"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="field_id" value="<?= (int) $f['id'] ?>"><button type="submit" data-cf-confirm="Hapus field &quot;<?= ops_e($f['label']) ?>&quot;?">Hapus field</button></form><?php endif; ?>
   </nav></details></td>
  </tr>
 <?php endforeach; ?>
 <?php if (!$items): ?><tr><td colspan="6" class="cf-empty">
  <span class="cf-empty-art" aria-hidden="true"><?= official_svg('doc') ?><b><?= official_svg('plus') ?></b></span>
  <h3><?= $status === 'all' ? 'Belum ada field tambahan' : 'Tidak ada field ' . ($status === 'active' ? 'aktif' : 'nonaktif') ?></h3>
  <p><?= $status === 'all' ? 'Tambahkan field pertama untuk melengkapi data warga.' : 'Ubah filter atau tambahkan field baru.' ?></p>
  <a class="cf-btn" href="<?= $base ?>?baru=1" data-cf-open><?= official_svg('plus') ?>Tambah Field</a>
 </td></tr><?php endif; ?>
 </tbody></table></div>
</section>

<dialog class="cf-dialog" id="cf-form" aria-labelledby="cf-form-t"<?= $form_open ? ' open data-cf-autoopen' : '' ?>>
 <header><h2 id="cf-form-t"><?= $editing ? 'Ubah Field' : 'Tambah Field' ?></h2><a class="cf-x" href="<?= $base ?>" data-cf-close aria-label="Tutup">&times;</a></header>
 <?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
 <form method="post" action="<?= $base . ($editing ? '/' . (int) $item['id'] : '') ?>" class="ops-form one"><?= ops_csrf() ?>
  <?= ops_field('label', 'Label Field', 'text', $item['label'] ?? '', array('required' => TRUE, 'maxlength' => 80, 'placeholder' => 'Contoh: Nomor rumah')) ?>
  <?= ops_field('field_type', 'Tipe isian', 'select', $item['field_type'] ?? 'text', array('options' => $types)) ?>
  <div data-cf-options><?= ops_field('options', 'Opsi pilihan (satu per baris)', 'textarea', $item['options'] ?? '', array('rows' => 3, 'placeholder' => "Contoh:\nMilik sendiri\nSewa", 'help' => 'Hanya untuk tipe "Pilihan". Minimal 2, maksimal 30 opsi.')) ?></div>
  <div class="cf-two"><?= ops_field('sort_order', 'Urutan', 'number', $item['sort_order'] ?? 0, array('min' => 0, 'max' => 999)) ?><?= ops_field('is_required', 'Wajib diisi', 'checkbox', $item['is_required'] ?? 0) ?></div>
  <?= ops_field('help_text', 'Keterangan singkat', 'text', $item['help_text'] ?? '', array('maxlength' => 160, 'placeholder' => 'Contoh: Isi dengan nomor rumah warga')) ?>
  <?= ops_field('citizen_editable', 'Tampil & dapat diisi warga di Edit Profil', 'checkbox', $item['citizen_editable'] ?? 1) ?>
  <p class="ops-note blue">Ditolak otomatis: NIK/KTP/KK, agama, suku, kesehatan, disabilitas, penghasilan, rekening, password, dan data sensitif lain (UU PDP).</p>
  <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= $base ?>" data-cf-close>Batal</a><button class="ops-btn" type="submit">Simpan Field</button></div>
 </form>
</dialog>
</div>
<link rel="stylesheet" href="<?= base_url('assets/custom-fields.css') ?>?v=<?= filemtime(FCPATH . 'assets/custom-fields.css') ?>">
<script>
(()=>{const d=document.getElementById('cf-form');if(!d)return;const modal=()=>{if(d.open&&!d.matches(':modal'))d.close();if(!d.open)d.showModal();d.querySelector('input:not([type=hidden]),select')?.focus();};
if(d.hasAttribute('data-cf-autoopen'))modal();
const fromUrl=/\/field-tambahan\/\d+/.test(location.pathname)||/[?&]baru=/.test(location.search);
document.querySelectorAll('[data-cf-open]').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();modal();}));
d.querySelectorAll('[data-cf-close]').forEach(a=>a.addEventListener('click',e=>{if(fromUrl)return;e.preventDefault();d.close();}));
d.addEventListener('cancel',e=>{if(fromUrl){e.preventDefault();location.assign(d.querySelector('[data-cf-close]').href);}});
const type=d.querySelector('[name=field_type]'),opt=d.querySelector('[data-cf-options]');const sync=()=>{opt.hidden=type.value!=='select';};type?.addEventListener('change',sync);sync();
document.querySelectorAll('[data-cf-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.cfConfirm))e.preventDefault();}));})();
</script>
