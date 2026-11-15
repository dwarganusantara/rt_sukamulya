/*
 * Foto setiap fitur admin & portal warga versi HP (390x844) + galeri HTML.
 * Jalankan terhadap SALINAN DB (lihat tests/responsive_audit.cjs):  node tests/mobile_feature_gallery.cjs [outDir]
 */
const fs = require('fs'), path = require('path');
const { chromium } = require(process.env.PLAYWRIGHT_PATH || 'C:/Users/chendy/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const BASE = process.env.RSP_BASE || 'http://127.0.0.1:8098/';
const OUT = process.argv[2] || path.join(process.cwd(), 'docs', 'responsive-ui', 'mobile-fitur');
const G = {
  admin: [
    ['Utama', [['admin', 'Dashboard']]],
    ['Kependudukan', [['admin/keluarga', 'Data Warga (Master KK)'], ['admin/keluarga/baru', 'Tambah KK Manual'], ['admin/rumah', 'Rumah & Domisili'], ['admin/rumah/baru', 'Tambah Rumah'], ['admin/rumah/tautkan', 'Hubungkan KK ke Rumah'], ['admin/penduduk', 'Data Penduduk'], ['admin/penduduk/rekap', 'Rekap Penduduk'], ['admin/kk/pengajuan', 'Verifikasi KK'], ['admin/mutasi', 'Mutasi Warga'], ['admin/mutasi/baru', 'Catat Mutasi'], ['admin/bansos', 'Bantuan Sosial'], ['admin/bansos/baru', 'Program Bantuan Baru'], ['admin/citizens', 'Akun Warga'], ['admin/citizen', 'Tambah Akun Warga']]],
    ['Layanan Warga', [['admin/requests', 'Pengajuan Warga (Surat)'], ['admin/letter_types', 'Jenis Surat & Ikon'], ['admin/letter_type', 'Tambah Jenis Surat'], ['admin/pengaduan?status=all', 'Pengaduan & Aspirasi'], ['admin/chat', 'Live Chat Warga']]],
    ['Kegiatan & Informasi', [['admin/posts/pengumuman', 'Pengumuman'], ['admin/post/pengumuman', 'Tambah Pengumuman'], ['admin/posts/agenda', 'Agenda Kegiatan'], ['admin/posts/galeri', 'Galeri Kegiatan'], ['admin/page-images', 'Gambar Halaman']]],
    ['Keuangan', [['admin/dues', 'Iuran Warga'], ['admin/due', 'Tambah Iuran'], ['admin/tunggakan', 'Tunggakan Iuran'], ['admin/transactions', 'Kas RT (Transaksi)'], ['admin/transaction', 'Tambah Transaksi Kas'], ['admin/rekonsiliasi', 'Rekonsiliasi Kas']]],
    ['Kegiatan & Keamanan', [['admin/program', 'Program Kerja'], ['admin/program/baru', 'Program Kerja Baru'], ['admin/notulen', 'Catatan & Notulen'], ['admin/notulen/baru', 'Notulen Baru'], ['admin/ronda', 'Jadwal & Absensi Ronda']]],
    ['Operasional RT', [['admin/inventaris', 'Aset & Inventaris'], ['admin/inventaris/pinjam?status=all', 'Peminjaman Aset'], ['admin/belanja', 'Belanja & Penerimaan'], ['admin/kontak', 'Kontak Penting'], ['admin/arsip', 'Arsip Dokumen']]],
    ['Pengaturan', [['admin/fitur', 'Manajemen Fitur & Menu'], ['admin/langganan', 'Paket & Langganan'], ['admin/pages', 'Profil RT (Halaman)'], ['admin/settings', 'Pengaturan Aplikasi'], ['admin/tampilan', 'Tema & Tampilan'], ['admin/officials', 'Manajemen Pengurus'], ['admin/official', 'Tambah Posisi Pengurus'], ['admin/letter_settings', 'Pengaturan Surat RT'], ['admin/quick_links', 'Menu & Ikon'], ['admin/akun', 'Akun & Hak Akses'], ['admin/field-tambahan', 'Field Tambahan Warga'], ['admin/antrean-email', 'Antrean Email'], ['admin/serah-terima', 'Serah Terima Pengurus'], ['admin/audit', 'Audit Aktivitas'], ['admin/backup', 'Backup Data'], ['admin/pengaturan/riwayat', 'Riwayat Pengaturan'], ['admin/notifications', 'Notifikasi'], ['admin/password', 'Ganti Password']]],
  ],
  warga: [
    ['Portal Warga', [['warga', 'Dashboard Warga'], ['warga/profil', 'Profil Saya'], ['warga/profil/edit', 'Edit Profil'], ['warga/keluarga', 'Data Kartu Keluarga'], ['warga/keluarga/scan', 'Upload / Scan KK'], ['warga/layanan', 'Layanan Surat'], ['warga/ajukan', 'Pengajuan Surat'], ['warga/riwayat', 'Riwayat Pengajuan'], ['warga/iuran', 'Iuran Saya'], ['warga/chat', 'Live Chat'], ['warga/mutasi', 'Mutasi Data Keluarga'], ['warga/pengaduan', 'Pengaduan Saya'], ['warga/pengaduan/buat', 'Buat Pengaduan'], ['warga/program', 'Program Kerja'], ['warga/ronda', 'Jadwal Ronda'], ['warga/kontak-penting', 'Kontak Penting'], ['warga/password', 'Ubah Password']]],
    ['Halaman Publik', [['', 'Beranda'], ['pengumuman', 'Pengumuman'], ['agenda', 'Agenda & Acara'], ['informasi', 'Informasi Warga'], ['galeri', 'Galeri Kegiatan'], ['keuangan', 'Laporan Kas RT'], ['profil', 'Profil RT'], ['struktur', 'Struktur Organisasi'], ['kontak', 'Kontak RT']]],
  ],
};

async function ctxFor(browser, kind) {
  const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  const page = await ctx.newPage();
  await page.goto(BASE + (kind === 'admin' ? 'admin/login' : 'warga/login'));
  await page.fill('input[name=username]', kind === 'admin' ? (process.env.RSP_ADMIN || 'rsp_admin') : (process.env.RSP_WARGA || 'dewi'));
  await page.fill('input[name=password]', process.env.RSP_PW || 'Preview#2026');
  await Promise.all([page.waitForNavigation(), page.locator('form button[type=submit]').first().click()]);
  await page.close();
  return ctx;
}

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const sections = [];
  for (const kind of ['admin', 'warga']) {
    const ctx = await ctxFor(browser, kind);
    const dir = path.join(OUT, kind); fs.mkdirSync(dir, { recursive: true });
    let n = 0;
    for (const [group, items] of G[kind]) {
      const shots = [];
      for (const [url, label] of items) {
        n++;
        const page = await ctx.newPage();
        let status = 0;
        try { const r = await page.goto(BASE + url, { waitUntil: 'load', timeout: 30000 }); status = r ? r.status() : 0; } catch (e) {}
        await page.evaluate(() => { document.querySelectorAll('dialog[open]').forEach(d => d.close()); }).catch(() => {});
        await page.waitForTimeout(350);
        const file = String(n).padStart(2, '0') + '-' + (url || 'beranda').replace(/\?.*$/, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') + '.jpg';
        await page.screenshot({ path: path.join(dir, file), type: 'jpeg', quality: 78 });
        const note = await page.evaluate(() => /Fitur Berlangganan/.test(document.body.innerText) ? 'Terkunci paket langganan' : (/Halaman tidak ditemukan|An Error Was Encountered/.test(document.body.innerText) ? 'Tidak tersedia' : ''));
        shots.push({ file: kind + '/' + file, label, url: '/' + url, status, note });
        await page.close();
        console.log(kind, n, label, status, note);
      }
      sections.push({ kind, group, shots });
    }
    await ctx.close();
  }
  await browser.close();
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const html = `<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Galeri Fitur Mobile</title>
<style>:root{--bg:#fdf3ea;--ink:#2b1a12;--or:#f05a14;--card:#fff}body{margin:0;background:var(--bg);color:var(--ink);font-family:Nunito,'Segoe UI',sans-serif}
header{position:sticky;top:0;z-index:2;padding:14px 16px;background:#fffaf6ee;backdrop-filter:blur(8px);border-bottom:1px solid #f1e3d6}h1{margin:0;font-size:20px}header p{margin:4px 0 0;font-size:13px;color:#6f7280}
nav{display:flex;gap:6px;overflow-x:auto;margin-top:10px}nav a{flex:none;padding:6px 12px;border-radius:99px;background:#fff;color:var(--ink);font-size:13px;font-weight:700;text-decoration:none;border:1px solid #f1e3d6}
h2{margin:28px 16px 10px;font-size:18px}h2 small{color:var(--or);font-size:12px;letter-spacing:.08em;text-transform:uppercase;display:block}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;padding:0 16px}
figure{margin:0;background:var(--card);border-radius:16px;padding:8px;box-shadow:0 6px 16px #c2551a14}figure img{display:block;width:100%;border-radius:10px;border:1px solid #eee}
figcaption{padding:8px 4px 2px;font-size:13.5px;font-weight:800}figcaption code{display:block;color:#6f7280;font-size:11.5px;font-weight:600}figcaption em{display:inline-block;margin-top:4px;padding:2px 8px;border-radius:99px;background:#ffeadb;color:#c2410c;font-size:11px;font-style:normal}</style></head><body>
<header><h1>Galeri Fitur Versi HP (390px)</h1><p>${sections.reduce((a, s) => a + s.shots.length, 0)} halaman · diambil ${new Date().toLocaleString('id-ID')} dari salinan DB uji</p><nav>${sections.map((s, i) => `<a href="#g${i}">${esc((s.kind === 'admin' ? 'Admin · ' : 'Warga · ') + s.group)}</a>`).join('')}</nav></header>
${sections.map((s, i) => `<h2 id="g${i}"><small>${s.kind === 'admin' ? 'Panel Admin' : 'Warga'}</small>${esc(s.group)}</h2><div class="grid">${s.shots.map(x => `<figure><a href="${x.file}" target="_blank"><img src="${x.file}" alt="${esc(x.label)}" loading="lazy"></a><figcaption>${esc(x.label)}<code>${esc(x.url)}</code>${x.note ? `<em>${esc(x.note)}</em>` : ''}</figcaption></figure>`).join('')}</div>`).join('')}
<p style="padding:24px 16px;color:#6f7280;font-size:12px">Dibuat oleh tests/mobile_feature_gallery.cjs</p></body></html>`;
  fs.writeFileSync(path.join(OUT, 'index.html'), html);
  fs.writeFileSync(path.join(OUT, 'daftar.json'), JSON.stringify(sections, null, 1));
  console.log('Galeri:', path.join(OUT, 'index.html'));
})().catch(e => { console.error(e); process.exit(1); });
