<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Notifikasi email (opsional). Notifikasi di portal warga selalu aktif dan tidak bergantung pada file ini.
 * Email baru terkirim bila: (1) "Kirim salinan notifikasi lewat email" diaktifkan di Pengaturan → Tema & Fitur,
 * (2) warga mengisi email dan mencentang persetujuan di Edit Profil, (3) smtp_host di bawah diisi.
 * Selama smtp_host kosong, email tetap tersimpan di antrean dan dikirim setelah dikonfigurasi.
 *
 * Proses antrean: menu Pengaturan Sistem → Antrean Email, atau terjadwal (cron / Task Scheduler, mis. tiap 10 menit):
 *   php /path/ke/sukamulya/index.php sistem cli_notify
 *
 * Simpan password SMTP sebagai environment variable (SMTP_PASS) di server, jangan di-commit ke repositori.
 */
$config['smtp_host'] = getenv('SMTP_HOST') ?: '';
$config['smtp_port'] = (int) (getenv('SMTP_PORT') ?: 587);
$config['smtp_user'] = getenv('SMTP_USER') ?: '';
$config['smtp_pass'] = getenv('SMTP_PASS') ?: '';
$config['smtp_crypto'] = getenv('SMTP_CRYPTO') ?: 'tls'; // tls | ssl | ''
$config['from_email'] = getenv('MAIL_FROM') ?: '';
$config['from_name'] = 'RT 009/RW 008 Suka Mulya IV';
$config['max_attempts'] = 5;
