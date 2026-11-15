<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Satu sumber aturan format foto untuk unggahan gambar konten (banner, logo, pengumuman, galeri, pengurus, profil).
 * Hanya format yang dapat ditampilkan langsung oleh browser. HEIC/HEIF (iPhone) tidak didukung browser dan
 * server belum memiliki konverter gambar (GD/Imagick), jadi ditolak dengan pesan yang jelas.
 */

if (!function_exists('image_upload_types')) {
    /** Untuk CI Upload 'allowed_types'. */
    function image_upload_types() {return 'jpg|jpeg|jpe|jfif|png|webp|gif|bmp|avif';}
}

if (!function_exists('image_accept')) {
    /** Untuk atribut accept="" pada <input type="file">. */
    function image_accept() {return 'image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,.jpg,.jpeg,.jfif,.png,.webp,.gif,.bmp,.avif';}
}

if (!function_exists('image_mime_map')) {
    /** MIME (hasil finfo) → ekstensi simpan. */
    function image_mime_map()
    {
        return array('image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/x-png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
            'image/bmp' => 'bmp', 'image/x-ms-bmp' => 'bmp', 'image/x-bmp' => 'bmp', 'image/avif' => 'avif');
    }
}

if (!function_exists('image_formats_label')) {
    function image_formats_label() {return 'JPG, PNG, WebP, GIF, BMP, atau AVIF';}
}

if (!function_exists('image_upload_error')) {
    /** Pesan galat ramah untuk unggahan foto yang ditolak. */
    function image_upload_error($raw, $filename = '')
    {
        $ext = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
        if (in_array($ext, array('heic', 'heif'), TRUE)) return 'Foto HEIC/HEIF (iPhone) belum didukung. Ubah ke JPG terlebih dahulu (di iPhone: Pengaturan › Kamera › Format › Paling Kompatibel), lalu unggah ulang.';
        if (stripos((string) $raw, 'filetype') !== FALSE || stripos((string) $raw, 'type of file') !== FALSE) return 'Format file tidak didukung. Gunakan ' . image_formats_label() . '.';
        if (stripos((string) $raw, 'larger') !== FALSE || stripos((string) $raw, 'size') !== FALSE) return 'Ukuran file melebihi batas yang diizinkan.';
        return trim(strip_tags((string) $raw)) ?: 'Unggahan gagal. Coba ulangi.';
    }
}
