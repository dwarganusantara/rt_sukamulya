<?php
$this->load->helper('official_icons');
$closed = $program['status'] === 'selesai';
list($pm, $py) = Sosial::parse_period($program['period']);
$period_label = $pm !== '' ? $pm . ' ' . $py : $program['period'];
$total = count($recipients);
$waiting = $summary['usulan'] + $summary['layak'];
$quota = (int) $program['quota'];
$used = $summary['disetujui'] + $summary['disalurkan'];
$pill = array('usulan' => array('Diusulkan', 'amber', 'hourglass'), 'layak' => array('Layak', 'green', 'check-circle'), 'tidak_layak' => array('Tidak Layak', 'red', 'close'),
    'disetujui' => array('Disetujui', 'blue', 'check'), 'ditolak' => array('Ditolak', 'red', 'close'), 'disalurkan' => array('Disalurkan', 'teal', 'cash'));
$status_msg = array('draft' => array('Program masih draf', 'Aktifkan program agar calon penerima dapat disetujui.', 'amber', 'clock'),
    'aktif' => array('Program berjalan', 'Calon penerima dapat diverifikasi dan disetujui' . ($quota ? ' · ' . $used . ' dari ' . $quota . ' kuota terpakai.' : '.'), 'green', 'check-circle'),
    'selesai' => array('Program telah selesai', 'Data penerima dikunci dan tidak dapat diubah.', 'blue', 'check-circle'));
$sm = $status_msg[$program['status']] ?? $status_msg['draft'];
$css = 'assets/bansos-v2.css';
?>
<link rel="stylesheet" href="<?= base_url($css) ?>?v=<?= filemtime(FCPATH . $css) ?>">
<div class="ops bsx">
<nav class="ops-crumb"><a href="<?= site_url('admin/bansos') ?>">Bantuan Sosial</a><span>›</span><strong><?= ops_e($program['name']) ?></strong></nav>

<section class="bsx-hero" style="--bsx-hero:url('<?= base_url('assets/img/home/home-banner-clean.jpg') ?>')">
 <span class="bsx-hero-ic" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M24 25s-11-6.6-11-14.2C13 6.5 16 4 19.3 4c2 0 3.700 1 4.700 2.600C25 5 26.700 4 28.700 4 32 4 35 6.500 35 10.800 35 18.400 24 25 24 25z" fill="#ef4444" stroke="#fff" stroke-width="2.200"/><path d="M6 34.500l7.500-5.500c2.200-1.600 5-1.900 7.400-.8l6.300 2.800c1.500.7 1.700 2.700.4 3.700-.8.600-1.800.7-2.700.4l-5.400-1.900M17.500 37.200l9.800 2.300c1.800.4 3.600 0 5.100-1l9.800-7c1.500-1.100 1.600-3.300.2-4.400-1-.8-2.400-.9-3.500-.2l-8.400 5.100" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
 <div class="bsx-hero-tx">
  <span class="bsx-kicker">Bantuan Sosial</span>
  <h1><?= ops_e($program['name']) ?><?php if ($program['aid_type'] !== '' && stripos($program['name'], $program['aid_type']) === FALSE): ?> <em>– <?= ops_e($program['aid_type']) ?></em><?php endif; ?></h1>
  <p>Kelola calon penerima, verifikasi kelayakan, persetujuan, hingga penyaluran bantuan secara mudah dan transparan.</p>
  <div class="bsx-chips">
   <span class="bsx-chip green"><?= official_svg('calendar') ?><?= ops_e($period_label) ?></span>
   <span class="bsx-chip orange"><?= official_svg('cash') ?><?= ops_money($program['unit_value']) ?> / penerima</span>
   <span class="bsx-chip blue"><?= official_svg('database') ?><?= $quota ? 'Kuota: ' . $quota : 'Tanpa kuota' ?></span>
   <?php if (!$closed && $can('bansos.write')): ?><a class="bsx-chip edit" href="<?= site_url('admin/bansos/' . (int) $program['id'] . '/ubah') ?>"><?= official_svg('pencil') ?>Ubah Program</a><?php endif; ?>
  </div>
 </div>
</section>

<?= ops_flash() ?>

<div class="bsx-stats">
 <div class="bsx-stat green"><span class="bsx-stat-ic"><?= official_svg('people') ?></span><div><strong><?= $total ?></strong><small>Total Calon Penerima</small></div><em><?= $quota ? 'Kuota: ' . $quota : 'Tanpa kuota' ?></em></div>
 <div class="bsx-stat amber"><span class="bsx-stat-ic"><?= official_svg('hourglass') ?></span><div><strong><?= $waiting ?></strong><small>Menunggu Proses</small></div><em>Verifikasi / Setuju</em></div>
 <div class="bsx-stat lime"><span class="bsx-stat-ic"><?= official_svg('check-circle') ?></span><div><strong><?= (int) $summary['disetujui'] ?></strong><small>Disetujui</small></div><em>Belum disalurkan</em></div>
 <div class="bsx-stat purple"><span class="bsx-stat-ic"><?= official_svg('cash') ?></span><div><strong><?= (int) $summary['disalurkan'] ?></strong><small>Sudah Disalurkan</small></div><em><?= ops_money($value) ?></em></div>
