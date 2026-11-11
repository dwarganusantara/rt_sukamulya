<?php
$this->load->helper('official_icons');
$mb_logged=(bool)$this->session->userdata('citizen_id');
$mb_uri=uri_string();
$mb_is=function($pattern)use($mb_uri){return (bool)preg_match('#^('.$pattern.')(/|$)#',$mb_uri);};
$mb_main=array(
 array($mb_logged?'warga':'','home','Beranda',$mb_uri===''||$mb_uri==='warga'),
 array($mb_logged?'warga/profil':'profil','person','Profil',$mb_is('profil|struktur|warga/profil|warga/edit-profil')),
 array('warga/layanan','doc','Pengajuan',$mb_is('warga/layanan|warga/riwayat|warga/ajukan|warga/draft|warga/pengajuan')),
 array('galeri','image','Galeri',$mb_is('galeri')),
);
$mb_more=array(
 array('pengumuman','megaphone','Pengumuman','orange'),
 array('agenda','calendar','Agenda Kegiatan','green'),
 array('informasi','alert','Informasi','blue'),
 array('keuangan','chart','Laporan Kas RT','purple'),
);
if($mb_logged){
 array_splice($mb_more,2,0,array(array('warga/iuran','wallet','Iuran Saya','blue'),array('warga/riwayat','clock','Riwayat Pengajuan','green')));
 $mb_more[]=array('warga#live-chat','chat','Chat Pengurus','orange');
 $mb_more[]=array('warga/password','shield','Ubah Password','purple');
}
$mb_more[]=array('kontak','phone','Kontak RT','red');
if(!isset($this->feature_gate))$this->load->library('Feature_gate');
$mb_ok=function($i){return $this->feature_gate->link_ok(strtok($i[0],'#'),'warga');};$mb_main=array_values(array_filter($mb_main,$mb_ok));$mb_more=array_values(array_filter($mb_more,$mb_ok));
$mb_other_active=$mb_is('pengumuman|agenda|informasi|keuangan|kontak|cari|halaman|warga/iuran|warga/password');
?>
<nav class="mbx-nav" aria-label="Navigasi utama">
 <?php foreach($mb_main as $item): ?><a class="<?= $item[3]?'active':'' ?>" href="<?= site_url($item[0]) ?>"<?= $item[3]?' aria-current="page"':'' ?>><?= official_svg($item[1]) ?><span><?= $item[2] ?></span></a><?php endforeach; ?>
 <button type="button" id="mbx-more-open" class="<?= $mb_other_active?'active':'' ?>" aria-haspopup="dialog"><?= official_svg('more-h') ?><span>Lainnya</span></button>
</nav>
<dialog class="mbx-sheet" id="mbx-sheet" aria-labelledby="mbx-sheet-title">
 <header><h2 id="mbx-sheet-title">Menu Lainnya</h2><button type="button" id="mbx-more-close" aria-label="Tutup menu"><?= official_svg('close') ?></button></header>
 <div class="mbx-sheet-grid"><?php foreach($mb_more as $item): ?><a class="tint-<?= $item[3] ?>" href="<?= site_url($item[0]) ?>"><span><?= official_svg($item[1]) ?></span><?= $item[2] ?></a><?php endforeach; ?></div>
 <?php if($mb_logged): ?><a class="mbx-sheet-exit" href="<?= site_url('warga/logout') ?>"><?= official_svg('back') ?>Keluar dari akun</a><?php else: ?><a class="mbx-sheet-login" href="<?= site_url('warga/login') ?>"><?= official_svg('person') ?>Masuk Warga</a><?php endif; ?>
</dialog>
<script src="<?= base_url('assets/mobile-app.js') ?>?v=<?= filemtime(FCPATH.'assets/mobile-app.js') ?>"></script>
