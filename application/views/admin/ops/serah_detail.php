<?php $final = $s['status'] === 'final'; $labels = array('kk_terverifikasi' => 'KK terverifikasi', 'anggota_aktif' => 'Anggota keluarga aktif', 'akun_warga_aktif' => 'Akun warga aktif', 'saldo_kas' => 'Saldo kas tercatat',
    'tunggakan_tagihan' => 'Tagihan menunggak', 'tunggakan_nilai' => 'Nilai tunggakan', 'aset_jenis' => 'Jenis aset/barang', 'aset_dipinjam' => 'Peminjaman aktif', 'dokumen_arsip' => 'Dokumen arsip',
    'pengaduan_terbuka' => 'Pengaduan terbuka', 'mutasi_menunggu' => 'Mutasi menunggu', 'backup_terakhir' => 'Backup terakhir', 'dibuat' => 'Snapshot dibuat'); ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/serah-terima') ?>">Serah Terima</a><span>›</span><strong><?= ops_e($s['title']) ?></strong></nav>
<?= ops_hero('Berita Acara', $s['title'], ($s['outgoing'] ?: '-') . ' → ' . ($s['incoming'] ?: '-') . ($s['period_label'] ? ' · ' . $s['period_label'] : ''), array(), 'org') ?>
<?= ops_flash() ?>
<p class="ops-note <?= $intact ? 'blue' : 'red' ?>">Hash snapshot: <code><?= ops_e(substr((string) $s['snapshot_hash'], 0, 24)) ?>…</code> — <?= $intact ? 'utuh, sesuai saat dibuat.' : 'TIDAK COCOK, snapshot mungkin diubah di luar aplikasi.' ?><?= $final ? ' Berita acara final dan terkunci.' : '' ?></p>
<div class="ops-grid">
 <div>
  <section class="ops-card"><h2>Snapshot data (agregat)</h2><dl class="ops-dl">
   <?php foreach ($snapshot as $k => $v): if ($k === 'program_berjalan') continue; ?><dt><?= ops_e($labels[$k] ?? $k) ?></dt><dd><?= in_array($k, array('saldo_kas', 'tunggakan_nilai'), TRUE) ? ops_money($v) : ops_e($v === NULL ? '—' : $v) ?></dd><?php endforeach; ?>
   <dt>Program berjalan</dt><dd><?= ops_e(implode("\n", $snapshot['program_berjalan'] ?? array()) ?: '—') ?></dd></dl></section>
  <section class="ops-card"><h2>Checklist pemeriksaan</h2>
   <form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="checklist">
    <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Item</th><th>Hasil</th><th>Catatan</th></tr></thead><tbody>
    <?php foreach ($checklist as $k => $row): ?><tr><td data-label="Item"><?= ops_e($row['label']) ?></td>
     <td data-label="Hasil"><?php if ($final || !$can('serahterima.write')): ?><?= ops_badge($row['status'], array('belum' => 'Belum', 'sesuai' => 'Sesuai', 'pengecualian' => 'Pengecualian'), array('belum' => 'muted', 'sesuai' => 'ok', 'pengecualian' => 'pending')) ?><?php else: ?><select name="status[<?= ops_e($k) ?>]" aria-label="Hasil <?= ops_e($row['label']) ?>"><?= ops_options(array('belum' => 'Belum diperiksa', 'sesuai' => 'Sesuai', 'pengecualian' => 'Pengecualian'), $row['status']) ?></select><?php endif; ?></td>
     <td data-label="Catatan"><?php if ($final || !$can('serahterima.write')): ?><?= ops_e($row['note'] ?: '—') ?><?php else: ?><input name="note[<?= ops_e($k) ?>]" value="<?= ops_e($row['note']) ?>" maxlength="300" aria-label="Catatan <?= ops_e($row['label']) ?>" style="width:100%"><?php endif; ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php if (!$final && $can('serahterima.write')): ?><div class="ops-form one" style="margin-top:10px"><?= ops_field('exceptions', 'Pengecualian / catatan berita acara', 'textarea', (string) $s['exceptions'], array('rows' => 3)) ?><div class="ops-form-actions"><button class="ops-btn" type="submit">Simpan Checklist</button></div></div>
    <?php elseif ($s['exceptions']): ?><p style="white-space:pre-line"><strong>Pengecualian:</strong> <?= ops_e($s['exceptions']) ?></p><?php endif; ?>
   </form>
  </section>
 </div>
 <div>
  <section class="ops-card"><h2>Persetujuan</h2>
   <ul class="ops-timeline"><?php foreach ($signoffs as $g): ?><li><strong><?= ops_e($g['username']) ?></strong> — pihak <?= $g['side'] === 'lama' ? 'lama' : 'baru' ?> (<?= ops_e($g['role_label']) ?>)<small><?= ops_date($g['signed_at'], TRUE) ?></small><?php if ($g['note']): ?><p><?= ops_e($g['note']) ?></p><?php endif; ?></li><?php endforeach; ?></ul>
   <?php if (!$signoffs): ?><p class="ops-muted">Belum ada persetujuan.</p><?php endif; ?>
   <?php if (!$final && $this->session->userdata('admin_role') !== 'auditor'): ?>
   <form method="post" class="ops-form one" style="margin-top:10px"><?= ops_csrf() ?><input type="hidden" name="act" value="sign">
    <?= ops_field('side', 'Saya menyetujui sebagai', 'select', 'lama', array('options' => array('lama' => 'Pengurus lama (menyerahkan)', 'baru' => 'Pengurus baru (menerima)'))) ?><?= ops_field('note', 'Catatan', 'text', '', array('maxlength' => 255)) ?>
    <button class="ops-btn ghost" type="submit">Setujui Berita Acara</button></form>
   <?php if ($can('serahterima.finalize')): ?><form method="post" style="margin-top:10px"><?= ops_csrf() ?><input type="hidden" name="act" value="finalize"><button class="ops-btn green" type="submit" onclick="return confirm('Finalkan berita acara? Tidak dapat diubah lagi.')">Finalkan</button></form><?php endif; ?>
   <?php endif; ?>
  </section>
  <section class="ops-card"><h2>Pengalihan akses</h2><p class="ops-muted">Setelah final, cabut akun pengurus lama dan reset password akun bersama di menu Akun Pengurus. Akun yang dicabut tidak dapat login dan sesi aktifnya berakhir.</p><?php if($can('akun.manage')): ?><a class="ops-btn ghost small" href="<?= site_url('admin/akun') ?>">Buka Akun Pengurus</a><?php endif; ?></section>
 </div>
</div>
</div>
