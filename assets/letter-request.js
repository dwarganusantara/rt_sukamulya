(() => {
  const form = document.getElementById('letter-request-form');
  if (!form) return;
  const data = JSON.parse(document.getElementById('lrq-data').textContent);
  const el = name => form.elements[name];
  const val = name => (el(name)?.value || '').trim();
  const typeSelect = document.getElementById('request-type');

  /* ---------------- Preview surat (template dari admin) ---------------- */
  const show = name => val(name) || '—';
  const formatDate = v => { const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v || ''); if (!m) return v || '—'; const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; return `${+m[3]} ${bulan[+m[2] - 1]} ${m[1]}`; };
  const updatePreview = () => {
    const values = { ...data.master,
      nama_warga: show('applicant_name'), nik_warga: show('applicant_nik'),
      tempat_tanggal_lahir: `${show('applicant_birth_place')}, ${formatDate(val('applicant_birth_date'))}`,
      jenis_kelamin: show('applicant_gender'), agama: show('applicant_religion'), pekerjaan: show('applicant_occupation'),
      alamat_ktp: show('applicant_ktp_address'), alamat_domisili: show('address'), keperluan_surat: show('purpose'),
      nama: show('applicant_name'), nik: show('applicant_nik'), alamat: show('address'), keperluan: show('purpose'), keterangan: show('description'),
      nama_almarhum: show('deceased_name'), tanggal_wafat: show('death_date'), tempat_wafat: show('death_place'), sebab_wafat: show('death_cause') };
    new FormData(form).forEach((value, key) => { if (typeof value === 'string' && !(key in values)) values[key] = value.trim() || '—'; });
    let template = data.templates[typeSelect.value] || 'Yang bertanda tangan di bawah ini {{jabatan_ketua_rt}}, menerangkan bahwa:\nNama Lengkap : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nAgama : {{agama}}\nPekerjaan : {{pekerjaan}}\nAlamat Sesuai KTP : {{alamat_ktp}}\nAlamat Domisili : {{alamat_domisili}}\nKeperluan : {{keperluan_surat}}';
    template = template.replace(/^\s*#\s+[^\r\n]+[\r\n]+/u, '').replace(/^\s*\*\*?Nomor\s*:\s*\{\{nomor_surat\}\}\*\*?\s*[\r\n]+/iu, '')
      .split(/\{\{(?:kota_penerbitan|tanda_tangan_rt|stempel_rt)\}\}/u)[0].replace(/\*\*/g, '').replace(/\{\{([a-z_]+)\}\}/g, (_, key) => values[key] || '—');
    const body = document.getElementById('preview-letter-body'); body.replaceChildren(); let table = null;
    template.trim().split(/\r?\n/u).forEach(raw => {
      const line = raw.trim();
      if (!line) { table = null; return; }
      const row = line.match(/^([^:]{2,45})\s*:\s*(.*)$/u);
      if (row) {
        if (!table) { table = document.createElement('table'); table.className = 'request-preview-fields'; body.appendChild(table); }
        const tr = document.createElement('tr'); const th = document.createElement('th'); th.textContent = row[1].trim();
        const colon = document.createElement('td'); colon.textContent = ':'; const td = document.createElement('td'); td.textContent = row[2].trim();
        tr.append(th, colon, td); table.appendChild(tr);
      } else { table = null; const p = document.createElement('p'); p.textContent = line; body.appendChild(p); }
    });
  };
  const updateType = () => {
    const isDeath = typeSelect.value === 'kematian';
    document.querySelectorAll('[data-type-fields]').forEach(g => { g.hidden = g.dataset.typeFields !== typeSelect.value; });
    document.getElementById('death-documents').hidden = !isDeath;
    const name = typeSelect.options[typeSelect.selectedIndex].text;
    document.getElementById('preview-letter-title').textContent = name.toUpperCase();
    document.getElementById('lrq-title-name').textContent = name;
    document.getElementById('lrq-paper-name').textContent = name.toUpperCase();
    if (typeof fillKkExtras === 'function') fillKkExtras();
    updatePreview();
  };
  typeSelect.addEventListener('change', updateType);
  form.addEventListener('input', updatePreview);
  form.addEventListener('change', updatePreview);
  document.getElementById('lrq-refresh').addEventListener('click', () => {
    updatePreview();
    const p = document.getElementById('lrq-preview');
    p.classList.remove('is-flash'); void p.offsetWidth; p.classList.add('is-flash');
  });

  /* ---------------- Sumber data: KK atau manual ---------------- */
  const kk = document.getElementById('lrq-kk');
  const autoFields = ['applicant_name', 'applicant_nik', 'applicant_birth_place', 'applicant_birth_date', 'applicant_gender', 'applicant_religion', 'applicant_occupation', 'applicant_ktp_address', 'address'];
  const religion = v => { const s = (v || '').toUpperCase(); if (s.includes('ISLAM')) return 'Islam'; if (s.includes('KATOLIK') || s.includes('KATHOLIK')) return 'Katolik'; if (s.includes('KRISTEN') || s.includes('PROTESTAN')) return 'Kristen (Protestan)'; if (s.includes('HINDU')) return 'Hindu'; if (s.includes('BUDHA') || s.includes('BUDDHA')) return 'Buddha'; if (s.includes('KONGHUCU') || s.includes('KHONGHUCU')) return 'Khonghucu'; return ''; };
  const titleCase = s => (s || '').toLowerCase().replace(/(^|[\s/(.-])(\S)/g, (m, a, b) => a + b.toUpperCase());
  const setField = (name, value) => { const f = el(name); if (!f) return; f.value = value || ''; f.classList.toggle('is-autofilled', !!value); clearError(f); };
  const lock = on => form.querySelectorAll('[data-kk-lock]').forEach(f => { f.readOnly = on; f.closest('.lrq-input').classList.toggle('is-locked', on); });

  let memberList, memberBtn, memberValue, options = [];
  const chooseMember = (li, focusBack = true) => {
    const m = JSON.parse(li.dataset.member);
    options.forEach(o => o.setAttribute('aria-selected', String(o === li)));
    memberValue.textContent = `${m.nama} (${m.hubungan || 'Anggota'})`;
    memberValue.classList.remove('is-placeholder');
    memberBtn.classList.remove('is-invalid');
    setField('applicant_name', m.nama); setField('applicant_nik', m.nik);
    setField('applicant_birth_place', titleCase(m.tempat_lahir)); setField('applicant_birth_date', m.tanggal_lahir);
    setField('applicant_gender', m.jk === 'P' ? 'Perempuan' : (m.jk === 'L' ? 'Laki-laki' : ''));
    setField('applicant_religion', religion(m.agama)); setField('applicant_occupation', titleCase(m.pekerjaan));
    setField('applicant_ktp_address', kk.dataset.address);
    const dom = el('address'); if (!dom.value.trim() || dom.classList.contains('is-autofilled')) setField('address', kk.dataset.address);
    lock(true); closeList(focusBack); updatePreview();
  };
  const openList = () => { memberList.hidden = false; memberBtn.setAttribute('aria-expanded', 'true'); const cur = options.find(o => o.getAttribute('aria-selected') === 'true') || options[0]; setActive(cur); memberList.focus(); };
  const closeList = (focusBack = true) => { if (!memberList || memberList.hidden) return; memberList.hidden = true; memberBtn.setAttribute('aria-expanded', 'false'); if (focusBack) memberBtn.focus(); };
  const setActive = li => { options.forEach(o => o.classList.toggle('is-active', o === li)); if (li) { memberList.setAttribute('aria-activedescendant', li.id); li.scrollIntoView({ block: 'nearest' }); } };
  if (kk) {
    memberList = document.getElementById('lrq-member-list'); memberBtn = document.getElementById('lrq-member-btn'); memberValue = document.getElementById('lrq-member-value');
    options = [...memberList.querySelectorAll('[role=option]')];
    memberBtn.addEventListener('click', () => (memberList.hidden ? openList() : closeList()));
    memberBtn.addEventListener('keydown', e => { if (['ArrowDown', 'ArrowUp'].includes(e.key)) { e.preventDefault(); openList(); } });
    memberList.addEventListener('click', e => { const li = e.target.closest('[role=option]'); if (li) chooseMember(li); });
    memberList.addEventListener('keydown', e => {
      const i = options.findIndex(o => o.classList.contains('is-active'));
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(options[Math.min(i + 1, options.length - 1)]); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(options[Math.max(i - 1, 0)]); }
      else if (e.key === 'Home') { e.preventDefault(); setActive(options[0]); }
      else if (e.key === 'End') { e.preventDefault(); setActive(options[options.length - 1]); }
      else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (options[i]) chooseMember(options[i]); }
      else if (e.key === 'Escape' || e.key === 'Tab') closeList(e.key === 'Escape');
    });
    document.addEventListener('click', e => { if (!document.getElementById('lrq-member').contains(e.target)) closeList(false); });
  }
  /* Data khusus per jenis surat yang bisa diambil dari KK */
  const kkValue = kind => (!kk ? '' : kind === 'head' ? kk.dataset.head : kind === 'kk_number' ? kk.dataset.kkNumber : kind === 'address' ? kk.dataset.address : '');
  var fillKkExtras = () => {
    if (!kk || kk.hidden) return;
    form.querySelectorAll('[data-kk-fill]').forEach(f => { if (!f.value.trim() || f.classList.contains('is-autofilled')) setField(f.name, kkValue(f.dataset.kkFill)); });
  };
  const clearKkExtras = () => form.querySelectorAll('[data-kk-fill],[data-person-filled]').forEach(f => { if (f.classList.contains('is-autofilled')) { f.value = ''; f.classList.remove('is-autofilled'); } f.removeAttribute('data-person-filled'); });
  // Typing / picking a KK member's name (e.g. almarhum, ayah, ibu) fills that person's related fields.
  form.addEventListener('change', e => {
    const f = e.target; if (!f.dataset?.person) return;
    const m = (data.members || []).find(x => x.nama.trim().toUpperCase() === f.value.trim().toUpperCase());
    if (!m) return;
    const map = JSON.parse(f.dataset.person);
    const from = { nik: m.nik, tempat_lahir: titleCase(m.tempat_lahir), tanggal_lahir: m.tanggal_lahir, jk: m.jk === 'P' ? 'Perempuan' : (m.jk === 'L' ? 'Laki-laki' : ''), agama: religion(m.agama), address: kk ? kk.dataset.address : '' };
    Object.entries(map).forEach(([key, target]) => { const t = el(target); if (t && from[key]) { setField(target, from[key]); t.setAttribute('data-person-filled', ''); } });
    updatePreview();
  });
  form.querySelectorAll('[data-members-into]').forEach(b => b.addEventListener('click', () => {
    const list = (data.members || []).map((m, i) => `${i + 1}. ${m.nama} (${m.hubungan || 'Anggota'})`).join('\n');
    setField(b.dataset.membersInto, list); el(b.dataset.membersInto).focus(); updatePreview();
  }));
  form.querySelectorAll('[data-kk-into]').forEach(b => b.addEventListener('click', () => { setField(b.dataset.kkInto, kk.dataset.address); updatePreview(); }));
  const desc = el('description'), count = document.getElementById('lrq-desc-count');
  const updateCount = () => { count.textContent = `${desc.value.length} / ${desc.maxLength}`; };
  desc.addEventListener('input', updateCount); updateCount();

  const setSource = (source, fromUser) => {
    const useKk = source === 'kk' && kk;
    if (kk) kk.hidden = !useKk;
    if (useKk) {
      const chosen = options.find(o => o.getAttribute('aria-selected') === 'true');
      if (chosen) chooseMember(chosen, false); else lock(false);
      fillKkExtras();
    } else {
      lock(false);
      if (fromUser) { autoFields.forEach(n => { const f = el(n); if (f && f.classList.contains('is-autofilled')) { f.value = ''; f.classList.remove('is-autofilled'); } }); clearKkExtras(); }
      if (fromUser) el('applicant_name').focus();
    }
    updatePreview();
  };
  form.querySelectorAll('input[name=data_source]').forEach(r => r.addEventListener('change', () => setSource(r.value, true)));
  // Manual edits make the value the resident's own (except locked KK identity).
  form.addEventListener('input', e => e.target.classList?.remove('is-autofilled'));

  /* ---------------- Validasi dengan tanda merah ---------------- */
  const errorFor = f => {
    const v = f.validity, label = f.dataset.label || 'Kolom ini';
    if (v.valueMissing) return f.tagName === 'SELECT' ? `${label} wajib dipilih.` : `${label} wajib diisi.`;
    if (v.patternMismatch || v.tooShort) return f.dataset.patternMessage || `${label} tidak sesuai format.`;
    if (v.rangeOverflow) return `${label} tidak boleh melebihi hari ini.`;
    return f.validationMessage || `${label} belum benar.`;
  };
  const clearError = f => { const box = f.closest('.lrq-input'); if (!box) return; box.classList.remove('is-invalid'); box.parentElement.querySelector('.lrq-err')?.remove(); };
  const markError = (f, msg) => {
    const box = f.closest('.lrq-input'); if (!box) return;
    box.classList.add('is-invalid'); box.parentElement.querySelector('.lrq-err')?.remove();
    const p = document.createElement('small'); p.className = 'lrq-err'; p.textContent = msg; box.after(p);
  };
  const validateStep1 = () => {
    const step = form.querySelector('[data-step="1"]');
    const bad = [];
    [...step.querySelectorAll('input,select,textarea')].filter(f => f.name && !f.closest('[hidden]') && f.willValidate).forEach(f => {
      if (f.validity.valid) clearError(f); else { markError(f, errorFor(f)); bad.push(f); }
    });
    const useKk = kk && !kk.hidden;
    if (useKk && !options.some(o => o.getAttribute('aria-selected') === 'true')) { memberBtn.classList.add('is-invalid'); bad.unshift(memberBtn); }
    if (bad.length) { bad[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(() => bad[0].focus({ preventScroll: true }), 350); }
    return !bad.length;
  };
  form.addEventListener('input', e => { if (e.target.closest?.('.lrq-input.is-invalid') && e.target.validity?.valid) clearError(e.target); });
  form.addEventListener('change', e => { if (e.target.closest?.('.lrq-input.is-invalid') && e.target.validity?.valid) clearError(e.target); });

  /* ---------------- Dokumen ---------------- */
  form.querySelectorAll('.lrq-drop input[type=file]').forEach(input => {
    const label = input.closest('.lrq-drop'); const name = label.querySelector('[data-file-name]'); const original = name.textContent;
    const update = () => {
      const file = input.files[0]; const max = Number(input.dataset.max || 5) * 1024 * 1024;
      label.classList.toggle('has-file', !!file); label.classList.toggle('is-invalid', !!file && file.size > max);
      name.textContent = !file ? original : (file.size > max ? `${file.name} terlalu besar (maks. ${input.dataset.max} MB)` : `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`);
    };
    input.addEventListener('change', update);
    ['dragenter', 'dragover'].forEach(t => label.addEventListener(t, e => { e.preventDefault(); label.classList.add('is-drag'); }));
    ['dragleave', 'drop'].forEach(t => label.addEventListener(t, e => { e.preventDefault(); label.classList.remove('is-drag'); }));
    label.addEventListener('drop', e => { if (e.dataTransfer.files.length) { const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]); input.files = dt.files; update(); } });
  });
  const filesOk = () => {
    const bad = [...form.querySelectorAll('.lrq-drop.is-invalid')].filter(l => !l.closest('[hidden]'));
    if (bad.length) bad[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    return !bad.length;
  };

  /* ---------------- Ringkasan konfirmasi ---------------- */
  const buildSummary = () => {
    const rows = [['Jenis Surat', typeSelect.options[typeSelect.selectedIndex].text], ['Sumber Data', kk && !kk.hidden ? 'Data Kartu Keluarga (terverifikasi)' : 'Isi manual'],
      ['Nama Lengkap', val('applicant_name')], ['NIK', val('applicant_nik')], ['Tempat, Tanggal Lahir', [val('applicant_birth_place'), formatDate(val('applicant_birth_date'))].filter(x => x && x !== '—').join(', ')],
      ['Jenis Kelamin', val('applicant_gender')], ['Agama', val('applicant_religion')], ['Pekerjaan', val('applicant_occupation')], ['No. HP', val('applicant_phone')],
      ['Alamat KTP', val('applicant_ktp_address')], ['Alamat Domisili', val('address')], ['Keperluan', val('purpose')], ['Keterangan', val('description')]];
    const extra = form.querySelector(`[data-type-fields="${typeSelect.value}"]`);
    if (extra) extra.querySelectorAll('.lrq-field').forEach(fd => { const f = fd.querySelector('input,textarea,select'); if (f) rows.push([fd.querySelector('label').textContent.trim(), f.value.trim()]); });
    const files = [...form.querySelectorAll('.lrq-drop input[type=file]')].filter(i => !i.closest('[hidden]') && i.files[0]).map(i => `${i.closest('.lrq-drop').querySelector('strong').textContent}: ${i.files[0].name}`);
    rows.push(['Dokumen', files.length ? files.join('\n') : 'Tidak ada dokumen dilampirkan']);
    const dl = document.getElementById('lrq-summary'); dl.replaceChildren();
    rows.forEach(([k, v]) => { const d = document.createElement('div'); const dt = document.createElement('dt'); dt.textContent = k; const dd = document.createElement('dd'); dd.textContent = v || '—'; if (!v) dd.className = 'is-empty'; d.append(dt, dd); dl.append(d); });
  };

  /* ---------------- Navigasi langkah ---------------- */
  let current = 1;
  const go = step => {
    if (step > current && current === 1 && !validateStep1()) return;
    if (step > current && current === 2 && !filesOk()) return;
    if (step === 3) buildSummary();
    current = step;
    form.querySelectorAll('[data-step]').forEach(s => { s.hidden = Number(s.dataset.step) !== step; });
    form.querySelectorAll('[data-actions]').forEach(a => { a.hidden = Number(a.dataset.actions) !== step; });
    form.querySelector('[data-draft-link]').hidden = step === 3;
    document.querySelectorAll('[data-step-tab]').forEach(li => { const n = Number(li.dataset.stepTab); li.classList.toggle('is-current', n === step); li.classList.toggle('is-done', n < step); li.querySelector('button').setAttribute('aria-current', n === step ? 'step' : 'false'); });
    document.querySelector('.lrq-main').scrollIntoView({ behavior: 'smooth', block: 'start' });
  };
  form.querySelectorAll('[data-next]').forEach(b => b.addEventListener('click', () => go(Number(b.dataset.next))));
  form.querySelectorAll('[data-prev]').forEach(b => b.addEventListener('click', () => go(Number(b.dataset.prev))));
  // Step tabs: always allowed backwards; forwards only through the same checks.
  document.querySelectorAll('[data-goto]').forEach(b => b.addEventListener('click', () => { const n = Number(b.dataset.goto); if (n < current) go(n); else if (n === current + 1) go(n); else if (n > current) { if (validateStep1()) { go(2); if (n === 3) go(3); } } }));
  form.addEventListener('submit', e => {
    if (e.submitter?.value === 'draft') return;
    if (!validateStep1()) { e.preventDefault(); go(1); return; }
    if (!filesOk()) { e.preventDefault(); go(2); return; }
    // Disable after the form data is built, so action=submit is still sent.
    const btn = document.getElementById('lrq-submit'); setTimeout(() => { btn.disabled = true; btn.lastChild.textContent = 'Mengirim…'; }, 0);
  });
  window.addEventListener('pageshow', e => { if (e.persisted) { const btn = document.getElementById('lrq-submit'); btn.disabled = false; btn.lastChild.textContent = 'Ajukan Pengajuan'; } });

  /* ---------------- Awal ---------------- */
  updateType();
  if (kk) {
    const initial = kk.dataset.initial && options.find(o => JSON.parse(o.dataset.member).nik === kk.dataset.initial);
    if (initial && !kk.hidden) chooseMember(initial, false);
    else if (!kk.hidden) lock(false);
    fillKkExtras();
  }
})();
