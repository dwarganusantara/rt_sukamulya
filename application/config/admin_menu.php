<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Definisi menu sidebar admin (satu sumber untuk sidebar, halaman Akun & Hak Akses, dan penegakan server).
 * Format item: array(path, ikon, label, peran)
 *   peran = array role yang diizinkan, ATAU string 'perm:<izin>' yang dibaca dari rt_access.rt_permissions,
 *           ATAU '*' untuk semua peran pengurus.
 * Hak Akses Menu per peran (tabel role_menu_access) hanya dapat MENGURANGI akses dari daftar ini, tidak menambah.
 */
$config['admin_menu_groups'] = array(
    'Utama' => array('orange', array(
        array('admin', 'home', 'Dashboard', array('admin', 'operator', 'ketua', 'sekretaris', 'keamanan', 'inventaris', 'sosial', 'auditor')),
    )),
    'Kependudukan' => array('green', array(
        array('admin/citizens', 'users', 'Data Warga', array('admin', 'operator', 'ketua')),
        array('admin/penduduk', 'idcard', 'Data Penduduk', 'perm:penduduk.read'),
        array('admin/kk/pengajuan', 'idcard', 'Verifikasi KK', array('admin', 'operator', 'ketua')),
        array('admin/mutasi', 'swap', 'Mutasi Warga', 'perm:mutasi.read'),
        array('admin/bansos', 'heart', 'Bantuan Sosial', 'perm:bansos.read'),
    )),
    'Layanan Warga' => array('green', array(
        array('admin/requests', 'doc', 'Pengajuan Warga', array('admin', 'operator', 'ketua')),
        array('admin/letter_types', 'tag', 'Jenis Surat & Ikon', array('admin', 'ketua')),
        array('admin/pengaduan', 'alert', 'Pengaduan & Aspirasi', 'perm:pengaduan.read'),
    )),
    'Kegiatan & Informasi' => array('blue', array(
        array('admin/posts/pengumuman', 'megaphone', 'Pengumuman', array('admin', 'operator', 'ketua')),
        array('admin/posts/agenda', 'calendar', 'Agenda Kegiatan', array('admin', 'operator', 'ketua')),
        array('admin/posts/galeri', 'gallery', 'Galeri Kegiatan', array('admin', 'operator', 'ketua')),
        array('admin/page-images', 'image', 'Gambar Halaman', array('admin', 'ketua')),
    )),
    'Keuangan' => array('purple', array(
        array('admin/dues', 'wallet', 'Iuran Warga', array('admin', 'bendahara', 'ketua')),
        array('admin/tunggakan', 'clock', 'Tunggakan Iuran Warga', 'perm:iuran.report'),
        array('admin/transactions', 'chart', 'Kas RT', array('admin', 'bendahara', 'ketua')),
        array('admin/rekonsiliasi', 'scale', 'Rekonsiliasi Kas', 'perm:kas.report'),
    )),
    'Kegiatan & Keamanan' => array('orange', array(
        array('admin/program', 'briefcase', 'Program Kerja', 'perm:program.read'),
        array('admin/notulen', 'clipboard', 'Catatan & Notulen', 'perm:notulen.read'),
        array('admin/ronda', 'shield', 'Jadwal Ronda', 'perm:ronda.read'),
    )),
    'Operasional RT' => array('blue', array(
        array('admin/inventaris', 'box', 'Aset & Inventaris', 'perm:aset.read'),
        array('admin/belanja', 'cart', 'Belanja & Penerimaan', 'perm:belanja.read'),
        array('admin/kontak', 'phone', 'Kontak Penting', 'perm:kontak.read'),
        array('admin/arsip', 'archive', 'Arsip Dokumen', 'perm:arsip.read'),
    )),
    'Pengaturan' => array('red', array(
        array('admin/fitur', 'grid', 'Manajemen Fitur & Menu', 'perm:fitur.manage'),
        array('admin/langganan', 'crown', 'Paket & Langganan', '*'),
        array('admin/pages', 'user', 'Profil RT', array('admin', 'operator', 'ketua')),
        array('admin/settings', 'gear', 'Pengaturan Aplikasi', array('admin', 'ketua')),
        array('admin/tampilan', 'image', 'Tema & Tampilan', 'perm:appearance.manage'),
        array('admin/officials', 'org', 'Manajemen Pengurus', array('admin', 'operator', 'ketua')),
        array('admin/letter_settings', 'mail', 'Pengaturan Surat RT', array('admin', 'ketua')),
        array('admin/quick_links', 'grid', 'Menu & Ikon', array('admin', 'ketua')),
        array('admin/akun', 'key', 'Akun & Hak Akses', 'perm:akun.manage'),
        array('admin/field-tambahan', 'grid', 'Field Tambahan Warga', 'perm:pengaturan.fields'),
        array('admin/antrean-email', 'mail', 'Antrean Email', 'perm:notifikasi.manage'),
        array('admin/serah-terima', 'org', 'Serah Terima Pengurus', 'perm:serahterima.read'),
        array('admin/audit', 'shield', 'Audit Aktivitas', 'perm:audit.read'),
        array('admin/backup', 'database', 'Backup Data', 'perm:backup.run'),
        array('admin/pengaturan/riwayat', 'clock', 'Riwayat Pengaturan', 'perm:pengaturan.history'),
    )),
);

/*
 * Menu yang tidak boleh dimatikan lewat Hak Akses Menu (mencegah pengurus terkunci dari aplikasi).
 * path => daftar role yang dikunci ('*' = semua role yang berhak menurut RBAC).
 */
$config['admin_menu_locked'] = array(
    'admin' => '*',
    'admin/langganan' => '*',
    'admin/akun' => array('admin'),
    'admin/tampilan' => array('admin'), // jalur "Pulihkan Tampilan" harus selalu bisa dibuka
);

/*
 * Menu inti: selalu Gratis dan tidak dapat dijadikan Berlangganan/Nonaktif oleh platform,
 * agar RT tetap bisa login, melihat paket, dan mengelola akun (MD §10.7).
 */
$config['admin_menu_core'] = array('admin', 'admin/langganan', 'admin/akun', 'admin/tampilan');

/* Deskripsi singkat peran untuk tab Pengaturan Peran. */
$config['admin_role_descriptions'] = array(
    'admin' => 'Akses penuh pengelolaan RT, akun pengurus, dan pengaturan aplikasi.',
    'ketua' => 'Memantau dan menyetujui kegiatan, keuangan, dan layanan warga.',
    'sekretaris' => 'Administrasi surat, arsip, notulen, dan data kependudukan.',
    'bendahara' => 'Pengelolaan iuran, kas RT, dan rekonsiliasi keuangan.',
    'operator' => 'Input harian: data warga, pengajuan, dan informasi kegiatan.',
    'keamanan' => 'Jadwal ronda, laporan kejadian, dan pengaduan keamanan.',
    'inventaris' => 'Aset, peminjaman barang, dan penerimaan belanja.',
    'sosial' => 'Pendataan dan penyaluran bantuan sosial.',
    'auditor' => 'Peninjauan baca-saja atas data, keuangan, dan audit aktivitas.',
);
