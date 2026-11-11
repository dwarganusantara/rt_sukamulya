<?php
$title='Pengajuan Surat | '.($settings['site_name']??'Sukamulya');
$citizen_dashboard=TRUE;
$this->load->view('site/header',compact('title','settings','menu','citizen_dashboard'));
$this->load->helper('family_ui');
$selected=$selected ?: array_key_first($catalog);
$values=$draft_values??array();
$raw=function($field) use($values) { return (string)($this->input->method(TRUE)==='POST' ? $this->input->post($field,TRUE) : ($values[$field]??'')); };
$old=function($field) use($raw) { return html_escape($raw($field)); };
$death=$selected==='kematian';
$previewTemplates=array();foreach($catalog as $key=>$letter)$previewTemplates[$key]=trim((string)($letter[5]??''));
$previewNow=new DateTime('now',new DateTimeZone('Asia/Jakarta'));
$previewMonths=array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
$previewDate=$previewNow->format('d').' '.$previewMonths[(int)$previewNow->format('n')].' '.$previewNow->format('Y');
$previewMaster=array('nama_ketua_rt'=>$settings['letter_signer_name']??'','jabatan_ketua_rt'=>$settings['letter_signer_title']??'Ketua RT 009 / RW 008','wilayah_rt'=>trim($settings['letter_environment']??''),'nomor_rt'=>'009','nomor_rw'=>'008','kota_penerbitan'=>$settings['letter_region']??'','tanggal_surat'=>$previewDate,'nomor_surat'=>'—');
$members=$family_members??array();
$has_kk=!empty($members);
$head_name=''; foreach($members as $m) if(strcasecmp($m['hubungan'],'Kepala Keluarga')===0){$head_name=$m['nama'];break;}
// Draft / re-posted form: use KK mode only when the saved NIK belongs to a KK member.
$saved_nik=$raw('applicant_nik'); $saved_name=$raw('applicant_name');
$initial_nik='';
if($has_kk){
 if($saved_nik!==''){foreach($members as $m) if($m['nik']===$saved_nik) $initial_nik=$m['nik'];}
 elseif($saved_name==='') $initial_nik=$family_self??'';
}
$source=($has_kk && ($initial_nik!=='' || ($saved_name==='' && $saved_nik===''))) ? 'kk' : 'manual';
$religions=array('Islam','Kristen (Protestan)','Katolik','Hindu','Buddha','Khonghucu');
$genders=array('Laki-laki','Perempuan');
/*
 * UI hints per type-specific field (stored values stay plain text):
 *  options  -> dropdown, kk -> filled from verified KK, person -> pick a KK member and fill related fields,
 *  members  -> "Isi dari anggota KK" button, suggest -> datalist of common answers.
 */
