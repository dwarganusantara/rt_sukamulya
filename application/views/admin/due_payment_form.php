<?php $format=function($n){return 'Rp '.number_format($n,0,',','.');}; ?>
<?= cfx_assets() ?>
<?= cfx_hero('dues','cash','Catat Pembayaran','Catat pembayaran yang diterima langsung oleh pengurus. Pembayaran langsung masuk laporan kas.',array(array('Beranda','admin'),array('Iuran Warga','admin/dues'),array('Catat Pembayaran','')),'admin/dues') ?>
<?php if($error): ?><div class="error off-error"><?= html_escape($error) ?></div><?php endif; ?>
<div class="cfx-layout">
<form class="cfx-card cfx-form" method="post" enctype="multipart/form-data" id="due-pay-form">
 <?= cfx_head('cash','green','Data Pembayaran','Pilih tagihan warga lalu isi jumlah yang diterima.') ?>
 <div class="cfx-field"><?= cfx_label('due-id','Tagihan',TRUE) ?><?= cfx_control('doc','<select id="due-id" name="due_id" required><option value="">Pilih tagihan warga</option>'.implode('',array_map(function($due)use($form,$format){$left=(float)$due['amount_due']-(float)$due['amount_paid'];return '<option value="'.(int)$due['id'].'" data-remaining="'.(int)$left.'"'.((int)$form['due_id']===(int)$due['id']?' selected':'').'>'.html_escape($due['display_name'].' — '.$due['category'].' '.$due['period'].' (sisa '.$format($left).')').'</option>';},$dues)).'</select>') ?><?php if(!$dues): ?><small class="cfx-hint">Tidak ada tagihan yang belum lunas.</small><?php endif; ?></div>
 <div class="cfx-grid">
  <div class="cfx-field"><?= cfx_label('due-amount','Jumlah Diterima (Rp)',TRUE) ?><?= cfx_control('#','<input id="due-amount" type="number" name="amount" min="1" step="1" required value="'.html_escape($form['amount']).'">') ?></div>
  <div class="cfx-field"><?= cfx_label('due-method','Metode',TRUE) ?><?= cfx_control('tag','<select id="due-method" name="method"><option value="tunai"'.($form['method']==='tunai'?' selected':'').'>Tunai</option><option value="transfer"'.($form['method']==='transfer'?' selected':'').'>Transfer</option></select>') ?></div>
 </div>
 <div class="cfx-field"><label class="cfx-label" for="due-proof">Bukti Pembayaran <span id="due-proof-note">(Opsional untuk tunai)</span></label><input id="due-proof" type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf"><small class="cfx-hint">JPG, PNG, WebP, atau PDF maksimal 5 MB. Wajib untuk transfer.</small></div>
 <div class="cfx-field"><?= cfx_label('due-note','Catatan') ?><?= cfx_control('pencil','<input id="due-note" name="note" maxlength="255" value="'.html_escape($form['note']).'" placeholder="Contoh: Diterima oleh bendahara saat rapat">') ?></div>
 <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
 <?= cfx_actions('Simpan Pembayaran','admin/dues') ?>
</form>
<aside class="cfx-side-col"><?= cfx_tips('Sebelum Menyimpan','',array('Pembayaran yang disimpan langsung terverifikasi dan menambah kas RT.','Jumlah tidak boleh melebihi sisa tagihan.','Tinjau dulu pembayaran warga yang masih menunggu pada tagihan yang sama.')) ?></aside>
</div>
<script>
(function(){
 var due=document.getElementById('due-id'),amount=document.getElementById('due-amount'),method=document.getElementById('due-method'),proof=document.getElementById('due-proof'),note=document.getElementById('due-proof-note');
 function syncDue(fill){var option=due.options[due.selectedIndex],left=option&&option.dataset.remaining;amount.max=left||'';if(fill&&left)amount.value=left}
 function syncMethod(){var transfer=method.value==='transfer';proof.required=transfer;note.textContent=transfer?'(Wajib untuk transfer)':'(Opsional untuk tunai)'}
 due.addEventListener('change',function(){syncDue(true)});method.addEventListener('change',syncMethod);
 syncDue(!amount.value);syncMethod();
})();
</script>
