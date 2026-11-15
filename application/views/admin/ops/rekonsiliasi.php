<?php
$tabs=array('admin/dues'=>'Iuran Warga','admin/tunggakan'=>'Tunggakan Iuran Warga','admin/transactions'=>'Kas RT','admin/rekonsiliasi'=>'Rekonsiliasi');
$issues=0;foreach($checks as $c)$issues+=count($c);
$saved=strpos((string)$this->session->flashdata('notice'),'Rekonsiliasi dicatat.')===0;
?>
<div class="ops ops-recon">
<?= ops_tabs($tabs,'admin/rekonsiliasi') ?>
<?= ops_hero('Keuangan','Rekonsiliasi Kas','Cocokkan saldo jurnal kas dengan saldo rekening bank atau kas fisik untuk memastikan kesesuaian data.',array(),'bank') ?>
<?= ops_flash() ?>
<?php if($error): ?><div class="error ops-flash" role="alert"><?= ops_e($error) ?></div><?php endif; ?>
<?= ops_stats(array(array('Saldo Sistem Hari Ini',ops_money($system),'Jurnal kas terverifikasi','blue',NULL,'wallet'),array('Temuan Integritas',$issues,$issues?'Perlu ditinjau':'Iuran dan kas konsisten',$issues?'orange':'green',NULL,'shield'),array('Rekonsiliasi Terakhir',$history?ops_date($history[0]['recon_date']):'—',$history?'Selisih '.ops_money($history[0]['difference']):'Belum pernah','purple',NULL,'calendar'))) ?>
<?php if($can('kas.reconcile')): ?>
<section class="ops-card recon-entry"><header><h2><?= official_svg('layers') ?>Catat Rekonsiliasi Kas</h2><details class="recon-guide"><summary><?= official_svg('doc') ?>Panduan</summary><p>Pilih tanggal pemeriksaan dan masukkan saldo rekening atau kas fisik pada tanggal tersebut. Periksa kembali sebelum menyimpan. Saldo sistem dihitung dari jurnal terverifikasi sampai tanggal yang dipilih. Rekonsiliasi mencatat selisih, tanpa mengubah saldo jurnal kas.</p></details></header>
<ol class="recon-steps" aria-label="Tahap rekonsiliasi"><li class="<?= $saved?'is-done':'is-current' ?>"><b>1</b>Data Rekonsiliasi</li><li class="<?= $saved?'is-done':'' ?>"><b>2</b>Konfirmasi</li><li class="<?= $saved?'is-current':'' ?>"><b>3</b>Selesai</li></ol>
<form method="post" class="ops-form recon-form" id="recon-form"><?= ops_csrf() ?>
<?= ops_field('recon_date','Periode Rekonsiliasi','date',date('Y-m-d'),array('required'=>TRUE,'max'=>date('Y-m-d'))) ?>
<?= ops_field('bank_balance','Saldo Rekening / Kas Fisik (Rp)','number','',array('required'=>TRUE,'step'=>'0.01','placeholder'=>'0')) ?>
<?= ops_field('note','Penjelasan Selisih (Opsional)','text','',array('maxlength'=>500,'placeholder'=>'Contoh: selisih biaya admin bank...')) ?>
<div class="ops-form-actions"><button class="ops-btn" type="submit">Lanjutkan</button></div>
</form>
<dialog class="recon-confirm" id="recon-confirm" aria-labelledby="recon-confirm-title"><header><h2 id="recon-confirm-title">Konfirmasi Rekonsiliasi</h2><button type="button" data-recon-close aria-label="Tutup konfirmasi">&times;</button></header><dl><dt>Per tanggal</dt><dd id="recon-confirm-date"></dd><dt>Saldo rekening / kas fisik</dt><dd id="recon-confirm-balance"></dd><dt>Penjelasan</dt><dd id="recon-confirm-note"></dd></dl><p class="ops-note blue">Saldo sistem dan selisih dihitung kembali oleh server sesuai tanggal yang dipilih. Penyimpanan ini tidak mengubah jurnal kas.</p><div class="ops-form-actions"><button type="button" class="ops-btn ghost" data-recon-close>Kembali</button><button type="button" class="ops-btn" id="recon-confirm-save">Simpan Rekonsiliasi</button></div></dialog>
</section>
<?php endif; ?>
<div class="recon-bottom-grid"><section class="ops-card recon-checks"><h2><?= official_svg('shield') ?>Pemeriksaan Integritas Iuran ↔ Kas</h2><?php foreach($checks as $label=>$list): ?><p class="recon-check"><span class="recon-check-icon <?= $list?'has-issue':'' ?>" aria-hidden="true"><?= official_svg($list?'alert':'check') ?></span><span><?= ops_e($label) ?><?php if($list): ?><small class="ops-muted"> — ID: <?= ops_e(implode(', ',array_slice(array_column($list,'id'),0,15))) ?></small><?php endif; ?></span></p><?php endforeach; ?><p class="ops-note blue">Koreksi transaksi dilakukan dengan jurnal pembalik di menu Kas RT, bukan menghapus data.</p></section>
<section class="ops-card recon-history" id="recon-history"><header><h2><?= official_svg('clock') ?>Riwayat Rekonsiliasi</h2><a class="ops-btn outline small" href="#recon-history" data-recon-history aria-expanded="false">Lihat Semua &rarr;</a></header><p class="recon-history-caption" hidden>Menampilkan maksimal 30 rekonsiliasi terbaru.</p><div class="ops-table-wrap"><table class="ops-table stack"><thead><tr><th>Tanggal</th><th class="num">Saldo Sistem</th><th class="num">Saldo Bank/Fisik</th><th class="num">Selisih</th><th>Catatan</th></tr></thead><tbody>
<?php foreach($history as $h): ?><tr><td data-label="Tanggal"><?= ops_date($h['recon_date']) ?><small><?= ops_e($h['username']?:'') ?></small></td><td data-label="Sistem" class="num"><?= ops_money($h['system_balance']) ?></td><td data-label="Bank" class="num"><?= ops_money($h['bank_balance']) ?></td><td data-label="Selisih" class="num"><?= abs((float)$h['difference'])<0.01?ops_badge('ok',array('ok'=>'Cocok'),array('ok'=>'ok')):'<b class="negative">'.ops_money($h['difference']).'</b>' ?></td><td data-label="Catatan"><?= ops_e($h['note']?:'—') ?></td></tr><?php endforeach; if(!$history): ?><tr><td colspan="5" class="ops-empty"><?= ops_empty_state('Belum ada data rekonsiliasi','Rekonsiliasi yang disimpan akan tampil di sini.','doc') ?></td></tr><?php endif; ?>
</tbody></table></div></section></div>
</div>
<script src="<?= base_url('assets/admin-reconciliation.js') ?>?v=<?= filemtime(FCPATH.'assets/admin-reconciliation.js') ?>" defer></script>
