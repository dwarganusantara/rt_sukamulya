<main class="letter-page cdx-page crd-page crd-ops"><div class="cdx-wrap">
 <?php $this->load->view('citizen/topbar', array('crumbs' => array(array('Beranda', 'warga'), array('Pengaduan', 'warga/pengaduan'), array('Buat Pengaduan', '')))); ?>
 <section class="cdx-hero cpx-warm"><span class="cdx-hero-icon"><?= official_svg('megaphone') ?></span><div><h1>Buat Pengaduan</h1><p>Jelaskan masalah dengan singkat dan jelas. Lampiran foto bersifat privat dan hanya dilihat pengurus berwenang.</p></div></section>
 <?php $this->load->view('citizen/ops/_flash', array('error' => $error)); ?>
 <div class="ops-note red"><strong>Keadaan darurat?</strong> Pengaduan di aplikasi tidak menjamin respons langsung. Untuk kebakaran, kriminalitas, atau kondisi medis darurat, segera hubungi layanan darurat resmi (Polisi 110, Pemadam 113, Ambulans 118/119)<?php foreach ($emergency as $c): ?> · <?= html_escape($c['name']) ?>: <a href="tel:<?= html_escape(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>"><?= html_escape($c['phone']) ?></a><?php endforeach; ?>.</div>
 <section class="cdx-card">
  <form method="post" enctype="multipart/form-data" action="<?= site_url('warga/pengaduan/buat') ?>"><?= ops_csrf() ?>
   <div class="ops-form">
    <?= ops_field('category', 'Kategori', 'select', $values['category'] ?? '', array('required' => TRUE, 'options' => $categories, 'placeholder_option' => 'Pilih kategori')) ?>
    <?= ops_field('location', 'Lokasi kejadian', 'text', $values['location'] ?? '', array('maxlength' => 190, 'placeholder' => 'Contoh: Depan pos ronda, Gang 3')) ?>
    <?= ops_field('title', 'Judul singkat', 'text', $values['title'] ?? '', array('required' => TRUE, 'maxlength' => 160, 'wide' => TRUE)) ?>
    <?= ops_field('description', 'Uraian', 'textarea', $values['description'] ?? '', array('required' => TRUE, 'rows' => 5, 'maxlength' => 5000, 'wide' => TRUE)) ?>
    <?= ops_field('attachment', 'Foto / dokumen (opsional)', 'file', '', array('accept' => 'image/jpeg,image/png,image/webp,application/pdf', 'help' => 'JPG, PNG, WebP, atau PDF maksimal 5 MB. Hindari memotret wajah orang lain tanpa izin.', 'wide' => TRUE)) ?>
    <?php if (!empty($anonymous_allowed)): ?>
    <label class="ops-field check wide"><input type="checkbox" name="is_anonymous" value="1" <?= !empty($values['is_anonymous']) ? 'checked' : '' ?>><span>Sembunyikan identitas saya dari petugas<br><small style="font-weight:400">Nama dan nomor Anda tidak ditampilkan kepada petugas yang menangani. Ketua RT dapat membukanya hanya untuk menangani penyalahgunaan, dan tindakan itu tercatat.</small></span></label>
    <?php endif; ?>
   </div>
   <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url('warga/pengaduan') ?>">Batal</a><button class="ops-btn" type="submit">Kirim Pengaduan</button></div>
  </form>
 </section>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
