<?php
$o = function ($k, $d = '') use ($old) { return $old[$k] ?? $d; };
$base = site_url('admin/serah-terima');
$status_ui = array('draft' => array('Draf', 'clock', 'draft'), 'review' => array('Ditinjau', 'hourglass', 'review'), 'final' => array('Selesai', 'check', 'final'));
$person_select = function ($name, $placeholder) use ($people, $o) {
    $sel = $o($name);
    $h = '<select id="f-' . $name . '" name="' . $name . '" required data-hv-person><option value="">' . ops_e($placeholder) . '</option>';
    foreach ($people as $k => $label) $h .= '<option value="' . ops_e($k) . '"' . ($sel === $k ? ' selected' : '') . '>' . ops_e($label) . '</option>';
    $h .= '<option value="__other"' . ($sel === '__other' ? ' selected' : '') . '>Lainnya (isi manual)…</option></select>';
    $h .= '<input type="text" class="hv-other" name="' . $name . '_other" maxlength="255" value="' . ops_e($o($name . '_other')) . '" placeholder="Nama pengurus" aria-label="Nama ' . ops_e(strtolower($placeholder)) . ' (manual)"' . ($sel === '__other' ? '' : ' hidden') . '>';
    return $h;
};
$from = $total ? ($page - 1) * 10 + 1 : 0;
?>
<div class="ops ops-handover hv">
<?= ops_hero('Pengaturan Sistem', 'Serah Terima Pengurus', 'Catat pergantian pengurus lama ke pengurus baru dengan proses yang tertata.', array(), 'people') ?>
<?= ops_flash() ?>

<ol class="hv-steps" aria-label="Tahap serah terima">
 <li class="active"><b>1</b><span><strong>Buat Sesi</strong><small>Isi data pengurus lama &amp; baru</small></span></li>
 <li><b>2</b><span><strong>Lengkapi Data</strong><small>Periksa arsip &amp; lampiran</small></span></li>
 <li><b>3</b><span><strong>Finalisasi</strong><small>Simpan dan distribusikan berita acara</small></span></li>
</ol>

