<?php
$f = $family['fields'];
$names = array();
foreach ($family['members'] as $m) $names[(int) $m['id']] = $m['fields']['nama_lengkap'] ?? '-';
$field_labels = array('nama_lengkap' => 'nama', 'nik' => 'NIK', 'jenis_kelamin' => 'jenis kelamin', 'tempat_lahir' => 'tempat lahir', 'tanggal_lahir' => 'tanggal lahir',
    'pendidikan' => 'pendidikan', 'pekerjaan' => 'pekerjaan', 'golongan_darah' => 'golongan darah', 'hubungan_keluarga' => 'hubungan', 'domisili' => 'status domisili');
$actions = array(array('admin/penduduk?tampil=keluarga', '← Kembali', 'ghost'));
if ($can('mutasi.review')) $actions[] = array('admin/mutasi/baru?keluarga=' . (int) $family['id'], '+ Catat Mutasi');
?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/penduduk') ?>">Data Penduduk</a><span>›</span><a href="<?= site_url('admin/penduduk?tampil=keluarga') ?>">Keluarga</a><span>›</span><strong>KK <?= ops_e(rt_mask_id($family['nomor_kk'])) ?></strong></nav>
<?= ops_hero('Kependudukan', 'KK ' . rt_mask_id($family['nomor_kk']), ($f['alamat'] ?? '') !== '' ? $f['alamat'] : 'Alamat belum diisi', $actions, 'people') ?>
<?= ops_flash() ?>

<?php if ($review): ?>
<div class="ops-note"><strong>Data perlu dilengkapi.</strong>
 <?php foreach ($review as $r): ?><br><?= ops_e($r['name']) ?>: <?= ops_e(implode(', ', array_map(function ($k) use ($field_labels) {return $field_labels[$k] ?? $k;}, $r['fields']))) ?><?php endforeach; ?>
 <br><small>Lengkapi melalui <em>Catat Mutasi → Koreksi Data</em> atau minta warga mengajukan koreksi dari portal, supaya perubahan tetap tercatat.</small>
</div>
<?php endif; ?>

