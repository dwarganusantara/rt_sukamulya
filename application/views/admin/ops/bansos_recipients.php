<section class="bsx-card bsx-recipient-card">
 <header class="bsx-card-head">
  <h2><?= official_svg('people') ?>Calon Penerima Bantuan Sosial</h2>
  <a href="<?= site_url('admin/bansos/ekspor') ?>"><?= official_svg('download') ?>Ekspor Rekap <?= official_svg('arrow-right') ?></a>
 </header>
 <div class="bsx-table-wrap">
  <table class="bsx-table bsx-recipient-table">
   <thead><tr><th>No</th><th>Nama Calon Penerima</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr></thead>
   <tbody>
   <?php foreach ($recipients as $i => $r):
    $pl = $pill[$r['status']] ?? array($labels[$r['status']] ?? $r['status'], 'gray', 'clock');
    $form_id = 'recipient-action-' . (int) $r['id'];
   ?>
    <tr id="penerima-<?= (int) $r['id'] ?>">
     <td data-label="No" class="bsx-no"><?= $i + 1 ?></td>
     <td data-label="Nama Calon Penerima">
      <div class="bsx-person"><span class="bsx-avatar"><?= official_svg('person') ?></span><div><strong><?= ops_e($r['recipient_name']) ?></strong><small><?= ops_e($r['address']) ?></small></div></div>
     </td>
     <td data-label="Status">
      <span class="bsx-pill <?= $pl[1] ?>"><?= official_svg($pl[2]) ?><?= ops_e($pl[0]) ?></span>
      <?= $r['distributed_at'] ? '<small>' . ops_date($r['distributed_at'], TRUE) . '</small>' : '' ?>
      <?= $r['proof'] ? '<small><a href="' . site_url('admin/bansos/bukti/' . (int) $r['id']) . '" target="_blank" rel="noopener">Bukti terima</a></small>' : '' ?>
     </td>
     <td data-label="Keterangan" class="bsx-note-cell">
      <?php if (!$closed && $r['status'] === 'usulan' && $can('bansos.write')): ?>
       <form id="<?= $form_id ?>" method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="verify"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>"></form>
       <label class="bsx-note-field" for="<?= $form_id ?>-note"><span><?= official_svg('doc') ?>Catatan Verifikasi</span><input id="<?= $form_id ?>-note" form="<?= $form_id ?>" name="note" placeholder="Masukkan catatan..." aria-label="Catatan verifikasi" maxlength="500"></label>
      <?php elseif (!$closed && $r['status'] === 'layak' && $can('bansos.approve')): ?>
       <form id="<?= $form_id ?>" method="post"><?= ops_csrf() ?><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>"></form>
       <label class="bsx-note-field" for="<?= $form_id ?>-note"><span><?= official_svg('doc') ?>Catatan Keputusan</span><input id="<?= $form_id ?>-note" form="<?= $form_id ?>" name="note" placeholder="Masukkan catatan..." aria-label="Catatan keputusan" maxlength="500"></label>
      <?php else: ?>
       <span class="bsx-note"><?= ops_e($r['decision_note'] ?: ($r['proposal_note'] ?: '—')) ?></span>
      <?php endif; ?>
     </td>
     <td data-label="Aksi" class="bsx-action-cell">
      <?php if (!$closed && $r['status'] === 'usulan' && $can('bansos.write')): ?>
       <span class="bsx-act-btns"><button class="bsx-btn green" form="<?= $form_id ?>" name="result" value="layak"><?= official_svg('check') ?>Layak</button><button class="bsx-btn red-o" form="<?= $form_id ?>" name="result" value="tidak_layak"><?= official_svg('close') ?>Tidak Layak</button></span>
      <?php elseif (!$closed && $r['status'] === 'layak' && $can('bansos.approve')): ?>
       <span class="bsx-act-btns"><button class="bsx-btn green" form="<?= $form_id ?>" name="act" value="approve"><?= official_svg('check') ?>Setujui</button><button class="bsx-btn red-o" form="<?= $form_id ?>" name="act" value="reject"><?= official_svg('close') ?>Tolak</button></span>
      <?php elseif (!$closed && $r['status'] === 'disetujui' && $can('bansos.write')): ?>
       <details class="bsx-more"><summary><?= official_svg('cash') ?>Catat Penyaluran</summary><form method="post" enctype="multipart/form-data"><?= ops_csrf() ?><input type="hidden" name="act" value="distribute"><input type="hidden" name="recipient_id" value="<?= (int) $r['id'] ?>"><label>Bukti penerimaan (privat)<input type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf"></label><input name="note" placeholder="Catatan (opsional)" aria-label="Catatan penyaluran" maxlength="500"><button class="bsx-btn green sm" type="submit" onclick="return confirm('Catat bantuan sudah disalurkan? Tidak dapat dicatat dua kali.')"><?= official_svg('save') ?>Simpan Penyaluran</button></form></details>
      <?php else: ?><span class="bsx-action-done"><?= official_svg('check-circle') ?>Selesai diproses</span><?php endif; ?>
     </td>
    </tr>
   <?php endforeach; if (!$recipients): ?>
    <tr><td colspan="5" class="bsx-empty">Belum ada calon penerima. Usulkan dari form di samping.</td></tr>
   <?php endif; ?>
   </tbody>
  </table>
 </div>
</section>
