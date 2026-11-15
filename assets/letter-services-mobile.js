/*
 * Layanan Surat versi HP: cari + dropdown kategori -> dropdown jenis surat -> kartu surat terpilih (Ajukan Pengajuan).
 * Data dari katalog yang sama dengan daftar desktop (script#lsm-data). Tautan tetap warga/ajukan?jenis=...
 */
(() => {
  const dataEl = document.getElementById('lsm-data');
  if (!dataEl) return;
  let items = [];
  try { items = JSON.parse(dataEl.textContent) || []; } catch (e) { return; }
  const $ = id => document.getElementById(id);
  const search = $('lsm-search'), cat = $('lsm-cat'), jenis = $('lsm-jenis'), count = $('lsm-count');
  const card = $('lsm-card'), icon = $('lsm-card-icon');
  const norm = s => (s || '').toLocaleLowerCase('id');

  const fillJenis = () => {
    const q = norm(search.value.trim()), c = cat.value, keep = jenis.value;
    const list = items.filter(it => (!c || it.cat === c) && (!q || norm(it.name + ' ' + it.desc).includes(q)));
    jenis.length = 1;
    list.forEach(it => jenis.add(new Option(it.name, it.key)));
    jenis.options[0].textContent = list.length ? '— Pilih jenis surat —' : 'Tidak ada surat yang cocok';
    if (list.some(it => it.key === keep)) jenis.value = keep; else jenis.value = '';
    // Satu hasil pencarian: langsung dipilih.
    if (list.length === 1 && q) jenis.value = list[0].key;
    count.textContent = q || c ? `${list.length} jenis surat tersedia` : '';
    showCard();
  };

  const showCard = () => {
    const it = items.find(x => x.key === jenis.value);
    card.hidden = !it;
    if (!it) return;
    $('lsm-card-cat').textContent = it.cat;
    $('lsm-card-cat').className = 'lsm-badge cat-' + norm(it.cat);
    $('lsm-card-name').textContent = it.name;
    $('lsm-card-desc').textContent = it.desc;
    $('lsm-card-go').href = it.url;
    icon.innerHTML = '';
    if (it.icon) { const img = new Image(); img.src = it.icon; img.alt = ''; icon.appendChild(img); }
    else { const s = document.createElement('span'); s.className = 'letter-icon letter-icon-' + it.key; icon.appendChild(s); }
  };

  search.addEventListener('input', fillJenis);
  cat.addEventListener('change', () => { jenis.value = ''; fillJenis(); });
  jenis.addEventListener('change', () => { showCard(); if (!card.hidden) card.scrollIntoView({ block: 'nearest', behavior: matchMedia('(prefers-reduced-motion:reduce)').matches ? 'auto' : 'smooth' }); });
  // Bisa dibuka dengan ?jenis=kunci / #kunci untuk memilih langsung.
  const pre = new URLSearchParams(location.search).get('jenis') || location.hash.slice(1);
  fillJenis();
  if (pre && items.some(x => x.key === pre)) { jenis.value = pre; showCard(); }
})();
