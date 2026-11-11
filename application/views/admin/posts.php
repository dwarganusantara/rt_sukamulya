<?php $label=array('pengumuman'=>'Pengumuman','agenda'=>'Agenda Kegiatan','galeri'=>'Galeri Kegiatan')[$type]; ?>
<?php if($type==='pengumuman'):
 $this->load->helper(array('official_icons','page_ui'));
 $short=array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
 $st_label=array('published'=>array('Dipublikasikan','ok'),'pending'=>array('Menunggu','warn'),'draft'=>array('Draf','muted'));
 $base_url_q=function($extra){return site_url('admin/posts/pengumuman').'?'.http_build_query($extra);};
?>
<div class="pg">
 <?= pg_crumb(array(array('Kelola Konten', NULL), array('Pengumuman', NULL))) ?>
 <section class="pg-hero pg-hero-care pg-hero-board"><span class="pg-hero-ic"><?= official_svg('megaphone') ?></span><div class="pg-hero-tx"><span class="pg-kicker">Kelola Konten</span><h1>Pengumuman</h1><p>Publikasikan informasi penting untuk seluruh warga RT 009/008.</p></div><span class="pg-board-art" aria-hidden="true"></span></section>

 <section class="pg-stats" aria-label="Ringkasan pengumuman">
  <a class="pg-stat blue" href="<?= site_url('admin/posts/pengumuman') ?>"><span class="pg-stat-ic soft"><?= official_svg('list') ?></span><span class="pg-stat-tx"><strong><?= (int)$total ?></strong><small>Total Pengumuman</small></span></a>
  <a class="pg-stat green" href="<?= $base_url_q(array('status'=>'published')) ?>"><span class="pg-stat-ic soft"><?= official_svg('check-circle') ?></span><span class="pg-stat-tx"><strong><?= (int)$status_counts['published'] ?></strong><small>Dipublikasikan</small></span></a>
  <a class="pg-stat amber" href="<?= $base_url_q(array('status'=>'pending')) ?>"><span class="pg-stat-ic soft"><?= official_svg('clock') ?></span><span class="pg-stat-tx"><strong><?= (int)$status_counts['pending'] ?></strong><small>Menunggu</small></span></a>
  <a class="pg-stat red" href="<?= $base_url_q(array('status'=>'draft')) ?>"><span class="pg-stat-ic soft"><?= official_svg('close') ?></span><span class="pg-stat-tx"><strong><?= (int)$status_counts['draft'] ?></strong><small>Draf</small></span></a>
 </section>

 <form class="pg-filter" method="get" action="<?= site_url('admin/posts/pengumuman') ?>" role="search">
  <div class="pg-filter-row">
   <label class="pg-search"><?= official_svg('search') ?><span class="sr-only">Cari pengumuman</span><input type="search" name="q" value="<?= html_escape($q) ?>" maxlength="100" placeholder="Cari judul pengumuman..."></label>
   <label class="pg-sel"><?= official_svg('list') ?><span class="sr-only">Status</span><select name="status" onchange="this.form.submit()"><option value=""<?= $status===''?' selected':'' ?>>Semua Status</option><option value="published"<?= $status==='published'?' selected':'' ?>>Dipublikasikan</option><option value="pending"<?= $status==='pending'?' selected':'' ?>>Menunggu</option><option value="draft"<?= $status==='draft'?' selected':'' ?>>Draf</option></select></label>
   <label class="pg-sel"><?= official_svg('sort') ?><span class="sr-only">Urutan</span><select name="sort" onchange="this.form.submit()"><option value="newest"<?= $sort==='newest'?' selected':'' ?>>Terbaru</option><option value="oldest"<?= $sort==='oldest'?' selected':'' ?>>Terlama</option><option value="title"<?= $sort==='title'?' selected':'' ?>>Judul A–Z</option></select></label>
   <noscript><button class="pg-btn outline" type="submit">Terapkan</button></noscript>
   <a class="pg-btn solid" href="<?= site_url('admin/post/pengumuman') ?>"><?= official_svg('plus') ?>Tambah Pengumuman</a>
  </div>
 </form>

 <section class="pg-card">
  <table class="pg-table pg-posts"><thead><tr><th class="no">No</th><th>Pengumuman</th><th class="c">Status</th><th>Tanggal Publikasi</th><th class="c">Aksi</th></tr></thead><tbody>
  <?php foreach($posts as $i=>$post): $t=strtotime($post['created_at']); $st=$st_label[$post['status']]??array(ucfirst($post['status']),'muted'); ?>
   <tr>
    <td class="no"><?= $i+1 ?></td>
    <td class="lead"><span class="pg-post"><?php if($post['image']): ?><img src="<?= base_url(html_escape($post['image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="pg-tile" aria-hidden="true"><?= official_svg('megaphone') ?></span><?php endif; ?><span><a href="<?= site_url('admin/post/pengumuman/'.$post['id']) ?>"><?= html_escape($post['title']) ?></a><small><?= html_escape(character_limiter(trim(strip_tags($post['body'])),150)) ?></small></span></span></td>
    <td data-label="Status" class="c"><span class="pg-pill <?= $st[1] ?>"><?= $st[0] ?></span></td>
    <td data-label="Tanggal Publikasi"><span class="pg-when"><?= official_svg('calendar') ?><span class="pg-date"><?= $t?date('d',$t).' '.$short[(int)date('n',$t)].' '.date('Y',$t):'-' ?><small><?= $t?date('H:i',$t).' WIB':'' ?></small></span></span></td>
    <td class="acts"><div class="pg-acts">
     <a class="pg-edit" href="<?= site_url('admin/post/pengumuman/'.$post['id']) ?>"><?= official_svg('pencil') ?>Edit</a>
     <details class="pg-menu"><summary aria-label="Menu lain untuk <?= html_escape($post['title']) ?>"><?= official_svg('more-v') ?></summary><div>
      <a href="<?= site_url('admin/post/pengumuman/'.$post['id']) ?>"><?= official_svg('pencil') ?>Edit pengumuman</a>
      <?php if($post['status']==='published'): ?><a href="<?= site_url('pengumuman/'.$post['id']) ?>" target="_blank" rel="noopener"><?= official_svg('eye') ?>Lihat di website</a><?php endif; ?>
      <a href="<?= site_url('admin/post/pengumuman') ?>"><?= official_svg('plus') ?>Tambah pengumuman baru</a>
     </div></details>
    </div></td>
   </tr>
  <?php endforeach; if(!$posts): ?><tr><td colspan="5" class="pg-empty">Tidak ada pengumuman yang cocok dengan pencarian atau filter.</td></tr><?php endif; ?>
  </tbody></table>
  <div class="pg-foot pg-foot-in"><p>Menampilkan <b><?= count($posts) ?></b> dari <b><?= (int)$total ?></b> pengumuman</p></div>
 </section>
