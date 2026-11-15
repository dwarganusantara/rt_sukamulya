<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tema & Tampilan (spesifikasi Spesifikasi_Pengaturan_Tema_RTRW_Digital.md).
 *  - Preset permanen (config/ui_presets.php); 'original-orange' = desain asli tanpa lapisan apa pun.
 *  - Draft kerja per admin (ui_config_drafts); pratinjau hanya pada sesi admin tersebut.
 *  - Penerbitan atomik sebagai versi baru (ui_config_versions) + penunjuk versi aktif (settings.ui_active_version).
 *  - Lapisan warna dibangkitkan otomatis dari SELURUH CSS aplikasi: rona oranye → warna tema; tema gelap membalik
 *    warna netral (latar putih → permukaan gelap, teks navy → terang). Hasil di-cache per versi.
 * Tidak mengubah data bisnis, route, maupun hak akses.
 */
class Ui_theme
{
    private $CI;
    private $presets;
    private $defaults;
    private $active = NULL;
    const CACHE_DIR = 'cache/ui-theme/';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('ui_presets', TRUE);
        $this->presets = (array) $this->CI->config->item('ui_presets', 'ui_presets');
        $this->defaults = (array) $this->CI->config->item('ui_default_config', 'ui_presets');
    }

    public function presets() { return $this->presets; }
    public function widgets() { return (array) $this->CI->config->item('ui_widgets', 'ui_presets'); }
    public function stat_cards() { return (array) $this->CI->config->item('ui_stat_cards', 'ui_presets'); }

    public function ensure()
    {
        $db = $this->CI->db;
        if (!$db->table_exists('ui_config_versions')) $db->query("CREATE TABLE IF NOT EXISTS ui_config_versions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, config_json MEDIUMTEXT NOT NULL, action VARCHAR(30) NOT NULL DEFAULT 'apply',
            note VARCHAR(255) NOT NULL DEFAULT '', created_by INT UNSIGNED NULL, created_by_name VARCHAR(80) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if (!$db->table_exists('ui_config_drafts')) $db->query("CREATE TABLE IF NOT EXISTS ui_config_drafts (
            owner_key VARCHAR(100) NOT NULL PRIMARY KEY, config_json MEDIUMTEXT NOT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /* ---------------- Konfigurasi ---------------- */

    /** Konfigurasi bawaan lengkap = preset asli (snapshot desain awal). */
    public function default_config()
    {
        $c = $this->defaults;
        $c['colors'] = $this->presets['original-orange']['colors'];
        $c['brand'] = array('logo' => '');
        return $c;
    }

    /** Whitelist + validasi bentuk; nilai tak dikenal diganti bawaan (konfigurasi invalid tidak pernah diterbitkan mentah). */
    public function normalize($c)
    {
        $d = $this->default_config();
        $c = is_array($c) ? $c : array();
        $out = $d;
        $out['themeId'] = isset($this->presets[$c['themeId'] ?? '']) ? $c['themeId'] : 'original-orange';
        $base = $this->presets[$out['themeId']]['colors'];
        foreach ($base as $k => $v) {
            $hex = strtoupper(trim((string) ($c['colors'][$k] ?? '')));
            $out['colors'][$k] = preg_match('/^#[0-9A-F]{6}$/', $hex) ? $hex : $v;
        }
        $pick = function ($v, $allowed, $def) {return in_array($v, $allowed, TRUE) ? $v : $def;};
        $out['sidebar']['variant'] = $pick($c['sidebar']['variant'] ?? '', array('standard', 'compact', 'icons'), 'standard');
        $out['components']['radius'] = $pick($c['components']['radius'] ?? '', array('square', 'medium', 'round'), 'medium');
        $out['components']['fontSize'] = $pick($c['components']['fontSize'] ?? '', array('small', 'medium', 'large'), 'medium');
        $out['components']['density'] = $pick($c['components']['density'] ?? '', array('comfortable', 'compact'), 'comfortable');
        $out['components']['inputHeight'] = $pick($c['components']['inputHeight'] ?? '', array('standard', 'compact'), 'standard');
        foreach (array('search', 'notifications', 'banner') as $k) $out['header'][$k] = isset($c['header'][$k]) ? (bool) $c['header'][$k] : TRUE;
        $widgets = array_keys($this->widgets());
        $order = array_values(array_intersect((array) ($c['dashboard']['order'] ?? array()), $widgets));
        foreach ($widgets as $w) if (!in_array($w, $order, TRUE)) $order[] = $w;
        $out['dashboard']['order'] = $order;
        $out['dashboard']['hidden'] = array_values(array_intersect((array) ($c['dashboard']['hidden'] ?? array()), $widgets));
        $out['dashboard']['stats_hidden'] = array_values(array_intersect((array) ($c['dashboard']['stats_hidden'] ?? array()), array_keys($this->stat_cards())));
        $logo = (string) ($c['brand']['logo'] ?? '');
        $out['brand']['logo'] = preg_match('#^uploads/[a-zA-Z0-9_./-]+\.(png|webp|jpe?g)$#', $logo) && is_file(FCPATH . $logo) ? $logo : '';
        return $out;
    }

    /** Validasi kontras (WCAG) sebelum diterbitkan. @return string[] pesan error */
    public function validate($c)
    {
        $this->CI->load->helper('theme');
        $errors = array();
        $col = $c['colors'];
        $dark = !empty($this->presets[$c['themeId']]['dark']);
        // Preset bawaan sudah dikurasi (termasuk desain asli Oranye Harmoni); pemeriksaan wajib untuk warna kustom.
        if ($col == $this->presets[$c['themeId']]['colors']) return $errors;
        if (theme_contrast(strtolower($col['accent']), '#ffffff') < 3) $errors[] = 'Warna aksen terlalu terang: teks putih pada tombol tidak terbaca (kontras minimal 3:1).';
        if (theme_contrast(strtolower($col['text']), strtolower($col['background'])) < 4.5) $errors[] = 'Kontras teks utama terhadap latar kurang dari 4.5:1 (WCAG AA).';
        if (!$dark && theme_contrast(strtolower($col['text']), strtolower($col['surface'])) < 4.5) $errors[] = 'Kontras teks utama terhadap kartu kurang dari 4.5:1 (WCAG AA).';
        return $errors;
    }

    /** Versi yang sedang diterbitkan (publik). Bila tidak ada/rusak → desain bawaan. */
    public function active()
    {
        if ($this->active !== NULL) return $this->active;
        $this->ensure();
        $id = (int) ($this->CI->db->get_where('settings', array('setting_key' => 'ui_active_version'))->row_array()['setting_value'] ?? 0);
        $row = $id ? $this->CI->db->get_where('ui_config_versions', array('id' => $id))->row_array() : NULL;
        $cfg = $row ? json_decode($row['config_json'], TRUE) : NULL;
        return $this->active = array('id' => $row ? (int) $row['id'] : 0, 'config' => $this->normalize($cfg ?: $this->default_config()));
    }

    /** Konfigurasi untuk permintaan saat ini: draft milik admin ini bila mode pratinjau aktif, selain itu versi aktif. */
    public function current()
    {
        $key = $this->owner_key();
        if ($key && $this->CI->session->userdata('ui_preview')) {
            $d = $this->draft($key);
            if ($d) return $d;
        }
        return $this->active()['config'];
    }

    public function previewing()
    {
        $key = $this->owner_key();
        return $key && $this->CI->session->userdata('ui_preview') && $this->draft($key);
    }

    public function owner_key()
    {
        $s = $this->CI->session;
        if ($s->userdata('admin_id')) return 'admin:' . (int) $s->userdata('admin_id');
        if ($s->userdata('platform_owner_name')) return 'pusat:' . $s->userdata('platform_owner_name');
        return '';
    }

    public function draft($key)
    {
        $this->ensure();
        $row = $this->CI->db->get_where('ui_config_drafts', array('owner_key' => $key))->row_array();
        return $row ? $this->normalize(json_decode($row['config_json'], TRUE)) : NULL;
    }

    public function save_draft($key, $config)
    {
        $this->ensure();
        $this->CI->db->replace('ui_config_drafts', array('owner_key' => $key, 'config_json' => json_encode($this->normalize($config)), 'updated_at' => date('Y-m-d H:i:s')));
    }

    public function discard_draft($key)
    {
        $this->ensure();
        $this->CI->db->delete('ui_config_drafts', array('owner_key' => $key));
        $this->CI->session->unset_userdata('ui_preview');
    }

    /** Terbitkan satu versi baru secara atomik. Bila gagal, versi aktif lama tetap dipakai. @return int id versi */
    public function publish($config, $action, $note, $actor_id, $actor_name)
    {
        $config = $this->normalize($config);
        if ($errors = $this->validate($config)) throw new RuntimeException(implode(' ', $errors));
        $this->ensure();
        $db = $this->CI->db;
        $db->trans_begin();
        try {
            $db->insert('ui_config_versions', array('config_json' => json_encode($config), 'action' => $action, 'note' => mb_substr($note, 0, 255),
                'created_by' => $actor_id ?: NULL, 'created_by_name' => mb_substr($actor_name, 0, 80)));
            $id = (int) $db->insert_id();
            $db->replace('settings', array('setting_key' => 'ui_active_version', 'setting_value' => (string) $id));
            if ($db->trans_status() === FALSE) throw new RuntimeException('Konfigurasi gagal diterapkan. Tampilan aktif sebelumnya tetap digunakan.');
            $db->trans_commit();
        } catch (Throwable $e) {$db->trans_rollback(); throw $e;}
        $this->active = NULL;
        return $id;
    }

    public function versions($limit = 30)
    {
        $this->ensure();
        return $this->CI->db->order_by('id', 'DESC')->limit($limit)->get('ui_config_versions')->result_array();
    }

    public function version($id)
    {
        $this->ensure();
        $row = $this->CI->db->get_where('ui_config_versions', array('id' => (int) $id))->row_array();
        return $row ? $this->normalize(json_decode($row['config_json'], TRUE)) : NULL;
    }

    /* ---------------- CSS ---------------- */

    /** Hash isi tampilan (untuk cache & versi URL). Kosong bila tampilan identik dengan bawaan. */
    public function css_hash($c)
    {
        $plain = $this->normalize($c); unset($plain['brand']);
        $default = $this->normalize($this->default_config()); unset($default['brand']);
        if ($plain == $default) return '';
        return substr(md5(json_encode($plain) . '|' . $this->css_signature()), 0, 16);
    }

    private function css_files()
    {
        $skip = '/^(citizen-|portal|login-|home-|family-|information|contact-|agenda-detail|public-|mobile-app|letter-request|letter-services|chat\.css|citizen|organization|page-images|rt09|sirut)/';
        $files = array();
        foreach (glob(FCPATH . 'assets/*.css') ?: array() as $f) if (!preg_match($skip, basename($f))) $files[] = $f;
        sort($files);
        return $files;
    }

    private function css_signature()
    {
        $s = '';
        foreach ($this->css_files() as $f) $s .= basename($f) . filemtime($f) . ';';
        return md5($s);
    }

    /** CSS lapisan tema untuk konfigurasi (dibangkitkan sekali lalu di-cache). */
    public function css($c)
    {
        $hash = $this->css_hash($c);
        if ($hash === '') return '';
        $dir = APPPATH . self::CACHE_DIR;
        $file = $dir . $hash . '.css';
        if (is_file($file)) return (string) file_get_contents($file);
        $css = "/* Lapisan Tema & Tampilan — dibangkitkan otomatis, jangan diedit. */\n";
        if ($c['themeId'] !== 'original-orange' || $c['colors'] != $this->presets['original-orange']['colors']) {
            $this->palette = $this->build_palette($c);
            foreach ($this->css_files() as $f) {
                $part = $this->transform_css((string) file_get_contents($f));
                if ($part !== '') $css .= "/* " . basename($f) . " */\n" . $part . "\n";
            }
            $css .= $this->scope_rules($this->theme_variables($c));
        }
        $css .= $this->scope_rules($this->layout_css($c));
        if (!is_dir($dir)) @mkdir($dir, 0755, TRUE);
        foreach (glob($dir . '*.css') ?: array() as $old) if (filemtime($old) < time() - 86400 * 30) @unlink($old);
        @file_put_contents($file, $css, LOCK_EX);
        return $css;
    }

    private $palette = array();

    private function build_palette($c)
    {
        $p = $this->hex2hsl($c['colors']['accent']);
        return array('h' => $p[0], 's' => $p[1], 'l' => $p[2], 'dark' => !empty($this->presets[$c['themeId']]['dark']),
            'surface' => $this->hex2hsl($c['colors']['surface']), 'bg' => $this->hex2hsl($c['colors']['background']), 'text' => $this->hex2hsl($c['colors']['text']));
    }

    private function theme_variables($c)
    {
        $col = $c['colors'];
        $v = ':root{--theme-accent:' . $col['accent'] . ';--theme-navy:' . ($this->palette['dark'] ? '#E2E8F0' : $col['text']) . ';--ui-primary:' . $col['primary'] . ';--ui-secondary:' . $col['secondary'] . ';--ui-bg:' . $col['background'] . ';--ui-surface:' . $col['surface'] . ';--ui-text:' . $col['text'] . "}\n";
        if ($this->palette['dark']) {
            $v .= 'body.admin-body,.admin-body .ad-workspace,.admin-body .ad-main{background:' . $col['background'] . '!important;color:' . $col['text'] . "}\n";
            $v .= '.admin-body .ad-sidebar,.admin-body .ad-topbar{background:' . $col['surface'] . "!important}\n";
            $v .= ".admin-body .ad-account-name strong,.admin-body .ad-account-name small,.admin-body .ad-topbar .ad-search input{color:#E2E8F0!important}.admin-body .sbx-head,.admin-body .sbx-label{color:#E2E8F0}\n";
            $v .= ".admin-body img{filter:none}.admin-body input,.admin-body select,.admin-body textarea{background-color:#0F1A2E!important;color:#E2E8F0!important;border-color:#2A3A57!important}.admin-body ::placeholder{color:#7C8BA5!important}\n";
        }
        return $v;
    }

    /** Tata letak/komponen/header/dashboard (bukan warna). */
    private function layout_css($c)
    {
        $o = '';
        $cards = '.admin-body :is(.ops-card,.dbh-card,.dbh-stat,.dbh-hero,.sp-panel,.sp-stat,.sp-hero,.akx-card,.sh-card,.sh-stat,.hv-card,.eq3-card,.eq3-stat,.cf-card,.lt-card,.ops-hero,.ops-stat,.panel)';
        if ($c['components']['radius'] === 'square') $o .= $cards . '{border-radius:6px!important}';
        if ($c['components']['radius'] === 'round') $o .= $cards . '{border-radius:28px!important}';
        if ($c['components']['fontSize'] === 'small') $o .= '.admin-body .ad-main{zoom:.93}';
        if ($c['components']['fontSize'] === 'large') $o .= '.admin-body .ad-main{zoom:1.07}';
        if ($c['components']['density'] === 'compact') $o .= '.admin-body .ad-main table :is(td,th){padding-top:6px!important;padding-bottom:6px!important}';
        if ($c['components']['inputHeight'] === 'compact') $o .= '.admin-body .ad-main :is(input:not([type=checkbox]):not([type=radio]):not([type=file]),select){min-height:36px!important;height:auto!important;padding-top:6px!important;padding-bottom:6px!important}';
        if ($c['sidebar']['variant'] === 'compact') $o .= '@media(min-width:951px){.admin-body .ad-sidebar{width:232px!important;min-width:232px!important}.admin-body .ad-shell{grid-template-columns:232px minmax(0,1fr)!important}.admin-body .sbx-row{font-size:13.5px}}';
        if (!$c['header']['search']) $o .= '.admin-body .ad-search{visibility:hidden}';
        if (!$c['header']['notifications']) $o .= '.admin-body .ad-bell{display:none!important}';
        if (!$c['header']['banner']) $o .= '.admin-body :is(.ops-hero-art,.sp-hero>img,.dbh-hero-img,.lt-hero-art){display:none!important}';
        // Dashboard: urutan & visibilitas widget (hanya UI; data tidak dihapus).
        $sel = array('hero' => '.dbh-hero', 'stats' => '.dbh-stats', 'stats_chart' => '.dbh-c-stat', 'dues' => '.dbh-c-dues', 'agenda' => '.dbh-c-agenda', 'letters' => '.dbh-c-letters', 'operations' => '.dbh-c-ops');
        $pos = array_flip($c['dashboard']['order']);
        $d = $this->normalize($this->default_config())['dashboard'];
        if ($c['dashboard']['order'] !== $d['order']) {
            $o .= '.dbh{display:flex;flex-direction:column;gap:16px}.dbh>*{margin-top:0!important;margin-bottom:0!important}';
            $o .= '.dbh>.dbh-hero{order:' . $pos['hero'] . '}.dbh>.dbh-stats{order:' . $pos['stats'] . '}.dbh>.dbh-c-ops{order:' . $pos['operations'] . '}';
            $o .= '.dbh>.dbh-row-a{order:' . min($pos['stats_chart'], $pos['dues']) . '}.dbh>.dbh-row-b{order:' . min($pos['agenda'], $pos['letters']) . '}';
            foreach (array('stats_chart', 'dues', 'agenda', 'letters') as $w) $o .= '.dbh-row>' . $sel[$w] . '{order:' . $pos[$w] . '}';
        }
        foreach ($c['dashboard']['hidden'] as $w) $o .= '.dbh ' . $sel[$w] . '{display:none!important}';
        $stat = array('residents' => '.dbh-stat.orange', 'agenda' => '.dbh-stat.blue', 'announcements' => '.dbh-stat.green', 'dues' => '.dbh-stat.purple', 'cash' => '.dbh-stat.amber');
        foreach ($c['dashboard']['stats_hidden'] as $s) $o .= '.dbh ' . $stat[$s] . '{display:none!important}';
        if ($c['dashboard']['stats_hidden']) $o .= '.dbh .dbh-stats{grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important}';
        if (array_intersect(array('stats_chart', 'dues'), $c['dashboard']['hidden']) || array_intersect(array('agenda', 'letters'), $c['dashboard']['hidden'])) $o .= '.dbh .dbh-row{grid-template-columns:repeat(auto-fit,minmax(320px,1fr))!important}';
        return $o === '' ? '' : "/* tata letak */\n" . $o . "\n";
    }

    /* ---- transformasi warna CSS ---- */

    private function transform_css($css)
    {
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $css = preg_replace('/@(import|charset)[^;]*;/i', '', $css);
        return $this->process_block($css);
    }

    private function process_block($s)
    {
        $out = ''; $i = 0; $n = strlen($s);
        while ($i < $n) {
            $open = strpos($s, '{', $i);
            if ($open === FALSE) break;
            $sel = trim(substr($s, $i, $open - $i));
            $depth = 1; $j = $open + 1;
            while ($j < $n && $depth) {if ($s[$j] === '{') $depth++; elseif ($s[$j] === '}') $depth--; $j++;}
            $body = substr($s, $open + 1, $j - $open - 2);
            if ($sel !== '' && $sel[0] === '@') {
                if (preg_match('/^@(media|supports|layer|container)/i', $sel)) {$inner = $this->process_block($body); if ($inner !== '') $out .= $sel . '{' . $inner . '}';}
            } elseif ($sel !== '' && strpos($body, '{') === FALSE) {
                $decl = $this->transform_decls($body);
                if ($decl !== '') $out .= $this->scope($sel) . '{' . $decl . '}';
            }
            $i = $j;
        }
        return $out;
    }

    /** Terapkan scope() pada semua aturan CSS buatan sendiri (termasuk di dalam @media). */
    private function scope_rules($css)
    {
        $out = ''; $i = 0; $n = strlen($css);
        while ($i < $n) {
            $open = strpos($css, '{', $i);
            if ($open === FALSE) break;
            $sel = trim(preg_replace('#/\*.*?\*/#s', '', substr($css, $i, $open - $i)));
            $depth = 1; $j = $open + 1;
            while ($j < $n && $depth) {if ($css[$j] === '{') $depth++; elseif ($css[$j] === '}') $depth--; $j++;}
            $body = substr($css, $open + 1, $j - $open - 2);
            $out .= ($sel !== '' && $sel[0] === '@') ? $sel . '{' . $this->scope_rules($body) . '}' : $this->scope($sel) . '{' . $body . '}';
            $i = $j;
        }
        return $out === '' ? '' : $out . "\n";
    }

    /** ":root " di depan setiap selektor: spesifisitas +1 agar lapisan (dimuat di <head>) menang atas CSS halaman yang sama. */
    private function scope($sel)
    {
        $list = array(); $buf = ''; $depth = 0;
        for ($k = 0, $n = strlen($sel); $k < $n; $k++) {
            $ch = $sel[$k];
            if ($ch === '(') $depth++; elseif ($ch === ')') $depth--;
            if ($ch === ',' && $depth === 0) {$list[] = trim($buf); $buf = ''; continue;}
            $buf .= $ch;
        }
        $list[] = trim($buf);
        foreach ($list as &$s) if ($s !== '' && !preg_match('/^(:root|html)\b/i', $s)) $s = ':root ' . $s;
        unset($s);
        return implode(',', $list);
    }

    private function transform_decls($body)
    {
        $parts = array(); $buf = ''; $depth = 0; $quote = '';
        for ($k = 0, $n = strlen($body); $k < $n; $k++) {
            $ch = $body[$k];
            if ($quote) {if ($ch === $quote) $quote = ''; $buf .= $ch; continue;}
            if ($ch === '"' || $ch === "'") $quote = $ch;
            if ($ch === '(') $depth++; elseif ($ch === ')') $depth--;
            if ($ch === ';' && $depth <= 0) {$parts[] = $buf; $buf = ''; continue;}
            $buf .= $ch;
        }
        $parts[] = $buf;
        $out = array();
        foreach ($parts as $d) {
            $p = strpos($d, ':');
            if ($p === FALSE) continue;
            $prop = strtolower(trim(substr($d, 0, $p))); $val = trim(substr($d, $p + 1));
            if ($prop === '' || $val === '') continue;
            $text = in_array($prop, array('color', 'fill', 'stroke', 'caret-color', '-webkit-text-fill-color', 'text-decoration-color', 'outline-color', 'column-rule-color'), TRUE);
            $new = $this->map_value($val, $text, $prop);
            if ($new !== $val) $out[] = $prop . ':' . $new;
        }
        return implode(';', $out);
    }

    private function map_value($val, $text, $prop)
    {
        // url(...) tidak diubah (gambar/ikon data-URI).
        $segments = preg_split('/(url\([^)]*\))/i', $val, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($segments as $k => &$seg) {
            if ($k % 2) continue;
            $seg = preg_replace_callback('/#([0-9a-fA-F]{8}|[0-9a-fA-F]{6}|[0-9a-fA-F]{4}|[0-9a-fA-F]{3})\b/', function ($m) use ($text) {
                $h = $m[1];
                if (strlen($h) <= 4) {$e = ''; foreach (str_split($h) as $c) $e .= $c . $c; $h = $e;}
                $a = strlen($h) === 8 ? substr($h, 6, 2) : '';
                $rgb = $this->map_rgb(hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)), $text);
                return $rgb === NULL ? $m[0] : '#' . $rgb . $a;
            }, $seg);
            $seg = preg_replace_callback('/rgba?\(\s*(\d{1,3})[\s,]+(\d{1,3})[\s,]+(\d{1,3})\s*(?:[,\/]\s*([\d.]+%?))?\s*\)/i', function ($m) use ($text) {
                $rgb = $this->map_rgb((int) $m[1], (int) $m[2], (int) $m[3], $text);
                if ($rgb === NULL) return $m[0];
                $r = hexdec(substr($rgb, 0, 2)); $g = hexdec(substr($rgb, 2, 2)); $b = hexdec(substr($rgb, 4, 2));
                return isset($m[4]) && $m[4] !== '' ? "rgba($r,$g,$b,{$m[4]})" : "rgb($r,$g,$b)";
            }, $seg);
            if ($this->palette['dark'] && !$text && preg_match('/^(background|background-color|border|border-color|border-top|border-bottom|border-left|border-right|box-shadow|outline)$/', $prop)) {
                $seg = preg_replace_callback('/\bwhite\b/i', function () {return '#' . $this->map_rgb(255, 255, 255, FALSE);}, $seg);
            }
        }
        unset($seg);
        return implode('', $segments);
    }

    /** Pemetaan satu warna. @return string|null rrggbb baru, NULL bila tidak berubah. */
    private function map_rgb($r, $g, $b, $text)
    {
        list($h, $s, $l) = $this->rgb2hsl($r, $g, $b);
        $P = $this->palette;
        $orange = $s >= 0.28 && $h >= 8 && $h <= 40 && $l >= 0.18;
        if ($orange) {
            // Rona oranye → rona aksen tema, saturasi diskalakan, kecerahan dipertahankan (nuansa krem tetap terang).
            $ns = min(1, $s * max(0.35, $P['s']) / 0.9);
            $nl = $l;
            if ($l > 0.25 && $l < 0.8) $nl = $l + ($P['l'] - 0.53) * (1 - abs($l - 0.53) * 1.6);
            if ($P['dark'] && $l >= 0.85) {$nl = 0.2; $ns = 0.5;}
            return $this->hsl2hex($P['h'], $ns, max(0, min(1, $nl)));
        }
        if (!$P['dark']) return NULL;
        // Tema gelap: balik warna netral & navy.
        $neutral = $s < 0.3 || ($h >= 195 && $h <= 250 && $l < 0.42);
        if ($text) {
            if ($neutral && $l < 0.6) return $this->hsl2hex(215, 0.25, min(0.94, 0.95 - $l * 0.5));
            return NULL;
        }
        if ($neutral) {
            if ($l >= 0.97) return $this->hsl2hex($P['surface'][0], $P['surface'][1], $P['surface'][2]);
            if ($l >= 0.9) return $this->hsl2hex($P['bg'][0], $P['bg'][1], $P['bg'][2] + 0.03);
            if ($l >= 0.55) return $this->hsl2hex(220, 0.28, 0.2 + (0.9 - $l) * 0.25);
            if ($l < 0.35) return $this->hsl2hex(215, 0.25, 0.86);
            return NULL;
        }
        if ($l >= 0.85) return $this->hsl2hex($h, min(0.6, $s * 0.6), 0.2);
        return NULL;
    }

    private function hex2hsl($hex) {$hex = ltrim($hex, '#'); return $this->rgb2hsl(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));}

    private function rgb2hsl($r, $g, $b)
    {
        $r /= 255; $g /= 255; $b /= 255;
        $max = max($r, $g, $b); $min = min($r, $g, $b); $l = ($max + $min) / 2; $d = $max - $min;
        if ($d == 0) return array(0, 0, $l);
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        if ($max == $r) $h = fmod(($g - $b) / $d + ($g < $b ? 6 : 0), 6); elseif ($max == $g) $h = ($b - $r) / $d + 2; else $h = ($r - $g) / $d + 4;
        return array($h * 60, $s, $l);
    }

    private function hsl2hex($h, $s, $l)
    {
        $h = fmod($h + 360, 360) / 360;
        $f = function ($p, $q, $t) {if ($t < 0) $t += 1; if ($t > 1) $t -= 1; if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t; if ($t < 1 / 2) return $q; if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6; return $p;};
        if ($s == 0) {$r = $g = $b = $l;} else {$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s; $p = 2 * $l - $q; $r = $f($p, $q, $h + 1 / 3); $g = $f($p, $q, $h); $b = $f($p, $q, $h - 1 / 3);}
        return sprintf('%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
    }
}
