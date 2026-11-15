<?php
/*
 * Dashboard admin v7 (redesign desktop "soft 3D").
 * Semua angka dari controller (data operasional). Tidak ada angka contoh dari mockup.
 * Kartu tampil hanya bila menu/izin/fitur terkait aktif untuk peran ini ($menu_ok, $show_*).
 */
$role = $this->session->userdata('admin_role');
$hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
$bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$short_month = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$money = function ($v) { return 'Rp ' . number_format((float) $v, 0, ',', '.'); };
$num = function ($v) { return number_format((int) $v, 0, ',', '.'); };
$fon = function ($code) use ($feature_on) { return !isset($feature_on[$code]) || $feature_on[$code]; };
$ok = function ($path) use ($menu_ok) { return !empty($menu_ok[$path]); };
$admin_name = trim((string) ($admin_user['username'] ?? '')) !== '' ? ucfirst($admin_user['username']) : 'Admin RT';
$today_label = $hari[(int) date('w')] . ', ' . (int) date('j') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

// Header: ilustrasi khusus dashboard bila sudah disimpan pemilik; jika belum, pakai Gambar Halaman yang ada.
$hero_file = 'assets/img/dashboard/admin-hero-3d.png';
$hero = is_file(FCPATH . $hero_file) ? $hero_file . '?v=' . filemtime(FCPATH . $hero_file) : ($settings['dashboard_page_image'] ?? 'assets/mockup/home-hero-v4.png');

// Total warga = anggota penduduk aktif (Data Penduduk); fallback akun warga aktif bila modul penduduk tidak tersedia.
$resident_total = $resident_stats ? (int) $resident_stats['members'] : ($citizen_count === NULL ? NULL : (int) $citizen_count);
$resident_delta = NULL;
if ($resident_stats && count($resident_stats['points']) >= 2) {
    $pts = $resident_stats['points'];
    $resident_delta = (int) $pts[count($pts) - 1]['members'] - (int) $pts[count($pts) - 2]['members'];
}
$complaint_open = (int) ($ops['complaints_open'] ?? 0);
$delta = function ($n, $good_up = TRUE) {
    if ($n === NULL || $n === 0) return '';
    $up = $n > 0;
    return '<b class="db7-delta ' . (($up === $good_up) ? 'good' : 'bad') . '">' . ($up ? '↑' : '↓') . ' ' . ($up ? '+' : '−') . abs($n) . '</b>';
};

