<?php
$title='Layanan Surat Warga | '.($settings['site_name']??'Sukamulya');
$citizen_letter_layout=TRUE; $citizen_shell=TRUE; $shell_active='letters';
$this->load->view('site/header',compact('title','settings','menu','citizen_letter_layout','citizen_shell','shell_active'));
$this->load->helper('official_icons');
$tabs=array(array('all','Semua Layanan','grid','orange'),array('Kependudukan','Kependudukan','person','red'),array('Keterangan','Keterangan','doc','green'),array('Kegiatan','Kegiatan','calendar','blue'),array('Lainnya','Lainnya','mail','blue'));
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-dues-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-dues-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-pages-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-pages-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/mobile-warm.css') ?>?v=<?= filemtime(FCPATH.'assets/mobile-warm.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/letter-services-mobile.css') ?>?v=<?= filemtime(FCPATH.'assets/letter-services-mobile.css') ?>">
<?php
// Versi HP (desain krem-oranye): pilih kategori lalu jenis surat lewat dropdown; kartu surat baru muncul setelah jenis dipilih.
$lsm_img=function($f){return base_url('assets/img/letters/'.$f).'?v='.filemtime(FCPATH.'assets/img/letters/'.$f);};
$lsm_data=array();$lsm_cats=array();
foreach($catalog as $key=>$letter){$lsm_cats[$letter[1]]=TRUE;$lsm_data[]=array('key'=>(string)$key,'name'=>$letter[0],'cat'=>$letter[1],'desc'=>$letter[2],'icon'=>!empty($letter[4])?base_url($letter[4]):'','url'=>site_url('warga/ajukan').'?jenis='.rawurlencode($key));}
?>
<main class="letter-page cdx-page cpx-services crd-page mw-page">
 <header class="mw-header mw-only"><?php $this->load->view('site/mobile_brand_header',array('settings'=>$settings)); ?></header>
 <section class="lsm mw-only" aria-labelledby="lsm-title">
  <img class="lsm-hero" src="<?= $lsm_img('mw-letters-hero.jpg') ?>" alt="" aria-hidden="true" width="941" height="250">
  <div class="lsm-title-card"><img src="<?= $lsm_img('mw-letters-doc.png') ?>" alt="" aria-hidden="true"><div><h1 id="lsm-title">Layanan Surat Warga</h1><p>Ajukan berbagai surat resmi RT dengan mudah dan cepat.</p></div></div>
  <?php if($this->session->flashdata('notice')): ?><div class="portal-success lsm-flash"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
  <label class="lsm-search"><?= official_svg('search') ?><span class="sr-only">Cari surat</span><input id="lsm-search" type="search" placeholder="Cari surat (mis. domisili, kelahiran, KTP)..." autocomplete="off"></label>
  <label class="lsm-select"><span class="lsm-select-ic tone-orange" aria-hidden="true"><?= official_svg('people') ?></span><span class="lsm-select-tx"><small>Pilih Kategori Surat</small><select id="lsm-cat"><option value="">Semua Kategori</option><?php foreach(array_keys($lsm_cats) as $cat): ?><option value="<?= html_escape($cat) ?>"><?= html_escape($cat) ?></option><?php endforeach; ?></select></span><svg class="lsm-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></label>
  <label class="lsm-select"><span class="lsm-select-ic tone-doc" aria-hidden="true"><?= official_svg('doc') ?></span><span class="lsm-select-tx"><small>Pilih Jenis Surat</small><select id="lsm-jenis"><option value="">— Pilih jenis surat —</option></select></span><svg class="lsm-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></label>
  <p class="lsm-count" id="lsm-count" aria-live="polite"></p>
  <article class="lsm-card" id="lsm-card" hidden aria-live="polite"><span class="lsm-card-icon" id="lsm-card-icon" aria-hidden="true"></span><div class="lsm-card-tx"><span class="lsm-badge" id="lsm-card-cat"></span><h2 id="lsm-card-name"></h2><p id="lsm-card-desc"></p></div><a class="lsm-go" id="lsm-card-go" href="#"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2.5 11.2 21 3.5l-7.7 18.5-2.4-7.4zM10.9 14.6l3.4-3.4"/></svg>Ajukan Pengajuan</a></article>
  <a class="lsm-help" href="<?= site_url('kontak') ?>"><img src="<?= $lsm_img('mw-letters-help.png') ?>" alt="" aria-hidden="true"><span><strong>Butuh bantuan?</strong><small>Pilih jenis surat, lengkapi formulir dan dokumen pendukung. Hubungi pengurus bila ragu.</small></span><?= official_svg('arrow-right') ?></a>
  <script type="application/json" id="lsm-data"><?= json_encode($lsm_data,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) ?></script>
 </section>