<div class="ops-grid">
 <div>
  <section class="ops-card"><header><div><h2>Anggota keluarga</h2><p>NIK dimasking. Anggota pindah/meninggal tetap ditampilkan sebagai histori.</p></div></header>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Nama</th><th>NIK</th><th>L/P</th><th>Lahir</th><th>Hubungan</th><th>Pekerjaan</th><th>Domisili</th><th>Status</th></tr></thead><tbody>
   <?php foreach ($family['members'] as $m): $mf = $m['fields']; ?>
    <tr>
     <td data-label="Nama"><strong><?= ops_e($mf['nama_lengkap'] ?? '-') ?></strong></td>
     <td data-label="NIK"><?= ops_e(rt_mask_id($m['nik'])) ?></td>
     <td data-label="L/P"><?= ops_e(($mf['jenis_kelamin'] ?? '') ?: '—') ?></td>
     <td data-label="Lahir"><?= ops_e(($mf['tempat_lahir'] ?? '') ?: '—') ?><small><?= ops_date($mf['tanggal_lahir'] ?? '') ?><?= $m['age'] !== NULL ? ' · ' . (int) $m['age'] . ' th' : '' ?></small></td>
     <td data-label="Hubungan"><?= ops_e(($mf['hubungan_keluarga'] ?? '') ?: '—') ?></td>
     <td data-label="Pekerjaan"><?= ops_e(($mf['pekerjaan'] ?? '') ?: '—') ?><?php if (($mf['pendidikan'] ?? '') !== ''): ?><small><?= ops_e($mf['pendidikan']) ?></small><?php endif; ?></td>
     <td data-label="Domisili"><?= !empty($m['domicile_status']) ? ops_e($domicile[$m['domicile_status']] ?? $m['domicile_status']) : '<span class="ops-muted">Belum diisi</span>' ?><?php if (!empty($m['resident_since'])): ?><small>sejak <?= ops_date($m['resident_since']) ?></small><?php endif; ?></td>
     <td data-label="Status"><?= ops_badge($m['status'], $status_labels, $status_tones) ?><?php if ($m['status'] !== 'aktif' && !empty($m['status_changed_at'])): ?><small><?= ops_date($m['status_changed_at']) ?></small><?php endif; ?></td>
    </tr>
   <?php endforeach; if (!$family['members']): ?><tr><td colspan="8" class="ops-empty">Belum ada anggota tercatat.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>

  <section class="ops-card"><header><div><h2>Riwayat mutasi</h2><p>Semua pengajuan mutasi untuk KK ini, terbaru di atas.</p></div></header>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Kode</th><th>Jenis</th><th>Anggota</th><th>Efektif</th><th>Status</th><th></th></tr></thead><tbody>
   <?php foreach ($mutations as $mu): ?>
    <tr>
     <td data-label="Kode"><strong>MUT-<?= str_pad((int) $mu['id'], 6, '0', STR_PAD_LEFT) ?></strong><small><?= ops_date($mu['created_at']) ?></small></td>
     <td data-label="Jenis"><?= ops_e($mut_types[$mu['mutation_type']] ?? $mu['mutation_type']) ?></td>
     <td data-label="Anggota"><?= ops_e($mu['member_id'] ? ($names[(int) $mu['member_id']] ?? '—') : 'Data keluarga / anggota baru') ?></td>
     <td data-label="Efektif"><?= ops_date($mu['effective_date']) ?></td>
     <td data-label="Status"><?= ops_badge($mu['status'], $mut_labels, $mut_tones) ?></td>
     <td data-label=""><?php if ($can('mutasi.read')): ?><a href="<?= site_url('admin/mutasi/' . (int) $mu['id']) ?>">Lihat</a><?php endif; ?></td>
    </tr>
   <?php endforeach; if (!$mutations): ?><tr><td colspan="6" class="ops-empty">Belum ada mutasi untuk KK ini.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>
 </div>

 <div>
  <section class="ops-card"><h2>Data KK</h2>
   <dl class="ops-dl">
    <dt>Nomor KK</dt><dd><?= ops_e(rt_mask_id($family['nomor_kk'])) ?></dd>
    <dt>Alamat</dt><dd><?= ops_e(($f['alamat'] ?? '') ?: '—') ?></dd>
    <dt>RT / RW</dt><dd><?= ops_e(($f['rt'] ?? '') ?: '-') ?> / <?= ops_e(($f['rw'] ?? '') ?: '-') ?></dd>
    <dt>Kelurahan</dt><dd><?= ops_e(($f['kelurahan'] ?? '') ?: '—') ?></dd>
    <dt>Kecamatan</dt><dd><?= ops_e(($f['kecamatan'] ?? '') ?: '—') ?></dd>
    <dt>Kota / Kab.</dt><dd><?= ops_e(($f['kota'] ?? '') ?: '—') ?></dd>
    <dt>Provinsi</dt><dd><?= ops_e(($f['provinsi'] ?? '') ?: '—') ?></dd>
    <dt>Kode pos</dt><dd><?= ops_e(($f['kode_pos'] ?? '') ?: '—') ?></dd>
    <dt>Terakhir diperbarui</dt><dd><?= ops_date($family['updated_at'], TRUE) ?></dd>
   </dl>
  </section>
  <section class="ops-card"><h2>Akun pengaju</h2>
   <?php if ($owner): ?>
    <dl class="ops-dl">
     <dt>Nama akun</dt><dd><?= ops_e($owner['display_name']) ?></dd>
     <dt>Username</dt><dd><?= ops_e($owner['username']) ?></dd>
     <dt>Status akun</dt><dd><?= (int) $owner['is_active'] ? 'Aktif' : 'Nonaktif' ?></dd>
    </dl>
    <?php if ($can_view_account): ?><p><a class="ops-btn ghost small" href="<?= site_url('admin/citizen/' . (int) $owner['id']) ?>">Buka akun warga</a></p><?php endif; ?>
   <?php else: ?><p class="ops-muted">Akun warga pengaju tidak ditemukan.</p><?php endif; ?>
  </section>
 </div>
</div>
</div>
