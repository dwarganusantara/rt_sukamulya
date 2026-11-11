<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter_definitions
{
    public static function fields()
    {
        return array(
            'surat_pengantar'=>array(
                array('tujuan_instansi','Instansi tujuan','text'),array('jenis_urusan','Jenis urusan administrasi','text'),array('nomor_kk','Nomor Kartu Keluarga','text')),
            'domisili'=>array(
                array('domicile_since','Berdomisili sejak','date'),array('residence_status','Status tempat tinggal','text'),array('household_head','Nama kepala keluarga','text')),
            'kematian'=>array(
                array('deceased_name','Nama lengkap almarhum','text'),array('deceased_nik','NIK almarhum','text'),array('deceased_birth_place','Tempat lahir almarhum','text'),array('deceased_birth_date','Tanggal lahir almarhum','date'),array('deceased_gender','Jenis kelamin almarhum','text'),array('deceased_religion','Agama almarhum','text'),array('death_date','Tanggal wafat','date'),array('death_time','Waktu wafat','time'),array('death_place','Tempat wafat','text'),array('death_cause','Sebab wafat','textarea'),array('last_address','Alamat terakhir almarhum','text'),array('relationship','Hubungan pemohon dengan almarhum','text')),
            'kelahiran'=>array(
                array('baby_name','Nama bayi','text'),array('baby_gender','Jenis kelamin bayi','text'),array('baby_birth_place','Tempat kelahiran','text'),array('baby_birth_date','Tanggal kelahiran','date'),array('baby_birth_time','Waktu kelahiran','time'),array('child_number','Anak ke','number'),array('father_name','Nama ayah','text'),array('father_nik','NIK ayah','text'),array('mother_name','Nama ibu','text'),array('mother_nik','NIK ibu','text'),array('birth_assistant','Penolong kelahiran','text')),
            'pindah'=>array(
                array('move_origin','Alamat asal','textarea'),array('move_destination','Alamat tujuan','textarea'),array('move_date','Tanggal rencana pindah','date'),array('move_reason','Alasan pindah','textarea'),array('moving_people','Nama anggota keluarga yang ikut pindah','textarea')),
            'warga_baru'=>array(
                array('previous_address','Alamat asal sebelumnya','textarea'),array('arrival_date','Tanggal mulai tinggal','date'),array('new_household_head','Nama kepala keluarga','text'),array('arriving_people','Anggota keluarga yang ikut menetap','textarea')),
            'ktp_kk'=>array(
                array('document_service','Layanan KTP / KK yang dimohon','text'),array('current_kk_number','Nomor KK saat ini','text'),array('data_change','Data yang dibuat atau diubah','textarea')),
            'skck'=>array(
                array('police_destination','Kantor kepolisian tujuan','text'),array('skck_reason','Keperluan SKCK','textarea')),
            'nikah'=>array(
                array('partner_name','Nama calon pasangan','text'),array('partner_nik','NIK calon pasangan','text'),array('marriage_date','Rencana tanggal pernikahan','date'),array('marriage_place','Tempat rencana pernikahan','text')),
            'usaha'=>array(
                array('business_name','Nama usaha','text'),array('business_type','Bidang usaha','text'),array('business_address','Alamat tempat usaha','textarea'),array('business_since','Usaha berjalan sejak','date')),
            'tidak_mampu'=>array(
                array('income_range','Penghasilan keluarga per bulan','text'),array('dependents','Jumlah tanggungan keluarga','number'),array('assistance_purpose','Bantuan atau keperluan yang dimohon','textarea')),
            'ahli_waris'=>array(
                array('heir_deceased_name','Nama pewaris yang meninggal','text'),array('heir_death_date','Tanggal meninggal pewaris','date'),array('heir_deceased_address','Alamat terakhir pewaris','textarea'),array('heir_names','Nama para ahli waris','textarea'),array('heir_relationships','Hubungan para ahli waris','textarea')),
            'kehilangan'=>array(
                array('lost_item','Barang atau dokumen yang hilang','text'),array('lost_number','Nomor atau identitas dokumen','text'),array('loss_date','Tanggal kehilangan','date'),array('loss_place','Tempat kehilangan','text'),array('loss_chronology','Kronologi kehilangan','textarea')),
            'kegiatan'=>array(
                array('event_name','Nama kegiatan','text'),array('event_date','Tanggal kegiatan','date'),array('event_time','Waktu kegiatan','time'),array('event_location','Lokasi kegiatan','text'),array('event_organizer','Penanggung jawab kegiatan','text')),
            'renovasi'=>array(
                array('property_address','Alamat rumah yang direnovasi','textarea'),array('renovation_work','Jenis pekerjaan renovasi','textarea'),array('renovation_start','Rencana mulai','date'),array('renovation_end','Rencana selesai','date')),
            'aduan'=>array(
                array('issue_category','Kategori aduan','text'),array('incident_location','Lokasi kejadian','text'),array('incident_date','Tanggal kejadian','date'),array('expected_followup','Tindak lanjut yang diharapkan','textarea')),
            'lainnya'=>array(
                array('other_subject','Pokok permohonan','text'),array('other_destination','Instansi atau pihak tujuan','text'),array('other_details','Rincian permohonan','textarea'))
        );
    }

    public static function templates()
    {
        $intro="Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\n";
        $closing="\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.";
        return array(
            'surat_pengantar'=>$intro."Yang bersangkutan memohon surat pengantar kepada {{tujuan_instansi}} untuk urusan {{jenis_urusan}}. Nomor Kartu Keluarga: {{nomor_kk}}.\nKeperluan: {{keperluan_surat}}.".$closing,
            'domisili'=>$intro."Berdasarkan keterangan yang tersedia pada pengurus RT, yang bersangkutan berdomisili di alamat tersebut sejak {{domicile_since}}. Status tempat tinggal: {{residence_status}}. Kepala keluarga: {{household_head}}.\nSurat keterangan domisili ini dimohon untuk {{keperluan_surat}}.".$closing,
            'kematian'=>"Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama Almarhum/Almarhumah : {{deceased_name}}\nNIK : {{deceased_nik}}\nTempat, Tanggal Lahir : {{deceased_birth_place}}, {{deceased_birth_date}}\nJenis Kelamin : {{deceased_gender}}\nAgama : {{deceased_religion}}\nAlamat Terakhir : {{last_address}}\n\nMenurut laporan dari {{nama_warga}}, hubungan dengan almarhum/almarhumah: {{relationship}}, yang bersangkutan meninggal dunia pada {{death_date}} pukul {{death_time}} di {{death_place}}. Keterangan sebab meninggal: {{death_cause}}.\nSurat ini dimohon untuk {{keperluan_surat}}.".$closing,
            'kelahiran'=>"Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa berdasarkan laporan warga telah terjadi kelahiran dengan data:\nNama Bayi : {{baby_name}}\nJenis Kelamin : {{baby_gender}}\nTempat, Tanggal Lahir : {{baby_birth_place}}, {{baby_birth_date}}\nWaktu Lahir : {{baby_birth_time}}\nAnak Ke : {{child_number}}\nNama Ayah / NIK : {{father_name}} / {{father_nik}}\nNama Ibu / NIK : {{mother_name}} / {{mother_nik}}\nPenolong Kelahiran : {{birth_assistant}}\n\nPelapor: {{nama_warga}}, NIK {{nik_warga}}, alamat {{alamat_domisili}}. Surat ini dimohon untuk {{keperluan_surat}}.".$closing,
            'pindah'=>$intro."Yang bersangkutan menyampaikan rencana pindah dari {{move_origin}} ke {{move_destination}} pada {{move_date}}. Alasan pindah: {{move_reason}}. Anggota keluarga yang ikut pindah: {{moving_people}}.\nSurat pengantar ini dimohon untuk {{keperluan_surat}}.".$closing,
            'warga_baru'=>$intro."Yang bersangkutan melaporkan telah mulai menetap di lingkungan ini sejak {{arrival_date}}, sebelumnya beralamat di {{previous_address}}. Kepala keluarga: {{new_household_head}}. Anggota keluarga yang ikut menetap: {{arriving_people}}.".$closing,
            'ktp_kk'=>$intro."Yang bersangkutan memohon pengantar layanan kependudukan: {{document_service}}. Nomor KK saat ini: {{current_kk_number}}. Data yang dibuat atau diubah: {{data_change}}. Keperluan: {{keperluan_surat}}.".$closing,
            'skck'=>$intro."Yang bersangkutan memohon pengantar untuk pengurusan Surat Keterangan Catatan Kepolisian pada {{police_destination}} dengan keperluan {{skck_reason}}. Surat ini merupakan pengantar dari pengurus RT setempat.".$closing,
            'nikah'=>$intro."Yang bersangkutan menyampaikan rencana pernikahan dengan {{partner_name}}, NIK {{partner_nik}}, pada {{marriage_date}} di {{marriage_place}}. Surat pengantar ini dimohon untuk melengkapi administrasi pernikahan.".$closing,
            'usaha'=>$intro."Berdasarkan keterangan yang disampaikan, yang bersangkutan menjalankan usaha {{business_name}} di bidang {{business_type}}, beralamat di {{business_address}}, sejak {{business_since}}. Surat ini dimohon untuk {{keperluan_surat}}.".$closing,
            'tidak_mampu'=>$intro."Berdasarkan keterangan yang disampaikan, penghasilan keluarga per bulan adalah {{income_range}} dengan {{dependents}} tanggungan. Yang bersangkutan memohon keterangan untuk {{assistance_purpose}}. Surat ini diberikan untuk pertimbangan pihak yang berwenang.".$closing,
            'ahli_waris'=>"Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa menurut laporan warga, {{heir_deceased_name}} yang terakhir beralamat di {{heir_deceased_address}} meninggal dunia pada {{heir_death_date}}. Nama pihak yang dilaporkan sebagai ahli waris: {{heir_names}}. Hubungan para pihak: {{heir_relationships}}.\nPelapor: {{nama_warga}}, NIK {{nik_warga}}. Keterangan ini disampaikan untuk keperluan {{keperluan_surat}} dan tetap memerlukan pemeriksaan dokumen oleh instansi berwenang.".$closing,
            'kehilangan'=>$intro."Yang bersangkutan melaporkan kehilangan {{lost_item}}, nomor atau identitas {{lost_number}}, pada {{loss_date}} di {{loss_place}}. Kronologi menurut pelapor: {{loss_chronology}}. Surat ini dimohon sebagai pengantar pelaporan kepada pihak berwenang.".$closing,
            'kegiatan'=>"Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa warga merencanakan kegiatan {{event_name}} pada {{event_date}} pukul {{event_time}} di {{event_location}}. Penanggung jawab kegiatan: {{event_organizer}}. Pemohon: {{nama_warga}}, NIK {{nik_warga}}. Kegiatan ini disampaikan kepada pengurus RT untuk keperluan {{keperluan_surat}}.".$closing,
            'renovasi'=>$intro."Yang bersangkutan menyampaikan rencana renovasi rumah di {{property_address}} berupa {{renovation_work}}, mulai {{renovation_start}} sampai {{renovation_end}}. Surat pengantar ini dibuat untuk pemberitahuan kepada lingkungan setempat.".$closing,
            'aduan'=>"Yang bertanda tangan di bawah ini:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nAlamat : {{alamat_domisili}}\n\nMenyampaikan aduan kategori {{issue_category}} mengenai kejadian di {{incident_location}} pada {{incident_date}}. Uraian: {{keterangan}}. Tindak lanjut yang diharapkan: {{expected_followup}}.\nDemikian aduan ini disampaikan kepada pengurus RT untuk ditindaklanjuti.",
            'lainnya'=>$intro."Yang bersangkutan mengajukan {{other_subject}} kepada {{other_destination}} dengan rincian: {{other_details}}. Keperluan surat: {{keperluan_surat}}.".$closing
        );
    }
}