<div class="cdx-wrap">
 <header class="cdx-top"><a class="cdx-back" href="<?= site_url('warga') ?>"><?= official_svg('back') ?>Kembali</a></header>
 <section class="cdx-hero cpx-warm"><img src="<?= base_url('assets/mockup/citizen-services-hero.png') ?>" alt=""><span class="cdx-hero-icon soft"><?= official_svg('list') ?></span><div><h1>Layanan <b>Surat Warga</b></h1><p>Ajukan berbagai jenis surat secara online, mudah dan cepat.</p></div></section>
 <div class="cpx-notice"><span><?= official_svg('alert') ?></span><div><strong>Ajukan surat sesuai kebutuhan Anda</strong><p>Pilih jenis surat, lengkapi formulir dan dokumen pendukung. Pengajuan akan diperiksa oleh pengurus RT.</p></div><a href="<?= site_url('kontak') ?>">Butuh bantuan? <?= official_svg('arrow-right') ?></a></div>
 <?php if($this->session->flashdata('notice')): ?><div class="portal-success cdx-flash"><?= html_escape($this->session->flashdata('notice')) ?></div><?php endif; ?>
 <div class="cpx-tools"><div class="letter-filters cpx-tabs"><?php foreach($tabs as $n=>$tab): ?><button type="button" class="<?= $n?'':'active' ?> tone-<?= $tab[3] ?>" data-category="<?= $tab[0] ?>"><span><?= official_svg($tab[2]) ?></span><?= $tab[1] ?></button><?php endforeach; ?></div><label class="cpx-search"><?= official_svg('search') ?><input id="letter-search" type="search" placeholder="Cari jenis surat..." aria-label="Cari jenis surat"></label></div>
 <div class="letter-grid" id="letter-grid"><?php foreach($catalog as $key=>$letter): ?><article class="letter-card" data-category="<?= html_escape($letter[1]) ?>" data-search="<?= html_escape(mb_strtolower($letter[0].' '.$letter[2])) ?>"><div class="letter-card-body"><?php if(!empty($letter[4])): ?><img class="letter-icon letter-icon-custom" src="<?= base_url(html_escape($letter[4])) ?>" alt="" aria-hidden="true"><?php else: ?><span class="letter-icon letter-icon-<?= html_escape($key) ?>" role="img" aria-label="Ikon <?= html_escape($letter[0]) ?>"></span><?php endif; ?><div><span class="letter-category <?= strtolower($letter[1]) ?>"><?= html_escape($letter[1]) ?></span><h2><?= html_escape($letter[0]) ?></h2><p><?= html_escape($letter[2]) ?></p></div></div><a href="<?= site_url('warga/ajukan') ?>?jenis=<?= rawurlencode($key) ?>">Ajukan Pengajuan <span>&rarr;</span></a></article><?php endforeach; ?></div>
 <p id="letter-no-results" class="citizen-empty" hidden>Tidak ada jenis surat yang cocok.</p>
</div><?php $this->load->view('citizen/red_deco'); ?></main>
<script>const filters=document.querySelectorAll('.letter-filters button'),cards=document.querySelectorAll('.letter-card'),search=document.getElementById('letter-search');let active='all';function filterLetters(){let q=search.value.trim().toLocaleLowerCase('id');let shown=0;cards.forEach(c=>{let yes=(active==='all'||c.dataset.category===active)&&c.dataset.search.includes(q);c.hidden=!yes;if(yes)shown++});document.getElementById('letter-no-results').hidden=shown>0}filters.forEach(b=>b.addEventListener('click',()=>{active=b.dataset.category;filters.forEach(x=>x.classList.toggle('active',x===b));filterLetters()}));search.addEventListener('input',filterLetters);</script>
<script src="<?= base_url('assets/letter-services-mobile.js') ?>?v=<?= filemtime(FCPATH.'assets/letter-services-mobile.js') ?>" defer></script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
