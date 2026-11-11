<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Komponen dashboard warga: heksagon bersudut bulat, ikon SVG berwarna (fallback buatan sendiri untuk ikon 3D),
 * dan ikon outline header (gaya Lucide, digambar ulang; tanpa CDN).
 * Catatan: ikon di sini adalah pengganti sementara aset 3D berlisensi. Untuk mengganti, simpan PNG/WebP
 * transparan di assets/img/dashboard/icons/<nama-ikon>.webp — dui_icon() otomatis memakainya.
 */

if (!function_exists('dui_hex_path')) {
    /** Path heksagon bersudut bulat. $pointy = TRUE: puncak di atas (untuk sarang lebah rapat). */
    function dui_hex_path($w, $h, $r, $pointy = FALSE)
    {
        $pts = $pointy
            ? array(array($w / 2, 0), array($w, $h * .25), array($w, $h * .75), array($w / 2, $h), array(0, $h * .75), array(0, $h * .25))
            : array(array($w * .25, 0), array($w * .75, 0), array($w, $h / 2), array($w * .75, $h), array($w * .25, $h), array(0, $h / 2));
        $n = count($pts); $d = '';
        for ($i = 0; $i < $n; $i++) {
            $p = $pts[$i]; $prev = $pts[($i + $n - 1) % $n]; $next = $pts[($i + 1) % $n];
            $a = dui_toward($p, $prev, $r); $b = dui_toward($p, $next, $r);
            $d .= ($i === 0 ? 'M' : 'L') . round($a[0], 1) . ' ' . round($a[1], 1) . 'Q' . round($p[0], 1) . ' ' . round($p[1], 1) . ' ' . round($b[0], 1) . ' ' . round($b[1], 1);
        }
        return $d . 'Z';
    }
    function dui_toward($p, $q, $r)
    {
        $dx = $q[0] - $p[0]; $dy = $q[1] - $p[1]; $len = sqrt($dx * $dx + $dy * $dy) ?: 1;
        return array($p[0] + $dx / $len * $r, $p[1] + $dy / $len * $r);
    }
}

if (!function_exists('dui_line')) {
    /** Ikon outline putih untuk header (24px, stroke 2.1). */
    function dui_line($name, $size = 22)
    {
        static $p = array(
            'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>',
            'profile' => '<circle cx="12" cy="8" r="4.2"/><path d="M4 21c.9-4.2 4-6.4 8-6.4s7.1 2.2 8 6.4"/>',
            'letters' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6M9 9h2"/>',
            'dues' => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v3h-4a2 2 0 0 0 0 4h4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/>',
            'gallery' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="9.5" r="1.8"/><path d="m21 16-5-5-9 9"/>',
            'events' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01"/>',
            'contact_rt' => '<circle cx="9" cy="8" r="3.6"/><path d="M2.5 20c.6-3.5 3.1-5.4 6.5-5.4s5.9 1.9 6.5 5.4M16 4.6a3.6 3.6 0 0 1 0 6.8M18.5 14.8c1.8.7 2.8 2.4 3 5.2"/>',
            'bell' => '<path d="M6 9a6 6 0 1 1 12 0c0 6.5 2.5 8 2.5 8h-17S6 15.5 6 9"/><path d="M10.3 20.5a2 2 0 0 0 3.4 0"/>',
            'chevron' => '<path d="m6 9 6 6 6-6"/>', 'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>', 'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'gear' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>', 'key' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.3-9.3M16 7l3 3M14 9l2 2"/>',
            'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'leaf' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10Z"/><path d="M2 21c0-3 1.9-5.4 5.1-6"/>',
        );
        return '<svg class="dui-line" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$name] ?? $p['home']) . '</svg>';
    }
}