// Soft-3D icon set (SVG bergradasi, gradien didefinisikan sekali di <defs>).
$icon = function ($name) {
    $s = array(
        'people' => '<circle cx="15" cy="15" r="6.5" fill="url(#g7-green2)"/><circle cx="33" cy="15" r="6.5" fill="url(#g7-green2)"/><path d="M4 34c0-7 5-11 11-11s11 4 11 11v3H4z" fill="url(#g7-green2)"/><path d="M22 34c0-7 5-11 11-11s11 4 11 11v3H22z" fill="url(#g7-green2)"/><circle cx="24" cy="18" r="8" fill="url(#g7-green)"/><path d="M11 40c0-8 6-13 13-13s13 5 13 13v3H11z" fill="url(#g7-green)"/><ellipse cx="21" cy="14" rx="3" ry="2" fill="#fff" opacity=".45"/>',
        'house' => '<path d="M9 22h30v20H9z" fill="url(#g7-cream)"/><path d="M4 24 24 6l20 18-4 3L24 13 8 27z" fill="url(#g7-orange)"/><path d="M20 30h8v12h-8z" fill="url(#g7-orange)"/><path d="M12 28h6v6h-6zM30 28h6v6h-6z" fill="#ffd7b0"/><path d="M8 20 24 6" stroke="#fff" stroke-width="2" opacity=".5"/>',
        'check' => '<circle cx="24" cy="24" r="19" fill="url(#g7-blue)"/><ellipse cx="19" cy="14" rx="9" ry="5" fill="#fff" opacity=".28"/><path d="m15 24 6 6 12-13" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>',
        'doc' => '<path d="M11 5h18l9 9v29H11z" fill="url(#g7-purple)"/><path d="M29 5v9h9z" fill="#c9b6ff"/><path d="M17 22h15M17 28h15M17 34h10" stroke="#fff" stroke-width="3" stroke-linecap="round"/><path d="M13 7h10" stroke="#fff" stroke-width="2" opacity=".4"/>',
        'megaphone' => '<path d="M6 19h8l20-10v30L14 29H6z" fill="url(#g7-green)"/><path d="M34 9c4 3 6 9 6 15s-2 12-6 15z" fill="url(#g7-green2)"/><path d="M10 29h7l3 11h-6z" fill="url(#g7-orange)"/><path d="M8 21h6" stroke="#fff" stroke-width="2" opacity=".5"/>',
        'coins' => '<ellipse cx="18" cy="36" rx="12" ry="5" fill="#d9921a"/><path d="M6 30v6c0 3 5 5 12 5s12-2 12-5v-6z" fill="url(#g7-gold)"/><ellipse cx="18" cy="30" rx="12" ry="5" fill="#ffd45a"/><path d="M18 22v6c0 3 5 5 12 5s12-2 12-5v-6z" fill="url(#g7-gold)"/><ellipse cx="30" cy="22" rx="12" ry="5" fill="#ffd45a"/><path d="M18 14v8c0 3 5 5 12 5s12-2 12-5v-8z" fill="url(#g7-gold)"/><ellipse cx="30" cy="14" rx="12" ry="5" fill="#ffe17d"/><ellipse cx="27" cy="13" rx="5" ry="1.6" fill="#fff" opacity=".6"/>',
        'chart' => '<rect x="6" y="26" width="9" height="16" rx="2" fill="url(#g7-orange)"/><rect x="19" y="16" width="9" height="26" rx="2" fill="url(#g7-orange)"/><rect x="32" y="8" width="9" height="34" rx="2" fill="url(#g7-orange)"/>',
        'wallet' => '<rect x="5" y="13" width="38" height="27" rx="6" fill="url(#g7-orange)"/><path d="M9 13 31 6l3 7z" fill="#ffb27a"/><rect x="29" y="22" width="14" height="10" rx="4" fill="#ffd2ad"/><circle cx="35" cy="27" r="2.4" fill="#e9601b"/>',
        'calendar' => '<rect x="6" y="9" width="36" height="33" rx="6" fill="url(#g7-cream)"/><path d="M6 15a6 6 0 0 1 6-6h24a6 6 0 0 1 6 6v5H6z" fill="url(#g7-orange)"/><path d="M15 5v8M33 5v8" stroke="#8a4a1d" stroke-width="3" stroke-linecap="round"/><path d="M13 27h5M22 27h5M31 27h5M13 34h5M22 34h5" stroke="#f2a26b" stroke-width="3" stroke-linecap="round"/>',
        'bolt' => '<path d="M27 4 10 27h12l-3 17 18-24H25z" fill="url(#g7-green)"/>',
        'task' => '<path d="M10 6h28v36H10z" rx="4" fill="url(#g7-orange)"/><path d="M17 17h14M17 24h14M17 31h9" stroke="#fff" stroke-width="3" stroke-linecap="round"/>',
        'payment' => '<rect x="5" y="11" width="38" height="26" rx="5" fill="url(#g7-orange)"/><rect x="5" y="16" width="38" height="5" fill="#c4470f"/><rect x="10" y="26" width="12" height="4" rx="2" fill="#ffe0c7"/>',
        'kk' => '<rect x="6" y="9" width="36" height="30" rx="5" fill="url(#g7-blue)"/><circle cx="17" cy="21" r="5" fill="#dff1ff"/><path d="M10 33c1-5 4-7 7-7s6 2 7 7z" fill="#dff1ff"/><path d="M28 19h9M28 25h9M28 31h6" stroke="#fff" stroke-width="3" stroke-linecap="round"/>',
        'swap' => '<circle cx="24" cy="24" r="19" fill="url(#g7-purple)"/><path d="M14 19h18l-5-5M34 29H16l5 5" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>',
    );
    return '<svg class="db7-3d" viewBox="0 0 48 48" aria-hidden="true">' . ($s[$name] ?? $s['task']) . '</svg>';
};

