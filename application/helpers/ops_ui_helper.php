<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* Komponen tampilan kecil untuk modul tambahan; mengikuti tema existing. */

function ops_valid_date($value)
{
    $d = DateTime::createFromFormat('!Y-m-d', (string) $value);
    return $d && $d->format('Y-m-d') === $value;
}

function ops_csrf()
{
    $CI =& get_instance();
    return '<input type="hidden" name="' . $CI->security->get_csrf_token_name() . '" value="' . $CI->security->get_csrf_hash() . '">';
}

function ops_e($value)
{
    return html_escape((string) $value);
}

function ops_money($value)
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function ops_date($value, $time = FALSE)
{
    if (!$value || strpos((string) $value, '0000') === 0) return '—';
    $months = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
    $ts = strtotime($value);
    return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts) . ($time ? ', ' . date('H:i', $ts) : '');
}

/** Status: teks + titik penanda, tidak mengandalkan warna saja. */
function ops_badge($status, $labels, $tones = array())
{
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    $tone = $tones[$status] ?? 'neutral';
    return '<span class="ops-badge ' . ops_e($tone) . '"><i aria-hidden="true"></i>' . ops_e($label) . '</span>';
}

function ops_options($options, $selected, $placeholder = NULL)
{
    $html = $placeholder !== NULL ? '<option value="">' . ops_e($placeholder) . '</option>' : '';
    foreach ($options as $value => $label) {
        $html .= '<option value="' . ops_e($value) . '"' . ((string) $selected === (string) $value ? ' selected' : '') . '>' . ops_e($label) . '</option>';
    }
    return $html;
}

/**
 * Field formulir. $type: text|textarea|select|date|number|month|time|file|checkbox|tel
 * $opts: required, options, placeholder, help, wide, min, max, step, accept
 */
function ops_field($name, $label, $type = 'text', $value = '', $opts = array())
{
    if(strpos(uri_string(),'admin')===0 && !isset($opts['placeholder']) && in_array($type,array('text','textarea','tel'),TRUE)){
        $hints=array('title'=>'Masukkan judul kegiatan atau dokumen...','location'=>'Masukkan lokasi kegiatan...','summary'=>'Tulis ringkasan hasil kegiatan atau rapat...','decisions'=>'Tulis keputusan yang disepakati...','name'=>'Masukkan nama layanan atau barang...','phone'=>'Contoh: 0812-3456-7890','address'=>'Masukkan alamat (opsional)','username'=>'Masukkan username...','full_name'=>'Masukkan nama lengkap...','note'=>'Masukkan keterangan (opsional)');
        $opts['placeholder']=$hints[$name]??('Masukkan '.mb_strtolower($label).'...');
    }
    $id = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);
    $req = !empty($opts['required']) ? ' required' : '';
    $star = !empty($opts['required']) ? ' <b class="ops-req" aria-hidden="true">*</b>' : '';
    $attrs = '';
    foreach (array('min', 'max', 'step', 'accept', 'maxlength', 'pattern', 'placeholder') as $a) if (isset($opts[$a])) $attrs .= ' ' . $a . '="' . ops_e($opts[$a]) . '"';
    $class = 'ops-field' . (!empty($opts['wide']) ? ' wide' : '');
    if ($type === 'checkbox') {
        return '<label class="' . $class . ' check"><input type="checkbox" name="' . ops_e($name) . '" value="1"' . ($value ? ' checked' : '') . '><span>' . ops_e($label) . '</span></label>';
    }
    $html = '<label class="' . $class . '" for="' . $id . '"><span>' . ops_e($label) . $star . '</span>';
    if ($type === 'textarea') {
        $html .= '<textarea id="' . $id . '" name="' . ops_e($name) . '" rows="' . (int) ($opts['rows'] ?? 4) . '"' . $req . $attrs . '>' . ops_e($value) . '</textarea>';
    } elseif ($type === 'select') {
        $html .= '<select id="' . $id . '" name="' . ops_e($name) . '"' . $req . '>' . ops_options($opts['options'] ?? array(), $value, $opts['placeholder_option'] ?? NULL) . '</select>';
    } elseif ($type === 'file') {
        $html .= '<input id="' . $id . '" type="file" name="' . ops_e($name) . '"' . $req . $attrs . '>';
    } else {
        $html .= '<input id="' . $id . '" type="' . ops_e($type) . '" name="' . ops_e($name) . '" value="' . ops_e($value) . '"' . $req . $attrs . '>';
    }
    // Decorate existing controls; names, validation and submitted values remain unchanged.
    $icons = array('title'=>'doc','name'=>'person','location'=>'pin','address'=>'pin','phone'=>'phone','participants'=>'people','participant_count'=>'person','summary'=>'doc','decisions'=>'check','agenda_id'=>'calendar','program_id'=>'flag','service_hours'=>'clock','note'=>'doc','username'=>'person','password'=>'key');
    $icon = $icons[$name] ?? (in_array($type,array('date','month','time'),TRUE)?'calendar':NULL);
    if ($icon && $type !== 'file' && strpos(uri_string(),'admin')===0) {
        $html = preg_replace('/(<(?:input|textarea|select)\b.*?<\/select>|<textarea\b.*?<\/textarea>|<input\b[^>]*>)/s', '<span class="ops-control">'.'<span class="ops-control-icon" aria-hidden="true">'.official_svg($icon).'</span>'.'$1'.($type==='password'?'<button type="button" class="ops-password-toggle" data-ops-password="'.ops_e($id).'" aria-label="Tampilkan password" aria-pressed="false">'.official_svg('eye').'</button>':'').'</span>', $html, 1);
    }
    if (!empty($opts['help'])) $html .= '<small>' . ops_e($opts['help']) . '</small>';
    return $html . '</label>';
}

