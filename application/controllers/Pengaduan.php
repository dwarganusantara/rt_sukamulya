<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 03 — Pengaduan & Aspirasi.
 * Tiket bernomor, triase prioritas, penugasan PIC, tanggapan publik/internal,
 * konfirmasi selesai atau buka kembali oleh warga. Lampiran disimpan privat.
 * Transisi status divalidasi di server; SLA tidak pernah menutup tiket otomatis.
 */
class Pengaduan extends Ops_Controller
{
    public static $categories = array('kebersihan' => 'Kebersihan', 'keamanan' => 'Keamanan', 'fasilitas' => 'Fasilitas Umum', 'ketertiban' => 'Ketertiban',
        'usulan' => 'Usulan / Aspirasi', 'darurat' => 'Darurat', 'lainnya' => 'Lainnya');
    public static $labels = array('submitted' => 'Diterima', 'triaged' => 'Ditelaah', 'assigned' => 'Ditugaskan', 'in_progress' => 'Diproses',
        'waiting_resident' => 'Menunggu Warga', 'resolved' => 'Selesai Ditangani', 'closed' => 'Ditutup', 'reopened' => 'Dibuka Kembali', 'rejected' => 'Ditolak');
    public static $tones = array('submitted' => 'pending', 'triaged' => 'info', 'assigned' => 'info', 'in_progress' => 'info', 'waiting_resident' => 'purple',
        'resolved' => 'ok', 'closed' => 'muted', 'reopened' => 'pending', 'rejected' => 'bad');
    private $priorities = array('rendah' => 'Rendah', 'normal' => 'Normal', 'tinggi' => 'Tinggi', 'mendesak' => 'Mendesak');
    /** Transisi yang diizinkan untuk petugas. */
    private $flow = array(
        'submitted' => array('triaged', 'assigned', 'in_progress', 'rejected'),
        'triaged' => array('assigned', 'in_progress', 'rejected'),
        'assigned' => array('in_progress', 'waiting_resident', 'resolved'),
        'in_progress' => array('waiting_resident', 'resolved'),
        'waiting_resident' => array('in_progress', 'resolved'),
        'reopened' => array('assigned', 'in_progress', 'resolved'),
        'resolved' => array('closed', 'in_progress'),
    );
    private $open = array('submitted', 'triaged', 'assigned', 'in_progress', 'waiting_resident', 'reopened');

    /* ================= WARGA ================= */

    public function index()
    {
        $this->guard_citizen();
        $items = $this->db->order_by('id', 'DESC')->get_where('complaints', array('citizen_id' => $this->citizen_id))->result_array();
        $this->render_citizen('pengaduan_list', 'Pengaduan Saya', array('items' => $items, 'categories' => self::$categories, 'labels' => self::$labels, 'tones' => self::$tones, 'open' => $this->open));
    }

