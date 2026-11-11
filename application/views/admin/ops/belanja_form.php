<?php $v = function ($k, $d = '') use ($item) { return $item[$k] ?? $d; }; $edit = !empty($item['id']); $rows = $items; while (count($rows) < 5) $rows[] = array(); ?>
<div class="ops">
<nav class="ops-crumb"><a href="<?= site_url('admin/belanja') ?>">Belanja</a><span>›</span><strong><?= $edit ? 'Ubah Permintaan' : 'Permintaan Baru' ?></strong></nav>
<?= ops_hero('Operasional RT', $edit ? 'Ubah Permintaan Belanja' : 'Permintaan Belanja Baru', 'Isi kebutuhan, kuantitas, dan estimasi harga. Centang "Masuk inventaris" untuk barang yang dicatat sebagai aset setelah diterima.', array(), 'briefcase') ?>
<?php if ($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<?php if ($edit && !empty($item['review_note'])): ?><p class="ops-note"><strong>Catatan peninjau:</strong> <?= ops_e($item['review_note']) ?></p><?php endif; ?>
<form method="post"><?= ops_csrf() ?>
 <section class="ops-card"><div class="ops-form">
  <?= ops_field('title', 'Judul permintaan', 'text', $v('title'), array('required' => TRUE, 'maxlength' => 160, 'wide' => TRUE)) ?>
  <?= ops_field('vendor', 'Penyedia / toko (opsional)', 'text', $v('vendor'), array('maxlength' => 140)) ?>
  <?= ops_field('program_id', 'Program kerja terkait', 'select', (string) $v('program_id'), array('options' => $programs, 'placeholder_option' => 'Tidak terkait program')) ?>
  <?= ops_field('reason', 'Alasan kebutuhan', 'textarea', $v('reason'), array('rows' => 3, 'wide' => TRUE)) ?>
  <?= ops_field('is_donation', 'Barang donasi (tidak ada pembayaran dari kas RT)', 'checkbox', $v('is_donation')) ?>
 </div></section>
 <section class="ops-card"><header><div><h2>Item</h2><p>Baris kosong diabaikan.</p></div><button type="button" class="ops-btn ghost small" id="ops-add-row">+ Tambah baris</button></header>
  <div class="ops-rows" id="ops-rows">
   <div class="ops-row-item ops-muted" aria-hidden="true" style="font-size:12px;font-weight:700"><span>Nama barang</span><span>Jumlah</span><span>Satuan</span><span>Harga satuan (Rp)</span><span>Inv.</span></div>
   <?php foreach ($rows as $i => $r): ?>
   <div class="ops-row-item"><input name="item_name[]" value="<?= ops_e($r['name'] ?? '') ?>" placeholder="Nama barang" aria-label="Nama barang" maxlength="160"><input type="number" name="item_qty[]" value="<?= ops_e($r['qty'] ?? 1) ?>" min="1" aria-label="Jumlah"><input name="item_unit[]" value="<?= ops_e($r['unit'] ?? 'unit') ?>" aria-label="Satuan" maxlength="30"><input type="number" name="item_price[]" value="<?= ops_e(isset($r['est_price']) ? (int) $r['est_price'] : '') ?>" min="0" aria-label="Harga satuan"><label title="Masuk inventaris"><input type="checkbox" name="item_inventory[<?= $i ?>]" value="1"<?= !empty($r['to_inventory']) ? ' checked' : '' ?> aria-label="Masuk inventaris"></label></div>
   <?php endforeach; ?>
  </div>
 </section>
 <div class="ops-form-actions"><a class="ops-btn ghost" href="<?= site_url($edit ? 'admin/belanja/' . (int) $item['id'] : 'admin/belanja') ?>">Batal</a><button class="ops-btn outline" type="submit">Simpan Draf</button><button class="ops-btn" type="submit" name="submit_now" value="1">Simpan &amp; Ajukan</button></div>
</form>
</div>
<script>
(function(){var box=document.getElementById('ops-rows'),btn=document.getElementById('ops-add-row');if(!box||!btn)return;btn.addEventListener('click',function(){var rows=box.querySelectorAll('.ops-row-item'),last=rows[rows.length-1],clone=last.cloneNode(true),i=rows.length-1;clone.querySelectorAll('input').forEach(function(el){if(el.type==='checkbox'){el.checked=false;el.name='item_inventory['+i+']';}else if(el.name==='item_qty[]'){el.value=1;}else if(el.name==='item_unit[]'){el.value='unit';}else{el.value='';}});box.appendChild(clone);clone.querySelector('input').focus();});})();
</script>
