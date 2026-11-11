<?php
/*
 * Daftar Pengumuman — desain merah. Header merah (citizen/shell_header) untuk warga maupun tamu
 * (tamu: menu publik + tombol "Portal Warga"). Identitas RT dari Pengaturan admin.
 */
$title='Pengumuman | '.($settings['site_name']??'Sukamulya');
$citizen_shell=TRUE; $shell_active='announcements';
$this->load->view('site/header',compact('title','settings','menu','citizen_shell','shell_active'));
$category_labels=array('kegiatan'=>'Kegiatan','informasi'=>'Informasi','iuran'=>'Iuran','lainnya'=>'Lainnya');
$ic=function($d,$s=16){return '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$d.'"/></svg>';};
$I=array('kegiatan'=>'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5','informasi'=>'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 11v5M12 8h.01','iuran'=>'M3 7h18v13H3zM3 7l13-4v4M16 13.5h.01','lainnya'=>'M5 12h.01M12 12h.01M19 12h.01','search'=>'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5','cal'=>'M4 6h16v15H4zM4 10h16M8 3v5M16 3v5','back'=>'M19 12H5M11 18l-6-6 6-6','arrow'=>'M5 12h14M13 6l6 6-6 6','mega'=>'M3 10v4l3 .5L17 19V5L6 9.5zM17 9.5a3 3 0 0 1 0 5M7 15l1 5h3l-1-4');
$months=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
$date_label=function($value)use($months){$time=strtotime($value);return date('d',$time).' '.$months[(int)date('n',$time)].' '.date('Y',$time);};
$rt=trim(($settings['brand_label']??'RT 009/008').' '.($settings['brand_subtitle']??'Suka Mulya IV'));
$hero=trim((string)($settings['announcement_page_image']??''))?:'assets/mockup/announcement-hero-v2.png';
$is_citizen=(bool)$this->session->userdata('citizen_id');
?>
<link rel="stylesheet" href="<?= base_url('assets/citizen-red.css') ?>?v=<?= filemtime(FCPATH.'assets/citizen-red.css') ?>">
<main class="announcement-page anx-page crd-page">
  <section class="anx-hero">
    <span class="anx-hero-art" aria-hidden="true"><img src="<?= base_url(html_escape($hero)) ?>" alt=""></span>
    <svg class="anx-hero-wave" viewBox="0 0 1600 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120V40C220 96 420 110 700 84S1260 30 1600 70v50Z" fill="#fff"/><path d="M0 52C230 104 430 116 700 92S1260 40 1600 80v10C1260 52 980 102 700 104S220 112 0 66Z" fill="#d9002c"/><path d="M0 0v34C120 70 220 84 320 82 200 66 90 36 0 0Z" fill="#e3062f" opacity=".85"/></svg>
    <div class="anx-wrap anx-hero-copy">
      <a class="anx-back" href="<?= site_url($is_citizen?'warga':'') ?>"><?= $ic($I['back'],20) ?>Kembali</a>
      <span class="anx-eyebrow">KABAR LINGKUNGAN</span>
      <h1>Pengumuman</h1>
      <p>Informasi terbaru dari <?= html_escape($rt) ?>.</p>
    </div>
  </section>
  <section class="anx-wrap anx-content">
    <form class="anx-tools" method="get" action="<?= site_url('pengumuman') ?>">
      <?php if($category!==''): ?><input type="hidden" name="kategori" value="<?= html_escape($category) ?>"><?php endif; ?>
      <label class="anx-search"><?= $ic($I['search'],22) ?><input type="search" name="q" value="<?= html_escape($query) ?>" placeholder="Cari pengumuman..." aria-label="Cari pengumuman"><button type="submit">Cari</button></label>
      <nav class="anx-filters" aria-label="Filter kategori">
        <a class="<?= $category===''?'active':'' ?>" href="<?= site_url('pengumuman').($query!==''?'?q='.rawurlencode($query):'') ?>">Semua</a>
        <?php foreach($category_labels as $key=>$label): ?><a class="<?= $category===$key?'active':'' ?>" href="<?= site_url('pengumuman').'?kategori='.$key.($query!==''?'&q='.rawurlencode($query):'') ?>"><?= $ic($I[$key],18) ?><?= $label ?></a><?php endforeach; ?>
      </nav>
    </form>
    <?php if($items): ?><div class="anx-grid">
      <?php foreach($items as $item): $excerpt=trim(preg_replace('/\s+/u',' ',str_replace(array('**','__'),' ',strip_tags($item['body'])))); $cat=isset($category_labels[$item['category']])?$item['category']:'lainnya'; ?>
        <article class="anx-card">
          <a class="anx-image" href="<?= site_url('pengumuman/'.(int)$item['id']) ?>"><?php if($item['image']): ?><img src="<?= base_url(html_escape($item['image'])) ?>" alt="<?= html_escape($item['title']) ?>" loading="lazy"><?php else: ?><span class="anx-image-fallback"><?= $ic($I['mega'],56) ?></span><?php endif; ?><b class="anx-cat is-<?= $cat ?>"><?= $ic($I[$cat],16) ?><?= $category_labels[$cat] ?></b></a>
          <div class="anx-body"><time datetime="<?= html_escape(substr($item['created_at'],0,10)) ?>"><?= $ic($I['cal'],16) ?><?= $date_label($item['created_at']) ?></time><h2><a href="<?= site_url('pengumuman/'.(int)$item['id']) ?>"><?= html_escape($item['title']) ?></a></h2><p><?= html_escape(mb_strimwidth($excerpt,0,200,'…')) ?></p><a class="anx-more" href="<?= site_url('pengumuman/'.(int)$item['id']) ?>">Baca Selengkapnya <?= $ic($I['arrow'],16) ?></a></div>
        </article>
      <?php endforeach; ?>
    </div><?php else: ?><div class="anx-empty"><span><?= $ic($I['mega'],40) ?></span><strong>Pengumuman tidak ditemukan</strong><p>Coba kata pencarian atau kategori yang berbeda.</p><a href="<?= site_url('pengumuman') ?>">Tampilkan semua pengumuman</a></div><?php endif; ?>
  </section>
  <?php $this->load->view('citizen/red_deco'); ?>
</main>
<?php $this->load->view('site/footer',array('settings'=>$settings,'citizen_shell'=>FALSE)); ?>
