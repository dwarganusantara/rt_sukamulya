<?php
/*
 * Widget chat melayang admin (pengganti halaman Live Chat Warga).
 * - Tombol "Chat Warga" di pojok kanan bawah setiap halaman admin, lengkap dengan jumlah pesan belum dibaca.
 * - Panel kotak masuk: semua percakapan warga (cari, filter Semua/Online/Belum dibaca).
 * - Multi-chat: beberapa jendela percakapan dapat dibuka bersamaan dan tetap terbuka saat pindah halaman.
 * Endpoint: admin/chat/inbox, admin/chat/pesan/{id}, admin/chat/kirim/{id}. Logika: assets/admin-chat-widget.js
 */
if (!$this->session->userdata('admin_id') || !in_array((string) $this->session->userdata('admin_role'), array('admin', 'operator', 'ketua'), TRUE)) return;
if (!isset($this->feature_gate)) $this->load->library('Feature_gate');
if (!$this->feature_gate->link_ok('admin/chat', 'admin') || !$this->db->table_exists('chat_messages')) return;
$acw_css = 'assets/admin-chat-widget.css'; $acw_js = 'assets/admin-chat-widget.js';
$acw_icon = function ($d, $s = 20) { return '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>'; };
?>
<link rel="stylesheet" href="<?= base_url($acw_css) ?>?v=<?= filemtime(FCPATH . $acw_css) ?>">
<div class="acw" id="acw"
 data-inbox="<?= site_url('admin/chat/inbox') ?>"
 data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
 data-csrf="<?= $this->security->get_csrf_hash() ?>"
 data-admin="<?= (int) $this->session->userdata('admin_id') ?>">
 <div class="acw-dock" id="acw-dock" aria-live="polite"></div>
 <section class="acw-inbox" id="acw-inbox" role="dialog" aria-label="Pesan warga" hidden>
  <header class="acw-inbox-head">
   <span class="acw-inbox-ic"><?= $acw_icon('M21 12a8 8 0 0 1-11.8 7L4 20l1.2-4.4A8 8 0 1 1 21 12z', 22) ?></span>
   <div><strong>Pesan Warga</strong><small id="acw-sum">Memuat percakapan...</small></div>
   <button type="button" class="acw-x" data-acw-close-inbox aria-label="Tutup daftar pesan"><?= $acw_icon('M6 6l12 12M18 6L6 18') ?></button>
  </header>
  <div class="acw-search"><?= $acw_icon('M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5', 18) ?><input type="search" id="acw-q" placeholder="Cari nama warga..." aria-label="Cari nama warga"></div>
  <div class="acw-filter" role="tablist">
   <button type="button" class="on" data-f="all">Semua <b id="acw-n-all">0</b></button>
   <button type="button" data-f="online">Online <b id="acw-n-online">0</b></button>
   <button type="button" data-f="unread">Belum Dibaca <b id="acw-n-unread">0</b></button>
  </div>
  <div class="acw-list" id="acw-list"><p class="acw-empty">Memuat...</p></div>
 </section>
 <button type="button" class="acw-launch" id="acw-launch" aria-controls="acw-inbox" aria-expanded="false">
  <span class="acw-launch-ic"><?= $acw_icon('M21 12a8 8 0 0 1-11.8 7L4 20l1.2-4.4A8 8 0 1 1 21 12zM8.5 12h.01M12 12h.01M15.5 12h.01', 30) ?><b id="acw-badge" hidden>0</b></span>
  <span class="acw-launch-tx"><strong>Chat Warga</strong><small><i id="acw-dot"></i> <span id="acw-online">Memuat...</span></small></span>
 </button>
 <div class="acw-toast" id="acw-toast" role="status" hidden></div>
</div>
<script src="<?= base_url($acw_js) ?>?v=<?= filemtime(FCPATH . $acw_js) ?>" defer></script>
