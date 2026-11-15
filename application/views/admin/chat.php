<?php
$last_id=0;
$profile_photo=function($citizen_id){$files=glob(FCPATH.'uploads/profiles/citizen_'.(int)$citizen_id.'_*')?:array();if(!$files)return '';usort($files,function($a,$b){return filemtime($b)<=>filemtime($a);});return base_url('uploads/profiles/'.basename($files[0]));};
$initial=function($name){return mb_strtoupper(mb_substr(trim((string)$name),0,1));};
$tones=array('blue','purple','orange','green','pink');
$tone=function($id)use($tones){return $tones[(int)$id%count($tones)];};
// Foto unggahan warga diutamakan; tanpa foto memakai avatar kartun sesuai jenis kelamin.
$avatar_url=function($item)use($profile_photo){return $profile_photo($item['citizen_id'])?:base_url('assets/mockup/chat-avatar-'.(($item['gender']??'female')==='male'?'male':'female').'.jpg');};
$avatar=function($item,$size='')use($avatar_url,$tone){
 return '<span class="chx-avatar '.$size.' tone-'.$tone($item['citizen_id']).'"><img src="'.$avatar_url($item).'" alt=""><i class="chx-dot '.(!empty($item['is_online'])?'on':'').'"></i></span>';
};
$time_label=function($at){if(!$at)return '';$ts=strtotime($at);$day=date('Y-m-d',$ts);if($day===date('Y-m-d'))return date('H:i',$ts);if($day===date('Y-m-d',strtotime('-1 day')))return 'Kemarin';return (int)date('j',$ts).' '.explode(' ',cfx_date($at))[1];};
$day_label=function($day){if($day===date('Y-m-d'))return 'Hari ini';if($day===date('Y-m-d',strtotime('-1 day')))return 'Kemarin';return cfx_date($day);};
$online_count=count(array_filter($conversations,function($c){return !empty($c['is_online']);}));
$unread_rows=count(array_filter($conversations,function($c){return !empty($c['unread']);}));
$unread_total=array_sum(array_map('intval',array_column($conversations,'unread')));
$phone=$selected['phone']??'';$email=$selected['email']??'';$notes=$selected['notes']??'';
$wa=$phone?preg_replace('/^0/','62',preg_replace('/\D/','',$phone)):'';
$emojis=array('😊','😀','😁','😂','🙏','👍','👌','🙂','😉','🤝','👋','🎉','❤️','✅','📌','📅','📢','🏠','💰','⏰','🙌','😅','🤗','💪');
?>
<link rel="stylesheet" href="<?= base_url('assets/admin-chat-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-chat-v3.css') ?>">
<section class="chx-hero">
 <div class="chx-hero-copy"><small>LAYANAN WARGA</small><h1>Live Chat Warga</h1><p>Lihat warga yang sedang online dan balas pesan secara langsung.<br>Bangun komunikasi yang lebih dekat dan responsif.</p></div>
 <aside class="chx-stats" aria-label="Ringkasan chat"><div><span class="green"><?= official_svg('people') ?></span><p><b id="chx-stat-online"><?= $online_count ?></b><small>Warga Online</small></p></div><div><span class="blue"><?= official_svg('chat') ?></span><p><b id="chx-stat-total"><?= count($conversations) ?></b><small>Total Percakapan</small></p></div><div><span class="orange"><?= official_svg('bell') ?></span><p><b class="new" id="chx-stat-unread"><?= $unread_total ?></b><small>Pesan Baru</small></p></div></aside>
