<?php
// Explicit demo offers requested by the owner; never seed licences or tenant statistics.
return array(
    'basic'=>array('name'=>'Gratis (Basic)','price'=>'0','duration_days'=>0,'theme'=>'green','sort_order'=>0,
        'description'=>'Fitur dasar portal warga. Data contoh paket; akses mengikuti pengaturan dan hak pengguna.',
        'features'=>array('letters','announcements','events'),
        'benefits'=>array('Dashboard','Data Warga')),
    'pro'=>array('name'=>'Pro','price'=>'99000','duration_days'=>365,'theme'=>'orange','sort_order'=>1,
        'description'=>'Semua fitur Basic, ditambah:',
        'features'=>array('letters','announcements','events','family_card','resident_profile','resident_mutation','social_aid','cash_report'),
        'benefits'=>array('Laporan & Ekspor sesuai hak akses')),
    'enterprise'=>array('name'=>'Enterprise','price'=>'199000','duration_days'=>365,'theme'=>'violet','sort_order'=>2,
        'description'=>'Semua fitur Pro, ditambah fitur operasional lingkungan:',
        'features'=>array('letters','announcements','events','family_card','resident_profile','resident_mutation','social_aid','cash_report','complaints','live_chat','dues','dues_arrears','cash_reconciliation','gallery','contacts','patrol','work_program','activity_notes','inventory','purchasing','documents'),
        'benefits'=>array('Manajemen Pengurus sesuai hak akses','Audit Aktivitas','Backup Data manual','Prioritas Dukungan — contoh manfaat paket'))
);