$kpis = array();
if ($resident_total !== NULL) $kpis[] = array('green', 'people', 'Total Warga', $num($resident_total), $delta($resident_delta), $resident_stats ? 'penduduk aktif' : 'akun warga aktif', $ok('admin/penduduk') ? 'admin/penduduk' : ($ok('admin/keluarga') ? 'admin/keluarga' : ''));
if ($house_count !== NULL) $kpis[] = array('orange', 'house', 'Rumah', $num($house_count), '', 'terdaftar', 'admin/rumah');
if ($kk_pending !== NULL) $kpis[] = array('blue', 'check', 'KK Perlu Verifikasi', $num($kk_pending), '', $kk_pending ? 'Lihat Detail →' : 'tidak ada antrean', 'admin/kk/pengajuan?status=pending', $kk_pending > 0);
if ($fon('letters') && $ok('admin/requests')) $kpis[] = array('purple', 'doc', 'Pengajuan Surat', $num($letters_processing), '', 'perlu diproses', 'admin/requests');
if ($fon('complaints') && $ok('admin/pengaduan')) $kpis[] = array('mint', 'megaphone', 'Pengaduan', $num($complaint_open), '', 'belum selesai', 'admin/pengaduan');
if ($show_cash) $kpis[] = array('gold', 'coins', 'Saldo Kas RT', $money($cash_month['balance']), '', 'transaksi terverifikasi', $ok('admin/transactions') ? 'admin/transactions' : '');

// Perlu tindakan: hanya antrean yang memang ada di alur aplikasi, diurutkan dari yang paling mendesak.
$tasks = array();
if ($fon('letters') && $ok('admin/requests') && $waiting_requests) $tasks[] = array('doc', 'Pengajuan surat baru', $waiting_requests . ' menunggu diperiksa', 'Proses', 'admin/requests?status=diajukan');
if (!empty($waiting_payments['n']) && $fon('dues') && $ok('admin/dues')) $tasks[] = array('payment', 'Pembayaran iuran', 'Perlu verifikasi (' . (int) $waiting_payments['n'] . ' pembayaran)', 'Verifikasi', 'admin/dues#pembayaran');
if ($complaints_new) $tasks[] = array('megaphone', 'Pengaduan masuk', $complaints_new . ' belum ditangani', 'Tindak Lanjut', 'admin/pengaduan');
if ($kk_pending) $tasks[] = array('kk', 'Verifikasi data KK', $kk_pending . ' pengajuan menunggu', 'Periksa', 'admin/kk/pengajuan?status=pending');
if ($mutations_pending) $tasks[] = array('swap', 'Mutasi warga', $mutations_pending . ' menunggu keputusan', 'Tinjau', 'admin/mutasi');
$tasks = array_slice($tasks, 0, 5);

