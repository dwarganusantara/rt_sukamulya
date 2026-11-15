<?php
$this->load->helper(array('official_icons', 'page_ui'));
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$day = function ($d) use ($short) {$t = strtotime((string) $d); return $t ? date('d', $t) . ' ' . $short[(int) date('n', $t)] . ' ' . date('Y', $t) : '—';};
$archive = !empty($archive);
$url = function ($extra = array()) use ($tab, $q, $status, $period, $archive) {
    $p = array_filter(array_merge(array('tab' => $tab === 'program' ? '' : $tab, 'q' => $q, 'status' => $status, 'periode' => $period, 'arsip' => $archive ? '1' : ''), $extra), function ($v) {return $v !== '' && $v !== NULL;});
    return site_url('admin/bansos') . ($p ? '?' . http_build_query($p) : '');
};
$tone_map = array('ok' => 'ok', 'bad' => 'bad', 'pending' => 'warn', 'info' => 'info', 'purple' => 'purple', 'muted' => 'muted');
$p_tone = array('aktif' => 'ok', 'draft' => 'warn', 'selesai' => 'muted');
$icon_for = function ($type) {
    $t = mb_strtolower((string) $type);
    foreach (array('sembako|pangan|beras' => array('cart', 'orange'), 'sehat|obat|medis' => array('plus', 'blue'), 'didik|sekolah|beasiswa' => array('grad', 'purple'), 'rumah|bangun' => array('home', 'green'), 'lansia|santun' => array('people', 'red'), 'tunai|uang' => array('cash', 'green')) as $re => $ic)
        if (preg_match('/' . $re . '/u', $t)) return $ic;
    return array('heart', 'orange');
};
$can_write = $can('bansos.write');
$can_delete = $can('bansos.approve');
$tabs = array('program' => array('Program Bantuan', 'briefcase'), 'calon' => array('Calon Penerima', 'people'), 'salur' => array('Penyaluran', 'send'), 'laporan' => array('Laporan', 'doc'));
$status_opts = $tab === 'calon' ? array('usulan' => 'Diusulkan', 'layak' => 'Layak', 'tidak_layak' => 'Tidak Layak', 'ditolak' => 'Ditolak') : ($tab === 'salur' ? array('disetujui' => 'Siap disalurkan', 'disalurkan' => 'Sudah disalurkan') : $labels);
$offset = ($page - 1) * 10;
?>
<div class="pg pg-bansos">
 <?= pg_crumb(array(array('Kependudukan', NULL), array('Bantuan Sosial', NULL))) ?>
 <section class="pg-hero pg-hero-care"><span class="pg-hero-ic"><?= official_svg('heart') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Layanan Sosial</span><h1>Bantuan Sosial</h1><p>Kelola program bantuan sosial, data calon penerima, dan proses penyaluran bantuan untuk warga RT 009/008 Suka Mulya IV.</p></div>
  <span class="pg-care-art" aria-hidden="true"><i>“Bersama<br>Kita Peduli”</i></span>
  <?php if ($can_write): ?><a class="pg-btn solid pg-hero-act" href="<?= site_url('admin/bansos/baru') ?>"><?= official_svg('plus') ?>Program Bantuan</a><?php endif; ?></section>
 <?= ops_flash() ?>

 <section class="pg-stats" aria-label="Ringkasan bantuan sosial">
  <a class="pg-stat blue" href="<?= site_url('admin/bansos') ?>"><span class="pg-stat-ic"><?= official_svg('people') ?></span><span class="pg-stat-tx"><small>Total Program Bantuan</small><strong><?= (int) $stats['programs'] ?></strong><em><?= (int) $stats['active'] ?> program aktif</em></span></a>
  <a class="pg-stat green" href="<?= site_url('admin/bansos?tab=calon') ?>"><span class="pg-stat-ic"><?= official_svg('people') ?></span><span class="pg-stat-tx"><small>Calon Penerima</small><strong><?= (int) $stats['candidates'] ?></strong><em>Menunggu verifikasi / persetujuan</em></span></a>
  <a class="pg-stat amber" href="<?= site_url('admin/bansos?tab=salur&status=disetujui') ?>"><span class="pg-stat-ic"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><small>Dalam Penyaluran</small><strong><?= (int) $stats['approved'] ?></strong><em>Disetujui, belum disalurkan</em></span></a>
  <a class="pg-stat purple" href="<?= site_url('admin/bansos?tab=salur&status=disalurkan') ?>"><span class="pg-stat-ic"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><small>Selesai</small><strong><?= (int) $stats['distributed'] ?></strong><em>Telah disalurkan</em></span></a>
 </section>

 <section class="pg-card pg-panel">
  <div class="pg-bar">
   <nav class="pg-tabs pg-tabs-solid pg-tabs-flat" aria-label="Bagian bantuan sosial">
    <?php foreach ($tabs as $key => $t): ?><a class="pg-tab" href="<?= site_url('admin/bansos' . ($key === 'program' ? '' : '?tab=' . $key)) ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>><?= official_svg($t[1]) ?><?= $t[0] ?></a><?php endforeach; ?>
   </nav>
   <details class="pg-menu pg-bansos-menu pg-export-menu"><summary title="Rekap agregat per program, tanpa identitas penerima"><?= official_svg('download') ?><span>Export Data</span></summary><div>
    <a href="<?= site_url('admin/bansos/ekspor?format=xlsx') ?>" download><?= official_svg('download') ?>Excel (.xlsx)</a>
    <a href="<?= site_url('admin/bansos/ekspor?format=xls') ?>" download><?= official_svg('download') ?>Excel 97-2003 (.xls)</a>
   </div></details>
  </div>

  <form class="pg-filter-row pg-filter-flat" method="get" action="<?= site_url('admin/bansos') ?>" role="search">
   <?php if ($tab !== 'program'): ?><input type="hidden" name="tab" value="<?= ops_e($tab) ?>"><?php endif; ?><?php if ($archive): ?><input type="hidden" name="arsip" value="1"><?php endif; ?>
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari</span><input type="search" name="q" value="<?= ops_e($q) ?>" maxlength="80" placeholder="<?= in_array($tab, array('calon', 'salur'), TRUE) ? 'Cari nama penerima atau program...' : 'Cari nama program, jenis, sumber, atau kriteria...' ?>"></label>
   <?php if ($tab !== 'laporan'): ?><label class="pg-sel"><?= official_svg('filter') ?><span class="sr-only">Status</span><select name="status" onchange="this.form.submit()"><?= ops_options($status_opts, $status, 'Status Semua') ?></select></label><?php endif; ?>
   <label class="pg-sel"><?= official_svg('calendar') ?><span class="sr-only">Periode</span><select name="periode" onchange="this.form.submit()"><option value="">Periode Semua</option><?php foreach ($periods as $p): ?><option value="<?= ops_e($p) ?>"<?= $p === $period ? ' selected' : '' ?>><?= ops_e($p) ?></option><?php endforeach; ?></select></label>
   <button class="pg-btn outline" type="submit"><?= official_svg('filter') ?>Filter</button>
   <?php if ($can_write && $tab === 'program'): ?><a class="pg-btn solid" href="<?= site_url('admin/bansos/baru') ?>"><?= official_svg('plus') ?>Tambah Program</a><?php endif; ?>
  </form>

  <?php if ($tab === 'program'): ?>
  <table class="pg-table pg-bansos-table"><thead><tr><th class="no">No</th><th>Program Bantuan</th><th>Jenis Bantuan</th><th class="num">Jumlah Penerima</th><th>Periode</th><th>Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $it): $ic = $icon_for($it['aid_type'] . ' ' . $it['name']); $delete_locked = $it['status'] === 'selesai' || (int) ($it['processed'] ?? 0) > 0; ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td class="lead"><span class="pg-person"><span class="pg-tile <?= $ic[1] ?>" aria-hidden="true"><?= official_svg($ic[0]) ?></span><span><strong><?= ops_e($it['name']) ?></strong><small class="pg-plain"><?= ops_e(mb_strimwidth((string) ($it['criteria'] ?: $it['source']), 0, 70, '…')) ?></small></span></span></td>
    <td data-label="Jenis Bantuan"><?= ops_e($it['aid_type']) ?></td>
    <td data-label="Jumlah Penerima" class="num"><?= (int) $it['total'] ?><?php if ((int) $it['quota']): ?> <small class="pg-meta">/ <?= (int) $it['quota'] ?></small><?php endif; ?></td>
    <td data-label="Periode"><?= ops_e($it['period'] ?: '—') ?></td>
    <td data-label="Status"><span class="pg-pill <?= $p_tone[$it['status']] ?? 'muted' ?>"><?= ops_e($labels[$it['status']] ?? $it['status']) ?></span></td>
    <td class="acts"><div class="pg-acts pg-bansos-actions">
     <?php if ($can_write && $it['status'] !== 'selesai'): ?><a class="pg-bansos-action manage" href="<?= site_url('admin/bansos/' . (int) $it['id'] . '/ubah') ?>" title="Ubah program" aria-label="Kelola program <?= ops_e($it['name']) ?>"><?= official_svg('pencil') ?><span>Kelola</span></a><?php endif; ?>
     <a class="pg-bansos-action view" href="<?= site_url('admin/bansos/' . (int) $it['id']) ?>" title="Lihat program" aria-label="Lihat program <?= ops_e($it['name']) ?>"><?= official_svg('eye') ?><span>Lihat</span></a>
     <?php if ($can_delete): if ($delete_locked): ?><span class="pg-del is-off" title="Tidak dapat dihapus: program sudah selesai atau penerimanya telah diproses." aria-label="Hapus tidak tersedia"><?= official_svg('trash') ?><span>Hapus</span></span>
     <?php else: ?>
     <form method="post" action="<?= site_url('admin/bansos/' . (int) $it['id']) ?>" class="pg-del-form" data-delete-name="<?= ops_e($it['name']) ?>" data-delete-count="<?= (int) $it['total'] ?>"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="from" value="index"><button class="pg-del" type="submit" title="Hapus program" aria-label="Hapus program <?= ops_e($it['name']) ?>"><?= official_svg('trash') ?><span>Hapus</span></button></form>
     <?php endif; endif; ?>
     <details class="pg-menu pg-bansos-menu"><summary aria-label="Menu lain"><?= official_svg('more-v') ?><span>Lainnya</span></summary><div>
      <a href="<?= site_url('admin/bansos/' . (int) $it['id']) ?>"><?= official_svg('people') ?>Kelola calon penerima</a>
      <a href="<?= site_url('admin/bansos?tab=salur&q=' . rawurlencode($it['name'])) ?>"><?= official_svg('send') ?>Lihat penyaluran</a>
      <?php if ($can_write && $it['status'] !== 'selesai'): ?><a href="<?= site_url('admin/bansos/' . (int) $it['id'] . '/ubah') ?>"><?= official_svg('pencil') ?>Ubah program</a><?php endif; ?>     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="pg-empty"><?= ($q !== '' || $status !== '' || $period !== '') ? 'Tidak ada program yang cocok dengan filter.' : 'Belum ada program bantuan.' ?><?php if ($can_write && $q === ''): ?> <a href="<?= site_url('admin/bansos/baru') ?>">Buat program pertama</a>.<?php endif; ?></td></tr><?php endif; ?>
  </tbody></table>

  <?php elseif ($tab === 'laporan'): $sum = array('total' => 0, 'pending' => 0, 'approved' => 0, 'distributed' => 0, 'value' => 0); ?>
  <table class="pg-table"><thead><tr><th>Program</th><th>Periode</th><th class="num">Kuota</th><th class="num">Tercatat</th><th class="num">Menunggu</th><th class="num">Disetujui</th><th class="num">Disalurkan</th><th class="num">Nilai Tersalur</th></tr></thead><tbody>
  <?php foreach ($rows as $it): foreach (array('total', 'pending', 'approved', 'distributed') as $k) $sum[$k] += (int) $it[$k]; $sum['value'] += (float) $it['distributed_value']; ?>
   <tr><td class="lead"><strong><?= ops_e($it['name']) ?></strong></td><td data-label="Periode"><?= ops_e($it['period'] ?: '—') ?></td><td data-label="Kuota" class="num"><?= (int) $it['quota'] ?: '—' ?></td><td data-label="Tercatat" class="num"><?= (int) $it['total'] ?></td><td data-label="Menunggu" class="num"><?= (int) $it['pending'] ?></td><td data-label="Disetujui" class="num"><?= (int) $it['approved'] ?></td><td data-label="Disalurkan" class="num"><?= (int) $it['distributed'] ?></td><td data-label="Nilai" class="num"><?= ops_money($it['distributed_value']) ?></td></tr>
  <?php endforeach; if ($rows): ?>
   <tr class="pg-total"><td class="lead"><strong>Total</strong></td><td data-label=""></td><td data-label="" class="num"></td><td data-label="Tercatat" class="num"><?= $sum['total'] ?></td><td data-label="Menunggu" class="num"><?= $sum['pending'] ?></td><td data-label="Disetujui" class="num"><?= $sum['approved'] ?></td><td data-label="Disalurkan" class="num"><?= $sum['distributed'] ?></td><td data-label="Nilai" class="num"><?= ops_money($sum['value']) ?></td></tr>
  <?php else: ?><tr><td colspan="8" class="pg-empty">Belum ada data untuk dilaporkan.</td></tr><?php endif; ?>
  </tbody></table>
  <p class="pg-note">Laporan hanya berisi angka agregat tanpa identitas penerima, aman untuk dibagikan ke rapat warga.</p>

  <?php else: ?>
  <p class="pg-scope-note"><?= official_svg('filter') ?><?php if ($archive): ?>Menampilkan juga calon dari <b>program yang sudah Selesai</b> (arsip). <a href="<?= $url(array('arsip' => '', 'hal' => NULL)) ?>">Sembunyikan program selesai</a><?php else: ?>Hanya program yang masih berjalan (Draf/Aktif)<?= $tab === 'salur' ? ', ditambah riwayat yang sudah disalurkan' : '' ?>. <a href="<?= $url(array('arsip' => '1', 'hal' => NULL)) ?>">Tampilkan juga program selesai</a><?php endif; ?></p>
  <table class="pg-table"><thead><tr><th class="no">No</th><th>Penerima</th><th>Program</th><th>Periode</th><th><?= $tab === 'salur' ? 'Disalurkan' : 'Diusulkan' ?></th><?php if ($tab === 'salur'): ?><th class="num">Nilai</th><?php endif; ?><th>Status</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $r): ?>
   <tr>
    <td class="no"><?= $offset + $i + 1 ?></td>
    <td class="lead"><span class="pg-person"><span class="pg-av" aria-hidden="true"><?= ops_e(mb_strtoupper(mb_substr($r['recipient_name'] ?: '?', 0, 1))) ?></span><span><strong><?= ops_e($r['recipient_name']) ?></strong><small class="pg-plain"><?= ops_e(mb_strimwidth((string) $r['address'], 0, 50, '…')) ?></small></span></span></td>
    <td data-label="Program"><?= ops_e($r['program_name']) ?><?php if (($r['program_status'] ?? '') === 'selesai'): ?> <span class="pg-pill muted">Program selesai</span><?php endif; ?></td>
    <td data-label="Periode"><?= ops_e($r['period'] ?: '—') ?></td>
    <td data-label="<?= $tab === 'salur' ? 'Disalurkan' : 'Diusulkan' ?>"><?= $tab === 'salur' ? ($r['distributed_at'] ? $day($r['distributed_at']) : '—') : $day($r['created_at']) ?></td>
    <?php if ($tab === 'salur'): ?><td data-label="Nilai" class="num"><?= $r['status'] === 'disalurkan' ? ops_money($r['amount']) : '—' ?></td><?php endif; ?>
    <td data-label="Status"><span class="pg-pill <?= $tone_map[$r_tones[$r['status']] ?? 'muted'] ?? 'muted' ?>"><?= ops_e($r_labels[$r['status']] ?? $r['status']) ?></span></td>
    <td class="acts"><div class="pg-acts"><a class="pg-edit" href="<?= site_url('admin/bansos/' . (int) $r['program_id']) ?>#penerima-<?= (int) $r['id'] ?>"><?= official_svg('eye') ?><?= in_array($r['status'], array('usulan', 'layak', 'disetujui'), TRUE) ? 'Proses' : 'Lihat' ?></a></div></td>
   </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="<?= $tab === 'salur' ? 8 : 7 ?>" class="pg-empty"><?= $tab === 'salur' ? 'Belum ada penerima yang disetujui atau disalurkan.' : ($q === '' && $status === '' && $period === '' && !$archive ? 'Belum ada calon penerima di program yang sedang berjalan. Usulkan dari halaman detail program.' : 'Tidak ada calon penerima untuk filter ini.') ?></td></tr><?php endif; ?>
  </tbody></table>
  <?php endif; ?>

  <?php if ($tab !== 'laporan'): ?><div class="pg-foot pg-foot-in"><p>Menampilkan <b><?= count($rows) ?></b> dari <b><?= (int) $matched ?></b> <?= $tab === 'program' ? 'program' : 'penerima' ?></p><?= pg_pager($page, $pages, function ($n) use ($url) {return $url(array('hal' => $n));}) ?></div><?php endif; ?>
 </section>
 <p class="pg-note">Privasi: jangan mencatat diagnosis kesehatan, rincian penghasilan, atau data rentan lain kecuali benar-benar diperlukan. Publikasi hanya dalam bentuk statistik agregat tanpa identitas.</p>
