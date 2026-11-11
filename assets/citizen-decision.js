(() => {
  const dialog = document.getElementById('cdp');
  if (!dialog || !dialog.showModal) return;
  const POLL_MS = 30000;
  const statusLabel = { approved: 'Disetujui', rejected: 'Ditolak', revision_requested: 'Perlu Perbaikan', disetujui: 'Disetujui', selesai: 'Selesai', ditolak: 'Ditolak' };
  const kicker = { approved: 'Selamat! Kabar baik dari Admin RT', rejected: 'Kabar dari Admin RT', revision: 'Tindakan diperlukan' };
  const key = it => `${it.kind}:${it.id}:${it.status}`;
  const $ = id => document.getElementById(id);
  let queue = [];
  let index = 0;
  const handled = new Set(); // acknowledged in this page view

  const ack = items => {
    const fresh = items.filter(it => !handled.has(key(it)));
    if (!fresh.length) return;
    fresh.forEach(it => handled.add(key(it)));
    // keepalive lets the request finish even when the click navigates away.
    fetch(dialog.dataset.ack, { method: 'POST', credentials: 'same-origin', keepalive: true,
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ items: fresh.map(({ kind, id, status }) => ({ kind, id, status })) }) }).catch(() => {});
  };

  const render = () => {
    const it = queue[index];
    if (!it) return;
    dialog.dataset.tone = it.tone;
    $('cdp-kicker').textContent = kicker[it.tone] || 'Kabar dari Admin RT';
    $('cdp-title').textContent = it.title;
    $('cdp-message').textContent = it.message;
    $('cdp-type').textContent = it.type;
    $('cdp-code').textContent = it.code;
    $('cdp-time').textContent = it.time;
    $('cdp-status').textContent = statusLabel[it.status] || it.status;
    $('cdp-note-box').hidden = !it.note;
    $('cdp-note').textContent = it.note || '';
    $('cdp-go').href = it.url;
    $('cdp-go-label').textContent = it.action;
    $('cdp-pager').hidden = queue.length < 2;
    $('cdp-pos').textContent = index + 1;
    $('cdp-total').textContent = queue.length;
    $('cdp-next').hidden = index >= queue.length - 1;
    // Replay the entrance + confetti for each item.
    dialog.classList.remove('is-play'); void dialog.offsetWidth; dialog.classList.add('is-play');
  };

  const open = () => {
    if (!queue.length) return;
    render();
    if (!dialog.open) dialog.showModal();
    $('cdp-go').focus();
  };
  const shownSoFar = () => queue.slice(0, index + 1);
  const close = () => { ack(shownSoFar()); dialog.close(); queue = queue.slice(index + 1); index = 0; };

  dialog.querySelectorAll('[data-cdp-close]').forEach(b => b.addEventListener('click', close));
  dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
  dialog.addEventListener('click', event => {
    if (event.target !== dialog) return;
    const r = dialog.getBoundingClientRect();
    if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) close();
  });
  $('cdp-next').addEventListener('click', () => { ack([queue[index]]); index = Math.min(index + 1, queue.length - 1); render(); });
  $('cdp-go').addEventListener('click', () => ack(shownSoFar()));

  const merge = items => {
    const known = new Set(queue.map(key));
    const added = (items || []).filter(it => !known.has(key(it)) && !handled.has(key(it)));
    queue = queue.concat(added);
    return added.length;
  };

  merge(JSON.parse(dialog.dataset.initial || '[]'));
  open();

  // Live: a decision made while the resident is browsing pops up within ~30 s.
  const poll = async () => {
    try {
      const response = await fetch(dialog.dataset.feed, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
      if (!response.ok) return;
      const data = await response.json();
      if (merge(data.items) && !dialog.open) open();
      else if (dialog.open) render(); // update "x dari y"
    } catch { /* offline: next tick */ }
  };
  setInterval(() => { if (!document.hidden) poll(); }, POLL_MS);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
