<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Manajemen Fitur & Menu: gerbang server untuk semua route fitur (halaman, AJAX, ekspor, unduhan).
| Fitur nonaktif ditolak walau URL dibuka langsung (BR-09).
*/
$hook['post_controller_constructor'][] = array(
    'class'    => '',
    'function' => 'feature_gate_hook',
    'filename' => 'Feature_gate.php',
    'filepath' => 'libraries',
);

/*
| Hak Akses Menu per peran (Akun & Hak Akses): menu yang dimatikan untuk sebuah peran ditolak di server
| walau URL, POST, AJAX, atau ekspor dibuka langsung. Berjalan setelah gerbang fitur/langganan.
*/
$hook['post_controller_constructor'][] = array(
    'class'    => '',
    'function' => 'menu_access_hook',
    'filename' => 'Menu_access.php',
    'filepath' => 'libraries',
);
