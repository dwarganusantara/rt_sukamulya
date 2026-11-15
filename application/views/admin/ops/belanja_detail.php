<?php $code = 'BLJ-' . str_pad((int) $item['id'], 5, '0', STR_PAD_LEFT); $s = $item['status']; $mine = (int) $item['requested_by'] === (int) $me_id; ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/belanja') ?>">Belanja</a><span>›</span><strong><?= $code ?></strong></nav>
<?= ops_hero($code, $item['title'], 'Diajukan ' . ops_date($item['created_at']) . ' oleh ' . ($item['requester'] ?: '-') . ($item['program_title'] ? ' · Program: ' . $item['program_title'] : ''), in_array($s, array('draft', 'rejected'), TRUE) && $can('belanja.write') ? array(array('admin/belanja/' . (int) $item['id'] . '/ubah', 'Ubah', 'ghost')) : array(), 'briefcase') ?>
<?= ops_flash() ?>
<?= ops_stats(array(array('Status', $labels[$s], $item['approver'] ? 'Diputuskan: ' . $item['approver'] : '', 'blue'), array('Estimasi', ops_money($item['estimated_total']), '', 'purple'),
    array('Realisasi', $item['actual_total'] !== NULL ? ops_money($item['actual_total']) : '—', $item['actual_note'], 'orange'), array('Kas', $item['transaction_id'] ? 'Transaksi #' . (int) $item['transaction_id'] : ($item['is_donation'] ? 'Donasi' : 'Belum diposting'), '', 'green'))) ?>
