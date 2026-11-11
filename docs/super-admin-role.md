# Super Admin platform di luar akun pengurus

Aturan terbaru menggantikan implementasi role `super_admin` pada akun pengurus. Super Admin adalah identitas milik operator server, bukan role Administrator RT atau data akun aplikasi.

## Login dan penyimpanan identitas

- Login khusus: `/platform/login`.
- Form `/admin/login` juga mengenali kredensial pemilik server dan mengarahkannya ke panel platform. Identitas dan sesi tetap terpisah; tidak membuat akun/role pengurus. Kedua form menggunakan pemeriksaan password dan pembatasan percobaan login yang sama.
- Username pemilik yang dikonfigurasi: `chendy`.
- Password mengikuti permintaan pemilik dan hanya disimpan sebagai hash. Password tidak dicantumkan pada dokumen, source code aplikasi, atau database akun.
- Konfigurasi privat berada pada `application/config/platform_owner.php`, terlindung dari akses web oleh aturan direktori aplikasi, dan dikecualikan dari version control. Template nonaktif disediakan pada `platform_owner.example.php`.
- Penyiapan/pergantian kredensial hanya melalui operator server, memakai `tools/setup-platform.ps1` atau `tools/set-platform-owner.php`. Tidak ada formulir tambah, reset password, atau ubah identitas pemilik di aplikasi.

Identitas ini tidak dibuat pada `admins` maupun `platform_admins`, tidak muncul pada daftar pengurus/matriks role, dan tidak bisa ditambahkan dengan mengirim parameter role palsu. Tabel identitas platform legacy tidak lagi dipakai untuk autentikasi. Akun pengurus dengan username yang sama dengan pemilik server tidak dapat dibuat.

## Wewenang

Setelah login khusus, pemilik server mengontrol katalog Gratis/Premium/Nonaktif, paket, harga, manfaat, komposisi fitur, aktivasi/perpanjangan, masa berlaku, trial/override, serta audit perubahan. Login ini tidak membuat sesi pengurus atau akun warga. Administrator RT tetap memakai akun dan izin operasional existing serta tunduk pada status langganan.

Semua endpoint pengelolaan, termasuk alias `/admin/langganan/kelola`, `/admin/langganan/paket`, dan POST kategori, memerlukan sesi pemilik platform yang valid. Mengetahui route atau memiliki role Administrator RT tidak memberikan akses.

Sesi pemilik memakai namespace terpisah dan kedaluwarsa satu jam. Fingerprint konfigurasi diperiksa setiap permintaan, sehingga pergantian kredensial mengakhiri validitas sesi lama. Login dibatasi delapan kegagalan per IP dalam 15 menit. Mutasi memakai CSRF dan dicatat pada audit langganan dengan scope `platform_owner`, tanpa mengambil ID akun pengurus.

## Migrasi dan kompatibilitas

Role `super_admin` dihapus dari pilihan Peran dan matriks izin. Bootstrap/pembuatan Super Admin dari akun pengurus telah dihapus. Jika sebelumnya ada akun pengurus dengan role lokal tersebut, migrasi menurunkan role menjadi Administrator untuk mempertahankan akun/data operasional; role istimewanya tidak lagi berlaku. Kolom ENUM kembali berisi role pengurus biasa. Tidak ada perubahan otomatis terhadap password pengurus.

`tools/create-platform-admin.php` hanya mempertahankan mode migrasi skema; tidak lagi membuat akun pemilik dalam database.

## Perbaikan tampilan dan hasil uji

Struktur entri sidebar divalidasi sebelum penyaringan izin agar definisi menu yang tidak valid tidak menghentikan seluruh halaman admin. Error logging aplikasi diaktifkan pada tingkat error saja, tanpa menampilkan detail internal di halaman pengguna.

`tests/platform_owner_smoke.php` memverifikasi dashboard admin lengkap, tidak adanya pemilik pada daftar/role, penolakan tambah Super Admin dan username pemilik melalui endpoint akun, pemisahan sesi, pengabaian identitas database legacy, kontrol paket, audit, hash password, dan logout. `tests/super_admin_smoke.php` meneruskan pengujian kebijakan terbaru ini.

Suite langganan, editor paket, dan halaman paket juga lolos pada database/storage disposable. Browser memeriksa dashboard lengkap, halaman akun tanpa role pemilik, login platform, penyimpanan kategori Premium, editor paket, dan tampilan mobile. Login pemilik serta halaman kontrol/editor juga diverifikasi langsung pada XAMPP; tidak ada akun pemilik yang dibuat pada tabel pengurus.

Pratinjau lingkungan uji: `tmp/family-preview/admin-dashboard-recovered.png`, `pengurus-without-owner.png`, dan `platform-owner-control.png`.