    public function buat()
    {
        $this->guard_citizen();
        $error = '';
        $values = array('category' => $this->choice('kategori', self::$categories, '', 'get'));
        $anonymous_allowed = $this->anonymous_allowed();
        if ($this->input->method(TRUE) === 'POST') {
            $values = array('category' => $this->choice('category', self::$categories), 'title' => $this->str('title', 160),
                'location' => $this->str('location', 190), 'description' => $this->text('description', 5000));
            // Identitas disembunyikan hanya bila fitur diaktifkan pengurus; tiket tetap terhubung ke akun (batas harian & penyalahgunaan).
            if ($anonymous_allowed) $values['is_anonymous'] = $this->input->post('is_anonymous') ? 1 : 0;
            try {
                if (!$values['category'] || mb_strlen($values['title']) < 5 || mb_strlen($values['description']) < 10) throw new RuntimeException('Pilih kategori, isi judul (min. 5 karakter) dan uraian (min. 10 karakter).');
                $today = $this->db->where('citizen_id', $this->citizen_id)->where('created_at >=', date('Y-m-d 00:00:00'))->count_all_results('complaints');
                if ($today >= 5) throw new RuntimeException('Batas 5 pengaduan per hari tercapai. Silakan lanjutkan besok atau hubungi pengurus melalui chat.');
                $this->load->library('Private_storage');
                $upload_error = '';
                $file = $this->private_storage->store('attachment', 'complaints', $upload_error, array('jpg', 'png', 'webp', 'pdf'));
                if ($upload_error) throw new RuntimeException($upload_error);
                $id = $this->atomic(function () use ($values, $file) {
                    $this->db->insert('complaints', $values + array('citizen_id' => $this->citizen_id, 'attachment' => $file['stored_name'] ?? NULL,
                        'priority' => $values['category'] === 'darurat' ? 'mendesak' : 'normal', 'status' => 'submitted', 'updated_at' => date('Y-m-d H:i:s')));
                    $id = (int) $this->db->insert_id();
                    $this->db->where('id', $id)->update('complaints', array('ticket_no' => $this->ticket($id)));
                    $this->db->insert('complaint_updates', array('complaint_id' => $id, 'actor_type' => 'citizen', 'actor_id' => $this->citizen_id, 'status_to' => 'submitted', 'message' => 'Pengaduan dikirim.'));
                    return $id;
                });
                $this->audit_citizen('create', 'complaint:' . $id, self::$categories[$values['category']]);
                $this->back('warga/pengaduan/' . $id, 'Pengaduan terkirim dengan nomor tiket ' . $this->ticket($id) . '. Pantau statusnya di halaman ini.');
            } catch (RuntimeException $e) {
                if (!empty($file['stored_name'])) $this->private_storage->delete('complaints', $file['stored_name']);
                $error = $e->getMessage();
            }
        }
        $this->render_citizen('pengaduan_form', 'Buat Pengaduan', array('values' => $values, 'error' => $error, 'categories' => self::$categories, 'emergency' => $this->emergency_contacts(), 'anonymous_allowed' => $anonymous_allowed));
    }

