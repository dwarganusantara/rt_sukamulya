<?php
$this->load->helper('family_ui');
$status_info=array(
 'pending'=>array('Menunggu','hourglass','is-pending','Menunggu Verifikasi'),
 'approved'=>array('Disetujui','check','is-approved','Disetujui'),
 'rejected'=>array('Ditolak','close','is-rejected','Ditolak'),
 'revision_requested'=>array('Perlu Perbaikan','edit','is-revision','Perlu Perbaikan'),
);
$list_url=function($change=array()) use($query){$q=array_merge($query,$change);foreach($q as $k=>$v)if($v===NULL||$v==='')unset($q[$k]);return site_url('admin/kk/pengajuan').($q?'?'.http_build_query($q):'');};
$return_to='admin/kk/pengajuan'.($query?'?'.http_build_query($query):'');
$fmt=function($d){return $d?date('d M Y, H:i',strtotime($d)):'-';};
$csrf_name=$this->security->get_csrf_token_name(); $csrf_hash=$this->security->get_csrf_hash();
$tabs=array('pending'=>'Menunggu Verifikasi','approved'=>'Disetujui','revision_requested'=>'Perlu Perbaikan','rejected'=>'Ditolak','all'=>'Semua');
$tab_icons=array('pending'=>'file','approved'=>'check','revision_requested'=>'edit','rejected'=>'close','all'=>'grid');
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-kk.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-kk.css') ?>">
<div class="akk">
<nav class="akk-crumb" aria-label="Breadcrumb"><a href="<?= site_url('admin') ?>"><?= family_icon('home') ?>Beranda</a><?= family_icon('chevron-right') ?><span aria-current="page">Verifikasi KK</span></nav>

<header class="akk-hero">
 <span class="akk-hero-orb"><?= family_icon('people') ?></span>
 <div class="akk-hero-copy"><h1>Verifikasi Data Keluarga</h1><p>Periksa dan setujui pengajuan data Kartu Keluarga (KK) dari warga.</p></div>
 <div class="akk-hero-art" aria-hidden="true"><div class="akk-hero-doc"><?= family_document_art() ?><span><?= family_icon('check') ?></span></div><img src="<?= base_url('assets/mockup/chat-admin-avatar.png') ?>" alt=""></div>
</header>

<?php if($msg=$this->session->flashdata('kk_notice')): ?><div class="akk-flash is-ok" role="status"><?= family_icon('check') ?><span><?= html_escape($msg) ?></span></div><?php endif; ?>
<?php if($msg=$this->session->flashdata('kk_error')): ?><div class="akk-flash is-err" role="alert"><?= family_icon('info') ?><span><?= html_escape($msg) ?></span></div><?php endif; ?>

<section class="akk-stats" aria-label="Ringkasan pengajuan">
 <a class="akk-stat is-pending" href="<?= $list_url(array('status'=>'pending','hal'=>NULL)) ?>"><span class="akk-stat-icon"><?= family_icon('file') ?></span><span><small>Menunggu Verifikasi</small><strong><?= $counts['pending'] ?> <em>Pengajuan</em></strong></span></a>
 <a class="akk-stat is-approved" href="<?= $list_url(array('status'=>'approved','hal'=>NULL)) ?>"><span class="akk-stat-icon"><?= family_icon('check') ?></span><span><small>Disetujui</small><strong><?= $counts['approved'] ?> <em>Pengajuan</em></strong></span></a>
 <a class="akk-stat is-rejected" href="<?= $list_url(array('status'=>'rejected','hal'=>NULL)) ?>"><span class="akk-stat-icon"><?= family_icon('close') ?></span><span><small>Ditolak</small><strong><?= $counts['rejected'] ?> <em>Pengajuan</em></strong></span></a>
 <a class="akk-stat is-total" href="<?= $list_url(array('status'=>'all','hal'=>NULL)) ?>"><span class="akk-stat-icon"><?= family_icon('people') ?></span><span><small>Total Pengajuan</small><strong><?= $counts['all'] ?> <em>Pengajuan</em></strong></span></a>
</section>

