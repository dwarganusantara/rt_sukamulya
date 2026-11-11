<?php
/* Ringkasan perubahan mutasi: nilai sebelum vs usulan. Variabel: $item, $family, $mask (bool) */
$prop = json_decode($item['proposed_values_json'], TRUE) ?: array();
$old = json_decode((string) $item['old_values_json'], TRUE) ?: array();
$labels = array('nama_lengkap' => 'Nama lengkap', 'nik' => 'NIK', 'jenis_kelamin' => 'Jenis kelamin', 'tempat_lahir' => 'Tempat lahir', 'tanggal_lahir' => 'Tanggal lahir',
    'agama' => 'Agama', 'pendidikan' => 'Pendidikan', 'pekerjaan' => 'Pekerjaan', 'golongan_darah' => 'Golongan darah', 'hubungan_keluarga' => 'Hubungan keluarga',
    'domicile_status' => 'Status domisili', 'alamat_tujuan' => 'Alamat tujuan', 'tempat_meninggal' => 'Tempat meninggal',
    'alamat' => 'Alamat', 'rt' => 'RT', 'rw' => 'RW', 'kelurahan' => 'Kelurahan', 'kecamatan' => 'Kecamatan', 'kota' => 'Kota/Kabupaten', 'provinsi' => 'Provinsi', 'kode_pos' => 'Kode pos');
$current = array();
if ($item['status'] === 'approved') {
    $current = ($old['fields'] ?? array()) + ($old['keluarga'] ?? array());
    if (isset($old['domicile_status'])) $current['domicile_status'] = $old['domicile_status'];
} elseif ($item['mutation_type'] === 'ubah_alamat') {
    $current = $family['fields'] ?? array();
} elseif ($item['member_id'] && $family) {
    foreach ($family['members'] as $m) if ((int) $m['id'] === (int) $item['member_id']) {$current = $m['fields']; $current['domicile_status'] = $m['domicile_status'] ?? '';}
}
?>
<div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Data</th><th><?= $item['status'] === 'approved' ? 'Sebelum' : 'Saat ini' ?></th><th>Usulan</th></tr></thead><tbody>
<?php foreach ($prop as $k => $val): if (!isset($labels[$k])) continue; $show = function ($x) use ($k, $mask) { $x = (string) $x; return $k === 'nik' && $mask ? rt_mask_id($x) : ($k === 'jenis_kelamin' ? (array('L' => 'Laki-laki', 'P' => 'Perempuan')[$x] ?? $x) : $x); }; ?>
 <tr><td data-label="Data"><strong><?= ops_e($labels[$k]) ?></strong></td><td data-label="Sebelum"><?= ops_e($show($current[$k] ?? '') ?: '—') ?></td><td data-label="Usulan"><?= ops_e($show($val) ?: '—') ?></td></tr>
<?php endforeach; ?>
<?php if (in_array($item['mutation_type'], array('pindah', 'meninggal'), TRUE)): ?>
 <tr><td data-label="Data"><strong>Status anggota</strong></td><td data-label="Sebelum"><?= ops_e(ucfirst($old['status'] ?? 'aktif')) ?></td><td data-label="Usulan"><?= $item['mutation_type'] === 'pindah' ? 'Pindah' : 'Meninggal' ?></td></tr>
<?php endif; ?>
</tbody></table></div>