</div>

<?php $this->load->view('admin/ops/bansos_recipients', get_defined_vars()); ?>

<div class="bsx-grid">
 <div class="bsx-col">
  <?php if (FALSE): // Tampilan lama; digantikan partial bansos_recipients. ?>
  <?php if (FALSE): // Informasi terbaru dan banner tidak ditampilkan pada detail program. ?>
  <section class="bsx-card">
   <header class="bsx-card-head"><h2><?= official_svg('people') ?>Calon Penerima Bantuan Sosial</h2><a href="<?= site_url('admin/bansos/ekspor') ?>"><?= official_svg('download') ?>Ekspor Rekap <?= official_svg('arrow-right') ?></a></header>
   <div class="bsx-table-wrap"><table class="bsx-table"><thead><tr><th>No</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr></thead><tbody>
   <?php foreach ($recipients as $i => $r): $pl = $pill[$r['status']] ?? array($labels[$r['status']] ?? $r['status'], 'gray', 'clock'); ?>
    <tr>
     <td data-label="No" class="bsx-no"><?= $i + 1 ?></td>
     <td data-label="Nama"><div class="bsx-person"><span class="bsx-avatar"><?= official_svg('person') ?></span><div><strong><?= ops_e($r['recipient_name']) ?></strong><small><?= ops_e($r['address']) ?></small></div></div></td>
     <td data-label="Status"><span class="bsx-pill <?= $pl[1] ?>"><?= official_svg($pl[2]) ?><?= ops_e($pl[0]) ?></span><?= $r['distributed_at'] ? '<small>' . ops_date($r['distributed_at'], TRUE) . '</small>' : '' ?><?= $r['proof'] ? '<small><a href="' . site_url('admin/bansos/bukti/' . (int) $r['id']) . '" target="_blank" rel="noopener">Bukti terima</a></small>' : '' ?></td>
     <td data-label="Keterangan"><span class="bsx-note"><?= ops_e($r['decision_note'] ?: ($r['proposal_note'] ?: '—')) ?></span>
     <?php if (!$closed): ?>
      <?php if ($r['status'] === 'usulan' && $can('bansos.write')): ?><form method="post" class="bsx-act"><?= ops_csrf() ?><input type="hidden" name="act" value="verify"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>">
       <details class="bsx-note-in"><summary><?= official_svg('doc') ?>Catatan Verifikasi</summary><input name="note" placeholder="Wajib bila tidak layak" aria-label="Catatan verifikasi" maxlength="500"></details>
       <span class="bsx-act-btns"><button class="bsx-btn green sm" name="result" value="layak"><?= official_svg('check') ?>Layak</button><button class="bsx-btn red-o sm" name="result" value="tidak_layak"><?= official_svg('close') ?>Tidak Layak</button></span></form><?php endif; ?>
      <?php if ($r['status'] === 'layak' && $can('bansos.approve')): ?><form method="post" class="bsx-act"><?= ops_csrf() ?><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>">
       <details class="bsx-note-in"><summary><?= official_svg('doc') ?>Catatan Keputusan</summary><input name="note" placeholder="Wajib bila ditolak" aria-label="Catatan keputusan" maxlength="500"></details>
       <span class="bsx-act-btns"><button class="bsx-btn green sm" name="act" value="approve"><?= official_svg('check') ?>Setujui</button><button class="bsx-btn red-o sm" name="act" value="reject"><?= official_svg('close') ?>Tolak</button></span></form><?php endif; ?>
      <?php if ($r['status'] === 'disetujui' && $can('bansos.write')): ?><details class="bsx-more"><summary><?= official_svg('cash') ?>Catat Penyaluran</summary><form method="post" enctype="multipart/form-data"><?= ops_csrf() ?><input type="hidden" name="act" value="distribute"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>">
       <label>Bukti penerimaan (privat)<input type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf"></label><input name="note" placeholder="Catatan (opsional)" aria-label="Catatan penyaluran" maxlength="500"><button class="bsx-btn green sm" type="submit" onclick="return confirm('Catat bantuan sudah disalurkan? Tidak dapat dicatat dua kali.')"><?= official_svg('save') ?>Simpan Penyaluran</button></form></details><?php endif; ?>
     <?php endif; ?></td>
    </tr>
   <?php endforeach; if (!$recipients): ?><tr><td colspan="4" class="bsx-empty">Belum ada calon penerima. Usulkan dari form di samping.</td></tr><?php endif; ?>
   </tbody></table></div>
  </section>
  <?php endif; ?>

  <section class="bsx-card">
   <header class="bsx-card-head"><h2 class="orange"><?= official_svg('megaphone') ?>Informasi Terbaru</h2><a href="<?= site_url('admin/posts/pengumuman') ?>">Lihat Semua <?= official_svg('arrow-right') ?></a></header>
   <?php if ($news): ?><ul class="bsx-news">
    <?php foreach ($news as $n): $ag = $n['type'] === 'agenda'; $when = $ag && $n['event_date'] ? $n['event_date'] . ' ' . ($n['event_time'] ?: '00:00:00') : $n['created_at']; ?>
     <li><a href="<?= site_url(($ag ? 'agenda/' : 'pengumuman/') . (int) $n['id']) ?>" target="_blank" rel="noopener"><span class="bsx-news-ic <?= $ag ? 'green' : 'blue' ?>"><?= official_svg($ag ? 'calendar' : 'bell') ?></span><span class="bsx-news-tx"><strong><?= $ag ? 'Agenda' : 'Pengumuman' ?>: <?= ops_e($n['title']) ?></strong><small><?= ops_date($when, $ag && $n['event_time']) ?></small></span><?= official_svg('chev-right') ?></a></li>
    <?php endforeach; ?>
   </ul><?php else: ?><p class="bsx-empty">Belum ada pengumuman atau agenda yang terbit.</p><?php endif; ?>
  </section>

  <div class="bsx-banner">
   <svg class="bsx-banner-art" viewBox="0 0 180 110" aria-hidden="true"><path d="M8 100c20-30 40-34 60-20" fill="none" stroke="#4ade80" stroke-width="6" stroke-linecap="round"/><path d="M14 78c-8-14 0-30 14-32 0 14-4 24-14 32zM30 92c-2-16 10-28 24-26-4 14-12 22-24 26z" fill="#22c55e"/><rect x="48" y="44" width="92" height="60" rx="6" fill="#f2b45a"/><path d="M48 44l12-14h68l12 14z" fill="#e59c3a"/><rect x="104" y="12" width="12" height="34" rx="3" fill="#facc15"/><rect x="106" y="6" width="8" height="8" rx="2" fill="#16a34a"/><ellipse cx="80" cy="34" rx="20" ry="10" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/><rect x="122" y="24" width="14" height="20" rx="3" fill="#dc2626"/><rect x="78" y="62" width="32" height="26" rx="4" fill="#fff"/><path d="M94 84s-10-6-10-12c0-3 2-5 5-5 2 0 4 1 5 3 1-2 3-3 5-3 3 0 5 2 5 5 0 6-10 12-10 12z" fill="#ef4444"/></svg>
   <strong>Bersama Membangun<br>Lingkungan yang Lebih Baik</strong>
  </div>
  <?php endif; ?>
  <?php $this->load->view('admin/ops/bansos_status', get_defined_vars()); ?>
 </div>

 <div class="bsx-col">
  <?php if (FALSE): // Status Program dipindahkan ke kolom utama. ?>
  <section class="bsx-card">
   <header class="bsx-card-head"><h2><?= official_svg('check-circle') ?>Status Program</h2><span class="bsx-status <?= ops_e($program['status']) ?>"><?= ops_e($program_labels[$program['status']]) ?></span></header>
   <div class="bsx-status-box <?= $sm[2] ?>"><?= official_svg($sm[3]) ?><div><strong><?= ops_e($sm[0]) ?></strong><span><?= ops_e($sm[1]) ?></span></div></div>
   <?php if ($can('bansos.approve') && !$closed): ?><form method="post" class="bsx-status-act"><?= ops_csrf() ?><input type="hidden" name="act" value="program_status"><?php if ($program['status'] === 'draft'): ?><button class="bsx-btn green wide" name="to" value="aktif"><?= official_svg('check') ?>Aktifkan Program</button><?php else: ?><button class="bsx-btn ghost wide" name="to" value="selesai" onclick="return confirm('Tutup program? Data tidak dapat diubah lagi.')"><?= official_svg('check-circle') ?>Tandai Selesai</button><?php endif; ?></form><?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($can('bansos.write') && !$closed): ?><section class="bsx-card">
   <header class="bsx-card-head"><h2><?= official_svg('people') ?>Usulan Calon Penerima</h2></header>
   <form method="post" class="bsx-form" id="bsx-usulan"><?= ops_csrf() ?><input type="hidden" name="act" value="add">
    <label for="f-family_id">Keluarga (data master KK) <b>*</b></label>
