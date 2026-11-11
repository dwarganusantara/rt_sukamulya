<?php
/*
 * Galeri Kegiatan — desain merah (komponen sama dengan halaman Pengumuman: assets/citizen-red.css, .anx-*).
 * Header merah untuk warga maupun tamu (mode tamu: menu publik + "Portal Warga"). Lightbox foto dipertahankan.
 */
$title='Galeri Kegiatan | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='gallery';
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$labels=array('kerja-bakti'=>'Kerja Bakti','lomba'=>'Lomba','sosial'=>'Sosial','keagamaan'=>'Keagamaan','rutin'=>'Kegiatan Rutin','lainnya'=>'Lainnya');
$ic=function($d,$s=16){return '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$d.'"/></svg>';};
$I=array(
 'all'=>'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
 'kerja-bakti'=>'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10ZM2 21c0-3 1.9-5.4 5.1-6',
 'lomba'=>'M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3',
 'sosial'=>'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c.6-3.6 3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c2 .7 3.2 2.5 3.5 5.2',
 'keagamaan'=>'M3 21h18M5 21V11l7-5 7 5v10M10 21v-5h4v5M12 3v3',
 'rutin'=>'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5M8 14h2M12 14h2M16 14h.01',
 'lainnya'=>'M5 12h.01M12 12h.01M19 12h.01',
 'search'=>'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5','cal'=>'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5','back'=>'M19 12H5M11 18l-6-6 6-6','arrow'=>'M5 12h14M13 6l6 6-6 6',
 'image'=>'M3 5h18v14H3zM3 16l5-5 4 4 3-3 6 6M15 9h.01');
$months=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$date_label=function($value)use($months){$time=strtotime($value);return date('d',$time).' '.$months[(int)date('n',$time)].' '.date('Y',$time);};
$rt=trim(($settings['brand_label']??'RT 009/008').' '.($settings['brand_subtitle']??'Suka Mulya IV'));
$hero=trim((string)($settings['gallery_page_image']??''))?:'assets/mockup/gallery-hero-v2.png';
$is_citizen=(bool)$this->session->userdata('citizen_id');
$qs=function($cat)use($query){$p=array();if($cat!=='')$p[]='kategori='.$cat;if($query!=='')$p[]='q='.rawurlencode($query);return site_url('galeri').($p?'?'.implode('&',$p):'');};
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="gallery-page anx-page glx-page crd-page">
 <section class="anx-hero">
  <span class="anx-hero-art" aria-hidden="true"><img src="<?= base_url(html_escape($hero)) ?>" alt=""></span>
  <svg class="anx-hero-wave" viewBox="0 0 1600 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V40C220 96 420 110 700 84S1260 30 1600 70v50Z" fill="#fff"/><path d="M0 52C230 104 430 116 700 92S1260 40 1600 80v10C1260 52 980 102 700 104S220 112 0 66Z" fill="#d9002c"/><path d="M0 0v34C120 70 220 84 320 82 200 66 90 36 0 0Z" fill="#e3062f" opacity=".85"/></svg>
  <div class="anx-wrap anx-hero-copy">
   <a class="anx-back" href="<?= site_url($is_citizen?'warga':'') ?>"><?= $ic($I['back'],20) ?>Kembali</a>
   <span class="anx-eyebrow">KABAR LINGKUNGAN</span>
   <h1 class="glx-title">Galeri <b>Kegiatan</b></h1>
   <p>Dokumentasi berbagai kegiatan dan momen<br><?= html_escape($rt) ?>.</p>
  </div>
 </section>
 <section class="anx-wrap anx-content">
  <form class="anx-tools" method="get" action="<?= site_url('galeri') ?>">
   <?php if($category!==''): ?><input type="hidden" name="kategori" value="<?= html_escape($category) ?>"><?php endif; ?>
   <label class="anx-search"><?= $ic($I['search'],22) ?><input type="search" name="q" value="<?= html_escape($query) ?>" placeholder="Cari foto kegiatan..." aria-label="Cari foto"><button type="submit">Cari</button></label>
   <nav class="anx-filters" aria-label="Filter galeri">
    <a class="<?= $category===''?'active':'' ?>" href="<?= $qs('') ?>"><?= $ic($I['all'],18) ?>Semua</a>
    <?php foreach($labels as $key=>$label): ?><a class="<?= $category===$key?'active':'' ?>" href="<?= $qs($key) ?>"><?= $ic($I[$key],18) ?><?= $label ?></a><?php endforeach; ?>
   </nav>
  </form>
  <?php if($items): ?><div class="anx-grid glx-grid">
   <?php foreach($items as $item): $cat=isset($labels[$item['category']])?$item['category']:'lainnya'; ?>
   <article class="anx-card gallery-public-card">
    <button class="anx-image gallery-photo-button" type="button" data-image="<?= base_url(html_escape($item['image'])) ?>" data-title="<?= html_escape($item['title']) ?>"><img src="<?= base_url(html_escape($item['image'])) ?>" alt="<?= html_escape($item['title']) ?>" loading="lazy"><b class="anx-cat glx-cat is-<?= $cat ?>"><?= $ic($I[$cat],16) ?><?= $labels[$cat] ?></b></button>
    <div class="anx-body"><time datetime="<?= html_escape(substr($item['created_at'],0,10)) ?>"><?= $ic($I['cal'],16) ?><?= $date_label($item['created_at']) ?></time><h2><?= html_escape($item['title']) ?></h2><p><?= html_escape(mb_strimwidth(trim(preg_replace('/\s+/u',' ',strip_tags($item['body']))),0,150,'…')) ?></p><button class="anx-more gallery-open" type="button">Lihat Foto <?= $ic($I['arrow'],16) ?></button></div>
   </article>
   <?php endforeach; ?>
  </div>
  <div class="glx-end"><span><?= $ic($I['image'],34) ?></span><div><strong>Semua dokumentasi sudah ditampilkan.</strong><small>Foto atau video kegiatan RT akan terus diperbarui oleh pengurus RT.</small></div></div>
  <?php else: ?><div class="anx-empty"><span><?= $ic($I['image'],40) ?></span><strong>Belum ada galeri kegiatan<?= $query!==''||$category!==''?' yang sesuai.':'.' ?></strong><p>Foto kegiatan akan ditampilkan di sini setelah diunggah oleh pengurus RT.</p><?php if($query!==''||$category!==''): ?><a href="<?= site_url('galeri') ?>">Tampilkan semua</a><?php endif; ?></div><?php endif; ?>
 </section>
 <div class="gallery-lightbox" id="gallery-lightbox" hidden role="dialog" aria-modal="true" aria-labelledby="gallery-lightbox-title"><button class="gallery-lightbox-close" type="button" aria-label="Tutup">×</button><figure><img src="" alt=""><figcaption id="gallery-lightbox-title"></figcaption></figure></div>
 <?php $this->load->view('citizen/red_deco'); ?>
</main>
<script>(function(){const box=document.getElementById('gallery-lightbox'),image=box.querySelector('img'),caption=box.querySelector('figcaption'),close=()=>{box.hidden=true;image.src='';document.body.style.overflow=''};document.querySelectorAll('.gallery-public-card').forEach(card=>{const source=card.querySelector('.gallery-photo-button'),open=()=>{image.src=source.dataset.image;image.alt=source.dataset.title;caption.textContent=source.dataset.title;box.hidden=false;document.body.style.overflow='hidden';box.querySelector('.gallery-lightbox-close').focus()};source.addEventListener('click',open);card.querySelector('.gallery-open').addEventListener('click',open)});box.querySelector('.gallery-lightbox-close').addEventListener('click',close);box.addEventListener('click',e=>{if(e.target===box)close()});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!box.hidden)close()})})();</script>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
