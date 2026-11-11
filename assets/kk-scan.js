(() => {
  const page = document.querySelector('.fui-scan-page');
  if (!page) return;
  const members = document.getElementById('kk-members');
  const count = document.getElementById('kk-count');
  const review = document.getElementById('kk-review');
  const upload = document.getElementById('kk-upload');
  const documentInput = document.getElementById('kk-document');
  const camera = document.getElementById('kk-camera-file');
  const progress = document.getElementById('kk-progress');
  let next = members.querySelectorAll('.fui-member').length;
  /*
   * CSRF guard: the site regenerates the token on every POST, and background GETs
   * (chat polling) that overlap a slow POST (OCR) can write an older token back into
   * the cookie. Re-read the current token right before submitting, then resubmit with
   * the same button. Returns true when the submit was deferred for the refresh.
   */
  const tokenUrl = page.dataset.tokenUrl;
  const submitWithFreshToken = (form, event) => {
    if (form.dataset.tokenFresh === '1') { form.dataset.tokenFresh = ''; return false; }
    if (!tokenUrl || !window.fetch || !form.requestSubmit) return false;
    event.preventDefault();
    const submitter = event.submitter && form.contains(event.submitter) ? event.submitter : null;
    fetch(tokenUrl, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => (response.ok ? response.json() : null))
      .then(token => {
        if (!token?.name || !token?.hash) return;
        form.querySelectorAll('input[type="hidden"]').forEach(input => { if (input.name === token.name) input.value = token.hash; });
      })
      .catch(() => {})
      .finally(() => {
        form.dataset.tokenFresh = '1';
        submitter ? form.requestSubmit(submitter) : form.requestSubmit();
      });
    return true;
  };
  const updateCount = () => {
    const rows = members.querySelectorAll('.fui-member');
    rows.forEach((row, index) => row.querySelector('.fui-member-number').textContent = index + 1);
    count.textContent = rows.length;
    document.getElementById('fui-no-members').hidden = rows.length > 0;
    document.getElementById('kk-add').disabled = rows.length >= 100;
  };
  const toggleEditor = (row, open) => {
    row.querySelector('.fui-member-editor').hidden = !open;
    row.querySelector('.fui-edit-member').setAttribute('aria-expanded', String(open));
  };
  const updateMember = row => {
    row.querySelectorAll('[data-summary]').forEach(cell => {
      const field = row.querySelector(`[name$="[${cell.dataset.summary}]"]`);
      cell.textContent = field?.value || (cell.dataset.summary === 'nama_lengkap' ? 'Anggota baru' : '—');
      if (cell.classList.contains('fui-gender')) cell.classList.toggle('is-pink', field?.value === 'P');
    });
    const required = [...row.querySelectorAll('[required]')];
    const complete = required.every(input => input.validity.valid);
    const status = row.querySelector('[data-member-status]');
    status.classList.toggle('is-pending', !complete);
    status.lastElementChild.textContent = complete ? 'Lengkap' : 'Periksa';
  };
  document.getElementById('kk-add').addEventListener('click', () => {
    if (members.querySelectorAll('.fui-member').length >= 100) return;
    const fragment = document.getElementById('kk-member-template').content.cloneNode(true);
    const row = fragment.querySelector('.fui-member');
    row.dataset.index = next;
    row.querySelectorAll('[data-field]').forEach(input => {
      input.name = `anggota[${next}][${input.dataset.field}]`;
      input.removeAttribute('data-field');
    });
    next++;
    members.appendChild(fragment);
    updateCount();
    row.querySelector('input').focus();
  });
  members.addEventListener('click', event => {
    const row = event.target.closest('.fui-member');
    if (!row) return;
    if (event.target.closest('.kk-remove')) {
      row.remove(); updateCount();
    } else if (event.target.closest('.fui-edit-member')) {
      const open = row.querySelector('.fui-member-editor').hidden;
      toggleEditor(row, open);
      if (open) row.querySelector('input,select').focus();
    } else if (event.target.closest('.fui-finish-member')) {
      const invalid = checkControls([...row.querySelectorAll('input,select')].filter(el => el.name), false);
      if (invalid.length) { focusField(invalid[0]); return; }
      updateMember(row); toggleEditor(row, false);
    }
  });
  members.addEventListener('input', event => {
    const row = event.target.closest('.fui-member');
    if (row) updateMember(row);
  });

  /* ---------- Validasi: tandai merah & arahkan kursor ke kolom yang kosong ---------- */
  const formError = document.getElementById('kk-form-error');
  let errorSeq = 0;
  const messageFor = el => {
    const v = el.validity, name = el.dataset.label || 'Data ini';
    if (v.valueMissing) {
      if (el.type === 'checkbox') return 'Centang konfirmasi ini sebelum mengajukan verifikasi.';
      return el.tagName === 'SELECT' ? `${name} wajib dipilih.` : `${name} wajib diisi.`;
    }
    if (v.patternMismatch || v.tooShort) return el.dataset.patternMessage || `${name} tidak sesuai format.`;
    return el.validationMessage || `${name} belum benar.`;
  };
  const setError = (el, message) => {
    const isCheck = el.type === 'checkbox';
    (isCheck ? el.closest('.fui-consent') : el.closest('.fui-input-wrap'))?.classList.toggle('is-invalid', !!message);
    el.setAttribute('aria-invalid', message ? 'true' : 'false');
    const slot = isCheck ? review.querySelector('.fui-consent-error') : el.closest('.fui-field')?.querySelector('.fui-field-error');
    if (slot) {
      slot.textContent = message || '';
      slot.hidden = !message;
      if (!slot.id) slot.id = `kk-error-${++errorSeq}`;
      if (message) el.setAttribute('aria-describedby', slot.id); else el.removeAttribute('aria-describedby');
    }
    const row = el.closest('.fui-member');
    if (row) row.classList.toggle('has-error', !!row.querySelector('[aria-invalid="true"]'));
  };
  // Rules the browser cannot express: exactly one Kepala Keluarga, unique NIK.
  // requireHead=false while editing one member: the head may simply not be added yet.
  const applyCustomRules = (requireHead = true) => {
    const relations = [...members.querySelectorAll('[data-relationship]')];
    const niks = [...members.querySelectorAll('[name$="[nik]"]')];
    [...relations, ...niks].forEach(el => el.setCustomValidity(''));
    const heads = relations.filter(el => el.value === 'Kepala Keluarga');
    if (requireHead && relations.length && !heads.length && relations.every(el => el.value)) {
      relations[0].setCustomValidity('Harus ada satu anggota dengan hubungan "Kepala Keluarga".');
    }
    heads.slice(1).forEach(el => el.setCustomValidity('Kepala Keluarga hanya boleh satu orang.'));
    const seen = new Set();
    niks.forEach(el => {
      if (!el.value) return;
      if (seen.has(el.value)) el.setCustomValidity('NIK ini sudah dipakai anggota lain.');
      seen.add(el.value);
    });
  };
  const checkControls = (controls, requireHead = true) => {
    applyCustomRules(requireHead);
    const invalid = [];
    controls.forEach(el => {
      if (!el.willValidate) return;
      const ok = el.validity.valid;
      setError(el, ok ? '' : messageFor(el));
      if (!ok) invalid.push(el);
    });
    return invalid;
  };
  const focusField = el => {
    const row = el.closest('.fui-member');
    if (row) toggleEditor(row, true);
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => el.focus({ preventScroll: true }), 400);
  };
  // Clear the red mark as soon as the field becomes valid.
  const recheck = event => {
    const el = event.target;
    if (!el.matches?.('input,select') || !el.name) return;
    if (el.matches('[data-relationship],[name$="[nik]"]')) {
      // Changing one relationship/NIK can fix (or break) the rule on another member.
      applyCustomRules(false);
      members.querySelectorAll('[data-relationship],[name$="[nik]"]').forEach(other => {
        if (other.getAttribute('aria-invalid') === 'true' || other.validity.customError) setError(other, other.validity.valid ? '' : messageFor(other));
      });
    } else if (el.getAttribute('aria-invalid') === 'true') {
      setError(el, el.validity.valid ? '' : messageFor(el));
    }
    if (formError && !formError.hidden && !review.querySelector('[aria-invalid="true"]')) formError.hidden = true;
  };
  review.addEventListener('input', recheck);
  review.addEventListener('change', recheck);
  review.noValidate = true;
  review.addEventListener('submit', event => {
    if (event.submitter?.value === 'draft') { submitWithFreshToken(review, event); return; }
    if (!members.querySelector('.fui-member')) {
      event.preventDefault();
      formError.textContent = 'Tambahkan minimal satu anggota keluarga sebelum mengajukan verifikasi.';
      formError.hidden = false;
      document.getElementById('kk-add').click();
      return;
    }
    const invalid = checkControls([...review.querySelectorAll('input,select')].filter(el => el.name));
    if (!invalid.length) { formError.hidden = true; submitWithFreshToken(review, event); return; }
    event.preventDefault();
    invalid.forEach(el => { const row = el.closest('.fui-member'); if (row) { toggleEditor(row, true); updateMember(row); } });
    formError.textContent = `Masih ada ${invalid.length} kolom yang kosong atau belum benar (ditandai merah). Kursor sudah diarahkan ke kolom pertama.`;
    formError.hidden = false;
    focusField(invalid[0]);
  });
  const showSelection = () => {
    const file = documentInput.files?.[0];
    const valid = file && file.size <= 10 * 1024 * 1024;
    document.getElementById('fui-selected-file').hidden = !file;
    document.getElementById('fui-read-file').hidden = !valid;
    document.getElementById('fui-file-name').textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB` : '';
    progress.textContent = file && !valid ? 'Ukuran file melebihi 10 MB. Pilih file yang lebih kecil.' : '';
  };
  document.getElementById('kk-camera').addEventListener('click', () => camera.click());
  camera.addEventListener('change', () => {
    if (camera.files.length) { documentInput.files = camera.files; showSelection(); }
  });
  documentInput.addEventListener('change', showSelection);
  document.getElementById('fui-clear-file').addEventListener('click', () => {
    documentInput.value = ''; camera.value = ''; showSelection();
  });
  const dropzone = document.getElementById('fui-dropzone');
  ['dragenter','dragover'].forEach(name => dropzone.addEventListener(name, event => {
    event.preventDefault(); dropzone.classList.add('is-dragging');
  }));
  ['dragleave','drop'].forEach(name => dropzone.addEventListener(name, event => {
    event.preventDefault(); dropzone.classList.remove('is-dragging');
  }));
  dropzone.addEventListener('drop', event => {
    if (event.dataTransfer.files.length) {
      const files = new DataTransfer(); files.items.add(event.dataTransfer.files[0]);
      documentInput.files = files.files; showSelection();
    }
  });
  upload.addEventListener('submit', event => {
    const file = documentInput.files[0];
    if (!file || file.size > 10 * 1024 * 1024) { event.preventDefault(); showSelection(); return; }
    if (submitWithFreshToken(upload, event)) return;
    document.getElementById('fui-read-file').disabled = true;
    progress.textContent = 'Sedang membaca data KK. Mohon tunggu hingga proses selesai…';
    page.querySelectorAll('.fui-steps li.is-current').forEach(step => step.classList.remove('is-current'));
    page.querySelectorAll('.fui-steps li:not(.fui-step-arrow)')[1].classList.add('is-current');
    showLoading();
  });
  // Waiting overlay while the server runs OCR (the request is a normal form post).
  const loading = document.getElementById('kk-loading');
  let loadingTimer;
  const showLoading = () => {
    if (!loading) return;
    const steps = [...loading.querySelectorAll('#kk-loading-steps li')];
    const text = document.getElementById('kk-loading-text');
    const bar = document.getElementById('kk-loading-bar');
    const time = document.getElementById('kk-loading-time');
    // [seconds, step index, message]; the bar eases toward 95% and finishes when the page reloads.
    const plan = [[0, 0, 'Mengunggah file Kartu Keluarga'], [3, 1, 'Menyiapkan dan merapikan gambar KK'], [7, 2, 'AI sedang membaca teks pada KK'], [20, 3, 'Menyusun data keluarga dan anggota'], [45, 3, 'Hampir selesai, mohon tunggu sebentar lagi']];
    const started = Date.now();
    loading.hidden = false;
    document.body.classList.add('fui-no-scroll');
    loading.querySelector('.fui-loading-card').focus?.();
    const tick = () => {
      const s = Math.floor((Date.now() - started) / 1000);
      const current = plan.filter(p => s >= p[0]).pop();
      steps.forEach((li, i) => {
        li.classList.toggle('is-done', i < current[1]);
        li.classList.toggle('is-active', i === current[1]);
      });
      if (text.textContent !== current[2]) text.textContent = current[2];
      bar.style.width = `${Math.min(95, 100 * (1 - Math.exp(-s / 25)))}%`;
      time.textContent = s < 60 ? `${s} detik` : `${Math.floor(s / 60)} menit ${s % 60} detik`;
    };
    tick();
    clearInterval(loadingTimer);
    loadingTimer = setInterval(tick, 1000);
  };
  // Returning via the back button (bfcache) must not leave the overlay stuck.
  window.addEventListener('pageshow', event => {
    if (!event.persisted || !loading || loading.hidden) return;
    loading.hidden = true; clearInterval(loadingTimer);
    document.body.classList.remove('fui-no-scroll');
    document.getElementById('fui-read-file').disabled = false;
    progress.textContent = '';
  });
  document.getElementById('fui-rescan').addEventListener('click', () => {
    document.getElementById('fui-upload-panel').hidden = false;
    document.getElementById('fui-upload-panel').scrollIntoView({behavior:'smooth',block:'start'});
  });
  updateCount();
})();
