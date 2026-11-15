<?php
/*
 * Detail Agenda — desain merah portal warga.
 * Warga yang login: header merah dua tingkat (citizen/shell_header, menu "Agenda" aktif).
 * Pengunjung umum: header situs biasa. Semua teks berasal dari data agenda & Pengaturan admin.
 */
$is_citizen = (bool) $this->session->userdata('citizen_id');
$title = $item['title'] . ' | Agenda';
$citizen_shell = TRUE; $shell_active = 'events'; // header merah juga untuk tamu (mode tamu)
$this->load->view('site/header', compact('title', 'settings', 'menu', 'citizen_shell', 'shell_active'));

$labels = array('kegiatan' => 'Kegiatan', 'kerja-bakti' => 'Kerja Bakti', 'lomba' => 'Lomba', 'sosial' => 'Sosial', 'lainnya' => 'Lainnya');
$months = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$ts = strtotime($item['event_date'] ?: $item['created_at']);
$date_long = date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
$date_short = date('j', $ts) . ' ' . substr($months[(int) date('n', $ts)], 0, 3) . ' ' . date('Y', $ts);
$time = $item['event_time'] ? substr($item['event_time'], 0, 5) . ' WIB' : '';
$place = trim((string) $item['event_location']);
$rt_label = trim((string) ($settings['brand_label'] ?? 'RT 009/008'));
$safe = nl2br(preg_replace('/\*\*(.+?)\*\*/us', '<strong>$1</strong>', html_escape($item['body'])));
// Subjudul: kalimat pertama isi agenda (tanpa format), maksimal ±150 karakter.
$plain = trim(preg_replace('/\s+/', ' ', str_replace('**', '', (string) $item['body'])));
$summary = preg_match('/^(.{20,150}?[.!?])(\s|$)/u', $plain, $mm) ? $mm[1] : (mb_strlen($plain) > 150 ? rtrim(mb_substr($plain, 0, 147)) . '…' : $plain);
// Judul dua warna: kata di tengah berwarna merah (mis. "Bazar UMKM Warga").
$words = preg_split('/\s+/u', trim($item['title']));
$n = count($words);
$title_html = $n >= 3
    ? html_escape($words[0]) . ' <em>' . html_escape(implode(' ', array_slice($words, 1, $n - 2))) . '</em> ' . html_escape($words[$n - 1])
    : ($n === 2 ? html_escape($words[0]) . ' <em>' . html_escape($words[1]) . '</em>' : html_escape($item['title']));