$person_map=array(
 'deceased_name'=>array('nik'=>'deceased_nik','tempat_lahir'=>'deceased_birth_place','tanggal_lahir'=>'deceased_birth_date','jk'=>'deceased_gender','agama'=>'deceased_religion','address'=>'last_address'),
 'father_name'=>array('nik'=>'father_nik'),
 'mother_name'=>array('nik'=>'mother_nik'),
 'heir_deceased_name'=>array('address'=>'heir_deceased_address'),
);
$field_ui=array(
 'residence_status'=>array('options'=>array('Milik Sendiri','Milik Orang Tua / Keluarga','Sewa / Kontrak','Kos','Menumpang','Rumah Dinas','Lainnya')),
 'household_head'=>array('kk'=>'head','suggest'=>'people'),'new_household_head'=>array('kk'=>'head','suggest'=>'people'),
 'nomor_kk'=>array('kk'=>'kk_number','numeric'=>TRUE),'current_kk_number'=>array('kk'=>'kk_number','numeric'=>TRUE),
 'move_origin'=>array('kk'=>'address'),
 'deceased_gender'=>array('options'=>$genders),'baby_gender'=>array('options'=>$genders),
 'deceased_religion'=>array('options'=>$religions),
 'relationship'=>array('suggest'=>'relations'),
 'moving_people'=>array('members'=>TRUE),'arriving_people'=>array('members'=>TRUE),'heir_names'=>array('members'=>TRUE),
 'issue_category'=>array('options'=>array('Keamanan & Ketertiban','Kebersihan & Lingkungan','Fasilitas / Infrastruktur','Sosial Kemasyarakatan','Pelayanan Pengurus','Saran / Masukan','Lainnya')),
 'document_service'=>array('options'=>array('Pembuatan KTP-el baru','Perubahan data KTP-el','Penggantian KTP-el hilang/rusak','Pembuatan KK baru','Perubahan data KK','Penambahan anggota KK','Pengurangan anggota KK','Lainnya')),
 'income_range'=>array('options'=>array('Kurang dari Rp500.000','Rp500.000 – Rp1.000.000','Rp1.000.000 – Rp2.000.000','Rp2.000.000 – Rp3.000.000','Lebih dari Rp3.000.000')),
 'birth_assistant'=>array('options'=>array('Dokter','Bidan','Perawat','Dukun Beranak','Lainnya')),
);
foreach($person_map as $f=>$map) $field_ui[$f]=($field_ui[$f]??array())+array('person'=>$map,'suggest'=>'people');
$field_icon=function($field,$type){ if($type==='date')return 'calendar'; if($type==='time')return 'clock'; if(preg_match('/_nik$|kk_number|nomor_kk/',$field))return 'card'; if(preg_match('/address|origin|destination|location|place|alamat/',$field))return 'pin'; if(preg_match('/name|head|people|heir_names|organizer|partner/',$field))return 'person'; if(preg_match('/gender/',$field))return 'people'; if(preg_match('/religion/',$field))return 'heart'; if($type==='number')return 'hash'; return 'file'; };
$steps=array(array('Isi Formulir','Data warga dan keperluan'),array('Upload Dokumen','Lampirkan dokumen pendukung'),array('Konfirmasi','Periksa kembali data'),array('Selesai','Menunggu persetujuan'));
$letter_name=$catalog[$selected][0]??'Surat Warga';
?>
<link rel="stylesheet" href="<?= base_url('assets/letter-request.css') ?>?v=<?= filemtime(FCPATH.'assets/letter-request.css') ?>">
<main class="lrq" id="lrq">
<form method="post" enctype="multipart/form-data" id="letter-request-form" novalidate>
<header class="lrq-hero">
 <img class="lrq-hero-art" src="<?= base_url('assets/mockup/citizen-services-hero.png') ?>" alt="">
 <div class="lrq-paper" aria-hidden="true"><span class="lrq-paper-name" id="lrq-paper-name"><?= html_escape(mb_strtoupper($letter_name)) ?></span><span class="lrq-paper-lines"><i></i><i></i><i></i><i></i></span><b><?= family_icon('check') ?></b></div>
 <div class="lrq-wrap lrq-hero-inner">
  <div class="lrq-hero-copy">
   <span class="lrq-hero-badge" aria-hidden="true"><?= family_icon('file') ?></span>
   <div><h1>Pengajuan <span id="lrq-title-name"><?= html_escape($letter_name) ?></span></h1>
   <p>Ajukan surat dengan mudah, pilih data dari Kartu Keluarga (KK) atau isi manual sesuai kebutuhan.</p></div>
  </div>
  <nav class="lrq-steps" aria-label="Tahapan pengajuan">
   <a class="lrq-back" href="<?= site_url('warga/layanan') ?>"><?= family_icon('back') ?>Kembali</a>
   <ol>
   <?php foreach($steps as $i=>$s): ?><li data-step-tab="<?= $i+1 ?>" class="<?= $i===0?'is-current':'' ?>"><button type="button" <?= $i===3?'disabled':'' ?> data-goto="<?= $i+1 ?>"><b><?= $i+1 ?></b><span><strong><?= $s[0] ?></strong><small><?= $s[1] ?></small></span></button></li><?php endforeach; ?>
   </ol>
  </nav>
 </div>
</header>

