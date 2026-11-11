<?php
$this->load->helper(array('official_icons', 'page_ui'));
$total = 0; foreach ($summary as $s) $total += $s['total'];
$can_remind = $can('iuran.remind');
$finance = $can_remind; // peran yang boleh membuka Iuran Warga & Kas RT (admin, ketua, bendahara)
$tabs = array();
if ($finance) $tabs['admin/dues'] = array('Iuran Warga', 'wallet');
$tabs['admin/tunggakan'] = array('Tunggakan Iuran Warga', 'clock');
if ($finance) $tabs['admin/transactions'] = array('Kas RT', 'chart');
if ($can('kas.report')) $tabs['admin/rekonsiliasi'] = array('Rekonsiliasi', 'list');
$bucket_ui = array('0_30' => array('green', 'ok'), '31_60' => array('amber', 'warn'), '61_90' => array('orange', 'orange'), '90_plus' => array('red', 'bad'));
$colors = array('0_30' => '#22a55a', '31_60' => '#f5b21b', '61_90' => '#f97316', '90_plus' => '#ef4444');
$short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$period_label = function ($p) use ($short) {return preg_match('/^(\d{4})-(\d{2})$/', $p, $m) ? $short[(int) $m[2]] . ' ' . $m[1] : $p;};
$url = function ($extra) use ($bucket, $period) {$p = array_filter(array_merge(array('umur' => $bucket, 'periode' => $period), $extra), 'strlen'); return site_url('admin/tunggakan') . ($p ? '?' . http_build_query($p) : '');};
?>
<div class="pg">
 <nav class="pg-tabs" aria-label="Menu keuangan"><?php foreach ($tabs as $uri => $t): ?><a class="pg-tab" href="<?= site_url($uri) ?>"<?= $uri === 'admin/tunggakan' ? ' aria-current="page"' : '' ?>><?= official_svg($t[1]) ?><?= $t[0] ?></a><?php endforeach; ?></nav>

 <section class="pg-hero pg-hero-care pg-hero-due"><span class="pg-hero-ic soft-ic"><?= official_svg('wallet') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Keuangan</span><h1>Tunggakan Iuran Warga</h1><p>Pantau tagihan iuran periode lalu yang belum dibayar berdasarkan lama keterlambatan (dihitung sejak akhir bulan periode).</p></div>
  <span class="pg-due-art" aria-hidden="true"></span>
  <a class="pg-btn outline-blue pg-hero-act" href="<?= site_url('admin/tunggakan?export=csv') ?>"><?= official_svg('download') ?>Ekspor CSV</a></section>
 <?= ops_flash() ?>

 <section class="pg-stats pg-stats-due" aria-label="Ringkasan tunggakan">
  <a class="pg-stat red" href="<?= site_url('admin/tunggakan') ?>"><span class="pg-stat-ic soft"><?= official_svg('people') ?></span><span class="pg-stat-tx"><strong><?= (int) $debtors ?></strong><small class="plain">Warga Menunggak</small></span></a>
  <a class="pg-stat blue" href="<?= site_url('admin/tunggakan') ?>"><span class="pg-stat-ic soft"><?= official_svg('wallet') ?></span><span class="pg-stat-tx"><strong><?= ops_money($total) ?></strong><small class="plain">Total Tunggakan</small></span></a>
  <?php foreach ($buckets as $k => $label): $u = $bucket_ui[$k]; ?>
  <a class="pg-stat <?= $u[0] ?><?= $bucket === $k ? ' is-on' : '' ?>" href="<?= $bucket === $k ? site_url('admin/tunggakan') : site_url('admin/tunggakan?umur=' . $k) ?>"<?= $bucket === $k ? ' aria-current="true"' : '' ?>><span class="pg-stat-ic soft"><?= official_svg('calendar') ?></span><span class="pg-stat-tx"><small><?= ops_e($label) ?></small><em><?= (int) $summary[$k]['citizens'] ?> warga</em><b><?= ops_money($summary[$k]['total']) ?></b></span></a>
  <?php endforeach; ?>
 </section>

 <div class="pg-due-grid">
  <section class="pg-card pg-panel">
   <header class="pg-panel-head"><div class="pg-form-head"><span class="pg-form-ic plain"><?= official_svg('calendar') ?></span><div><h2>Daftar Tunggakan<?= $bucket ? ' — ' . ops_e($buckets[$bucket]) : '' ?></h2><p>Rincian warga yang memiliki tagihan belum dibayar. Pengingat tampil di portal warga (dan email bila diaktifkan), bukan pesan WhatsApp otomatis.</p></div></div>
    <form method="get" action="<?= site_url('admin/tunggakan') ?>" class="pg-inline-filter"><?php if ($bucket): ?><input type="hidden" name="umur" value="<?= ops_e($bucket) ?>"><?php endif; ?>
     <label class="pg-sel"><?= official_svg('calendar') ?><span class="sr-only">Periode</span><select name="periode" onchange="this.form.submit()"><option value="">Semua Periode</option><?php foreach ($periods as $p): ?><option value="<?= ops_e($p) ?>"<?= $p === $period ? ' selected' : '' ?>><?= ops_e($period_label($p)) ?></option><?php endforeach; ?></select></label>
     <noscript><button class="pg-btn ghost small" type="submit">Terapkan</button></noscript>
     <?php if ($bucket || $period): ?><a class="pg-btn ghost small" href="<?= site_url('admin/tunggakan') ?>">Reset</a><?php endif; ?></form>
   </header>
   <form method="post" action="<?= site_url('admin/tunggakan/ingatkan') ?>" id="due-form"><?= ops_csrf() ?>
    <table class="pg-table pg-due-table"><thead><tr><?php if ($can_remind): ?><th class="pick"><input type="checkbox" id="due-all" aria-label="Pilih semua tagihan"></th><?php endif; ?><th class="no">No</th><th>Warga / KK</th><th>Jenis Iuran</th><th>Periode</th><th class="c">Keterlambatan</th><th class="num">Nominal</th><th class="c">Aksi</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): $name = $r['full_name'] ?: $r['display_name']; $u = $bucket_ui[$r['bucket']]; $cid = (int) $r['citizen_id']; ?>
     <tr>
      <?php if ($can_remind): ?><td class="pick"><input type="checkbox" name="due_ids[]" value="<?= (int) $r['id'] ?>" aria-label="Pilih tagihan <?= ops_e($name) ?> <?= ops_e($r['period']) ?>"></td><?php endif; ?>
      <td class="no"><?= $i + 1 ?></td>
      <td class="lead"><span class="pg-person"><span class="pg-av tone-<?= $i % 4 ?>" aria-hidden="true"><?= official_svg('person') ?></span><span><strong><?= ops_e($name) ?></strong><small class="pg-plain pg-mono"><?= isset($kk[$cid]) ? 'KK ' . ops_e(rt_mask_id($kk[$cid])) : 'KK belum terverifikasi' ?></small></span></span></td>
      <td data-label="Jenis Iuran"><?= ops_e($r['category']) ?></td>
      <td data-label="Periode"><?= ops_e($period_label($r['period'])) ?></td>
      <td data-label="Keterlambatan" class="c"><span class="pg-late <?= $u[1] ?>"><?= (int) $r['days'] ?> hari</span></td>
      <td data-label="Nominal" class="num"><strong><?= ops_money($r['remaining']) ?></strong><?php if ((float) $r['amount_paid'] > 0): ?><br><small class="pg-meta">dari <?= ops_money($r['amount_due']) ?></small><?php endif; ?></td>
      <td class="acts"><div class="pg-acts">
       <?php if ($can_remind): ?><button class="pg-edit" type="submit" name="one" value="<?= (int) $r['id'] ?>" formnovalidate><?= official_svg('send') ?>Kirim Pengingat</button><?php endif; ?>
       <details class="pg-menu"><summary aria-label="Menu lain untuk <?= ops_e($name) ?>"><?= official_svg('more-v') ?></summary><div>
        <span class="pg-menu-info"><?= official_svg('clock') ?><?= isset($last[(int) $r['id']]) ? 'Diingatkan ' . ops_date($last[(int) $r['id']], TRUE) : 'Belum pernah diingatkan' ?></span>
        <?php if ($finance): ?><a href="<?= site_url('admin/due/' . (int) $r['id']) ?>"><?= official_svg('pencil') ?>Ubah / catat pembayaran</a><?php endif; ?>
        <a href="<?= $url(array('periode' => $r['period'])) ?>"><?= official_svg('calendar') ?>Tunggakan periode ini</a>
       </div></details>
      </div></td>
     </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="pg-empty"><?= ($bucket || $period) ? 'Tidak ada tunggakan untuk filter ini.' : 'Tidak ada tunggakan. Semua tagihan periode lalu sudah lunas.' ?></td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($rows && $can_remind): ?><div class="pg-foot pg-foot-in"><p><b id="due-count">0</b> tagihan dipilih · pengingat ke tagihan yang sama maksimal 1× per 3 hari</p><button class="pg-btn solid small" type="submit" id="due-bulk" disabled><?= official_svg('send') ?>Kirim Pengingat ke Terpilih</button></div><?php endif; ?>
   </form>
  </section>

  <section class="pg-card pg-panel pg-dist">
   <div class="pg-form-head"><span class="pg-form-ic plain"><?= official_svg('chart') ?></span><div><h2>Distribusi Tunggakan</h2><p>Sebaran jumlah warga berdasarkan keterlambatan terlamanya.</p></div></div>
   <?php $sum = max(1, array_sum($distribution)); $circ = 2 * M_PI * 46; $offset = 0; ?>
   <div class="pg-donut-wrap">
    <svg class="pg-donut" viewBox="0 0 120 120" role="img" aria-label="Distribusi <?= (int) $debtors ?> warga menunggak"><circle cx="60" cy="60" r="46" fill="none" stroke="#eef1f5" stroke-width="16"/>
     <?php foreach ($distribution as $k => $n): if (!$n) continue; $len = $circ * $n / $sum; ?><circle cx="60" cy="60" r="46" fill="none" stroke="<?= $colors[$k] ?>" stroke-width="16" stroke-dasharray="<?= round($len, 2) ?> <?= round($circ, 2) ?>" stroke-dashoffset="<?= round(-$offset, 2) ?>" transform="rotate(-90 60 60)"><title><?= ops_e($buckets[$k]) ?>: <?= (int) $n ?> warga</title></circle><?php $offset += $len; endforeach; ?>
     <text x="60" y="58" class="n"><?= (int) $debtors ?></text><text x="60" y="76" class="l">Warga</text></svg>
    <ul class="pg-dist-legend"><?php foreach ($buckets as $k => $label): $n = (int) $distribution[$k]; ?><li><i style="background:<?= $colors[$k] ?>"></i><b><?= ops_e($label) ?></b><span><?= $n ?> warga (<?= array_sum($distribution) ? (int) round(100 * $n / $sum) : 0 ?>%)</span></li><?php endforeach; ?></ul>
   </div>
   <?php if ($per_citizen): ?><h3 class="pg-sub">Tunggakan terbesar per warga</h3><ul class="pg-rank"><?php foreach (array_slice($per_citizen, 0, 5, TRUE) as $c): ?><li><span><?= ops_e($c['name']) ?><small><?= (int) $c['count'] ?> tagihan · terlama <?= (int) $c['oldest'] ?> hari</small></span><b><?= ops_money($c['total']) ?></b></li><?php endforeach; ?></ul><?php endif; ?>
  </section>
 </div>
</div>
<?php if ($can_remind): ?>
<script>
(function(){
 var all=document.getElementById('due-all'),boxes=[].slice.call(document.querySelectorAll('input[name="due_ids[]"]')),cnt=document.getElementById('due-count'),bulk=document.getElementById('due-bulk');
 function sync(){var n=boxes.filter(function(b){return b.checked}).length;if(cnt)cnt.textContent=n;if(bulk)bulk.disabled=!n;if(all){all.checked=n&&n===boxes.length;all.indeterminate=n>0&&n<boxes.length}}
 if(all)all.addEventListener('change',function(){boxes.forEach(function(b){b.checked=all.checked});sync()});
 boxes.forEach(function(b){b.addEventListener('change',sync)});sync();
})();
</script>
<?php endif; ?>