// Grafik kas: skala sumbu Y "rapi".
$cash_peak = 0;
foreach ((array) ($cash_month['trend'] ?? array()) as $p) $cash_peak = max($cash_peak, (float) $p['income'], (float) $p['expense']);
$axis_max = 0; $axis_step = 0;
if ($cash_peak > 0) {
    $raw = $cash_peak / 5; $mag = pow(10, floor(log10($raw))); $norm = $raw / $mag;
    $axis_step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 2.5 ? 2.5 : ($norm <= 5 ? 5 : 10)))) * $mag;
    $axis_max = $axis_step * ceil($cash_peak / $axis_step);
}
$short_money = function ($v) {
    $v = (float) $v;
    if ($v >= 1e9) return rtrim(rtrim(number_format($v / 1e9, 1, ',', '.'), '0'), ',') . ' M';
    if ($v >= 1e6) return rtrim(rtrim(number_format($v / 1e6, 1, ',', '.'), '0'), ',') . ' jt';
    if ($v >= 1e3) return rtrim(rtrim(number_format($v / 1e3, 1, ',', '.'), '0'), ',') . ' rb';
    return number_format($v, 0, ',', '.');
};
$period_label = function ($p) use ($bulan) { $t = strtotime($p . '-01'); return $bulan[(int) date('n', $t)] . ' ' . date('Y', $t); };
$keep = function ($skip) { $q = $_GET; unset($q[$skip]); $out = ''; foreach ($q as $k => $v) if (is_string($v) && preg_match('/^[a-z_]{1,20}$/', $k)) $out .= '<input type="hidden" name="' . html_escape($k) . '" value="' . html_escape($v) . '">'; return $out; };
$ago = function ($at) use ($short_month) {
    $t = strtotime((string) $at); if (!$t) return '';
    if (date('Y-m-d', $t) === date('Y-m-d')) return 'Hari ini, ' . date('H:i', $t);
    if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) return 'Kemarin, ' . date('H:i', $t);
    return (int) date('j', $t) . ' ' . $short_month[(int) date('n', $t)] . ' ' . date('Y', $t) . ', ' . date('H:i', $t);
};
$feed_icon = array('letter' => 'doc', 'complaint' => 'megaphone', 'payment' => 'coins', 'kk' => 'kk');
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-dashboard-v7.css') ?>?v=<?= filemtime(FCPATH . 'assets/admin-dashboard-v7.css') ?>">
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>
 <linearGradient id="g7-green" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4fd26b"/><stop offset="1" stop-color="#159447"/></linearGradient>
 <linearGradient id="g7-green2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8fe39a"/><stop offset="1" stop-color="#2fae58"/></linearGradient>
 <linearGradient id="g7-orange" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff9a4d"/><stop offset="1" stop-color="#ec5a12"/></linearGradient>
 <linearGradient id="g7-cream" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff8ee"/><stop offset="1" stop-color="#f6dcc0"/></linearGradient>
 <linearGradient id="g7-blue" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5fb6ff"/><stop offset="1" stop-color="#1769e0"/></linearGradient>
 <linearGradient id="g7-purple" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#a98bff"/><stop offset="1" stop-color="#6b3fe0"/></linearGradient>
 <linearGradient id="g7-gold" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffcc3f"/><stop offset="1" stop-color="#e59a12"/></linearGradient>
</defs></svg>

