(() => {
  const statusClass = { pending: 'is-pending', approved: 'is-approved', rejected: 'is-rejected', revision_requested: 'is-revision' };

  /* ---------- Panel Detail Pengajuan (halaman daftar) ---------- */
  const panel = document.getElementById('akk-panel');
  let current = null;
  const fill = (row) => {
    if (!panel || !row) return;
    const d = JSON.parse(row.dataset.detail);
    current = d;
    document.querySelectorAll('.akk-row.is-selected').forEach(r => r.classList.remove('is-selected'));
    row.classList.add('is-selected');
    const set = (key, value) => panel.querySelectorAll(`[data-f="${key}"]`).forEach(el => { el.textContent = value; });
    ['code', 'name', 'nomor_kk', 'kepala', 'alamat', 'rt_rw', 'count', 'date', 'status_label'].forEach(k => set(k, d[k]));
    const badge = panel.querySelector('[data-f="status_label"]');
    badge.className = `akk-badge ${statusClass[d.status] || ''}`;
    const list = panel.querySelector('[data-f="members"]');
    list.replaceChildren(...d.members.map(m => {
      const li = document.createElement('li');
      const name = document.createElement('strong'); name.textContent = m.nama || '-';
      const meta = document.createElement('small'); meta.textContent = `${m.hubungan || '-'} · ${m.nik || 'NIK kosong'}`;
      li.append(name, meta); return li;
    }));
    const noteBox = panel.querySelector('[data-f="notes-box"]');
    noteBox.hidden = !d.notes;
    set('notes', d.notes || '');
    panel.querySelector('[data-f="url"]').href = d.url;
    panel.querySelector('[data-f="actions"]').hidden = d.status !== 'pending';
    panel.hidden = false;
  };
  if (panel) {
    const rows = [...document.querySelectorAll('.akk-row')];
    const wanted = new URLSearchParams(location.search).get('lihat');
    fill(rows.find(r => JSON.parse(r.dataset.detail).id === Number(wanted)) || rows[0]);
    document.querySelector('.akk-table')?.addEventListener('click', event => {
      // The eye icon is a real link to the full detail page; decision buttons open the dialog.
      if (event.target.closest('[data-decide], a')) return;
      const row = event.target.closest('.akk-row');
      if (!row) return;
      fill(row);
      // Visible feedback even when the same request was already shown.
      panel.classList.remove('is-flash'); void panel.offsetWidth; panel.classList.add('is-flash');
      // When the panel sits below the table (narrow screens), bring it into view.
      if (panel.getBoundingClientRect().top > innerHeight) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.getElementById('akk-panel-close')?.addEventListener('click', () => {
      panel.hidden = true;
      document.querySelectorAll('.akk-row.is-selected').forEach(r => r.classList.remove('is-selected'));
    });
  }

  /* ---------- Stepper halaman detail: tandai bagian yang sedang dilihat ---------- */
  const steps = document.getElementById('akd-steps');
  if (steps && 'IntersectionObserver' in window) {
    const links = [...steps.querySelectorAll('a[href^="#"]')];
    const visible = new Map();
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => visible.set(e.target.id, e.isIntersecting ? e.intersectionRatio : 0));
      // Highest visible section wins; ties go to the earlier one.
      let best = null, ratio = 0;
      links.forEach(a => { const r = visible.get(a.hash.slice(1)) || 0; if (r > ratio) { ratio = r; best = a; } });
      if (best) links.forEach(a => a.classList.toggle('is-active', a === best));
    }, { threshold: [0, .25, .5, .75, 1] });
    links.forEach(a => { const target = document.getElementById(a.hash.slice(1)); if (target) observer.observe(target); });
  }

  /* ---------- Dialog keputusan: Setujui / Tolak / Minta Perbaikan ---------- */
  const dialog = document.getElementById('akk-decide');
  if (!dialog) return;
  const form = document.getElementById('akk-decide-form');
  const own = document.getElementById('akk-own');
  const ownWrap = document.getElementById('akk-own-wrap');
  const notes = document.getElementById('akk-notes');
  const error = document.getElementById('akk-decide-error');
  const submit = document.getElementById('akk-decide-submit');
  const submitLabel = document.getElementById('akk-decide-label');
  const submitIcon = submit.querySelector('.akd-btn-ic');
  const counter = document.getElementById('akk-note-count');
  const icons = document.getElementById('akk-icons').content;
  const modes = {
    approved: { title: 'Setujui Pengajuan?', lead: 'Data keluarga akan disimpan sebagai data terverifikasi dan langsung tampil di akun warga.', label: 'Catatan untuk warga (opsional)', placeholder: 'Contoh: Data sudah sesuai dengan KK asli.', button: 'Ya, Setujui', own: true, need: false },
    rejected: { title: 'Tolak Pengajuan?', lead: 'Pengajuan akan ditolak dan warga melihat alasan penolakan di akunnya.', label: 'Alasan penolakan (wajib)', placeholder: 'Contoh: Nomor KK tidak sesuai dengan dokumen asli.', button: 'Ya, Tolak', own: false, need: true },
    revision_requested: { title: 'Minta Perbaikan Data?', lead: 'Pengajuan dikembalikan ke warga untuk diperbaiki lalu diajukan ulang.', label: 'Bagian yang perlu diperbaiki (wajib)', placeholder: 'Contoh: Tanggal lahir anggota ke-3 belum diisi.', button: 'Kirim Permintaan', own: false, need: true },
  };
  const updateCount = () => { counter.textContent = `${notes.value.length} / ${notes.maxLength}`; counter.classList.toggle('is-near', notes.value.length > notes.maxLength * 0.9); };
  const open = (status, id, code, name) => {
    const m = modes[status];
    if (!m || !id) return;
    form.action = `${dialog.dataset.base}/${id}`;
    dialog.dataset.mode = status; // drives colours, shield symbol and illustration marks
    document.getElementById('akk-decide-status').value = status;
    document.getElementById('akk-decide-title').textContent = m.title;
    document.getElementById('akk-decide-sub').textContent = `${code} · ${name}`;
    document.getElementById('akk-decide-lead').textContent = m.lead;
    document.getElementById('akk-note-label').textContent = m.label;
    notes.placeholder = m.placeholder; notes.value = ''; notes.required = m.need; updateCount();
    ownWrap.hidden = !m.own; own.checked = false; own.required = m.own;
    submitIcon.replaceChildren(icons.querySelector(`[data-k="${status}"]`).firstElementChild.cloneNode(true));
    submitLabel.textContent = m.button; submit.disabled = false;
    error.hidden = true;
    [notes, ownWrap].forEach(el => el.classList.remove('is-invalid'));
    dialog.showModal();
    (m.own ? own : notes).focus();
  };
  document.addEventListener('click', event => {
    const btn = event.target.closest('[data-decide]');
    if (!btn) return;
    if (btn.dataset.id) open(btn.dataset.decide, btn.dataset.id, btn.dataset.code, btn.dataset.name);
    else if (current) open(btn.dataset.decide, current.id, current.code, current.name);
  });
  dialog.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
  dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  form.noValidate = true;
  form.addEventListener('submit', event => {
    const problems = [];
    if (own.required && !own.checked) { problems.push('Centang konfirmasi pemeriksaan terlebih dahulu.'); ownWrap.classList.add('is-invalid'); }
    if (notes.required && !notes.value.trim()) { problems.push('Isi catatan/alasan agar warga tahu yang harus diperbaiki.'); notes.classList.add('is-invalid'); }
    if (problems.length) {
      event.preventDefault();
      error.textContent = problems.join(' ');
      error.hidden = false;
      (own.required && !own.checked ? own : notes).focus();
      return;
    }
    submit.disabled = true; submitLabel.textContent = 'Memproses…';
  });
  own.addEventListener('change', () => { ownWrap.classList.toggle('is-invalid', own.required && !own.checked); if (own.checked && !(notes.required && !notes.value.trim())) error.hidden = true; });
  notes.addEventListener('input', () => { notes.classList.toggle('is-invalid', notes.required && !notes.value.trim()); updateCount(); });
})();
