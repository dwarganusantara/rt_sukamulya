<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penyimpanan berkas privat di application/storage (diblokir web server oleh
 * application/.htaccess). Nama acak, validasi MIME dari isi file, checksum SHA-256.
 * Unduhan hanya melalui controller yang sudah memeriksa izin.
 */
class Private_storage
{
    private $base;
    private $types = array(
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/msword' => 'doc', 'application/vnd.ms-excel' => 'xls', 'text/plain' => 'txt', 'text/csv' => 'csv',
    );

    public function __construct()
    {
        $this->base = APPPATH . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($this->base)) {
            @mkdir($this->base, 0750, TRUE);
            @file_put_contents($this->base . '.htaccess', "<IfModule authz_core_module>\n    Require all denied\n</IfModule>\n<IfModule !authz_core_module>\n    Deny from all\n</IfModule>\n");
            @file_put_contents($this->base . 'index.html', '');
        }
    }

    public function dir($folder)
    {
        $folder = preg_replace('/[^a-z0-9_-]/', '', (string) $folder);
        $dir = $this->base . $folder . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {@mkdir($dir, 0750, TRUE); @file_put_contents($dir . 'index.html', '');}
        return $dir;
    }

    /**
     * Simpan upload. $allowed = daftar ekstensi yang diterima.
     * @return array|NULL [stored_name, original_name, mime, size, checksum]
     */
    public function store($field, $folder, &$error, $allowed = array('jpg', 'png', 'webp', 'pdf'), $max_bytes = 5242880)
    {
        $file = $_FILES[$field] ?? NULL;
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return NULL;
        if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {$error = 'Berkas gagal diunggah.'; return NULL;}
        if ((int) $file['size'] < 1 || (int) $file['size'] > $max_bytes) {$error = 'Ukuran berkas maksimal ' . round($max_bytes / 1048576) . ' MB.'; return NULL;}
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ext = $this->types[$mime] ?? '';
        if ($ext === '' || !in_array($ext, $allowed, TRUE)) {$error = 'Jenis berkas tidak diizinkan. Gunakan: ' . strtoupper(implode(', ', $allowed)) . '.'; return NULL;}
        if (in_array($ext, array('jpg', 'png', 'webp'), TRUE) && !@getimagesize($file['tmp_name'])) {$error = 'Berkas gambar tidak valid.'; return NULL;}
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = $this->dir($folder) . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) {$error = 'Berkas gagal disimpan.'; return NULL;}
        return array('stored_name' => $name, 'original_name' => mb_substr(basename((string) $file['name']), 0, 190), 'mime' => $mime,
            'size' => (int) filesize($target), 'checksum' => hash_file('sha256', $target));
    }

    public function path($folder, $name)
    {
        $name = basename((string) $name);
        if (!preg_match('/^[a-f0-9]{32}\.[a-z]{3,4}$/', $name) && !preg_match('/^[a-z0-9_.-]+$/i', $name)) return NULL;
        $path = $this->dir($folder) . $name;
        return is_file($path) ? $path : NULL;
    }

    public function delete($folder, $name)
    {
        $path = $this->path($folder, $name);
        if ($path) @unlink($path);
    }

    /** Kirim berkas ke browser. Pemanggil WAJIB sudah memeriksa izin. */
    public function send($folder, $name, $download_name = '', $inline = TRUE)
    {
        $path = $this->path($folder, $name);
        if (!$path) show_404();
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        $download_name = preg_replace('/[^\w .()-]/u', '_', $download_name ?: basename($path));
        $inline = $inline && in_array($mime, array('image/jpeg', 'image/png', 'image/webp', 'application/pdf'), TRUE);
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $download_name . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($path);
        exit;
    }
}
