/*
 * Form Pengajuan Surat versi HP (desain 14 Nov 2026).
 * Lapisan tampilan: semua aksi memicu kontrol asli (data-goto, radio data_source, select type, input purpose,
 * tombol #lrq-submit). Validasi, isi otomatis KK, preview, unggah & pengiriman tetap oleh letter-request.js.
 */
(() => {
  const form = document.getElementById('letter-request-form');
  const main = document.getElementById('lrq');
  if (!form || !main || !document.querySelector('.lrqm-top')) return;
  const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
  const typeSelect = $('#request-type');
  const fire = (el, type) => el && el.dispatchEvent(new Event(type, { bubbles: true }));

  /* ---- Langkah: ikuti status langkah asli ---- */
  const currentStep = () => { const li = $('[data-step-tab].is-current'); return li ? Number(li.dataset.stepTab) : 1; };
  const goto = n => $(`[data-goto="${n}"]`)?.click();
  const submitText = $('#lrqm-submit-text');
  const syncSteps = () => {
    const s = currentStep();
    $$('[data-lrqm-step]').forEach(li => {
      const n = Number(li.dataset.lrqmStep), mine = s === 3 ? 3 : 1;
      li.classList.toggle('is-current', n === mine); li.classList.toggle('is-done', n < mine);
    });
    main.dataset.lrqmStep = s;
    if (submitText) submitText.textContent = s === 1 ? 'Ajukan Surat' : (s === 2 ? 'Lanjut ke Preview' : 'Kirim Pengajuan');
  };
  $$('[data-step-tab]').forEach(li => new MutationObserver(syncSteps).observe(li, { attributes: true, attributeFilter: ['class'] }));
  $$('[data-lrqm-goto]').forEach(b => b.addEventListener('click', () => goto(Number(b.dataset.lrqmGoto))));
  $('#lrqm-submit')?.addEventListener('click', () => {
    const s = currentStep();
    if (s === 1) goto(typeSelect.value === 'kematian' ? 2 : 3);   // Surat kematian wajib dokumen: buka langkah dokumen dulu.
    else if (s === 2) goto(3);
    else $('#lrq-submit')?.click();                                 // tombol kirim asli (action=submit)
  });

  /* ---- Kartu akordeon ---- */
  $$('.lrqm-card').forEach(card => card.querySelector('.lrqm-card-head')?.addEventListener('click', e => {
    const open = !card.classList.contains('is-open'); card.classList.toggle('is-open', open); e.currentTarget.setAttribute('aria-expanded', open ? 'true' : 'false');
  }));
  const openCardOf = el => { const c = el?.closest('.lrqm-card'); if (c && !c.classList.contains('is-open')) { c.classList.add('is-open'); c.querySelector('.lrqm-card-head')?.setAttribute('aria-expanded', 'true'); } };

  /* ---- Sumber data (KK / manual) ---- */
  const title = $('.lrqm-person-title');
  const syncSource = () => {
    const manual = !document.querySelector('input[name=data_source][value=kk]:checked');
    main.classList.toggle('lrqm-manual', manual);
    $$('[data-lrqm-src]').forEach(b => b.setAttribute('aria-pressed', String((b.dataset.lrqmSrc === 'manual') === manual)));
    if (title) title.textContent = manual ? title.dataset.manual : title.dataset.kk;
  };
  $$('[data-lrqm-src]').forEach(b => b.addEventListener('click', () => {
    const r = document.querySelector(`input[name=data_source][value="${b.dataset.lrqmSrc}"]`);
    if (r && !r.disabled && !r.checked) r.click();
    syncSource(); openCardOf(title);
  }));
  form.addEventListener('change', e => { if (e.target.name === 'data_source') syncSource(); });

  /* ---- Ringkasan data KK + "Ubah jika perlu" ---- */
  const refreshSum = () => $$('[data-sum]').forEach(dd => { const f = form.elements[dd.dataset.sum]; dd.textContent = (f && f.value.trim()) || '—'; });
  form.addEventListener('input', refreshSum); form.addEventListener('change', refreshSum);
  const memberValue = $('#lrq-member-value');
  if (memberValue) new MutationObserver(() => setTimeout(refreshSum, 0)).observe(memberValue, { childList: true, characterData: true, subtree: true });
  const editBtn = $('#lrqm-show-data');
  const showPerson = on => { main.classList.toggle('lrqm-show-person', on); editBtn?.setAttribute('aria-expanded', String(on)); if (editBtn) editBtn.lastChild.textContent = on ? 'Sembunyikan detail' : 'Ubah jika perlu'; };
  editBtn?.addEventListener('click', () => showPerson(!main.classList.contains('lrqm-show-person')));
  // Bila validasi asli menandai kolom yang tersembunyi, buka bagiannya agar bisa diperbaiki.
  new MutationObserver(muts => {
    muts.forEach(m => m.addedNodes.forEach(n => {
      if (n.nodeType === 1 && n.classList.contains('lrq-err')) { if (n.closest('.lrqm-person-part')) showPerson(true); openCardOf(n); }
    }));
    const badMember = $('#lrq-member-btn.is-invalid'); if (badMember) openCardOf(badMember);
  }).observe(form, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });

  /* ---- Surat populer ---- */
  const syncType = () => $$('[data-lrqm-type]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.lrqmType === typeSelect.value)));
  $$('[data-lrqm-type]').forEach(b => b.addEventListener('click', () => {
    if (!b.dataset.lrqmType) { openCardOf(typeSelect); typeSelect.focus(); return; }
    typeSelect.value = b.dataset.lrqmType; fire(typeSelect, 'change'); syncType();
  }));
  typeSelect.addEventListener('change', syncType);

  /* ---- Keperluan surat (chip -> isi kolom keperluan asli) ---- */
  const purpose = form.elements.purpose;
  const syncPurpose = () => $$('[data-lrqm-purpose]').forEach(b => b.setAttribute('aria-pressed', String(!!b.dataset.lrqmPurpose && b.dataset.lrqmPurpose === purpose.value.trim())));
  $$('[data-lrqm-purpose]').forEach(b => b.addEventListener('click', () => {
    if (b.dataset.lrqmPurpose) { purpose.value = b.dataset.lrqmPurpose; fire(purpose, 'input'); fire(purpose, 'change'); }
    else { purpose.value = ''; fire(purpose, 'input'); purpose.focus(); }
    syncPurpose();
  }));
  purpose?.addEventListener('input', syncPurpose);

  /* ---- Tambah keterangan (sakelar) ---- */
  const descOn = $('#lrqm-desc-on');
  const syncDesc = () => main.classList.toggle('lrqm-desc-on', !!descOn?.checked);
  descOn?.addEventListener('change', () => { syncDesc(); if (descOn.checked) form.elements.description.focus(); });

  /* ---- Preview sebagai lembar penuh ---- */
  const closePreview = () => main.classList.remove('lrqm-preview-open');
  $('#lrqm-preview-open')?.addEventListener('click', () => { $('#lrq-refresh')?.click(); main.classList.add('lrqm-preview-open'); $('#lrqm-preview-close')?.focus(); });
  $('#lrqm-preview-close')?.addEventListener('click', closePreview);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });

  syncSource(); syncType(); syncPurpose(); syncDesc(); refreshSum(); syncSteps();
  // Draft / kiriman ulang dengan keterangan terisi: tampilkan isiannya.
  if (form.elements.description?.value.trim() && descOn) { descOn.checked = true; syncDesc(); }
})();
