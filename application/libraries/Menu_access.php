<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hak Akses Menu per peran (halaman Akun & Hak Akses → Detail Hak Akses).
 *
 * Aturan:
 *  - Sumber menu: config/admin_menu.php (sama dengan sidebar).
 *  - Izin dasar tetap RBAC rt_access; pengaturan di sini HANYA dapat mematikan menu yang diizinkan RBAC.
 *  - Tidak ada baris = mengikuti RBAC (aktif). Baris allowed=0 = menu dimatikan untuk peran tersebut.
 *  - Menu terkunci (admin_menu_locked) selalu aktif agar pengurus tidak terkunci dari aplikasi.
 *  - Ditegakkan di server lewat hook untuk halaman, POST, AJAX, ekspor, dan alias URL controller.
 *  - Urutan keputusan bisnis: fitur DISABLED/langganan (Feature_gate) → peran (RBAC + hak akses menu) → halaman.
 */
class Menu_access
{
    const TABLE = 'role_menu_access';

    private $CI;
    private $groups;
    private $locked;
    private $perms;
    private $roles;
    private $tenant;
    private $items = NULL;
    private $overrides = array();
    private $ready = NULL;

    /** Metode controller Admin yang merupakan sub-halaman dari sebuah menu. */
    private $admin_methods = array(
        'index' => 'admin',
        'page' => 'admin/pages', 'delete_page' => 'admin/pages',
        'page_images' => 'admin/page-images',
        'official' => 'admin/officials', 'official_photo_delete' => 'admin/officials', 'official_delete' => 'admin/officials',
        'transaction' => 'admin/transactions', 'edit_transaction' => 'admin/transactions', 'review_transaction' => 'admin/transactions', 'submit_transaction' => 'admin/transactions',
        'quick_link' => 'admin/quick_links', 'quick_link_move' => 'admin/quick_links', 'quick_link_delete' => 'admin/quick_links',
        'citizen' => 'admin/citizens',
        'request_detail' => 'admin/requests',
        'letter_type' => 'admin/letter_types',
        'dues_export' => 'admin/dues', 'due_payment_record' => 'admin/dues', 'due_delete' => 'admin/dues', 'billing_campaign' => 'admin/dues', 'review_due_payment' => 'admin/dues', 'due' => 'admin/dues',
    );

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('admin_menu', TRUE);
        $this->CI->config->load('rt_access', TRUE);
        $this->CI->config->load('features', TRUE);
        $this->groups = (array) $this->CI->config->item('admin_menu_groups', 'admin_menu');
        $this->locked = (array) $this->CI->config->item('admin_menu_locked', 'admin_menu');
        $this->perms = (array) $this->CI->config->item('rt_permissions', 'rt_access');
        $this->roles = (array) $this->CI->config->item('rt_roles', 'rt_access');
        $this->tenant = (int) ($this->CI->config->item('feature_tenant_id', 'features') ?: 1);
    }

    /* ---------------- Skema ---------------- */

    public function ensure()
    {
        if ($this->ready !== NULL) return $this->ready;
        if (!isset($this->CI->db)) $this->CI->load->database();
        if (!$this->CI->db->table_exists(self::TABLE)) {
            $debug = $this->CI->db->db_debug; $this->CI->db->db_debug = FALSE;
            $this->CI->db->query("CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
                tenant_id INT UNSIGNED NOT NULL,
                role VARCHAR(40) NOT NULL,
                menu_path VARCHAR(120) NOT NULL,
                allowed TINYINT(1) NOT NULL DEFAULT 1,
                updated_by INT UNSIGNED NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (tenant_id, role, menu_path),
                KEY idx_role_menu_role (tenant_id, role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->CI->db->db_debug = $debug;
        }
        $this->ready = $this->CI->db->table_exists(self::TABLE);
        $this->ensure_custom_roles();
        return $this->ready;
    }

    /* ---------------- Peran turunan (Tambah Peran) ---------------- */

    /**
     * Peran tambahan dibuat sebagai turunan peran dasar: admins.role tetap berisi peran dasar
     * (seluruh pemeriksaan izin lama tetap berlaku), admins.custom_role berisi kunci peran turunan
     * yang memiliki nama, deskripsi, dan Hak Akses Menu sendiri (hanya dapat mempersempit peran dasar).
     */
    private function ensure_custom_roles()
    {
        $db = $this->CI->db; $debug = $db->db_debug; $db->db_debug = FALSE;
        if (!$db->table_exists('custom_roles')) {
            $db->query("CREATE TABLE IF NOT EXISTS custom_roles (
                role_key VARCHAR(40) NOT NULL PRIMARY KEY,
                tenant_id INT UNSIGNED NOT NULL,
                label VARCHAR(80) NOT NULL,
                description VARCHAR(255) NOT NULL DEFAULT '',
                base_role VARCHAR(40) NOT NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_custom_roles_tenant (tenant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if ($db->table_exists('admins') && !$db->field_exists('custom_role', 'admins')) {
            $db->query('ALTER TABLE admins ADD COLUMN custom_role VARCHAR(40) NULL DEFAULT NULL');
        }
        $db->db_debug = $debug;
    }

    private $custom = NULL;

    /** role_key => row (label, description, base_role) */
    public function custom_roles()
    {
        if ($this->custom !== NULL) return $this->custom;
        $this->custom = array();
        if (!$this->ensure() || !$this->CI->db->table_exists('custom_roles')) return $this->custom;
        foreach ($this->CI->db->order_by('created_at')->get_where('custom_roles', array('tenant_id' => $this->tenant))->result_array() as $r) {
            if (isset($this->roles[$r['base_role']])) $this->custom[$r['role_key']] = $r;
        }
        return $this->custom;
    }

    /** Semua peran (dasar + turunan): key => label. */
    public function all_roles()
    {
        $out = $this->roles;
        foreach ($this->custom_roles() as $k => $r) $out[$k] = $r['label'];
        return $out;
    }

    /** Peran dasar untuk sebuah kunci peran (turunan → dasar). */
    public function base_role($role)
    {
        $c = $this->custom_roles();
        return isset($c[$role]) ? $c[$role]['base_role'] : $role;
    }

    public function is_custom($role)
    {
        return isset($this->custom_roles()[$role]);
    }

    /** Kunci peran efektif sebuah akun untuk Hak Akses Menu. */
    public function account_role(array $account)
    {
        $c = (string) ($account['custom_role'] ?? '');
        return ($c !== '' && $this->is_custom($c) && $this->custom_roles()[$c]['base_role'] === $account['role']) ? $c : $account['role'];
    }

    public function create_custom_role($label, $description, $base, $actor)
    {
        $label = trim(preg_replace('/\s+/', ' ', (string) $label));
        if (mb_strlen($label) < 3 || mb_strlen($label) > 80) throw new RuntimeException('Nama peran 3–80 karakter.');
        if (!isset($this->roles[$base])) throw new RuntimeException('Pilih peran dasar yang valid.');
        foreach ($this->all_roles() as $existing) if (mb_strtolower($existing) === mb_strtolower($label)) throw new RuntimeException('Nama peran sudah dipakai.');
        $slug = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_') ?: 'peran';
        $key = 'c_' . substr($slug, 0, 28);
        $i = 1; $try = $key;
        while (isset($this->roles[$try]) || $this->CI->db->get_where('custom_roles', array('role_key' => $try))->num_rows()) $try = $key . '_' . (++$i);
        $this->CI->db->insert('custom_roles', array('role_key' => $try, 'tenant_id' => $this->tenant, 'label' => $label, 'description' => mb_substr(trim((string) $description), 0, 255), 'base_role' => $base, 'created_by' => (int) $actor ?: NULL));
        $this->custom = NULL;
        return $try;
    }

    public function update_custom_role($key, $label, $description)
    {
        if (!$this->is_custom($key)) throw new RuntimeException('Hanya peran tambahan yang dapat diubah.');
        $label = trim(preg_replace('/\s+/', ' ', (string) $label));
        if (mb_strlen($label) < 3 || mb_strlen($label) > 80) throw new RuntimeException('Nama peran 3–80 karakter.');
        foreach ($this->all_roles() as $k => $existing) if ($k !== $key && mb_strtolower($existing) === mb_strtolower($label)) throw new RuntimeException('Nama peran sudah dipakai.');
        $this->CI->db->where(array('role_key' => $key, 'tenant_id' => $this->tenant))->update('custom_roles', array('label' => $label, 'description' => mb_substr(trim((string) $description), 0, 255)));
        $this->custom = NULL;
    }

    public function delete_custom_role($key)
    {
        if (!$this->is_custom($key)) throw new RuntimeException('Peran tambahan tidak ditemukan.');
        if ($this->CI->db->where('custom_role', $key)->count_all_results('admins')) throw new RuntimeException('Peran masih dipakai akun. Pindahkan akun ke peran lain terlebih dahulu.');
        $this->CI->db->delete('custom_roles', array('role_key' => $key, 'tenant_id' => $this->tenant));
        $this->CI->db->delete(self::TABLE, array('tenant_id' => $this->tenant, 'role' => $key));
        $this->custom = NULL;
        unset($this->overrides[$key]);
    }

    /* ---------------- Katalog menu ---------------- */

    public function groups() { return $this->groups; }
    public function roles() { return $this->roles; }

    /** path => [path, icon, label, roles(array), group, tone] */
    public function items()
    {
        if ($this->items !== NULL) return $this->items;
        $this->items = array();
        foreach ($this->groups as $group => $def) {
            foreach ((array) $def[1] as $item) {
                if (!is_array($item) || !isset($item[0], $item[1], $item[2], $item[3])) continue;
                $this->items[$item[0]] = array($item[0], $item[1], $item[2], $this->resolve_roles($item[3]), $group, $def[0]);
            }
        }
        return $this->items;
    }

    public function resolve_roles($spec)
    {
        if ($spec === '*') return array_keys($this->roles);
        if (is_string($spec) && strpos($spec, 'perm:') === 0) return (array) ($this->perms[substr($spec, 5)] ?? array('admin'));
        return (array) $spec;
    }

    /** Diizinkan oleh matriks RBAC dasar (tanpa pengaturan hak akses menu). */
    public function rbac_allows($role, $path)
    {
        $items = $this->items();
        return isset($items[$path]) && in_array($this->base_role($role), $items[$path][3], TRUE);
    }

    public function is_locked($role, $path)
    {
        if (!isset($this->locked[$path])) return FALSE;
        $role = $this->base_role($role);
        return $this->locked[$path] === '*' || in_array($role, (array) $this->locked[$path], TRUE);
    }

    /** path => allowed (0/1) yang tersimpan untuk peran. */
    public function overrides($role)
    {
        if (isset($this->overrides[$role])) return $this->overrides[$role];
        if (!$this->ensure()) return $this->overrides[$role] = array();
        $rows = $this->CI->db->select('menu_path,allowed')->get_where(self::TABLE, array('tenant_id' => $this->tenant, 'role' => (string) $role))->result_array();
        return $this->overrides[$role] = array_map('intval', array_column($rows, 'allowed', 'menu_path'));
    }

    /** Keputusan akhir: RBAC dasar DAN tidak dimatikan pada Hak Akses Menu. */
    public function allowed($role, $path)
    {
        if (!isset($this->items()[$path])) return TRUE;
        if (!$this->rbac_allows($role, $path)) return FALSE;
        if ($this->is_locked($role, $path)) return TRUE;
        $o = $this->overrides($role);
        return !isset($o[$path]) || $o[$path] === 1;
    }

    /* ---------------- Perubahan ---------------- */

    /**
     * Simpan menu aktif untuk peran. Menu di luar RBAC / terkunci diabaikan (tidak bisa dipaksa lewat request).
     * Mengembalikan array('on' => [...label], 'off' => [...label]) berisi perubahan untuk audit.
     */
    public function save($role, array $enabled, $actor)
    {
        if (!isset($this->all_roles()[$role])) throw new RuntimeException('Peran tidak dikenal.');
        if (!$this->ensure()) throw new RuntimeException('Tabel hak akses menu belum tersedia.');
        $enabled = array_flip(array_map('strval', $enabled));
        $changes = array('on' => array(), 'off' => array());
        $now = date('Y-m-d H:i:s');
        $db = $this->CI->db;
        $db->trans_begin();
        try {
            foreach ($this->items() as $path => $item) {
                if (!$this->rbac_allows($role, $path) || $this->is_locked($role, $path)) continue;
                $before = $this->allowed($role, $path);
                $after = isset($enabled[$path]);
                $db->replace(self::TABLE, array('tenant_id' => $this->tenant, 'role' => $role, 'menu_path' => $path, 'allowed' => $after ? 1 : 0, 'updated_by' => (int) $actor ?: NULL, 'updated_at' => $now));
                if ($before !== $after) $changes[$after ? 'on' : 'off'][] = $item[2];
            }
            if ($db->trans_status() === FALSE) throw new RuntimeException('Hak akses menu gagal disimpan.');
            $db->trans_commit();
        } catch (Throwable $e) {
            $db->trans_rollback();
            throw $e;
        }
        unset($this->overrides[$role]);
        return $changes;
    }

    /** Kembalikan peran ke bawaan RBAC (semua menu yang diizinkan peran aktif). */
    public function reset($role)
    {
        if (!isset($this->all_roles()[$role])) throw new RuntimeException('Peran tidak dikenal.');
        if ($this->ensure()) $this->CI->db->delete(self::TABLE, array('tenant_id' => $this->tenant, 'role' => $role));
        unset($this->overrides[$role]);
    }

    /** Ringkasan untuk tab Pengaturan Peran: [allowed, total_rbac]. */
    public function summary($role)
    {
        $ok = 0; $total = 0;
        foreach ($this->items() as $path => $item) {
            if (!$this->rbac_allows($role, $path)) continue;
            $total++;
            if ($this->allowed($role, $path)) $ok++;
        }
        return array($ok, $total);
    }

    /* ---------------- Komersial per menu (Gratis / Berlangganan / Nonaktif) ---------------- */

    /** Menu inti selalu Gratis agar RT tidak terkunci dari aplikasinya (MD §10.7). */
    public function is_core($path)
    {
        return in_array($path, (array) $this->CI->config->item('admin_menu_core', 'admin_menu'), TRUE);
    }

    private function gate()
    {
        if (!isset($this->CI->feature_gate)) $this->CI->load->library('Feature_gate');
        return $this->CI->feature_gate;
    }

    /**
     * Peta komersial setiap menu sidebar: path => array(own, parent).
     *  own    = kode yang diatur Super Admin untuk menu ini (kode katalog fitur untuk menu utama fitur, atau 'menu:<path>').
     *  parent = kode katalog fitur induk bila menu ini halaman tambahan dari fitur yang sama (mis. Jenis Surat → Pengajuan Surat).
     * Menu inti: own & parent NULL (selalu Gratis).
     */
    public function commercial_map()
    {
        static $map = NULL;
        if ($map !== NULL) return $map;
        $map = array(); $primary = array();
        foreach ($this->items() as $path => $item) {
            if ($this->is_core($path)) {$map[$path] = array(NULL, NULL); continue;}
            $m = $this->gate()->match($path);
            if ($m && !isset($primary[$m[0]])) {$primary[$m[0]] = $path; $map[$path] = array($m[0], NULL);}
            else $map[$path] = array('menu:' . $path, $m ? $m[0] : NULL);
        }
        return $map;
    }

    /** Kode komersial milik menu (yang diatur di Aktivasi Fitur). NULL untuk menu inti. */
    public function commercial_code($path)
    {
        return $this->commercial_map()[$path][0] ?? NULL;
    }

    /** Path menu dari kode komersial (kebalikan commercial_code). */
    public function path_for_code($code)
    {
        foreach ($this->commercial_map() as $path => $c) if ($c[0] === $code) return $path;
        return NULL;
    }

    private function decide($code)
    {
        if ($code === NULL) return 'ALLOW';
        $catalog = $this->gate()->catalog();
        if (isset($catalog[$code])) return $this->gate()->commercial($code);
        $this->CI->load->model('Subscription_model');
        return $this->CI->Subscription_model->decision($code, $this->tenant);
    }

    /** Keputusan komersial efektif menu: ALLOW | SUBSCRIPTION_REQUIRED | FEATURE_DISABLED. */
    public function commercial($path)
    {
        if (!isset($this->commercial_map()[$path])) return 'ALLOW';
        list($own, $parent) = $this->commercial_map()[$path];
        $p = $this->decide($parent);
        if ($p === 'FEATURE_DISABLED') return $p;
        $o = $this->decide($own);
        if ($o !== 'ALLOW') return $o;
        return $p;
    }

    /** Hanya bagian yang tidak ditegakkan Feature_gate (kode menu:...). */
    private function commercial_own($path)
    {
        $own = $this->commercial_code($path);
        return ($own !== NULL && strpos($own, 'menu:') === 0) ? $this->decide($own) : 'ALLOW';
    }

    /** Kategori komersial menu untuk tampilan: FREE | PREMIUM | DISABLED (gabungan menu & induk). */
    public function commercial_type($path)
    {
        if (!isset($this->commercial_map()[$path])) return 'FREE';
        $this->CI->load->model('Subscription_model');
        $types = $this->CI->Subscription_model->catalog();
        $rank = array('FREE' => 0, 'PREMIUM' => 1, 'DISABLED' => 2); $best = 'FREE';
        foreach ($this->commercial_map()[$path] as $code) {
            if ($code === NULL) continue;
            $t = $types[$code]['commercial_type'] ?? 'FREE';
            if ($rank[$t] > $rank[$best]) $best = $t;
        }
        return $best;
    }

    /**
     * Katalog komersial lengkap untuk panel Super Admin & editor paket:
     * semua menu sidebar (+ fitur katalog di luar sidebar, mis. Live Chat). code => [name, category, admin, path, core]
     */
    public function commercial_catalog()
    {
        $out = array();
        foreach ($this->items() as $path => $item) {
            $code = $this->commercial_code($path);
            if ($code === NULL) continue;
            $out[$code] = array('name' => $item[2], 'category' => $this->category_label($item[4]), 'admin' => array($path), 'path' => $path);
        }
        foreach ($this->gate()->catalog() as $code => $f) if (!isset($out[$code])) $out[$code] = $f + array('path' => $f['admin'][0] ?? '');
        return $out;
    }

    public function category_label($group)
    {
        return $group === 'Utama' ? 'Umum' : $group;
    }

    /* ---------------- Pencocokan URI ---------------- */

    /** Menu yang menaungi sebuah URI admin (awalan terpanjang). NULL bila bukan halaman menu. */
    public function match($uri)
    {
        $uri = trim(strtolower((string) parse_url((string) $uri, PHP_URL_PATH)), '/');
        if ($uri === 'admin' || $uri === 'admin/index') return 'admin';
        if (preg_match('#^admin/posts?/([a-z_-]+)#', $uri, $m) && isset($this->items()['admin/posts/' . $m[1]])) return 'admin/posts/' . $m[1];
        $best = NULL; $len = -1;
        foreach ($this->items() as $path => $item) {
            if ($path === 'admin') continue;
            $p = strtolower($path);
            if (($uri === $p || strpos($uri, $p . '/') === 0) && strlen($p) > $len) {$best = $path; $len = strlen($p);}
        }
        return $best;
    }

    /** Menu untuk permintaan saat ini, termasuk akses lewat alias controller/method (mis. /sistem/akun). */
    public function current_path()
    {
        $router = $this->CI->router;
        $class = strtolower((string) $router->class);
        $method = strtolower((string) $router->method);
        if ($class === 'admin') {
            if (in_array($method, array('posts', 'post'), TRUE)) {
                $type = strtolower((string) ($this->CI->uri->rsegment(3) ?: 'pengumuman'));
                return isset($this->items()['admin/posts/' . $type]) ? 'admin/posts/' . $type : NULL;
            }
            if (isset($this->admin_methods[$method])) return $this->admin_methods[$method];
            $direct = 'admin/' . $method;
            if (isset($this->items()[$direct])) return $direct;
            return NULL;
        }
        $hit = $this->match(uri_string());
        if ($hit) return $hit;
        $actual = implode('/', $this->CI->uri->rsegment_array());
        foreach ((array) $router->routes as $source => $target) {
            if (!is_string($target) || !is_string($source) || strpos($source, 'admin') !== 0) continue;
            $pattern = preg_replace('/\\\\\$[0-9]+/', '([^/]+)', preg_quote($target, '#'));
            if (!preg_match('#^' . $pattern . '$#i', $actual, $captures)) continue;
            $n = 0;
            $canonical = preg_replace_callback('/\(:(?:num|any)\)/', function () use (&$n, $captures) {return $captures[++$n] ?? '0';}, $source);
            if ($hit = $this->match($canonical)) return $hit;
        }
        return NULL;
    }

    /* ---------------- Penegakan ---------------- */

    public function enforce()
    {
        if (is_cli()) return;
        $this->CI->load->library('Platform_owner');
        if($this->CI->platform_owner->valid_session())return;
        $id = (int) $this->CI->session->userdata('admin_id');
        if (!$id) return; // belum login: controller yang mengarahkan ke halaman login
        $path = $this->current_path();
        if ($path === NULL) return;
        $this->ensure();
        $account = $this->CI->db->select('role,is_active,custom_role')->get_where('admins', array('id' => $id))->row_array();
        if (!$account) return; // guard controller menghancurkan sesi yang tidak valid
        $account['role'] = $this->account_role($account); // peran turunan memakai Hak Akses Menu sendiri
        if (!$this->rbac_allows($account['role'], $path)) return; // penolakan RBAC tetap ditangani controller (pesan lama)
        // Langganan per menu (kode menu:...). Kode katalog fitur sudah ditegakkan Feature_gate sebelum hook ini.
        $commercial = $this->commercial_own($path);
        if ($commercial !== 'ALLOW') $this->deny_subscription($path, $commercial);
        if ($this->allowed($account['role'], $path)) return;
        $this->deny($this->items()[$path][2]);
    }

    private function deny_subscription($path, $error)
    {
        set_status_header(403);
        header('Cache-Control: no-store, private');
        $name = $this->items()[$path][2];
        if ($this->CI->input->is_ajax_request() || stripos((string) $this->CI->input->server('HTTP_ACCEPT'), 'application/json') !== FALSE) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $error, 'feature' => $path, 'message' => $error === 'SUBSCRIPTION_REQUIRED' ? 'Fitur memerlukan langganan aktif.' : 'Fitur tidak tersedia.'));
        } else {
            if (!isset($this->CI->Content_model)) $this->CI->load->model('Content_model');
            echo $this->CI->load->view('errors/subscription', array('name' => $name, 'error' => $error, 'side' => 'admin'), TRUE);
        }
        exit;
    }

    private function deny($label)
    {
        set_status_header(403);
        header('Cache-Control: no-store, private');
        $ajax = $this->CI->input->is_ajax_request() || stripos((string) $this->CI->input->server('HTTP_ACCEPT'), 'application/json') !== FALSE;
        if ($ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'PERMISSION_DENIED', 'message' => 'Menu ' . $label . ' tidak diaktifkan untuk peran Anda.'));
        } else {
            echo $this->CI->load->view('errors/menu_denied', array('name' => $label), TRUE);
        }
        exit;
    }
}

/** Hook: dipasang di config/hooks.php setelah feature_gate_hook. */
function menu_access_hook()
{
    $CI =& get_instance();
    if (!isset($CI->db)) $CI->load->database();
    $CI->load->library('Menu_access');
    $CI->menu_access->enforce();
}
