<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kk_ocr
{
    public function scan($file)
    {
        $ci=get_instance();
        $ci->load->config('kk_scan');
        $ci->load->library('Kk_fields');
        $endpoint=$ci->config->item('kk_ocr_endpoint');
        $url=parse_url($endpoint);
        if ($endpoint && (!$url || !in_array($url['host'] ?? '',array('127.0.0.1','localhost','[::1]'),TRUE) || ($url['scheme'] ?? '')!=='http')) {
            throw new RuntimeException('Alamat worker OCR lokal tidak valid.');
        }
        if (!extension_loaded('curl')) throw new RuntimeException('Ekstensi cURL diperlukan untuk OCR lokal.');
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Pilih foto atau PDF KK yang valid.');
        if ($file['size'] > 10*1024*1024 || filesize($file['tmp_name']) > 10*1024*1024) throw new RuntimeException('Ukuran file maksimal 10 MB.');
        require_once __DIR__.'/Kk_file_types.php';
        $mime=Kk_file_types::detect($file['tmp_name']);
        if (!$endpoint) {
            try {
                $ci->load->library('Kk_local_engine');
                @set_time_limit(180);
                $result=$ci->kk_local_engine->scan($file['tmp_name'],$mime,$ci->config->item('kk_tesseract'),$ci->config->item('kk_pdftoppm'),$ci->config->item('kk_magick'));
                log_message('error', 'OCR RAW RESULT: '.print_r($result, TRUE));
                $data=$ci->kk_fields->normalize($result);
                if(!$data['jumlah_anggota']) throw new RuntimeException('Belum ada anggota terbaca. Pastikan seluruh tabel KK terlihat jelas atau isi manual.');
                return $data;
            } finally {if(is_uploaded_file($file['tmp_name'])) @unlink($file['tmp_name']);}
        }
        $ch=curl_init($endpoint);
        $body='';
        curl_setopt_array($ch,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>array('document'=>new CURLFile($file['tmp_name'],$mime,'document')),CURLOPT_HTTPHEADER=>array('Authorization: Bearer '.$ci->config->item('kk_ocr_token')),CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>175,CURLOPT_FOLLOWLOCATION=>FALSE,CURLOPT_PROXY=>'',CURLOPT_WRITEFUNCTION=>function($ch,$chunk) use (&$body) {if(strlen($body)+strlen($chunk)>1048576)return 0;$body.=$chunk;return strlen($chunk);}));
        try {
            $ok=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
            if (!$ok || $status!==200) throw new RuntimeException('Foto belum dapat dibaca. Coba ulang atau isi manual.');
            $result=json_decode($body,TRUE);
            if (!is_array($result) || !isset($result['keluarga'],$result['anggota'])) throw new RuntimeException('Hasil OCR tidak valid. Coba ulang atau isi manual.');
            $data=$ci->kk_fields->normalize($result);
            if (!$data['jumlah_anggota']) throw new RuntimeException('Belum ada anggota terbaca. Ambil foto lebih jelas atau isi manual.');
            return $data;
        } finally {
            curl_close($ch);
            // Original PHP upload never moves into webroot or persistent storage.
            if (is_uploaded_file($file['tmp_name'])) @unlink($file['tmp_name']);
        }
    }
}
