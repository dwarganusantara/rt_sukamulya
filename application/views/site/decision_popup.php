<?php
$this->load->helper(array('citizen_notice', 'family_ui'));
$decision_items = citizen_notice_items((int) $this->session->userdata('citizen_id'));
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-decision.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-decision.css') ?>">
<dialog class="cdp" id="cdp" data-tone="approved" aria-labelledby="cdp-title" aria-describedby="cdp-message"
 data-feed="<?= site_url('warga/keputusan') ?>" data-ack="<?= site_url('warga/keputusan/baca') ?>"
 data-initial="<?= html_escape(json_encode($decision_items, JSON_UNESCAPED_UNICODE)) ?>">
 <div class="cdp-confetti" aria-hidden="true"><?php for ($i = 0; $i < 18; $i++): ?><i></i><?php endfor; ?></div>
 <button type="button" class="cdp-x" data-cdp-close aria-label="Tutup"><?= family_icon('close') ?></button>
 <div class="cdp-hero">
  <span class="cdp-badge" aria-hidden="true">
   <i class="cdp-ring"></i>
   <span class="cdp-sym sym-approved"><?= family_icon('check') ?></span>
   <span class="cdp-sym sym-rejected"><?= family_icon('close') ?></span>
   <span class="cdp-sym sym-revision"><?= family_icon('edit') ?></span>
  </span>
  <p class="cdp-kicker" id="cdp-kicker">Kabar dari Admin RT</p>
  <h2 id="cdp-title">Pengajuan Disetujui!</h2>
  <p class="cdp-message" id="cdp-message"></p>
 </div>
 <div class="cdp-body">
  <dl class="cdp-info">
   <div><dt><?= family_icon('file') ?><span id="cdp-l-type">Jenis Pengajuan</span></dt><dd id="cdp-type">-</dd></div>
   <div><dt><?= family_icon('hash') ?><span id="cdp-l-code">No. Pengajuan</span></dt><dd id="cdp-code">-</dd></div>
   <div><dt><?= family_icon('calendar') ?><span id="cdp-l-time">Diproses</span></dt><dd id="cdp-time">-</dd></div>
   <div><dt><?= family_icon('info') ?>Status</dt><dd><span class="cdp-status" id="cdp-status">Disetujui</span></dd></div>
   <div id="cdp-program-row" hidden><dt><?= family_icon('heart') ?>Program Bantuan</dt><dd id="cdp-program">-</dd></div>
   <div id="cdp-amount-row" hidden><dt><b class="cdp-rp" aria-hidden="true">Rp</b>Nominal Bantuan</dt><dd id="cdp-amount" class="cdp-amount">-</dd></div>
  </dl>
  <div class="cdp-note" id="cdp-note-box" hidden><strong><?= family_icon('person') ?>Catatan Admin</strong><p id="cdp-note"></p></div>
 </div>
 <footer class="cdp-foot">
  <span class="cdp-pager" id="cdp-pager" hidden><b id="cdp-pos">1</b> dari <b id="cdp-total">1</b> kabar</span>
  <div class="cdp-actions">
   <button type="button" class="cdp-btn is-ghost" id="cdp-next" data-cdp-next>Berikutnya<?= family_icon('arrow') ?></button>
   <a class="cdp-btn is-main" id="cdp-go" href="#"><span id="cdp-go-label">Lihat Detail</span><?= family_icon('arrow') ?></a>
  </div>
 </footer>
</dialog>
<script src="<?= base_url('assets/citizen-decision.js') ?>?v=<?= filemtime(FCPATH.'assets/citizen-decision.js') ?>" defer></script>
