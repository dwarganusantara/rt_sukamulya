<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Katalog Manajemen Fitur & Menu (spesifikasi: Bisnis_Proses_Manajemen_Fitur_RT_RW.md).
 * Hanya modul yang SUDAH dibangun dan dipetakan ke route nyata. Kode dipakai di app_features.code.
 *
 *  admin  : awalan URI halaman pengurus (admin/...)
 *  warga  : awalan URI portal warga & halaman publik terkait
 *  parent : kode fitur induk (BR-03/BR-11: anak tidak efektif bila induk nonaktif)
 *  open   : SQL hitung transaksi belum selesai (peringatan saat dinonaktifkan, BR-07/4C)
 *
 * Fitur inti (dashboard, login, profil, akun, hak akses, backup, audit, pengaturan) TIDAK masuk katalog
 * sehingga tidak dapat dimatikan (BR-12).
 */
$config['feature_tenant_id'] = 1; // Aplikasi satu RT: tenant default.

$config['feature_catalog'] = array(
    // ---------- Kependudukan ----------
    'family_card' => array('name' => 'Kartu Keluarga', 'desc' => 'Ajukan, lihat, dan verifikasi data Kartu Keluarga (KK)', 'category' => 'Kependudukan', 'icon' => 'people', 'tone' => 'blue',
        'admin' => array('admin/kk'), 'warga' => array('warga/keluarga'),
        'open' => "SELECT COUNT(*) n FROM kk_scan_requests WHERE status='pending'"),
    'resident_profile' => array('name' => 'Data Penduduk', 'desc' => 'Daftar KK & anggota keluarga terverifikasi beserta rekap statistik', 'category' => 'Kependudukan', 'icon' => 'list', 'tone' => 'blue',
        'admin' => array('admin/penduduk'), 'warga' => array()),
    'resident_mutation' => array('name' => 'Mutasi Warga', 'desc' => 'Pengajuan masuk, pindah, meninggal, dan koreksi data anggota', 'category' => 'Kependudukan', 'icon' => 'swap', 'tone' => 'blue', 'parent' => 'family_card',
        'admin' => array('admin/mutasi'), 'warga' => array('warga/mutasi'),
        'open' => "SELECT COUNT(*) n FROM resident_mutations WHERE status IN ('submitted','in_review','revision_requested')"),
    'social_aid' => array('name' => 'Bantuan Sosial', 'desc' => 'Pendataan dan pengelolaan program bantuan sosial', 'category' => 'Kependudukan', 'icon' => 'heart', 'tone' => 'red',
        'admin' => array('admin/bansos'), 'warga' => array(),
        'open' => "SELECT COUNT(*) n FROM aid_recipients WHERE status IN ('usulan','layak','disetujui')"),
    // ---------- Layanan Warga ----------
    'letters' => array('name' => 'Pengajuan Surat', 'desc' => 'Pengajuan berbagai surat pengantar RT', 'category' => 'Layanan Warga', 'icon' => 'doc', 'tone' => 'purple',
        'admin' => array('admin/requests', 'admin/request_detail', 'admin/letter_types', 'admin/letter_type', 'admin/letter_settings'),
        'warga' => array('warga/layanan', 'warga/ajukan', 'warga/riwayat', 'warga/draft', 'warga/pengajuan', 'warga/lampiran', 'warga/dokumen', 'warga/surat', 'warga/aset-surat'),
        'open' => "SELECT COUNT(*) n FROM service_requests WHERE status IN ('diajukan','diproses')"),
    'complaints' => array('name' => 'Pengaduan & Aspirasi', 'desc' => 'Sampaikan keluhan, usulan, dan saran', 'category' => 'Layanan Warga', 'icon' => 'megaphone', 'tone' => 'red',
        'admin' => array('admin/pengaduan'), 'warga' => array('warga/pengaduan'),
        'open' => "SELECT COUNT(*) n FROM complaints WHERE status IN ('submitted','triaged','assigned','in_progress','waiting_resident','reopened')"),
    'live_chat' => array('name' => 'Live Chat Warga', 'desc' => 'Percakapan langsung warga dengan pengurus', 'category' => 'Layanan Warga', 'icon' => 'chat', 'tone' => 'green',
        'admin' => array('admin/chat', 'admin/chat_inbox', 'admin/chat_messages', 'admin/chat_send', 'admin/chat_note'), 'warga' => array('warga/chat')),
    // ---------- Keuangan ----------
    'dues' => array('name' => 'Iuran Warga', 'desc' => 'Kelola tagihan dan pembayaran iuran', 'category' => 'Keuangan', 'icon' => 'database', 'tone' => 'orange',
        'admin' => array('admin/dues', 'admin/due', 'admin/due_delete', 'admin/dues_export', 'admin/due_payment_record', 'admin/review_due_payment', 'admin/billing_campaign'), 'warga' => array('warga/iuran', 'warga/bukti-iuran'),
        'open' => "SELECT COUNT(*) n FROM due_payments WHERE status='pending'"),
    'dues_arrears' => array('name' => 'Tunggakan Iuran Warga', 'desc' => 'Laporan iuran yang belum dibayar dan pengingat', 'category' => 'Keuangan', 'icon' => 'clock', 'tone' => 'amber', 'parent' => 'dues',
        'admin' => array('admin/tunggakan'), 'warga' => array()),
    'cash_report' => array('name' => 'Kas RT', 'desc' => 'Pencatatan kas dan laporan keuangan publik', 'category' => 'Keuangan', 'icon' => 'chart', 'tone' => 'orange',
        'admin' => array('admin/transactions', 'admin/transaction', 'admin/edit_transaction', 'admin/review_transaction', 'admin/submit_transaction'), 'warga' => array('keuangan','bukti')),
    'cash_reconciliation' => array('name' => 'Rekonsiliasi Kas', 'desc' => 'Pencocokan saldo sistem dengan kas fisik/rekening', 'category' => 'Keuangan', 'icon' => 'list', 'tone' => 'orange', 'parent' => 'cash_report',
        'admin' => array('admin/rekonsiliasi'), 'warga' => array()),
    // ---------- Informasi ----------
    'announcements' => array('name' => 'Pengumuman', 'desc' => 'Informasi penting untuk seluruh warga', 'category' => 'Informasi', 'icon' => 'megaphone', 'tone' => 'orange',
        'admin' => array('admin/posts/pengumuman', 'admin/post/pengumuman'), 'warga' => array('pengumuman')),
    'events' => array('name' => 'Agenda Kegiatan', 'desc' => 'Jadwal kegiatan lingkungan RT', 'category' => 'Informasi', 'icon' => 'calendar', 'tone' => 'green',
        'admin' => array('admin/posts/agenda', 'admin/post/agenda'), 'warga' => array('agenda')),
    'gallery' => array('name' => 'Galeri Kegiatan', 'desc' => 'Dokumentasi foto kegiatan warga', 'category' => 'Informasi', 'icon' => 'image', 'tone' => 'purple',
        'admin' => array('admin/posts/galeri', 'admin/post/galeri'), 'warga' => array('galeri')),
    'contacts' => array('name' => 'Kontak Penting', 'desc' => 'Nomor darurat dan layanan publik di sekitar RT', 'category' => 'Informasi', 'icon' => 'phone', 'tone' => 'blue',
        'admin' => array('admin/kontak'), 'warga' => array('warga/kontak-penting')),
    // ---------- Operasional ----------
    'patrol' => array('name' => 'Jadwal Ronda', 'desc' => 'Jadwal, absensi, dan laporan kejadian ronda', 'category' => 'Operasional', 'icon' => 'shield', 'tone' => 'green',
        'admin' => array('admin/ronda'), 'warga' => array('warga/ronda')),
    'work_program' => array('name' => 'Program Kerja', 'desc' => 'Rencana, progres, dan evaluasi program kerja pengurus', 'category' => 'Operasional', 'icon' => 'briefcase', 'tone' => 'blue',
        'admin' => array('admin/program'), 'warga' => array()),
    'activity_notes' => array('name' => 'Catatan & Notulen', 'desc' => 'Notulen rapat dan tindak lanjut', 'category' => 'Operasional', 'icon' => 'lines', 'tone' => 'blue',
        'admin' => array('admin/notulen'), 'warga' => array()),
    'inventory' => array('name' => 'Aset & Inventaris', 'desc' => 'Data aset RT dan peminjaman barang', 'category' => 'Operasional', 'icon' => 'box', 'tone' => 'amber',
        'admin' => array('admin/inventaris'), 'warga' => array(),
        'open' => "SELECT COUNT(*) n FROM asset_loans WHERE status IN ('requested','approved','borrowed')"),
    'purchasing' => array('name' => 'Belanja & Penerimaan', 'desc' => 'Pengajuan belanja, persetujuan, dan penerimaan barang', 'category' => 'Operasional', 'icon' => 'cart', 'tone' => 'amber',
        'admin' => array('admin/belanja'), 'warga' => array(),
        'open' => "SELECT COUNT(*) n FROM purchase_requests WHERE status IN ('submitted','approved')"),
    'documents' => array('name' => 'Arsip Dokumen', 'desc' => 'Penyimpanan dokumen dan arsip RT', 'category' => 'Operasional', 'icon' => 'database', 'tone' => 'blue',
        'admin' => array('admin/arsip'), 'warga' => array()),
);
