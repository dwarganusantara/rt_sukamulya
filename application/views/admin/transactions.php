<link rel="stylesheet" href="<?= base_url('assets/admin-transactions.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-transactions.css') ?>">
<?php
$income=(float)($summary['income']??0); $expense=(float)($summary['expense']??0);
$incomeCount=(int)($summary['income_count']??0); $expenseCount=(int)($summary['expense_count']??0);
$monthLabel=date('F Y',strtotime($period.'-01'));
$months=array('January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember');
$monthLabel=strtr($monthLabel,$months);
$svg=function($name){$paths=array(
 'wallet'=>'<path d="M4 8.5h20v15H4zM4 8.5V6h15v2.5M19 14h7v5h-7a2.5 2.5 0 0 1 0-5z"/>',
 'up'=>'<path d="M16 25V7m0 0-7 7m7-7 7 7"/>','down'=>'<path d="M16 7v18m0 0-7-7m7 7 7-7"/>',
 'cash'=>'<path d="M7 5h18v22H7zM11 10h10M11 15h10M11 20h6"/>',
 'search'=>'<circle cx="14" cy="14" r="8"/><path d="m20 20 7 7"/>',
 'calendar'=>'<path d="M6 8h20v19H6zM6 13h20M11 4v7M21 4v7"/>',
 'filter'=>'<path d="M5 7h22l-8 9v9l-6 3V16z"/>',
 'reset'=>'<path d="M7 11a10 10 0 1 1-1 9M7 11V4m0 7h7"/>',
 'receipt'=>'<path d="M8 4h16v24l-4-3-4 3-4-3-4 3zM12 10h8M12 15h8M12 20h5"/>',
 'reverse'=>'<path d="M11 9 5 15l6 6M6 15h11a8 8 0 0 1 8 8"/>'
 ); return '<svg viewBox="0 0 32 32" aria-hidden="true">'.$paths[$name].'</svg>';};
?>
<div class="cash-page">
 <nav class="cash-breadcrumb"><a href="<?= site_url('admin') ?>">Beranda</a><span>›</span><a href="<?= site_url('admin/transactions') ?>">Kas RT</a><span>›</span><strong>Transaksi Kas</strong></nav>
 <section class="cash-hero">
  <div class="cash-hero-copy"><span class="cash-title-icon"><?= $svg('wallet') ?></span><div><h1>Transaksi Kas</h1><p>Transaksi langsung masuk ke laporan kas setelah disimpan. Bukti pembayaran bersifat opsional.</p></div></div>
  <?php if(in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><div class="cash-create"><a href="<?= site_url('admin/transaction/masuk') ?>"><span><?= $svg('up') ?></span>Pemasukan</a><a class="outline" href="<?= site_url('admin/transaction/keluar') ?>"><span><?= $svg('down') ?></span>Pengeluaran</a></div><?php endif; ?>
 </section>
 <section class="cash-stats" aria-label="Ringkasan transaksi">
  <article class="income"><span><?= $svg('up') ?></span><div><small>Total Pemasukan</small><strong>Rp <?= number_format($income,0,',','.') ?></strong><em><?= $incomeCount ?> transaksi</em></div></article>
  <article class="expense"><span><?= $svg('down') ?></span><div><small>Total Pengeluaran</small><strong>Rp <?= number_format($expense,0,',','.') ?></strong><em><?= $expenseCount ?> transaksi</em></div></article>
  <article class="balance"><span><?= $svg('cash') ?></span><div><small>Saldo Kas</small><strong>Rp <?= number_format($balance,0,',','.') ?></strong><em>Per <?= date('t',strtotime($period.'-01')).' '.$monthLabel ?></em></div></article>
 </section>
 <form class="cash-filter" method="get" action="<?= site_url('admin/transactions') ?>">
  <label><span><?= $svg('calendar') ?></span><div><small>Pilih Periode</small><input type="month" name="period" value="<?= html_escape($period) ?>"></div></label>
  <label><div><small>Jenis Transaksi</small><select name="type"><option value="" <?= $type===''?'selected':'' ?>>Semua</option><option value="masuk" <?= $type==='masuk'?'selected':'' ?>>Pemasukan</option><option value="keluar" <?= $type==='keluar'?'selected':'' ?>>Pengeluaran</option></select></div></label>
  <label class="search"><span><?= $svg('search') ?></span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari transaksi..."></label>
  <a class="cash-reset" href="<?= site_url('admin/transactions') ?>"><?= $svg('reset') ?><span>Reset</span></a><button type="submit"><?= $svg('filter') ?><span>Filter</span></button>
 </form>
 <section class="cash-table-card"><div class="cash-table-scroll"><table><thead><tr><th>ID</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Jumlah</th><th class="action-head">Bukti / Aksi</th></tr></thead><tbody>
 <?php foreach($items as $item): ?><tr><td>#<?= (int)$item['id'] ?></td><td><?= date('d-m-Y',strtotime($item['tx_date'])) ?></td><td><span class="cash-kind <?= $item['type']==='masuk'?'in':'out' ?>"><i><?= $svg($item['type']==='masuk'?'up':'down') ?></i><?= $item['type']==='masuk'?'Pemasukan':'Pengeluaran' ?></span></td><td><strong class="cash-description"><?= html_escape($item['description']) ?></strong><small><?= html_escape($item['category']) ?></small></td><td><b class="cash-amount <?= $item['type']==='masuk'?'in':'out' ?>">Rp <?= number_format($item['amount'],0,',','.') ?></b></td><td><div class="cash-actions"><?php if(!empty($item['proof'])): ?><a href="<?= site_url('bukti/'.$item['id']) ?>" target="_blank" title="Lihat bukti" aria-label="Lihat bukti"><?= $svg('receipt') ?></a><?php endif; ?><?php if(in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><a class="reverse" href="<?= site_url('admin/transaction/'.($item['type']==='masuk'?'keluar':'masuk').'?reverse='.$item['id']) ?>" title="Buat transaksi pembalik" aria-label="Buat transaksi pembalik"><?= $svg('reverse') ?></a><?php endif; ?><?php if(empty($item['proof'])&&!in_array($this->session->userdata('admin_role'),array('admin','bendahara'),TRUE)): ?><span>-</span><?php endif; ?></div></td></tr><?php endforeach; ?>
 <?php if(!$items): ?><tr><td colspan="6" class="cash-empty">Tidak ada transaksi pada periode atau filter ini.</td></tr><?php endif; ?>
 </tbody></table></div><footer>Menampilkan <?= count($items) ?> transaksi untuk <?= html_escape($monthLabel) ?></footer></section>
</div>
