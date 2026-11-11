<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 14 Arsip Digital, 15 Serah Terima Pengurus, 16 Backup & Audit,
 * serta manajemen akun pengurus (role) dan riwayat pengaturan (Modul 06).
 */
class Sistem extends Ops_Controller
{
    private $doc_categories = array('surat_keluar' => 'Surat Keluar', 'surat_masuk' => 'Surat Masuk', 'sk' => 'SK / Keputusan', 'notulen' => 'Notulen', 'keuangan' => 'Laporan Keuangan',
        'aset' => 'Dokumen Aset', 'program' => 'Program Kerja', 'serah_terima' => 'Berita Acara', 'umum' => 'Umum');
    private $levels = array('umum' => 'Umum (semua pengurus berizin)', 'internal' => 'Internal', 'rahasia' => 'Rahasia (Ketua/Admin saja)');
    private $doc_types = array('pdf', 'jpg', 'png', 'webp', 'docx', 'xlsx', 'doc', 'xls', 'txt', 'csv');
    private $checklist = array('warga' => 'Rekap data warga & KK (agregat)', 'kas' => 'Saldo kas & bukti transaksi', 'tunggakan' => 'Daftar tunggakan iuran', 'aset' => 'Aset & inventaris (fisik dicek)',
        'program' => 'Program kerja berjalan', 'dokumen' => 'Arsip dokumen & stempel', 'akun' => 'Akun aplikasi: rotasi password & cabut akses pengurus lama');

    /* ===================== 14 ARSIP ===================== */

    private function doc_scope()
    {
        $this->db->where('d.deleted_at IS NULL', NULL, FALSE);
        if (!$this->can('arsip.confidential')) $this->db->where('d.confidentiality !=', 'rahasia');
    }

