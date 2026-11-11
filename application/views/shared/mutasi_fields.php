<?php
/* Field formulir mutasi per jenis. Variabel: $type, $family, $values, $relationships, $domicile, $member_edit, $family_edit */
$v = function ($k, $d = '') use ($values) { return $values[$k] ?? $d; };
$active = array();
foreach ($family['members'] as $m) if ($m['status'] === 'aktif') $active[(int) $m['id']] = ($m['fields']['nama_lengkap'] ?? '-') . ' — ' . ($m['fields']['hubungan_keluarga'] ?? '-');
$labels = array('nama_lengkap' => 'Nama lengkap', 'nik' => 'NIK (16 digit)', 'jenis_kelamin' => 'Jenis kelamin', 'tempat_lahir' => 'Tempat lahir', 'tanggal_lahir' => 'Tanggal lahir',
    'agama' => 'Agama', 'pendidikan' => 'Pendidikan', 'pekerjaan' => 'Pekerjaan', 'golongan_darah' => 'Golongan darah', 'hubungan_keluarga' => 'Hubungan keluarga',
    'alamat' => 'Alamat', 'rt' => 'RT', 'rw' => 'RW', 'kelurahan' => 'Kelurahan', 'kecamatan' => 'Kecamatan', 'kota' => 'Kota/Kabupaten', 'provinsi' => 'Provinsi', 'kode_pos' => 'Kode pos');
$gender = array('L' => 'Laki-laki', 'P' => 'Perempuan');
?>
<div class="ops-form">
<?php if (in_array($type, array('pindah', 'meninggal', 'ubah_data'), TRUE)): ?>
 <?= ops_field('member_id', 'Anggota keluarga', 'select', $v('member_id'), array('required' => TRUE, 'options' => $active, 'placeholder_option' => 'Pilih anggota', 'wide' => TRUE)) ?>
<?php endif; ?>
<?php if ($type === 'masuk'): ?>
 <?php
  // Pilihan baku Dukcapil; nilai lama di luar daftar tetap dipertahankan sebagai opsi.
  $keep = function ($list, $cur) {$o = array_combine($list, $list); if ($cur !== '' && !isset($o[$cur])) $o[$cur] = $cur; return $o;};
  $religions = $keep(array('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Kepercayaan terhadap Tuhan YME'), (string) $v('agama'));
  $educations = $keep(array('Tidak/Belum Sekolah', 'Belum Tamat SD/Sederajat', 'Tamat SD/Sederajat', 'SLTP/Sederajat', 'SLTA/Sederajat', 'Diploma I/II', 'Akademi/Diploma III/Sarjana Muda', 'Diploma IV/Strata I', 'Strata II', 'Strata III'), (string) $v('pendidikan'));
  $bloods = $keep(array('A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Tidak Tahu'), (string) $v('golongan_darah'));
 ?>
 <?= ops_field('m_nama_lengkap', $labels['nama_lengkap'], 'text', $v('nama_lengkap'), array('required' => TRUE, 'maxlength' => 140, 'placeholder' => 'Masukkan nama lengkap sesuai dokumen')) ?>
 <?= ops_field('m_nik', $labels['nik'], 'text', $v('nik'), array('required' => TRUE, 'pattern' => '\d{16}', 'maxlength' => 16, 'placeholder' => 'Masukkan 16 digit NIK', 'help' => 'Isi sesuai dokumen resmi. Data tidak diverifikasi otomatis ke Dukcapil.')) ?>
 <?= ops_field('m_jenis_kelamin', $labels['jenis_kelamin'], 'select', $v('jenis_kelamin'), array('required' => TRUE, 'options' => $gender, 'placeholder_option' => 'Pilih jenis kelamin')) ?>
 <?= ops_field('m_hubungan_keluarga', $labels['hubungan_keluarga'], 'select', $v('hubungan_keluarga'), array('required' => TRUE, 'options' => $relationships, 'placeholder_option' => 'Pilih hubungan keluarga')) ?>
 <?= ops_field('m_tempat_lahir', $labels['tempat_lahir'], 'text', $v('tempat_lahir'), array('maxlength' => 100, 'placeholder' => 'Masukkan tempat lahir')) ?>
 <?= ops_field('m_tanggal_lahir', $labels['tanggal_lahir'], 'date', $v('tanggal_lahir'), array('max' => date('Y-m-d'))) ?>
 <?= ops_field('m_agama', $labels['agama'], 'select', $v('agama'), array('options' => $religions, 'placeholder_option' => 'Pilih agama')) ?>
 <?= ops_field('m_pendidikan', $labels['pendidikan'], 'select', $v('pendidikan'), array('options' => $educations, 'placeholder_option' => 'Pilih pendidikan terakhir')) ?>
 <?= ops_field('m_pekerjaan', $labels['pekerjaan'], 'text', $v('pekerjaan'), array('maxlength' => 100, 'placeholder' => 'Masukkan pekerjaan')) ?>
 <?= ops_field('m_golongan_darah', $labels['golongan_darah'], 'select', $v('golongan_darah'), array('options' => $bloods, 'placeholder_option' => 'Pilih golongan darah')) ?>
 <?= ops_field('domicile_status', 'Status domisili', 'select', $v('domicile_status', 'tetap'), array('options' => $domicile)) ?>
