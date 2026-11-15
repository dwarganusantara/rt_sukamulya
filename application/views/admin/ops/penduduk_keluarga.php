<?php
$f = $family['fields'];
$names = array();
foreach ($family['members'] as $m) $names[(int) $m['id']] = $m['fields']['nama_lengkap'] ?? '-';
$actions = array(array('admin/penduduk?tampil=keluarga', '← Kembali', 'ghost'));
if ($can('mutasi.review')) $actions[] = array('admin/mutasi/baru?keluarga=' . (int) $family['id'], '+ Catat Mutasi');
?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/penduduk') ?>">Data Penduduk</a><span>›</span><a href="<?= site_url('admin/penduduk?tampil=keluarga') ?>">Keluarga</a><span>›</span><strong>KK <?= ops_e(rt_mask_id($family['nomor_kk'])) ?></strong></nav>
<?= ops_hero('Kependudukan', 'KK ' . rt_mask_id($family['nomor_kk']), ($f['alamat'] ?? '') !== '' ? $f['alamat'] : 'Alamat belum diisi', $actions, 'people') ?>
<?= ops_flash() ?>


<div class="kk-detail-main">
 <div>
  <section class="ops-card"><header><div><h2>Anggota keluarga</h2><p>NIK dimasking. Anggota pindah/meninggal tetap ditampilkan sebagai histori.</p></div></header>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Nama</th><th>NIK</th><th>L/P</th><th>Lahir</th><th>Hubungan</th><th>Pekerjaan</th><th>Domisili</th><th>Status</th><?php if ($links_ready): ?><th>Akun</th><?php endif; ?></tr></thead><tbody>
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
     <?php if ($links_ready): $acc = $account_of[(int) $m['id']] ?? NULL; ?><td data-label="Akun"><?= $acc ? ($can_view_account ? ops_e($acc['username']) : 'Tertaut') . '<small>' . ($acc['verification_source'] === 'aktivasi_undangan' ? 'via aktivasi' : 'diverifikasi pengurus') . '</small>' : '<span class="ops-muted">—</span>' ?></td><?php endif; ?>
    </tr>
   <?php endforeach; if (!$family['members']): ?><tr><td colspan="<?= $links_ready ? 9 : 8 ?>" class="ops-empty">Belum ada anggota tercatat.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>

  <?php if ($links_ready && $can_view_account): ?>
  <section class="ops-card"><header><div><h2>Akun Portal Warga</h2><p>Satu akun hanya untuk satu anggota, dan satu anggota hanya boleh punya satu akun.</p></div></header>
   <?php if (!$owner): ?>
    <p class="ops-note">KK ini belum memiliki akun portal warga<?= $invite ? ' (undangan aktivasi masih berlaku sampai ' . ops_date($invite['expires_at']) . ')' : '' ?>.</p>
   <?php else: ?>
    <p>Pemilik akun: <strong><?= ops_e($owner['display_name']) ?></strong> (<?= ops_e($owner['username']) ?>)<?= (int) $owner['is_active'] === 1 ? '' : ' · <span class="ops-muted">nonaktif</span>' ?></p>
    <?php if ($owner_member): ?>
     <p>Tertaut ke anggota: <strong><?= ops_e($names[$owner_member] ?? '-') ?></strong></p>
     <?php if ($can_link): ?><form method="post" action="<?= site_url('admin/penduduk/keluarga/' . (int) $family['id'] . '/tautkan-akun') ?>" onsubmit="return confirm('Lepas tautan akun dari anggota ini?')"><?= ops_csrf() ?><input type="hidden" name="act" value="lepas"><button class="ops-btn ghost" type="submit">Lepas tautan</button></form><?php endif; ?>
    <?php elseif ($owner_linked_elsewhere): ?>
     <p class="ops-note">Akun ini sudah tertaut ke anggota KK lain. Periksa riwayat mutasi sebelum mengubahnya.</p>
    <?php else: ?>
     <p class="ops-note">Akun ini belum ditautkan ke anggota tertentu.</p>
     <?php if ($can_link): ?>
     <form method="post" class="ops-form one" action="<?= site_url('admin/penduduk/keluarga/' . (int) $family['id'] . '/tautkan-akun') ?>"><?= ops_csrf() ?>
      <label>Anggota pemilik akun<select name="member_id" required><option value="">Pilih anggota…</option>
       <?php foreach ($family['members'] as $m): if ($m['status'] !== 'aktif' || ($m['age'] !== NULL && $m['age'] < 17) || isset($account_of[(int) $m['id']])) continue; ?>
        <option value="<?= (int) $m['id'] ?>"><?= ops_e(($m['fields']['nama_lengkap'] ?? '-') . ' — ' . (($m['fields']['hubungan_keluarga'] ?? '') ?: 'anggota')) ?></option>
       <?php endforeach; ?>
      </select></label>
      <label><input type="checkbox" name="identity_checked" value="1" required> Saya sudah memeriksa bahwa pemilik akun adalah anggota ini (KTP/telepon/tatap muka).</label>
      <button class="ops-btn" type="submit">Tautkan akun</button>
     </form>
     <?php endif; ?>
    <?php endif; ?>
   <?php endif; ?>
  </section>
  <?php endif; ?>

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
</div>
</div>
