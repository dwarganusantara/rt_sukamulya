<?php
$root=dirname(__DIR__,2);
$p=$root.'/application/views/admin/ops/fields.php';$s=file_get_contents($p);
$s=str_replace('<th>Label</th><th>Tipe</th><th>Aturan</th><th class="num">Terisi</th>','<th>Label Field</th><th>Tipe Isian</th><th>Wajib</th><th class="num">Urutan</th>',$s);
$s=str_replace("<?= (int) \$f['is_required'] ? 'Wajib' : 'Opsional' ?>", "<span class=\"ops-badge <?= (int)\$f['is_required']?'ops-req-badge':'ops-more-muted' ?>\"><?= (int)\$f['is_required']?'Ya':'Tidak' ?></span>",$s);
$s=str_replace("<?= (int) \$f['filled'] ?>","<?= (int) \$f['sort_order'] ?>",$s);
$s=str_replace('<h2><?= $editing ? \'Ubah Field\' : \'Field Baru\' ?></h2>','<h2><?= $editing ? \'Ubah Field\' : \'Tambah Field\' ?></h2>',$s);
$s=str_replace("ops_field('label', 'Label'","ops_field('label', 'Label Field'",$s);
$s=str_replace('<div class="ops-actions"><a href=', '<details class="ops-row-menu"><summary aria-label="Aksi field">&bull;&bull;&bull;</summary><nav><a href=', $s);
$s=str_replace('</button></form></div></td>','</button></form></nav></details></td>',$s);
file_put_contents($p,$s);
$p=$root.'/application/views/admin/ops/serah_index.php';$s=file_get_contents($p);
$s=str_replace('<th>Sesi</th><th>Periode</th><th>Lama ? Baru</th>','<th>Periode</th><th>Pengurus Lama</th><th>Pengurus Baru</th>',$s);
$start=strpos($s,'<tr data-period=');$end=strpos($s,'<td data-label="Status">',$start);
if($start!==FALSE&&$end!==FALSE){$replace=<<<'HTML'
<tr data-period="<?= ops_e($s['period_label']) ?>"><td data-label="Periode"><strong><?= ops_e($s['period_label']?:'Belum diisi') ?></strong><small><?= ops_e($s['title']) ?></small></td><td data-label="Pengurus Lama"><?= ops_e($s['outgoing']?:'—') ?></td><td data-label="Pengurus Baru"><?= ops_e($s['incoming']?:'—') ?></td>
HTML;
$s=substr($s,0,$start).$replace.substr($s,$end);}
file_put_contents($p,$s);
$p=$root.'/application/views/admin/ops/backup.php';$s=file_get_contents($p);
$s=str_replace('<h2>Backup sekarang</h2>','<h2>Backup Sekarang</h2><p class="ops-muted">Buat salinan data aplikasi saat ini.</p>',$s);
$s=str_replace('<summary>Jadwal Otomatis</summary>','<summary>Jadwal Otomatis</summary><p class="ops-muted">Lihat cara menjadwalkan backup di server.</p>',$s);
$s=str_replace('<summary>Panduan Pemulihan</summary>','<summary>Restore / Pemulihan</summary><p class="ops-muted">Lihat panduan mengembalikan salinan data di lingkungan terpisah.</p>',$s);
file_put_contents($p,$s);