<main class="db7">
 <section class="db7-hero" style="--hero:url('<?= html_escape(base_url($hero)) ?>')">
  <div class="db7-hero-copy">
   <h1>Selamat Datang,<br><span><?= html_escape($admin_name) ?>!</span> <i aria-hidden="true">👋</i></h1>
   <p>Kelola data warga dan layanan RT dengan mudah</p>
   <span class="db7-date"><?= official_svg('calendar') ?><?= $today_label ?></span>
  </div>
 </section>

 <?php if ($kpis): ?>
 <section class="db7-kpis" aria-label="Ringkasan utama" style="--n:<?= count($kpis) ?>">
  <?php foreach ($kpis as $k): $tag = $k[6] !== '' ? 'a' : 'div'; ?>
   <<?= $tag ?> class="db7-kpi <?= $k[0] ?>"<?= $k[6] !== '' ? ' href="' . site_url($k[6]) . '"' : '' ?>>
    <span class="db7-kpi-ic"><?= $icon($k[1]) ?></span>
    <span class="db7-kpi-tx"><small><?= $k[2] ?></small><strong><?= $k[3] ?><?= $k[4] ?></strong><em<?= !empty($k[7]) ? ' class="cta"' : '' ?>><?= $k[5] ?></em></span>
   </<?= $tag ?>>
  <?php endforeach; ?>
 </section>
 <?php endif; ?>

 <section class="db7-grid">
  <?php if ($iuran): $billed = $iuran['billed']; $lunas_n = $iuran['lunas']; $pc = $billed > 0 ? (int) round(100 * $lunas_n / $billed) : 0; ?>
  <article class="db7-card db7-dues">
   <header><h2><?= $icon('chart') ?>Rekap Iuran Warga</h2>
    <form method="get" class="db7-select"><?= $keep('iuran') ?><label><span class="sr-only">Periode iuran</span><select name="iuran" onchange="this.form.submit()"><?php foreach ($iuran['periods'] as $p): ?><option value="<?= html_escape($p) ?>"<?= $p === $iuran['period'] ? ' selected' : '' ?>><?= $p === date('Y-m') ? 'Bulan Ini' : $period_label($p) ?></option><?php endforeach; ?></select></label><noscript><button type="submit">Terapkan</button></noscript></form>
   </header>
   <?php if ($billed > 0): ?>
   <div class="db7-dues-body">
    <div class="db7-donut" style="--pc:<?= $pc ?>" role="img" aria-label="<?= $pc ?> persen warga lunas"><span><b><?= $pc ?>%</b><small>Lunas</small></span></div>
    <ul class="db7-legend">
     <li><i class="green"></i><span>Sudah Bayar<b><?= $num($lunas_n) ?> warga</b></span></li>
     <li><i class="orange"></i><span>Belum Bayar<b><?= $num($billed - $lunas_n) ?> warga</b></span></li>
     <li><i class="gray"></i><span>Total Ditagih<b><?= $num($billed) ?> warga</b></span></li>
    </ul>
   </div>
   <?php else: ?><p class="db7-empty">Belum ada tagihan iuran untuk <?= $period_label($iuran['period']) ?>.</p><?php endif; ?>
   <span class="db7-coins" aria-hidden="true"><?= $icon('coins') ?></span>
   <?php if ($ok('admin/dues')): ?><a class="db7-more green" href="<?= site_url('admin/dues') ?>">Lihat Detail <?= official_svg('arrow-right') ?></a><?php endif; ?>
  </article>
  <?php endif; ?>

  <?php if ($show_cash): ?>
  <article class="db7-card db7-cash">
   <header><h2><?= $icon('wallet') ?>Grafik Keuangan RT</h2>
    <form method="get" class="db7-select"><?= $keep('kas') ?><label><span class="sr-only">Rentang grafik</span><select name="kas" onchange="this.form.submit()"><option value="6"<?= $cash_span === 6 ? ' selected' : '' ?>>6 Bulan Terakhir</option><option value="12"<?= $cash_span === 12 ? ' selected' : '' ?>>12 Bulan Terakhir</option></select></label><noscript><button type="submit">Terapkan</button></noscript></form>
   </header>
   <div class="db7-legend-row"><span class="in">Pemasukan</span><span class="out">Pengeluaran</span></div>
   <?php if ($axis_max > 0): ?>
   <div class="db7-chart" style="--cols:<?= count($cash_month['trend']) ?>">
    <div class="db7-axis"><?php for ($v = $axis_max; $v >= 0; $v -= $axis_step): ?><span><?= $short_money($v) ?></span><?php endfor; ?></div>
    <div class="db7-plot">
     <?php $peak_i = 0; foreach ($cash_month['trend'] as $i => $p) if ($p['income'] >= $cash_month['trend'][$peak_i]['income']) $peak_i = $i; ?>
     <?php foreach ($cash_month['trend'] as $i => $p): $t = strtotime($p['period'] . '-01'); $tip = $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) . ' — Pemasukan ' . $money($p['income']) . ' · Pengeluaran ' . $money($p['expense']); ?>
      <a class="db7-col" href="<?= $ok('admin/transactions') ? site_url('admin/transactions') : '#' ?>" title="<?= html_escape($tip) ?>" aria-label="<?= html_escape($tip) ?>">
       <?php if ($i === $peak_i && $p['income'] > 0): ?><em class="db7-peak" style="bottom:calc(<?= round(100 * $p['income'] / $axis_max, 1) ?>% + 6px)"><?= html_escape('Rp ' . $short_money($p['income'])) ?></em><?php endif; ?>
       <span class="in" style="height:<?= round(100 * $p['income'] / $axis_max, 1) ?>%"></span><span class="out" style="height:<?= round(100 * $p['expense'] / $axis_max, 1) ?>%"></span>
       <small><?= $short_month[(int) date('n', $t)] ?></small>
      </a>
     <?php endforeach; ?>
    </div>
   </div>
   <?php else: ?><p class="db7-empty">Belum ada transaksi kas terverifikasi pada <?= $cash_span ?> bulan terakhir.</p><?php endif; ?>
  </article>
  <?php endif; ?>

  <article class="db7-card db7-tasks">
   <header class="db7-tasks-head"><h2><?= $icon('task') ?>Perlu Tindakan Hari Ini</h2><?php if ($tasks): ?><b class="db7-count"><?= count($tasks) ?></b><?php endif; ?></header>
   <?php if ($tasks): ?><ul>
    <?php foreach ($tasks as $t): ?><li><a href="<?= site_url($t[4]) ?>"><span class="db7-task-ic"><?= $icon($t[0]) ?></span><span class="db7-task-tx"><b><?= html_escape($t[1]) ?></b><small><?= html_escape($t[2]) ?></small></span><em><?= html_escape($t[3]) ?></em></a></li><?php endforeach; ?>
   </ul><?php else: ?><p class="db7-empty">Tidak ada pekerjaan tertunda. 🎉</p><?php endif; ?>
  </article>

  <?php if ($ok('admin/posts/agenda')): ?>
  <article class="db7-card db7-agenda">
   <header><h2><?= $icon('calendar') ?>Agenda Terdekat</h2><a class="db7-link" href="<?= site_url('admin/posts/agenda') ?>">Lihat Semua <?= official_svg('arrow-right') ?></a></header>
   <?php if ($agenda_next): ?><ul>
    <?php foreach ($agenda_next as $a): $at = strtotime($a['event_date']); ?><li><a href="<?= site_url('admin/post/agenda/' . (int) $a['id']) ?>"><span class="db7-day"><b><?= date('d', $at) ?></b><small><?= $short_month[(int) date('n', $at)] ?></small></span><span class="db7-agenda-tx"><b><?= html_escape($a['title']) ?></b><small><?= $a['event_time'] ? html_escape(substr($a['event_time'], 0, 5)) . ' WIB' : 'Waktu belum diisi' ?></small><?php if ($a['event_location'] !== ''): ?><small class="loc"><?= official_svg('pin') ?><?= html_escape($a['event_location']) ?></small><?php endif; ?></span></a></li><?php endforeach; ?>
   </ul><?php else: ?><p class="db7-empty">Belum ada agenda mendatang yang dipublikasikan.</p><?php endif; ?>
   <span class="db7-cal-art" aria-hidden="true"><?= $icon('calendar') ?></span>
  </article>
  <?php endif; ?>

  <article class="db7-card db7-activity">
   <header><h2><?= $icon('bolt') ?>Aktivitas Terbaru</h2><?php if ($ok('admin/requests')): ?><a class="db7-link" href="<?= site_url('admin/requests?status=semua') ?>">Lihat Semua <?= official_svg('arrow-right') ?></a><?php endif; ?></header>
   <?php if ($activity): ?><ol>
    <?php foreach ($activity as $a): ?><li><a href="<?= site_url($a['url']) ?>"><span class="db7-act-ic"><?= $icon($feed_icon[$a['kind']] ?? 'task') ?></span><span><b><?= html_escape($a['title']) ?></b><small><?= $ago($a['at']) ?></small></span></a></li><?php endforeach; ?>
   </ol><?php else: ?><p class="db7-empty">Belum ada aktivitas.</p><?php endif; ?>
  </article>

  <?php $shortcuts = array_filter(array(
      array('people', 'Data Warga', $ok('admin/keluarga') ? 'admin/keluarga' : ($ok('admin/penduduk') ? 'admin/penduduk' : '')),
      array('coins', 'Iuran Warga', $show_dues && $ok('admin/dues') ? 'admin/dues' : ''),
      array('wallet', 'Kas RT', $show_cash && $ok('admin/transactions') ? 'admin/transactions' : ''),
      array('megaphone', 'Pengaduan', $fon('complaints') && $ok('admin/pengaduan') ? 'admin/pengaduan' : ''),
  ), function ($s) {return $s[2] !== '';}); ?>
  <?php if ($shortcuts): ?>
  <details class="db7-card db7-reports" open>
   <summary><h2><?= $icon('chart') ?>Laporan &amp; Statistik</h2><span class="db7-chev"><?= official_svg('chev-right') ?></span></summary>
   <div><?php foreach ($shortcuts as $s): ?><a href="<?= site_url($s[2]) ?>"><?= $icon($s[0]) ?><span><?= $s[1] ?></span></a><?php endforeach; ?></div>
  </details>
  <?php endif; ?>
 </section>
</main>