</section>
<div class="chx-layout<?= $selected?'':' no-selection' ?>">
 <aside class="chx-panel chx-list">
  <header><h2>Daftar Warga</h2><span class="chx-online-pill"><i></i><span id="chx-online-pill"><?= $online_count ?></span> online</span></header>
  <div class="chx-tools"><label class="chx-search"><?= official_svg('search') ?><input id="chx-search" type="search" placeholder="Cari nama warga..." autocomplete="off" aria-label="Cari nama warga"></label><button type="button" class="chx-icon-btn" id="chx-sort" data-mode="recent" title="Urutan: terbaru. Klik untuk urut nama" aria-label="Ubah urutan daftar warga"><?= official_svg('sort') ?></button></div>
  <div class="chx-tabs" role="tablist"><button type="button" class="active" data-tab="all" role="tab" aria-selected="true">Semua <b id="chx-tab-all"><?= count($conversations) ?></b></button><button type="button" data-tab="online" role="tab" aria-selected="false">Online <b class="green" id="chx-tab-online"><?= $online_count ?></b></button><button type="button" data-tab="unread" role="tab" aria-selected="false">Belum Dibaca <b class="red" id="chx-tab-unread"><?= $unread_rows ?></b></button></div>
  <div class="chx-people" id="chx-people">
  <?php foreach($conversations as $item): $active=$selected&&(int)$selected['id']===(int)$item['id']; ?>
   <a class="chx-person<?= $active?' active':'' ?>" data-id="<?= (int)$item['id'] ?>" data-name="<?= html_escape(mb_strtolower($item['display_name'].' '.$item['username'])) ?>" data-online="<?= !empty($item['is_online'])?1:0 ?>" data-unread="<?= (int)$item['unread'] ?>" href="<?= site_url('admin/chat') ?>?conversation=<?= (int)$item['id'] ?>"><?= $avatar($item) ?><span class="chx-person-copy"><strong><?= html_escape($item['display_name']) ?></strong><small data-preview><?= html_escape(mb_strimwidth($item['last_message']?:'Belum ada pesan',0,42,'…')) ?></small></span><span class="chx-person-meta"><time data-time><?= html_escape($time_label($item['last_message_time'])) ?></time><b data-badge<?= $item['unread']?'':' hidden' ?>><?= (int)$item['unread'] ?></b></span></a>
  <?php endforeach; ?>
  <p class="chx-none" id="chx-none"<?= $conversations?' hidden':'' ?>><?= $conversations?'Tidak ada warga yang cocok.':'Belum ada percakapan warga.' ?></p>
  </div>
 </aside>
 <?php if($selected): ?>
 <section class="chx-panel chx-window">
  <header class="chx-window-head"><?= $avatar($selected,'large') ?><div><strong><?= html_escape($selected['display_name']) ?></strong><small data-status class="<?= !empty($selected['is_online'])?'on':'' ?>"><i></i><span><?= !empty($selected['is_online'])?'Online':'Sedang offline' ?></span></small></div><nav aria-label="Aksi percakapan"><button type="button" class="chx-icon-btn" disabled title="Panggilan video belum tersedia" aria-label="Panggilan video belum tersedia"><?= official_svg('video') ?></button><?php if($phone): ?><a class="chx-icon-btn" href="tel:<?= html_escape($phone) ?>" title="Telepon <?= html_escape($phone) ?>" aria-label="Telepon warga"><?= official_svg('phone') ?></a><?php else: ?><button type="button" class="chx-icon-btn" disabled title="Warga belum punya nomor telepon" aria-label="Warga belum punya nomor telepon"><?= official_svg('phone') ?></button><?php endif; ?><details class="chx-menu"><summary class="chx-icon-btn" aria-label="Menu percakapan"><?= official_svg('more-v') ?></summary><div><a href="<?= site_url('admin/citizen/'.(int)$selected['citizen_id']) ?>">Lihat Data Warga</a><?php if($wa): ?><a href="https://wa.me/<?= html_escape($wa) ?>" target="_blank" rel="noopener">Buka WhatsApp</a><?php endif; ?></div></details></nav></header>
  <div class="chx-messages" id="chx-messages">
   <?php $current_day='';foreach($messages as $message):$last_id=max($last_id,(int)$message['id']);$mine=$message['sender_type']==='admin';$day=substr($message['created_at'],0,10);if($day!==$current_day):$current_day=$day; ?><div class="chx-day" data-day="<?= $day ?>"><span><?= html_escape($day_label($day)) ?></span></div><?php endif; ?>
   <div class="chx-row <?= $mine?'mine':'theirs' ?>" data-id="<?= (int)$message['id'] ?>"><?php if(!$mine): ?><?= $avatar($selected,'mini') ?><?php endif; ?><div class="chx-bubble"><p><?= nl2br(html_escape($message['message'])) ?></p><time><?= date('H:i',strtotime($message['created_at'])) ?><?php if($mine): ?><span class="chx-tick<?= $message['is_read']?' read':'' ?>"><?= $message['is_read']?'✓✓':'✓' ?></span><?php endif; ?></time></div></div>
   <?php endforeach; ?>
   <p class="chx-start" id="chx-start"<?= $messages?' hidden':'' ?>>Belum ada pesan. Mulai percakapan dengan <?= html_escape($selected['display_name']) ?>.</p>
  </div>
  <form class="chx-compose" id="chx-form"><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><button class="chx-attach" type="button" disabled title="Lampiran belum tersedia" aria-label="Lampiran belum tersedia"><?= official_svg('clip') ?></button><div class="chx-input"><textarea name="message" rows="1" maxlength="2000" placeholder="Tulis balasan untuk <?= html_escape($selected['display_name']) ?>..." aria-label="Tulis balasan" required></textarea><details class="chx-emoji"><summary aria-label="Pilih emoji"><?= official_svg('smile') ?></summary><div><?php foreach($emojis as $emoji): ?><button type="button" data-emoji="<?= $emoji ?>" aria-label="Sisipkan <?= $emoji ?>"><?= $emoji ?></button><?php endforeach; ?></div></details></div><button class="chx-send" type="submit" aria-label="Kirim"><?= official_svg('send') ?></button></form>
  <p class="chx-send-error" id="chx-send-error" role="alert" hidden></p>
 </section>
 <aside class="chx-side">
  <section class="chx-panel"><header class="chx-side-head"><span class="chx-side-icon blue"><?= official_svg('person') ?></span><h2>Informasi Warga</h2><a class="chx-mini-link" href="<?= site_url('admin/citizen/'.(int)$selected['citizen_id']) ?>" aria-label="Buka data warga" title="Buka data warga"><?= official_svg('more-v') ?></a></header><div class="chx-profile"><?= $avatar($selected,'large') ?><div><strong><?= html_escape($selected['display_name']) ?></strong><small data-status class="<?= !empty($selected['is_online'])?'on':'' ?>"><i></i><span><?= !empty($selected['is_online'])?'Online':'Sedang offline' ?></span></small></div></div><ul class="chx-facts"><li><?= official_svg('phone') ?><?= html_escape($phone?:'Belum ada nomor telepon') ?></li><li><?= official_svg('mail') ?><span><?= html_escape($email?:'Belum ada email') ?></span></li><li><?= official_svg('people') ?>Warga RT 009/008</li><li><?= official_svg('calendar') ?>Bergabung: <?= cfx_date($selected['joined_at']) ?></li></ul></section>
  <section class="chx-panel chx-note" id="chx-note" data-url="<?= site_url('admin/chat_note/'.(int)$selected['id']) ?>"><header class="chx-side-head"><span class="chx-side-icon orange"><?= official_svg('pencil') ?></span><h2>Catatan</h2></header><p class="chx-note-text" id="chx-note-text"<?= $notes?'':' hidden' ?>><?= html_escape($notes) ?></p><p class="chx-note-empty" id="chx-note-empty"<?= $notes?' hidden':'' ?>>Belum ada catatan untuk warga ini.</p><div class="chx-note-edit" id="chx-note-edit" hidden><textarea id="chx-note-input" rows="3" maxlength="1000" aria-label="Catatan warga"><?= html_escape($notes) ?></textarea><div><button type="button" id="chx-note-save">Simpan</button><button type="button" id="chx-note-cancel">Batal</button></div></div><button type="button" class="chx-note-add" id="chx-note-add"><?= $notes?'Ubah Catatan':'+ &nbsp;Tambah Catatan' ?></button></section>
  <section class="chx-panel"><header class="chx-side-head"><span class="chx-side-icon purple"><?= official_svg('image') ?></span><h2>Media yang Dibagikan</h2></header><p class="chx-media-empty">Belum ada media yang dibagikan.</p></section>
 </aside>
 <?php else: ?>
 <section class="chx-panel chx-window chx-empty"><?= official_svg('chat') ?><strong>Pilih percakapan</strong><p>Percakapan warga akan muncul di sini.</p></section>
 <?php endif; ?>
</div>
<script>
window.CHX_CONFIG=<?= json_encode(array(
 'selected'=>$selected?(int)$selected['id']:0,'last'=>(int)$last_id,
 'pollUrl'=>$selected?site_url('admin/chat/pesan/'.$selected['id']):'','sendUrl'=>$selected?site_url('admin/chat/kirim/'.$selected['id']):'',
 'csrfName'=>$this->security->get_csrf_token_name(),'citizenAvatar'=>$selected?$avatar_url($selected):'',
 'citizenInitial'=>$selected?$initial($selected['display_name']):'','citizenTone'=>$selected?$tone($selected['citizen_id']):'',
 'today'=>date('Y-m-d'),
),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
</script>
<script src="<?= base_url('assets/admin-chat.js') ?>?v=<?= filemtime(FCPATH.'assets/admin-chat.js') ?>"></script>
