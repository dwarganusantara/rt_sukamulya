<?php
$this->load->helper(array('official_icons','content_form'));
$top_id=(int)$this->session->userdata('citizen_id');
$top_citizen=$this->db->select('display_name,gender')->get_where('citizens',array('id'=>$top_id))->row_array();
$top_photos=glob(FCPATH.'uploads/profiles/citizen_'.$top_id.'_*')?:array();
usort($top_photos,function($a,$b){return filemtime($b)<=>filemtime($a);});
$top_avatar=$top_photos?'uploads/profiles/'.basename($top_photos[0]):'assets/mockup/chat-avatar-'.(($top_citizen['gender']??'female')==='male'?'male':'female').'.jpg';
$top_unread=0;
if($this->db->table_exists('chat_messages')){$top_conv=$this->db->select('id')->get_where('chat_conversations',array('citizen_id'=>$top_id))->row_array();if($top_conv)$top_unread=$this->db->where('conversation_id',(int)$top_conv['id'])->where('sender_type','admin')->where('is_read',0)->count_all_results('chat_messages');}
$top_unpaid=$this->db->where('citizen_id',$top_id)->where('amount_due > amount_paid',NULL,FALSE)->count_all_results('dues');
$top_total=$top_unread+$top_unpaid;
$top_label=$top_total?trim(($top_unread?$top_unread.' pesan baru dari pengurus. ':'').($top_unpaid?$top_unpaid.' tagihan belum lunas.':'')):'Tidak ada pemberitahuan baru. Lihat pengumuman.';
$top_target=$top_unread?'warga/chat':($top_unpaid?'warga/iuran#tagihan':'pengumuman');
?>
<header class="cdx-top">
 <a class="cdx-back" href="<?= site_url('warga') ?>"><?= official_svg('back') ?>Kembali</a>
 <nav class="cdx-crumb" aria-label="Breadcrumb"><?php foreach($crumbs as $i=>$crumb): ?><?php if($i): ?><i>›</i><?php endif; ?><?php if(!empty($crumb[1])): ?><a href="<?= site_url($crumb[1]) ?>"><?= html_escape($crumb[0]) ?></a><?php elseif($i<count($crumbs)-1): ?><span><?= html_escape($crumb[0]) ?></span><?php else: ?><b><?= html_escape($crumb[0]) ?></b><?php endif; ?><?php endforeach; ?></nav>
 <div class="cdx-tools">
  <a class="cdx-tool" href="<?= site_url($top_target) ?>" aria-label="<?= html_escape($top_label) ?>" title="<?= html_escape($top_label) ?>"><?= official_svg('bell') ?><?php if($top_total): ?><b><?= (int)$top_total ?></b><?php endif; ?></a>
  <details class="cdx-account"><summary><img src="<?= base_url(html_escape($top_avatar)) ?>" alt=""><span><strong><?= html_escape($top_citizen['display_name']??'Warga') ?></strong><small>Warga RT 009/008</small></span><i class="acx-caret" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg></i></summary><?php $this->load->view('site/account_menu',array('acx_avatar'=>$top_avatar,'acx_name'=>$top_citizen['display_name']??'Warga')); ?></details>
 </div>
</header>
<script>document.addEventListener('click',function(event){var account=document.querySelector('.cdx-account');if(account&&!event.target.closest('.cdx-account'))account.open=false});</script>
