<?php
/*
 * Header merek mobile tema krem-oranye (logo, "RT/RW Digital", pil RT, subjudul, lonceng, profil).
 * Dipakai beranda mobile (site/home_mobile) dan halaman publik bertema krem (mis. Pengumuman).
 * Pemanggil membungkusnya dalam <header class="mw-header">. Gaya: assets/mobile-warm.css.
 * Variabel: $settings; opsional $mw_title (judul, default "RT/RW Digital"); opsional $mw_new (bool, titik merah lonceng) — default: ada pengumuman terbit 7 hari terakhir.
 */
$this->load->helper('official_icons');
if(!isset($this->feature_gate))$this->load->library('Feature_gate');
$mw_logged=(bool)$this->session->userdata('citizen_id');
$mw_logo=trim((string)($settings['logo']??''));if(!$mw_logo||!is_file(FCPATH.$mw_logo))$mw_logo='assets/mockup/logo.png';
$mw_area=trim((string)($settings['brand_subtitle']??'Suka Mulya IV'));
$mw_bell=$this->feature_gate->link_ok('pengumuman','warga');
if(!isset($mw_new))$mw_new=$mw_bell&&$this->db->where(array('type'=>'pengumuman','status'=>'published'))->where('created_at >=',date('Y-m-d H:i:s',strtotime('-7 days')))->count_all_results('posts')>0;
?>
<img class="mw-leaves" src="<?= base_url('assets/mockup/home-mobile-leaves.png') ?>" alt="" aria-hidden="true">
<div class="mw-top">
 <div class="mw-brand"><a class="mw-logo" href="<?= site_url($mw_logged?'warga':'') ?>" aria-label="Beranda"><img src="<?= base_url(html_escape($mw_logo)) ?>" alt="Logo lingkungan"></a><div class="mw-brand-tx"><strong class="mw-app"><?= html_escape($mw_title ?? 'RT/RW Digital') ?></strong><b class="mw-pill"><?= html_escape($settings['brand_label']??'RT 009/008') ?></b><span><?= html_escape($mw_area) ?></span></div></div>
 <div class="mw-actions"><?php if($mw_bell): ?><a href="<?= site_url('pengumuman') ?>" aria-label="<?= $mw_new?'Ada pengumuman baru. ':'' ?>Lihat pengumuman"><?= official_svg('bell') ?><?php if($mw_new): ?><i class="mw-dot" aria-hidden="true"></i><?php endif; ?></a><?php endif; ?><a href="<?= site_url($mw_logged?'warga/profil':'warga/login') ?>" aria-label="<?= $mw_logged?'Profil saya':'Masuk warga' ?>"><?= official_svg('person') ?></a></div>
</div>