</div>
<dialog class="pg-delete-dialog" id="bansos-delete-dialog" aria-labelledby="bansos-delete-title" aria-describedby="bansos-delete-desc">
 <button class="pg-delete-x" type="button" data-delete-close aria-label="Tutup"><?= official_svg('close') ?></button>
 <div class="pg-delete-icon" aria-hidden="true"><?= official_svg('trash') ?></div>
 <span class="pg-delete-kicker">Konfirmasi Penghapusan</span>
 <h2 id="bansos-delete-title">Hapus Program Bantuan?</h2>
 <p id="bansos-delete-desc">Program <strong id="bansos-delete-name"></strong> akan dihapus secara permanen.</p>
 <div class="pg-delete-impact" id="bansos-delete-impact" hidden><?= official_svg('people') ?><span><strong id="bansos-delete-count"></strong> calon penerima juga akan ikut terhapus.</span></div>
 <p class="pg-delete-warning"><?= official_svg('alert') ?><span>Tindakan ini tidak dapat dibatalkan. Pastikan program yang dipilih sudah benar.</span></p>
 <div class="pg-delete-actions"><button class="pg-btn ghost" type="button" data-delete-close><?= official_svg('close') ?>Batal</button><button class="pg-delete-confirm" type="button" id="bansos-delete-confirm"><?= official_svg('trash') ?>Hapus Program</button></div>
