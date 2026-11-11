<?php
$title='Struktur Organisasi | '.($settings['site_name']??'Sukamulya');
$this->load->view('site/header',compact('title','settings','menu'));
$leader=NULL;$members=array();
foreach($items as $candidate){if(empty($candidate['parent_id'])){$leader=$candidate;break;}}
if(!$leader&&$items)$leader=$items[0];
if($leader){foreach($items as $candidate){if((int)$candidate['id']!==(int)$leader['id'])$members[]=$candidate;}}
$hero=$settings['structure_page_image']??'assets/mockup/home-hero-v4.png';
$photo=function($item){return !empty($item['photo'])?base_url(html_escape($item['photo'])):'';};
?>
<main class="org-public">
 <section class="org-hero"><div class="wrap org-hero-inner"><div class="org-intro"><nav class="org-breadcrumb"><a href="<?= site_url() ?>">Beranda</a><span>›</span><a href="<?= site_url('profil') ?>">Profil</a><span>›</span><strong>Struktur Organisasi</strong></nav><h1>Struktur Organisasi RT</h1><p>Pengurus RT siap melayani dan bekerja bersama untuk memberikan layanan terbaik bagi warga.</p></div><img class="org-hero-art" src="<?= base_url(html_escape($hero)) ?>" alt="Lingkungan Suka Mulya IV"></div></section>
 <section class="org-section"><div class="wrap">
 <?php if($leader): ?><div class="org-tree">
  <article class="org-card org-leader"><span class="org-role-icon"><?= html_escape($leader['icon']??'👑') ?></span><?php if($photo($leader)): ?><img src="<?= $photo($leader) ?>" alt="Foto <?= html_escape($leader['name']) ?>"><?php else: ?><span class="org-avatar"><?= html_escape(mb_substr($leader['name'],0,1)) ?></span><?php endif; ?><div><h2><?= html_escape($leader['position']) ?></h2><strong><?= html_escape($leader['name']) ?></strong><p><?= html_escape($leader['description']?:'Bertanggung jawab atas pengelolaan dan pelayanan RT secara keseluruhan.') ?></p></div></article>
  <?php if($members): ?><div class="org-branches"><?php foreach($members as $i=>$item): ?><article class="org-card org-member org-color-<?= ($i%5)+1 ?>"><span class="org-role-icon"><?= html_escape($item['icon']??'👤') ?></span><?php if($photo($item)): ?><img src="<?= $photo($item) ?>" alt="Foto <?= html_escape($item['name']) ?>"><?php else: ?><span class="org-avatar"><?= html_escape(mb_substr($item['name'],0,1)) ?></span><?php endif; ?><div><h2><?= html_escape($item['position']) ?></h2><strong><?= html_escape($item['name']) ?></strong><p><?= html_escape($item['description']?:'Melayani warga sesuai tugas dan tanggung jawabnya.') ?></p></div></article><?php endforeach; ?></div><?php endif; ?>
 </div><?php else: ?><div class="org-empty"><h2>Data pengurus belum tersedia</h2><p>Silakan kembali lagi setelah struktur organisasi diperbarui.</p></div><?php endif; ?>
 </div></section>
 <section class="org-commitment"><div class="wrap"><h2>Komitmen Kami</h2><p>Bekerja bersama untuk lingkungan yang aman, nyaman, dan sejahtera.</p><div class="org-values"><div><b>◉</b><strong>Pelayanan Cepat</strong></div><div><b>▣</b><strong>Informasi Transparan</strong></div><div><b>♢</b><strong>Lingkungan Aman</strong></div><div><b>♧</b><strong>Kebersamaan Warga</strong></div></div></div></section>
</main>
<?php $this->load->view('site/footer',compact('settings')); ?>
