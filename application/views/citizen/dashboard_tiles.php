<?php
/*
 * Tile heksagon menu warga. Dipakai dashboard warga (layout honeycomb) dan pratinjau admin (grid).
 * Variabel: $dashboard_items, opsional $dashboard_layout ('honey'|'grid'), $dashboard_empty_message.
 */
$this->load->helper('dashboard_ui');
$layout = $dashboard_layout ?? 'grid';
$hex_outer = dui_hex_path(200, 230, 20, TRUE);
$hex_mid = dui_hex_path(190, 219, 18, TRUE);
$hex_face = dui_hex_path(178, 206, 16, TRUE);
if (empty($GLOBALS['dwi_defs_done'])): $GLOBALS['dwi_defs_done'] = TRUE;
 $tones = array('maroon' => array('#e0215a', '#8d0022'), 'mint' => array('#effffd', '#b9f1ec'), 'orange' => array('#ffb238', '#ff7a00'), 'lavender' => array('#f6f0ff', '#d9c7ff'),
  'navy' => array('#1f6fd1', '#06245e'), 'cream' => array('#fffaf4', '#ffe2c8'), 'cyan' => array('#eafffe', '#b2efeb'), 'peach' => array('#fff4ea', '#ffd7b8'), 'sky' => array('#eef6ff', '#c9e2ff'),
  'rose' => array('#fff0f4', '#ffcfdc'), 'lilac' => array('#f6f0ff', '#d9c7ff')); ?>
<svg class="dwi-defs" width="0" height="0" aria-hidden="true" focusable="false"><defs>
 <?php foreach ($tones as $k => $c): ?><linearGradient id="dwi-g-<?= $k ?>" x1="0" y1="0" x2=".35" y2="1"><stop offset="0" stop-color="<?= $c[0] ?>"/><stop offset="1" stop-color="<?= $c[1] ?>"/></linearGradient><?php endforeach; ?>
 <linearGradient id="dwi-gloss" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".75"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
 <clipPath id="dwi-clip"><path transform="translate(11 11)" d="<?= $hex_face ?>"/></clipPath>
</defs></svg>
<template id="dwi-hex-tpl"><svg class="dwi-hex" viewBox="0 0 200 230" aria-hidden="true" focusable="false"><path class="dwi-hex-edge" d="<?= $hex_outer ?>"/><path class="dwi-hex-ring" transform="translate(5 5)" d="<?= $hex_mid ?>"/><path class="dwi-hex-face" transform="translate(11 11)" d="<?= $hex_face ?>"/><g clip-path="url(#dwi-clip)"><ellipse class="dwi-hex-gloss" cx="100" cy="44" rx="92" ry="58"/></g></svg></template>
<?php endif;
$hex = '<svg class="dwi-hex" viewBox="0 0 200 230" aria-hidden="true" focusable="false"><path class="dwi-hex-edge" d="' . $hex_outer . '"/><path class="dwi-hex-ring" transform="translate(5 5)" d="' . $hex_mid . '"/><path class="dwi-hex-face" transform="translate(11 11)" d="' . $hex_face . '"/><g clip-path="url(#dwi-clip)"><ellipse class="dwi-hex-gloss" cx="100" cy="44" rx="92" ry="58"/></g></svg>';
$items = array_values((array) $dashboard_items);
$n = count($items);
// Honeycomb: baris atas ±5/12 bagian, baris bawah sisanya (12 menu → 5 + 7).
$rows = $layout === 'honey' && $n > 3 ? array(array_slice($items, 0, max(1, (int) floor($n * 5 / 12))), array_slice($items, max(1, (int) floor($n * 5 / 12)))) : array($items);
$tile = function ($item) use ($hex) {
    return '<a class="dwi-tile tone-' . html_escape($item['color']) . '" href="' . site_url($item['route']) . '" data-menu-key="' . html_escape($item['key']) . '" data-icon="' . html_escape($item['icon']) . '" aria-label="' . html_escape($item['label']) . '">'
        . $hex . '<span class="dwi-icon" aria-hidden="true">' . dui_icon($item['icon']) . '</span><strong>' . html_escape($item['label']) . '</strong></a>';
};
?>
<nav class="dwi-grid<?= $layout === 'honey' ? ' dwi-honey' : '' ?>" aria-label="Layanan warga">
<?php foreach ($items as $item) echo $tile($item); ?>
<?php if (!$items): ?><p class="dwi-empty" role="status"><?php if (!empty($dashboard_empty_message)): ?><?= html_escape($dashboard_empty_message) ?><?php else: ?>Belum ada layanan yang tersedia. Hubungi <a href="<?= site_url('kontak') ?>">pengurus RT</a>.<?php endif; ?></p><?php endif; ?>
</nav>
