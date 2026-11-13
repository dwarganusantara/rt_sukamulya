<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Field-only boundary shared by OCR, review and submission. Never infer identity. */
class Kk_fields
{
    public $family_fields = array('nomor_kk','alamat','rt','rw','kelurahan','kecamatan','kota','provinsi','kode_pos');
    public $member_fields = array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah','hubungan_keluarga');
    /** Status Hubungan Dalam Keluarga (SHDK) sesuai daftar Dukcapil pada KK. */
    public $relationships = array('Kepala Keluarga','Suami','Istri','Anak','Menantu','Cucu','Orang Tua','Mertua','Famili Lain','Pembantu','Lainnya');

    /** Map OCR/typed text to one SHDK option; '' when it cannot be matched safely. */
    public function relationship($value)
    {
        $v=mb_strtoupper(trim(preg_replace('/[^\p{L}\s]+/u',' ',(string)$value)),'UTF-8');
        $v=preg_replace('/\s+/',' ',$v);
        if($v==='') return '';
        foreach($this->relationships as $option) if($v===mb_strtoupper($option,'UTF-8')) return $option;
        // Tolerate OCR joins/splits such as "KEPALAKELUARGA" or "ORANGTUA"; order matters (MENANTU before ANAK).
        $compact=str_replace(' ','',$v);
        $rules=array('KEPALA'=>'Kepala Keluarga','ISTRI'=>'Istri','SUAMI'=>'Suami','MENANTU'=>'Menantu','CUCU'=>'Cucu','MERTUA'=>'Mertua','ORANGTUA'=>'Orang Tua','FAMILI'=>'Famili Lain','PEMBANTU'=>'Pembantu','ANAK'=>'Anak','LAINNYA'=>'Lainnya');
        foreach($rules as $needle=>$option) if(strpos($compact,$needle)!==FALSE) return $option;
        return '';
    }

    
    public function normalize($input)
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException('Data KK tidak valid.');
        }

        $out = array(
            'keluarga' => array(),
            'anggota' => array()
        );

        // Alias untuk membaca berbagai format hasil OCR.
        $family_aliases = array(
            'nomor_kk' => array('nomor_kk', 'no_kk', 'NIK_KK'),
            'alamat' => array('alamat', 'address'),
            'rt' => array('rt', 'RT'),
            'rw' => array('rw', 'RW'),
            'kelurahan' => array('kelurahan', 'desa', 'kelurahan_desa'),
            'kecamatan' => array('kecamatan'),
            'kota' => array('kota', 'kabupaten', 'kabupaten_kota'),
            'provinsi' => array('provinsi'),
            'kode_pos' => array('kode_pos', 'kodepos')
        );

        $member_aliases = array(
            'nama_lengkap' => array('nama_lengkap', 'nama', 'NAMA'),
            'nik' => array('nik', 'NIK'),
            'jenis_kelamin' => array('jenis_kelamin', 'jk', 'jenis_kelamin'),
            'tempat_lahir' => array('tempat_lahir', 'tempat'),
            'tanggal_lahir' => array('tanggal_lahir', 'tgl_lahir', 'tanggal'),
            'agama' => array('agama'),
            'pendidikan' => array('pendidikan', 'pendidikan_terakhir'),
            'pekerjaan' => array('pekerjaan'),
            'golongan_darah' => array('golongan_darah', 'gol_darah'),
            'hubungan_keluarga' => array('hubungan_keluarga', 'hubungan', 'shdk', 'status_hubungan')
        );

        // Normalisasi data keluarga.
        $family = $input['keluarga'] ?? $input['family'] ?? array();

        if (!is_array($family)) {
            $family = array();
        }

        foreach ($this->family_fields as $key) {
            $value = '';

            foreach ($family_aliases[$key] ?? array($key) as $alias) {
                if (isset($family[$alias]) && is_scalar($family[$alias])) {
                    $value = $family[$alias];
                    break;
                }

                // Mendukung OCR yang meletakkan field keluarga di root JSON.
                if (isset($input[$alias]) && is_scalar($input[$alias])) {
                    $value = $input[$alias];
                    break;
                }
            }

            $out['keluarga'][$key] = $this->clean($value);
        }

        $out['keluarga']['fields_need_review'] = array();

        if (!preg_match('/^\d{16}$/D', $out['keluarga']['nomor_kk'])) {
            $out['keluarga']['nomor_kk'] = '';
            $out['keluarga']['fields_need_review'][] = 'nomor_kk';
        }

        foreach (array('rt', 'rw') as $key) {
            if (
                $out['keluarga'][$key] !== '' &&
                !preg_match('/^\d{1,3}$/D', $out['keluarga'][$key])
            ) {
                $out['keluarga'][$key] = '';
                $out['keluarga']['fields_need_review'][] = $key;
            }
        }

        // Membaca daftar anggota.
        $rows = $input['anggota']
            ?? $input['members']
            ?? $input['data_anggota']
            ?? array();

        // Jika OCR hanya menghasilkan satu objek anggota.
        if (isset($rows['nama']) || isset($rows['nama_lengkap']) || isset($rows['nik'])) {
            $rows = array($rows);
        }

        if (!is_array($rows) || count($rows) > 100) {
            throw new InvalidArgumentException(
                'Daftar anggota tidak valid (maksimal 100).'
            );
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $m = array();

            foreach ($this->member_fields as $key) {
                $value = '';

                foreach ($member_aliases[$key] ?? array($key) as $alias) {
                    if (isset($row[$alias]) && is_scalar($row[$alias])) {
                        $value = $row[$alias];
                        break;
                    }
                }

                $m[$key] = $this->clean($value);
            }

            // Abaikan baris kosong.
            if ($m['nama_lengkap'] === '' && $m['nik'] === '') {
                continue;
            }

            $review = array();

            // Validasi NIK.
            if (!preg_match('/^\d{16}$/D', $m['nik'])) {
                $m['nik'] = '';
                $review[] = 'nik';
            }

            // Normalisasi jenis kelamin dan hubungan keluarga.
            $m['jenis_kelamin'] = $this->gender($m['jenis_kelamin']);

            $m['hubungan_keluarga'] = $this->relationship(
                $m['hubungan_keluarga']
            );

            // Normalisasi tanggal lahir.
            if ($m['tanggal_lahir'] !== '') {
                $value = str_replace('/', '-', $m['tanggal_lahir']);

                $format = preg_match('/^\d{4}-/', $value)
                    ? '!Y-m-d'
                    : '!d-m-Y';

                $date = DateTime::createFromFormat($format, $value);

                $m['tanggal_lahir'] = (
                    $date &&
                    $date->format(substr($format, 1)) === $value
                ) ? $date->format('Y-m-d') : '';
            }

            // Tandai field yang perlu diperiksa pengguna.
            foreach ($this->member_fields as $key) {
                if ($m[$key] === '') {
                    $review[] = $key;
                }
            }

            $existing_review = isset($row['fields_need_review'])
                && is_array($row['fields_need_review'])
                ? array_intersect(
                    $this->member_fields,
                    $row['fields_need_review']
                )
                : array();

            $m['fields_need_review'] = array_values(
                array_unique(array_merge($review, $existing_review))
            );

            $out['anggota'][] = $m;
        }

        $out['jumlah_anggota'] = count($out['anggota']);

        return $out;
    }


    public function gender($value)
    {
        // OCR can split a single printed word at spaces or table separators.
        // Normalize separators only; do not infer from name, NIK or relationship.
        $value=mb_strtoupper(trim((string)$value), 'UTF-8');
        $value=trim($value," \t\n\r\0\x0B.,:;|()[]{}");
        $value=preg_replace('/[\s\p{Pd}|_]+/u','',$value);
        if(in_array($value,array('L','LAKILAKI'),TRUE))return 'L';
        if(in_array($value,array('P','PEREMPUAN'),TRUE))return 'P';
        // Salah baca OCR umum pada SATU kata yang tercetak di sel Jenis Kelamin (I/l/1 tertukar, satu-dua huruf meleset).
        // Seluruh isi sel harus mendekati satu kata utuh; teks campuran (mis. "PEREMPUAN ISLAM") atau
        // karakter tidak pasti ("PEREM?UAN") tetap dikosongkan untuk diperiksa warga.
        $v=strtr($value,array('1'=>'I','!'=>'I','4'=>'A'));
        if($v==='' || preg_match('/[^A-Z]/',$v))return '';
        $male=levenshtein($v,'LAKILAKI');$female=levenshtein($v,'PEREMPUAN');
        if($male<=2 && $male<$female)return 'L';
        if($female<=2 && $female<$male)return 'P';
        return '';
    }

    private function clean($value)
    {
        if (!is_scalar($value)) return '';
        $value=trim(preg_replace('/\s+/u',' ',strip_tags((string)$value)));
        return in_array($value,array('-','--','—'),TRUE) ? '' : mb_substr($value,0,255);
    }

    public function validate($data)
    {
        $errors=array(); $seen=array(); $heads=0;
        if (!preg_match('/^\d{16}$/D',$data['keluarga']['nomor_kk'])) $errors[]='Nomor KK wajib 16 digit.';
        if ($data['keluarga']['alamat']==='') $errors[]='Alamat wajib diisi.';
        if (!$data['anggota']) $errors[]='Tambahkan minimal satu anggota.';
        foreach ($data['anggota'] as $i=>$m) {
            $n=$i+1;
            if ($m['nama_lengkap']==='' || !preg_match('/^\d{16}$/D',$m['nik']) || !in_array($m['jenis_kelamin'],array('L','P'),TRUE) || $m['hubungan_keluarga']==='') $errors[]="Lengkapi nama, NIK 16 digit, jenis kelamin dan hubungan anggota $n.";
            if ($m['nik']!=='' && isset($seen[$m['nik']])) $errors[]="NIK anggota $n duplikat dalam pengajuan.";
            $seen[$m['nik']]=TRUE;
            if ($m['hubungan_keluarga']!=='' && !in_array($m['hubungan_keluarga'],$this->relationships,TRUE)) $errors[]="Hubungan keluarga anggota $n tidak valid.";
            if (strtoupper($m['hubungan_keluarga'])==='KEPALA KELUARGA') $heads++;
        }
        if ($heads!==1) $errors[]='Harus ada tepat satu Kepala Keluarga.';
        return $errors;
    }
}