<?php $accounts = $accounts ?? array();
    $open_fam = count(array_filter($families, function ($f) { return !$f['registered']; }));
    $open_acc = count(array_filter($accounts, function ($a) { return !$a['registered']; }));
    $open_all = $open_fam + $open_acc; ?>
    <span class="bsx-input sel"><select id="f-family_id" name="family_id">
     <option value="">Pilih Kepala Keluarga (atau isi manual)</option>
     <?php if ($families || $accounts): ?><option value="all"<?= $open_all ? '' : ' disabled' ?>>✔ Seluruh Warga — <?= $open_all ?> belum terdaftar (<?= $open_fam ?> KK + <?= $open_acc ?> akun tanpa KK)</option><?php endif; ?>
     <?php if ($families): ?><optgroup label="Kepala Keluarga (data master KK)">
     <?php foreach ($families as $fid => $f): ?><option value="<?= (int) $fid ?>" data-name="<?= ops_e($f['name']) ?>" data-address="<?= ops_e($f['address']) ?>"<?= $f['registered'] ? ' disabled' : '' ?>><?= ops_e($f['label']) ?><?= $f['registered'] ? ' (sudah terdaftar)' : '' ?></option><?php endforeach; ?>
     </optgroup><?php endif; ?>
     <?php if ($accounts): ?><optgroup label="Akun warga (belum ada data KK)">
     <?php foreach ($accounts as $cid => $a): ?><option value="c:<?= (int) $cid ?>" data-name="<?= ops_e($a['name']) ?>" data-address="<?= ops_e($a['address']) ?>"<?= $a['registered'] ? ' disabled' : '' ?>><?= ops_e($a['name']) ?><?= $a['address'] !== '' ? ' — ' . ops_e(mb_strimwidth($a['address'], 0, 40, '…')) : ' — alamat belum diisi' ?><?= $a['registered'] ? ' (sudah terdaftar)' : '' ?></option><?php endforeach; ?>
     </optgroup><?php endif; ?>
    </select></span>
    <p class="bsx-all-note" id="f-all-note" hidden><?= official_svg('people') ?><span>Semua <b><?= $open_all ?></b> warga yang belum terdaftar akan diusulkan sekaligus: <?= $open_fam ?> kepala keluarga dari data master KK dan <?= $open_acc ?> akun warga yang belum punya data KK (nama &amp; alamat dari profil akun).</span></p>
    <div id="f-single">
    <label for="f-recipient_name">Nama Penerima <b>*</b></label>
    <span class="bsx-input"><input id="f-recipient_name" name="recipient_name" maxlength="140" placeholder="Masukkan nama penerima"></span>
    <label for="f-address">Alamat</label>
    <span class="bsx-input"><?= official_svg('pin') ?><input id="f-address" name="address" maxlength="255" placeholder="Masukkan alamat lengkap"></span>
    </div>
    <label for="f-proposal_note">Alasan Usulan</label>
    <span class="bsx-input"><input id="f-proposal_note" name="proposal_note" maxlength="500" placeholder="Ringkas, tanpa data kesehatan"></span>
    <div class="bsx-form-act"><button class="bsx-btn ghost-green" type="reset"><?= official_svg('swap') ?>Batal</button><button class="bsx-btn green" type="submit"><?= official_svg('send') ?>Kirim Usulan</button></div>
   </form>
  </section><?php endif; ?>

  <section class="bsx-card"><header class="bsx-card-head"><h2><?= official_svg('lines') ?>Kriteria Kelayakan</h2></header><p class="bsx-criteria"><?= ops_e($program['criteria'] ?: 'Belum diisi.') ?></p></section>
 </div>
