<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/arsip') ?>">Arsip</a><span>›</span><strong><?= ops_e($doc['title']) ?></strong></nav>
<?= ops_hero($categories[$doc['category']] ?? 'Dokumen', $doc['title'], ($doc['doc_number'] ?: 'Tanpa nomor') . ' · versi ' . (int) $doc['current_version'], array(array('admin/arsip/' . (int) $doc['id'] . '/unduh', 'Unduh Versi Terbaru')), 'doc') ?>
<?= ops_flash() ?>
<div class="ops-grid">
 <section class="ops-card"><h2>Riwayat versi</h2>
  <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Versi</th><th>Berkas</th><th>Checksum (SHA-256)</th><th>Diunggah</th><th></th></tr></thead><tbody>
  <?php foreach ($versions as $v): ?><tr><td data-label="Versi">v<?= (int) $v['version'] ?></td><td data-label="Berkas"><?= ops_e($v['original_name']) ?><small><?= number_format($v['size_bytes'] / 1024, 0, ',', '.') ?> KB<?= $v['note'] ? ' · ' . ops_e($v['note']) : '' ?></small></td>
   <td data-label="Checksum"><code style="font-size:11px;overflow-wrap:anywhere"><?= ops_e(substr($v['checksum'], 0, 16)) ?>…</code></td><td data-label="Diunggah"><?= ops_e($v['username'] ?: '-') ?><small><?= ops_date($v['created_at'], TRUE) ?></small></td>
   <td data-label=""><a href="<?= site_url('admin/arsip/' . (int) $doc['id'] . '/unduh/' . (int) $v['version']) ?>">Unduh</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
 </section>
 <div>
  <section class="ops-card"><h2>Metadata</h2><dl class="ops-dl"><dt>Kerahasiaan</dt><dd><?= ops_e($levels[$doc['confidentiality']] ?? $doc['confidentiality']) ?></dd><dt>Tanggal</dt><dd><?= ops_date($doc['doc_date']) ?></dd><dt>Periode</dt><dd><?= ops_e($doc['period'] ?: '—') ?></dd><dt>Retensi</dt><dd><?= $doc['retention_until'] ? ops_date($doc['retention_until']) . ($doc['retention_until'] < date('Y-m-d') ? ' (lewat masa simpan — tinjau)' : '') : 'Tidak ditentukan' ?></dd></dl></section>
  <?php if ($can('arsip.write')): ?>
  <section class="ops-card"><h2>Unggah versi baru</h2><form method="post" enctype="multipart/form-data" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="version"><?= ops_field('document', 'Berkas', 'file', '', array('required' => TRUE)) ?><?= ops_field('note', 'Catatan perubahan', 'text', '', array('maxlength' => 255)) ?><button class="ops-btn" type="submit">Unggah Versi</button></form></section>
  <section class="ops-card"><h2>Hapus dokumen</h2><form method="post" class="ops-form one" onsubmit="return confirm('Hapus dokumen ini dari daftar? (soft delete)')"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><?= ops_field('reason', 'Alasan', 'text', '', array('required' => TRUE, 'maxlength' => 255)) ?><button class="ops-btn red outline" type="submit">Hapus (soft delete)</button></form></section>
  <?php endif; ?>
 </div>
</div>
</div>
