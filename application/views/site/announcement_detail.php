<?php
$title=$item['title'].' | '.($settings['site_name']??'Sukamulya');
$this->load->view('site/header',compact('title','settings','menu'));
$labels=array('kegiatan'=>'Kegiatan','informasi'=>'Informasi','iuran'=>'Iuran','lainnya'=>'Lainnya');
$safe=html_escape($item['body']);
$safe=preg_replace('/\*\*(.+?)\*\*/us','<strong>$1</strong>',$safe);
$safe=nl2br($safe);
?>
<main class="announcement-detail-page">
  <header class="announcement-detail-head"><div class="wrap"><nav aria-label="Breadcrumb"><a href="<?= site_url() ?>">Beranda</a><span>›</span><a href="<?= site_url('pengumuman') ?>">Pengumuman</a></nav><span class="announcement-category <?= $category ?>"><?= $labels[$category] ?></span><h1><?= html_escape($item['title']) ?></h1><time datetime="<?= html_escape(substr($item['created_at'],0,10)) ?>"><?= date('d M Y',strtotime($item['created_at'])) ?></time></div></header>
  <div class="wrap announcement-detail-layout">
    <article class="announcement-detail-article"><?php if($item['image']): ?><img src="<?= base_url(html_escape($item['image'])) ?>" alt="<?= html_escape($item['title']) ?>"><?php endif; ?><div class="announcement-detail-body"><?= $safe ?></div><a class="announcement-back" href="<?= site_url('pengumuman') ?>">← Kembali ke Pengumuman</a></article>
    <aside><h2>Pengumuman Lainnya</h2><?php foreach($related as $other): ?><a href="<?= site_url('pengumuman/'.(int)$other['id']) ?>"><time><?= date('d M Y',strtotime($other['created_at'])) ?></time><strong><?= html_escape($other['title']) ?></strong></a><?php endforeach; ?><?php if(!$related): ?><p>Belum ada pengumuman lainnya.</p><?php endif; ?></aside>
  </div>
</main>
<?php $this->load->view('site/footer',compact('settings')); ?>
