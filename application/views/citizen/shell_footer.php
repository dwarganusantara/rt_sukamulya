<?php
/* Footer portal warga: ilustrasi kampung + pita merah berisi nama aplikasi, lokasi RT, dan slogan (dari Pengaturan). */
$this->load->helper(array('dashboard_ui', 'citizen_shell'));
$sh = citizen_shell_vars($settings ?? array());
$sp = $sh['slogan_parts'];
?>
<?php if (!$sh['custom_footer']): ?>
<footer class="dwfoot2">
 <div class="dwfoot2-stage">
  <img class="dwfoot2-art" src="<?= base_url(html_escape($sh['footer_art'])) ?>" alt="" loading="lazy" decoding="async">
  <div class="dwfoot2-band dwb-wrap">
   <span class="dwfoot2-brand"><img src="<?= base_url(html_escape($sh['logo'])) ?>" alt=""><b><?= html_escape($sh['app_l1']) ?><?php if ($sh['app_l2'] !== ''): ?> <br><?= html_escape($sh['app_l2']) ?><?php endif; ?></b></span>
   <span class="dwfoot2-rt"><?= dui_line('pin', 30) ?><b><?= html_escape($sh['rt_label']) ?> <br><?= html_escape($sh['rt_area']) ?></b></span>
   <span class="dwfoot2-slogan"><?= dui_line('leaf', 30) ?><i><?= html_escape($sp[0] . (isset($sp[1]) ? ',' : '')) ?><?php if (isset($sp[1])): ?><br><em><?= html_escape($sp[1]) ?></em><?php endif; ?></i></span>
  </div>
 </div>
</footer>
<?php else: ?>
<footer class="dwfoot">
 <img class="dwfoot-art" src="<?= base_url(html_escape($sh['footer_art'])) ?>" alt="" loading="lazy" decoding="async">
 <svg class="dwfoot-wave" viewBox="0 0 1600 120" preserveAspectRatio="none" aria-hidden="true">
  <path fill="#ffffff" d="M0 54C260 30 520 20 800 26s560 20 800 4v90H0Z"/>
  <path fill="#f41532" d="M0 64C270 40 530 30 800 36s560 22 800 8v10C1340 70 1080 52 800 48S260 56 0 78Z"/>
  <path fill="url(#dwfoot-g)" d="M0 76C270 54 530 46 800 50s560 16 800 2v68H0Z"/>
  <defs><linearGradient id="dwfoot-g" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#7a0019"/><stop offset=".5" stop-color="#ad0029"/><stop offset="1" stop-color="#7a0019"/></linearGradient></defs>
 </svg>
 <div class="dwfoot-inner dwb-wrap">
  <span class="dwfoot-brand"><img src="<?= base_url(html_escape($sh['logo'])) ?>" alt=""><b><?= html_escape($sh['app']) ?></b></span>
  <span class="dwfoot-rt"><?= dui_line('pin', 18) ?><?= html_escape(trim($sh['rt_label'] . ' ' . $sh['rt_area'])) ?></span>
  <span class="dwfoot-slogan"><?= dui_line('leaf', 20) ?><i><?= html_escape($sh['slogan']) ?></i></span>
 </div>
</footer>
<?php endif; ?>
