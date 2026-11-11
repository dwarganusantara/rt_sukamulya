<?php
/*
 * Header portal warga (merah, dua tingkat): logo & nama aplikasi, lokasi RT, lonceng, akun, navigasi utama.
 * Variabel: $settings, opsional $shell_active (kunci menu yang aktif, mis. 'home'/'profile'; '' = tidak ada).
 */
$this->load->helper(array('dashboard_ui', 'citizen_shell'));
$sh = citizen_shell_vars($settings ?? array(), array('citizen' => $citizen ?? NULL, 'profile_photo' => $profile_photo ?? NULL, 'notify' => $notify ?? NULL, 'dashboard_header' => $dashboard_header ?? NULL));
$sh_active = $shell_active ?? '';
$sh_icons = array('home' => 'home', 'profile' => 'profile', 'letters' => 'letters', 'dues' => 'dues', 'gallery' => 'gallery', 'events' => 'events', 'contact_rt' => 'contact_rt', 'announcements' => 'megaphone', 'info' => 'info');
$sh_n = $sh['notify'];
// Mode tamu (belum login): menu publik + tombol "Portal Warga", tanpa lonceng & akun.
$sh_guest = !$this->session->userdata('citizen_id');
if ($sh_guest) {
    if (!isset($this->feature_gate)) $this->load->library('Feature_gate');
    $sh['nav'] = array();
    foreach (array('home' => array('', 'Beranda'), 'profile' => array('profil', 'Profil'), 'announcements' => array('pengumuman', 'Pengumuman'), 'events' => array('agenda', 'Agenda'), 'info' => array('informasi', 'Informasi'), 'gallery' => array('galeri', 'Galeri'), 'contact_rt' => array('kontak', 'Kontak')) as $k => $it) {
        if ($it[0] === '' || $this->feature_gate->link_ok($it[0], 'warga')) $sh['nav'][$k] = array('route' => $it[0], 'label' => $it[1]);
    }
}
$sh_svg = function ($d, $s = 24) { return '<svg class="dui-line" width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>'; };
$sh_extra = array('megaphone' => 'M3 10v4l3 .5L17 19V5L6 9.5zM17 9.5a3 3 0 0 1 0 5M7 15l1 5h3l-1-4', 'info' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 11v5M12 8h.01');
$sh_icon = function ($key, $s = 24) use ($sh_icons, $sh_extra, $sh_svg) { $n = $sh_icons[$key] ?? 'home'; return isset($sh_extra[$n]) ? $sh_svg($sh_extra[$n], $s) : dui_line($n, $s); };
?>
<header class="dwh"><svg class="dwh-ribbon" viewBox="0 0 900 90" preserveAspectRatio="none" aria-hidden="true"><path d="M260 0C430 60 640 82 900 30V0Z" fill="#ffffff" opacity=".10"/><path d="M380 0C540 44 700 58 900 14V0Z" fill="#ff3150" opacity=".35"/><path d="M470 0C600 30 740 40 900 6" fill="none" stroke="#fff" stroke-width="3" opacity=".55"/><path d="M330 0C500 52 690 70 900 22" fill="none" stroke="#fff" stroke-width="1.5" opacity=".35"/></svg>
 <div class="dwh-top dwb-wrap">
  <a class="dwh-brand" href="<?= site_url('warga') ?>"><img src="<?= base_url(html_escape($sh['logo'])) ?>" alt="Logo <?= html_escape($sh['app']) ?>"><span><b><?= html_escape($sh['app_l1']) ?></b><?php if ($sh['app_l2'] !== ''): ?><b><?= html_escape($sh['app_l2']) ?></b><?php endif; ?></span></a>
  <?php /* Kotak lokasi RT di header dihapus (permintaan desain) — identitas RT tetap tampil di konten halaman. */ ?>
  <div class="dwh-actions">
   <?php if ($sh_guest): ?>
   <a class="dwh-guest-bell" href="<?= site_url('pengumuman') ?>" aria-label="Lihat pengumuman terbaru" title="Pengumuman terbaru"><?= dui_line('bell', 24) ?></a>
   <a class="dwh-portal" href="<?= site_url('warga/login') ?>"><?= dui_line('profile', 20) ?><span>Portal Warga</span></a>
   <button type="button" class="dwh-burger" id="dwh-burger" aria-controls="dwh-nav" aria-expanded="false" aria-label="Buka menu"><?= dui_line('menu', 26) ?></button>
   <?php else: ?>
   <a class="dwh-bell" href="<?= site_url($sh_n['target']) ?>" aria-label="<?= $sh_n['total'] ? (int) $sh_n['total'] . ' pemberitahuan baru' : 'Tidak ada pemberitahuan baru' ?>" title="<?= html_escape(trim(($sh_n['chat'] ? $sh_n['chat'] . ' pesan baru dari pengurus. ' : '') . ($sh_n['dues'] ? $sh_n['dues'] . ' tagihan belum lunas.' : '')) ?: 'Tidak ada pemberitahuan baru') ?>"><?= dui_line('bell', 28) ?><?php if ($sh_n['total']): ?><b><?= $sh_n['total'] > 99 ? '99+' : (int) $sh_n['total'] ?></b><?php endif; ?></a>
   <span class="dwh-vsep" aria-hidden="true"></span>
   <details class="dwh-user">
    <summary><img src="<?= base_url(html_escape($sh['avatar'])) ?>" alt=""><span><b><?= html_escape($sh['name']) ?></b><small>Warga <?= html_escape($sh['rt_label']) ?></small></span><?= dui_line('chevron', 22) ?></summary>
    <div class="dwh-menu" role="menu">
     <a role="menuitem" href="<?= site_url('warga/profil') ?>"><?= dui_line('profile', 18) ?>Profil Saya</a>
     <a role="menuitem" href="<?= site_url('warga/password') ?>"><?= dui_line('key', 18) ?>Ubah Password</a>
     <a role="menuitem" class="out" href="<?= site_url('warga/logout') ?>"><?= dui_line('logout', 18) ?>Keluar</a>
    </div>
   </details>
   <button type="button" class="dwh-burger" id="dwh-burger" aria-controls="dwh-nav" aria-expanded="false" aria-label="Buka menu"><?= dui_line('menu', 26) ?></button>
   <?php endif; ?>
  </div>
 </div>
 <nav class="dwh-nav" id="dwh-nav" aria-label="Menu utama warga">
  <div class="dwb-wrap"><?php foreach ($sh['nav'] as $key => $h): $on = $key === $sh_active; ?><a href="<?= site_url($h['route']) ?>"<?= $on ? ' class="on" aria-current="page"' : '' ?>><?= $sh_icon($key, 24) ?><span><?= html_escape($h['label']) ?></span></a><?php endforeach; ?></div>
 </nav>
</header>
<script>
(function(){
 var b=document.getElementById('dwh-burger'),nav=document.getElementById('dwh-nav');
 if(b&&nav){b.addEventListener('click',function(){var open=nav.classList.toggle('open');b.setAttribute('aria-expanded',open?'true':'false');b.setAttribute('aria-label',open?'Tutup menu':'Buka menu');});}
 var u=document.querySelector('.dwh-user');
 if(u){document.addEventListener('click',function(e){if(!u.contains(e.target))u.open=false});document.addEventListener('keydown',function(e){if(e.key==='Escape'){u.open=false;if(nav)nav.classList.remove('open')}})}
})();
</script>
