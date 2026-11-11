<?php $closed = $program['status'] === 'selesai'; ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/bansos') ?>">Bantuan Sosial</a><span>›</span><strong><?= ops_e($program['name']) ?></strong></nav>
<?= ops_hero($program['aid_type'], $program['name'], trim($program['period'] . ' · ' . ops_money($program['unit_value']) . ' per penerima · ' . $program['source'], ' ·'), !$closed && $can('bansos.write') ? array(array('admin/bansos/' . (int) $program['id'] . '/ubah', 'Ubah Program', 'ghost')) : array(), 'heart') ?>
<?= ops_flash() ?>
<?= ops_stats(array(array('Status Program', $program_labels[$program['status']], $program['quota'] ? 'Kuota ' . (int) $program['quota'] : 'Tanpa kuota', 'blue'),
    array('Menunggu', $summary['usulan'] + $summary['layak'], 'Verifikasi / persetujuan', 'orange'), array('Disetujui', $summary['disetujui'], 'Belum disalurkan', 'purple'),
    array('Disalurkan', $summary['disalurkan'], ops_money($value), 'green'))) ?>
<div class="ops-grid">
 <section class="ops-card"><h2>Calon penerima</h2>
  <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Penerima</th><th>Status</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($recipients as $r): ?>
   <tr><td data-label="Penerima"><strong><?= ops_e($r['recipient_name']) ?></strong><small><?= ops_e($r['address']) ?></small><?= $r['proposal_note'] ? '<small>Usulan: ' . ops_e($r['proposal_note']) . '</small>' : '' ?></td>
    <td data-label="Status"><?= ops_badge($r['status'], $labels, $tones) ?><?= $r['distributed_at'] ? '<small>' . ops_date($r['distributed_at'], TRUE) . '</small>' : '' ?><?= $r['proof'] ? '<small><a href="' . site_url('admin/bansos/bukti/' . (int) $r['id']) . '" target="_blank" rel="noopener">Bukti terima</a></small>' : '' ?></td>
    <td data-label="Catatan"><?= ops_e($r['decision_note'] ?: '—') ?></td>
    <td data-label="Aksi"><?php if (!$closed): ?><div class="ops-actions">
     <?php if ($r['status'] === 'usulan' && $can('bansos.write')): ?><form method="post" class="ops-actions"><?= ops_csrf() ?><input type="hidden" name="act" value="verify"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>"><input name="note" placeholder="Catatan verifikasi" aria-label="Catatan verifikasi" maxlength="500"><button class="ops-btn green small" name="result" value="layak">Layak</button><button class="ops-btn red outline small" name="result" value="tidak_layak">Tidak layak</button></form><?php endif; ?>
     <?php if ($r['status'] === 'layak' && $can('bansos.approve')): ?><form method="post" class="ops-actions"><?= ops_csrf() ?><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>"><input name="note" placeholder="Catatan / alasan" aria-label="Catatan keputusan" maxlength="500"><button class="ops-btn green small" name="act" value="approve">Setujui</button><button class="ops-btn red outline small" name="act" value="reject">Tolak</button></form><?php endif; ?>
     <?php if ($r['status'] === 'disetujui' && $can('bansos.write')): ?><details class="ops-more"><summary>Catat penyaluran</summary><form method="post" enctype="multipart/form-data" class="ops-form one" style="margin-top:8px"><?= ops_csrf() ?><input type="hidden" name="act" value="distribute"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>">
      <?= ops_field('proof', 'Bukti penerimaan (privat)', 'file', '', array('accept' => 'image/jpeg,image/png,image/webp,application/pdf')) ?><?= ops_field('note', 'Catatan', 'text', '', array('maxlength' => 500)) ?><button class="ops-btn small" type="submit" onclick="return confirm('Catat bantuan sudah disalurkan? Tidak dapat dicatat dua kali.')">Simpan Penyaluran</button></form></details><?php endif; ?>
    </div><?php endif; ?></td></tr>
  <?php endforeach; if (!$recipients): ?><tr><td colspan="4" class="ops-empty">Belum ada calon penerima.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
 <div>
  <?php if ($can('bansos.approve') && !$closed): ?><section class="ops-card"><h2>Status program</h2>
   <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="program_status"><?php if ($program['status'] === 'draft'): ?><button class="ops-btn green" name="to" value="aktif">Aktifkan Program</button><?php else: ?><button class="ops-btn" name="to" value="selesai" onclick="return confirm('Tutup program? Data tidak dapat diubah lagi.')">Tandai Selesai</button><?php endif; ?></form>
  </section><?php endif; ?>
  <?php if ($can('bansos.write') && !$closed): ?><section class="ops-card"><h2>Usulkan calon penerima</h2>
   <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="add">
    <?= ops_field('family_id', 'Keluarga terverifikasi', 'select', '', array('options' => $families, 'placeholder_option' => 'Pilih keluarga (disarankan)')) ?>
    <?= ops_field('recipient_name', 'Atau nama penerima', 'text', '', array('maxlength' => 140, 'help' => 'Diisi otomatis dari Kepala Keluarga bila memilih keluarga.')) ?>
    <?= ops_field('address', 'Alamat', 'text', '', array('maxlength' => 255)) ?>
    <?= ops_field('proposal_note', 'Alasan usulan (ringkas, tanpa data kesehatan)', 'text', '', array('maxlength' => 500)) ?>
    <div class="ops-form-actions"><button class="ops-btn" type="submit">Tambahkan</button></div>
   </form>
  </section><?php endif; ?>
  <section class="ops-card"><h2>Kriteria</h2><p class="ops-muted" style="white-space:pre-line"><?= ops_e($program['criteria'] ?: 'Belum diisi.') ?></p></section>
 </div>
</div>
</div>
