<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'libraries/Letter_definitions.php';

class Letter_model extends CI_Model
{
    public function catalog()
    {
        $catalog = array(
            'surat_pengantar'=>array('Surat Pengantar','Kependudukan','Untuk berbagai keperluan administrasi ke kelurahan atau instansi lainnya.','▤'),
            'domisili'=>array('Surat Keterangan Domisili','Kependudukan','Keterangan tempat tinggal warga RT 009/008.','⌂'),
            'kematian'=>array('Surat Keterangan Kematian','Keterangan','Laporan warga yang meninggal dunia.','✿'),
            'kelahiran'=>array('Surat Pengantar Kelahiran','Keterangan','Laporan kelahiran bayi di lingkungan RT.','●'),
            'pindah'=>array('Surat Pengantar Pindah Domisili','Kependudukan','Pengurusan perpindahan alamat domisili.','▣'),
            'warga_baru'=>array('Surat Pengantar Warga Baru','Kependudukan','Laporan warga yang baru menetap di lingkungan RT.','♟'),
            'ktp_kk'=>array('Surat Pengantar KTP / KK','Kependudukan','Pengurusan atau perubahan dokumen kependudukan.','▤'),
            'skck'=>array('Surat Pengantar SKCK','Keterangan','Pengantar untuk keperluan pengurusan SKCK.','▤'),
            'nikah'=>array('Surat Pengantar Nikah','Keterangan','Pengurusan administrasi pernikahan.','◇'),
            'usaha'=>array('Surat Keterangan Usaha','Keterangan','Pengantar untuk keperluan administrasi usaha warga.','▥'),
            'tidak_mampu'=>array('Surat Pengantar Tidak Mampu','Keterangan','Pengantar untuk pengajuan bantuan sosial atau pendidikan.','♟'),
            'ahli_waris'=>array('Surat Pengantar Ahli Waris','Keterangan','Pengantar proses administrasi ahli waris.','▤'),
            'kehilangan'=>array('Surat Pengantar Kehilangan','Keterangan','Pengantar untuk pengurusan dokumen yang hilang.','△'),
            'kegiatan'=>array('Surat Pengantar Kegiatan Warga','Kegiatan','Pemberitahuan kegiatan atau acara lingkungan.','▦'),
            'renovasi'=>array('Surat Pengantar Renovasi Rumah','Kegiatan','Pemberitahuan kegiatan renovasi rumah.','⌂'),
            'aduan'=>array('Aduan / Saran','Lainnya','Sampaikan keluhan atau saran untuk pengurus RT.','◉'),
            'lainnya'=>array('Keperluan Lainnya','Lainnya','Ajukan keperluan administrasi lainnya.','▤')
        );
        $rows = $this->db->order_by('sort_order')->order_by('title')->get('letter_types')->result_array();
        if (!$rows) return $catalog;
        $ordered = array();
        $defaults = Letter_definitions::templates();
        foreach ($rows as $row) {
            if (!$row['is_active']) continue;
            $row['body_template'] = trim((string)$row['body_template']) ?: ($defaults[$row['type_key']]??'');
            $ordered[$row['type_key']] = array($row['title'], $row['category'], $row['description'], $catalog[$row['type_key']][3] ?? '▤', $row['icon_path'], $row['body_template']);
        }
        return $ordered;
    }

    public function types()
    {
        return $this->db->order_by('sort_order')->order_by('title')->get('letter_types')->result_array();
    }

    public function fields($type = NULL)
    {
        $fields=Letter_definitions::fields();
        return $type === NULL ? $fields : ($fields[$type]??array());
    }
}
