<?php $last_ok = NULL; foreach ($runs as $r) if ($r['status'] === 'success') {$last_ok = $r; break;} $stale = !$last_ok || strtotime($last_ok['created_at']) < strtotime('-7 days'); $last_failed = $runs && $runs[0]['status'] === 'failed'; ?>
<div class="ops ops-backup">
<?= ops_hero('Pengaturan Sistem', 'Backup Data', 'Buat salinan (backup) data aplikasi secara berkala untuk menjaga keamanan data.', array(), 'database') ?>
<?= ops_flash() ?>
<?php if ($last_failed): ?><p class="ops-note red"><strong>Backup terakhir gagal:</strong> <?= ops_e($runs[0]['message']) ?></p><?php endif; ?>

<div class="ops-grid">
 <section class="ops-card"><h2>Riwayat backup</h2>
  <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Waktu</th><th>Jenis</th><th>Status</th><th class="num">Ukuran</th><th>Verifikasi</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($runs as $r): ?><tr><td data-label="Waktu"><?= ops_date($r['created_at'], TRUE) ?><small><?= $r['triggered_by'] ? 'Manual' : 'Terjadwal/CLI' ?></small></td><td data-label="Jenis"><?= $r['kind'] === 'files' ? 'Berkas' : 'Database' ?></td>
   <td data-label="Status"><?= ops_badge($r['status'], array('success' => 'Berhasil', 'failed' => 'Gagal'), array('success' => 'ok', 'failed' => 'bad')) ?><small><?= ops_e($r['message']) ?></small></td>
   <td data-label="Ukuran" class="num"><?= $r['size_bytes'] ? number_format($r['size_bytes'] / 1024, 0, ',', '.') . ' KB' : '—' ?></td><td data-label="Verifikasi"><?= $r['verified_at'] ? ops_date($r['verified_at'], TRUE) : '—' ?></td>
   <td data-label="Aksi"><?php if ($r['file_name']): ?><div class="ops-actions"><form method="post"><?= ops_csrf() ?><input type="hidden" name="run_id" value="<?= (int) $r['id'] ?>"><button class="ops-btn ghost small" name="act" value="verify">Verifikasi</button></form><form method="post"><?= ops_csrf() ?><input type="hidden" name="run_id" value="<?= (int) $r['id'] ?>"><button class="ops-btn ghost small" name="act" value="download">Unduh</button></form></div><?php endif; ?></td></tr><?php endforeach; ?>
  <?php if (!$runs): ?><tr><td colspan="6" class="ops-empty"><?= ops_empty_state('Belum ada data backup','Lakukan backup pertama untuk membuat salinan data.','database',NULL,'Jalankan Backup Sekarang','backup-run') ?></td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
 <div>
  <section class="ops-card"><h2>Backup Sekarang</h2><p class="ops-muted">Buat salinan data aplikasi saat ini.</p>
   <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="act" value="run">
    <details class="ops-field-advanced"><summary>Opsi Backup</summary><?php if ($zip): ?><?= ops_field('with_files', 'Sertakan berkas (uploads & penyimpanan privat)', 'checkbox', 0) ?><?php endif; ?></details>
    <button class="ops-btn" id="backup-run" type="submit"><?= official_svg('database') ?>Jalankan Backup</button></form>
   <?php if ($stale): ?><p class="ops-note">Belum ada backup berhasil dalam 7 hari terakhir.</p><?php endif; ?><p class="ops-muted">Retensi: 14 backup terbaru per jenis disimpan; berkas lebih lama dihapus otomatis (catatan tetap ada).</p>
  </section>
  <section class="ops-card"><details class="ops-guide-panel"><summary>Jadwal Otomatis</summary><p class="ops-muted">Lihat cara menjadwalkan backup di server.</p><p class="ops-muted">Daftarkan perintah berikut di Windows Task Scheduler atau cron hosting (misal setiap hari pukul 02.00):</p>
   <pre style="white-space:pre-wrap;font-size:12px;background:#f8fafc;padding:10px;border-radius:10px;border:1px solid #edf0f4">php <?= ops_e(FCPATH) ?>index.php sistem cli_backup
php <?= ops_e(FCPATH) ?>index.php sistem cli_backup files</pre></details></section>
  <section class="ops-card"><details class="ops-guide-panel"><summary>Restore / Pemulihan</summary><p class="ops-muted">Lihat panduan mengembalikan salinan data di lingkungan terpisah.</p><p class="ops-muted">Restore tidak dijalankan dari aplikasi untuk mencegah penimpaan data produksi tanpa sengaja. Prosedur (wajib otorisasi Ketua RT dan backup terbaru sebelum restore) ada di <code>docs/RT_RW_DIGITAL_IMPLEMENTASI.md</code>. Uji restore di database salinan secara berkala.</p></details></section>
 </div>
</div>
</div>