</div>
<?php elseif($type==='agenda'): ?>
<link rel="stylesheet" href="<?= base_url('assets/admin-agenda-v2.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-agenda-v2.css') ?>">
<div class="agenda-admin">
 <nav class="agenda-breadcrumb"><a href="<?= site_url('admin') ?>">Beranda</a><span>›</span><a href="<?= site_url('admin/posts/agenda') ?>">Konten</a><span>›</span><strong>Agenda Kegiatan</strong></nav>
 <section class="agenda-hero"><div class="agenda-title"><b>▦</b><div><span>KELOLA KONTEN</span><h1>Agenda Kegiatan</h1><p><?= (int)$total ?> konten tersedia dan dapat dikelola.</p></div></div><a href="<?= site_url('admin/post/agenda') ?>">＋ &nbsp; Tambah Konten</a></section>
 <form class="agenda-filter" method="get" action="<?= site_url('admin/posts/agenda') ?>">
  <label class="agenda-search"><span>⌕</span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari judul kegiatan..."></label>
  <label><span>▦</span><select name="date_scope"><option value="" <?= $date_scope===''?'selected':'' ?>>Semua Tanggal</option><option value="upcoming" <?= $date_scope==='upcoming'?'selected':'' ?>>Akan Datang</option><option value="past" <?= $date_scope==='past'?'selected':'' ?>>Sudah Lewat</option></select></label>
  <label><span>⌖</span><select name="location"><option value="">Semua Lokasi</option><?php foreach($locations as $row): ?><option value="<?= html_escape($row['event_location']) ?>" <?= $location===$row['event_location']?'selected':'' ?>><?= html_escape($row['event_location']) ?></option><?php endforeach; ?></select></label>
  <label><span>●</span><select name="status"><option value="" <?= $status===''?'selected':'' ?>>Semua Status</option><option value="published" <?= $status==='published'?'selected':'' ?>>Dipublikasikan</option><option value="pending" <?= $status==='pending'?'selected':'' ?>>Menunggu</option><option value="draft" <?= $status==='draft'?'selected':'' ?>>Draf</option></select></label>
  <label><span>⇅</span><select name="sort"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option><option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Terlama</option><option value="title" <?= $sort==='title'?'selected':'' ?>>Judul A–Z</option></select></label><button type="submit">Filter</button>
 </form>
 <section class="agenda-table-card"><div class="agenda-table-scroll"><table><thead><tr><th>Foto</th><th>Judul &amp; Keterangan</th><th>Jadwal</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
 <?php foreach($posts as $post): ?><tr><td><?php if($post['image']): ?><img src="<?= base_url(html_escape($post['image'])) ?>" alt=""><?php else: ?><span class="agenda-no-image">▧</span><?php endif; ?></td><td><strong><?= html_escape($post['title']) ?></strong><p><?= html_escape(character_limiter(trim(strip_tags($post['body'])),125)) ?></p></td><td><span class="agenda-date"><b>▦</b><span><?= $post['event_date']?date('d M Y',strtotime($post['event_date'])):'Tanggal belum diisi' ?><small><?= $post['event_time']?html_escape(substr($post['event_time'],0,5)).' WIB':'Waktu belum diisi' ?></small></span></span></td><td><span class="agenda-location">⌖ <span><?= html_escape($post['event_location']?:'Lokasi belum diisi') ?></span></span></td><td><span class="agenda-status <?= html_escape($post['status']) ?>"><i></i><?= $post['status']==='published'?'Dipublikasikan':($post['status']==='pending'?'Menunggu':'Draf') ?></span></td><td><div class="agenda-actions"><a class="edit" href="<?= site_url('admin/post/agenda/'.$post['id']) ?>">✎ &nbsp; Edit</a><a class="more" href="<?= site_url('admin/post/agenda/'.$post['id']) ?>" aria-label="Menu agenda <?= html_escape($post['title']) ?>">⋮</a></div></td></tr><?php endforeach; ?>
 <?php if(!$posts): ?><tr><td colspan="6" class="agenda-empty">Tidak ada agenda yang cocok dengan pencarian atau filter.</td></tr><?php endif; ?>
 </tbody></table></div><footer>Menampilkan <?= count($posts) ?> dari <?= (int)$total ?> konten</footer></section>
