/* Widget chat melayang admin — multi-chat (lihat application/views/admin/chat_widget.php). */
(function () {
  'use strict';
  var root = document.getElementById('acw');
  if (!root || !window.fetch) return;

  var $ = function (id) { return document.getElementById(id); };
  var inbox = $('acw-inbox'), launch = $('acw-launch'), dock = $('acw-dock'), list = $('acw-list');
  var csrfName = root.dataset.csrfName, csrf = root.dataset.csrf;
  var store = 'sukamulya.adminchat.' + root.dataset.admin;
  var convs = {};          // id -> data percakapan dari inbox
  var wins = {};           // id -> jendela terbuka
  var filter = 'all', firstLoad = true, seenUnread = {};
  var XHR = { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' };

  function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }
  function hhmm(ts) { return ts ? String(ts).slice(11, 16) : ''; }
  function shortTime(ts) {
    if (!ts) return '';
    var d = new Date(String(ts).replace(' ', 'T')), now = new Date();
    if (isNaN(d)) return hhmm(ts);
    return d.toDateString() === now.toDateString() ? hhmm(ts) : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
  }
  function maxWins() { var w = window.innerWidth; return w < 700 ? 1 : Math.max(1, Math.min(3, Math.floor((w - 420) / 340))); }
  function save() { try { sessionStorage.setItem(store, JSON.stringify(Object.keys(wins).map(function (id) { return { id: +id, min: wins[id].min }; }))); } catch (e) {} }
  function restore() { try { return JSON.parse(sessionStorage.getItem(store) || '[]'); } catch (e) { return []; } }

  /* ---------- Kotak masuk ---------- */
  function setInbox(open) {
    inbox.hidden = !open; launch.setAttribute('aria-expanded', open ? 'true' : 'false'); root.classList.toggle('inbox-open', open);
    if (open) { var q = $('acw-q'); if (q && window.innerWidth > 700) q.focus(); }
  }
  launch.addEventListener('click', function () { setInbox(inbox.hidden); });
  root.querySelector('[data-acw-close-inbox]').addEventListener('click', function () { setInbox(false); });
  $('acw-q').addEventListener('input', renderList);
  root.querySelectorAll('.acw-filter button').forEach(function (b) {
    b.addEventListener('click', function () { filter = b.dataset.f; root.querySelectorAll('.acw-filter button').forEach(function (x) { x.classList.toggle('on', x === b); }); renderList(); });
  });

  function renderList() {
    var q = $('acw-q').value.trim().toLowerCase(), items = Object.keys(convs).map(function (k) { return convs[k]; });
    items.sort(function (a, b) { return (b.time || '').localeCompare(a.time || '') || a.name.localeCompare(b.name); });
    $('acw-n-all').textContent = items.length;
    $('acw-n-online').textContent = items.filter(function (c) { return c.online; }).length;
    $('acw-n-unread').textContent = items.filter(function (c) { return c.unread; }).length;
    items = items.filter(function (c) {
      if (q && c.name.toLowerCase().indexOf(q) < 0) return false;
      return filter === 'online' ? c.online : filter === 'unread' ? c.unread > 0 : true;
    });
    list.textContent = '';
    if (!items.length) { list.appendChild(el('p', 'acw-empty', q || filter !== 'all' ? 'Tidak ada percakapan yang cocok.' : 'Belum ada warga yang memulai chat.')); return; }
    items.forEach(function (c) {
      var row = el('button', 'acw-row' + (c.unread ? ' has-unread' : '') + (wins[c.id] ? ' is-open' : ''));
      row.type = 'button';
      var av = el('span', 'acw-av' + (c.online ? ' on' : '')); var img = el('img'); img.src = c.avatar; img.alt = ''; av.appendChild(img);
      var tx = el('span', 'acw-row-tx'); tx.appendChild(el('strong', null, c.name)); tx.appendChild(el('small', null, c.last || 'Belum ada pesan'));
      var meta = el('span', 'acw-row-meta'); meta.appendChild(el('time', null, shortTime(c.time))); if (c.unread) meta.appendChild(el('b', null, c.unread > 99 ? '99+' : c.unread));
      row.append(av, tx, meta);
      row.addEventListener('click', function () { openWin(c.id, false, true); if (window.innerWidth < 700) setInbox(false); });
      list.appendChild(row);
    });
  }

  function pollInbox() {
    return fetch(root.dataset.inbox, XHR).then(function (r) { if (!r.ok) throw r; return r.json(); }).then(function (j) {
      csrf = j.csrf || csrf;
      var fresh = {};
      j.conversations.forEach(function (c) {
        fresh[c.id] = c;
        // Pesan baru dari warga yang jendelanya tidak sedang terbuka: beri tahu admin.
        if (!firstLoad && c.unread > (seenUnread[c.id] || 0) && (!wins[c.id] || wins[c.id].min)) toast(c);
        seenUnread[c.id] = c.unread;
      });
      convs = fresh;
      var unread = j.conversations.reduce(function (n, c) { return n + (wins[c.id] && !wins[c.id].min ? 0 : c.unread); }, 0);
      $('acw-badge').hidden = !unread; $('acw-badge').textContent = unread > 99 ? '99+' : unread;
      launch.classList.toggle('has-unread', unread > 0);
      $('acw-online').textContent = j.online + ' warga online';
      $('acw-dot').classList.toggle('on', j.online > 0);
      $('acw-sum').textContent = j.conversations.length + ' percakapan · ' + j.unread + ' belum dibaca';
      Object.keys(wins).forEach(function (id) { if (convs[id]) paintHead(wins[id], convs[id]); });
      renderList();
      if (firstLoad) { firstLoad = false; boot(); }
    }).catch(function () { $('acw-online').textContent = 'Tidak terhubung'; $('acw-dot').classList.remove('on'); });
  }

  function toast(c) {
    var t = $('acw-toast'); t.textContent = ''; t.hidden = false;
    var img = el('img'); img.src = c.avatar; img.alt = '';
    var tx = el('span'); tx.appendChild(el('strong', null, 'Pesan baru dari ' + c.name)); tx.appendChild(el('small', null, c.last || ''));
    t.append(img, tx);
    t.onclick = function () { t.hidden = true; openWin(c.id, false, true); };
    clearTimeout(toast.timer); toast.timer = setTimeout(function () { t.hidden = true; }, 6000);
  }

  /* ---------- Jendela percakapan ---------- */
  function openWin(id, minimized, focus) {
    var c = convs[id]; if (!c) return;
    if (wins[id]) { setMin(wins[id], false); if (focus) wins[id].input.focus(); return; }
    var ids = Object.keys(wins); if (ids.length >= maxWins()) closeWin(ids[0]); // jendela tertua ditutup bila ruang penuh
    var w = { id: id, last: 0, busy: false, min: !!minimized };
    w.box = el('section', 'acw-win'); w.box.setAttribute('role', 'dialog'); w.box.setAttribute('aria-label', 'Chat dengan ' + c.name);
    var head = el('header', 'acw-win-head'); w.head = head;
    w.av = el('span', 'acw-av'); w.img = el('img'); w.img.alt = ''; w.av.appendChild(w.img);
    var tx = el('span', 'acw-win-tx'); w.name = el('strong'); w.status = el('small'); tx.append(w.name, w.status);
    w.badge = el('b', 'acw-win-badge'); w.badge.hidden = true;
    var minB = el('button', 'acw-icon-btn'); minB.type = 'button'; minB.setAttribute('aria-label', 'Perkecil'); minB.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 12h12"/></svg>';
    var xB = el('button', 'acw-icon-btn'); xB.type = 'button'; xB.setAttribute('aria-label', 'Tutup chat'); xB.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
    head.append(w.av, tx, w.badge, minB, xB);
    w.msgs = el('div', 'acw-msgs'); w.msgs.appendChild(el('p', 'acw-empty', 'Memuat pesan...'));
    w.form = el('form', 'acw-form');
    w.input = el('textarea'); w.input.rows = 1; w.input.maxLength = 2000; w.input.required = true; w.input.placeholder = 'Tulis balasan untuk ' + c.name + '...';
    var send = el('button', 'acw-send'); send.type = 'submit'; send.setAttribute('aria-label', 'Kirim');
    send.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.4 21 12 3.4 3.6 3.4 10l12.6 2-12.6 2z"/></svg>';
    w.form.append(w.input, send);
    w.box.append(head, w.msgs, w.form);
    head.addEventListener('click', function (e) { if (e.target.closest('button')) return; setMin(w, !w.min); });
    minB.addEventListener('click', function () { setMin(w, true); });
    xB.addEventListener('click', function () { closeWin(id); });
    w.form.addEventListener('submit', function (e) { e.preventDefault(); sendMsg(w); });
    w.input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(w); } });
    w.input.addEventListener('input', function () { w.input.style.height = 'auto'; w.input.style.height = Math.min(110, w.input.scrollHeight) + 'px'; });
    wins[id] = w; dock.appendChild(w.box);
    paintHead(w, c); setMin(w, w.min); pollWin(w); save(); renderList();
    w.timer = setInterval(function () { pollWin(w); }, 4000);
    if (focus && !w.min) w.input.focus();
  }

  function paintHead(w, c) {
    w.img.src = c.avatar; w.name.textContent = c.name;
    w.status.textContent = c.online ? 'Online' : 'Offline'; w.av.classList.toggle('on', !!c.online);
    w.badge.hidden = !(w.min && c.unread); w.badge.textContent = c.unread;
  }
  function setMin(w, min) { w.min = min; w.box.classList.toggle('is-min', min); if (!min) { w.badge.hidden = true; pollWin(w); setTimeout(function () { w.msgs.scrollTop = w.msgs.scrollHeight; }, 0); } save(); }
  function closeWin(id) { var w = wins[id]; if (!w) return; clearInterval(w.timer); w.box.remove(); delete wins[id]; save(); renderList(); }

  function addMsg(w, m, readUpTo) {
    var ex = w.msgs.querySelector('[data-id="' + m.id + '"]');
    var mine = m.sender_type === 'admin';
    if (ex) { if (mine) ex.querySelector('time').textContent = hhmm(m.created_at) + (m.id <= readUpTo ? ' ✓✓' : ' ✓'); return; }
    var empty = w.msgs.querySelector('.acw-empty'); if (empty) empty.remove();
    var b = el('div', 'acw-bubble ' + (mine ? 'mine' : 'theirs')); b.dataset.id = m.id;
    b.appendChild(el('p', null, m.message));
    b.appendChild(el('time', null, hhmm(m.created_at) + (mine ? (m.id <= readUpTo ? ' ✓✓' : ' ✓') : '')));
    w.msgs.appendChild(b); w.last = Math.max(w.last, +m.id);
  }
  function pollWin(w) {
    if (w.busy || w.min) return; // jendela diperkecil tidak menandai pesan sebagai dibaca
    var c = convs[w.id]; if (!c) return;
    var nearBottom = w.msgs.scrollHeight - w.msgs.scrollTop - w.msgs.clientHeight < 60;
    fetch(c.poll + '?after=' + w.last, XHR).then(function (r) { if (!r.ok) throw r; return r.json(); }).then(function (j) {
      csrf = j.csrf || csrf;
      var ru = +j.read_up_to || 0, had = w.last;
      j.messages.forEach(function (m) { addMsg(w, m, ru); });
      w.msgs.querySelectorAll('.acw-bubble.mine').forEach(function (b) { b.querySelector('time').textContent = b.querySelector('time').textContent.replace(/ ✓✓?$/, '') + (+b.dataset.id <= ru ? ' ✓✓' : ' ✓'); });
      if (!w.last && !w.msgs.children.length) w.msgs.appendChild(el('p', 'acw-empty', 'Belum ada pesan. Mulai percakapan dengan warga.'));
      if (w.last > had && (nearBottom || !had)) w.msgs.scrollTop = w.msgs.scrollHeight;
      if (convs[w.id]) { convs[w.id].unread = 0; seenUnread[w.id] = 0; }
    }).catch(function () { w.status.textContent = 'Tidak terhubung'; });
  }
  function sendMsg(w) {
    var text = w.input.value.trim(), c = convs[w.id]; if (!text || !c || w.busy) return;
    w.busy = true; var fd = new FormData(); fd.append(csrfName, csrf); fd.append('message', text);
    fetch(c.send, { method: 'POST', body: fd, headers: XHR.headers, credentials: 'same-origin' }).then(function (r) { if (!r.ok) throw r; return r.json(); }).then(function (j) {
      csrf = j.csrf || csrf; w.input.value = ''; w.input.style.height = '';
    }).catch(function () { alert('Pesan belum berhasil dikirim. Coba lagi.'); })
      .then(function () { w.busy = false; pollWin(w); w.msgs.scrollTop = w.msgs.scrollHeight; w.input.focus(); });
  }

  /* ---------- Mulai ---------- */
  function boot() {
    restore().forEach(function (s) { if (convs[s.id]) openWin(s.id, s.min, false); });
    var m = location.search.match(/[?&]chat=(\d+)/);
    if (m && convs[m[1]]) openWin(+m[1], false, true);
    else if (location.hash === '#chat') setInbox(true);
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !inbox.hidden) setInbox(false); });
  window.addEventListener('resize', function () { var ids = Object.keys(wins); while (ids.length > maxWins()) closeWin(ids.shift()); });
  pollInbox(); setInterval(pollInbox, 5000);
})();
