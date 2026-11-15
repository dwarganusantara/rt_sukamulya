<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Pengaduan', 'warga/pengaduan'), array($item['ticket_no'], '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('megaphone') ?></span><div><h1><?= html_escape($item['title']) ?></h1><p><?= html_escape($item['ticket_no']) ?> · <?= html_escape($categories[$item['category']] ?? '') ?> · <?= ops_date($item['created_at'], TRUE) ?></p></div></section>
 <?php $this->load->view('citizen/ops/_flash'); ?>
 <div class="ops-grid">
  <section class="cdx-card">
   <header class="cdx-head"><span class="orange"><?= official_svg('doc') ?></span><div><h2>Status: <?= ops_badge($item['status'], $labels, $tones) ?></h2><p>Terakhir diperbarui <?= ops_date($item['updated_at'], TRUE) ?></p></div></header>
   <dl class="ops-dl"><dt>Lokasi</dt><dd><?= html_escape($item['location'] ?: '—') ?></dd><dt>Uraian</dt><dd><?= html_escape($item['description']) ?></dd>
    <dt>Lampiran</dt><dd><?= $item['attachment'] ? '<a href="' . site_url('warga/pengaduan/' . (int) $item['id'] . '/lampiran') . '" target="_blank" rel="noopener">Lihat lampiran</a>' : '—' ?></dd>
    <?php if ($item['resolution_note']): ?><dt>Penyelesaian</dt><dd><?= html_escape($item['resolution_note']) ?></dd><?php endif; ?></dl>
   <h3 style="margin:16px 0 8px">Riwayat</h3>
   <ul class="ops-timeline"><?php foreach ($updates as $u): ?><li><strong><?= $u['actor_type'] === 'admin' ? 'Pengurus RT' : 'Anda' ?></strong><?= $u['status_to'] ? ' → ' . ops_badge($u['status_to'], $labels, $tones) : '' ?><small><?= ops_date($u['created_at'], TRUE) ?></small><?php if ($u['message']): ?><p><?= html_escape($u['message']) ?></p><?php endif; ?></li><?php endforeach; ?></ul>
  </section>
  <section class="cdx-card">
   <?php if ($item['status'] === 'resolved'): ?>
    <h2 style="margin-top:0">Apakah masalah sudah tuntas?</h2>
    <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="action" value="confirm">
     <?= ops_field('rating', 'Penilaian penanganan', 'select', '5', array('options' => array('5' => '5 — Sangat baik', '4' => '4 — Baik', '3' => '3 — Cukup', '2' => '2 — Kurang', '1' => '1 — Buruk'))) ?>
     <?= ops_field('message', 'Komentar (opsional)', 'textarea', '', array('rows' => 2, 'maxlength' => 2000)) ?>
     <div class="ops-form-actions"><button class="ops-btn green" type="submit">Ya, Tutup Tiket</button></div>
    </form>
    <form method="post" class="ops-form one" style="margin-top:14px"><?= ops_csrf() ?><input type="hidden" name="action" value="reopen">
     <?= ops_field('message', 'Belum tuntas? Jelaskan alasannya', 'textarea', '', array('rows' => 2, 'maxlength' => 2000)) ?>
     <div class="ops-form-actions"><button class="ops-btn outline" type="submit">Buka Kembali</button></div>
    </form>
   <?php elseif (in_array($item['status'], $open, TRUE)): ?>
    <h2 style="margin-top:0">Tambah tanggapan</h2>
    <?php if ($item['status'] === 'waiting_resident'): ?><p class="ops-note">Pengurus menunggu informasi tambahan dari Anda.</p><?php endif; ?>
    <form method="post" class="ops-form one"><?= ops_csrf() ?><input type="hidden" name="action" value="comment">
     <?= ops_field('message', 'Pesan', 'textarea', '', array('required' => TRUE, 'rows' => 3, 'maxlength' => 2000)) ?>
     <div class="ops-form-actions"><button class="ops-btn" type="submit">Kirim Tanggapan</button></div>
    </form>
   <?php else: ?>
    <p class="ops-muted">Tiket ini sudah <?= html_escape(mb_strtolower($labels[$item['status']])) ?>.<?= $item['rating'] ? ' Terima kasih atas penilaian Anda.' : '' ?></p>
   <?php endif; ?>
   <a class="ops-btn ghost" style="margin-top:12px" href="<?= site_url('warga/pengaduan') ?>">Kembali ke daftar</a>
  </section>
 </div>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