    public function detail($id = 0)
    {
        $this->guard_citizen();
        $item = $this->db->get_where('complaints', array('id' => (int) $id, 'citizen_id' => $this->citizen_id))->row_array();
        if (!$item) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $action = $this->choice('action', array('comment' => 1, 'confirm' => 1, 'reopen' => 1));
            $message = $this->text('message', 2000);
            try {
                $this->atomic(function () use ($id, $action, $message) {
                    $c = $this->lock('complaints', $id);
                    if (!$c || (int) $c['citizen_id'] !== $this->citizen_id) throw new RuntimeException('Pengaduan tidak ditemukan.');
                    $to = NULL;
                    if ($action === 'comment') {
                        if (!in_array($c['status'], $this->open, TRUE)) throw new RuntimeException('Tiket sudah selesai. Gunakan "Buka kembali" bila masalah belum tuntas.');
                        if (mb_strlen($message) < 2) throw new RuntimeException('Tulis tanggapan Anda.');
                        if ($c['status'] === 'waiting_resident') $to = 'in_progress';
                    } elseif ($action === 'confirm') {
                        if ($c['status'] !== 'resolved') throw new RuntimeException('Konfirmasi hanya untuk tiket yang sudah selesai ditangani.');
                        $to = 'closed';
                        $rating = (int) $this->input->post('rating');
                        $this->db->where('id', (int) $id)->update('complaints', array('rating' => $rating >= 1 && $rating <= 5 ? $rating : NULL, 'closed_at' => date('Y-m-d H:i:s')));
                    } elseif ($action === 'reopen') {
                        if ($c['status'] !== 'resolved' || strtotime((string) $c['resolved_at']) < strtotime('-14 days')) throw new RuntimeException('Tiket hanya dapat dibuka kembali maksimal 14 hari setelah dinyatakan selesai.');
                        if (mb_strlen($message) < 5) throw new RuntimeException('Jelaskan alasan membuka kembali tiket.');
                        $to = 'reopened';
                    } else {
                        throw new RuntimeException('Aksi tidak valid.');
                    }
                    if ($to) $this->db->where('id', (int) $id)->update('complaints', array('status' => $to, 'updated_at' => date('Y-m-d H:i:s')));
                    $this->db->insert('complaint_updates', array('complaint_id' => (int) $id, 'actor_type' => 'citizen', 'actor_id' => $this->citizen_id,
                        'status_from' => $to ? $c['status'] : NULL, 'status_to' => $to, 'message' => $message ?: ($action === 'confirm' ? 'Warga mengonfirmasi penanganan selesai.' : NULL)));
                });
                $this->audit_citizen($action, 'complaint:' . (int) $id);
                $this->back('warga/pengaduan/' . (int) $id, 'Tanggapan tersimpan.');
            } catch (RuntimeException $e) {
                $this->back('warga/pengaduan/' . (int) $id, '', $e->getMessage());
            }
        }
        $updates = $this->db->where('complaint_id', (int) $id)->where('is_internal', 0)->order_by('id')->get('complaint_updates')->result_array();
        $this->render_citizen('pengaduan_detail', 'Tiket ' . $item['ticket_no'], array('item' => $item, 'updates' => $updates, 'categories' => self::$categories,
            'labels' => self::$labels, 'tones' => self::$tones, 'open' => $this->open));
    }

    public function lampiran_warga($id = 0)
    {
        $this->guard_citizen();
        $item = $this->db->get_where('complaints', array('id' => (int) $id, 'citizen_id' => $this->citizen_id))->row_array();
        if (!$item || !$item['attachment']) show_404();
        $this->load->library('Private_storage');
        $this->private_storage->send('complaints', $item['attachment'], $item['ticket_no'] . '-lampiran');
    }

    /* ================= ADMIN ================= */

    public function admin_index()
    {
        $this->guard_admin('pengaduan.read');
        $status = $this->choice('status', self::$labels + array('open' => 'Terbuka', 'all' => 'Semua'), 'open', 'get');
        $category = $this->choice('kategori', self::$categories, '', 'get');
        $q = $this->str('q', 80, 'get');
        $this->scope();
        $counts = array();
        foreach ($this->db->select('status,COUNT(*) n')->group_by('status')->get('complaints')->result_array() as $r) $counts[$r['status']] = (int) $r['n'];
        $apply = function () use ($status, $category, $q) {
            $this->db->from('complaints p')->join('citizens c', 'c.id=p.citizen_id', 'left')->join('admins a', 'a.id=p.assigned_to', 'left');
            $this->scope('p.');
            if ($status === 'open') $this->db->where_in('p.status', $this->open);
            elseif ($status !== 'all') $this->db->where('p.status', $status);
            if ($category) $this->db->where('p.category', $category);
            if ($q !== '') {
                // Nama pelapor anonim tidak ikut dicari, agar pencarian nama tidak membuka identitasnya.
                $name_match = $this->has_anonymous_column() ? '(p.is_anonymous=0 AND c.display_name LIKE ' . $this->db->escape('%' . $this->db->escape_like_str($q) . '%') . " ESCAPE '!')" : 'c.display_name LIKE ' . $this->db->escape('%' . $this->db->escape_like_str($q) . '%') . " ESCAPE '!'";
                $this->db->group_start()->like('p.ticket_no', $q)->or_like('p.title', $q)->or_like('p.location', $q)->or_where($name_match, NULL, FALSE)->group_end();
            }
        };
        $apply(); $matched = (int) $this->db->count_all_results();
        $per_page = 15; $pages = max(1, (int) ceil($matched / $per_page)); $page = min($pages, max(1, (int) $this->input->get('hal')));
        $apply();
        $items = $this->db->select('p.*,c.display_name,a.username pic')->order_by("FIELD(p.priority,'mendesak','tinggi','normal','rendah')", '', FALSE)->order_by('p.id', 'DESC')->limit($per_page, ($page - 1) * $per_page)->get()->result_array();
        foreach ($items as &$it) if (!empty($it['is_anonymous'])) $it['display_name'] = 'Warga (identitas disembunyikan)';
        unset($it);
        $this->render('pengaduan_index', array('matched' => $matched, 'page' => $page, 'pages' => $pages, 'items' => $items, 'counts' => $counts, 'status' => $status, 'category' => $category, 'q' => $q,
            'categories' => self::$categories, 'labels' => self::$labels, 'tones' => self::$tones, 'priorities' => $this->priorities, 'open' => $this->open));
    }

    public function admin_detail($id = 0)
    {
        $this->guard_admin('pengaduan.read');
        $id = (int) $id;
        $item = $this->find_scoped($id);
        $reveal = FALSE;
        if ($this->input->method(TRUE) === 'POST' && $this->input->post('act') === 'reveal') {
            // Pembukaan identitas pelapor anonim: hanya Ketua/Admin, wajib alasan, tercatat di audit, tidak disimpan di sesi.
            $this->need('pengaduan.reveal');
            $reason = $this->str('reason', 255);
            if (mb_strlen($reason) < 10) $this->back('admin/pengaduan/' . $id, '', 'Tuliskan alasan membuka identitas pelapor (minimal 10 karakter).');
            $this->audit('reveal_identity', 'complaint:' . $id, $reason);
            $reveal = TRUE;
        } elseif ($this->input->method(TRUE) === 'POST') {
            $this->need('pengaduan.handle');
            $status = $this->choice('status', self::$labels, '');
            $priority = $this->choice('priority', $this->priorities, '');
            $assign = (int) $this->input->post('assigned_to');
            $message = $this->text('message', 3000);
            $internal = $this->input->post('is_internal') ? 1 : 0;
            try {
                $changed = $this->atomic(function () use ($id, $status, $priority, $assign, $message, $internal) {
                    $c = $this->lock('complaints', $id);
                    if (!$c) throw new RuntimeException('Tiket tidak ditemukan.');
                    $update = array();
                    $changes = array();
                    if ($priority && $priority !== $c['priority']) {$update['priority'] = $priority; $changes[] = 'Prioritas: ' . $this->priorities[$priority];}
                    if ($assign !== (int) $c['assigned_to'] && $this->input->post('assigned_to') !== NULL) {
                        $this->need('pengaduan.assign');
                        if ($assign && !isset($this->Ops_model->staff_options()[$assign])) throw new RuntimeException('PIC tidak valid atau akunnya nonaktif.');
                        $update['assigned_to'] = $assign ?: NULL;
                        $changes[] = 'PIC: ' . ($assign ? $this->Ops_model->staff_options()[$assign] : 'dikosongkan');
                        if ($assign && in_array($c['status'], array('submitted', 'triaged', 'reopened'), TRUE) && !$status) $status = 'assigned';
                    }
                    if ($status && $status !== $c['status']) {
                        if (!in_array($status, $this->flow[$c['status']] ?? array(), TRUE)) throw new RuntimeException('Perubahan status dari "' . self::$labels[$c['status']] . '" ke "' . self::$labels[$status] . '" tidak diizinkan.');
                        if (in_array($status, array('resolved', 'rejected'), TRUE) && mb_strlen($message) < 5) throw new RuntimeException('Tuliskan catatan penyelesaian / alasan penolakan untuk warga.');
                        if (in_array($status, array('resolved', 'rejected', 'waiting_resident'), TRUE) && $internal) throw new RuntimeException('Catatan untuk status ini harus terlihat oleh warga (jangan centang catatan internal).');
                        $update['status'] = $status;
                        if ($status === 'resolved') {$update['resolved_at'] = date('Y-m-d H:i:s'); $update['resolution_note'] = $message;}
                        if ($status === 'closed') $update['closed_at'] = date('Y-m-d H:i:s');
                    } else {
                        $status = NULL;
                    }
                    if (!$update && $message === '') throw new RuntimeException('Tidak ada perubahan untuk disimpan.');
                    if ($update) $this->db->where('id', $id)->update('complaints', $update + array('updated_at' => date('Y-m-d H:i:s')));
                    $text = trim(implode("\n", $changes) . ($message !== '' ? "\n" . $message : ''));
                    $this->db->insert('complaint_updates', array('complaint_id' => $id, 'actor_type' => 'admin', 'actor_id' => $this->admin_id,
                        'status_from' => $status ? $c['status'] : NULL, 'status_to' => $status, 'message' => $text ?: NULL, 'is_internal' => $internal));
                    return array('status' => $status, 'citizen_id' => (int) $c['citizen_id'], 'ticket' => $c['ticket_no']);
                });
                // Status aktual dari dalam transaksi (bisa NULL bila tidak berubah, atau "assigned" otomatis saat PIC dipilih).
                $this->audit('update', 'complaint:' . $id, $changed['status'] ? 'status=' . $changed['status'] : 'tanggapan');
                if ($changed['status'] && $changed['citizen_id'] && !$internal) {
                    $this->load->library('Notifier');
                    $this->notifier->enqueue_citizen($changed['citizen_id'], 'Pengaduan ' . $changed['ticket'] . ': ' . self::$labels[$changed['status']],
                        'Status pengaduan ' . $changed['ticket'] . ' sekarang: ' . self::$labels[$changed['status']] . '.' . ($message !== '' ? "\nTanggapan pengurus: " . $message : ''),
                        'complaint:' . $id . ':' . $changed['status'] . ':' . date('YmdHis'));
                }
                $this->back('admin/pengaduan/' . $id, 'Tiket diperbarui.');
            } catch (RuntimeException $e) {
                $this->back('admin/pengaduan/' . $id, '', $e->getMessage());
            }
        }
        $item = $this->db->select('p.*,c.display_name,c.phone,a.username pic')->from('complaints p')->join('citizens c', 'c.id=p.citizen_id', 'left')->join('admins a', 'a.id=p.assigned_to', 'left')->where('p.id', $id)->get()->row_array();
        $updates = $this->db->select('u.*,a.username admin_name,c.display_name citizen_name')->from('complaint_updates u')
            ->join('admins a', "a.id=u.actor_id AND u.actor_type='admin'", 'left')->join('citizens c', "c.id=u.actor_id AND u.actor_type='citizen'", 'left')
            ->where('u.complaint_id', $id)->order_by('u.id')->get()->result_array();
        $hidden = !empty($item['is_anonymous']) && !$reveal;
        if ($hidden) {
            $item['display_name'] = 'Warga (identitas disembunyikan)';
            $item['phone'] = '';
            foreach ($updates as &$u) if ($u['actor_type'] === 'citizen') $u['citizen_name'] = 'Pelapor';
            unset($u);
        }
        $this->render('pengaduan_detail', array('item' => $item, 'updates' => $updates, 'categories' => self::$categories, 'labels' => self::$labels, 'tones' => self::$tones,
            'priorities' => $this->priorities, 'next' => $this->flow[$item['status']] ?? array(), 'staff' => $this->Ops_model->staff_options(),
            'anonymous' => !empty($item['is_anonymous']), 'revealed' => $reveal, 'can_reveal' => $this->can('pengaduan.reveal')));
    }

    public function lampiran_admin($id = 0)
    {
        $this->guard_admin('pengaduan.read');
        $item = $this->find_scoped((int) $id);
        if (!$item['attachment']) show_404();
        $this->audit('download', 'complaint_attachment:' . (int) $id);
        $this->load->library('Private_storage');
        $this->private_storage->send('complaints', $item['attachment'], $item['ticket_no'] . '-lampiran');
    }

    /* ================= ATURAN ================= */

    private function has_anonymous_column()
    {
        static $has = NULL;
        if ($has === NULL) $has = $this->db->field_exists('is_anonymous', 'complaints');
        return $has;
    }

    private function anonymous_allowed()
    {
        return $this->has_anonymous_column() && (($this->Content_model->settings()['complaint_anonymous'] ?? '0') === '1');
    }

    private function ticket($id)
    {
        return 'ADU-' . date('ym') . '-' . str_pad((int) $id, 5, '0', STR_PAD_LEFT);
    }

    /** Petugas keamanan hanya melihat tiket keamanan/darurat atau yang ditugaskan kepadanya. */
    private function scope($prefix = '')
    {
        if ($this->role === 'keamanan') {
            $this->db->group_start()->where($prefix . 'assigned_to', $this->admin_id)->or_where_in($prefix . 'category', array('keamanan', 'darurat'))->group_end();
        }
    }

    private function find_scoped($id)
    {
        $this->db->where('id', (int) $id);
        $this->scope();
        $item = $this->db->get('complaints')->row_array();
        if (!$item) show_404();
        return $item;
    }

    private function emergency_contacts()
    {
        return $this->db->where('is_active', 1)->where('is_public', 1)->where('is_emergency', 1)->order_by('sort_order')->limit(6)->get('important_contacts')->result_array();
    }
}