<div class="lrq-wrap">
<?php if($error): ?><div class="lrq-error" role="alert"><?= family_icon('info') ?><span><?= html_escape($error) ?></span></div><?php endif; ?>
<div class="lrq-grid">
 <section class="lrq-card lrq-main">
  <!-- STEP 1: data -->
  <div class="lrq-step" data-step="1">
   <header class="lrq-head"><span class="lrq-head-ic"><?= family_icon('file') ?></span><div><h2>Data untuk Surat</h2><p>Pilih sumber data pemohon, lalu lengkapi isian sesuai kebutuhan.</p></div></header>

   <label class="lrq-label" for="request-type">Jenis Surat <b>*</b></label>
   <div class="lrq-input"><?= family_icon('file') ?><select name="type" id="request-type" required data-label="Jenis surat"><?php foreach($catalog as $key=>$letter): ?><option value="<?= html_escape($key) ?>" <?= $selected===$key?'selected':'' ?>><?= html_escape($letter[0]) ?></option><?php endforeach; ?></select></div>

   <p class="lrq-label">Sumber Data Pemohon</p>
   <div class="lrq-source" role="radiogroup" aria-label="Sumber data pemohon">
    <label class="lrq-source-card <?= $has_kk?'':'is-disabled' ?>"><input type="radio" name="data_source" value="kk" <?= $source==='kk'?'checked':'' ?> <?= $has_kk?'':'disabled' ?>><span class="lrq-radio" aria-hidden="true"></span><span class="lrq-source-ic"><?= family_icon('people') ?></span><span><strong>Pilih dari Data KK</strong><small><?= $has_kk?'Gunakan data anggota keluarga yang sudah terdaftar.':'KK Anda belum diverifikasi Admin RT.' ?></small></span></label>
    <label class="lrq-source-card"><input type="radio" name="data_source" value="manual" <?= $source==='manual'?'checked':'' ?>><span class="lrq-radio" aria-hidden="true"></span><span class="lrq-source-ic is-grey"><?= family_icon('edit') ?></span><span><strong>Isi Manual</strong><small>Isi data pemohon secara manual (jika belum terdaftar di KK).</small></span></label>
   </div>
   <?php if(!$has_kk): ?><p class="lrq-hint"><?= family_icon('info') ?><span>Ingin data terisi otomatis? <a href="<?= site_url('warga/keluarga') ?>">Daftarkan Kartu Keluarga</a> lalu tunggu verifikasi Admin RT.</span></p><?php endif; ?>

   <?php if($has_kk): ?>
   <div class="lrq-kk" id="lrq-kk" data-address="<?= html_escape($family_address) ?>" data-initial="<?= html_escape($initial_nik) ?>" data-kk-number="<?= html_escape($family_kk) ?>" data-head="<?= html_escape($head_name) ?>" <?= $source==='kk'?'':'hidden' ?>>
    <div class="lrq-kk-pick">
     <p class="lrq-label" id="lrq-member-label">Pilih Anggota Keluarga (dari KK)</p>
     <div class="lrq-member" id="lrq-member">
      <button type="button" class="lrq-member-btn" id="lrq-member-btn" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="lrq-member-label lrq-member-value"><?= family_icon('people') ?><span id="lrq-member-value" class="is-placeholder">Pilih anggota keluarga...</span><?= family_icon('chevron') ?></button>
      <ul class="lrq-member-list" id="lrq-member-list" role="listbox" aria-labelledby="lrq-member-label" tabindex="-1" hidden>
      <?php foreach($members as $i=>$m): ?>
       <li role="option" id="lrq-opt-<?= $i ?>" aria-selected="false" data-member="<?= html_escape(json_encode($m,JSON_UNESCAPED_UNICODE)) ?>"><span class="lrq-avatar <?= $m['jk']==='P'?'is-p':'' ?>"><?= html_escape(mb_strtoupper(mb_substr($m['nama'],0,1))) ?></span><span class="lrq-opt-text"><strong><?= html_escape($m['nama']) ?> <em>(<?= html_escape($m['hubungan']?:'Anggota') ?>)</em></strong><small>NIK: <?= html_escape($m['nik']?:'-') ?> <i>|</i> <?= $m['jk']==='P'?'Perempuan':($m['jk']==='L'?'Laki-laki':'-') ?></small></span><span class="lrq-opt-tick" aria-hidden="true"><?= family_icon('check') ?></span></li>
      <?php endforeach; ?>
      </ul>
     </div>
    </div>
    <div class="lrq-kk-note"><?= family_icon('check') ?><p>Data otomatis diambil dari Kartu Keluarga dan sudah diverifikasi. Lengkapi hanya data yang belum tersedia, jika ada.</p></div>
   </div>
   <?php endif; ?>

   <p class="lrq-section-title"><?= family_icon('person') ?>Data Pemohon</p>
   <div class="lrq-fields">
    <div class="lrq-field"><label for="f-name">Nama Lengkap <b>*</b></label><div class="lrq-input"><?= family_icon('person') ?><input id="f-name" name="applicant_name" maxlength="140" value="<?= $old('applicant_name') ?>" required data-label="Nama lengkap" data-kk-lock placeholder="Nama sesuai KTP"><span class="lrq-lock" title="Dari KK terverifikasi"><?= family_icon('check') ?></span></div></div>
    <div class="lrq-field"><label for="f-nik">NIK <b>*</b></label><div class="lrq-input"><?= family_icon('card') ?><input id="f-nik" name="applicant_nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="<?= $old('applicant_nik') ?>" required data-label="NIK" data-pattern-message="NIK harus 16 digit angka." data-kk-lock placeholder="16 digit NIK"><span class="lrq-lock" title="Dari KK terverifikasi"><?= family_icon('check') ?></span></div></div>
    <div class="lrq-field"><label for="f-place">Tempat Lahir</label><div class="lrq-input"><?= family_icon('pin') ?><input id="f-place" name="applicant_birth_place" maxlength="100" value="<?= $old('applicant_birth_place') ?>" placeholder="Kota kelahiran"></div></div>
    <div class="lrq-field"><label for="f-date">Tanggal Lahir</label><div class="lrq-input"><?= family_icon('calendar') ?><input id="f-date" type="date" name="applicant_birth_date" max="<?= date('Y-m-d') ?>" value="<?= $old('applicant_birth_date') ?>" data-label="Tanggal lahir"></div></div>
    <div class="lrq-field"><label for="f-gender">Jenis Kelamin</label><div class="lrq-input"><?= family_icon('people') ?><select id="f-gender" name="applicant_gender"><option value="">Pilih jenis kelamin</option><?php foreach($genders as $choice): ?><option value="<?= $choice ?>" <?= $raw('applicant_gender')===$choice?'selected':'' ?>><?= $choice ?></option><?php endforeach; ?></select></div></div>
    <div class="lrq-field"><label for="f-religion">Agama <b>*</b></label><div class="lrq-input"><?= family_icon('heart') ?><select id="f-religion" name="applicant_religion" required data-label="Agama"><option value="">Pilih agama</option><?php foreach($religions as $religion): ?><option value="<?= html_escape($religion) ?>" <?= $raw('applicant_religion')===$religion?'selected':'' ?>><?= html_escape($religion) ?></option><?php endforeach; ?></select></div></div>
    <div class="lrq-field"><label for="f-job">Pekerjaan</label><div class="lrq-input"><?= family_icon('briefcase') ?><input id="f-job" name="applicant_occupation" maxlength="100" value="<?= $old('applicant_occupation') ?>" list="lrq-jobs" placeholder="Pekerjaan sesuai KTP"></div></div>
    <div class="lrq-field"><label for="f-phone">No. HP <small>(opsional)</small></label><div class="lrq-input"><?= family_icon('phone') ?><input id="f-phone" name="applicant_phone" inputmode="tel" maxlength="25" value="<?= $old('applicant_phone') ?>" placeholder="Masukkan nomor HP"></div></div>
    <div class="lrq-field is-wide"><label for="f-ktp">Alamat Sesuai KTP <b>*</b></label><div class="lrq-input"><?= family_icon('home') ?><input id="f-ktp" name="applicant_ktp_address" maxlength="255" value="<?= $old('applicant_ktp_address') ?>" list="lrq-addr" required data-label="Alamat sesuai KTP" placeholder="Alamat lengkap sesuai KTP"></div></div>
    <div class="lrq-field is-wide"><label for="f-dom">Alamat Domisili <b>*</b></label><div class="lrq-input"><?= family_icon('home') ?><input id="f-dom" name="address" maxlength="255" value="<?= $old('address') ?>" list="lrq-addr" required data-label="Alamat domisili" placeholder="Alamat tempat tinggal saat ini"></div></div>
   </div>

   <?php foreach($type_fields as $typeKey=>$fields): if(!$fields || !isset($catalog[$typeKey]))continue; ?>
   <div class="lrq-extra" data-type-fields="<?= html_escape($typeKey) ?>" <?= $selected===$typeKey?'':'hidden' ?>>
    <p class="lrq-section-title"><?= family_icon('file') ?>Data Khusus <?= html_escape($catalog[$typeKey][0]) ?></p>
    <div class="lrq-fields">
    <?php foreach($fields as $definition): list($field,$label,$inputType)=$definition; $ui=$field_ui[$field]??array(); $v=$raw($field); $is_nik=(bool)preg_match('/_nik$/',$field);
     $attrs='name="'.html_escape($field).'" id="x-'.html_escape($field).'" data-label="'.html_escape($label).'"';
     if(isset($ui['kk']) && $has_kk) $attrs.=' data-kk-fill="'.$ui['kk'].'"';
     if(isset($ui['person']) && $has_kk) $attrs.=' data-person="'.html_escape(json_encode($ui['person'])).'"';
     if(($ui['suggest']??'')==='people' && $has_kk) $attrs.=' list="lrq-people"';
     if(($ui['suggest']??'')==='relations') $attrs.=' list="lrq-relations"';
     if($is_nik || !empty($ui['numeric'])) $attrs.=' inputmode="numeric"'.($is_nik?' pattern="[0-9]{16}" maxlength="16" data-pattern-message="NIK harus 16 digit angka."':' maxlength="20"');
     $wide=$inputType==='textarea'; ?>
     <div class="lrq-field <?= $wide?'is-wide':'' ?>"><label for="x-<?= html_escape($field) ?>"><?= html_escape($label) ?></label>
     <?php if(!empty($ui['options'])): $opts=$ui['options']; if($v!=='' && !in_array($v,$opts,TRUE)) $opts[]=$v; ?>
      <div class="lrq-input"><?= family_icon($field_icon($field,$inputType)) ?><select <?= $attrs ?>><option value="">Pilih <?= html_escape(mb_strtolower($label)) ?></option><?php foreach($opts as $o): ?><option value="<?= html_escape($o) ?>" <?= $v===$o?'selected':'' ?>><?= html_escape($o) ?></option><?php endforeach; ?></select></div>
     <?php elseif($wide): ?>
      <div class="lrq-input is-plain"><textarea <?= $attrs ?> maxlength="255" rows="2" placeholder="<?= html_escape($label) ?>"><?= html_escape($v) ?></textarea></div>
      <?php if(!empty($ui['members']) && $has_kk): ?><button type="button" class="lrq-mini" data-members-into="<?= html_escape($field) ?>"><?= family_icon('people') ?>Isi dari anggota KK</button><?php endif; ?>
      <?php if(!empty($ui['kk']) && $has_kk): ?><button type="button" class="lrq-mini" data-kk-into="<?= html_escape($field) ?>"><?= family_icon('home') ?>Gunakan alamat KK</button><?php endif; ?>
     <?php else: ?>
      <div class="lrq-input"><?= family_icon($field_icon($field,$inputType)) ?><input type="<?= html_escape($inputType) ?>" <?= $attrs ?> <?= (!$is_nik && empty($ui['numeric']))?'maxlength="255"':'' ?> value="<?= html_escape($v) ?>" <?= $inputType==='number'?'min="0"':'' ?> placeholder="<?= in_array($inputType,array('date','time'),TRUE)?'':html_escape(isset($ui['person'])&&$has_kk?'Ketik atau pilih dari anggota KK':$label) ?>"></div>
     <?php endif; ?>
     </div>
    <?php endforeach; ?>
    </div>
   </div>
   <?php endforeach; ?>

   <p class="lrq-section-title is-purple"><?= family_icon('edit') ?>Keterangan Pengajuan</p>
   <div class="lrq-fields">
    <div class="lrq-field is-wide"><label for="f-purpose">Keperluan Surat <b>*</b></label><div class="lrq-input"><?= family_icon('edit') ?><input id="f-purpose" name="purpose" maxlength="255" value="<?= $old('purpose') ?>" required data-label="Keperluan surat" placeholder="Contoh: Untuk keperluan pengurusan administrasi di kelurahan."></div></div>
    <div class="lrq-field is-wide"><label for="f-desc">Keterangan Tambahan <small>(opsional)</small></label><div class="lrq-input is-plain"><textarea id="f-desc" name="description" rows="3" maxlength="500" placeholder="Tambahkan keterangan jika diperlukan..."><?= $old('description') ?></textarea></div><small class="lrq-count" id="lrq-desc-count">0 / 500</small></div>
   </div>

   <datalist id="lrq-jobs"><?php foreach(array('Belum/Tidak Bekerja','Mengurus Rumah Tangga','Pelajar/Mahasiswa','Karyawan Swasta','Pegawai Negeri Sipil','Wiraswasta','Buruh Harian Lepas','Pensiunan','Guru','Pedagang') as $j): ?><option value="<?= $j ?>"><?php endforeach; ?></datalist>
   <datalist id="lrq-relations"><?php foreach(array('Anak','Suami','Istri','Orang Tua','Saudara Kandung','Cucu','Menantu','Kerabat','Lainnya') as $j): ?><option value="<?= $j ?>"><?php endforeach; ?></datalist>
   <datalist id="lrq-addr"><?php if(!empty($family_address)): ?><option value="<?= html_escape($family_address) ?>"><?php endif; ?></datalist>
   <datalist id="lrq-people"><?php foreach($members as $m): ?><option value="<?= html_escape($m['nama']) ?>" label="<?= html_escape($m['hubungan']) ?>"><?php endforeach; ?></datalist>
  </div>

  <!-- STEP 2: upload -->
  <div class="lrq-step" data-step="2" hidden>
   <header class="lrq-head"><span class="lrq-head-ic is-blue"><?= family_icon('upload') ?></span><div><h2>Upload Dokumen Pendukung</h2><p>Lampirkan berkas JPG, PNG, atau PDF bila diperlukan.</p></div></header>
   <div id="death-documents" <?= $death?'':'hidden' ?>>
    <p class="lrq-section-title"><?= family_icon('file') ?>Dokumen Wajib Surat Kematian <small>(maks. 2 MB per dokumen)</small></p>
    <div class="lrq-drops">
     <?php foreach(array('doc_ktp'=>'KTP Almarhum','doc_kk'=>'Kartu Keluarga','doc_death'=>'Surat Keterangan Wafat') as $name=>$label): ?>
     <label class="lrq-drop"><input type="file" name="<?= $name ?>" data-death-field data-max="2" accept=".jpg,.jpeg,.png,.pdf"><span class="lrq-drop-ic"><?= family_icon('upload') ?></span><strong><?= $label ?></strong><small data-file-name>Pilih file</small></label>
     <?php endforeach; ?>
    </div>
   </div>
   <p class="lrq-section-title"><?= family_icon('file') ?>Lampiran Tambahan <small>(opsional, maks. 5 MB)</small></p>
   <label class="lrq-drop is-wide"><input type="file" name="attachment" data-max="5" accept=".jpg,.jpeg,.png,.pdf"><span class="lrq-drop-ic"><?= family_icon('cloud') ?></span><strong>Klik untuk memilih file atau tarik ke sini</strong><small data-file-name>JPG, PNG, atau PDF</small></label>
   <div class="lrq-privacy"><?= family_icon('shield') ?><p>Dokumen hanya digunakan untuk verifikasi pengajuan oleh pengurus RT.</p></div>
  </div>

  <!-- STEP 3: konfirmasi -->
  <div class="lrq-step" data-step="3" hidden>
   <header class="lrq-head"><span class="lrq-head-ic is-purple"><?= family_icon('check') ?></span><div><h2>Konfirmasi Pengajuan</h2><p>Periksa kembali data sebelum dikirim ke Admin RT.</p></div></header>
   <dl class="lrq-summary" id="lrq-summary"></dl>
   <div class="lrq-confirm-note"><?= family_icon('info') ?><p>Setelah dikirim, pengajuan diverifikasi Admin RT. Anda akan mendapat pemberitahuan ketika pengajuan disetujui.</p></div>
  </div>
 </section>

 <aside class="lrq-side">
  <section class="lrq-card lrq-preview-card">
   <header class="lrq-head"><span class="lrq-head-ic is-purple"><?= family_icon('file') ?></span><div><h2>Preview Surat</h2><p>Tampilan surat akan mengikuti data yang Anda isi.</p></div></header>
   <div class="request-preview" id="lrq-preview"><?php $this->load->view('shared/letter_head',array('settings'=>$settings,'headClass'=>'request-preview-head','logoUrl'=>site_url('warga/aset-surat/logo'))); ?><div class="request-preview-title"><b id="preview-letter-title"><?= html_escape($letter_name) ?></b><small>Nomor surat diberikan pengurus setelah diperiksa.</small></div><div class="request-preview-body" id="preview-letter-body"></div><div class="request-preview-sign"><?php $this->load->view('shared/letter_sign_heading',array('settings'=>$settings)); ?><div class="request-preview-sign-images"><?php if(!empty($settings['letter_stamp'])): ?><img class="stamp" src="<?= site_url('warga/aset-surat/stamp') ?>" alt="Stempel RT"><?php endif; ?><?php if(!empty($settings['letter_signature'])): ?><img class="signature" src="<?= site_url('warga/aset-surat/signature') ?>" alt="Tanda tangan RT"><?php endif; ?></div><strong><?= html_escape($settings['letter_signer_name']??'Nama Ketua RT') ?></strong></div></div>
   <div class="lrq-actions">
    <div data-actions="1"><button type="button" class="lrq-btn is-ghost" id="lrq-refresh"><?= family_icon('eye') ?>Perbarui Preview</button><button type="button" class="lrq-btn is-main" data-next="2"><?= family_icon('send') ?>Lanjut Upload Dokumen</button></div>
    <div data-actions="2" hidden><button type="button" class="lrq-btn is-ghost" data-prev="1"><?= family_icon('back') ?>Kembali</button><button type="button" class="lrq-btn is-main" data-next="3"><?= family_icon('check') ?>Lanjut Konfirmasi</button></div>
    <div data-actions="3" hidden><button type="submit" name="action" value="draft" formnovalidate class="lrq-btn is-ghost"><?= family_icon('save') ?>Simpan Draft</button><button type="submit" name="action" value="submit" class="lrq-btn is-main" id="lrq-submit"><?= family_icon('send') ?>Ajukan Pengajuan</button></div>
    <button type="submit" name="action" value="draft" formnovalidate class="lrq-draft-link" data-draft-link><?= family_icon('save') ?>Simpan sebagai Draft</button>
   </div>
  </section>
 </aside>
</div>
</div>
<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
</main>
<script type="application/json" id="lrq-data"><?= json_encode(array('templates'=>$previewTemplates,'master'=>$previewMaster,'members'=>$members),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= base_url('assets/letter-request.js') ?>?v=<?= filemtime(FCPATH.'assets/letter-request.js') ?>" defer></script>
<?php $this->load->view('site/footer',compact('settings')); ?>
