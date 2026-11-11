<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Matriks hak akses modul RT/RW Digital (pola module.action).
 * Backend selalu memeriksa izin ini; menyembunyikan menu bukan pengamanan.
 * Role lama (admin, operator, ketua, bendahara) tetap dipakai oleh controller Admin.
 */
$config['rt_roles'] = array(
    'admin' => 'Administrator',
    'ketua' => 'Ketua RT',
    'sekretaris' => 'Sekretaris',
    'bendahara' => 'Bendahara',
    'operator' => 'Operator',
    'keamanan' => 'Petugas Keamanan',
    'inventaris' => 'Petugas Inventaris',
    'sosial' => 'Petugas Sosial',
    'auditor' => 'Auditor / Peninjau',
);

$staff = array('admin', 'ketua', 'sekretaris', 'bendahara', 'operator', 'keamanan', 'inventaris', 'sosial', 'auditor');

$config['rt_permissions'] = array(
    // Data penduduk terverifikasi (baca saja; perubahan lewat Verifikasi KK / Mutasi).
    'penduduk.read' => array('admin', 'ketua', 'sekretaris', 'operator', 'auditor'),
    'penduduk.export' => array('admin', 'ketua', 'sekretaris'),
    'penduduk.export_full' => array('admin', 'ketua'),

    'mutasi.read' => array('admin', 'ketua', 'sekretaris', 'operator', 'auditor'),
    'mutasi.review' => array('admin', 'ketua', 'sekretaris', 'operator'),
    'mutasi.approve' => array('admin', 'ketua', 'sekretaris'),

    'pengaduan.read' => array('admin', 'ketua', 'sekretaris', 'operator', 'keamanan', 'auditor'),
    'pengaduan.handle' => array('admin', 'ketua', 'sekretaris', 'operator', 'keamanan'),
    'pengaduan.assign' => array('admin', 'ketua', 'sekretaris'),

    'program.read' => $staff,
    'program.write' => array('admin', 'ketua', 'sekretaris', 'operator'),
    'program.approve' => array('admin', 'ketua'),

    'notulen.read' => $staff,
    'notulen.write' => array('admin', 'ketua', 'sekretaris', 'operator'),
    'notulen.approve' => array('admin', 'ketua'),

    'ronda.read' => array('admin', 'ketua', 'sekretaris', 'operator', 'keamanan', 'auditor'),
    'ronda.write' => array('admin', 'ketua', 'operator', 'keamanan'),

    'aset.read' => array('admin', 'ketua', 'sekretaris', 'operator', 'inventaris', 'bendahara', 'auditor'),
    'aset.write' => array('admin', 'ketua', 'operator', 'inventaris'),
    'aset.approve' => array('admin', 'ketua'),

    'kontak.read' => $staff,
    'kontak.write' => array('admin', 'ketua', 'sekretaris', 'operator'),

    'belanja.read' => array('admin', 'ketua', 'sekretaris', 'bendahara', 'inventaris', 'operator', 'auditor'),
    'belanja.write' => array('admin', 'ketua', 'sekretaris', 'bendahara', 'inventaris', 'operator'),
    'belanja.approve' => array('admin', 'ketua'),
    'belanja.receive' => array('admin', 'ketua', 'sekretaris', 'inventaris', 'operator'),
    'belanja.post' => array('admin', 'bendahara'),

    'bansos.read' => array('admin', 'ketua', 'sosial'),
    'bansos.write' => array('admin', 'ketua', 'sosial'),
    'bansos.approve' => array('admin', 'ketua'),

    'arsip.read' => array('admin', 'ketua', 'sekretaris', 'bendahara', 'auditor'),
    'arsip.write' => array('admin', 'ketua', 'sekretaris'),
    'arsip.confidential' => array('admin', 'ketua'),

    'serahterima.read' => array('admin', 'ketua', 'sekretaris', 'bendahara', 'auditor'),
    'serahterima.write' => array('admin', 'ketua', 'sekretaris'),
    'serahterima.finalize' => array('admin', 'ketua'),

    'iuran.report' => array('admin', 'ketua', 'bendahara', 'auditor'),
    'iuran.remind' => array('admin', 'ketua', 'bendahara'),
    'kas.reconcile' => array('admin', 'bendahara'),
    'kas.report' => array('admin', 'ketua', 'bendahara', 'auditor'),

    'akun.manage' => array('admin'),
    'langganan.manage' => array(),
    'audit.read' => array('admin', 'ketua', 'auditor'),
    'backup.run' => array('admin'),
    'pengaturan.history' => array('admin', 'ketua'),
    'pengaturan.fields' => array('admin', 'ketua'),
    'notifikasi.manage' => array('admin'),
    // Manajemen Fitur & Menu: hanya Administrator (BR-12, UAT-05).
    'fitur.manage' => array('admin'),
    // Tema & Tampilan: menerbitkan/reset tema global hanya Administrator (Admin Pusat juga diizinkan).
    'appearance.manage' => array('admin'),
    // Membuka identitas pelapor pengaduan anonim (untuk penanganan penyalahgunaan); selalu diaudit.
    'pengaduan.reveal' => array('admin', 'ketua'),
);
unset($staff);
