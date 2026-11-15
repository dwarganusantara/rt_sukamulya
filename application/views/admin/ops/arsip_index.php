<?php
$doc_group = function ($category) {
    if (in_array($category, array('surat_keluar', 'surat_masuk', 'sk'), TRUE)) return 'surat';
    if (in_array($category, array('keuangan', 'program', 'notulen'), TRUE)) return 'laporan';
    if ($category === 'aset') return 'dokumentasi';
    return 'arsip';
};
$category_tone = array('surat_keluar' => 'orange', 'surat_masuk' => 'orange', 'sk' => 'orange', 'notulen' => 'purple', 'keuangan' => 'blue', 'aset' => 'green', 'program' => 'blue', 'serah_terima' => 'purple', 'umum' => 'muted');
$periods = array_values(array_unique(array_filter(array_column($items, 'period'))));
rsort($periods);
?>
<div class="ops ops-archive-admin">
<?= ops_hero('Informasi & Arsip', 'Dokumen & Arsip Digital', 'Kelola dokumen penting RT secara aman, terstruktur, dan mudah diakses.', array(), 'doc') ?>
<?= ops_flash() ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>

<nav class="archive-tabs" aria-label="Kategori dokumen">
 <button type="button" class="is-active" data-archive-tab="all"><?= official_svg('doc') ?>Semua Dokumen</button>
 <button type="button" data-archive-tab="surat"><?= official_svg('doc') ?>Surat</button>
 <button type="button" data-archive-tab="laporan"><?= official_svg('layers') ?>Laporan</button>
 <button type="button" data-archive-tab="dokumentasi"><?= official_svg('image') ?>Foto/Dokumentasi</button>
 <button type="button" data-archive-tab="arsip"><?= official_svg('database') ?>Arsip</button>
</nav>
<div class="archive-toolbar">
 <label class="archive-search"><?= official_svg('search') ?><span class="sr-only">Cari dokumen</span><input id="archive-search" type="search" value="<?= ops_e($q) ?>" placeholder="Cari judul atau nomor dokumen..." autocomplete="off"></label>
 <label class="archive-period"><?= official_svg('calendar') ?><span class="sr-only">Periode</span><select id="archive-period"><option value="">Semua Periode</option><?php foreach ($periods as $period): ?><option value="<?= ops_e(mb_strtolower($period)) ?>"><?= ops_e($period) ?></option><?php endforeach; ?></select></label>
 <?php if ($can('arsip.write')): ?><button class="archive-upload-open" type="button" data-archive-open><?= official_svg('plus') ?>Unggah Dokumen</button><?php endif; ?>
</div>

<div class="archive-layout<?= $can('arsip.write') ? '' : ' no-form' ?>" id="archive-layout">
 <section class="archive-list-card">
  <div class="archive-table-wrap"><table class="archive-table" id="archive-table"><thead><tr><th class="num">No</th><th>Judul Dokumen</th><th>Kategori</th><th>Tanggal</th><th>Versi</th><th class="action">Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $index => $document): $date = $document['doc_date'] ?: $document['created_at']; ?>
   <tr data-archive-group="<?= $doc_group($document['category']) ?>" data-archive-period="<?= ops_e(mb_strtolower((string) $document['period'])) ?>" data-archive-search="<?= ops_e(mb_strtolower($document['title'] . ' ' . $document['doc_number'] . ' ' . $document['period'])) ?>">
    <td class="num"><?= $index + 1 ?></td>
    <td class="archive-title"><span><?= official_svg($document['category'] === 'aset' ? 'image' : 'doc') ?></span><div><strong><?= ops_e($document['title']) ?></strong><small><?= ops_e($document['doc_number'] ?: ($document['period'] ?: 'Tanpa nomor dokumen')) ?></small></div></td>
    <td><span class="archive-category <?= $category_tone[$document['category']] ?? 'muted' ?>"><?= official_svg($document['category'] === 'aset' ? 'image' : ($doc_group($document['category']) === 'laporan' ? 'layers' : 'doc')) ?><?= ops_e($categories[$document['category']] ?? $document['category']) ?></span></td>
    <td><?= ops_date($date) ?></td><td><span class="archive-version">v<?= (int) $document['current_version'] ?>.0</span></td>
    <td class="action"><div class="archive-actions"><a href="<?= site_url('admin/arsip/' . (int) $document['id']) ?>" aria-label="Lihat <?= ops_e($document['title']) ?>"><?= official_svg('eye') ?></a><details><summary aria-label="Menu dokumen"><?= official_svg('more-v') ?></summary><div><a href="<?= site_url('admin/arsip/' . (int) $document['id']) ?>"><?= official_svg('eye') ?>Lihat detail</a><a href="<?= site_url('admin/arsip/' . (int) $document['id'] . '/unduh') ?>"><?= official_svg('download') ?>Unduh versi terbaru</a></div></details></div></td>
   </tr>
  <?php endforeach; ?>
   <tr class="archive-empty"<?= $items ? ' hidden' : '' ?>><td colspan="6"><?= ops_empty_state('Belum ada dokumen', 'Unggah dokumen pertama untuk mulai mengelola arsip.', 'doc') ?></td></tr>
  </tbody></table></div>
  <footer>Menampilkan <b id="archive-count"><?= count($items) ?></b> dari <?= count($items) ?> dokumen</footer>
 </section>

 <?php if ($can('arsip.write')): ?>
 <aside class="archive-upload-card" id="archive-upload-card">
  <header><div><?= official_svg('upload') ?><h2>Unggah Dokumen</h2></div><button type="button" data-archive-close aria-label="Tutup panel unggah">&times;</button></header>
  <nav class="archive-form-tabs" aria-label="Tahap unggah"><button type="button" class="is-active" data-archive-step="info"><?= official_svg('doc') ?>Info Dokumen</button><button type="button" data-archive-step="file"><?= official_svg('clip') ?>Berkas</button></nav>
  <form method="post" enctype="multipart/form-data" class="archive-form"><?= ops_csrf() ?>
   <div class="archive-step is-active" data-archive-panel="info">
    <?= ops_field('title', 'Judul Dokumen', 'text', '', array('required' => TRUE, 'maxlength' => 190, 'placeholder' => 'Contoh: Notulen Rapat RT')) ?>
    <div class="archive-form-grid"><?= ops_field('category', 'Kategori', 'select', 'umum', array('options' => $categories)) ?><?= ops_field('doc_date', 'Tanggal', 'date', date('Y-m-d')) ?></div>
    <?= ops_field('doc_number', 'Nomor dokumen', 'text', '', array('maxlength' => 100, 'placeholder' => 'Opsional')) ?>
    <div class="archive-form-grid"><?= ops_field('period', 'Periode', 'text', '', array('maxlength' => 40, 'placeholder' => '2026 / Semester 2')) ?><?= ops_field('confidentiality', 'Akses', 'select', 'internal', array('options' => $can('arsip.confidential') ? $levels : array_diff_key($levels, array('rahasia' => 1)))) ?></div>
    <?= ops_field('retention_until', 'Simpan hingga (retensi)', 'date', '') ?>
    <button class="archive-next" type="button" data-archive-next>Lanjut Pilih Berkas<?= official_svg('chev-right') ?></button>
   </div>
   <div class="archive-step" data-archive-panel="file">
    <div class="archive-dropzone"><?= official_svg('upload') ?><strong>Pilih berkas dokumen</strong><p>PDF, gambar, Word, Excel, TXT, atau CSV. Maksimal 10 MB.</p><?= ops_field('document', 'Berkas', 'file', '', array('required' => TRUE)) ?></div>
    <button class="archive-back" type="button" data-archive-back><?= official_svg('back') ?>Kembali ke Informasi</button>
   </div>
   <div class="archive-form-actions"><button class="ops-btn ghost" type="button" data-archive-close>Batal</button><button class="ops-btn" type="submit">Unggah</button></div>
  </form>
 </aside>
 <?php endif; ?>
