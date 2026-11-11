<?php $open = in_array($item['status'], array('submitted', 'in_review'), TRUE); ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/mutasi') ?>">Mutasi Warga</a><span>›</span><strong><?= ops_e($code) ?></strong></nav>
<?= ops_hero('Kependudukan', $code . ' — ' . ($types[$item['mutation_type']] ?? ''), 'Periksa kesesuaian usulan dengan dokumen pendukung sebelum menyetujui. Persetujuan langsung memperbarui data KK master.', array(array('admin/mutasi', '← Kembali', 'ghost')), 'people') ?>
<?= ops_flash() ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><header><h2>Perubahan yang diajukan</h2><?= ops_badge($item['status'], $labels, $tones) ?></header>
   <?php $mask = TRUE; $this->load->view('shared/mutasi_changes', compact('item', 'family', 'mask')); ?>
  </section>
  <section class="ops-card"><header><div><h2>Anggota keluarga terdaftar</h2><p>KK <?= ops_e(rt_mask_id($family['nomor_kk'] ?? '')) ?> · <?= ops_e($family['fields']['alamat'] ?? '') ?></p></div></header>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Nama</th><th>NIK</th><th>Hubungan</th><th>Domisili</th><th>Status</th></tr></thead><tbody>
   <?php foreach (($family['members'] ?? array()) as $m): ?>
    <tr><td data-label="Nama"><?= ops_e($m['fields']['nama_lengkap'] ?? '-') ?><?= (int) $m['id'] === (int) $item['member_id'] ? ' <b class="ops-req">← mutasi ini</b>' : '' ?></td><td data-label="NIK"><?= ops_e(rt_mask_id($m['nik'])) ?></td><td data-label="Hubungan"><?= ops_e($m['fields']['hubungan_keluarga'] ?? '-') ?></td><td data-label="Domisili"><?= ops_e($domicile[$m['domicile_status'] ?? ''] ?? '—') ?></td><td data-label="Status"><?= ops_badge($m['status'], array('aktif' => 'Aktif', 'pindah' => 'Pindah', 'meninggal' => 'Meninggal'), array('aktif' => 'ok', 'pindah' => 'muted', 'meninggal' => 'muted')) ?></td></tr>
   <?php endforeach; ?>
   </tbody></table></div>
  </section>
 </div>
 <div>
  <section class="ops-card"><h2>Informasi pengajuan</h2>
   <dl class="ops-dl">
    <dt>Pemohon</dt><dd><?= ops_e($item['display_name'] ?: 'Dicatat pengurus: ' . ($item['creator'] ?? '-')) ?></dd>
    <dt>Diajukan</dt><dd><?= ops_date($item['created_at'], TRUE) ?></dd>
    <dt>Tanggal efektif</dt><dd><?= ops_date($item['effective_date']) ?></dd>
    <dt>Alasan</dt><dd><?= ops_e($item['reason']) ?></dd>
    <?php if ($item['reviewed_at']): ?><dt>Diputuskan</dt><dd><?= ops_e($item['reviewer'] ?? '-') ?> · <?= ops_date($item['reviewed_at'], TRUE) ?></dd><?php endif; ?>
    <?php if ($item['review_note']): ?><dt>Catatan</dt><dd><?= ops_e($item['review_note']) ?></dd><?php endif; ?>
    <?php if ($item['applied_at']): ?><dt>Diterapkan</dt><dd><?= ops_date($item['applied_at'], TRUE) ?></dd><?php endif; ?>
   </dl>
  </section>
  <?php if ($open && $can('mutasi.review')): ?>
  <section class="ops-card"><h2>Keputusan</h2>
   <form method="post" class="ops-form one"><?= ops_csrf() ?>
    <?= ops_field('review_note', 'Catatan / alasan', 'textarea', '', array('rows' => 3, 'maxlength' => 1000, 'help' => 'Wajib untuk "Perlu perbaikan" dan "Tolak".')) ?>
    <div class="ops-actions">
     <?php if ($item['status'] === 'submitted'): ?><button class="ops-btn ghost" name="decision" value="in_review">Tandai Diperiksa</button><?php endif; ?>
     <button class="ops-btn outline" name="decision" value="revision_requested">Minta Perbaikan</button>
     <button class="ops-btn red outline" name="decision" value="rejected" onclick="return confirm('Tolak mutasi ini?')">Tolak</button>
     <?php if ($can('mutasi.approve')): ?><button class="ops-btn green" name="decision" value="approved" onclick="return confirm('Setujui dan terapkan perubahan ke data KK master?')">Setujui &amp; Terapkan</button><?php endif; ?>
    </div>
   </form>
  </section>
  <?php endif; ?>
 </div>
</div>
</div>