/** Kepala halaman modul admin: judul, deskripsi, tombol aksi. */
function ops_hero($kicker, $title, $text, $actions = array(), $icon = 'doc')
{
    $route=uri_string();
    $art=NULL;
    foreach(array('admin/notulen'=>'minutes','admin/inventaris'=>'inventory','admin/belanja'=>'purchase','admin/kontak'=>'contacts','admin/arsip'=>'archive','admin/akun'=>'accounts','admin/field-tambahan'=>'fields','admin/antrean-email'=>'email','admin/audit'=>'audit','admin/rekonsiliasi'=>'reconciliation','admin/backup'=>'backup','admin/pengaturan/riwayat'=>'settings-history','admin/serah-terima'=>'handover') as $prefix=>$image)if(strpos($route,$prefix)===0){$art=$image;break;}
    $html = '<section class="ops-hero'.($art?' ops-hero-illustrated':'').'"><span class="ops-hero-icon" aria-hidden="true">' . official_svg($icon) . '</span><div><span class="ops-kicker">' . ops_e($kicker) . '</span><h1>' . ops_e($title) . '</h1><p>' . ops_e($text) . '</p></div>';
    if ($actions) {
        $html .= '<div class="ops-hero-actions">';
        foreach ($actions as $a) $html .= '<a class="ops-btn ' . ops_e($a[2] ?? '') . '" href="' . site_url($a[0]) . '">' . ops_e($a[1]) . '</a>';
        $html .= '</div>';
    }
    if($art)$html.='<img class="ops-hero-art" src="'.base_url('assets/img/admin-ops/'.$art.'.svg').'" alt="" aria-hidden="true" width="290" height="165">';
    return $html . '</section>';
}

/** Kartu statistik. $items: [label, value, hint, tone, url?] */
function ops_stats($items)
{
    $html = '<section class="ops-stats" aria-label="Ringkasan">';
    foreach ($items as $index=>$s) {
        $tag = !empty($s[4]) ? 'a href="' . site_url($s[4]) . '"' : 'article';
        $close = !empty($s[4]) ? 'a' : 'article';
        $icons=array('box','clock','alert','archive');
        $decoration=strpos(uri_string(),'admin')===0?'<span class="ops-stat-icon" aria-hidden="true">'.official_svg($s[5]??$icons[$index%4]).'</span>':'';
        $html .= '<' . $tag . ' class="ops-stat ' . ops_e($s[3] ?? 'blue') . '">'.$decoration.'<small>' . ops_e($s[0]) . '</small><strong>' . ops_e($s[1]) . '</strong><em>' . ops_e($s[2] ?? '') . '</em></' . $close . '>';
    }
    return $html . '</section>';
}

function ops_flash()
{
    $CI =& get_instance();
    $html = '';
    if ($e = $CI->session->flashdata('ops_error')) $html .= '<div class="error ops-flash" role="alert">' . ops_e($e) . '</div>';
    return $html;
}

/** Tab navigasi antar-submenu modul. $tabs: [url => label] */
function ops_tabs($tabs, $active)
{
    $html = '<nav class="ops-tabs" aria-label="Submenu">';
    foreach ($tabs as $url => $label) $html .= '<a href="' . site_url($url) . '"' . ($url === $active ? ' aria-current="page" class="active"' : '') . '>' . ops_e($label) . '</a>';
    return $html . '</nav>';
}

/** Empty table state with a real permitted action, never a placeholder link. */
function ops_empty_state($title,$description,$icon='doc',$route=NULL,$action=NULL,$focus=NULL)
{
 $html='<div class="ops-empty-state">'.official_svg($icon).'<strong>'.ops_e($title).'</strong><p>'.ops_e($description).'</p>';
 if($route)$html.='<a class="ops-btn" href="'.site_url($route).'">+ '.ops_e($action).'</a>';
 elseif($focus)$html.='<button type="button" class="ops-btn" data-ops-focus="'.ops_e($focus).'">+ '.ops_e($action).'</button>';
 return $html.'</div>';
}
