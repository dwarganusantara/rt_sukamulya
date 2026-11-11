<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gerbang fitur dinamis (Manajemen Fitur & Menu).
 *  BR-03 effective = operational AND parent_effective AND commercial entitlement.
 *  BR-04 akses     = effective AND visibilitas sisi (warga/pengurus) AND izin role (dicek controller masing-masing)
 * Dipanggil otomatis oleh hook post_controller_constructor untuk SETIAP permintaan (halaman, AJAX, ekspor, unduhan).
 * Bila tabel belum ada (sebelum migrasi), gerbang terbuka agar aplikasi lama tetap berjalan.
 */
class Feature_gate
{
    private $CI;
    private $catalog;
    private $tenant;
    private $state = NULL;   // code => row (enabled, visible_to_residents, visible_to_staff, updated_at, id)

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('features', TRUE);
        $this->catalog = (array) $this->CI->config->item('feature_catalog', 'features');
        $this->tenant = (int) ($this->CI->config->item('feature_tenant_id', 'features') ?: 1);
    }

    public function tenant() { return $this->tenant; }
    public function catalog() { return $this->catalog; }

    /** Muat status semua fitur tenant (sekali per permintaan). */
    public function state($refresh = FALSE)
    {
        if ($this->state !== NULL && !$refresh) return $this->state;
        $this->state = array();
        if (!$this->CI->db->table_exists('app_features') || !$this->CI->db->table_exists('tenant_features')) return $this->state = FALSE;
        $rows = $this->CI->db->select('f.id,f.code,t.enabled,t.visible_to_residents,t.visible_to_staff,t.updated_at,t.updated_by')
            ->from('app_features f')->join('tenant_features t', 't.feature_id=f.id AND t.tenant_id=' . $this->tenant, 'left')->get()->result_array();
        foreach ($rows as $r) $this->state[$r['code']] = $r;
        // Kode baru di katalog → daftarkan (default OFF, BR-05/UAT-14), lalu muat ulang sekali.
        if (!$refresh && array_diff_key($this->catalog, array_filter($this->state, function ($r) {return $r['enabled'] !== NULL;}))) {
            $this->CI->load->model('Ops_model');
            $this->CI->Ops_model->sync_features();
            return $this->state(TRUE);
        }
        return $this->state;
    }

    private function row($code)
    {
        $s = $this->state();
        if ($s === FALSE) return array('enabled' => 1, 'visible_to_residents' => 1, 'visible_to_staff' => 1);
        // Fitur di katalog tanpa baris tenant = OFF (BR-05). Kode di luar katalog bukan fitur dinamis.
        return $s[$code] ?? array('enabled' => 0, 'visible_to_residents' => 0, 'visible_to_staff' => 0);
    }

    /** BR-03/BR-11: aktif efektif bila aktif sendiri dan seluruh induknya aktif efektif. */
    public function effective($code, $depth = 0)
    {
        if (!isset($this->catalog[$code])) return TRUE;
        if ($depth > 5 || !(int) ($this->row($code)['enabled'] ?? 0)) return FALSE;
        if ($this->commercial($code) !== 'ALLOW') return FALSE;
        $parent = $this->catalog[$code]['parent'] ?? NULL;
        return $parent ? $this->effective($parent, $depth + 1) : TRUE;
    }

    public function for_residents($code)
    {
        if (!isset($this->catalog[$code])) return TRUE;
        return $this->effective($code) && (int) ($this->row($code)['visible_to_residents'] ?? 0) === 1;
    }

    public function for_staff($code)
    {
        $this->CI->load->library('Platform_owner');
        if($this->CI->platform_owner->valid_session())return TRUE;
        if (!isset($this->catalog[$code])) return TRUE;
        return $this->effective($code) && (int) ($this->row($code)['visible_to_staff'] ?? 0) === 1;
    }

    /** Kode fitur untuk sebuah URI (awalan terpanjang). Mengembalikan [code, side] atau NULL. */
    public function match($uri)
    {
        $uri = trim(strtolower((string) parse_url((string) $uri, PHP_URL_PATH)), '/');
        $best = NULL; $len = -1;
        foreach ($this->catalog as $code => $f) {
            foreach (array('admin', 'warga') as $side) {
                foreach ((array) ($f[$side] ?? array()) as $prefix) {
                    if (($uri === $prefix || strpos($uri, $prefix . '/') === 0) && strlen($prefix) > $len) {$best = array($code, $side); $len = strlen($prefix);}
                }
            }
        }
        return $best;
    }

    /** Untuk menyaring tautan menu: apakah URI boleh ditampilkan bagi sisi tertentu. */
    public function link_ok($uri, $audience = 'auto')
    {
        $m = $this->match($uri);
        if (!$m) return TRUE;
        $audience = $audience === 'auto' ? $m[1] : $audience;
        return $audience === 'admin' ? $this->for_staff($m[0]) : $this->for_residents($m[0]);
    }

    /**
     * Penegakan di server (hook). Pengurus: fitur nonaktif ditolak, kecuali role 'admin' yang tetap boleh membuka
     * halaman pengurus untuk menyelesaikan transaksi lama (Bagian 4C). Warga/publik: selalu ditolak.
     */
    public function enforce()
    {
        if (is_cli()) return;
        $this->CI->load->library('Platform_owner');
        if($this->CI->platform_owner->valid_session())return;
        $uri = uri_string();
        $m = $this->match($uri);
        if (!$m && strtolower($this->CI->router->class)==='admin' && in_array(strtolower($this->CI->router->method),array('posts','post'),TRUE)) {
            $type=$this->CI->uri->rsegment(3) ?: 'pengumuman';
            $m=$this->match('admin/posts/'.$type);
        }
        // CI3 also exposes controller/method URLs. Resolve aliases by router output,
        // so /sosial/index cannot bypass the /admin/bansos commercial gate.
        if (!$m) {
            $actual=implode('/', $this->CI->uri->rsegment_array());
            foreach ((array)$this->CI->router->routes as $source=>$target) {
                if (!is_string($target) || !is_string($source)) continue;
                $pattern=preg_quote($target,'#');
                $pattern=preg_replace('/\\\\\$[0-9]+/','([^/]+)',$pattern);
                if (!preg_match('#^'.$pattern.'$#i',$actual,$captures)) continue;
                $n=0;
                $canonical=preg_replace_callback('/\(:(?:num|any)\)/',function()use(&$n,$captures){return $captures[++$n]??'0';},$source);
                $candidate=$this->match($canonical);
                if ($candidate) { $m=$candidate; break; }
            }
        }
        if (!$m) return;
        list($code, $side) = $m;
        $commercial = $this->commercial($code);
        if ($commercial !== 'ALLOW') {
            if ($side === 'admin') {
                $id = (int)$this->CI->session->userdata('admin_id');
                if (!$id) redirect('admin/login');
                $account = $this->CI->db->get_where('admins', array('id'=>$id))->row_array();
                if (!$account || (isset($account['is_active']) && !$account['is_active'])) redirect('admin/login');
                if (!$this->subscription_role_ok($code, $account['role'])) show_error('Akses tidak diizinkan untuk peran Anda.',403);
            } elseif (array_filter((array)($this->catalog[$code]['warga']??array()),function($prefix){return strpos($prefix,'warga/')===0;})) {
                $id=(int)$this->CI->session->userdata('citizen_id');
                if (!$id || !$this->CI->db->get_where('citizens',array('id'=>$id,'is_active'=>1))->num_rows()) redirect('warga/login');
            }
            if (!$this->operational($code,$side)) $commercial='FEATURE_DISABLED';
            $this->subscription_deny($code, $side, $commercial);
        }
        if ($side === 'admin') {
            if ($this->for_staff($code)) return;
            if ($this->CI->session->userdata('admin_role') === 'admin' && $this->CI->session->userdata('admin_id')) {
                $this->CI->load->vars(array('feature_off_notice' => $this->catalog[$code]['name']));
                return;
            }
        } elseif ($this->for_residents($code)) {
            return;
        }
        $this->deny($code, $side);
    }

    /** Also follows dependencies; commercial gates never use the legacy admin bypass. */
    public function commercial($code, $depth = 0)
    {
        if (!isset($this->catalog[$code])) return 'ALLOW';
        if ($depth > 5) return 'FEATURE_DISABLED';
        $this->CI->load->model('Subscription_model');
        $result = $this->CI->Subscription_model->decision($code, $this->tenant);
        if ($result !== 'ALLOW') return $result;
        $parent = $this->catalog[$code]['parent'] ?? NULL;
        return $parent ? $this->commercial($parent, $depth + 1) : 'ALLOW';
    }

    public function subscription_role_ok($code, $role)
    {
        $this->CI->config->load('rt_access', TRUE);
        $permissions = (array)$this->CI->config->item('rt_permissions','rt_access');
        $map = array('resident_profile'=>'penduduk.read','resident_mutation'=>'mutasi.read','social_aid'=>'bansos.read','complaints'=>'pengaduan.read','dues_arrears'=>'iuran.report','cash_reconciliation'=>'kas.report','patrol'=>'ronda.read','work_program'=>'program.read','activity_notes'=>'notulen.read','inventory'=>'aset.read','purchasing'=>'belanja.read','documents'=>'arsip.read','contacts'=>'kontak.read');
        $roles = isset($map[$code]) ? ($permissions[$map[$code]] ?? array()) : (in_array($code,array('dues','cash_report'),TRUE) ? array('admin','ketua','bendahara') : array('admin','ketua','operator'));
        return in_array($role,$roles,TRUE);
    }

    public function menu_visible($uri)
    {
        if ($this->link_ok($uri,'admin')) return TRUE;
        $m = $this->match($uri);
        if (!$m || $this->commercial($m[0]) !== 'SUBSCRIPTION_REQUIRED') return FALSE;
        return $this->operational($m[0],'admin');
    }

    public function operational($code, $side='admin')
    {
        $depth=0;
        do {
            $row=$this->row($code);
            if (!$row['enabled'] || !$row[$side==='admin'?'visible_to_staff':'visible_to_residents']) return FALSE;
            $code=$this->catalog[$code]['parent'] ?? NULL;
        } while ($code && ++$depth < 6);
        return TRUE;
    }

    private function subscription_deny($code, $side, $error)
    {
        set_status_header(403);
        header('Cache-Control: no-store, private');
        if ($this->CI->input->is_ajax_request() || stripos((string)$this->CI->input->server('HTTP_ACCEPT'),'application/json') !== FALSE) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error'=>$error,'feature'=>$code,'message'=>$error==='SUBSCRIPTION_REQUIRED'?'Fitur memerlukan langganan aktif.':'Fitur tidak tersedia.'));
        } else {
            echo $this->CI->load->view('errors/subscription', array('name'=>$this->catalog[$code]['name'],'error'=>$error,'side'=>$side), TRUE);
        }
        exit;
    }

    private function deny($code, $side)
    {
        $name = $this->catalog[$code]['name'] ?? 'Layanan';
        $ajax = $this->CI->input->is_ajax_request() || stripos((string) $this->CI->input->server('HTTP_ACCEPT'), 'application/json') !== FALSE;
        set_status_header(403);
        header('Cache-Control: no-store, private');
        if ($ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'feature_disabled', 'message' => 'Layanan sedang tidak tersedia.'));
        } else {
            $back = $side === 'admin' ? site_url('admin') : site_url($this->CI->session->userdata('citizen_id') ? 'warga' : '');
            echo $this->CI->load->view('errors/feature_off', array('name' => $name, 'back' => $back), TRUE);
        }
        exit;
    }

    /** Jumlah transaksi belum selesai (untuk peringatan dampak). NULL bila tidak relevan/tabel tak ada. */
    public function open_count($code)
    {
        $sql = $this->catalog[$code]['open'] ?? '';
        if ($sql === '') return NULL;
        $db = $this->CI->db; $debug = $db->db_debug; $db->db_debug = FALSE;
        $q = $db->query($sql); $db->db_debug = $debug;
        return $q ? (int) ($q->row_array()['n'] ?? 0) : NULL;
    }
}

/** Hook: dipasang di config/hooks.php. */
function feature_gate_hook()
{
    $CI =& get_instance();
    if (!isset($CI->db)) $CI->load->database();
    $CI->load->model('Ops_model');
    $CI->Ops_model->ensure();
    $CI->load->library('Feature_gate');
    $CI->feature_gate->enforce();
}
