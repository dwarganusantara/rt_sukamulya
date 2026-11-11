<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 06 — tema warna dinamis.
 * CSS memakai var(--theme-accent, #f0501a) dan var(--theme-navy, #082654); tanpa pengaturan,
 * tampilan identik dengan desain asli. Nilai hanya diterima dalam format #rrggbb (tanpa CSS bebas).
 */

const THEME_DEFAULT_ACCENT = '#f0501a';
const THEME_DEFAULT_NAVY = '#082654';

function theme_hex($value)
{
    $value = strtolower(trim((string) $value));
    return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : '';
}

/** Rasio kontras WCAG antara dua warna #rrggbb. */
function theme_contrast($a, $b)
{
    $lum = function ($hex) {
        $out = 0;
        foreach (array(1 => 0.2126, 3 => 0.7152, 5 => 0.0722) as $pos => $weight) {
            $c = hexdec(substr($hex, $pos, 2)) / 255;
            $out += $weight * ($c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4));
        }
        return $out;
    };
    $l1 = $lum($a); $l2 = $lum($b);
    return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
}

/**
 * Validasi pasangan warna. Aksen dipakai sebagai latar tombol berteks putih (min. 3:1, setara desain asli),
 * navy dipakai sebagai warna teks di atas putih (min. 4.5:1, WCAG AA).
 * @return string pesan error, '' bila valid
 */
function theme_validate($accent, $navy)
{
    if ($accent === '' || $navy === '') return 'Warna tema harus berformat #rrggbb.';
    if (theme_contrast($accent, '#ffffff') < 3) return 'Warna aksen terlalu terang: teks putih di atas tombol tidak terbaca (kontras minimal 3:1).';
    if (theme_contrast($navy, '#ffffff') < 4.5) return 'Warna utama terlalu terang untuk teks (kontras minimal 4.5:1 terhadap putih).';
    return '';
}

/** Tag <style> untuk <head>; kosong bila tema default/tidak valid. */
function theme_style($settings)
{
    $accent = theme_hex($settings['theme_accent'] ?? '');
    $navy = theme_hex($settings['theme_navy'] ?? '');
    $vars = '';
    if ($accent && $accent !== THEME_DEFAULT_ACCENT) $vars .= '--theme-accent:' . $accent . ';';
    if ($navy && $navy !== THEME_DEFAULT_NAVY) $vars .= '--theme-navy:' . $navy . ';';
    return $vars === '' ? '' : '<style id="rt-theme">:root{' . $vars . '}</style>';
}
