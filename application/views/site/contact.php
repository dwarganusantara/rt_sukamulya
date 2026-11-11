<?php
$title='Kontak RT | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='contact_rt'; // header merah (warga & tamu)
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$rt_full=trim(($settings['brand_label']??'RT 009/008').' '.($settings['brand_subtitle']??'Suka Mulya IV'));
$phone=trim((string)($settings['phone']??''));$phone_href=preg_replace('/[^0-9+]/','',$phone);
$email=trim((string)($settings['email']??''));$address=trim((string)($settings['address']??''));
$emergency=trim((string)($settings['emergency_contact']??''));$emergency_href=preg_replace('/[^0-9+]/','',$emergency);
if(strlen($emergency_href)<8)$emergency_href='';
$whatsapp=preg_replace('/[^0-9]/','',(string)($settings['whatsapp']??''));
$whatsapp=preg_replace('/^0/','62',$whatsapp);
$web_url=function($url){$url=trim((string)$url);return preg_match('#^https?://#i',$url)?$url:'';};
$map=$web_url($settings['map_url']??'');$instagram=$web_url($settings['instagram_url']??'');
$arrow='<span class="ctc-arrow" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16M13 5l7 7-7 7"></path></svg></span>';
$open=function($tag,$class,$href,$external=FALSE){
 if(!$href)return '<div class="'.$class.' is-empty">';
 return '<a class="'.$class.'" href="'.html_escape($href).'"'.($external?' target="_blank" rel="noopener"':'').'>';
};
$close=function($href){return $href?'</a>':'</div>';};
$cards=array(
 array('address','contact-address.png','Alamat RT',$address!==''?nl2br(html_escape($address)).'<br>'.html_escape($settings['brand_label']??'RT 009/008'):'Belum tersedia',$map,TRUE),
 array('phone','contact-phone.png','Telepon',$phone!==''?html_escape($phone):'Belum tersedia',$phone_href?'tel:'.$phone_href:'',FALSE),
 array('email','contact-email.png','Email',$email!==''?html_escape($email):'Belum tersedia',$email?'mailto:'.$email:'',FALSE),
 array('emergency','contact-emergency.png','Kontak Darurat',$emergency!==''?nl2br(html_escape($emergency)):'Belum tersedia',$emergency_href?'tel:'.$emergency_href:'',FALSE),
);
$channels=array(
 array('contact-whatsapp.png','WhatsApp','Chat Langsung',$whatsapp?'https://wa.me/'.$whatsapp:''),
 array('contact-instagram.png','Instagram','Lihat Kegiatan',$instagram),
 array('contact-maps.png','Google Maps','Lokasi RT',$map),
);
?>
<link rel="stylesheet" href="<?= base_url('assets/contact-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/contact-v3.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="ctc-page crd-page crd-contact">
 <section class="ctc-hero">
  <img class="ctc-leaf" src="<?= base_url('assets/mockup/contact-leaf.png') ?>" alt="">
  <img class="ctc-art" src="<?= base_url(html_escape($settings['contact_page_image']??'assets/mockup/contact-hero-v3.png')) ?>" alt="Warga <?= html_escape($rt_full) ?> menyapa di depan gerbang lingkungan">
  <div class="ctc-copy">
   <nav class="ctc-crumb" aria-label="Breadcrumb"><a href="<?= site_url($this->session->userdata('citizen_id')?'warga':'') ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>Beranda</a><i>›</i><b>Kontak RT</b></nav>
   <span class="ctc-eyebrow">HUBUNGI KAMI</span>
   <h1>Kontak <b>RT</b></h1>
   <h2><?= html_escape($rt_full) ?></h2>
   <p>Hubungi pengurus untuk informasi, saran, atau bantuan terkait lingkungan <?= html_escape($rt_full) ?>. Kami siap melayani dengan cepat dan ramah.</p>
  </div>
 </section>
 <div class="ctc-content">
  <section class="ctc-cards" id="kontak-rt" aria-label="Kontak utama">
   <?php foreach($cards as $card): ?>
   <?= $open('a','ctc-card '.$card[0],$card[4],$card[5]) ?><img src="<?= base_url('assets/mockup/'.$card[1]) ?>" alt=""><span class="ctc-card-copy"><strong><?= $card[2] ?></strong><small><?= $card[3] ?></small></span><?= $card[4]?$arrow:'' ?><?= $close($card[4]) ?>
   <?php endforeach; ?>
  </section>
  <section class="ctc-lower">
   <article class="ctc-panel ctc-hours">
    <header class="ctc-head"><span class="blue"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span><div><h2>Jam Layanan</h2><p>Pengurus RT siap melayani warga pada jam berikut:</p></div></header>
    <div class="ctc-hours-body"><div class="ctc-hours-main"><div class="ctc-times"><div><b>Senin – Jumat</b><small>08.00 – 16.00 WIB</small></div><div><b>Sabtu – Minggu</b><small>08.00 – 12.00 WIB</small></div></div><p class="ctc-note"><i>i</i>Di luar jam tersebut, silakan hubungi kontak darurat yang tersedia.</p></div><img src="<?= base_url('assets/mockup/contact-calendar.png') ?>" alt=""></div>
   </article>
   <article class="ctc-panel ctc-channels">
    <header class="ctc-head"><span class="blue"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a4 4 0 0 0 6 0l3-3a4 4 0 0 0-6-6l-1 1M14 10a4 4 0 0 0-6 0l-3 3a4 4 0 0 0 6 6l1-1"></path></svg></span><div><h2>Cara Lain Menghubungi</h2><p>Anda juga dapat menghubungi kami melalui:</p></div></header>
    <div class="ctc-channel-grid"><?php foreach($channels as $channel): ?><?= $open('a','ctc-channel',$channel[3],TRUE) ?><img src="<?= base_url('assets/mockup/'.$channel[0]) ?>" alt=""><strong><?= $channel[1] ?></strong><small><?= $channel[3]?$channel[2]:'Belum tersedia' ?></small><?= $close($channel[3]) ?><?php endforeach; ?></div>
    <a class="ctc-all" href="#kontak-rt"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"></path></svg>Buka Semua Kontak</a>
   </article>
   <article class="ctc-panel ctc-message"><span class="ctc-fast"><i></i>Respon Cepat</span>
    <header class="ctc-head"><span class="orange"><svg width="25" height="25" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 3H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4v4l5-4h7a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM8 12.500a1.500 1.500 0 1 1 0-3 1.500 1.500 0 0 1 0 3zm4 0a1.500 1.500 0 1 1 0-3 1.500 1.500 0 0 1 0 3zm4 0a1.500 1.500 0 1 1 0-3 1.500 1.500 0 0 1 0 3z"></path></svg></span><div><h2>Tulis Pesan</h2><p>Sampaikan pertanyaan, saran, atau masukan Anda.</p></div></header>
    <a class="ctc-send" href="<?= site_url('warga/chat') ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 21l21-9L2 3v7l15 2-15 2z"></path></svg>Kirim Pesan Sekarang</a>
    <small class="ctc-send-note">Pesan dikirim lewat Live Chat. Masuk dengan akun warga untuk memulai.</small>
    <img src="<?= base_url('assets/mockup/contact-note.png') ?>" alt="">
   </article>
  </section>
 </div>
 <?php $this->load->view('citizen/red_deco'); ?>
</main>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