    public function arsip()
    {
        $this->guard_admin('arsip.read');
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('arsip.write');
            $this->load->library('Private_storage');
            $data = array('title' => $this->str('title', 190), 'category' => $this->choice('category', $this->doc_categories, 'umum'), 'doc_number' => $this->str('doc_number', 100),
                'period' => $this->str('period', 40), 'doc_date' => $this->date_or_null('doc_date'), 'module' => $this->str('module', 30) ?: 'umum',
                'confidentiality' => $this->choice('confidentiality', $this->levels, 'internal'), 'retention_until' => $this->date_or_null('retention_until'));
            if ($data['confidentiality'] === 'rahasia' && !$this->can('arsip.confidential')) $data['confidentiality'] = 'internal';
            $file = NULL;
            try {
                if (mb_strlen($data['title']) < 3) throw new RuntimeException('Judul dokumen wajib diisi.');
                $file = $this->private_storage->store('document', 'documents', $error, $this->doc_types, 10485760);
                if ($error) throw new RuntimeException($error);
                if (!$file) throw new RuntimeException('Pilih berkas dokumen.');
                $id = $this->atomic(function () use ($data, $file) {
                    $this->db->insert('documents', $data + array('uploaded_by' => $this->admin_id, 'current_version' => 1));
                    $id = (int) $this->db->insert_id();
                    $this->db->insert('document_versions', array('document_id' => $id, 'version' => 1, 'stored_name' => $file['stored_name'], 'original_name' => $file['original_name'],
                        'mime' => $file['mime'], 'size_bytes' => $file['size'], 'checksum' => $file['checksum'], 'uploaded_by' => $this->admin_id, 'note' => 'Versi awal'));
                    return $id;
                });
                $this->audit('upload', 'document:' . $id, $data['confidentiality']);
                $this->back('admin/arsip/' . $id, 'Dokumen diarsipkan secara privat.');
            } catch (RuntimeException $e) {
                if ($file) $this->private_storage->delete('documents', $file['stored_name']);
                $error = $e->getMessage();
            }
        }
        $q = $this->str('q', 80, 'get');
        $cat = $this->choice('kategori', $this->doc_categories, '', 'get');
        $this->db->select('d.*,a.username')->from('documents d')->join('admins a', 'a.id=d.uploaded_by', 'left');
        $this->doc_scope();
        if ($cat) $this->db->where('d.category', $cat);
        if ($q !== '') $this->db->group_start()->like('d.title', $q)->or_like('d.doc_number', $q)->or_like('d.period', $q)->group_end();
        $items = $this->db->order_by('d.id', 'DESC')->limit(200)->get()->result_array();
        $this->render('arsip_index', array('items' => $items, 'q' => $q, 'cat' => $cat, 'categories' => $this->doc_categories, 'levels' => $this->levels, 'error' => $error));
    }

    public function arsip_detail($id = 0)
    {
        $this->guard_admin('arsip.read');
        $id = (int) $id;
        $this->db->from('documents d')->where('d.id', $id);
        $this->doc_scope();
        $doc = $this->db->get()->row_array();
        if (!$doc) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('arsip.write');
            $act = $this->str('act', 20);
            $this->load->library('Private_storage');
            $file = NULL;
            try {
                if ($act === 'version') {
                    $err = '';
                    $file = $this->private_storage->store('document', 'documents', $err, $this->doc_types, 10485760);
                    if ($err) throw new RuntimeException($err);
                    if (!$file) throw new RuntimeException('Pilih berkas versi baru.');
                    $this->atomic(function () use ($id, $file) {
                        $d = $this->lock('documents', $id);
                        $v = (int) $d['current_version'] + 1;
                        $this->db->insert('document_versions', array('document_id' => $id, 'version' => $v, 'stored_name' => $file['stored_name'], 'original_name' => $file['original_name'],
                            'mime' => $file['mime'], 'size_bytes' => $file['size'], 'checksum' => $file['checksum'], 'uploaded_by' => $this->admin_id, 'note' => $this->str('note', 255)));
                        $this->db->where('id', $id)->update('documents', array('current_version' => $v, 'updated_at' => date('Y-m-d H:i:s')));
                    });
                    $this->audit('new_version', 'document:' . $id);
                } elseif ($act === 'delete') {
                    $reason = $this->str('reason', 255);
                    if (mb_strlen($reason) < 3) throw new RuntimeException('Tuliskan alasan penghapusan.');
                    $this->db->where('id', $id)->update('documents', array('deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $this->admin_id));
                    $this->audit('soft_delete', 'document:' . $id, $reason);
                    $this->back('admin/arsip', 'Dokumen dipindahkan ke arsip terhapus (soft delete). Berkas tetap tersimpan untuk audit.');
                } else {
                    throw new RuntimeException('Aksi tidak dikenal.');
                }
                $this->back('admin/arsip/' . $id, 'Versi baru diunggah.');
            } catch (RuntimeException $e) {
                if ($file) $this->private_storage->delete('documents', $file['stored_name']);
                $this->back('admin/arsip/' . $id, '', $e->getMessage());
            }
        }
        $versions = $this->db->select('v.*,a.username')->from('document_versions v')->join('admins a', 'a.id=v.uploaded_by', 'left')->where('v.document_id', $id)->order_by('v.version', 'DESC')->get()->result_array();
        $this->render('arsip_detail', array('doc' => $doc, 'versions' => $versions, 'categories' => $this->doc_categories, 'levels' => $this->levels));
    }

    public function arsip_unduh($id = 0, $version = 0)
    {
        $this->guard_admin('arsip.read');
        $this->db->from('documents d')->where('d.id', (int) $id);
        $this->doc_scope();
        $doc = $this->db->get()->row_array();
        if (!$doc) show_error('Dokumen tidak ditemukan atau Anda tidak berhak mengaksesnya.', 403);
        $v = $this->db->get_where('document_versions', array('document_id' => (int) $id, 'version' => (int) $version ?: (int) $doc['current_version']))->row_array();
        if (!$v) show_404();
        $this->load->library('Private_storage');
        $path = $this->private_storage->path('documents', $v['stored_name']);
        if (!$path || !hash_equals($v['checksum'], hash_file('sha256', $path))) show_error('Integritas berkas tidak valid (checksum berbeda). Hubungi administrator.', 500);
        $this->audit('download', 'document:' . (int) $id, 'v' . (int) $v['version']);
        $this->private_storage->send('documents', $v['stored_name'], $v['original_name'], FALSE);
    }

    /* ===================== 15 SERAH TERIMA ===================== */

    public function serah_terima()
    {
        $this->guard_admin('serahterima.read');
        // Pilihan pengurus diambil dari Manajemen Pengurus (nama — jabatan), boleh diisi manual bila belum terdaftar.
        $officials = $this->db->table_exists('officials') ? $this->db->select('id,name,position,is_active')->order_by('is_active', 'DESC')->order_by('sort_order')->order_by('id')->get('officials')->result_array() : array();
        $people = array();
        foreach ($officials as $o) $people['o' . $o['id']] = $o['name'] . ($o['position'] !== '' ? ' — ' . $o['position'] : '');
        $old = array();
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('serahterima.write');
            if ($this->str('act', 20) === 'delete') {
                $sid = (int) $this->input->post('id');
                $s = $this->db->get_where('handover_sessions', array('id' => $sid))->row_array();
                if (!$s) show_404();
                if ($s['status'] !== 'draft') $this->back('admin/serah-terima', '', 'Hanya sesi berstatus Draf yang dapat dihapus.');
                $this->db->trans_start();
                $this->db->delete('handover_signoffs', array('session_id' => $sid));
                $this->db->delete('handover_sessions', array('id' => $sid, 'status' => 'draft'));
                $this->db->trans_complete();
                $this->audit('delete', 'handover:' . $sid, $s['title']);
                $this->back('admin/serah-terima', 'Sesi draf "' . $s['title'] . '" dihapus.');
            }
            $pick = function ($field) use ($people) {
                $v = $this->str($field, 20);
                if ($v === '__other') return $this->str($field . '_other', 255);
                return isset($people[$v]) ? $people[$v] : '';
            };
            $title = $this->str('title', 160);
            $period = trim(preg_replace('/\s*[-–—]\s*/u', ' – ', $this->str('period_label', 60)));
            $outgoing = $pick('outgoing'); $incoming = $pick('incoming');
            $old = array('title' => $title, 'period_label' => $period, 'outgoing' => $this->str('outgoing', 20), 'incoming' => $this->str('incoming', 20),
                'outgoing_other' => $this->str('outgoing_other', 255), 'incoming_other' => $this->str('incoming_other', 255));
            $error = '';
            if (mb_strlen($title) < 3) $error = 'Judul sesi wajib diisi (minimal 3 karakter).';
            elseif (!preg_match('/^\d{4} – \d{4}$/u', $period)) $error = 'Periode kepengurusan wajib diisi dengan format tahun, contoh: 2026 – 2029.';
            elseif ((int) substr($period, -4) <= (int) substr($period, 0, 4)) $error = 'Tahun akhir periode harus lebih besar dari tahun awal.';
            elseif (mb_strlen($outgoing) < 2) $error = 'Pilih atau isi pengurus lama.';
            elseif (mb_strlen($incoming) < 2) $error = 'Pilih atau isi pengurus baru.';
            elseif (mb_strtolower($outgoing) === mb_strtolower($incoming)) $error = 'Pengurus lama dan pengurus baru tidak boleh sama.';
            if ($error === '') {
                $snapshot = $this->snapshot();
                $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE);
                $checklist = array();
                foreach ($this->checklist as $k => $label) $checklist[$k] = array('label' => $label, 'status' => 'belum', 'note' => '');
                $this->db->insert('handover_sessions', array('title' => $title, 'period_label' => $period, 'outgoing' => $outgoing, 'incoming' => $incoming,
                    'snapshot_json' => $json, 'snapshot_hash' => hash('sha256', $json), 'checklist_json' => json_encode($checklist, JSON_UNESCAPED_UNICODE), 'status' => 'draft', 'created_by' => $this->admin_id));
                $id = (int) $this->db->insert_id();
                $this->audit('create', 'handover:' . $id);
                $this->back('admin/serah-terima/' . $id, 'Sesi serah terima dibuat dengan snapshot data saat ini. Lanjutkan dengan melengkapi checklist dan persetujuan.');
            }
            $this->session->set_flashdata('ops_error', $error);
            $this->session->set_flashdata('handover_old', $old);
            redirect('admin/serah-terima#buat-sesi');
        }
        $all = $this->db->order_by('id', 'DESC')->get('handover_sessions')->result_array();
        $period = $this->str('period', 60, 'get');
        $items = $period === '' ? $all : array_values(array_filter($all, function ($s) use ($period) {return $s['period_label'] === $period;}));
        $per = 10; $total = count($items); $pages = max(1, (int) ceil($total / $per)); $page = min($pages, max(1, (int) $this->input->get('page')));
        $this->render('serah_index', array('items' => array_slice($items, ($page - 1) * $per, $per), 'total' => $total, 'page' => $page, 'pages' => $pages,
            'periods' => array_values(array_unique(array_filter(array_column($all, 'period_label')))), 'period' => $period, 'people' => $people,
            'old' => $this->session->flashdata('handover_old') ?: array()));
    }

    public function serah_detail($id = 0)
    {
        $this->guard_admin('serahterima.read');
        $id = (int) $id;
        $s = $this->db->get_where('handover_sessions', array('id' => $id))->row_array();
        if (!$s) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 20);
            try {
                $this->atomic(function () use ($id, $act) {
                    $s = $this->lock('handover_sessions', $id);
                    if ($s['status'] === 'final') throw new RuntimeException('Berita acara sudah final dan tidak dapat diubah.');
                    if ($act === 'checklist') {
                        $this->need('serahterima.write');
                        $list = json_decode($s['checklist_json'], TRUE) ?: array();
                        foreach ($list as $k => &$row) {
                            $st = (string) ($this->input->post('status')[$k] ?? 'belum');
                            $row['status'] = in_array($st, array('belum', 'sesuai', 'pengecualian'), TRUE) ? $st : 'belum';
                            $row['note'] = mb_substr(trim((string) ($this->input->post('note', TRUE)[$k] ?? '')), 0, 300);
                        }
                        unset($row);
                        $this->db->where('id', $id)->update('handover_sessions', array('checklist_json' => json_encode($list, JSON_UNESCAPED_UNICODE), 'exceptions' => $this->text('exceptions', 3000),
                            'status' => 'review', 'updated_at' => date('Y-m-d H:i:s')));
                    } elseif ($act === 'sign') {
                        if ($this->role === 'auditor') throw new RuntimeException('Auditor hanya dapat meninjau, tidak menandatangani berita acara.');
                        $side = $this->choice('side', array('lama' => 1, 'baru' => 1));
                        if (!$side) throw new RuntimeException('Pilih pihak (lama/baru).');
                        if ($this->db->get_where('handover_signoffs', array('session_id' => $id, 'admin_id' => $this->admin_id))->row_array()) throw new RuntimeException('Anda sudah menandatangani sesi ini.');
                        $roles = $this->config->item('rt_roles', 'rt_access');
                        $this->db->insert('handover_signoffs', array('session_id' => $id, 'admin_id' => $this->admin_id, 'side' => $side, 'role_label' => $roles[$this->role] ?? $this->role, 'note' => $this->str('note', 255)));
                    } elseif ($act === 'finalize') {
                        $this->need('serahterima.finalize');
                        $list = json_decode($s['checklist_json'], TRUE) ?: array();
                        foreach ($list as $row) if ($row['status'] === 'belum') throw new RuntimeException('Semua item checklist harus diperiksa (sesuai/pengecualian) sebelum final.');
                        $sides = array_column($this->db->get_where('handover_signoffs', array('session_id' => $id))->result_array(), 'side');
                        if (!in_array('lama', $sides, TRUE) || !in_array('baru', $sides, TRUE)) throw new RuntimeException('Butuh persetujuan minimal satu pengurus lama dan satu pengurus baru.');
                        if (!hash_equals((string) $s['snapshot_hash'], hash('sha256', (string) $s['snapshot_json']))) throw new RuntimeException('Snapshot tidak utuh (hash berbeda).');
                        $this->db->where('id', $id)->update('handover_sessions', array('status' => 'final', 'finalized_by' => $this->admin_id, 'finalized_at' => date('Y-m-d H:i:s')));
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->audit('handover_' . $act, 'handover:' . $id);
                });
                $this->back('admin/serah-terima/' . $id, 'Serah terima diperbarui.');
            } catch (RuntimeException $e) {
                $this->back('admin/serah-terima/' . $id, '', $e->getMessage());
            }
        }
        $signoffs = $this->db->select('h.*,a.username')->from('handover_signoffs h')->join('admins a', 'a.id=h.admin_id', 'left')->where('h.session_id', $id)->order_by('h.id')->get()->result_array();
        $this->render('serah_detail', array('s' => $s, 'snapshot' => json_decode((string) $s['snapshot_json'], TRUE) ?: array(), 'checklist' => json_decode((string) $s['checklist_json'], TRUE) ?: array(),
            'signoffs' => $signoffs, 'intact' => hash_equals((string) $s['snapshot_hash'], hash('sha256', (string) $s['snapshot_json']))));
    }

    /** Snapshot agregat — tanpa identitas warga (tidak ada NIK/nama). */
    private function snapshot()
    {
        $m = $this->Ops_model->monitoring();
        return array(
            'dibuat' => date('Y-m-d H:i:s'),
            'kk_terverifikasi' => $m['kk_active'], 'anggota_aktif' => $m['members_active'], 'akun_warga_aktif' => (int) $this->db->where('is_active', 1)->count_all_results('citizens'),
            'saldo_kas' => $m['cash_balance'], 'tunggakan_tagihan' => $m['arrears_count'], 'tunggakan_nilai' => $m['arrears_total'],
            'aset_jenis' => (int) $this->db->where('status !=', 'dihapus')->count_all_results('assets'),
            'aset_dipinjam' => (int) $this->db->where('status', 'borrowed')->count_all_results('asset_loans'),
            'program_berjalan' => array_column($this->db->select('title')->where_in('status', array('aktif', 'ditunda'))->where('deleted_at IS NULL', NULL, FALSE)->get('work_programs')->result_array(), 'title'),
            'dokumen_arsip' => (int) $this->db->where('deleted_at IS NULL', NULL, FALSE)->count_all_results('documents'),
            'pengaduan_terbuka' => $m['complaints_open'], 'mutasi_menunggu' => $m['mutations_pending'],
            'backup_terakhir' => $m['last_backup']['created_at'] ?? NULL,
        );
    }

    /* ===================== AKUN PENGURUS & ROLE ===================== */

    public function akun($id = 0)
    {
        $this->guard_admin('akun.manage');
        $base_roles = $this->config->item('rt_roles', 'rt_access');
        $this->load->library(array('Menu_access', 'Feature_gate'));
        $this->menu_access->ensure();
        // Peran dasar + peran tambahan (turunan). Akun dengan peran tambahan menyimpan peran dasarnya di admins.role.
        $roles = $this->menu_access->all_roles();
        $id = (int) $id;
        $this->load->library('Platform_owner');
        $item = $id ? $this->db->get_where('admins', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->db->trans_begin();
            $this->db->query("SELECT id FROM admins WHERE role='admin' FOR UPDATE");
            if(in_array($this->input->post('role'),array('super_admin','central_admin'),TRUE)){$this->db->trans_rollback();show_error('Admin Pusat / Super Admin terkunci untuk akun pengurus.',403);}
            $username = $this->str('username', 80);
            $chosen = $this->choice('role', $roles);
            $role = $chosen ? $this->menu_access->base_role($chosen) : '';
            $custom = ($chosen && $this->menu_access->is_custom($chosen)) ? $chosen : NULL;
            $active = $this->input->post('is_active') ? 1 : 0;
            $password = (string) $this->input->post('password');
            if (!$id && !preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username)) $error = 'Username 3–80 karakter (huruf, angka, titik, garis).';
            elseif (!$role) $error = 'Pilih peran.';
            elseif(!$id&&$this->platform_owner->recognizes($username))$error='Username tidak tersedia untuk akun pengurus.';
            elseif (!$id && strlen($password) < 10) $error = 'Password awal minimal 10 karakter.';
            elseif ($password !== '' && strlen($password) < 10) $error = 'Password minimal 10 karakter.';
            elseif (!$id && $this->db->get_where('admins', array('username' => $username))->row_array()) $error = 'Username sudah dipakai.';
            elseif ($id === $this->admin_id && (!$active || $role !== $this->role)) $error = 'Anda tidak dapat menonaktifkan atau menurunkan peran akun sendiri.';
            elseif ($id && in_array($item['role'],array('admin'),TRUE) && (!in_array($role,array('admin'),TRUE)||!$active) && !$this->db->where_in('role',array('admin'))->where('is_active',1)->where('id !=',$id)->count_all_results('admins')) $error='Harus tersisa minimal satu Administrator aktif.';
            if (!$error) {
                $data = array('role' => $role, 'custom_role' => $custom, 'is_active' => $active, 'full_name' => $this->str('full_name', 140) ?: NULL);
                if ($password !== '') $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                if ($id) $this->db->where('id', $id)->update('admins', $data);
                else {$this->db->insert('admins', $data + array('username' => $username, 'created_at' => date('Y-m-d H:i:s'))); $id = (int) $this->db->insert_id();}
                $what = array();
                if ($item && ($item['role'] !== $role || (string) ($item['custom_role'] ?? '') !== (string) $custom)) $what[] = 'role ' . ($item['custom_role'] ?: $item['role']) . '->' . ($custom ?: $role);
                if ($item && (int) $item['is_active'] !== $active) $what[] = $active ? 'diaktifkan' : 'dicabut';
                if ($password !== '') $what[] = 'password direset';
                $this->audit($item ? 'update' : 'create', 'admin:' . $id, implode(', ', $what));
                if(!$this->db->trans_status()){$this->db->trans_rollback();show_error('Akun gagal disimpan.',500);}
                $this->db->trans_commit();
                $this->back('admin/akun', 'Akun pengurus disimpan.' . ($item && !$active ? ' Sesi akun tersebut otomatis berakhir pada permintaan berikutnya.' : ''));
            }
            $this->db->trans_rollback();
            $item = array_merge($item ?: array(), array('username' => $username, 'role' => $role, 'custom_role' => $custom, 'is_active' => $active, 'full_name' => $this->str('full_name', 140)));
        }
        $items = $this->db->where_in('role',array_keys($base_roles))->order_by('is_active', 'DESC')->order_by('username')->get('admins')->result_array();
        foreach ($items as &$a) $a['role_key'] = $this->menu_access->account_role($a);
        unset($a);
        if ($item) $item['role_key'] = $this->menu_access->account_role($item + array('custom_role' => NULL));
        $perms = $this->config->item('rt_permissions', 'rt_access');
        $role_options=$roles;
        $this->config->load('admin_menu', TRUE);
        $selected_role = $this->choice('role', $roles, 'admin', 'get');
        $tab = $this->choice('tab', array('akun' => 1, 'peran' => 1, 'menu' => 1), 'akun', 'get');
        $role_counts = array_fill_keys(array_keys($roles), 0);
        foreach ($items as $a) if ((int) ($a['is_active'] ?? 1) && isset($role_counts[$a['role_key']])) $role_counts[$a['role_key']]++;
        $this->render('akun', array('items' => $items, 'item' => $item, 'roles' => $roles,'role_options'=>$role_options, 'error' => $error, 'perms' => $perms,
            'menu_access' => $this->menu_access, 'feature_gate' => $this->feature_gate, 'selected_role' => $selected_role, 'tab' => $tab, 'role_counts' => $role_counts,
            'role_descriptions' => (array) $this->config->item('admin_role_descriptions', 'admin_menu'), 'base_roles' => $base_roles,
            'custom_roles' => $this->menu_access->custom_roles()));
    }

    /** Tambah / ubah / hapus peran tambahan (turunan peran dasar). */
    public function akun_peran()
    {
        $this->guard_admin('akun.manage');
        $this->post_only();
        $this->load->library('Menu_access');
        $this->menu_access->ensure();
        $action = (string) $this->input->post('action');
        try {
            if ($action === 'create') {
                $key = $this->menu_access->create_custom_role($this->str('label', 80), $this->str('description', 255), $this->str('base_role', 40), $this->admin_id);
                $this->audit('create', 'role:' . $key, 'peran tambahan "' . $this->str('label', 80) . '" turunan ' . $this->str('base_role', 40));
                $this->back('admin/akun?tab=peran&role=' . rawurlencode($key), 'Peran ' . $this->str('label', 80) . ' ditambahkan. Atur hak akses menunya lalu simpan.');
            }
            $key = $this->str('role', 40);
            if ($action === 'update') {
                $this->menu_access->update_custom_role($key, $this->str('label', 80), $this->str('description', 255));
                $this->audit('update', 'role:' . $key, 'nama/deskripsi peran diubah');
                $this->back('admin/akun?tab=peran&role=' . rawurlencode($key), 'Peran diperbarui.');
            }
            if ($action === 'delete') {
                $this->menu_access->delete_custom_role($key);
                $this->audit('delete', 'role:' . $key, 'peran tambahan dihapus');
                $this->back('admin/akun?tab=peran', 'Peran tambahan dihapus.');
            }
            throw new RuntimeException('Aksi tidak dikenal.');
        } catch (RuntimeException $e) {
            $this->back('admin/akun?tab=peran' . ($action !== 'create' && $this->str('role', 40) ? '&role=' . rawurlencode($this->str('role', 40)) : ''), '', $e->getMessage());
        }
    }

    /** Simpan / pulihkan Hak Akses Menu untuk satu peran (POST). Hanya mengurangi akses dari RBAC dasar. */
    public function akun_hak_akses()
    {
        $this->guard_admin('akun.manage');
        $this->post_only();
        $this->load->library('Menu_access');
        $this->menu_access->ensure();
        $roles = $this->menu_access->all_roles();
        $role = $this->choice('role', $roles);
        if (!$role) $this->back('admin/akun?tab=peran', '', 'Pilih peran yang valid.');
        $back = 'admin/akun?role=' . rawurlencode($role) . '&tab=' . ($this->choice('tab', array('akun' => 1, 'peran' => 1, 'menu' => 1), 'akun'));
        try {
            if ($this->input->post('action') === 'default') {
                $this->menu_access->reset($role);
                $this->audit('reset', 'role_menu:' . $role, 'hak akses menu dipulihkan ke bawaan peran');
                $this->back($back, 'Hak akses menu ' . $roles[$role] . ' dipulihkan ke bawaan.');
            }
            $menus = $this->input->post('menus');
            $menus = is_array($menus) ? array_filter($menus, 'is_string') : array();
            $changes = $this->menu_access->save($role, $menus, $this->admin_id);
            $detail = array();
            if ($changes['off']) $detail[] = 'dimatikan: ' . implode(', ', $changes['off']);
            if ($changes['on']) $detail[] = 'diaktifkan: ' . implode(', ', $changes['on']);
            $this->audit('update', 'role_menu:' . $role, $detail ? implode('; ', $detail) : 'tanpa perubahan');
            $this->back($back, $detail ? 'Hak akses menu ' . $roles[$role] . ' disimpan.' : 'Tidak ada perubahan hak akses menu.');
        } catch (RuntimeException $e) {
            $this->back($back, '', $e->getMessage());
        }
    }

    /* ===================== 16 AUDIT ===================== */

    public function audit_log()
    {
        $this->guard_admin('audit.read');
        $f = array('q' => $this->str('q', 80, 'get'), 'admin' => (int) $this->input->get('admin'), 'from' => $this->str('dari', 10, 'get'), 'to' => $this->str('sampai', 10, 'get'));
        $apply = function () use ($f) {
            $this->db->from('activity_logs l')->join('admins a', 'a.id=l.admin_id', 'left');
            if ($f['q'] !== '') $this->db->group_start()->like('l.action', $f['q'])->or_like('l.subject', $f['q'])->or_like('l.details', $f['q'])->group_end();
            if ($f['admin']) $this->db->where('l.admin_id', $f['admin']);
            if (ops_valid_date($f['from'])) $this->db->where('l.created_at >=', $f['from'] . ' 00:00:00');
            if (ops_valid_date($f['to'])) $this->db->where('l.created_at <=', $f['to'] . ' 23:59:59');
        };
        if ($this->input->get('export') === 'csv') {
            $apply();
            $rows = $this->db->select('l.created_at,a.username,l.action,l.subject,l.details,l.ip_address')->order_by('l.id', 'DESC')->limit(5000)->get()->result_array();
            $this->audit('export', 'audit_log', count($rows) . ' baris');
            $this->csv('audit-log-' . date('Ymd') . '.csv', array('Waktu', 'Pengguna', 'Aksi', 'Objek', 'Detail', 'IP'), array_map('array_values', $rows));
        }
        $page = max(1, (int) $this->input->get('hal'));
        $apply();
        $total = (int) $this->db->count_all_results();
        $apply();
        $rows = $this->db->select('l.*,a.username')->order_by('l.id', 'DESC')->limit(50, ($page - 1) * 50)->get()->result_array();
        $admins = array();
        foreach ($this->db->select('id,username')->order_by('username')->get('admins')->result_array() as $a) $admins[(int) $a['id']] = $a['username'];
        $this->render('audit', array('rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max(1, (int) ceil($total / 50)), 'f' => $f, 'admins' => $admins));
    }

    /* ===================== 16 BACKUP ===================== */

    public function backup()
    {
        $this->guard_admin('backup.run');
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 20);
            if ($act === 'run') {
                $r = $this->run_backup($this->admin_id, $this->input->post('with_files') ? TRUE : FALSE);
                $this->back('admin/backup', $r['ok'] ? $r['message'] : '', $r['ok'] ? '' : $r['message']);
            }
            $run = $this->db->get_where('backup_runs', array('id' => (int) $this->input->post('run_id')))->row_array();
            if (!$run || !$run['file_name']) show_404();
            $this->load->library('Private_storage');
            $path = $this->private_storage->path('backups', $run['file_name']);
            if ($act === 'verify') {
                $ok = $path && hash_equals((string) $run['checksum'], hash_file('sha256', $path)) && $this->decrypt_file($path) !== NULL;
                if ($ok) $this->db->where('id', $run['id'])->update('backup_runs', array('verified_at' => date('Y-m-d H:i:s')));
                $this->audit('verify', 'backup:' . $run['id'], $ok ? 'ok' : 'gagal');
                $this->back('admin/backup', $ok ? 'Backup #' . $run['id'] . ' valid: checksum cocok dan dapat didekripsi.' : '', $ok ? '' : 'Backup #' . $run['id'] . ' GAGAL diverifikasi.');
            }
            if ($act === 'download') {
                if (!$path) show_404();
                $this->audit('download', 'backup:' . $run['id']);
                $this->private_storage->send('backups', $run['file_name'], $run['file_name'], FALSE);
            }
            show_404();
        }
        $runs = $this->db->order_by('id', 'DESC')->limit(30)->get('backup_runs')->result_array();
        $this->render('backup', array('runs' => $runs, 'zip' => class_exists('ZipArchive')));
    }

    /** Jalankan dari Task Scheduler / cron: php index.php sistem cli_backup [files] */
    public function cli_backup($files = '')
    {
        if (!is_cli()) show_404();
        $r = $this->run_backup(NULL, $files === 'files');
        echo ($r['ok'] ? 'OK: ' : 'GAGAL: ') . $r['message'] . PHP_EOL;
        exit($r['ok'] ? 0 : 1);
    }

    private function backup_key()
    {
        return hash('sha256', (string) $this->config->item('encryption_key') . '|rt-backup', TRUE);
    }

    private function encrypt_to($plain, $target)
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt(gzencode($plain, 6), 'aes-256-gcm', $this->backup_key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === FALSE) throw new RuntimeException('Enkripsi gagal.');
        if (file_put_contents($target, 'RTBK1' . $iv . $tag . $cipher) === FALSE) throw new RuntimeException('Berkas backup gagal ditulis.');
    }

    private function decrypt_file($path)
    {
        $raw = @file_get_contents($path);
        if (!$raw || substr($raw, 0, 5) !== 'RTBK1') return NULL;
        $plain = openssl_decrypt(substr($raw, 33), 'aes-256-gcm', $this->backup_key(), OPENSSL_RAW_DATA, substr($raw, 5, 12), substr($raw, 17, 16));
        return $plain === FALSE ? NULL : @gzdecode($plain);
    }

    private function run_backup($actor, $with_files)
    {
        $this->load->library('Private_storage');
        $dir = $this->private_storage->dir('backups');
        $stamp = date('Ymd-His');
        $made = array();
        try {
            if (!function_exists('openssl_encrypt')) throw new RuntimeException('Ekstensi OpenSSL tidak tersedia.');
            $this->load->dbutil();
            $sql = $this->dbutil->backup(array('format' => 'txt', 'add_drop' => TRUE, 'add_insert' => TRUE, 'newline' => "\n"));
            if (!is_string($sql) || strlen($sql) < 100) throw new RuntimeException('Dump database kosong.');
            $name = 'db-' . $stamp . '.sql.gz.enc';
            $this->encrypt_to("-- Backup RT/RW Digital " . date('c') . "\n" . $sql, $dir . $name);
            $made[] = array('database', $name);
            $files_error = '';
            if ($with_files && !class_exists('ZipArchive')) {
                // Backup database tetap disimpan; kegagalan bagian berkas dicatat terpisah.
                $files_error = 'Ekstensi ZipArchive tidak aktif; backup berkas dilewati.';
                $this->db->insert('backup_runs', array('kind' => 'files', 'status' => 'failed', 'message' => $files_error, 'triggered_by' => $actor));
            } elseif ($with_files) {
                // Bagian berkas gagal tidak boleh membatalkan backup database yang sudah jadi:
                // kegagalannya dicatat terpisah (sama seperti saat ZipArchive tidak aktif).
                $tmp = NULL;
                try {
                    // Enkripsi AES-GCM memproses berkas utuh di memori: naikkan batas khusus proses ini.
                    @ini_set('memory_limit', '768M');
                    @set_time_limit(600);
                    $total = 0;
                    foreach (array(APPPATH . 'storage', FCPATH . 'uploads') as $root) {
                        if (!is_dir($root)) continue;
                        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $f) {
                            if (strpos(str_replace('\\', '/', $f->getPathname()), '/storage/backups/') === FALSE) $total += $f->getSize();
                        }
                    }
                    if ($total > 200 * 1048576) throw new RuntimeException('Total berkas ' . round($total / 1048576) . ' MB melebihi batas 200 MB untuk backup berkas dari aplikasi; backup folder uploads & application/storage langsung di server.');
                    $tmp = tempnam(sys_get_temp_dir(), 'rtbk');
                    $zip = new ZipArchive();
                    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== TRUE) throw new RuntimeException('Arsip ZIP sementara tidak dapat dibuat.');
                    foreach (array(APPPATH . 'storage' => 'storage', FCPATH . 'uploads' => 'uploads') as $root => $prefix) {
                        if (!is_dir($root)) continue;
                        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
                        foreach ($it as $f) {
                            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
                            if (strpos($rel, 'backups/') === 0) continue;
                            $zip->addFile($f->getPathname(), $prefix . '/' . $rel);
                        }
                    }
                    if (!$zip->close()) throw new RuntimeException('Arsip ZIP gagal ditulis.');
                    $fname = 'files-' . $stamp . '.zip.enc';
                    $this->encrypt_to(file_get_contents($tmp), $dir . $fname);
                    $made[] = array('files', $fname);
                } catch (Throwable $fe) {
                    $files_error = 'Backup berkas gagal: ' . $fe->getMessage();
                    $this->db->insert('backup_runs', array('kind' => 'files', 'status' => 'failed', 'message' => mb_substr($files_error, 0, 500), 'triggered_by' => $actor));
                } finally {
                    if ($tmp) @unlink($tmp);
                }
            }
            foreach ($made as $m) {
                $this->db->insert('backup_runs', array('kind' => $m[0], 'status' => 'success', 'file_name' => $m[1], 'size_bytes' => filesize($dir . $m[1]),
                    'checksum' => hash_file('sha256', $dir . $m[1]), 'message' => 'Terenkripsi AES-256-GCM', 'triggered_by' => $actor));
            }
            $this->rotate($dir, 14);
            rt_audit('backup', 'backup_run', implode(', ', array_column($made, 1)), $actor ?: 0);
            return array('ok' => TRUE, 'message' => 'Backup selesai: ' . implode(', ', array_column($made, 1)) . '. Unduh dan simpan salinan di media terpisah (offsite).' . ($files_error ? ' Catatan: ' . $files_error : ''));
        } catch (Throwable $e) {
            foreach ($made as $m) @unlink($dir . $m[1]);
            $this->db->insert('backup_runs', array('kind' => 'database', 'status' => 'failed', 'message' => mb_substr($e->getMessage(), 0, 500), 'triggered_by' => $actor));
            rt_audit('backup_failed', 'backup_run', $e->getMessage(), $actor ?: 0);
            return array('ok' => FALSE, 'message' => 'Backup gagal: ' . $e->getMessage());
        }
    }

    /** Retensi: simpan 14 backup sukses terbaru per jenis; berkas lama dihapus, catatan tetap. */
    private function rotate($dir, $keep)
    {
        foreach (array('database', 'files') as $kind) {
            $old = $this->db->where('kind', $kind)->where('status', 'success')->where('file_name IS NOT NULL', NULL, FALSE)->order_by('id', 'DESC')->limit(1000, $keep)->get('backup_runs')->result_array();
            foreach ($old as $r) {
                @unlink($dir . basename($r['file_name']));
                $this->db->where('id', $r['id'])->update('backup_runs', array('file_name' => NULL, 'message' => 'Berkas dihapus oleh rotasi retensi'));
            }
        }
    }

    /* ===================== 06 RIWAYAT PENGATURAN ===================== */

    public function pengaturan_riwayat()
    {
        $this->guard_admin('pengaturan.history');
        // Kolom nama pelaku (non-destruktif) untuk perubahan oleh Admin Pusat yang tidak memiliki admin_id.
        if (!$this->db->field_exists('changed_by_name', 'settings_history')) $this->db->query('ALTER TABLE settings_history ADD COLUMN changed_by_name VARCHAR(80) NULL DEFAULT NULL');
        $filters = array('q' => $this->str('q', 100, 'get'), 'period' => $this->str('period', 10, 'get'), 'per' => (int) $this->input->get('per'), 'page' => max(1, (int) $this->input->get('page')));
        if (!in_array($filters['per'], array(10, 25, 50), TRUE)) $filters['per'] = 10;
        if (!preg_match('/^(|today|7|30|\d{4}-\d{2})$/', $filters['period'])) $filters['period'] = '';
        $back = 'admin/pengaturan/riwayat' . (($qs = http_build_query(array_filter(array('q' => $filters['q'], 'period' => $filters['period'], 'per' => $filters['per'] !== 10 ? $filters['per'] : NULL, 'page' => $filters['page'] > 1 ? $filters['page'] : NULL)))) ? '?' . $qs : '');
        if ($this->input->method(TRUE) === 'POST') {
            $h = $this->db->get_where('settings_history', array('id' => (int) $this->input->post('history_id')))->row_array();
            if (!$h) show_404();
            $old = json_decode($h['snapshot_json'], TRUE) ?: array();
            unset($old['ops_schema_version']);
            if (!$old) $this->back($back, '', 'Perubahan #' . (int) $h['id'] . ' tidak memiliki nilai sebelumnya untuk dikembalikan.');
            $this->Content_model->save_settings($old, 'Kembalikan ke nilai sebelum perubahan #' . (int) $h['id'] . ': ' . implode(', ', array_keys($old)));
            $this->audit('restore', 'settings_history:' . (int) $h['id'], implode(',', array_keys($old)));
            $this->back($back, count($old) . ' field dikembalikan ke nilai sebelum perubahan #' . (int) $h['id'] . '. Pemulihan ini juga tercatat di riwayat.');
        }
        $all = $this->db->select('h.*,a.username')->from('settings_history h')->join('admins a', 'a.id=h.changed_by', 'left')->order_by('h.id', 'DESC')->limit(1000)->get()->result_array();
        $today = date('Y-m-d'); $users = array(); $cats = array(); $today_n = 0; $months = array();
        foreach ($all as &$r) {
            $r['changes'] = json_decode($r['snapshot_json'], TRUE) ?: array();
            unset($r['changes']['ops_schema_version']);
            $r['category'] = $this->history_category($r['note'], array_keys($r['changes']));
            $r['actor'] = $r['username'] ?: ($r['changed_by_name'] ?? '') ?: 'Sistem';
            $users[$r['actor']] = TRUE; $cats[$r['category']] = ($cats[$r['category']] ?? 0) + 1;
            if (substr($r['created_at'], 0, 10) === $today) $today_n++;
            $months[substr($r['created_at'], 0, 7)] = TRUE;
        }
        unset($r);
        arsort($cats);
        // Filter di server: kata kunci (catatan, field, nilai, pelaku) + periode.
        $q = mb_strtolower($filters['q']); $p = $filters['period'];
        $rows = array_values(array_filter($all, function ($r) use ($q, $p, $today) {
            if ($p === 'today' && substr($r['created_at'], 0, 10) !== $today) return FALSE;
            if (ctype_digit($p) && strtotime($r['created_at']) < strtotime('-' . (int) $p . ' days')) return FALSE;
            if (preg_match('/^\d{4}-\d{2}$/', $p) && substr($r['created_at'], 0, 7) !== $p) return FALSE;
            if ($q === '') return TRUE;
            $hay = mb_strtolower($r['note'] . ' ' . $r['actor'] . ' ' . $r['category'] . ' ' . implode(' ', array_keys($r['changes'])) . ' ' . implode(' ', array_map('strval', $r['changes'])));
            return mb_strpos($hay, $q) !== FALSE;
        }));
        $total = count($rows); $pages = max(1, (int) ceil($total / $filters['per']));
        $filters['page'] = min($filters['page'], $pages);
        $this->render('pengaturan_riwayat', array('rows' => array_slice($rows, ($filters['page'] - 1) * $filters['per'], $filters['per']), 'filters' => $filters,
            'total' => $total, 'pages' => $pages, 'months' => array_keys($months), 'current' => $this->Content_model->settings(),
            'stats' => array('total' => count($all), 'today' => $today_n, 'users' => count($users), 'top' => key($cats) ?: '-')));
    }

    /** Kategori perubahan pengaturan berdasarkan field yang berubah (untuk chip & statistik "Fitur Terbanyak"). */
    private function history_category($note, array $keys)
    {
        if (stripos((string) $note, 'Kembalikan') === 0) return 'Pemulihan';
        if (!$keys) return 'Pengaturan';
        $match = function ($re) use ($keys) {foreach ($keys as $k) if (!preg_match($re, $k)) return FALSE; return TRUE;};
        if ($match('/(banner|_image|image_|logo|hero|_jpg|_png|_webp|favicon|page_image|background)/i')) return 'Tampilan';
        if ($match('/^(letter_|surat_|sign|stamp|kop_)/i')) return 'Surat';
        if ($match('/^subscription_/i')) return 'Langganan';
        if ($match('/^(notify_|smtp_|mail_|email_|wa_)/i')) return 'Notifikasi';
        if ($match('/^(dashboard_|quick_|menu_)/i')) return 'Dashboard';
        return 'Pengaturan';
    }

    /* ===================== 06 FIELD TAMBAHAN WARGA ===================== */

    public function field_tambahan($id = 0)
    {
        $this->guard_admin('pengaturan.fields');
        $this->load->library('Custom_fields');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('custom_field_definitions', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            if ($this->str('act', 20) === 'toggle') {
                $fid = (int) $this->input->post('field_id');
                $f = $this->db->get_where('custom_field_definitions', array('id' => $fid))->row_array();
                if (!$f) show_404();
                $this->db->where('id', $fid)->update('custom_field_definitions', array('is_active' => $f['is_active'] ? 0 : 1, 'updated_at' => date('Y-m-d H:i:s')));
                $this->audit($f['is_active'] ? 'deactivate' : 'activate', 'custom_field:' . $fid, $f['label']);
                $this->back('admin/field-tambahan', 'Status field diubah. Nilai yang sudah diisi warga tetap tersimpan.');
            }
            if ($this->str('act', 20) === 'delete') {
                // Hapus hanya bila belum ada nilai terisi; selain itu nonaktifkan agar data warga tidak hilang.
                $fid = (int) $this->input->post('field_id');
                $f = $this->db->get_where('custom_field_definitions', array('id' => $fid))->row_array();
                if (!$f) show_404();
                if ((int) $this->db->where('field_id', $fid)->where("value <> ''", NULL, FALSE)->count_all_results('citizen_field_values')) $this->back('admin/field-tambahan', '', 'Field "' . $f['label'] . '" sudah diisi warga dan tidak dapat dihapus. Nonaktifkan saja agar data tetap tersimpan.');
                $this->db->trans_start();
                $this->db->delete('citizen_field_values', array('field_id' => $fid));
                $this->db->delete('custom_field_definitions', array('id' => $fid));
                $this->db->trans_complete();
                $this->audit('delete', 'custom_field:' . $fid, $f['label']);
                $this->back('admin/field-tambahan', 'Field "' . $f['label'] . '" dihapus.');
            }
            $data = array('label' => $this->str('label', 80), 'field_type' => $this->choice('field_type', Custom_fields::$types, 'text'), 'help_text' => $this->str('help_text', 160),
                'is_required' => $this->input->post('is_required') ? 1 : 0, 'citizen_editable' => $this->input->post('citizen_editable') ? 1 : 0,
                'sort_order' => (int) $this->input->post('sort_order'), 'updated_at' => date('Y-m-d H:i:s'));
            $options = array_values(array_unique(array_filter(array_map(function ($o) {return mb_substr(trim($o), 0, 80);}, explode("\n", str_replace("\r", '', $this->text('options', 2000)))), 'strlen')));
            $data['options'] = $data['field_type'] === 'select' ? implode("\n", array_slice($options, 0, 30)) : '';
            if (mb_strlen($data['label']) < 2) $error = 'Label field wajib diisi.';
            elseif ($e = $this->custom_fields->label_error($data['label'])) $error = $e;
            elseif ($data['field_type'] === 'select' && count($options) < 2) $error = 'Field pilihan membutuhkan minimal 2 opsi (satu per baris).';
            elseif ($item && $item['field_type'] !== $data['field_type'] && (int) $this->db->where('field_id', $id)->where('value !=', '')->count_all_results('citizen_field_values') > 0) $error = 'Tipe field tidak dapat diubah karena sudah ada nilai yang diisi. Buat field baru dan nonaktifkan yang lama.';
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('custom_field_definitions', $data);
                else {$this->db->insert('custom_field_definitions', $data + array('created_by' => $this->admin_id)); $id = (int) $this->db->insert_id();}
                $this->audit($item ? 'update' : 'create', 'custom_field:' . $id, $data['label']);
                $this->back('admin/field-tambahan', 'Field tambahan disimpan.');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $items = $this->db->select("d.*, (SELECT COUNT(*) FROM citizen_field_values v WHERE v.field_id=d.id AND v.value<>'' AND v.value<>'0') filled", FALSE)
            ->from('custom_field_definitions d')->order_by('d.is_active', 'DESC')->order_by('d.sort_order')->order_by('d.id')->get()->result_array();
        $status = $this->choice('status', array('all' => 1, 'active' => 1, 'inactive' => 1), 'all', 'get');
        $counts = array('all' => count($items), 'active' => count(array_filter($items, function ($f) {return (int) $f['is_active'];})));
        $counts['inactive'] = $counts['all'] - $counts['active'];
        if ($status !== 'all') $items = array_values(array_filter($items, function ($f) use ($status) {return (int) $f['is_active'] === ($status === 'active' ? 1 : 0);}));
        $this->render('fields', array('items' => $items, 'item' => $item, 'error' => $error, 'types' => Custom_fields::$types, 'status' => $status, 'counts' => $counts,
            'form_open' => $id > 0 || $error !== '' || $this->input->get('baru')));
    }

    /* ===================== 11 ANTREAN NOTIFIKASI EMAIL ===================== */

    public function antrean_email()
    {
        $this->guard_admin('notifikasi.manage');
        $this->load->library('Notifier');
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 20);
            if ($act === 'process') {
                $s = $this->notifier->process(50);
                $this->audit('notify_process', 'notification_outbox', json_encode($s));
                $this->back('admin/antrean-email', ($s['waiting'] ? $s['waiting'] . ' email menunggu: SMTP belum dikonfigurasi.' : 'Terkirim ' . $s['sent'] . ', gagal ' . $s['failed'] . '.') . (!empty($s['blocked'])?' '.$s['blocked'].' email ditunda karena lisensi fitur belum aktif.':''));
            }
            if ($act === 'retry') {
                $this->db->where('id', (int) $this->input->post('outbox_id'))->where('status', 'failed')->update('notification_outbox', array('status' => 'pending', 'attempts' => 0, 'next_attempt_at' => NULL));
                $this->audit('notify_retry', 'notification_outbox:' . (int) $this->input->post('outbox_id'));
                $this->back('admin/antrean-email', 'Email dijadwalkan ulang.');
            }
            if ($act === 'bulk') {
                // Aksi massal dari checkbox: coba lagi (gagal → antre) atau hapus dari antrean (hanya antre/gagal; terkirim tetap tersimpan).
                $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->input->post('ids')))));
                $op = $this->str('bulk_action', 20);
                if (!$ids) $this->back('admin/antrean-email', '', 'Pilih minimal satu email pada daftar.');
                if ($op === 'retry') {
                    $this->db->where_in('id', $ids)->where('status', 'failed')->update('notification_outbox', array('status' => 'pending', 'attempts' => 0, 'next_attempt_at' => NULL, 'last_error' => NULL));
                    $n = $this->db->affected_rows();
                    $this->audit('notify_retry', 'notification_outbox', 'massal: ' . implode(',', $ids));
                    $this->back('admin/antrean-email', $n . ' email gagal dijadwalkan ulang.' . ($n < count($ids) ? ' Email yang tidak berstatus Gagal dilewati.' : ''));
                }
                if ($op === 'delete') {
                    $this->db->where_in('id', $ids)->where_in('status', array('pending', 'failed'))->delete('notification_outbox');
                    $n = $this->db->affected_rows();
                    $this->audit('notify_delete', 'notification_outbox', 'massal: ' . implode(',', $ids));
                    $this->back('admin/antrean-email', $n . ' email dihapus dari antrean.' . ($n < count($ids) ? ' Email yang sedang dikirim/terkirim tidak dihapus.' : ''));
                }
                $this->back('admin/antrean-email', '', 'Pilih aksi massal.');
            }
            if ($act === 'smtp_save') {
                if ($this->notifier->source() === 'server') {
                    // SMTP dikelola server: panel hanya mengubah aktif/nonaktif notifikasi email.
                    $this->Content_model->save_settings(array('notify_email' => $this->input->post('notify_email') ? '1' : '0'), 'Notifikasi email ' . ($this->input->post('notify_email') ? 'diaktifkan' : 'dinonaktifkan'));
                    $this->back('admin/antrean-email', 'Pengaturan notifikasi email disimpan. SMTP tetap mengikuti konfigurasi server.');
                }
                $c = array('host' => $this->str('smtp_host', 190), 'port' => (int) $this->input->post('smtp_port'), 'user' => $this->str('smtp_user', 190), 'pass' => (string) $this->input->post('smtp_pass'),
                    'crypto' => $this->choice('smtp_crypto', array('tls' => 1, 'ssl' => 1, 'none' => 1), 'tls'), 'from_email' => $this->str('from_email', 190), 'from_name' => $this->str('from_name', 120),
                    'clear_pass' => (bool) $this->input->post('clear_pass'));
                if ($c['crypto'] === 'none') $c['crypto'] = '';
                $err = '';
                if ($c['host'] !== '' && !preg_match('/^[a-z0-9.-]+$/i', $c['host'])) $err = 'Host SMTP tidak valid (contoh: smtp.gmail.com).';
                elseif ($c['port'] < 1 || $c['port'] > 65535) $err = 'Port SMTP 1–65535 (umumnya 587 untuk TLS, 465 untuk SSL).';
                elseif ($c['host'] !== '' && !filter_var($c['from_email'], FILTER_VALIDATE_EMAIL)) $err = 'Email pengirim wajib berupa alamat email yang valid.';
                elseif (strlen($c['pass']) > 255) $err = 'Password SMTP terlalu panjang.';
                if ($err) $this->back('admin/antrean-email#smtp', '', $err);
                $this->notifier->save_panel_config($c);
                $this->Content_model->save_settings(array('notify_email' => $this->input->post('notify_email') ? '1' : '0'), 'Notifikasi email ' . ($this->input->post('notify_email') ? 'diaktifkan' : 'dinonaktifkan'));
                $this->audit('smtp_config', 'notification_smtp', 'host=' . $c['host'] . ' port=' . $c['port'] . ' crypto=' . ($c['crypto'] ?: 'none') . ($c['pass'] !== '' ? ' (password diperbarui)' : ''));
                $this->back('admin/antrean-email', 'Konfigurasi SMTP disimpan.' . ($c['host'] === '' ? ' SMTP dikosongkan — email tetap disimpan di antrean.' : ' Gunakan "Kirim Email Uji" untuk memastikan pengiriman berhasil.'));
            }
            if ($act === 'smtp_test') {
                $to = $this->str('test_email', 190);
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) $this->back('admin/antrean-email#smtp', '', 'Masukkan alamat email tujuan uji yang valid.');
                list($ok, $err) = $this->notifier->send_one($to, 'Uji SMTP — ' . ($this->Content_model->settings()['site_name'] ?? 'RT/RW Digital'), "Email uji dari menu Antrean Email.\nBila Anda menerima pesan ini, konfigurasi SMTP sudah benar.\n\nDikirim: " . date('d-m-Y H:i'));
                $this->audit('smtp_test', 'notification_smtp', ($ok ? 'berhasil' : 'gagal') . ' ke ' . preg_replace('/^(.{2}).*@/', '$1•••@', $to));
                $ok ? $this->back('admin/antrean-email', 'Email uji berhasil dikirim ke ' . $to . '.') : $this->back('admin/antrean-email#smtp', '', 'Email uji gagal: ' . $err);
            }
            if ($act === 'broadcast') {
                $subject = $this->str('subject', 150); $body = $this->text('body', 3000);
                if (mb_strlen($subject) < 3 || mb_strlen($body) < 5) $this->back('admin/antrean-email#buat', '', 'Subjek (min. 3 karakter) dan isi pesan (min. 5 karakter) wajib diisi.');
                if (!$this->notifier->enabled()) $this->back('admin/antrean-email#buat', '', 'Notifikasi email belum diaktifkan. Aktifkan melalui tombol Atur SMTP terlebih dahulu.');
                if (preg_match('/\b\d{16}\b/', $subject . ' ' . $body)) $this->back('admin/antrean-email#buat', '', 'Isi pesan tidak boleh memuat NIK/nomor KK (16 digit).');
                $n = $this->notifier->enqueue_broadcast($subject, $body, date('YmdHis') . bin2hex(random_bytes(3)));
                $this->audit('notify_broadcast', 'notification_outbox', $n . ' penerima: ' . $subject);
                $this->back('admin/antrean-email', $n ? $n . ' email notifikasi masuk antrean. Klik "Proses Antrean Sekarang" untuk mengirim.' : 'Tidak ada warga yang mengaktifkan notifikasi email, sehingga tidak ada email yang diantrekan.');
            }
            show_404();
        }
        $counts = array_column($this->db->select('status, COUNT(*) n')->group_by('status')->get('notification_outbox')->result_array(), 'n', 'status');
        $rows = $this->db->order_by('id', 'DESC')->limit(100)->get('notification_outbox')->result_array();
        $smtp = array();
        foreach (array('smtp_host', 'smtp_port', 'smtp_user', 'smtp_crypto', 'from_email', 'from_name') as $k) $smtp[$k] = $this->notifier->cfg($k);
        $smtp['has_pass'] = $this->notifier->cfg('smtp_pass') !== '';
        $this->render('outbox', array('rows' => $rows, 'counts' => $counts, 'enabled' => $this->notifier->enabled(), 'smtp_ready' => $this->notifier->ready(),
            'smtp' => $smtp, 'smtp_source' => $this->notifier->source(), 'consenting' => count($this->notifier->consenting_citizens())));
    }

    /** Jalankan terjadwal: php index.php sistem cli_notify */
    public function cli_notify()
    {
        if (!is_cli()) show_404();
        $this->load->library('Notifier');
        $s = $this->notifier->process(100);
        echo 'Terkirim ' . $s['sent'] . ', gagal ' . $s['failed'] . ($s['waiting'] ? ', menunggu SMTP ' . $s['waiting'] : '') . (!empty($s['blocked'])?', menunggu lisensi '.$s['blocked']:'') . PHP_EOL;
        exit(0);
    }

    private function csv($filename, $header, $rows)
    {
        $safe = function ($v) {$v = (string) $v; return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;};
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $header, ';');
        foreach ($rows as $r) fputcsv($out, array_map($safe, $r), ';');
        fclose($out);
        exit;
    }
}