</div>
<?php else: ?>
<link rel="stylesheet" href="<?= base_url('assets/admin-gallery-v3.css') ?>?v=<?= filemtime(FCPATH.'assets/admin-gallery-v3.css') ?>">
<?php
$galleryCategory=function($post){$text=mb_strtolower(($post['title']??'').' '.($post['body']??''));if(preg_match('/kerja bakti|gotong royong|bersih lingkungan/u',$text))return array('Lingkungan','leaf');if(preg_match('/lomba|turnamen|kompetisi|pertandingan/u',$text))return array('Pendidikan','trophy');if(preg_match('/pengajian|keagamaan|maulid|isra|idul|kajian/u',$text))return array('Sosial','people');if(preg_match('/posyandu|santunan|donor|kesehatan/u',$text))return array('Kesehatan','heart');return array('Kegiatan','calendar');};
?>
<div class="gallery-admin-v3">
 <section class="gallery-hero-v3"><div class="gallery-title-v3"><b>◉</b><div><span>KELOLA KONTEN</span><h1>Galeri Kegiatan</h1><p><?= (int)$total ?> konten tersedia dan dapat dikelola.</p></div></div></section>
 <form class="gallery-filter-v3" method="get" action="<?= site_url('admin/posts/galeri') ?>"><label class="gallery-search-v3"><span>⌕</span><input type="search" name="q" value="<?= html_escape($q) ?>" placeholder="Cari judul kegiatan..."></label><label><span>▦</span><select name="date_scope"><option value="" <?= $date_scope===''?'selected':'' ?>>Semua Tanggal</option><option value="month" <?= $date_scope==='month'?'selected':'' ?>>Bulan Ini</option><option value="year" <?= $date_scope==='year'?'selected':'' ?>>Tahun Ini</option></select></label><label><span>●</span><select name="status"><option value="" <?= $status===''?'selected':'' ?>>Semua Status</option><option value="published" <?= $status==='published'?'selected':'' ?>>Dipublikasikan</option><option value="pending" <?= $status==='pending'?'selected':'' ?>>Menunggu</option><option value="draft" <?= $status==='draft'?'selected':'' ?>>Draf</option></select></label><label><span>⇅</span><select name="sort"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option><option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Terlama</option><option value="title" <?= $sort==='title'?'selected':'' ?>>Judul A–Z</option></select></label><button type="submit">Filter</button><a class="gallery-upload-v3" href="<?= site_url('admin/post/galeri') ?>">＋ &nbsp; Upload Foto</a></form>
 <div class="gallery-grid-v3"><?php foreach($posts as $post): $category=$galleryCategory($post); ?><article class="gallery-card-v3"><div class="gallery-photo-v3"><?php if($post['image']): ?><img src="<?= base_url(html_escape($post['image'])) ?>" alt="<?= html_escape($post['title']) ?>"><?php else: ?><span>▧</span><?php endif; ?><time>▦ &nbsp; <?= date('d M Y',strtotime($post['created_at'])) ?></time><a href="<?= site_url('admin/post/galeri/'.$post['id']) ?>" aria-label="Menu <?= html_escape($post['title']) ?>">⋮</a></div><div class="gallery-card-body-v3"><span class="gallery-category-v3 <?= $category[1] ?>"><?= html_escape($category[0]) ?></span><h2><?= html_escape($post['title']) ?></h2><p><?= html_escape(mb_strimwidth(trim(preg_replace('/\s+/u',' ',strip_tags($post['body']))),0,145,'…')) ?></p><footer><?php if($post['image']): ?><a class="view" href="<?= base_url(html_escape($post['image'])) ?>" target="_blank" rel="noopener">◉ &nbsp; Lihat</a><?php endif; ?><a class="edit" href="<?= site_url('admin/post/galeri/'.$post['id']) ?>">✎ &nbsp; Edit</a><a class="more" href="<?= site_url('admin/post/galeri/'.$post['id']) ?>" aria-label="Menu galeri">⋮</a></footer></div></article><?php endforeach; ?><?php if(!$posts): ?><div class="gallery-empty-v3">Tidak ada foto kegiatan yang cocok dengan pencarian atau filter.</div><?php endif; ?></div>
 <footer class="gallery-count-v3">Menampilkan <?= count($posts) ?> dari <?= (int)$total ?> konten</footer>
</div>
<?php endif; ?>
