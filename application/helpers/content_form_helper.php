<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function cfx_assets()
{
    $out = '';
    foreach (array('assets/admin-officials-v2.css', 'assets/admin-content-form.css') as $css) {
        $out .= '<link rel="stylesheet" href="' . base_url($css) . '?v=' . filemtime(FCPATH . $css) . '">';
    }
    return $out;
}

function cfx_script()
{
    $js = 'assets/admin-content-form.js';
    return '<script src="' . base_url($js) . '?v=' . filemtime(FCPATH . $js) . '"></script>';
}

function cfx_hero($variant, $icon, $title, $subtitle, array $crumbs, $back = '')
{
    $nav = '';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $index => $crumb) {
        if ($index) $nav .= '<i>›</i>';
        $nav .= $index === $last ? '<b>' . html_escape($crumb[0]) . '</b>' : '<a href="' . site_url($crumb[1]) . '">' . html_escape($crumb[0]) . '</a>';
    }
    return '<section class="off-hero cfx-hero cfx-hero-' . $variant . '"><div class="off-hero-copy"><nav class="off-crumb">' . $nav . '</nav><div class="off-hero-title"><span class="off-hero-icon">' . official_svg($icon) . '</span><div><h1>' . html_escape($title) . '</h1><p>' . html_escape($subtitle) . '</p></div></div></div>'
        . ($back ? '<a class="cfx-back" href="' . site_url($back) . '">' . official_svg('back') . 'Kembali</a>' : '') . '</section>';
}

function cfx_head($icon, $tint, $title, $subtitle = '')
{
    return '<header class="off-card-head"><span class="off-tile off-tile-large tint-' . $tint . '">' . official_svg($icon) . '</span><div><h2>' . $title . '</h2>' . ($subtitle ? '<p>' . html_escape($subtitle) . '</p>' : '') . '</div></header>';
}

function cfx_label($for, $text, $required = FALSE, $optional = FALSE)
{
    return '<label class="cfx-label" for="' . $for . '">' . html_escape($text) . ($required ? ' <em>*</em>' : '') . ($optional ? ' <span>(Opsional)</span>' : '') . '</label>';
}

// Kolom dengan ikon di dalam bingkai input.
function cfx_control($icon, $field, $textarea = FALSE)
{
    $mark = strlen($icon) === 1 ? '<b>' . $icon . '</b>' : official_svg($icon);
    return '<div class="cfx-control' . ($textarea ? ' cfx-control-area' : '') . '"><span>' . $mark . '</span>' . $field . '</div>';
}

// Baris dengan kotak ikon di sisi kiri kolom.
function cfx_side($icon, $tint, $content)
{
    return '<div class="cfx-side"><span class="off-tile off-tile-side tint-' . $tint . '">' . official_svg($icon) . '</span><div>' . $content . '</div></div>';
}

function cfx_text_limit($value, $limit)
{
    return max($limit, mb_strlen((string) $value));
}

function cfx_counter($id, $value, $limit)
{
    return '<small class="cfx-counter" data-counter="' . $id . '"><span>' . mb_strlen((string) $value) . '</span>/' . $limit . '</small>';
}

function cfx_upload($variant, $name, $thumbTint, $title, $hint, $pick, $saved = FALSE, $required = FALSE)
{
    $id = 'cfx-file-' . $name;
    $hint = $saved ? $hint . ' Kosongkan untuk mempertahankan gambar lama.' : $hint;
    $text = '<span class="cfx-zone-text"><b>' . html_escape($title) . '</b><small data-file-name>' . html_escape($hint) . '</small></span>';
    $button = '<label class="cfx-pick" for="' . $id . '">' . official_svg('upload') . html_escape($pick) . '</label>';
    $zone = '<label class="cfx-zone" for="' . $id . '">' . ($variant === 'thumb' ? '<span class="off-tile off-tile-large cfx-thumb tint-' . $thumbTint . '">' . official_svg('image') . '</span>' : official_svg('upload')) . $text . '</label>';
    return '<div class="cfx-upload cfx-upload-' . $variant . '"><input id="' . $id . '" type="file" name="' . $name . '" accept="' . image_accept() . '"' . ($required ? ' required' : '') . '>' . $zone . $button . '</div>';
}

function cfx_switch($name, $checked, $title, $hint)
{
    return '<label class="off-switch cfx-switch"><input type="checkbox" name="' . $name . '" value="1"' . ($checked ? ' checked' : '') . '><i></i><span><b>' . html_escape($title) . '</b><small>' . html_escape($hint) . '</small></span></label>';
}

function cfx_tips($title, $subtitle, array $items)
{
    $rows = '';
    foreach ($items as $item) $rows .= '<li>' . official_svg('check-circle') . '<span>' . html_escape($item) . '</span></li>';
    return '<section class="cfx-card"><header class="off-card-head"><span class="off-tile off-tile-large tint-orange">' . official_svg('bulb') . '</span><div><h2>' . html_escape($title) . '</h2>' . ($subtitle ? '<p>' . html_escape($subtitle) . '</p>' : '') . '</div></header><ul class="cfx-tips">' . $rows . '</ul></section>';
}

function cfx_actions($save, $cancel)
{
    return '<div class="off-form-actions cfx-actions"><button class="off-submit" type="submit">' . official_svg('save') . html_escape($save) . '</button><a class="off-cancel" href="' . site_url($cancel) . '">' . official_svg('close') . 'Batal</a></div>';
}

function cfx_date($date)
{
    static $months = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
    $time = strtotime((string) $date) ?: time();
    return (int) date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
}
