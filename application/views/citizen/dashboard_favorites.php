<?php
/* Menu Favorit (maks. 4, per akun warga). Variabel: $dashboard_items, $dashboard_favorites. */
$this->load->helper(array('dashboard_ui', 'official_icons'));
$fav_tones = array('teal', 'orange', 'purple', 'teal');
$fav_hex = dui_hex_path(70, 80, 11, TRUE);
?>
<section class="dwf" aria-labelledby="dwf-title">
 <h2 id="dwf-title" class="dwf-title"><span class="dwf-star" aria-hidden="true">&#9733;</span>Menu Favorit</h2>
 <span class="dwf-sep" aria-hidden="true"></span>
 <nav class="dwf-list" aria-label="Menu favorit">
  <?php $i = 0; foreach ($dashboard_favorites as $key => $item): ?>
  <a class="dwf-item" href="<?= site_url($item['route']) ?>" data-menu-key="<?= html_escape($key) ?>">
   <span class="dwf-hex fav-<?= $fav_tones[$i % 4] ?>" aria-hidden="true"><svg class="dwf-bg" viewBox="0 0 70 80" focusable="false"><path d="<?= $fav_hex ?>"/><ellipse cx="35" cy="20" rx="27" ry="15" fill="#fff" opacity=".24"/></svg><?= official_svg($item['icon']) ?></span>
   <b><?= html_escape($item['label']) ?></b>
  </a>
  <?php $i++; endforeach; ?>
  <?php if (!$dashboard_favorites): ?><p class="dwf-empty">Belum ada menu favorit. Klik <b>Atur Menu</b> untuk memilih.</p><?php endif; ?>
 </nav>
 <span class="dwf-sep" aria-hidden="true"></span>
 <button type="button" class="dwf-edit" id="dwi-favorites-open" aria-haspopup="dialog"><?= dui_line('gear', 22) ?>Atur Menu</button>
</section>
<dialog id="dwi-favorites-dialog" class="dwf-dialog" aria-labelledby="dwi-picker-title">
 <form method="post" action="<?= site_url('warga/favorit/simpan') ?>">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
  <header><div><h2 id="dwi-picker-title">Atur Menu Favorit</h2><p>Pilih maksimal 4 menu. Gunakan panah untuk mengubah urutan.</p></div><button type="button" class="dwi-picker-close dwf-x" aria-label="Tutup"><?= dui_line('close', 20) ?></button></header>
  <p id="dwi-favorites-count" class="dwf-count" aria-live="polite"><?= count($dashboard_favorites) ?> dari 4 menu dipilih</p>
  <div class="dwi-picker-list">
  <?php foreach ($dashboard_favorites + $dashboard_items as $key => $item): ?>
   <div class="dwi-picker-row"><label><input type="checkbox" name="favorites[]" value="<?= html_escape($key) ?>"<?= isset($dashboard_favorites[$key]) ? ' checked' : '' ?>><span class="dwf-mini" aria-hidden="true"><?= dui_icon($item['icon']) ?></span><span><?= html_escape($item['label']) ?></span></label><button type="button" data-favorite-move="up" aria-label="Naikkan <?= html_escape($item['label']) ?>">&uarr;</button><button type="button" data-favorite-move="down" aria-label="Turunkan <?= html_escape($item['label']) ?>">&darr;</button></div>
  <?php endforeach; ?>
  <?php if (!$dashboard_items): ?><p>Belum ada layanan yang tersedia.</p><?php endif; ?>
  </div>
  <footer><button type="button" class="dwi-picker-close dwf-cancel">Batal</button><button type="submit" class="dwf-edit">Simpan Favorit</button></footer>
 </form>
</dialog>
<script src="<?= base_url('assets/dashboard-favorites.js') ?>?v=<?= filemtime(FCPATH . 'assets/dashboard-favorites.js') ?>" defer></script>