</div>
</div>
<script>
(function(){
 var table=document.getElementById('archive-table'),search=document.getElementById('archive-search'),period=document.getElementById('archive-period'),tabs=[].slice.call(document.querySelectorAll('[data-archive-tab]')),count=document.getElementById('archive-count'),active='all';
 function filter(){var q=(search.value||'').toLocaleLowerCase('id').trim(),p=period.value,n=0;table.querySelectorAll('tbody tr[data-archive-group]').forEach(function(row){var show=(active==='all'||row.dataset.archiveGroup===active)&&(!q||row.dataset.archiveSearch.indexOf(q)>-1)&&(!p||row.dataset.archivePeriod===p);row.hidden=!show;if(show)n++});var empty=table.querySelector('.archive-empty');if(empty)empty.hidden=n!==0;count.textContent=n}
 tabs.forEach(function(tab){tab.addEventListener('click',function(){active=tab.dataset.archiveTab;tabs.forEach(function(x){x.classList.toggle('is-active',x===tab)});filter()})});search.addEventListener('input',filter);period.addEventListener('change',filter);
 var layout=document.getElementById('archive-layout'),card=document.getElementById('archive-upload-card');document.querySelectorAll('[data-archive-open]').forEach(function(x){x.addEventListener('click',function(){if(card){card.hidden=false;layout.classList.remove('form-closed')}})});document.querySelectorAll('[data-archive-close]').forEach(function(x){x.addEventListener('click',function(){if(card){card.hidden=true;layout.classList.add('form-closed')}})});
 var stepButtons=[].slice.call(document.querySelectorAll('[data-archive-step]')),panels=[].slice.call(document.querySelectorAll('[data-archive-panel]'));function step(name){stepButtons.forEach(function(x){x.classList.toggle('is-active',x.dataset.archiveStep===name)});panels.forEach(function(x){x.classList.toggle('is-active',x.dataset.archivePanel===name)})}stepButtons.forEach(function(x){x.addEventListener('click',function(){step(x.dataset.archiveStep)})});document.querySelectorAll('[data-archive-next]').forEach(function(x){x.addEventListener('click',function(){step('file')})});document.querySelectorAll('[data-archive-back]').forEach(function(x){x.addEventListener('click',function(){step('info')})});var form=document.querySelector('.archive-form');if(form)form.addEventListener('submit',function(e){var title=form.querySelector('[name=title]'),file=form.querySelector('input[type=file]');if(title&&!title.value.trim()){e.preventDefault();step('info');title.focus();return}if(file&&!file.files.length){e.preventDefault();step('file');file.focus()}});filter();
})();
</script>
