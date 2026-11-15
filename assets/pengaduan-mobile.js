/*
 * Pengaduan & Aspirasi versi HP. Form dikirim biasa ke warga/pengaduan/buat (aturan server tetap berlaku:
 * kategori wajib, judul min. 5, uraian min. 10 karakter, maks. 5 pengaduan/hari).
 * Prioritas "Darurat" = kategori Darurat (server menetapkan prioritas mendesak). Draft disimpan di perangkat ini.
 */
(() => {
  const root = document.querySelector('.pgm');
  if (!root) return;
  let cfg = {}; try { cfg = JSON.parse(document.getElementById('pgm-cfg').textContent); } catch (e) {}
  const $ = s => root.querySelector(s), $$ = s => [...root.querySelectorAll(s)];
  const page = root.closest('main');

  /* Tab */
  const tabs = $$('.pgm-tabs [data-pgm-tab]');
  const show = name => {
    tabs.forEach(t => t.setAttribute('aria-selected', String(t.dataset.pgmTab === name)));
    $$('[data-pgm-panel]').forEach(p => { p.hidden = p.dataset.pgmPanel !== name; });
    if (page) page.dataset.pgmTab = name;
  };
  $$('[data-pgm-tab]').forEach(b => b.addEventListener('click', () => { show(b.dataset.pgmTab); if (!b.closest('.pgm-tabs')) $('.pgm-tabs').scrollIntoView({ block: 'start', behavior: 'smooth' }); }));
  show(location.hash === '#tiket' ? 'tiket' : (location.hash === '#ringkasan' ? 'ringkasan' : (cfg.tab || 'buat')));

  /* Akordeon */
  $$('.pgm-card').forEach(c => c.querySelector('.pgm-card-head').addEventListener('click', e => {
    const open = !c.classList.contains('is-open'); c.classList.toggle('is-open', open); e.currentTarget.setAttribute('aria-expanded', String(open));
  }));
  const openCard = el => { const c = el.closest('.pgm-card'); if (c && !c.classList.contains('is-open')) c.querySelector('.pgm-card-head').click(); };

  /* Prioritas <-> kategori Darurat */
  const form = document.getElementById('pgm-form'); if (!form) return;
  const cat = form.elements.category, note = $('#pgm-sos-note');
  const syncPrio = () => {
    const urgent = cat.value === 'darurat';
    $$('[data-pgm-prio]').forEach(b => b.setAttribute('aria-checked', String((b.dataset.pgmPrio === 'darurat') === urgent)));
    if (note) note.hidden = !urgent;
  };
  $$('[data-pgm-prio]').forEach(b => b.addEventListener('click', () => {
    if (b.dataset.pgmPrio === 'darurat') cat.value = 'darurat'; else if (cat.value === 'darurat') cat.value = '';
    cat.dispatchEvent(new Event('change', { bubbles: true })); syncPrio();
    if (b.dataset.pgmPrio === 'normal' && !cat.value) { openCard(cat); cat.focus(); }
  }));
  cat.addEventListener('change', syncPrio);

  /* Lampiran: nama & ukuran file */
  const file = $('#pgm-file'), fname = $('#pgm-file-name');
  file?.addEventListener('change', () => {
    const f = file.files[0]; const big = f && f.size > 5 * 1024 * 1024;
    fname.textContent = !f ? 'Pilih foto atau dokumen' : (big ? `${f.name} terlalu besar (maks. 5 MB)` : `${f.name} · ${(f.size / 1024 / 1024).toFixed(2)} MB`);
    file.closest('.pgm-file').classList.toggle('is-bad', !!big); file.closest('.pgm-file').classList.toggle('has-file', !!f && !big);
  });

  /* Validasi sebelum kirim (pesan dekat kolom) */
  const msg = $('#pgm-msg');
  const errOf = f => {
    const v = f.value.trim(), min = Number(f.dataset.min || 0);
    if (f.required && !v) return `${f.dataset.label} wajib ${f.tagName === 'SELECT' ? 'dipilih' : 'diisi'}.`;
    if (min && v.length < min) return `${f.dataset.label} minimal ${min} karakter.`;
    return '';
  };
  const mark = (f, text) => {
    const box = f.closest('.pgm-f'); if (!box) return;
    box.classList.toggle('is-bad', !!text); box.querySelector('.pgm-err')?.remove();
    if (text) { const s = document.createElement('small'); s.className = 'pgm-err'; s.textContent = text; box.appendChild(s); }
  };
  [cat, form.elements.title, form.elements.description].forEach(f => f.addEventListener('input', () => { if (f.closest('.is-bad')) mark(f, errOf(f)); }));
  form.addEventListener('submit', e => {
    let first = null;
    [cat, form.elements.title, form.elements.description].forEach(f => { const t = errOf(f); mark(f, t); if (t && !first) first = f; });
    if (file?.files[0] && file.files[0].size > 5 * 1024 * 1024) { first = first || file; openCard(file); }
    if (first) { e.preventDefault(); openCard(first); msg.textContent = 'Lengkapi data laporan yang ditandai merah.'; msg.hidden = false; first.focus({ preventScroll: false }); return; }
    msg.hidden = true; clearDraft();
    const btn = $('#pgm-send'); setTimeout(() => { btn.disabled = true; btn.lastChild.textContent = 'Mengirim…'; }, 0);
  });

  /* Draft di perangkat ini */
  const KEY = cfg.key || 'sukamulya.pengaduan.draft';
  const fields = ['category', 'title', 'location', 'description'];
  const store = { get() { try { return JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { return null; } }, set(v) { try { localStorage.setItem(KEY, JSON.stringify(v)); return true; } catch (e) { return false; } } };
  function clearDraft() { try { localStorage.removeItem(KEY); } catch (e) {} }
  const toast = text => { msg.textContent = text; msg.hidden = false; msg.classList.add('is-ok'); setTimeout(() => { msg.hidden = true; msg.classList.remove('is-ok'); }, 3500); };
  $('#pgm-draft')?.addEventListener('click', () => {
    const v = {}; fields.forEach(n => { v[n] = form.elements[n].value; }); v.at = Date.now();
    toast(store.set(v) ? 'Draft tersimpan di perangkat ini. Lampiran perlu dipilih ulang saat mengirim.' : 'Draft tidak dapat disimpan di perangkat ini.');
  });
  // Pulihkan draft bila form masih kosong (bukan kiriman ulang dengan error).
  const saved = store.get();
  if (saved && fields.every(n => !form.elements[n].value.trim())) {
    fields.forEach(n => { if (saved[n]) form.elements[n].value = saved[n]; });
    toast('Draft pengaduan sebelumnya dipulihkan.');
  }
  syncPrio();
})();
