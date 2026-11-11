(() => {
  const dialog = document.getElementById('aal');
  if (!dialog || !dialog.showModal) return;
  const POLL_MS = 30000;
  const SNOOZE_MS = 15 * 60 * 1000;
  const key = `sukamulya.alerts.${dialog.dataset.admin}`;
  const page = dialog.dataset.page || '';
  const baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
  let data = JSON.parse(dialog.dataset.initial || '{}');
  let audio;

  const store = {
    get() { try { return JSON.parse(localStorage.getItem(key) || '{}'); } catch { return {}; } },
    set(value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* private mode */ } },
  };
  // Snoozing stores the current signature; a newer submission changes it and pops up again.
  const acknowledge = (ms = SNOOZE_MS) => store.set({ sig: data.signature, until: Date.now() + ms });

  const render = (fresh = {}) => {
    ['kk', 'letters'].forEach(type => {
      const box = dialog.querySelector(`[data-aal="${type}"]`);
      const info = data[type] || { count: 0, items: [] };
      box.hidden = !info.count;
      box.querySelector('[data-aal-count]').textContent = info.count;
      box.querySelector('[data-aal-new]').hidden = !fresh[type];
      box.querySelector('[data-aal-go]').href = info.url || '#';
      const list = box.querySelector('[data-aal-list]');
      list.replaceChildren(...(info.items || []).map(item => {
        const li = document.createElement('li');
        const a = document.createElement('a'); a.href = item.url;
        const t = document.createElement('strong'); t.textContent = item.title;
        const m = document.createElement('small'); m.textContent = item.meta;
        const time = document.createElement('time'); time.textContent = item.time;
        a.append(t, m, time); li.append(a); return li;
      }));
      if (info.count > (info.items || []).length) {
        const more = document.createElement('li'); more.className = 'aal-more';
        more.textContent = `+${info.count - info.items.length} pengajuan lainnya`;
        list.append(more);
      }
    });
    const parts = [];
    if (data.kk?.count) parts.push(`${data.kk.count} verifikasi KK`);
    if (data.letters?.count) parts.push(`${data.letters.count} pengajuan surat`);
    document.getElementById('aal-title').textContent = fresh.kk || fresh.letters ? 'Ada Pengajuan Baru Masuk!' : 'Pengajuan Menunggu Verifikasi';
    document.getElementById('aal-sub').textContent = `${parts.join(' dan ')} menunggu tindakan Anda.`;
    dialog.querySelector('.aal-grid').classList.toggle('is-single', parts.length === 1);
    document.title = data.total ? `(${data.total}) ${baseTitle}` : baseTitle;
  };

  const chime = () => {
    // Only audible after the admin has interacted with the page (browser autoplay rules).
    try {
      audio = audio || new (window.AudioContext || window.webkitAudioContext)();
      [0, 0.18].forEach((offset, i) => {
        const osc = audio.createOscillator(); const gain = audio.createGain();
        osc.frequency.value = i ? 1046 : 784; osc.type = 'sine';
        gain.gain.setValueAtTime(0.0001, audio.currentTime + offset);
        gain.gain.exponentialRampToValueAtTime(0.18, audio.currentTime + offset + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + offset + 0.35);
        osc.connect(gain).connect(audio.destination);
        osc.start(audio.currentTime + offset); osc.stop(audio.currentTime + offset + 0.4);
      });
    } catch { /* audio unavailable */ }
  };

  const show = (fresh, sound) => {
    render(fresh);
    if (!data.total) { if (dialog.open) dialog.close(); return; }
    if (!dialog.open) dialog.showModal();
    if (sound) chime();
  };
  const shouldShow = () => {
    if (!data.total) return false;
    const seen = store.get();
    return seen.sig !== data.signature || Date.now() > (seen.until || 0);
  };

  dialog.querySelectorAll('[data-aal-snooze]').forEach(b => b.addEventListener('click', () => { acknowledge(); dialog.close(); }));
  dialog.addEventListener('cancel', () => acknowledge()); // Esc
  dialog.addEventListener('click', event => {
    if (event.target === dialog) { acknowledge(); dialog.close(); return; }
    // Following a link counts as handling the alert for now.
    if (event.target.closest('a')) acknowledge();
  });

  // First paint: on the review pages themselves the list is already visible, so just record it.
  const onReviewPage = /^admin\/(kk\/pengajuan|requests|request_detail)(\/|$)/.test(page);
  render();
  if (onReviewPage) { if (store.get().sig !== data.signature) acknowledge(); }
  else if (shouldShow()) show({}, false);

  let timer;
  const poll = async () => {
    try {
      const response = await fetch(dialog.dataset.endpoint, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
      if (response.redirected || !response.ok) return; // logged out or no access
      const next = await response.json();
      if (typeof next.total !== 'number') return;
      const fresh = {
        kk: (next.kk?.latest_id || 0) > (data.kk?.latest_id || 0),
        letters: (next.letters?.latest_id || 0) > (data.letters?.latest_id || 0),
      };
      data = next;
      if (fresh.kk || fresh.letters) show(fresh, true);
      else if (dialog.open) show({}, false); // keep the open popup current
      else render();
    } catch { /* offline: try again next tick */ }
  };
  const schedule = () => { clearInterval(timer); timer = setInterval(() => { if (!document.hidden) poll(); }, POLL_MS); };
  document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
  schedule();
})();
