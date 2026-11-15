/*
 * Iuran Saya versi HP: tab Ringkasan / Riwayat / Tagihan + filter riwayat pembayaran.
 * Tab "Tagihan" menampilkan tabel tagihan yang sudah ada (form Kirim Pembayaran tidak diubah).
 */
(() => {
  const root = document.querySelector('.idm');
  if (!root) return;
  const page = root.closest('main');
  const tabs = [...root.querySelectorAll('.idm-tabs [data-idm-tab]')];
  const motion = matchMedia('(prefers-reduced-motion:reduce)').matches ? 'auto' : 'smooth';
  const show = name => {
    tabs.forEach(t => { const on = t.dataset.idmTab === name; t.setAttribute('aria-selected', on ? 'true' : 'false'); t.tabIndex = on ? 0 : -1; });
    root.querySelectorAll('[data-idm-panel]').forEach(p => { p.hidden = p.dataset.idmPanel !== name; });
    page.dataset.idmTab = name;
  };
  root.querySelectorAll('[data-idm-tab]').forEach(b => b.addEventListener('click', () => {
    show(b.dataset.idmTab);
    if (!b.closest('.idm-tabs')) root.querySelector('.idm-tabs').scrollIntoView({ block: 'start', behavior: motion });
  }));
  root.querySelector('.idm-tabs').addEventListener('keydown', e => {
    if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
    const i = tabs.findIndex(t => t.getAttribute('aria-selected') === 'true');
    const n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
    n.click(); n.focus();
  });

  // Buka tagihan tertentu di tab Tagihan (untuk "Bayar Iuran" dan "Detail" riwayat).
  const openDue = (id, focusPay) => {
    show('tagihan');
    const row = document.getElementById('detail-' + id), toggle = document.querySelector('[data-toggle="detail-' + id + '"]');
    if (row && row.hidden && toggle) toggle.click();
    (row || document.getElementById('tagihan'))?.scrollIntoView({ block: 'start', behavior: motion });
    if (focusPay) setTimeout(() => row?.querySelector('input[name="amount"]')?.focus({ preventScroll: true }), 350);
  };
  root.querySelector('[data-idm-pay]')?.addEventListener('click', e => openDue(e.currentTarget.dataset.idmPay, true));
  root.querySelectorAll('[data-idm-detail]').forEach(b => b.addEventListener('click', () => openDue(b.dataset.idmDetail, false)));

  // Filter riwayat pembayaran.
  const period = document.getElementById('idm-f-period'), status = document.getElementById('idm-f-status');
  const q = document.getElementById('idm-f-q'), qwrap = document.getElementById('idm-f-qwrap'), find = document.getElementById('idm-f-find');
  const rows = [...root.querySelectorAll('.idm-log li')], none = document.getElementById('idm-log-none');
  const chips = [...root.querySelectorAll('[data-idm-chip]')];
  const apply = () => {
    const p = period?.value || '', s = status?.value || '', t = (q?.value || '').trim().toLocaleLowerCase('id');
    let shown = 0;
    rows.forEach(r => { const ok = (!p || r.dataset.period === p) && (!s || r.dataset.status === s) && (!t || r.dataset.search.includes(t)); r.hidden = !ok; if (ok) shown++; });
    if (none) none.hidden = shown > 0 || !rows.length;
    chips.forEach(c => c.classList.toggle('is-on', c.dataset.idmChip === p));
  };
  period?.addEventListener('change', apply);
  status?.addEventListener('change', apply);
  q?.addEventListener('input', apply);
  chips.forEach(c => c.addEventListener('click', () => { if (period) period.value = period.value === c.dataset.idmChip ? '' : c.dataset.idmChip; apply(); }));
  find?.addEventListener('click', () => { const open = qwrap.hidden; qwrap.hidden = !open; find.setAttribute('aria-expanded', open ? 'true' : 'false'); if (open) q.focus(); else { q.value = ''; apply(); } });
  root.querySelector('[data-idm-reset]')?.addEventListener('click', () => { if (period) period.value = ''; if (status) status.value = ''; if (q) q.value = ''; apply(); });
  apply();

  if (root.querySelector('.idm-flash.error')) show('tagihan'); else show('ringkasan');
})();
