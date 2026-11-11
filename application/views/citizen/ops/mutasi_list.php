<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Data Keluarga', 'warga/keluarga'), array('Mutasi Data', '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('people') ?></span><div><h1>Mutasi Data Keluarga</h1><p>Laporkan anggota masuk/lahir, pindah, meninggal, koreksi data, atau perubahan alamat. Data keluarga diperbarui setelah disetujui pengurus.</p></div></section>
 <?php $this->load->view('citizen/ops/_flash'); ?>
 <?php if (!$family): ?>
  <section class="cdx-card"><p class="ops-note">Data Kartu Keluarga Anda belum terverifikasi. Ajukan data KK terlebih dahulu; mutasi hanya bisa diajukan untuk keluarga terverifikasi.</p><a class="cdx-report" href="<?= site_url('warga/keluarga') ?>"><?= official_svg('people') ?>Buka Data Keluarga</a></section>
 <?php else: ?>
  <section class="cdx-card">
   <header class="cdx-head"><span class="orange"><?= official_svg('plus') ?></span><div><h2>Ajukan mutasi baru</h2><p>Pilih jenis perubahan yang ingin dilaporkan.</p></div></header>
   <div class="cpo-types">
    <?php $hints = array('masuk' => 'Kelahiran atau anggota baru yang ikut KK ini.', 'pindah' => 'Anggota pindah domisili keluar dari RT.', 'meninggal' => 'Laporkan anggota keluarga yang meninggal dunia.', 'ubah_data' => 'Koreksi pekerjaan, pendidikan, nama, dan data lain (bukan NIK).', 'ubah_alamat' => 'Perubahan alamat atau wilayah KK.'); ?>
    <?php foreach ($types as $k => $label): ?><a class="cpo-type" href="<?= site_url('warga/mutasi/ajukan?jenis=' . $k) ?>"><strong><?= html_escape($label) ?></strong><small><?= html_escape($hints[$k]) ?></small></a><?php endforeach; ?>
   </div>
  </section>
 <?php endif; ?>
 <section class="cdx-card">
  <header class="cdx-head"><span class="blue"><?= official_svg('clock') ?></span><div><h2>Riwayat mutasi</h2><p>Status pengajuan mutasi Anda.</p></div></header>
  <div class="cdx-scroll"><table class="cdx-table ops-table stack"><thead><tr><th>Kode</th><th>Tanggal</th><th>Jenis</th><th>Anggota</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $it): $prop = json_decode($it['proposed_values_json'], TRUE) ?: array(); ?>
   <tr><td data-label="Kode"><strong>MUT-<?= str_pad((int) $it['id'], 6, '0', STR_PAD_LEFT) ?></strong></td><td data-label="Tanggal"><?= ops_date($it['created_at']) ?></td><td data-label="Jenis"><?= html_escape($types[$it['mutation_type']] ?? '') ?></td><td data-label="Anggota"><?= html_escape($member_names[(int) $it['member_id']] ?? ($prop['nama_lengkap'] ?? 'Data keluarga')) ?></td><td data-label="Status"><?= ops_badge($it['status'], $labels, $tones) ?></td><td data-label="Aksi"><a class="cpx-detail" href="<?= site_url('warga/mutasi/' . (int) $it['id']) ?>">Detail</a></td></tr>
  <?php endforeach; if (!$items): ?><tr><td colspan="6" class="ops-empty">Belum ada pengajuan mutasi.</td></tr><?php endif; ?>
  </tbody></table></div>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