<div class="ops-grid">
 <div>
  <section class="ops-card"><h2>Item</h2>
   <?php $can_receive = in_array($s, array('ordered', 'partially_received'), TRUE) && $can('belanja.receive'); ?>
   <?php if ($can_receive): ?><form method="post"><?= ops_csrf() ?><input type="hidden" name="act" value="receive"><?php endif; ?>
   <div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Barang</th><th class="num">Dipesan</th><th class="num">Diterima</th><th class="num">Harga est.</th><th class="num">Harga aktual</th><?php if ($can_receive): ?><th>Terima sekarang</th><?php endif; ?></tr></thead><tbody>
   <?php foreach ($rows as $r): ?><tr><td data-label="Barang"><?= ops_e($r['name']) ?><?= $r['to_inventory'] ? ' <span class="ops-badge info">Inventaris</span>' : '' ?><?= $r['asset_id'] ? '<small><a href="' . site_url('admin/inventaris/' . (int) $r['asset_id']) . '">Lihat aset</a></small>' : '' ?></td>
    <td data-label="Dipesan" class="num"><?= (int) $r['qty'] ?> <?= ops_e($r['unit']) ?></td><td data-label="Diterima" class="num"><?= (int) $r['qty_received'] ?></td><td data-label="Harga est." class="num"><?= ops_money($r['est_price']) ?></td><td data-label="Harga aktual" class="num"><?= $r['actual_price'] !== NULL ? ops_money($r['actual_price']) : '—' ?></td>
    <?php if ($can_receive): $rem = (int) $r['qty'] - (int) $r['qty_received']; ?><td data-label="Terima"><?php if ($rem > 0): ?><div class="ops-actions"><input type="number" name="recv[<?= (int) $r['id'] ?>]" min="0" max="<?= $rem ?>" value="<?= $rem ?>" aria-label="Jumlah diterima" style="width:80px"><input type="number" name="price[<?= (int) $r['id'] ?>]" min="0" placeholder="Harga aktual" aria-label="Harga aktual" style="width:120px"></div><?php else: ?>Lengkap<?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?>
   </tbody></table></div>
   <?php if ($can_receive): ?><div class="ops-inline" style="margin-top:10px"><?= ops_field('note', 'Catatan kondisi barang', 'text', '', array('maxlength' => 500)) ?><button class="ops-btn green" type="submit">Catat Penerimaan</button></div></form><?php endif; ?>
  </section>
  <section class="ops-card"><h2>Alasan & riwayat</h2>
   <dl class="ops-dl"><dt>Alasan</dt><dd><?= ops_e($item['reason'] ?: '—') ?></dd><dt>Penyedia</dt><dd><?= ops_e($item['vendor'] ?: '—') ?></dd><?php if ($item['review_note']): ?><dt>Catatan</dt><dd><?= ops_e($item['review_note']) ?></dd><?php endif; ?>
    <?php if ($item['proof']): ?><dt>Bukti bayar</dt><dd><a href="<?= site_url('admin/belanja/' . (int) $item['id'] . '/bukti') ?>" target="_blank" rel="noopener">Lihat bukti (privat)</a></dd><?php endif; ?></dl>
   <?php if ($receipts): ?><h3>Penerimaan barang</h3><ul class="ops-timeline"><?php foreach ($receipts as $g): $its = json_decode($g['items_json'], TRUE) ?: array(); ?><li><strong><?= ops_date($g['receipt_date']) ?></strong> oleh <?= ops_e($g['username'] ?: '-') ?><p><?= ops_e(implode(', ', array_map(function ($x) {return $x['name'] . ' × ' . $x['qty'];}, $its))) ?><?= $g['condition_note'] ? "\n" . ops_e($g['condition_note']) : '' ?></p></li><?php endforeach; ?></ul><?php endif; ?>
  </section>
 </div>
 <div>
  <section class="ops-card"><h2>Tindakan</h2>
   <?php if (in_array($s, array('draft', 'rejected'), TRUE) && $can('belanja.write')): ?><form method="post"><?= ops_csrf() ?><button class="ops-btn" name="act" value="submit">Ajukan Persetujuan</button></form><?php endif; ?>
   <?php if ($s === 'submitted' && $can('belanja.approve')): ?>
    <?php if ($mine): ?><p class="ops-note">Anda adalah pemohon. Persetujuan harus dilakukan pengurus lain yang berwenang.</p><?php else: ?>
    <form method="post" class="ops-form one"><?= ops_csrf() ?><?= ops_field('note', 'Catatan keputusan', 'text', '', array('maxlength' => 500, 'help' => 'Wajib saat menolak.')) ?>
     <div class="ops-actions"><button class="ops-btn green" name="act" value="approve">Setujui</button><button class="ops-btn red outline" name="act" value="reject">Tolak</button></div></form><?php endif; ?>
   <?php endif; ?>
   <?php if ($s === 'approved' && $can('belanja.write')): ?><form method="post" class="ops-form one"><?= ops_csrf() ?><?= ops_field('vendor', 'Penyedia', 'text', $item['vendor'], array('maxlength' => 140)) ?><button class="ops-btn" name="act" value="order">Tandai Sudah Dipesan</button></form><?php endif; ?>
   <?php if (in_array($s, array('ordered', 'partially_received', 'received'), TRUE) && !$item['transaction_id'] && !$item['is_donation'] && $can('belanja.post')): ?>
    <form method="post" enctype="multipart/form-data" class="ops-form one" style="margin-top:10px"><?= ops_csrf() ?><h3 style="margin:0">Posting pembayaran ke kas</h3>
     <?= ops_field('actual_total', 'Nilai realisasi (Rp)', 'number', (int) $item['estimated_total'], array('min' => 1, 'required' => TRUE)) ?>
     <?= ops_field('actual_note', 'Alasan selisih dari estimasi', 'text', '', array('maxlength' => 255)) ?>
     <?= ops_field('proof', 'Bukti pembayaran (privat)', 'file', '', array('accept' => 'image/jpeg,image/png,image/webp,application/pdf')) ?>
     <button class="ops-btn" name="act" value="post" onclick="return confirm('Posting pengeluaran ke kas? Pembatalan nanti memakai transaksi pembalik.')">Posting ke Kas</button></form>
   <?php endif; ?>
   <?php if ($s === 'received' && ($item['transaction_id'] || $item['is_donation']) && $can('belanja.write')): ?><form method="post" style="margin-top:10px"><?= ops_csrf() ?><button class="ops-btn green" name="act" value="close">Tutup Permintaan</button></form><?php endif; ?>
   <?php if (in_array($s, array('draft', 'submitted', 'approved', 'ordered'), TRUE) && !$item['transaction_id'] && $can('belanja.write')): ?><form method="post" class="ops-form one" style="margin-top:14px" onsubmit="return confirm('Batalkan permintaan ini?')"><?= ops_csrf() ?><?= ops_field('note', 'Alasan pembatalan', 'text', '', array('maxlength' => 500)) ?><button class="ops-btn red outline" name="act" value="cancel">Batalkan</button></form><?php endif; ?>
   <?php if (in_array($s, array('closed', 'cancelled'), TRUE)): ?><p class="ops-muted">Permintaan sudah <?= ops_e(mb_strtolower($labels[$s])) ?>.</p><?php endif; ?>
  </section>
 </div>
</div>
</div>