</div>
</div>
<script>
(function(){
 var s=document.getElementById('f-family_id'),n=document.getElementById('f-recipient_name'),a=document.getElementById('f-address'),f=document.getElementById('bsx-usulan'),
  single=document.getElementById('f-single'),note=document.getElementById('f-all-note'),btn=f&&f.querySelector('button[type=submit]');
 if(!s||!n||!a)return;
 var btnHtml=btn?btn.innerHTML:'';
 function sync(){var o=s.options[s.selectedIndex],all=s.value==='all',on=!!s.value&&!all;
  if(single)single.hidden=all; if(note)note.hidden=!all;
  if(btn)btn.innerHTML=all?btnHtml.replace('Kirim Usulan','Usulkan Seluruh Warga'):btnHtml;
  if(on){n.value=o.getAttribute('data-name')||'';a.value=o.getAttribute('data-address')||'';}else if(a.readOnly){n.value='';a.value='';}
  n.readOnly=on;a.readOnly=on;}
 s.addEventListener('change',sync);
 if(f){
  f.addEventListener('reset',function(){setTimeout(function(){n.readOnly=a.readOnly=false;sync();},0);});
  f.addEventListener('submit',function(e){if(s.value==='all'&&!confirm('Usulkan seluruh warga yang belum terdaftar (data KK + akun warga tanpa KK) sebagai calon penerima program ini?'))e.preventDefault();});
 }
 sync();
})();
</script>
