<?php
$v = function ($key, $default = '') use ($item) {return $item[$key] ?? $default;};
$edit = !empty($item['id']);
$group = function ($contact) {
    if (!empty($contact['is_emergency']) || $contact['category'] === 'darurat') return 'darurat';
    if ($contact['category'] === 'keamanan') return 'keamanan';
    if (in_array($contact['category'], array('pemerintahan', 'utilitas', 'kesehatan', 'kebersihan'), TRUE)) return 'publik';
    return 'lainnya';
};
$tones = array('darurat' => 'red', 'kesehatan' => 'green', 'keamanan' => 'purple', 'pemerintahan' => 'blue', 'utilitas' => 'blue', 'kebersihan' => 'green', 'pengurus' => 'orange', 'umum' => 'muted');
?>
<div class="ops ops-contacts-admin">
<?= ops_hero('Informasi', 'Kontak Penting', 'Daftar kontak layanan dan pihak terkait di lingkungan RT.', array(), 'phone') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<section class="ops-card contact-directory">
 <nav class="contact-tabs" aria-label="Kategori kontak">
  <button type="button" class="is-active" data-contact-filter="all"><?= official_svg('people') ?>Semua Kontak</button>
  <button type="button" data-contact-filter="publik"><?= official_svg('home') ?>Layanan Publik</button>
  <button type="button" data-contact-filter="keamanan"><?= official_svg('shield') ?>Keamanan</button>
  <button type="button" data-contact-filter="darurat"><?= official_svg('alert') ?>Darurat</button>
  <button type="button" data-contact-filter="lainnya"><?= official_svg('people') ?>Lainnya</button>
 </nav>
 <div class="contact-toolbar">
  <label class="contact-search"><?= official_svg('search') ?><span class="sr-only">Cari kontak</span><input type="search" id="contact-search" placeholder="Cari nama, layanan, atau nomor telepon..." autocomplete="off"></label>
  <?php if ($can('kontak.write')): ?><?php if ($edit): ?><a class="contact-add" href="<?= site_url('admin/kontak') ?>"><?= official_svg('plus') ?>Tambah Kontak</a><?php else: ?><button class="contact-add" type="button" data-contact-open><?= official_svg('plus') ?>Tambah Kontak</button><?php endif; ?><?php endif; ?>
 </div>
 <div class="contact-table-wrap">
  <table class="contact-table" id="contact-table"><thead><tr><th class="num">No</th><th>Nama Layanan</th><th>Kategori</th><th>Nomor Telepon</th><th>Jam Layanan</th><th class="action">Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $index => $contact): $stale = !$contact['verified_at'] || $contact['verified_at'] < date('Y-m-d', strtotime('-6 months')); ?>
   <tr data-contact-group="<?= $group($contact) ?>" data-contact-search="<?= ops_e(mb_strtolower($contact['name'] . ' ' . $contact['phone'] . ' ' . ($categories[$contact['category']] ?? $contact['category']))) ?>">
    <td class="num"><?= $index + 1 ?></td>
    <td class="contact-name"><strong><?= ops_e($contact['name']) ?></strong><small><?= $contact['is_public'] ? 'Tampil di portal warga' : 'Internal pengurus' ?><?= !$contact['is_active'] ? ' · Nonaktif' : '' ?></small></td>
    <td><span class="contact-category <?= $tones[$contact['category']] ?? 'muted' ?>"><?= official_svg($contact['category'] === 'keamanan' ? 'shield' : ($contact['category'] === 'darurat' ? 'alert' : 'home')) ?><?= ops_e($categories[$contact['category']] ?? $contact['category']) ?></span></td>
    <td><a class="contact-phone" href="tel:<?= ops_e(preg_replace('/[^0-9+]/', '', $contact['phone'])) ?>"><?= official_svg('phone') ?><?= ops_e($contact['phone']) ?></a></td>
    <td class="contact-hours"><?= ops_e($contact['service_hours'] ?: 'Tidak ditentukan') ?><?php if ($stale): ?><small>Perlu diperiksa ulang</small><?php endif; ?></td>
    <td class="action"><?php if ($can('kontak.write')): ?><details class="contact-menu"><summary aria-label="Aksi untuk <?= ops_e($contact['name']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/kontak/' . (int) $contact['id']) ?>"><?= official_svg('pencil') ?>Ubah kontak</a>
      <form method="post" action="<?= site_url('admin/kontak') ?>"><?= ops_csrf() ?><input type="hidden" name="contact_id" value="<?= (int) $contact['id'] ?>"><button name="act" value="verify"><?= official_svg('check-circle') ?>Tandai sudah dicek</button></form>
      <form method="post" action="<?= site_url('admin/kontak') ?>"><?= ops_csrf() ?><input type="hidden" name="contact_id" value="<?= (int) $contact['id'] ?>"><button name="act" value="toggle"><?= official_svg('swap') ?><?= $contact['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
     </div></details><?php else: ?>—<?php endif; ?></td>
   </tr>
  <?php endforeach; ?>
   <tr class="contact-empty"<?= $items ? ' hidden' : '' ?>><td colspan="6">Belum ada kontak penting yang sesuai.</td></tr>
  </tbody></table>
 </div>
 <footer class="contact-footer"><span id="contact-count"><?= count($items) ?></span> kontak ditampilkan</footer>
</section>
<?php if ($can('kontak.write')): ?>
<dialog class="contact-dialog" id="contact-dialog" data-auto-open="<?= ($edit || $error) ? '1' : '0' ?>" aria-labelledby="contact-dialog-title">
 <header><div><span><?= official_svg('phone') ?></span><div><small>Kontak Penting</small><h2 id="contact-dialog-title"><?= $edit ? 'Ubah Kontak' : 'Tambah Kontak' ?></h2></div></div><button type="button" data-contact-close aria-label="Tutup">&times;</button></header>
 <form method="post" action="<?= site_url('admin/kontak' . ($edit ? '/' . (int) $item['id'] : '')) ?>" class="ops-form contact-form"><?= ops_csrf() ?>
  <?= ops_field('name', 'Nama layanan', 'text', $v('name'), array('required' => TRUE, 'maxlength' => 140, 'placeholder' => 'Contoh: PLN Call Center')) ?>
  <?= ops_field('category', 'Kategori', 'select', $v('category', 'umum'), array('options' => $categories)) ?>
  <?= ops_field('phone', 'Nomor telepon', 'tel', $v('phone'), array('required' => TRUE, 'maxlength' => 30, 'placeholder' => 'Contoh: 123 atau 0812-3456-7890')) ?>
  <?= ops_field('service_hours', 'Jam layanan', 'text', $v('service_hours'), array('maxlength' => 100, 'placeholder' => '24 jam / Senin–Jumat 08.00–16.00')) ?>
  <?= ops_field('address', 'Alamat', 'text', $v('address'), array('maxlength' => 255)) ?>
  <?= ops_field('note', 'Keterangan', 'text', $v('note'), array('maxlength' => 255)) ?>
  <?= ops_field('sort_order', 'Urutan', 'number', (int) $v('sort_order', 0)) ?>
  <fieldset class="contact-options"><legend>Pengaturan</legend>
   <?= ops_field('is_public', 'Tampilkan di portal warga', 'checkbox', $edit ? $v('is_public') : 1) ?>
   <?= ops_field('is_emergency', 'Kontak darurat', 'checkbox', $v('is_emergency')) ?>
   <?= ops_field('is_active', 'Aktif', 'checkbox', $edit ? $v('is_active') : 1) ?>
   <?= ops_field('verified_now', 'Nomor sudah diperiksa hari ini', 'checkbox', 0) ?>
  </fieldset>
  <div class="ops-form-actions"><button class="ops-btn ghost" type="button" data-contact-close>Batal</button><button class="ops-btn" type="submit">Simpan Kontak</button></div>
 </form>
</dialog>
<?php endif; ?>
</div>
<script>
(function(){
 var table=document.getElementById('contact-table'),search=document.getElementById('contact-search'),tabs=[].slice.call(document.querySelectorAll('[data-contact-filter]')),count=document.getElementById('contact-count'),active='all';
 function filter(){var q=(search.value||'').toLocaleLowerCase('id').trim(),n=0;table.querySelectorAll('tbody tr[data-contact-group]').forEach(function(row){var show=(active==='all'||row.dataset.contactGroup===active)&&(!q||row.dataset.contactSearch.indexOf(q)>-1);row.hidden=!show;if(show)n++});var empty=table.querySelector('.contact-empty');if(empty)empty.hidden=n!==0;count.textContent=n}
 tabs.forEach(function(tab){tab.addEventListener('click',function(){active=tab.dataset.contactFilter;tabs.forEach(function(x){x.classList.toggle('is-active',x===tab)});filter()})});search.addEventListener('input',filter);
 document.querySelectorAll('.contact-menu').forEach(function(menu){menu.addEventListener('toggle',function(){if(menu.open)document.querySelectorAll('.contact-menu[open]').forEach(function(x){if(x!==menu)x.removeAttribute('open')})})});
 var dialog=document.getElementById('contact-dialog');if(dialog){document.querySelectorAll('[data-contact-open]').forEach(function(x){x.addEventListener('click',function(){dialog.showModal()})});document.querySelectorAll('[data-contact-close]').forEach(function(x){x.addEventListener('click',function(){dialog.close()})});dialog.addEventListener('click',function(e){if(e.target===dialog)dialog.close()});if(dialog.dataset.autoOpen==='1')dialog.showModal()}
 filter();
})();
</script>
