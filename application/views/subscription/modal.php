<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/subscription.css') ?>?v=<?= filemtime(FCPATH.'assets/subscription.css') ?>">
<dialog class="sub-modal" id="subscription-modal" aria-labelledby="sub-title" aria-describedby="sub-description" data-status-url="<?= site_url('admin/langganan/status') ?>" data-tenant-name="<?= html_escape(trim(($site_settings['brand_label']??'RT/RW').' '.($site_settings['brand_subtitle']??''))) ?>">
 <button type="button" class="sub-close" data-sub-close aria-label="Tutup">×</button>
 <div class="sub-crown" aria-hidden="true"><svg viewBox="0 0 100 100"><defs><linearGradient id="sub-gold" x2="0" y2="1"><stop stop-color="#ffb323"/><stop offset="1" stop-color="#ff8500"/></linearGradient></defs><rect x="5" y="5" width="82" height="82" rx="27" fill="#fff0d4"/><path d="M22 39l17 11 10-22 12 22 17-11-8 32H30zM31 78h38" fill="url(#sub-gold)" stroke="#fff" stroke-width="2"/><circle cx="49" cy="28" r="5" fill="#ff9c00"/><circle cx="78" cy="78" r="18" fill="#ff8500" stroke="white" stroke-width="3"/><rect x="71" y="75" width="14" height="12" rx="2" fill="none" stroke="white" stroke-width="2"/><path d="M73 75v-4a5 5 0 0 1 10 0v4M78 79v4" fill="none" stroke="white" stroke-width="2"/></svg></div>
 <h2 id="sub-title">Fitur Berlangganan</h2>
 <p id="sub-description" data-sub-message>Fitur <strong data-sub-name></strong> belum dapat digunakan untuk RT/RW Anda.<br>Untuk menggunakan fitur ini, silakan berlangganan / subscribe dengan menghubungi Pengembang.</p>
 <p class="sub-status" data-sub-status role="status"></p>
 <div class="sub-contacts">
  <a data-sub-wa href="https://wa.me/6285697578958" target="_blank" rel="noopener noreferrer"><span class="sub-contact-icon sub-wa" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M26 5a14 14 0 0 0-22 17L2 30l8-2A14 14 0 0 0 26 5Z" fill="#fff"/><path d="M23 20c-1-.5-3-1.5-3.5-1.2l-1.4 1.7c-3-1-5-3-6-6l1.6-1.4c.4-.5-.8-3-1.2-3.8-.3-.8-1.2-.8-2-.5-2.8 1-3 4-.8 7.5 2.2 3.6 5.6 6.3 9.6 7 2 .4 4.2-1.4 3.7-3.3Z" fill="#0eae43"/></svg></span><span>Hubungi via WhatsApp<strong>085697578958 <small>(Chendy)</small></strong></span><b aria-hidden="true">›</b></a>
  <a href="https://www.warganusa.com" target="_blank" rel="noopener noreferrer"><span class="sub-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/><path d="M2 12h20M4 6h16M4 18h16"/></svg></span><span>Kunjungi Website<strong>www.warganusa.com</strong></span><b aria-hidden="true">›</b></a>
 </div>
 <a class="sub-primary" href="<?= site_url('admin/langganan') ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 7l5 4 4-8 4 8 5-4-3 12H6zM6 21h12v-2H6z"/></svg> Lihat Paket Berlangganan</a>
 <button type="button" class="sub-later" data-sub-close>Nanti Saja</button>
</dialog>
<script src="<?= base_url('assets/subscription.js') ?>?v=<?= filemtime(FCPATH.'assets/subscription.js') ?>" defer></script>
