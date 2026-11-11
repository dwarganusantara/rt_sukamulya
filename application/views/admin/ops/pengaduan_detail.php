<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/pengaduan') ?>">Pengaduan</a><span>›</span><strong><?= ops_e($item['ticket_no']) ?></strong></nav>
<?= ops_hero($categories[$item['category']] ?? 'Pengaduan', $item['ticket_no'] . ' — ' . $item['title'], 'Dilaporkan ' . ops_date($item['created_at'], TRUE) . ' oleh ' . ($item['display_name'] ?: '-'), array(array('admin/pengaduan', '← Kembali', 'ghost')), 'megaphone') ?>
<?= ops_flash() ?>
<?php if ($item['category'] === 'darurat'): ?><p class="ops-note red">Kategori darurat: pastikan pelapor sudah diarahkan ke layanan darurat resmi. Aplikasi ini tidak menjamin respons langsung.</p><?php endif; ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><header><h2>Laporan</h2><?= ops_badge($item['status'], $labels, $tones) ?></header>
   <dl class="ops-dl">
    <dt>Lokasi</dt><dd><?= ops_e($item['location'] ?: '—') ?></dd>
    <dt>Uraian</dt><dd><?= ops_e($item['description']) ?></dd>
    <dt>Lampiran</dt><dd><?= $item['attachment'] ? '<a href="' . site_url('admin/pengaduan/' . (int) $item['id'] . '/lampiran') . '" target="_blank" rel="noopener">Lihat lampiran (privat)</a>' : '—' ?></dd>
    <?php if ($item['resolution_note']): ?><dt>Penyelesaian</dt><dd><?= ops_e($item['resolution_note']) ?></dd><?php endif; ?>
    <?php if ($item['rating']): ?><dt>Penilaian warga</dt><dd><?= str_repeat('★', (int) $item['rating']) . str_repeat('☆', 5 - (int) $item['rating']) ?> (<?= (int) $item['rating'] ?>/5)</dd><?php endif; ?>
   </dl>
  </section>
  <section class="ops-card"><h2>Riwayat penanganan</h2>
   <ul class="ops-timeline">
   <?php foreach ($updates as $u): ?>
    <li class="<?= $u['is_internal'] ? 'internal' : '' ?>"><strong><?= ops_e($u['actor_type'] === 'admin' ? ($u['admin_name'] ?: 'Pengurus') : ($u['citizen_name'] ?: 'Warga')) ?></strong><?= $u['status_to'] ? ' → ' . ops_badge($u['status_to'], $labels, $tones) : '' ?><?= $u['is_internal'] ? ' <span class="ops-badge purple">Internal</span>' : '' ?><small><?= ops_date($u['created_at'], TRUE) ?></small><?php if ($u['message']): ?><p><?= ops_e($u['message']) ?></p><?php endif; ?></li>
   <?php endforeach; ?>
   </ul>
  </section>
 </div>
 <div>
  <section class="ops-card"><h2>Detail tiket</h2>
   <dl class="ops-dl">
    <dt>Pelapor</dt><dd><?= ops_e($item['display_name'] ?: '-') ?><?= $item['phone'] ? '<br><small>' . ops_e($item['phone']) . '</small>' : '' ?><?php if (!empty($anonymous)): ?><br><span class="ops-badge purple"><i aria-hidden="true"></i><?= !empty($revealed) ? 'Identitas dibuka (tercatat di audit)' : 'Identitas disembunyikan' ?></span><?php endif; ?></dd>
    <dt>Prioritas</dt><dd><?= ops_e($priorities[$item['priority']] ?? $item['priority']) ?></dd>
    <dt>PIC</dt><dd><?= ops_e($item['pic'] ?: 'Belum ditugaskan') ?></dd>
    <dt>Diperbarui</dt><dd><?= ops_date($item['updated_at'], TRUE) ?></dd>
   </dl>
   <?php if (!empty($anonymous) && empty($revealed) && !empty($can_reveal)): ?>
   <details class="ops-more"><summary>Buka identitas pelapor (khusus penanganan penyalahgunaan)</summary>
    <form method="post" class="ops-form one" style="margin-top:8px"><?= ops_csrf() ?><input type="hidden" name="act" value="reveal">
     <?= ops_field('reason', 'Alasan membuka identitas', 'text', '', array('required' => TRUE, 'maxlength' => 255, 'help' => 'Dicatat di Audit Aktivitas. Identitas hanya tampil pada halaman ini, tidak disimpan.')) ?>
     <div class="ops-form-actions"><button class="ops-btn red outline" type="submit">Tampilkan identitas</button></div>
    </form>
   </details>
   <?php endif; ?>
  </section>
  <?php if ($can('pengaduan.handle') && $item['status'] !== 'closed' && $item['status'] !== 'rejected'): ?>
  <section class="ops-card"><h2>Tindak lanjut</h2>
   <form method="post" class="ops-form one"><?= ops_csrf() ?>
    <?php if ($next): $opts = array(); foreach ($next as $s) $opts[$s] = $labels[$s]; ?><?= ops_field('status', 'Ubah status', 'select', '', array('options' => $opts, 'placeholder_option' => 'Tidak diubah')) ?><?php endif; ?>
    <?= ops_field('priority', 'Prioritas', 'select', $item['priority'], array('options' => $priorities)) ?>
    <?php if ($can('pengaduan.assign')): ?><?= ops_field('assigned_to', 'PIC', 'select', (string) $item['assigned_to'], array('options' => $staff, 'placeholder_option' => 'Belum ditugaskan')) ?><?php endif; ?>
    <?= ops_field('message', 'Tanggapan / catatan', 'textarea', '', array('rows' => 4, 'maxlength' => 3000, 'help' => 'Wajib saat menyelesaikan atau menolak tiket.')) ?>
    <?= ops_field('is_internal', 'Catatan internal (tidak terlihat oleh warga)', 'checkbox', 0) ?>
    <div class="ops-form-actions"><button class="ops-btn" type="submit">Simpan Tindak Lanjut</button></div>
   </form>
  </section>
  <?php endif; ?>
 </div>
</div>
</div>
