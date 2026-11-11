<?php $qs = function ($extra) use ($f) { return http_build_query(array_filter(array('q' => $f['q'], 'admin' => $f['admin'] ?: '', 'dari' => $f['from'], 'sampai' => $f['to'])) + $extra); }; ?>
<div class="ops ops-audit">
<?= ops_hero('Pengaturan Sistem', 'Audit Aktivitas', 'Jejak aksi penting: siapa, apa, kapan, dan dari mana. NIK/nomor KK dimasking dan rahasia tidak dicatat. Log hanya ditambah, tidak diubah dari aplikasi.', array(array('admin/audit?' . $qs(array('export' => 'csv')), 'Ekspor CSV', 'outline')), 'shield') ?>
<?php
$today=new DateTimeImmutable('today');
$quick_ranges=array(
 'Hari Ini'=>array($today,$today),
 '7 Hari Terakhir'=>array($today->modify('-6 days'),$today),
 '30 Hari Terakhir'=>array($today->modify('-29 days'),$today),
 'Bulan Ini'=>array($today->modify('first day of this month'),$today),
 'Bulan Lalu'=>array($today->modify('first day of last month'),$today->modify('last day of last month'))
);
?>
<details class="ops-card audit-filter" open><summary><span class="audit-filter-icon" aria-hidden="true"><?= official_svg('filter') ?></span><span><strong>Filter &amp; Pencarian</strong><small>Temukan riwayat aktivitas berdasarkan pengguna, waktu, dan kata kunci.</small></span><span class="audit-filter-mode">Mode Filter <i aria-hidden="true"></i></span></summary>
<form method="get" action="<?= site_url('admin/audit') ?>" class="audit-form">
 <label class="audit-search"><span class="sr-only">Cari log</span><?= official_svg('search') ?><input type="search" name="q" value="<?= ops_e($f['q']) ?>" placeholder="Cari aksi, objek, atau detail..."></label>
 <label class="audit-user"><span class="sr-only">Pengguna</span><?= official_svg('person') ?><select name="admin"><?= ops_options($admins,$f['admin']?:'','Semua pengguna') ?></select></label>
 <fieldset class="audit-period"><legend>Periode</legend><label><span class="sr-only">Dari tanggal</span><input type="date" name="dari" value="<?= ops_e($f['from']) ?>"></label><span aria-hidden="true">&ndash;</span><label><span class="sr-only">Sampai tanggal</span><input type="date" name="sampai" value="<?= ops_e($f['to']) ?>"></label></fieldset>
 <nav class="audit-ranges" aria-label="Rentang tanggal cepat"><strong>Rentang Cepat:</strong><?php foreach($quick_ranges as $label=>$range):$from=$range[0]->format('Y-m-d');$to=$range[1]->format('Y-m-d');$active=$f['from']===$from&&$f['to']===$to;$url=http_build_query(array_filter(array('q'=>$f['q'],'admin'=>$f['admin']?:'','dari'=>$from,'sampai'=>$to))); ?><a class="<?= $active?'is-active':'' ?>" href="<?= site_url('admin/audit?'.$url) ?>"<?= $active?' aria-current="true"':'' ?>><?= official_svg('calendar') ?><?= ops_e($label) ?></a><?php endforeach; ?></nav>
 <div class="audit-filter-actions"><a class="ops-btn ghost" href="<?= site_url('admin/audit') ?>"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 10a9 9 0 1 1 1 7M3 4v6h6"/></svg>Reset</a><button class="ops-btn" type="submit"><?= official_svg('filter') ?>Terapkan Filter</button></div>
</form></details>
<section class="ops-card"><header><h2>Hasil Aktivitas</h2><span class="ops-badge">Total <?= number_format($total,0,',','.') ?> aktivitas</span></header><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Objek</th><th>Detail</th><th>IP</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td data-label="Waktu"><?= ops_date($r['created_at'], TRUE) ?></td><td data-label="Pengguna"><?= (int) $r['admin_id'] ? ops_e($r['username'] ?: '#' . $r['admin_id']) : '<span class="ops-muted">Warga / sistem</span>' ?></td>
 <td data-label="Aksi"><?= ops_badge($r['action'],array(),array('update'=>'info','create'=>'ok','view'=>'ok','export'=>'purple','delete'=>'bad')) ?></td><td data-label="Objek"><?= ops_e($r['subject']) ?></td><td data-label="Detail"><?= ops_e($r['details'] ?? '') ?></td><td data-label="IP"><?= ops_e($r['ip_address'] ?? '') ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6" class="ops-empty"><?= ops_empty_state('Tidak ada aktivitas','Tidak ada log untuk filter yang dipilih.','shield') ?></td></tr><?php endif; ?>
</tbody></table></div>
<footer class="ops-actions" style="justify-content:space-between;margin-top:10px"><span class="ops-muted"><?= number_format($total, 0, ',', '.') ?> entri · halaman <?= $page ?> dari <?= $pages ?></span><span class="ops-actions"><?php if ($page > 1): ?><a class="ops-btn ghost small" href="<?= site_url('admin/audit?' . $qs(array('hal' => $page - 1))) ?>">‹ Sebelumnya</a><?php endif; ?><?php if ($page < $pages): ?><a class="ops-btn ghost small" href="<?= site_url('admin/audit?' . $qs(array('hal' => $page + 1))) ?>">Berikutnya ›</a><?php endif; ?></span></footer>
</section>
</div>