<div class="akk-board">
<section class="akk-card akk-list">
 <header class="akk-list-head">
  <h2><span class="akk-orb"><?= family_icon('people') ?></span>Daftar Pengajuan Data Keluarga</h2>
  <form class="akk-filters" method="get" action="<?= site_url('admin/kk/pengajuan') ?>" role="search">
   <input type="hidden" name="status" value="<?= html_escape($status) ?>"><input type="hidden" name="tampil" value="<?= (int)$per ?>">
   <label class="akk-select"><?= family_icon('calendar') ?><span class="akk-sr">Periode</span><select name="periode" onchange="this.form.submit()"><?php foreach($periods as $k=>$v): ?><option value="<?= $k ?>" <?= $period===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></label>
   <label class="akk-search"><?= family_icon('search') ?><span class="akk-sr">Cari pengajuan</span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari nama, NIK, atau No. KK..."></label>
  </form>
 </header>
 <nav class="akk-tabs" aria-label="Filter status">
  <?php foreach($tabs as $k=>$v): ?><a class="akk-tab tab-<?= $k ?> <?= $status===$k?'is-active':'' ?>" href="<?= $list_url(array('status'=>$k,'hal'=>NULL)) ?>" <?= $status===$k?'aria-current="page"':'' ?>><?= family_icon($tab_icons[$k]) ?><?= $v ?> <b>(<?= (int)$counts[$k] ?>)</b></a><?php endforeach; ?>
 </nav>
 <div class="akk-table-wrap">
 <table class="akk-table">
  <thead><tr><th>No.</th><th>No. Pengajuan</th><th>Nama Pemohon</th><th>No. KK</th><th>Jumlah Anggota</th><th>Tanggal Pengajuan</th><th>Status</th><th class="akk-c">Aksi</th></tr></thead>
  <tbody>
  <?php foreach($items as $i=>$it): $si=$status_info[$it['status']]??array($it['status'],'info','',$it['status']); $detail=array('id'=>$it['id'],'code'=>$it['code'],'name'=>$it['name'],'nomor_kk'=>$it['nomor_kk']?:'-','alamat'=>$it['alamat']?:'-','rt_rw'=>$it['rt_rw']?:'-','kepala'=>$it['kepala']?:'-','count'=>count($it['members']),'date'=>$fmt($it['created_at']),'status'=>$it['status'],'status_label'=>$si[3],'notes'=>$it['review_notes'],'reviewed'=>$it['reviewed_at']?$fmt($it['reviewed_at']):'','members'=>$it['members'],'url'=>site_url('admin/kk/pengajuan/'.$it['id'])); ?>
  <tr class="akk-row" data-detail="<?= html_escape(json_encode($detail,JSON_UNESCAPED_UNICODE)) ?>" tabindex="-1">
   <td><?= ($page-1)*$per+$i+1 ?></td>
   <td><strong><?= $it['code'] ?></strong></td>
   <td><strong><?= html_escape($it['name']) ?></strong></td>
   <td class="akk-mono"><?= html_escape($it['nomor_kk']?:'-') ?></td>
   <td><?= count($it['members']) ?> Orang</td>
   <td><span class="akk-date"><?= family_icon('calendar') ?><?= $fmt($it['created_at']) ?></span></td>
   <td><span class="akk-badge <?= $si[2] ?>"><?= family_icon($si[1]) ?><?= $si[0] ?></span></td>
   <td class="akk-actions">
    <a class="akk-act is-view" href="<?= site_url('admin/kk/pengajuan/'.$it['id']) ?>" aria-label="Lihat data lengkap <?= $it['code'] ?>" title="Lihat data lengkap"><?= family_icon('eye') ?></a>
    <?php if($it['status']==='pending'): ?>
    <button type="button" class="akk-act is-ok" data-decide="approved" data-id="<?= $it['id'] ?>" data-code="<?= $it['code'] ?>" data-name="<?= html_escape($it['name']) ?>" aria-label="Setujui <?= $it['code'] ?>" title="Setujui"><?= family_icon('check') ?></button>
    <button type="button" class="akk-act is-no" data-decide="rejected" data-id="<?= $it['id'] ?>" data-code="<?= $it['code'] ?>" data-name="<?= html_escape($it['name']) ?>" aria-label="Tolak <?= $it['code'] ?>" title="Tolak"><?= family_icon('close') ?></button>
    <?php endif; ?>
   </td>
  </tr>
  <?php endforeach; if(!$items): ?>
  <tr><td colspan="8" class="akk-empty"><span><?= family_icon($q!==''?'search':'check') ?></span><strong><?= $q!==''?'Tidak ada pengajuan yang cocok':($status==='pending'?'Semua pengajuan sudah diverifikasi':'Belum ada pengajuan') ?></strong><small><?= $q!==''?'Coba kata kunci lain atau ubah filter periode.':($status==='pending'?'Tidak ada pengajuan KK yang menunggu verifikasi.':'Pengajuan dari warga akan tampil di sini.') ?></small></td></tr>
  <?php endif; ?>
  </tbody>
 </table>
 </div>
 <footer class="akk-pager">
  <form method="get" action="<?= site_url('admin/kk/pengajuan') ?>" class="akk-per"><?php foreach(array('status'=>$status,'periode'=>$period,'q'=>$q) as $k=>$v): if($v==='')continue; ?><input type="hidden" name="<?= $k ?>" value="<?= html_escape($v) ?>"><?php endforeach; ?><label>Tampilkan <select name="tampil" onchange="this.form.submit()"><?php foreach(array(10,25,50) as $n): ?><option value="<?= $n ?>" <?= $per===$n?'selected':'' ?>><?= $n ?></option><?php endforeach; ?></select></label><span>dari <?= $total ?> data</span></form>
  <nav class="akk-pages" aria-label="Halaman">
   <?php if($page>1): ?><a class="akk-page" href="<?= $list_url(array('hal'=>$page-1)) ?>" aria-label="Sebelumnya"><?= family_icon('back') ?></a><?php else: ?><span class="akk-page is-disabled" aria-hidden="true"><?= family_icon('back') ?></span><?php endif; ?>
   <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?><a class="akk-page <?= $p===$page?'is-current':'' ?>" href="<?= $list_url(array('hal'=>$p)) ?>" <?= $p===$page?'aria-current="page"':'' ?>><?= $p ?></a><?php endfor; ?>
   <?php if($page<$pages): ?><a class="akk-page" href="<?= $list_url(array('hal'=>$page+1)) ?>" aria-label="Berikutnya"><?= family_icon('arrow') ?></a><?php else: ?><span class="akk-page is-disabled" aria-hidden="true"><?= family_icon('arrow') ?></span><?php endif; ?>
  </nav>
 </footer>