</dialog>
<script>
(function(){
 var dialog=document.getElementById('bansos-delete-dialog'),name=document.getElementById('bansos-delete-name'),impact=document.getElementById('bansos-delete-impact'),count=document.getElementById('bansos-delete-count'),confirmButton=document.getElementById('bansos-delete-confirm'),pending=null;
 if(!dialog)return;
 document.querySelectorAll('.pg-del-form').forEach(function(form){form.addEventListener('submit',function(e){if(form.dataset.deleteConfirmed==='1')return;e.preventDefault();pending=form;name.textContent='“'+form.dataset.deleteName+'”';var n=parseInt(form.dataset.deleteCount||'0',10);count.textContent=n;impact.hidden=n<1;dialog.showModal();setTimeout(function(){dialog.querySelector('[data-delete-close]').focus()},0);});});
 dialog.querySelectorAll('[data-delete-close]').forEach(function(button){button.addEventListener('click',function(){dialog.close();});});
 dialog.addEventListener('click',function(e){if(e.target===dialog)dialog.close();});
 dialog.addEventListener('close',function(){if(pending)pending.querySelector('button[type=submit]').focus();});
 confirmButton.addEventListener('click',function(){if(!pending)return;confirmButton.disabled=true;pending.dataset.deleteConfirmed='1';pending.requestSubmit();});
})();
</script>
