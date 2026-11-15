<?php $this->load->helper('family_ui'); ?>
<dialog class="akk-dialog" id="akk-decide" data-mode="approved" aria-labelledby="akk-decide-title" aria-describedby="akk-decide-lead" data-base="<?= site_url('admin/kk/pengajuan') ?>">
 <form method="post" id="akk-decide-form" action="#">
  <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()) ?>" value="<?= html_escape($this->security->get_csrf_hash()) ?>">
  <input type="hidden" name="return" value="<?= html_escape($return_to) ?>">
  <input type="hidden" name="status" id="akk-decide-status" value="">
  <button type="button" class="akd-close" data-close aria-label="Tutup"><?= family_icon('close') ?></button>

  <div class="akd-hero">
   <div class="akd-hero-copy">
    <span class="akd-shield" aria-hidden="true">
     <svg viewBox="0 0 64 72" fill="none"><path d="M32 3 58 13v18c0 18-11.5 31-26 38C17.5 62 6 49 6 31V13z" fill="var(--m1)"/><path d="M32 3 58 13v18c0 18-11.5 31-26 38z" fill="var(--m2)" opacity=".35"/><path d="M32 13 49 20v12c0 11-7.3 19.6-17 24.4C22.3 51.6 15 43 15 32V20z" fill="none" stroke="#fff" stroke-width="3.2" stroke-linejoin="round"/>
      <path class="sym sym-approved" d="m24 34 6 6 11-12" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
      <path class="sym sym-rejected" d="m25.5 27.5 13 13m0-13-13 13" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
      <path class="sym sym-revision_requested" d="m36 24 5 5-12 12-6 1 1-6z" stroke="#fff" stroke-width="3.2" stroke-linejoin="round"/></svg>
     <i class="spark s1"></i><i class="spark s2"></i><i class="spark s3"></i>
    </span>
    <h2 id="akk-decide-title">Setujui Pengajuan?</h2>
    <p class="akd-who"><?= family_icon('person') ?><span id="akk-decide-sub">-</span></p>
   </div>
   <svg class="akd-art" viewBox="0 0 260 190" fill="none" aria-hidden="true">
    <ellipse cx="140" cy="105" rx="115" ry="78" fill="var(--m-soft)"/>
    <path d="M40 150c-14-22-6-48 20-58-2 26-6 42-20 58z" fill="#4ade80"/><path d="M48 155c4-28 24-42 48-38-14 22-28 34-48 38z" fill="#22c55e"/>
    <path d="M214 60c18-14 38-10 44 6-20 4-32 2-44-6z" fill="#4ade80"/><path d="M206 74c4-22 22-34 40-30-10 18-22 26-40 30z" fill="#22c55e"/>
    <g transform="rotate(-8 110 90)"><rect x="66" y="24" width="96" height="124" rx="12" fill="var(--m1)"/><rect x="74" y="36" width="80" height="104" rx="6" fill="#fff"/><rect x="96" y="16" width="36" height="16" rx="6" fill="var(--m1)"/><circle cx="114" cy="16" r="5" fill="#fff" stroke="var(--m1)" stroke-width="3"/>
     <g class="sym sym-approved" stroke="var(--m1)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"><path d="m84 62 6 6 10-12M84 88l6 6 10-12M84 114l6 6 10-12"/></g>
     <g class="sym sym-rejected" stroke="var(--m1)" stroke-width="5" stroke-linecap="round"><path d="m85 57 12 12m0-12-12 12M85 83l12 12m0-12-12 12M85 109l12 12m0-12-12 12"/></g>
     <g class="sym sym-revision_requested" stroke="var(--m1)" stroke-width="5" stroke-linecap="round"><path d="M84 63h14M84 89h14M84 115h14"/></g>
     <path d="M108 63h34M108 89h30M108 115h26" stroke="#cbd5e1" stroke-width="5" stroke-linecap="round"/></g>
    <g transform="rotate(7 196 132)"><rect x="140" y="92" width="112" height="76" rx="8" fill="#e6f3ff" stroke="#93c5fd" stroke-width="2"/><text x="196" y="112" text-anchor="middle" font-family="Arial,sans-serif" font-size="11" font-weight="800" fill="#10254e">KARTU KELUARGA</text><rect x="150" y="120" width="92" height="38" fill="#fff" stroke="#93c5fd"/><path d="M150 130h92M150 140h92M150 150h92M172 120v38M196 120v38M220 120v38" stroke="#bfdbfe"/><rect x="150" y="120" width="92" height="6" fill="#bfdbfe"/></g>
   </svg>
  </div>

  <div class="akd-body">
   <p class="akd-lead" id="akk-decide-lead"></p>
   <label class="akd-check" id="akk-own-wrap"><input type="checkbox" name="ownership_checked" value="1" id="akk-own"><span class="akd-box" aria-hidden="true"><?= family_icon('check') ?></span><span>Saya sudah memeriksa kewenangan pemohon dan kesesuaian data dengan Kartu Keluarga asli.</span></label>
   <label class="akd-note" for="akk-notes"><span class="akd-note-label"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.2-4.4A8 8 0 1 1 21 12zM8.5 10.5h7M8.5 14h4.5"/></svg><span id="akk-note-label">Catatan</span></span></label>
   <textarea name="notes" id="akk-notes" rows="3" maxlength="500" aria-describedby="akk-note-count"></textarea>
   <div class="akd-count" id="akk-note-count" aria-live="polite">0 / 500</div>
   <p class="akk-decide-error" id="akk-decide-error" role="alert" hidden></p>
  </div>

  <footer class="akd-foot">
   <button type="button" class="akd-btn is-cancel" data-close><?= family_icon('close') ?>Batal</button>
   <button type="submit" class="akd-btn is-confirm" id="akk-decide-submit"><span class="akd-btn-ic" aria-hidden="true"></span><span id="akk-decide-label">Ya, Setujui</span></button>
  </footer>
 </form>
 <template id="akk-icons"><span data-k="approved"><?= family_icon('check') ?></span><span data-k="rejected"><?= family_icon('close') ?></span><span data-k="revision_requested"><?= family_icon('send') ?></span></template>
</dialog>