</section>

<aside class="akk-card akk-panel" id="akk-panel" aria-live="polite" <?= $items?'':'hidden' ?>>
 <header><span class="akk-panel-icon"><?= family_icon('file') ?></span><div><h2>Detail Pengajuan</h2><small data-f="code">-</small></div><button type="button" class="akk-x" id="akk-panel-close" aria-label="Tutup detail"><?= family_icon('close') ?></button></header>
 <dl class="akk-panel-list">
  <div><dt><?= family_icon('person') ?>Nama Pemohon</dt><dd data-f="name">-</dd></div>
  <div><dt><?= family_icon('hash') ?>No. KK</dt><dd data-f="nomor_kk" class="akk-mono">-</dd></div>
  <div><dt><?= family_icon('people') ?>Kepala Keluarga</dt><dd data-f="kepala">-</dd></div>
  <div><dt><?= family_icon('home') ?>Alamat</dt><dd><span data-f="alamat">-</span><small> · RT/RW <span data-f="rt_rw">-</span></small></dd></div>
  <div><dt><?= family_icon('people') ?>Jumlah Anggota</dt><dd><span data-f="count">0</span> Orang</dd></div>
  <div><dt><?= family_icon('calendar') ?>Tanggal</dt><dd data-f="date">-</dd></div>
  <div><dt><?= family_icon('info') ?>Status</dt><dd><span class="akk-badge" data-f="status_label">-</span></dd></div>
 </dl>
 <div class="akk-panel-members"><h3>Anggota Keluarga</h3><ol data-f="members"></ol></div>
 <div class="akk-panel-note" data-f="notes-box" hidden><strong>Catatan Admin</strong><p data-f="notes"></p></div>
 <a class="akk-full" data-f="url" href="#"><?= family_icon('eye') ?><span>Lihat Data Lengkap</span><?= family_icon('chevron-right') ?></a>
 <div class="akk-panel-actions" data-f="actions">
  <button type="button" class="akk-btn is-ok" data-decide="approved"><?= family_icon('check') ?>Setujui</button>
  <button type="button" class="akk-btn is-no" data-decide="rejected"><?= family_icon('close') ?>Tolak</button>
  <button type="button" class="akk-link" data-decide="revision_requested"><?= family_icon('edit') ?>Minta Perbaikan ke Warga</button>
 </div>
</aside>
</div>
</div>

<?php $this->load->view('admin/kk_decision_dialog',array('return_to'=>$return_to)); ?>
<script src="<?= base_url('assets/admin-kk.js') ?>?v=<?= filemtime(FCPATH.'assets/admin-kk.js') ?>" defer></script>