$ic = function ($d, $s = 20) { return '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>'; };
$I = array(
    'cal' => 'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5M8 14h2M12 14h2M16 14h.01M8 17.5h2M12 17.5h2',
    'clock' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
    'pin' => 'M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0ZM12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
    'people' => 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c.6-3.6 3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c2 .7 3.2 2.5 3.5 5.2',
    'tag' => 'M3 12V4h8l10 10-8 8L3 12zM7.5 7.5h.01',
    'back' => 'M19 12H5M11 18l-6-6 6-6',
    'home' => 'M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10',
    'chev' => 'M9 6l6 6-6 6',
    'mega' => 'M3 10v4l3 .5L17 19V5L6 9.5zM17 9.5a3 3 0 0 1 0 5M7 15l1 5h3l-1-4',
    'store' => 'M3 9l1.5-5h15L21 9M3 9v2a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0V9M3 9h18M5 13v8h14v-8M10 21v-5h4v5',
);
// Ilustrasi kalender merah (SVG, tanpa aset gambar tambahan).
$calendar_art = '<svg class="agx-calart" viewBox="0 0 120 120" aria-hidden="true"><defs><linearGradient id="agx-cr" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff4d5e"/><stop offset="1" stop-color="#c8102e"/></linearGradient><linearGradient id="agx-cw" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#f1f3f8"/></linearGradient></defs><rect x="14" y="22" width="84" height="80" rx="16" fill="url(#agx-cw)" stroke="#e6e9f0" stroke-width="2"/><path d="M14 38a16 16 0 0 1 16-16h52a16 16 0 0 1 16 16v8H14z" fill="url(#agx-cr)"/><g fill="#f3c9cf"><rect x="27" y="56" width="14" height="11" rx="3"/><rect x="48" y="56" width="14" height="11" rx="3"/><rect x="69" y="56" width="14" height="11" rx="3"/><rect x="27" y="74" width="14" height="11" rx="3"/><rect x="48" y="74" width="14" height="11" rx="3"/></g><g fill="none" stroke="#c8102e" stroke-width="6" stroke-linecap="round"><path d="M34 14v16M56 14v16M78 14v16"/></g><circle cx="88" cy="88" r="20" fill="url(#agx-cr)" stroke="#fff" stroke-width="4"/><path d="M79 88l6 6 12-12" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<link rel="stylesheet" href="<?= base_url('assets/agenda-detail.css') ?>?v=<?= filemtime(FCPATH . 'assets/agenda-detail.css') ?>">
<main class="agx<?= $is_citizen ? ' agx--shell' : '' ?>">
 <section class="agx-hero">
  <span class="agx-hero-bg" aria-hidden="true"><img src="<?= base_url('assets/img/dashboard/footer-banner-v2.jpg') ?>" alt="" decoding="async"></span>
  <div class="agx-wrap">
   <div class="agx-top">
    <a class="agx-back" href="<?= site_url('agenda') ?>"><?= $ic($I['back']) ?>Kembali</a>
    <nav class="agx-crumb" aria-label="Breadcrumb"><a href="<?= site_url($is_citizen ? 'warga' : '') ?>"><?= $ic($I['home'], 17) ?>Beranda</a><i aria-hidden="true">›</i><a href="<?= site_url('agenda') ?>">Agenda</a><i aria-hidden="true">›</i><span aria-current="page">Detail Agenda</span></nav>
   </div>
   <div class="agx-head">
    <span class="agx-head-art"><?= $calendar_art ?></span>
    <div class="agx-head-copy">
     <h1><?= $title_html ?></h1>
     <?php if ($summary !== ''): ?><p><?= html_escape($summary) ?></p><?php endif; ?>
     <ul class="agx-chips">
      <li><?= $ic($I['cal']) ?><?= html_escape($date_short) ?></li>
      <?php if ($time): ?><li><?= $ic($I['clock']) ?><?= html_escape($time) ?></li><?php endif; ?>
      <?php if ($place !== ''): ?><li><?= $ic($I['pin']) ?><?= html_escape($place) ?></li><?php endif; ?>
     </ul>
    </div>
   </div>
  </div>
 </section>

 <div class="agx-wrap agx-grid">
  <article class="agx-card agx-main">
   <figure class="agx-photo<?= $item['image'] ? '' : ' is-empty' ?>">
    <?php if ($item['image']): ?><img src="<?= base_url(html_escape($item['image'])) ?>" alt="<?= html_escape($item['title']) ?>"><?php else: ?><span class="agx-photo-ph"><?= $calendar_art ?></span><?php endif; ?>
    <figcaption class="agx-ribbon"><?= $ic($I['mega'], 22) ?>Agenda <?= html_escape($labels[$category] ?? 'Resmi') ?> RT</figcaption>
    <svg class="agx-photo-wave" viewBox="0 0 900 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 60V38C180 6 360 52 560 40S820 6 900 18v42Z" fill="#fff"/><path d="M0 44C190 14 370 58 560 46S820 14 900 26v6C820 22 740 50 560 54S180 24 0 52Z" fill="#d9002c"/></svg>
   </figure>
   <div class="agx-foot">
    <div class="agx-desc"><span class="agx-desc-ic"><?= $ic($I['store'], 24) ?></span><div class="agx-body"><?= $safe ?></div></div>
    <a class="agx-btn" href="<?= site_url('agenda') ?>"><?= $ic($I['back']) ?>Kembali ke Agenda</a>
   </div>
  </article>

  <aside class="agx-side">
   <section class="agx-card agx-info" aria-labelledby="agx-info-t">
    <h2 class="agx-side-head" id="agx-info-t"><?= $ic($I['cal'], 24) ?>Informasi Agenda</h2>
    <div class="agx-info-body">
     <dl>
      <div><dt><?= $ic($I['cal'], 19) ?>Tanggal</dt><dd><?= html_escape($date_long) ?></dd></div>
      <?php if ($time): ?><div><dt><?= $ic($I['clock'], 19) ?>Waktu</dt><dd><?= html_escape($time) ?></dd></div><?php endif; ?>
      <div><dt><?= $ic($I['pin'], 19) ?>Lokasi</dt><dd><?= html_escape($place !== '' ? $place : 'Akan diinformasikan') ?></dd></div>
      <div><dt><?= $ic($I['people'], 19) ?>Peserta</dt><dd>Seluruh Warga <?= html_escape($rt_label) ?></dd></div>
      <div><dt><?= $ic($I['tag'], 19) ?>Kategori</dt><dd><?= html_escape($labels[$category] ?? 'Lainnya') ?></dd></div>
     </dl>
     <span class="agx-info-art"><?= $calendar_art ?></span>
    </div>
   </section>

   <section class="agx-card agx-more" aria-labelledby="agx-more-t">
    <div class="agx-side-head"><h2 id="agx-more-t"><?= $ic($I['cal'], 24) ?>Agenda Lainnya</h2><a href="<?= site_url('agenda') ?>">Lihat Semua<?= $ic($I['chev'], 14) ?></a></div>
    <div class="agx-more-list">
     <?php foreach ($related as $other): $ots = strtotime($other['event_date'] ?: $other['created_at']); ?>
     <a href="<?= site_url('agenda/' . (int) $other['id']) ?>"><time datetime="<?= date('Y-m-d', $ots) ?>"><b><?= date('d', $ots) ?></b><small><?= substr($months[(int) date('n', $ots)], 0, 3) ?></small></time><strong><?= html_escape($other['title']) ?></strong><?= $ic($I['chev'], 18) ?></a>
     <?php endforeach; ?>
     <?php if (!$related): ?><p class="agx-empty">Belum ada agenda lainnya.</p><?php endif; ?>
    </div>
   </section>
  </aside>
 </div>

 <div class="agx-deco" aria-hidden="true">
  <svg class="agx-deco-town" viewBox="0 0 360 70"><g fill="#f6b3c0"><path d="M10 70V44l16-12 16 12v26zM22 70V58h8v12z"/><circle cx="58" cy="40" r="12"/><rect x="56" y="48" width="4" height="22"/><path d="M84 70V40h4V26h2V16l2-6 2 6v10h2v14h4v30z"/><path d="M110 70V46c0-14 12-22 22-22s22 8 22 22v24z"/><path d="M128 24c0-6 4-10 4-14 0 4 4 8 4 14z"/><path d="M160 70V40h4V28h2V18l2-6 2 6v10h2v12h4v30z"/><circle cx="196" cy="42" r="11"/><rect x="194" y="50" width="4" height="20"/><path d="M214 70V48l20-14 20 14v22z"/><path d="M262 70V52l14-10 14 10v18z"/><circle cx="306" cy="44" r="10"/><rect x="304" y="50" width="4" height="20"/><circle cx="330" cy="48" r="8"/><rect x="328" y="54" width="4" height="16"/></g></svg>
  <svg class="agx-deco-wave" viewBox="0 0 1600 90" preserveAspectRatio="none"><path d="M0 90V52C260 20 520 70 820 48S1360 6 1600 30v60Z" fill="#c8102e"/><path d="M0 60C260 28 520 78 820 56S1360 14 1600 38v10C1360 26 1100 66 820 70S260 44 0 74Z" fill="#fff" opacity=".9"/><path d="M0 76C280 46 540 92 840 70s520-38 760-20v40H0Z" fill="#a8001d"/></svg>
 </div>
</main>
<?php $this->load->view('site/footer', array('settings' => $settings, 'citizen_shell' => FALSE)); ?>
