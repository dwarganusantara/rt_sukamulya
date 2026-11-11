<link rel="stylesheet" href="<?= base_url('assets/admin-billing-campaign.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-billing-campaign.css') ?>">
<?php $posted=function($key,$fallback=''){ $value=$this->input->post($key); return $value!==NULL?$value:$fallback; }; $selectedCitizen=(string)$posted('citizen_id','all'); ?>
<div class="billing-page">
 <nav class="billing-breadcrumb" aria-label="Breadcrumb"><a href="<?= site_url('admin/dues') ?>">Keuangan</a><span>›</span><a href="<?= site_url('admin/dues') ?>">Iuran Warga</a><span>›</span><strong>Buat Tagihan</strong></nav>
 <section class="billing-hero"><div class="billing-hero-copy"><span class="billing-hero-icon" aria-hidden="true">▣</span><div><h1>Buat Tagihan Iuran</h1><p>Satu formulir untuk membuat tagihan iuran bagi seluruh warga<br>atau satu warga tertentu.</p></div></div></section>
 <?php if($error): ?><div class="error billing-error"><?= html_escape($error) ?></div><?php endif; ?>
 <div class="billing-layout">
  <form class="billing-form-card" method="post">
   <header class="billing-card-title"><span aria-hidden="true">▤</span><h2>Form Tagihan</h2></header>
   <div class="billing-form-grid">
    <label><strong>Jenis Iuran</strong><span class="billing-control"><i>▣</i><select name="due_type" required><option value="rutin" <?= $posted('due_type','rutin')==='rutin'?'selected':'' ?>>Rutin bulanan</option><option value="non_rutin" <?= $posted('due_type')==='non_rutin'?'selected':'' ?>>Nonrutin</option><option value="sosial" <?= $posted('due_type')==='sosial'?'selected':'' ?>>Sosial</option></select></span><small>Pilih jenis iuran yang akan ditagihkan.</small></label>
    <label><strong>Periode</strong><span class="billing-control"><i>▦</i><input type="month" name="period" value="<?= html_escape($posted('period',date('Y-m'))) ?>" required></span><small>Pilih periode tagihan.</small></label>
    <label><strong>Nama Tagihan</strong><span class="billing-control"><i>▤</i><input name="title" maxlength="140" required placeholder="Contoh: Iuran Keamanan Oktober" value="<?= html_escape($posted('title')) ?>"></span><small>Nama yang akan tampil di tagihan warga.</small></label>
    <label><strong>Kategori</strong><span class="billing-control"><i>◇</i><select name="category" required><option value="">Pilih kategori</option><?php foreach(array('Keamanan','Kebersihan','Sosial','Pembangunan','Operasional','Lain-lain') as $category): ?><option value="<?= $category ?>" <?= $posted('category')===$category?'selected':'' ?>><?= $category ?></option><?php endforeach; ?></select></span><small>Contoh: Keamanan, Kebersihan, dan lainnya.</small></label>
    <label><strong>Nominal per Warga (Rp)</strong><span class="billing-control"><i>▤</i><input type="number" name="amount" min="1" max="999999999999" step="1" required placeholder="Contoh: 50000" value="<?= html_escape($posted('amount')) ?>"></span><small>Jumlah iuran untuk setiap warga.</small></label>
    <label><strong>Sasaran</strong><span class="billing-control"><i>♧</i><select name="target" id="bill-target"><option value="semua" <?= $posted('target','semua')==='semua'?'selected':'' ?>>Semua warga aktif</option><option value="warga" <?= $posted('target')==='warga'?'selected':'' ?>>Satu warga tertentu</option></select></span><small>Tentukan siapa yang menerima tagihan.</small></label>
    <label class="billing-wide" id="bill-citizen"><strong>Pilih Warga</strong><span class="billing-control"><i>♙</i><select name="citizen_id" id="bill-citizen-select" required><option value="all" <?= $selectedCitizen==='all' || $posted('target','semua')==='semua'?'selected':'' ?>>Semua Warga Aktif</option><?php foreach($citizens as $citizen): ?><option value="<?= (int)$citizen['id'] ?>" <?= $selectedCitizen===(string)$citizen['id']?'selected':'' ?>><?= html_escape($citizen['display_name']) ?></option><?php endforeach; ?></select></span><small>Pilih semua warga aktif atau satu warga tertentu.</small></label>
    <label class="billing-wide"><strong>Keterangan</strong><span class="billing-control billing-textarea"><i>▤</i><textarea name="note" maxlength="255" rows="3" placeholder="Contoh: Iuran keamanan lingkungan bulan ini."><?= html_escape($posted('note')) ?></textarea></span><small>Informasi tambahan yang akan terlihat oleh warga.</small></label>
   </div>
   <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
   <div class="billing-actions"><button class="button billing-submit" type="submit"><span>➤</span> Buat Tagihan</button><button class="billing-reset" type="reset"><span>↻</span> Reset</button></div>
  </form>
  <aside class="billing-side">
   <section class="billing-info-card"><header class="billing-card-title"><span aria-hidden="true">i</span><h2>Informasi</h2></header><div class="billing-info-list"><p><b class="orange">▤</b>Tagihan akan dikirim ke warga sesuai sasaran yang dipilih.</p><p><b class="green">▧</b>Warga dapat melihat tagihan di halaman mereka.</p><p><b class="purple">♧</b>Notifikasi akan muncul di akun warga secara otomatis.</p><p><b class="blue">▤</b>Riwayat tagihan dapat dilihat di menu data iuran.</p></div></section>
   <section class="billing-example-card"><header class="billing-card-title"><span aria-hidden="true">●</span><h2>Contoh Jenis dan Kategori</h2></header><div class="billing-example-list"><div><strong>Jenis Iuran</strong><ul><li>Rutin bulanan</li><li>Keamanan</li><li>Kebersihan</li><li>Kegiatan RT</li><li>Lain-lain</li></ul></div><div><strong>Kategori</strong><ul><li>Keamanan</li><li>Kebersihan</li><li>Sosial</li><li>Pembangunan</li><li>Operasional</li><li>Lain-lain</li></ul></div></div></section>
  </aside>
 </div>
</div>
<script>(function(){
 var target=document.getElementById('bill-target'),citizen=document.getElementById('bill-citizen'),select=document.getElementById('bill-citizen-select');
 function syncFromTarget(){
  var single=target.value==='warga';
  citizen.classList.toggle('is-required',single);
  if(!single) select.value='all';
  else if(select.value==='all') select.value='';
 }
 function syncFromCitizen(){
  target.value=select.value==='all'?'semua':'warga';
  citizen.classList.toggle('is-required',target.value==='warga');
 }
 target.addEventListener('change',syncFromTarget);
 select.addEventListener('change',syncFromCitizen);
 document.querySelector('.billing-form-card').addEventListener('reset',function(){setTimeout(syncFromCitizen,0);});
 syncFromCitizen();
})();</script>
