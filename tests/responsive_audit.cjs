/*
 * Audit responsif seluruh modul (Playwright + Chrome sistem).
 * Jalankan terhadap SALINAN DB (bukan DB live):
 *   $env:DB_NAME='sukamulya_rsp_test'; php -S 127.0.0.1:8098 -t . <router.php>
 *   node tests/responsive_audit.cjs [outDir] [--widths=320,768] [--only=admin/fitur] [--shots]
 * Env: RSP_BASE (default http://127.0.0.1:8098/), RSP_ADMIN, RSP_WARGA, RSP_PW, PLAYWRIGHT_PATH.
 * Elemen di dalam wadah overflow-x auto/scroll/hidden (scroll lokal tabel) tidak dihitung overflow halaman.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require(process.env.PLAYWRIGHT_PATH || 'C:/Users/chendy/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');

const BASE = process.env.RSP_BASE || 'http://127.0.0.1:8098/';
const ADMIN = process.env.RSP_ADMIN || 'rsp_admin';
const WARGA = process.env.RSP_WARGA || 'dewi';
const PW = process.env.RSP_PW || 'Preview#2026';
const args = process.argv.slice(2);
const opt = k => (args.find(a => a.startsWith('--' + k + '=')) || '').split('=')[1];
const OUT = args.find(a => !a.startsWith('--')) || path.join(process.cwd(), 'tmp', 'responsive-audit');
const WIDTHS = (opt('widths') || '320,360,375,390,414,430,768,820,1024,1280,1920').split(',').map(Number);
const ONLY = opt('only');
const SHOTS = args.includes('--shots');
fs.mkdirSync(OUT, { recursive: true });

const PAGES = [
  ['anon', ''], ['anon', 'pengumuman'], ['anon', 'agenda'], ['anon', 'galeri'], ['anon', 'kontak'], ['anon', 'keuangan'], ['anon', 'profil'], ['anon', 'struktur'], ['anon', 'warga/login'], ['anon', 'admin/login'], ['anon', 'warga/daftar'],
  ['admin', 'admin'], ['admin', 'admin/keluarga'], ['admin', 'admin/keluarga/baru'], ['admin', 'admin/rumah'], ['admin', 'admin/rumah/baru'], ['admin', 'admin/rumah/tautkan'], ['admin', 'admin/rumah/1'],
  ['admin', 'admin/penduduk'], ['admin', 'admin/penduduk?tampil=keluarga'], ['admin', 'admin/penduduk/rekap'], ['admin', 'admin/penduduk/keluarga/1'],
  ['admin', 'admin/kk/pengajuan'], ['admin', 'admin/mutasi?status=all'], ['admin', 'admin/mutasi/baru'], ['admin', 'admin/bansos'], ['admin', 'admin/bansos?tab=calon'], ['admin', 'admin/bansos?tab=salur'], ['admin', 'admin/bansos?tab=laporan'], ['admin', 'admin/bansos/baru'],
  ['admin', 'admin/requests'], ['admin', 'admin/letter_types'], ['admin', 'admin/letter_type'], ['admin', 'admin/pengaduan?status=all'], ['admin', 'admin/chat'],
  ['admin', 'admin/posts/pengumuman'], ['admin', 'admin/post/pengumuman'], ['admin', 'admin/posts/agenda'], ['admin', 'admin/posts/galeri'], ['admin', 'admin/page-images'],
  ['admin', 'admin/dues'], ['admin', 'admin/due'], ['admin', 'admin/tunggakan'], ['admin', 'admin/transactions'], ['admin', 'admin/transaction'], ['admin', 'admin/rekonsiliasi'],
  ['admin', 'admin/program'], ['admin', 'admin/program/baru'], ['admin', 'admin/notulen'], ['admin', 'admin/notulen/baru'], ['admin', 'admin/ronda'], ['admin', 'admin/ronda/kejadian'],
  ['admin', 'admin/inventaris'], ['admin', 'admin/inventaris/baru'], ['admin', 'admin/inventaris/pinjam?status=all'], ['admin', 'admin/belanja'], ['admin', 'admin/belanja/baru'], ['admin', 'admin/kontak'], ['admin', 'admin/arsip'],
  ['admin', 'admin/fitur'], ['admin', 'admin/langganan'], ['admin', 'admin/pages'], ['admin', 'admin/settings'], ['admin', 'admin/tampilan'], ['admin', 'admin/officials'], ['admin', 'admin/official'],
  ['admin', 'admin/letter_settings'], ['admin', 'admin/quick_links'], ['admin', 'admin/akun'], ['admin', 'admin/akun/hak-akses'], ['admin', 'admin/akun/peran'], ['admin', 'admin/field-tambahan'],
  ['admin', 'admin/antrean-email'], ['admin', 'admin/serah-terima'], ['admin', 'admin/audit'], ['admin', 'admin/backup'], ['admin', 'admin/pengaturan/riwayat'], ['admin', 'admin/citizens'], ['admin', 'admin/citizen'],
  ['admin', 'admin/notifications'], ['admin', 'admin/search?q=a'], ['admin', 'admin/password'],
  ['warga', 'warga'], ['warga', 'warga/layanan'], ['warga', 'warga/riwayat'], ['warga', 'warga/ajukan'], ['warga', 'warga/iuran'], ['warga', 'warga/profil'], ['warga', 'warga/profil/edit'], ['warga', 'warga/password'],
  ['warga', 'warga/keluarga'], ['warga', 'warga/keluarga/scan'], ['warga', 'warga/chat'], ['warga', 'warga/mutasi'], ['warga', 'warga/mutasi/ajukan?jenis=ubah_data'], ['warga', 'warga/pengaduan'], ['warga', 'warga/pengaduan/buat'],
  ['warga', 'warga/program'], ['warga', 'warga/ronda'], ['warga', 'warga/kontak-penting'],
];

const PROBE = () => {
  const w = document.documentElement.clientWidth, sw = document.documentElement.scrollWidth, bad = [], small = [], zoomy = [];
  const label = e => e.tagName.toLowerCase() + (e.id ? '#' + e.id : '') + (typeof e.className === 'string' && e.className.trim() ? '.' + e.className.trim().split(/\s+/).slice(0, 2).join('.') : '');
  const scoped = e => { let p = e.parentElement; while (p && p !== document.body) { if (/(auto|scroll|hidden|clip)/.test(getComputedStyle(p).overflowX)) return true; p = p.parentElement; } return false; };
  const visible = e => { const r = e.getBoundingClientRect(); const cs = getComputedStyle(e); return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none' && !e.closest('[hidden],dialog:not([open]),details:not([open])>*:not(summary)'); };
  if (sw > w + 1) document.querySelectorAll('body *').forEach(e => {
    const r = e.getBoundingClientRect(); if (r.width <= 0 || r.right <= w + 1) return;
    if (getComputedStyle(e).position === 'fixed') return; if (scoped(e)) return;
    bad.push(label(e) + '(r=' + Math.round(r.right) + ')');
  });
  if (w < 768) {
    document.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=file]),select,textarea').forEach(e => { if (!visible(e)) return; if (parseFloat(getComputedStyle(e).fontSize) < 16) zoomy.push(label(e)); });
    document.querySelectorAll('main a.btn,main button,main .button,main [role=button],main input[type=submit],.ad-topbar button,.ad-topbar a').forEach(e => { if (!visible(e)) return; const r = e.getBoundingClientRect(); if (r.height < 32 || r.width < 32) small.push(label(e) + '(' + Math.round(r.width) + 'x' + Math.round(r.height) + ')'); });
  }
  const top = document.querySelector('.ad-topbar,.cz-topbar,.site-header,header');
  return { w, sw, title: document.title, h: document.documentElement.scrollHeight, headerH: top ? Math.round(top.getBoundingClientRect().height) : 0,
    bad: [...new Set(bad)].slice(0, 8), small: [...new Set(small)].slice(0, 8), zoomy: [...new Set(zoomy)].slice(0, 6), smallN: small.length, zoomyN: zoomy.length,
    err: /A PHP Error|Database Error|uncaught Exception|Severity:/i.test(document.body.innerText) };
};

async function login(browser, kind) {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  if (kind === 'anon') return ctx;
  const page = await ctx.newPage();
  await page.goto(BASE + (kind === 'admin' ? 'admin/login' : 'warga/login'));
  await page.fill('input[name=username]', kind === 'admin' ? ADMIN : WARGA);
  await page.fill('input[name=password]', PW);
  await Promise.all([page.waitForLoadState('load'), page.locator('form button[type=submit],form input[type=submit]').first().click()]);
  await page.waitForTimeout(300);
  if (/login/.test(page.url())) throw new Error('Login ' + kind + ' gagal: ' + page.url());
  await page.close();
  return ctx;
}

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const ctxs = {}; for (const k of ['anon', 'admin', 'warga']) ctxs[k] = await login(browser, k);
  const rows = []; let fail = 0;
  for (const [who, p] of PAGES) {
    if (ONLY && !p.startsWith(ONLY)) continue;
    const page = await ctxs[who].newPage();
    const jsErr = []; page.on('pageerror', e => jsErr.push(String(e.message).slice(0, 120)));
    page.on('dialog', d => d.dismiss().catch(() => {}));
    for (const w of WIDTHS) {
      await page.setViewportSize({ width: w, height: w < 768 ? 800 : 900 });
      let status = 0;
      try { const resp = await page.goto(BASE + p, { waitUntil: 'load', timeout: 30000 }); status = resp ? resp.status() : 0; } catch (e) { jsErr.push('NAV ' + e.message.slice(0, 80)); }
      await page.evaluate(() => { const d = document.querySelector('dialog[open]'); if (d) d.close(); }).catch(() => {});
      await page.waitForTimeout(150);
      const r = await page.evaluate(PROBE).catch(e => ({ w, sw: 0, bad: ['probe:' + e.message], small: [], zoomy: [] }));
      const overflow = r.sw > r.w + 1;
      const ok = !overflow && !r.err && status < 500 && !jsErr.length;
      if (!ok) fail++;
      rows.push({ who, path: p, w, status, final: page.url().replace(BASE, ''), ok, overflow, sw: r.sw, h: r.h, headerH: r.headerH, bad: r.bad, small: r.small, smallN: r.smallN, zoomy: r.zoomy, zoomyN: r.zoomyN, err: r.err, js: jsErr.splice(0) });
      if (SHOTS && [390, 820, 1024, 1280].includes(w)) await page.screenshot({ path: path.join(OUT, `${who}-${(p || 'beranda').replace(/[^a-z0-9]+/gi, '-')}-${w}.png`), fullPage: false });
    }
    await page.close();
  }
  await browser.close();
  fs.writeFileSync(path.join(OUT, 'report.json'), JSON.stringify(rows, null, 1));
  const lines = rows.filter(r => !r.ok || r.zoomyN || r.smallN).map(r => `${r.ok ? 'WARN' : 'FAIL'} ${r.w}px ${r.who} /${r.path} [${r.status}]` + (r.overflow ? ` overflow ${r.sw}>${r.w} :: ${r.bad.join(', ')}` : '') + (r.err ? ' [PHP error]' : '') + (r.js.length ? ' JS: ' + r.js.join(' | ') : '') + (r.zoomyN ? ` input<16px:${r.zoomyN}` : '') + (r.smallN ? ` tap<32:${r.smallN}` : ''));
  fs.writeFileSync(path.join(OUT, 'report.txt'), lines.join('\n'));
  console.log(`${rows.length} cek, ${fail} gagal (overflow/JS/PHP). Detail: ${path.join(OUT, 'report.txt')}`);
  process.exit(fail ? 1 : 0);
})().catch(e => { console.error(e); process.exit(2); });
