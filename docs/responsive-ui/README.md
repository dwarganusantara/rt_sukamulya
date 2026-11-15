# Responsive UI/UX — RT/RW Digital (14 Nov 2026)

Lapisan responsif global untuk seluruh modul admin, portal warga, dan situs publik. Hanya tampilan: tidak ada perubahan database, controller, model, routing, validasi, atau hak akses.

## Cara kerja

| File | Peran |
|---|---|
| `assets/responsive-ui.css` | Dimuat **terakhir** di `admin/header.php`, `site/header.php`, `citizen/dashboard.php`. Breakpoint: mobile ≤767, tablet 768–1199, desktop ≥1200 (desktop tidak diubah kecuali perbaikan overflow). |
| `assets/responsive-ui.js` | Drawer (tombol tutup, Escape, tombol kembali), filter lipat, tabel generik → kartu, tab form, peringatan perubahan belum disimpan. |

Penanda opsional di view:

- `data-rsp-keep` — tabel/elemen dibiarkan apa adanya (tidak dijadikan kartu).
- `data-rsp-tabs` pada wadah + `data-rsp-tab="Label"` pada anak langsung — menjadi tab di mobile (panel hanya disembunyikan, nilai input tetap; field wajib kosong otomatis membuka tabnya).
- `data-rsp-noguard` pada form — tanpa peringatan "perubahan belum disimpan".

## Perilaku per komponen

- **Header admin**: 58px di mobile, sticky; pencarian ringkas; nama akun disembunyikan (avatar tetap); dropdown akun selebar layar.
- **Sidebar**: drawer ≤950px (sudah ada) + tombol tutup, Escape, tombol kembali, overlay; di 951–1199px default tertutup (preferensi pengguna di localStorage tetap dihormati).
- **Hero/judul halaman**: satu baris ringkas, deskripsi maks 2 baris, ilustrasi disembunyikan di mobile.
- **Statistik**: grid 2 kolom (`pg-stats`, `ops-stats`, `request-stats-v2`, `dux-metrics`, `cash-stats`, `cdx-metrics`, `cwp-stats`, `pfy-stats`, `sp-summary`).
- **Tab navigasi**: satu baris dengan scroll lokal (bukan grid bertumpuk).
- **Filter**: pencarian tetap terlihat; filter lanjutan di tombol **Filter** (dengan badge jumlah filter aktif).
- **Tabel**: `pg-table` → kartu grid 2 kolom label/nilai, aksi (Detail, ⋮) di bawah kartu. Tabel lain yang sederhana → kartu otomatis (`rsp-cards`, label dari `<thead>`); tabel kompleks (colspan/rowspan) → scroll horizontal hanya di dalam tabel.
- **Input**: 16px di mobile (mencegah auto-zoom iOS). **Area sentuh**: ≥44px untuk tombol utama, menu ⋮, aksi tabel.
- **Dialog**: hampir layar penuh di mobile, isi dapat discroll.
- **Manajemen Fitur & Menu**: kolom Akses = satu tombol dropdown dengan ringkasan `Warga` / `Pengurus` / `2 Akses` / `Tidak ada akses`; pilihan bergaya checkbox (`menuitemcheckbox`), disimpan lewat form `admin/fitur/update` yang sama.

## Pengujian

Gunakan salinan DB, bukan live:

```powershell
# salinan lokal: sukamulya_rsp_test (akun uji rsp_admin / warga dewi, password Preview#2026 — hanya di salinan)
$env:DB_NAME='sukamulya_rsp_test'; php -S 127.0.0.1:8098 -t . <router.php>   # router: return false untuk file statis
node tests/responsive_audit.cjs tmp/responsive-audit --shots   # 100 halaman × 11 lebar
node tests/responsive_functions.cjs                             # 29 uji fungsi
```

Hasil 14 Nov 2026: **1100 cek, 0 overflow / error JS / error PHP**; 0 input <16px di mobile; **29/29** uji fungsi lulus. Laporan: `report.txt`, `report.json`; screenshot pilihan di `screenshots/` (set lengkap di `tmp/responsive-audit/`, tidak di-commit).
