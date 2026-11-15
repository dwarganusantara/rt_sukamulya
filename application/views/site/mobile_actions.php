<?php
// Aksi header versi HP/tablet: lonceng dan foto untuk warga yang sudah masuk.
$this->load->helper('official_icons');
$mb_id=(int)$this->session->userdata('citizen_id');
if($mb_id):
 $mb_citizen=$this->db->select('gender')->get_where('citizens',array('id'=>$mb_id))->row_array();
 $mb_photos=glob(FCPATH.'uploads/profiles/citizen_'.$mb_id.'_*')?:array();
 usort($mb_photos,function($a,$b){return filemtime($b)<=>filemtime($a);});
 $mb_avatar=$mb_photos?'uploads/profiles/'.basename($mb_photos[0]):'assets/mockup/chat-avatar-'.(($mb_citizen['gender']??'female')==='male'?'male':'female').'.jpg';
 $mb_chat=0;
 if($this->db->table_exists('chat_messages')){$mb_conv=$this->db->select('id')->get_where('chat_conversations',array('citizen_id'=>$mb_id))->row_array();if($mb_conv)$mb_chat=$this->db->where('conversation_id',(int)$mb_conv['id'])->where('sender_type','admin')->where('is_read',0)->count_all_results('chat_messages');}
 $mb_dues=$this->db->where('citizen_id',$mb_id)->where('amount_due > amount_paid',NULL,FALSE)->count_all_results('dues');
 $mb_total=$mb_chat+$mb_dues;
 $mb_label=$mb_total?trim(($mb_chat?$mb_chat.' pesan baru dari pengurus. ':'').($mb_dues?$mb_dues.' tagihan belum lunas.':'')):'Tidak ada pemberitahuan baru. Lihat pengumuman.';
?>
<div class="mbx-actions"><a class="mbx-bell" href="<?= site_url($mb_chat?'warga#live-chat':($mb_dues?'warga/iuran':'pengumuman')) ?>" aria-label="<?= html_escape($mb_label) ?>"><?= official_svg('bell') ?><?php if($mb_total): ?><b><?= $mb_total>9?'9+':$mb_total ?></b><?php endif; ?></a><a class="mbx-avatar" href="<?= site_url('warga/profil') ?>" aria-label="Profil saya"><img src="<?= base_url(html_escape($mb_avatar)) ?>" alt=""></a></div>
<?php endif; ?>
