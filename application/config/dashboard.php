<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Routes are server-owned; administrators may change presentation, never destinations.
$config['dashboard_icons']=array('person'=>'Profil','people'=>'Keluarga','doc'=>'Surat','clock'=>'Riwayat','wallet'=>'Dompet','chart'=>'Grafik','megaphone'=>'Pengumuman','calendar'=>'Agenda','image'=>'Galeri','shield'=>'Keamanan','alert'=>'Aspirasi','chat'=>'Chat','swap'=>'Mutasi','phone'=>'Telepon','gear'=>'Pengaturan','upload'=>'Unggah / OCR');
// Tema heksagon (spesifikasi dashboard warga). Lima tema lama tetap dikenali agar pengaturan tersimpan tidak rusak.
$config['dashboard_colors']=array('maroon'=>'Merah marun','mint'=>'Mint','orange'=>'Oranye','lavender'=>'Lavender','navy'=>'Biru tua','cream'=>'Krem','cyan'=>'Cyan','peach'=>'Jingga','sky'=>'Biru','rose'=>'Merah muda','lilac'=>'Ungu');
$config['dashboard_categories']=array('Akun','Kependudukan','Layanan Warga','Keuangan','Informasi','Bantuan');
$config['dashboard_images']=array('people'=>'portal-warga.png','doc'=>'portal-surat.png','wallet'=>'portal-iuran.png','megaphone'=>'portal-pengumuman.png','calendar'=>'portal-agenda.png','image'=>'portal-galeri.png','chat'=>'portal-chat.png','gear'=>'portal-setting.png','phone'=>'portal-phone.png');
// Array fields: route, label, icon, color, category, dashboard, default favorite, header.
// 12 menu utama sesuai spesifikasi (urutan = urutan tampil). Menu lain tersedia tetapi default tidak tampil.
$config['dashboard_registry']=array(
 'profile'=>array('warga/profil','Profil Saya','person','maroon','Akun',1,1,1),
 'family'=>array('warga/keluarga','Kartu Keluarga','people','mint','Kependudukan',1,0,0),
 'letters'=>array('warga/layanan','Pengajuan Surat','doc','orange','Layanan Warga',1,1,1),
 'history'=>array('warga/riwayat','Riwayat Surat','clock','lavender','Layanan Warga',1,0,0),
 'dues'=>array('warga/iuran','Iuran Warga','wallet','mint','Keuangan',1,1,1),
 'cash'=>array('keuangan','Laporan Kas','chart','navy','Keuangan',1,0,0),
 'announcements'=>array('pengumuman','Pengumuman','megaphone','cream','Informasi',1,0,0),
 'events'=>array('agenda','Agenda Kegiatan','calendar','cream','Informasi',1,0,1),
 'gallery'=>array('galeri','Galeri Kegiatan','image','lavender','Informasi',1,0,1),
 'patrol'=>array('warga/ronda','Jadwal Ronda','shield','cyan','Informasi',1,0,0),
 'complaints'=>array('warga/pengaduan','Pengaduan & Aspirasi','alert','cream','Layanan Warga',1,0,0),
 'chat'=>array('warga/chat','Chat Pengurus','chat','lavender','Layanan Warga',1,1,0),
 'ocr'=>array('warga/keluarga/scan','Upload KK / OCR','upload','cyan','Kependudukan',0,0,0),
 'mutation'=>array('warga/mutasi','Mutasi Data','swap','mint','Kependudukan',0,0,0),
 'contact_rt'=>array('kontak','Kontak Pengurus','phone','cream','Bantuan',0,0,1),
 'contacts'=>array('warga/kontak-penting','Kontak Penting','phone','cyan','Bantuan',0,0,0),
 'password'=>array('warga/password','Pengaturan Akun','gear','lavender','Akun',0,0,0),
 'home'=>array('warga','Beranda','person','sky','Akun',0,0,1)
);
// Navigasi header dashboard (urutan & label pendek sesuai spesifikasi). Tetap disaring Manajemen Fitur & Menu.
$config['dashboard_header']=array('home'=>'Beranda','profile'=>'Profil Saya','letters'=>'Layanan Surat','dues'=>'Iuran Warga','gallery'=>'Galeri','events'=>'Agenda','contact_rt'=>'Kontak Pengurus');
$config['dashboard_slogan_default']='Satu Warga, Satu Keluarga, Untuk Lingkungan yang Lebih Baik.';
$config['dashboard_quote_default']='Bersama Membangun Lingkungan yang Lebih Nyaman, Aman dan Guyub.';
// Ilustrasi bawaan banner & footer (teks tercetak sudah dihapus; identitas RT dirender HTML dari Pengaturan).
$config['dashboard_hero_default']='assets/img/dashboard/portal-hero-v2.jpg';
$config['dashboard_footer_default']='assets/img/dashboard/footer-banner-v2.jpg';