if (!function_exists('dui_icon')) {
    /** Ikon berwarna (gaya 3D) untuk menu heksagon. Memakai aset lokal bila ada. */
    function dui_icon($name)
    {
        foreach (array('webp', 'png', 'svg') as $ext) {
            $rel = 'assets/img/dashboard/icons/' . $name . '.' . $ext;
            if (is_file(FCPATH . $rel)) return '<img class="dui-icon-img" src="' . base_url($rel) . '?v=' . filemtime(FCPATH . $rel) . '" alt="" loading="lazy" decoding="async">';
        }
        static $n = 0; $n++; $u = 'i' . $n;
        $g = function ($id, $a, $b, $x2 = '0', $y2 = '1') use ($u) {return '<linearGradient id="' . $id . $u . '" x1="0" y1="0" x2="' . $x2 . '" y2="' . $y2 . '"><stop offset="0" stop-color="' . $a . '"/><stop offset="1" stop-color="' . $b . '"/></linearGradient>';};
        $f = function ($id) use ($u) {return 'url(#' . $id . $u . ')';};
        $shadow = '<ellipse cx="32" cy="59" rx="20" ry="3.2" fill="#000" opacity=".14"/>';
        switch ($name) {
            case 'person':
                $s = $g('sk', '#ffd9b8', '#f2a77a') . $g('sh', '#4aa3ff', '#1458c9') . $g('hr', '#3b2a22', '#120b08');
                $b = $shadow . '<path d="M10 58c1-12 10-19 22-19s21 7 22 19z" fill="' . $f('sh') . '"/><path d="M26 39h12l-6 8z" fill="#fff" opacity=".85"/><circle cx="32" cy="23" r="12" fill="' . $f('sk') . '"/><path d="M19.5 22c0-9 5.6-13.5 12.8-13.5S44.6 13 44.5 22c-2.5-4-6.5-5.6-12.5-5.6S22 18 19.5 22z" fill="' . $f('hr') . '"/><ellipse cx="27" cy="18" rx="5" ry="2.4" fill="#fff" opacity=".35"/>';
                break;
            case 'people':
                $s = $g('rf', '#ff5a5f', '#c8102e') . $g('pp', '#4aa3ff', '#1458c9') . $g('sk', '#ffd9b8', '#f2a77a');
                $b = $shadow . '<path d="M6 30 32 8l26 22-5 3L32 15 11 33z" fill="' . $f('rf') . '"/><path d="M14 31v-3l18-15 18 15v3" fill="#ffe4e6" opacity=".55"/>'
                    . '<circle cx="20" cy="36" r="5.5" fill="' . $f('sk') . '"/><circle cx="44" cy="36" r="5.5" fill="' . $f('sk') . '"/><circle cx="32" cy="33" r="6.2" fill="' . $f('sk') . '"/>'
                    . '<path d="M11 56c.5-7 4.3-11 9-11s8.5 4 9 11z" fill="' . $f('pp') . '"/><path d="M35 56c.5-7 4.3-11 9-11s8.5 4 9 11z" fill="' . $f('pp') . '"/><path d="M22 57c.6-8 4.6-13 10-13s9.4 5 10 13z" fill="#0f4fb8"/>';
                break;
            case 'doc':
                $s = $g('pa', '#ffffff', '#e6ebf2') . $g('pe', '#ffcf3d', '#ff8a00') . $g('ln', '#9aa7b8', '#7b8798');
                $b = $shadow . '<rect x="13" y="6" width="34" height="46" rx="4" fill="#cfd6e0"/><rect x="11" y="4" width="34" height="46" rx="4" fill="' . $f('pa') . '"/><g stroke="' . $f('ln') . '" stroke-width="3" stroke-linecap="round"><path d="M18 15h20M18 22h20M18 29h14M18 36h10"/></g>'
                    . '<g transform="rotate(40 44 40)"><rect x="40" y="18" width="9" height="34" rx="2" fill="' . $f('pe') . '"/><path d="M40 52h9l-4.5 8z" fill="#f6d1a5"/><path d="M43 57h3l-1.5 3z" fill="#333"/><rect x="40" y="18" width="9" height="5" rx="2" fill="#ff5a7a"/></g>';
                break;
            case 'clock':
                $s = $g('cl', '#b07cff', '#6d28d9') . $g('fc', '#ffffff', '#ede4ff');
                $b = $shadow . '<path d="M12 30a20 20 0 1 1 6 14.5" fill="none" stroke="' . $f('cl') . '" stroke-width="7" stroke-linecap="round"/><path d="M5 26l7 9 8-8z" fill="#6d28d9"/><circle cx="32" cy="30" r="14" fill="' . $f('fc') . '"/><path d="M32 21v10l7 4" fill="none" stroke="#5b21b6" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>';
                break;
            case 'wallet':
                $s = $g('wl', '#34d399', '#047857') . $g('co', '#ffe27a', '#e0a100') . $g('fl', '#10b981', '#065f46');
                $b = $shadow . '<rect x="6" y="22" width="48" height="32" rx="7" fill="' . $f('wl') . '"/><path d="M6 30h48v4H6z" fill="#065f46" opacity=".35"/><rect x="38" y="33" width="18" height="12" rx="4" fill="' . $f('fl') . '"/><circle cx="45" cy="39" r="2.6" fill="#ffe27a"/>'
                    . '<circle cx="40" cy="16" r="11" fill="' . $f('co') . '" stroke="#c98a00" stroke-width="1.5"/><text x="40" y="20.5" font-family="Arial,sans-serif" font-weight="700" font-size="11" fill="#9a6400" text-anchor="middle">Rp</text>';
                break;
            case 'chart':
                $s = $g('b1', '#5ec8ff', '#1677e8') . $g('b2', '#ffe066', '#f5a300');
                $b = $shadow . '<rect x="9" y="32" width="11" height="22" rx="3" fill="' . $f('b1') . '"/><rect x="24" y="22" width="11" height="32" rx="3" fill="' . $f('b1') . '"/><rect x="39" y="10" width="11" height="44" rx="3" fill="' . $f('b2') . '"/><g fill="#fff" opacity=".35"><rect x="11" y="34" width="3" height="16" rx="1.5"/><rect x="26" y="24" width="3" height="26" rx="1.5"/><rect x="41" y="12" width="3" height="38" rx="1.5"/></g>';
                break;
            case 'megaphone':
                $s = $g('mg', '#ff5a5f', '#c8102e') . $g('mw', '#ffffff', '#e6ebf2');
                $b = $shadow . '<path d="M10 26v12l8 1 20 12V13L18 25z" fill="' . $f('mw') . '"/><path d="M38 13l12-6v50l-12-6z" fill="' . $f('mg') . '"/><rect x="6" y="25" width="10" height="14" rx="3" fill="' . $f('mg') . '"/><path d="M19 39l4 14h7l-4-13" fill="#c8102e"/><g stroke="#ff5a5f" stroke-width="3" stroke-linecap="round"><path d="M55 22l5-3M56 32h6M55 42l5 3"/></g>';
                break;
            case 'calendar':
                $s = $g('cr', '#ff5a5f', '#c8102e') . $g('cp', '#ffffff', '#eef1f5');
                $b = $shadow . '<rect x="7" y="11" width="50" height="44" rx="7" fill="' . $f('cp') . '" stroke="#e5e7eb"/><path d="M7 18a7 7 0 0 1 7-7h36a7 7 0 0 1 7 7v7H7z" fill="' . $f('cr') . '"/><rect x="17" y="5" width="6" height="13" rx="3" fill="#7f1d1d"/><rect x="41" y="5" width="6" height="13" rx="3" fill="#7f1d1d"/><path d="M21 39l7 7 15-15" fill="none" stroke="#dc2626" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>';
                break;
            case 'image':
                $s = $g('fr', '#a78bfa', '#6d28d9') . $g('sky', '#bfe3ff', '#7cc4ff') . $g('mt', '#34d399', '#059669');
                $b = $shadow . '<rect x="6" y="10" width="52" height="42" rx="7" fill="' . $f('fr') . '"/><rect x="11" y="15" width="42" height="32" rx="4" fill="' . $f('sky') . '"/><path d="M11 43l12-14 9 10 7-7 14 15v0a4 4 0 0 1-4 4H15a4 4 0 0 1-4-4z" fill="' . $f('mt') . '"/><circle cx="43" cy="23" r="5" fill="#ffd34d"/>';
                break;
            case 'shield':
                $s = $g('sd', '#5ec8ff', '#0b5ed7') . $g('sh2', '#ffffff', '#ffffff');
                $b = $shadow . '<path d="M32 4l22 8v15c0 15-9 25-22 30C19 52 10 42 10 27V12z" fill="' . $f('sd') . '"/><path d="M32 8l18 6.5V27c0 12-7 20.5-18 25" fill="#fff" opacity=".18"/><path d="M21 30l8 8 14-15" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>';
                break;
            case 'alert':
                $s = $g('al', '#ffc94d', '#ff7a00');
                $b = $shadow . '<path d="M28 8.5a4.6 4.6 0 0 1 8 0l21 37A4.6 4.6 0 0 1 53 52H11a4.6 4.6 0 0 1-4-6.5z" fill="' . $f('al') . '"/><path d="M29 10.5 9 46" stroke="#fff" stroke-width="2" opacity=".4"/><rect x="29" y="20" width="6" height="18" rx="3" fill="#fff"/><circle cx="32" cy="44" r="3.6" fill="#fff"/>';
                break;
            case 'chat':
                $s = $g('c1', '#b07cff', '#6d28d9') . $g('c2', '#e4d4ff', '#b794f6');
                $b = $shadow . '<path d="M24 22h28a6 6 0 0 1 6 6v14a6 6 0 0 1-6 6h-4v8l-9-8H24a6 6 0 0 1-6-6V28a6 6 0 0 1 6-6z" fill="' . $f('c2') . '"/><path d="M12 8h28a6 6 0 0 1 6 6v16a6 6 0 0 1-6 6H22l-10 9v-9a6 6 0 0 1-6-6V14a6 6 0 0 1 6-6z" fill="' . $f('c1') . '"/><g fill="#fff"><circle cx="17" cy="22" r="3"/><circle cx="26" cy="22" r="3"/><circle cx="35" cy="22" r="3"/></g>';
                break;
            default:
                $s = $g('df', '#ff8a3d', '#f0501a');
                $b = $shadow . '<circle cx="32" cy="30" r="22" fill="' . $f('df') . '"/><g transform="translate(18 16) scale(1.17)" fill="#fff">' . (function_exists('official_svg') ? preg_replace('#^<svg[^>]*>|</svg>$#', '', official_svg($name)) : '') . '</g>';
        }
        return '<svg class="dui-icon" viewBox="0 0 64 64" aria-hidden="true" focusable="false"><defs>' . $s . '</defs>' . $b . '</svg>';
    }
}

if (!function_exists('dui_title_case')) {
    /** "RT DIGITAL WARGA NUSANTARA" → "RT Digital Warga Nusantara" (singkatan ≤3 huruf tetap kapital). */
    function dui_title_case($text)
    {
        return implode(' ', array_map(function ($w) {return mb_strlen($w) <= 3 && mb_strtoupper($w) === $w ? $w : mb_convert_case(mb_strtolower($w), MB_CASE_TITLE);}, preg_split('/\s+/', trim((string) $text))));
    }
}
