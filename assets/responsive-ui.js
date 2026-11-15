/*
 * responsive-ui.js — perilaku responsif global (pasangan assets/responsive-ui.css).
 * Tidak mengubah data/proses: hanya menambah kelas, label kartu, tombol lipat, dan navigasi tab di sisi klien.
 * Penanda opsional di view:
 *   data-rsp-keep           tabel/elemen dibiarkan apa adanya
 *   data-rsp-tabs           wadah form; anak langsung [data-rsp-tab="Label"] menjadi tab di mobile
 *   data-rsp-noguard        form tidak diberi peringatan "perubahan belum disimpan"
 */
(function () {
  'use strict';
  var mq = window.matchMedia('(max-width:767px)');
  var body = document.body;
  var isAdmin = body.classList.contains('admin-body');
  var main = document.querySelector('.ad-main') || document.querySelector('.portal-body main') || document.querySelector('main') || body;
  var text = function (el) { return (el.textContent || '').replace(/\s+/g, ' ').trim(); };

  /* ---------- 1. Drawer sidebar admin: tombol tutup, Escape, tombol kembali ---------- */
  if (isAdmin) {
    var sidebar = document.querySelector('.ad-sidebar');
    var drawerSmall = function () { return window.innerWidth <= 950; };
    if (sidebar && !sidebar.querySelector('.rsp-drawer-close')) {
      var close = document.createElement('button');
      close.type = 'button'; close.className = 'rsp-drawer-close'; close.setAttribute('aria-label', 'Tutup menu'); close.innerHTML = '&times;';
      close.hidden = true;
      close.addEventListener('click', function () { closeDrawer(); });
      sidebar.appendChild(close);
    }
    var pushed = false;
    function closeDrawer() {
      if (!body.classList.contains('ad-open')) return;
      body.classList.remove('ad-open');
      var b = document.getElementById('ad-hamburger'); if (b) { b.setAttribute('aria-expanded', 'false'); b.focus(); }
    }
    new MutationObserver(function () {
      var open = body.classList.contains('ad-open') && drawerSmall();
      var c = sidebar && sidebar.querySelector('.rsp-drawer-close'); if (c) c.hidden = !open;
      if (open && !pushed) { try { history.pushState({ rspDrawer: 1 }, ''); pushed = true; } catch (e) {} if (c) c.focus(); }
      if (!open && pushed) { pushed = false; if (history.state && history.state.rspDrawer) { try { history.back(); } catch (e) {} } }
    }).observe(body, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('popstate', function () { if (body.classList.contains('ad-open')) { pushed = false; body.classList.remove('ad-open'); } });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && body.classList.contains('ad-open')) closeDrawer(); });
    // Saat jendela diperlebar melewati batas drawer, pastikan overlay tidak tertinggal.
    window.addEventListener('resize', function () { if (!drawerSmall() && body.classList.contains('ad-open')) body.classList.remove('ad-open'); });
  }

  /* ---------- 2. Tabel: label kartu otomatis atau scroll lokal ---------- */
  function headLabels(table) {
    var row = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
    if (!row) return null;
    var out = [];
    Array.prototype.forEach.call(row.cells, function (th) { var span = th.colSpan || 1; for (var i = 0; i < span; i++) out.push(text(th)); });
    return out;
  }
  function scrollable(el) {
    for (var p = el.parentElement; p && p !== main && p !== body; p = p.parentElement) {
      if (/(auto|scroll)/.test(getComputedStyle(p).overflowX)) return true;
    }
    return false;
  }
  function isActionCell(td, label) {
    if (/^(aksi|action|opsi|#)?$/i.test(label || '')) {
      if (td.querySelector('a,button,form,details')) return true;
    }
    var clone = td.cloneNode(true);
    Array.prototype.forEach.call(clone.querySelectorAll('a,button,form,details,svg'), function (n) { n.remove(); });
    return text(clone) === '' && !!td.querySelector('a,button,form,details');
  }
  function prepTable(table) {
    if (table.dataset.rspDone) return; table.dataset.rspDone = '1';
    if (table.closest('[data-rsp-keep]') || table.classList.contains('pg-table') || table.classList.contains('ftx-table')) return;
    if (table.closest('.letter-paper,.letter-page,.print-area,.lt-template,.ads-letter,.cal-table,.ronda-grid')) return;
    var labels = headLabels(table);
    var rows = table.tBodies.length ? Array.prototype.slice.call(table.tBodies[0].rows) : [];
    var eligible = labels && labels.length >= 3 && rows.length > 0 && rows.every(function (r) {
      return r.cells.length === 1 || Array.prototype.every.call(r.cells, function (c) { return (c.colSpan || 1) === 1 && (c.rowSpan || 1) === 1; });
    });
    if (eligible) {
      table.classList.add('rsp-cards');
      var leadIdx = /^(no\.?|#|nomor urut)$/i.test(labels[0]) ? 1 : 0;
      rows.forEach(function (r) {
        if (r.cells.length === 1) return;
        Array.prototype.forEach.call(r.cells, function (td, i) {
          var lab = labels[i] || '';
          if (i < leadIdx) { td.classList.add('rsp-no'); return; }
          if (i === leadIdx) td.classList.add('rsp-lead');
          else if (isActionCell(td, lab)) td.classList.add('rsp-acts');
          if (!td.hasAttribute('data-label') && lab) td.setAttribute('data-label', lab);
        });
      });
      return;
    }
    if (!scrollable(table)) {
      var wrap = document.createElement('div'); wrap.className = 'rsp-scroll';
      table.parentNode.insertBefore(wrap, table); wrap.appendChild(table);
    }
  }
  Array.prototype.forEach.call(main.querySelectorAll('table'), prepTable);

  /* ---------- 3. Filter lipat di mobile: pencarian tetap tampil, filter lanjutan di tombol "Filter" ---------- */
  function prepFilter(form) {
    if (form.dataset.rspFilter) return;
    var search = form.querySelector('input[type=search],input[name=q],input[name=cari]');
    if (!search) return;
    var item = search.closest('label,.pg-search,.ftx-search,.anx-search') || search;
    if (item === form || !form.contains(item)) item = search;
    var row = item.parentElement; if (!row) return;
    var adv = Array.prototype.filter.call(row.children, function (c) {
      return c !== item && c.tagName !== 'NOSCRIPT' && !(c.tagName === 'INPUT' && c.type === 'hidden') && getComputedStyle(c).display !== 'none';
    });
    var controls = adv.filter(function (c) { return c.matches('select,input,label,div') || c.querySelector('select,input:not([type=hidden])'); });
    if (controls.length < 2) return;
    form.dataset.rspFilter = '1';
    form.classList.add('rsp-filter'); row.classList.add('rsp-filter-row'); item.classList.add('rsp-filter-search');
    adv.forEach(function (c) { c.classList.add('rsp-adv'); });
    var active = 0;
    Array.prototype.forEach.call(form.querySelectorAll('.rsp-adv select'), function (s) { if (s.selectedIndex > 0) active++; });
    Array.prototype.forEach.call(form.querySelectorAll('.rsp-adv input[type=date],.rsp-adv input[type=month],.rsp-adv input[type=text]'), function (i) { if (i.value) active++; });
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'rsp-filter-toggle'; btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>Filter' + (active ? '<b>' + active + '</b>' : '');
    btn.setAttribute('aria-label', 'Tampilkan filter lanjutan' + (active ? ' (' + active + ' aktif)' : ''));
    btn.addEventListener('click', function () { var o = form.classList.toggle('rsp-open'); btn.setAttribute('aria-expanded', o ? 'true' : 'false'); });
    item.insertAdjacentElement('afterend', btn);
    if (!mq.matches) btn.hidden = true;
  }
  var filterForms = main.querySelectorAll('form[role=search],form.pg-filter,form.ftx-filter,form[method=get]');
  Array.prototype.forEach.call(filterForms, function (f) { if (!f.closest('.ad-topbar')) prepFilter(f); });
  function syncMq() {
    Array.prototype.forEach.call(document.querySelectorAll('.rsp-filter-toggle'), function (b) { b.hidden = !mq.matches; });
    Array.prototype.forEach.call(document.querySelectorAll('[data-rsp-tabs]'), applyTabs);
  }

  /* ---------- 4. Tab form di mobile (data-rsp-tabs) — nilai input tetap karena panel hanya disembunyikan ---------- */
  function applyTabs(box) {
    var panels = Array.prototype.filter.call(box.children, function (c) { return c.hasAttribute('data-rsp-tab'); });
    if (panels.length < 2) return;
    var bar = box.querySelector(':scope>.rsp-tabbar');
    if (!bar) {
      bar = document.createElement('div'); bar.className = 'rsp-tabbar'; bar.setAttribute('role', 'tablist'); bar.setAttribute('aria-label', 'Bagian form');
      panels.forEach(function (p, i) {
        var id = p.id || ('rsp-tab-' + Math.random().toString(36).slice(2, 8)); p.id = id;
        var b = document.createElement('button'); b.type = 'button'; b.setAttribute('role', 'tab'); b.setAttribute('aria-controls', id);
        b.textContent = p.getAttribute('data-rsp-tab');
        if (p.querySelector('[required]')) { b.classList.add('rsp-has-req'); b.title = 'Berisi field wajib'; }
        b.addEventListener('click', function () { select(i, true); });
        bar.appendChild(b);
      });
      box.insertBefore(bar, box.firstChild);
      // Validasi: field wajib yang tersembunyi di tab lain — pindah ke tab tersebut.
      box.addEventListener('invalid', function (e) {
        if (!box.classList.contains('rsp-tabs-on')) return;
        var idx = panels.findIndex(function (p) { return p.contains(e.target); });
        if (idx >= 0 && panels[idx].hidden) select(idx, false);
      }, true);
    }
    function select(i, focus) {
      panels.forEach(function (p, k) { p.hidden = k !== i; bar.children[k].setAttribute('aria-selected', k === i ? 'true' : 'false'); bar.children[k].tabIndex = k === i ? 0 : -1; });
      box.dataset.rspTabIndex = i;
      if (focus) { bar.children[i].scrollIntoView({ block: 'nearest', inline: 'center' }); var top = box.getBoundingClientRect().top + window.pageYOffset - 70; if (window.pageYOffset > top) window.scrollTo(0, top); }
    }
    if (mq.matches) { box.classList.add('rsp-tabs-on'); select(+box.dataset.rspTabIndex || 0, false); }
    else { box.classList.remove('rsp-tabs-on'); panels.forEach(function (p) { p.hidden = false; }); }
  }
  Array.prototype.forEach.call(document.querySelectorAll('[data-rsp-tabs]'), applyTabs);
  if (mq.addEventListener) mq.addEventListener('change', syncMq); else if (mq.addListener) mq.addListener(syncMq);

  /* ---------- 5. Peringatan perubahan belum disimpan (form POST panjang) ---------- */
  var dirty = null;
  Array.prototype.forEach.call(main.querySelectorAll('form[method=post],form[method=POST]'), function (f) {
    if (f.hasAttribute('data-rsp-noguard') || f.closest('dialog')) return;
    var fields = f.querySelectorAll('input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=search]),select,textarea');
    if (fields.length < 5) return;
    f.addEventListener('input', function (e) { if (e.isTrusted) dirty = f; });
    f.addEventListener('change', function (e) { if (e.isTrusted) dirty = f; });
    f.addEventListener('submit', function () { dirty = null; });
  });
  window.addEventListener('beforeunload', function (e) { if (dirty && document.body.contains(dirty)) { e.preventDefault(); e.returnValue = ''; return ''; } });
})();