<div class="hv-grid">
 <section class="ops-card hv-card">
  <header class="hv-head"><h2>Daftar Sesi Serah Terima</h2>
   <form method="get" action="<?= $base ?>" class="hv-period"><?= official_svg('calendar') ?><select name="period" aria-label="Filter periode" data-hv-autosubmit><option value="">Semua Periode</option><?php foreach ($periods as $p): ?><option value="<?= ops_e($p) ?>"<?= $period === $p ? ' selected' : '' ?>><?= ops_e($p) ?></option><?php endforeach; ?></select><noscript><button type="submit">OK</button></noscript></form>
  </header>
  <div class="hv-table-wrap"><table class="hv-table"><thead><tr><th>Periode</th><th>Pengurus Lama</th><th>Pengurus Baru</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($items as $s): $st = $status_ui[$s['status']] ?? array(ucfirst($s['status']), 'clock', 'draft'); $url = site_url('admin/serah-terima/' . (int) $s['id']); ?>
   <tr>
    <td data-label="Periode"><strong title="<?= ops_e($s['title']) ?>"><?= ops_e($s['period_label'] ?: 'Belum diisi') ?></strong><small><?= ops_e($s['title']) ?></small></td>
    <td data-label="Pengurus Lama"><?= ops_e($s['outgoing'] ?: '—') ?></td>
    <td data-label="Pengurus Baru"><?= ops_e($s['incoming'] ?: '—') ?></td>
    <td data-label="Status"><span class="hv-status <?= $st[2] ?>"><?= official_svg($st[1]) ?><?= $st[0] ?></span></td>
    <td data-label="Aksi"><div class="hv-actions">
     <a class="hv-icon-btn" href="<?= $url ?>" title="<?= $s['status'] === 'final' ? 'Lihat berita acara' : 'Lengkapi data' ?>" aria-label="<?= $s['status'] === 'final' ? 'Lihat' : 'Lengkapi' ?> sesi <?= ops_e($s['title']) ?>"><?= official_svg($s['status'] === 'final' ? 'eye' : 'pencil') ?></a>
     <details class="ops-row-menu hv-more"><summary aria-label="Aksi lain sesi <?= ops_e($s['title']) ?>"><?= official_svg('more-v') ?></summary><nav>
      <a href="<?= $url ?>"><?= $s['status'] === 'final' ? 'Lihat berita acara' : 'Lengkapi data & checklist' ?></a>
      <?php if ($s['status'] === 'draft' && $can('serahterima.write')): ?><form method="post" action="<?= $base ?>"><?= ops_csrf() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button type="submit" data-hv-confirm="Hapus sesi draf &quot;<?= ops_e($s['title']) ?>&quot;?">Hapus sesi draf</button></form><?php endif; ?>
     </nav></details>
    </div></td>
   </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="5" class="hv-none"><?= official_svg('people') ?><strong><?= $period !== '' ? 'Tidak ada sesi pada periode ini.' : 'Belum ada sesi serah terima.' ?></strong><span><?= $period !== '' ? '<a href="' . $base . '">Tampilkan semua periode</a>' : 'Buat sesi untuk mencatat pergantian pengurus.' ?></span></td></tr><?php endif; ?>
  </tbody></table></div>
  <footer class="hv-foot"><span>Menampilkan <?= count($items) ?> dari <?= (int) $total ?> sesi</span>
   <nav class="hv-pager" aria-label="Halaman sesi">
    <?php $q = function ($p) use ($period) {return '?' . http_build_query(array_filter(array('period' => $period, 'page' => $p > 1 ? $p : NULL)));}; ?>
    <?php if ($page > 1): ?><a href="<?= $base . $q($page - 1) ?>" aria-label="Sebelumnya"><?= official_svg('back') ?></a><?php else: ?><span class="off"><?= official_svg('back') ?></span><?php endif; ?>
    <?php for ($i = 1; $i <= $pages; $i++): ?><?php if ($i === $page): ?><span class="cur" aria-current="page"><?= $i ?></span><?php else: ?><a href="<?= $base . $q($i) ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?>
    <?php if ($page < $pages): ?><a href="<?= $base . $q($page + 1) ?>" aria-label="Berikutnya"><?= official_svg('chev-right') ?></a><?php else: ?><span class="off"><?= official_svg('chev-right') ?></span><?php endif; ?>
   </nav>
  </footer>
 </section>

 <?php if ($can('serahterima.write')): ?>
 <section class="ops-card hv-card hv-new" id="buat-sesi">
  <header class="hv-new-head"><span aria-hidden="true"><?= official_svg('plus') ?></span><h2>Buat Sesi Baru</h2></header>
  <form method="post" action="<?= $base ?>" class="hv-form" autocomplete="off"><?= ops_csrf() ?>
   <label for="f-title">Judul Sesi <b>*</b></label>
   <input id="f-title" name="title" required maxlength="160" value="<?= ops_e($o('title', 'Serah Terima Pengurus RT ' . date('Y'))) ?>">
   <label for="f-period">Periode Kepengurusan <b>*</b></label>
   <span class="hv-input-icon"><?= official_svg('calendar') ?><input id="f-period" name="period_label" required maxlength="60" pattern="\s*\d{4}\s*[-–—]\s*\d{4}\s*" title="Format tahun, contoh: 2026 – 2029" value="<?= ops_e($o('period_label', date('Y') . ' – ' . (date('Y') + 3))) ?>"></span>
   <div class="hv-two">
    <div><label for="f-outgoing">Pengurus Lama <b>*</b></label><?= $person_select('outgoing', 'Pilih pengurus lama') ?></div>
    <div><label for="f-incoming">Pengurus Baru <b>*</b></label><?= $person_select('incoming', 'Pilih pengurus baru') ?></div>
   </div>
   <?php if (!$people): ?><p class="hv-hint">Belum ada data di Manajemen Pengurus — pilih "Lainnya (isi manual)".</p><?php endif; ?>
   <p class="hv-hint">Setelah dilanjutkan, sistem mengambil snapshot data RT saat ini (tanpa identitas warga) untuk berita acara.</p>
   <div class="hv-buttons"><a class="hv-btn ghost" href="<?= $base ?>">Batal</a><button class="hv-btn" type="submit"><?= official_svg('arrow-right') ?>Lanjutkan</button></div>
  </form>
 </section>
 <?php endif; ?>
</div>
</div>
<link rel="stylesheet" href="<?= base_url('assets/handover.css') ?>?v=<?= filemtime(FCPATH . 'assets/handover.css') ?>">
<script>
(()=>{document.querySelectorAll('[data-hv-autosubmit]').forEach(s=>s.addEventListener('change',()=>s.form.submit()));
document.querySelectorAll('[data-hv-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.hvConfirm))e.preventDefault();}));
document.querySelectorAll('[data-hv-person]').forEach(s=>{const other=s.nextElementSibling;const sync=()=>{const on=s.value==='__other';other.hidden=!on;other.required=on;if(on)other.focus();};s.addEventListener('change',sync);});})();
</script>