<?php elseif ($type === 'pindah'): ?>
 <?= ops_field('alamat_tujuan', 'Alamat tujuan pindah', 'text', $v('alamat_tujuan'), array('required' => TRUE, 'wide' => TRUE, 'maxlength' => 255)) ?>
<?php elseif ($type === 'meninggal'): ?>
 <?= ops_field('tempat_meninggal', 'Tempat meninggal (opsional)', 'text', $v('tempat_meninggal'), array('wide' => TRUE, 'maxlength' => 160, 'help' => 'Status anggota menjadi "meninggal". Riwayat surat dan keuangan tidak dihapus.')) ?>
<?php elseif ($type === 'ubah_data'): ?>
 <p class="ops-note blue ops-field wide">Isi hanya field yang ingin dikoreksi; field kosong tidak diubah. Perubahan NIK/nomor KK tidak melalui menu ini — ajukan ulang data KK agar diverifikasi tambahan.</p>
 <?php foreach ($member_edit as $f): ?>
  <?php if ($f === 'jenis_kelamin'): ?><?= ops_field('m_' . $f, $labels[$f], 'select', $v($f), array('options' => $gender, 'placeholder_option' => 'Tidak diubah')) ?>
  <?php elseif ($f === 'hubungan_keluarga'): ?><?= ops_field('m_' . $f, $labels[$f], 'select', $v($f), array('options' => $relationships, 'placeholder_option' => 'Tidak diubah')) ?>
  <?php elseif ($f === 'tanggal_lahir'): ?><?= ops_field('m_' . $f, $labels[$f], 'date', $v($f)) ?>
  <?php else: ?><?= ops_field('m_' . $f, $labels[$f], 'text', $v($f), array('placeholder' => 'Tidak diubah')) ?><?php endif; ?>
 <?php endforeach; ?>
 <?= ops_field('domicile_status', 'Status domisili', 'select', $v('domicile_status'), array('options' => $domicile, 'placeholder_option' => 'Tidak diubah')) ?>
<?php elseif ($type === 'ubah_alamat'): ?>
 <?php foreach ($family_edit as $f): ?>
  <?= ops_field('f_' . $f, $labels[$f] . ' (saat ini: ' . (($family['fields'][$f] ?? '') ?: '—') . ')', 'text', $v($f), array('placeholder' => 'Tidak diubah', 'wide' => $f === 'alamat')) ?>
 <?php endforeach; ?>
<?php endif; ?>
 <?= ops_field('effective_date', 'Tanggal efektif', 'date', $v('effective_date', date('Y-m-d')), array('required' => TRUE)) ?>
 <?= ops_field('reason', 'Alasan / keterangan', 'textarea', $v('reason'), array('required' => TRUE, 'wide' => TRUE, 'rows' => 3, 'maxlength' => 1000, 'placeholder' => 'Tuliskan alasan atau keterangan mutasi...')) ?>
</div>
