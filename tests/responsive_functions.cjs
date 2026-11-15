/*
 * Uji fungsi komponen responsif (drawer, filter lipat, dropdown Akses, tab form, peringatan belum disimpan).
 * Jalankan terhadap SALINAN DB (lihat tests/responsive_audit.cjs). Mengubah satu nilai akses fitur lalu MENGEMBALIKANNYA.
 *   node tests/responsive_functions.cjs
 */
const { chromium } = require(process.env.PLAYWRIGHT_PATH || 'C:/Users/chendy/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const BASE = process.env.RSP_BASE || 'http://127.0.0.1:8098/';
const results = [];
const check = (name, ok, info = '') => { results.push({ name, ok: !!ok, info }); console.log((ok ? 'PASS ' : 'FAIL ') + name + (info ? ' — ' + info : '')); };

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext({ viewport: { width: 390, height: 800 }, hasTouch: true, isMobile: true });
  const page = await ctx.newPage();
  const jsErr = []; page.on('pageerror', e => jsErr.push(e.message));
  await page.goto(BASE + 'admin/login');
  await page.fill('input[name=username]', process.env.RSP_ADMIN || 'rsp_admin');
  await page.fill('input[name=password]', process.env.RSP_PW || 'Preview#2026');
  await Promise.all([page.waitForLoadState('load'), page.locator('form button[type=submit]').first().click()]);
  const closeAlert = () => page.evaluate(() => { const d = document.querySelector('dialog[open]'); if (d) d.close(); });

  /* 1. Drawer mobile */
  await page.goto(BASE + 'admin/mutasi'); await closeAlert();
  const isOpen = () => page.evaluate(() => document.body.classList.contains('ad-open'));
  await page.tap('#ad-hamburger'); await page.waitForTimeout(250);
  check('Drawer terbuka via hamburger', await isOpen());
  check('Tombol tutup drawer tampil', await page.isVisible('.rsp-drawer-close'));
  await page.keyboard.press('Escape'); await page.waitForTimeout(250);
  check('Drawer tertutup dengan Escape', !(await isOpen()));
  await page.tap('#ad-hamburger'); await page.waitForTimeout(250);
  await page.tap('.rsp-drawer-close'); await page.waitForTimeout(250);
  check('Drawer tertutup dengan tombol tutup', !(await isOpen()));
  await page.tap('#ad-hamburger'); await page.waitForTimeout(250);
  const urlBefore = page.url();
  await page.goBack(); await page.waitForTimeout(400);
  check('Drawer tertutup dengan tombol kembali (tanpa meninggalkan halaman)', !(await isOpen()) && page.url() === urlBefore, page.url());
  await page.tap('#ad-hamburger'); await page.waitForTimeout(250);
  await page.mouse.click(370, 400); await page.waitForTimeout(250);
  check('Drawer tertutup dengan klik overlay', !(await isOpen()));
  const topH = await page.evaluate(() => Math.round(document.querySelector('.ad-topbar').getBoundingClientRect().height));
  check('Tinggi header mobile 56–64px', topH >= 56 && topH <= 64, topH + 'px');

  /* 2. Filter lipat */
  const advHidden = () => page.evaluate(() => { const s = document.querySelector('.pg-filter select'); return !s || s.getBoundingClientRect().height === 0; });
  check('Filter lanjutan tersembunyi default di mobile', await advHidden());
  check('Pencarian tetap terlihat', await page.isVisible('.pg-filter input[type=search]'));
  await page.tap('.rsp-filter-toggle'); await page.waitForTimeout(150);
  check('Tombol Filter menampilkan filter lanjutan', !(await advHidden()));
  await page.selectOption('.pg-filter select[name=status]', 'all');
  await Promise.all([page.waitForURL(/status=all/), page.locator('.pg-filter button[type=submit]').click()]);
  await page.waitForLoadState('load'); await closeAlert();
  check('Filter tetap bekerja (submit GET)', /status=all/.test(page.url()), page.url().replace(BASE, ''));
  check('Badge jumlah filter aktif tampil', await page.isVisible('.rsp-filter-toggle b'));

  /* 3. Kartu tabel: aksi dan detail tetap ada */
  const card = await page.evaluate(() => { const tr = document.querySelector('.pg-table tbody tr'); if (!tr) return null; return { labels: [...tr.querySelectorAll('td[data-label]')].map(t => t.dataset.label), view: !!tr.querySelector('.pg-view'), menu: !!tr.querySelector('.pg-menu'), w: tr.getBoundingClientRect().width }; });
  check('Kartu Mutasi memuat semua kolom + aksi Detail & menu ⋮', card && card.labels.length >= 6 && card.view && card.menu && card.w <= 390, JSON.stringify(card));

  /* 4. Tab form + nilai dipertahankan + validasi field wajib */
  await page.goto(BASE + 'admin/official'); await closeAlert();
  const tabs = await page.$$eval('.rsp-tabbar [role=tab]', b => b.map(x => x.textContent));
  check('Tab form tampil di mobile (Tambah Posisi)', tabs.length === 2, tabs.join(' | '));
  await page.fill('#official-name', 'Uji Responsif');
  await page.tap('.rsp-tabbar [role=tab]:nth-child(2)');
  check('Panel tab 1 tersembunyi saat tab 2 aktif', !(await page.isVisible('#official-name')));
  await page.tap('.rsp-tabbar [role=tab]:nth-child(1)');
  check('Nilai input tetap setelah pindah tab', (await page.inputValue('#official-name')) === 'Uji Responsif');
  await page.fill('#official-name', '');
  await page.tap('.rsp-tabbar [role=tab]:nth-child(2)');
  await page.evaluate(() => document.querySelector('.off-form-card').requestSubmit());
  await page.waitForTimeout(250);
  check('Field wajib kosong membuka tab berisi field tsb', await page.isVisible('#official-name'));
  check('Tidak terkirim saat field wajib kosong', /admin\/official$/.test(page.url()));

  /* 5. Peringatan perubahan belum disimpan */
  await page.fill('#official-name', 'Belum disimpan');
  let warned = false;
  page.once('dialog', d => { warned = d.type() === 'beforeunload'; d.accept(); });
  await page.goto(BASE + 'admin/officials').catch(() => {});
  await page.waitForTimeout(300);
  check('Peringatan sebelum keluar saat ada perubahan', warned);

  /* 6. Dropdown Akses (desktop) */
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto(BASE + 'admin/fitur'); await closeAlert();
  const row = page.locator('tr:has(details.ftx-access.is-multi)').first();
  const code = (await row.getAttribute('id')).replace('fitur-', '');
  const sum = () => page.locator('#fitur-' + code + ' details.ftx-access summary span').innerText();
  check('Ringkasan tombol menampilkan "2 Akses"', (await sum()) === '2 Akses');
  check('Badge Warga/Pengurus tidak tampil terus-menerus (hanya 1 tombol)', (await page.locator('#fitur-' + code + ' td[data-label=Akses] summary').count()) === 1);
  await page.click('#fitur-' + code + ' details.ftx-access summary');
  const opts = await page.$$eval('#fitur-' + code + ' .ftx-access-option button', b => b.map(x => x.textContent.trim() + ':' + x.getAttribute('aria-checked')));
  check('Dropdown berisi pilihan Warga & Pengurus (checkbox)', opts.length === 2 && opts.every(o => /:true$/.test(o)), opts.join(', '));
  await page.mouse.click(640, 120); await page.waitForTimeout(100);
  check('Dropdown tertutup dengan klik di luar', !(await page.locator('#fitur-' + code + ' details.ftx-access').evaluate(d => d.open)));
  await page.click('#fitur-' + code + ' details.ftx-access summary');
  await Promise.all([page.waitForLoadState('load'), page.click('#fitur-' + code + ' .ftx-access-option button:has-text("Pengurus")')]);
  await closeAlert();
  check('Mencabut Pengurus tersimpan → ringkasan "Warga"', (await sum()) === 'Warga', await sum());
  await page.click('#fitur-' + code + ' details.ftx-access summary');
  await Promise.all([page.waitForLoadState('load'), page.click('#fitur-' + code + ' .ftx-access-option button:has-text("Pengurus")')]);
  await closeAlert();
  check('Mengembalikan Pengurus tersimpan → "2 Akses"', (await sum()) === '2 Akses', await sum());

  /* 7. Tablet landscape: sidebar default tertutup, hamburger membukanya */
  const tctx = await browser.newContext({ viewport: { width: 1024, height: 768 }, storageState: await ctx.storageState() });
  const tp = await tctx.newPage();
  await tp.addInitScript(() => { try { localStorage.removeItem('sukamulya.sidebar.collapsed'); } catch (e) {} });
  await tp.goto(BASE + 'admin'); await tp.evaluate(() => { const d = document.querySelector('dialog[open]'); if (d) d.close(); });
  check('Tablet 1024: sidebar default tertutup', !(await tp.isVisible('.ad-sidebar')));
  await tp.click('#ad-hamburger');
  check('Tablet 1024: hamburger menampilkan sidebar', await tp.isVisible('.ad-sidebar'));
  await tp.click('#ad-hamburger');
  const dctx = await browser.newContext({ viewport: { width: 1280, height: 800 }, storageState: await ctx.storageState() });
  const dp = await dctx.newPage(); await dp.addInitScript(() => { try { localStorage.removeItem('sukamulya.sidebar.collapsed'); } catch (e) {} });
  await dp.goto(BASE + 'admin');
  check('Desktop 1280: sidebar tetap tampil (tanpa regresi)', await dp.isVisible('.ad-sidebar'));

  check('Tidak ada error JavaScript', jsErr.length === 0, jsErr.join(' | '));
  await browser.close();
  const fail = results.filter(r => !r.ok).length;
  console.log(`\n${results.length - fail}/${results.length} lulus`);
  process.exit(fail ? 1 : 0);
})().catch(e => { console.error(e); process.exit(2); });
