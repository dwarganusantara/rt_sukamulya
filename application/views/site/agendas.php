<?php
/*
 * Daftar Agenda & Acara — desain merah (komponen sama dengan Pengumuman/Galeri: assets/citizen-red.css, .anx-*).
 * Header merah untuk warga maupun tamu (mode tamu: menu publik + "Portal Warga"). Identitas RT dari Pengaturan.
 */
$title='Agenda & Acara | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='events';
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$labels=array('kegiatan'=>'Kegiatan','kerja-bakti'=>'Kerja Bakti','lomba'=>'Lomba','sosial'=>'Sosial','lainnya'=>'Lainnya');
$ic=function($d,$s=16){return '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$d.'"/></svg>';};
$I=array(
 'all'=>'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
 'kegiatan'=>'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c.6-3.6 3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c2 .7 3.2 2.5 3.5 5.2',
 'kerja-bakti'=>'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10ZM2 21c0-3 1.9-5.4 5.1-6',
 'lomba'=>'M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3',
 'sosial'=>'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z',
 'lainnya'=>'M5 12h.01M12 12h.01M19 12h.01',
 'search'=>'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5','cal'=>'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5','back'=>'M19 12H5M11 18l-6-6 6-6','arrow'=>'M5 12h14M13 6l6 6-6 6',
 'clock'=>'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2','pin'=>'M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0ZM12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z');
$months=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$date_label=function($value)use($months){$time=strtotime($value);return date('d',$time).' '.$months[(int)date('n',$time)].' '.date('Y',$time);};
$rt=trim(($settings['brand_label']??'RT 009/008').' '.($settings['brand_subtitle']??'Suka Mulya IV'));
$hero=trim((string)($settings['agenda_page_image']??''))?:'assets/mockup/agenda-hero-v2.png';
$is_citizen=(bool)$this->session->userdata('citizen_id');
$qs=function($cat)use($query){$p=array();if($cat!=='')$p[]='kategori='.$cat;if($query!=='')$p[]='q='.rawurlencode($query);return site_url('agenda').($p?'?'.implode('&',$p):'');};
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="agenda-page anx-page agl-page crd-page">
 <section class="anx-hero">
  <span class="anx-hero-art" aria-hidden="true"><img src="<?= base_url(html_escape($hero)) ?>" alt=""></span>
  <svg class="anx-hero-wave" viewBox="0 0 1600 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V40C220 96 420 110 700 84S1260 30 1600 70v50Z" fill="#fff"/><path d="M0 52C230 104 430 116 700 92S1260 40 1600 80v10C1260 52 980 102 700 104S220 112 0 66Z" fill="#d9002c"/><path d="M0 0v34C120 70 220 84 320 82 200 66 90 36 0 0Z" fill="#e3062f" opacity=".85"/></svg>
  <div class="anx-wrap anx-hero-copy">
   <a class="anx-back" href="<?= site_url($is_citizen?'warga':'') ?>"><?= $ic($I['back'],20) ?>Kembali</a>
   <span class="anx-eyebrow">KABAR LINGKUNGAN</span>
   <h1 class="glx-title">Agenda &amp; <b>Acara</b></h1>
   <p>Informasi terbaru dari <?= html_escape($rt) ?><br>terkait kegiatan, acara, dan agenda lingkungan.</p>
  </div>
 </section>
 <section class="anx-wrap anx-content">
  <form class="anx-tools" method="get" action="<?= site_url('agenda') ?>">
   <?php if($category!==''): ?><input type="hidden" name="kategori" value="<?= html_escape($category) ?>"><?php endif; ?>
   <label class="anx-search"><?= $ic($I['search'],22) ?><input type="search" name="q" value="<?= html_escape($query) ?>" placeholder="Cari agenda atau acara..." aria-label="Cari agenda"><button type="submit">Cari</button></label>
   <nav class="anx-filters" aria-label="Filter agenda">
    <a class="<?= $category===''?'active':'' ?>" href="<?= $qs('') ?>"><?= $ic($I['all'],18) ?>Semua</a>
    <?php foreach($labels as $key=>$label): ?><a class="<?= $category===$key?'active':'' ?>" href="<?= $qs($key) ?>"><?= $ic($I[$key],18) ?><?= $label ?></a><?php endforeach; ?>
   </nav>
  </form>
  <?php if($items): ?><div class="anx-grid agl-grid">
   <?php foreach($items as $item): $cat=isset($labels[$item['category']])?$item['category']:'lainnya'; $excerpt=trim(preg_replace('/\s+/u',' ',strip_tags(str_replace('**','',$item['body'])))); $url=site_url('agenda/'.(int)$item['id']); ?>
   <article class="anx-card">
    <a class="anx-image" href="<?= $url ?>"><?php if($item['image']): ?><img src="<?= base_url(html_escape($item['image'])) ?>" alt="<?= html_escape($item['title']) ?>" loading="lazy"><?php else: ?><span class="anx-image-fallback"><?= $ic($I['cal'],56) ?></span><?php endif; ?><b class="anx-cat agl-cat is-<?= $cat ?>"><?= $ic($I[$cat],16) ?><?= $labels[$cat] ?></b></a>
    <div class="anx-body">
     <time datetime="<?= html_escape(substr($item['event_date']?:$item['created_at'],0,10)) ?>"><?= $ic($I['cal'],16) ?><?= $date_label($item['event_date']?:$item['created_at']) ?></time>
     <h2><a href="<?= $url ?>"><?= html_escape($item['title']) ?></a></h2>
     <?php if($item['event_time']||$item['event_location']): ?><div class="agl-meta"><?php if($item['event_time']): ?><span><?= $ic($I['clock'],15) ?><?= html_escape(substr($item['event_time'],0,5)) ?> WIB</span><?php endif; ?><?php if($item['event_location']): ?><span><?= $ic($I['pin'],15) ?><?= html_escape($item['event_location']) ?></span><?php endif; ?></div><?php endif; ?>
     <p><?= html_escape(mb_strimwidth($excerpt,0,170,'…')) ?></p>
     <a class="anx-more" href="<?= $url ?>">Lihat Detail <?= $ic($I['arrow'],16) ?></a>
    </div>
   </article>
   <?php endforeach; ?>
  </div><?php else: ?><div class="anx-empty"><span><?= $ic($I['cal'],40) ?></span><strong>Agenda tidak ditemukan</strong><p>Belum ada agenda terbit atau tidak ada hasil yang sesuai.</p><a href="<?= site_url('agenda') ?>">Tampilkan semua agenda</a></div><?php endif; ?>
 </section>
 <?php $this->load->view('citizen/red_deco'); ?>
</main>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
