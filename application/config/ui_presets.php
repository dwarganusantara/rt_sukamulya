<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Preset Tema & Tampilan (spesifikasi: Spesifikasi_Pengaturan_Tema_RTRW_Digital.md).
 * Preset bersifat PERMANEN dan tidak pernah ditimpa oleh penyimpanan admin. 'original-orange' adalah snapshot
 * desain awal putih–navy–oranye: tanpa lapisan tema sama sekali, sehingga identik dengan tampilan asli.
 *  accent : warna aksi (tombol, menu aktif) — pengganti oranye pada seluruh CSS
 *  dark   : tema gelap (permukaan & teks netral dibalik)
 */
$config['ui_presets'] = array(
    'original-orange' => array('name' => 'Oranye Harmoni', 'desc' => 'Tema utama dengan nuansa oranye yang hangat, modern, dan mudah digunakan.', 'default' => TRUE,
        'colors' => array('primary' => '#F97316', 'secondary' => '#FFEDD5', 'accent' => '#F97316', 'background' => '#F8FAFC', 'surface' => '#FFFFFF', 'text' => '#102A56'), 'dark' => FALSE,
        'swatches' => array('#F97316', '#FDBA74', '#FFEDD5', '#102A56', '#E5E7EB')),
    'emerald' => array('name' => 'Hijau Emerald', 'desc' => 'Memberikan kesan segar, profesional, dan modern dengan nuansa hijau yang menenangkan.',
        'colors' => array('primary' => '#16A34A', 'secondary' => '#DCFCE7', 'accent' => '#16A34A', 'background' => '#F8FAFC', 'surface' => '#FFFFFF', 'text' => '#102A56'), 'dark' => FALSE,
        'swatches' => array('#047857', '#16A34A', '#BBF7D0', '#1E3A5F', '#DCFCE7')),
    'ocean' => array('name' => 'Biru Ocean', 'desc' => 'Memberikan kesan tenang, profesional, dan terpercaya dengan nuansa biru yang elegan.',
        'colors' => array('primary' => '#2563EB', 'secondary' => '#DBEAFE', 'accent' => '#2563EB', 'background' => '#F8FAFC', 'surface' => '#FFFFFF', 'text' => '#102A56'), 'dark' => FALSE,
        'swatches' => array('#1D4ED8', '#3B82F6', '#93C5FD', '#BFDBFE', '#1E293B')),
    'purple' => array('name' => 'Ungu Modern', 'desc' => 'Terlihat kreatif, dinamis, dan modern dengan warna ungu yang elegan.',
        'colors' => array('primary' => '#7C3AED', 'secondary' => '#EDE9FE', 'accent' => '#7C3AED', 'background' => '#FAF9FF', 'surface' => '#FFFFFF', 'text' => '#1E1B4B'), 'dark' => FALSE,
        'swatches' => array('#6D28D9', '#8B5CF6', '#C4B5FD', '#475569', '#CBD5E1')),
    'navy' => array('name' => 'Dark Navy', 'desc' => 'Memberikan kesan modern, elegan, dan fokus pada konten dengan tampilan gelap yang nyaman di mata.',
        'colors' => array('primary' => '#172554', 'secondary' => '#334155', 'accent' => '#3B82F6', 'background' => '#0B1220', 'surface' => '#111C33', 'text' => '#F8FAFC'), 'dark' => TRUE,
        'swatches' => array('#0B1220', '#1E3A8A', '#0EA5E9', '#64748B', '#94A3B8')),
    'teal' => array('name' => 'Teal Fresh', 'desc' => 'Memberikan kesan bersih, segar, dan ramah dengan warna teal yang modern.',
        'colors' => array('primary' => '#0D9488', 'secondary' => '#CCFBF1', 'accent' => '#0D9488', 'background' => '#F8FAFC', 'surface' => '#FFFFFF', 'text' => '#102A56'), 'dark' => FALSE,
        'swatches' => array('#0F766E', '#2DD4BF', '#99F6E4', '#CCFBF1', '#1E293B')),
);

/* Konfigurasi bawaan selain warna (bagian yang dipulihkan oleh "Reset ke Default"). */
$config['ui_default_config'] = array(
    'schemaVersion' => 1,
    'themeId' => 'original-orange',
    'sidebar' => array('variant' => 'standard'),           // standard | compact | icons
    'components' => array('radius' => 'medium', 'fontSize' => 'medium', 'density' => 'comfortable', 'inputHeight' => 'standard'), // radius: square|medium|round
    'header' => array('search' => TRUE, 'notifications' => TRUE, 'banner' => TRUE),
    // Widget dashboard (Akses Cepat tidak tersedia di dashboard default, sesuai spesifikasi).
    'dashboard' => array(
        'order' => array('hero', 'stats', 'stats_chart', 'dues', 'agenda', 'letters', 'operations'),
        'hidden' => array(),
        'stats_hidden' => array(),
    ),
);

$config['ui_widgets'] = array(
    'hero' => 'Banner Sambutan', 'stats' => 'Kartu Ringkasan', 'stats_chart' => 'Grafik Statistik Warga', 'dues' => 'Iuran Warga',
    'agenda' => 'Agenda Kegiatan', 'letters' => 'Pengajuan Surat Terbaru', 'operations' => 'Monitoring Operasional',
);
$config['ui_stat_cards'] = array('residents' => 'Total Warga', 'agenda' => 'Agenda Bulan Ini', 'announcements' => 'Pengumuman', 'dues' => 'Total Iuran Bulan Ini', 'cash' => 'Saldo Kas RT');
