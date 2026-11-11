<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 07 Program Kerja, 08 Catatan Kegiatan & Notulen, 09 Jadwal Ronda & Absensi.
 */
class Kegiatan extends Ops_Controller
{
    public static $program_labels = array('draft' => 'Draf', 'diajukan' => 'Menunggu Persetujuan', 'aktif' => 'Aktif', 'ditunda' => 'Ditunda', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan');
    public static $program_tones = array('draft' => 'muted', 'diajukan' => 'pending', 'aktif' => 'info', 'ditunda' => 'purple', 'selesai' => 'ok', 'dibatalkan' => 'bad');
    private $program_categories = array('lingkungan' => 'Lingkungan & Kebersihan', 'keamanan' => 'Keamanan', 'sosial' => 'Sosial & Kemasyarakatan', 'infrastruktur' => 'Infrastruktur', 'kesehatan' => 'Kesehatan', 'administrasi' => 'Administrasi', 'lainnya' => 'Lainnya');
    private $program_flow = array('draft' => array('diajukan', 'dibatalkan'), 'diajukan' => array('aktif', 'draft'), 'aktif' => array('ditunda', 'selesai', 'dibatalkan'), 'ditunda' => array('aktif', 'dibatalkan'));
    public static $note_labels = array('draft' => 'Draf', 'final' => 'Menunggu Pengesahan', 'disahkan' => 'Disahkan');
    public static $note_tones = array('draft' => 'muted', 'final' => 'pending', 'disahkan' => 'ok');
    public static $action_labels = array('open' => 'Belum Dikerjakan', 'in_progress' => 'Berjalan', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan');
    public static $action_tones = array('open' => 'pending', 'in_progress' => 'info', 'done' => 'ok', 'cancelled' => 'muted');
    public static $shifts = array('shift_1' => 'Shift 1 (22.00–01.00)', 'shift_2' => 'Shift 2 (01.00–04.00)', 'penuh' => 'Semalam (22.00–04.00)');
    private $shift_start = array('shift_1' => '22:00', 'shift_2' => '01:00', 'penuh' => '22:00');
    public static $attendance = array('scheduled' => 'Terjadwal', 'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'tidak_hadir' => 'Tidak Hadir', 'izin' => 'Izin', 'diganti' => 'Digantikan');
    public static $attendance_tones = array('scheduled' => 'muted', 'hadir' => 'ok', 'terlambat' => 'pending', 'tidak_hadir' => 'bad', 'izin' => 'purple', 'diganti' => 'info');
    private $incident_categories = array('pencurian' => 'Pencurian', 'kebakaran' => 'Kebakaran', 'mencurigakan' => 'Orang/kendaraan mencurigakan', 'gangguan' => 'Gangguan ketertiban', 'kecelakaan' => 'Kecelakaan', 'lainnya' => 'Lainnya');

    /* ===================== 07 PROGRAM KERJA ===================== */

    public function program_index()
    {
        $this->guard_admin('program.read');
        $status = $this->choice('status', self::$program_labels, '', 'get');
        $q = $this->str('q', 80, 'get');
        $counts = array();
        foreach ($this->db->select('status,COUNT(*) n')->where('deleted_at IS NULL', NULL, FALSE)->group_by('status')->get('work_programs')->result_array() as $r) $counts[$r['status']] = (int) $r['n'];
        $this->db->where('deleted_at IS NULL', NULL, FALSE);
        if ($status) $this->db->where('status', $status);
        if ($q !== '') $this->db->group_start()->like('title', $q)->or_like('pic', $q)->or_like('description', $q)->group_end();
        $items = $this->db->order_by("FIELD(status,'diajukan','aktif','ditunda','draft','selesai','dibatalkan')", '', FALSE)->order_by('period_start', 'DESC')->get('work_programs')->result_array();
        $this->render('program_index', array('items' => $items, 'counts' => $counts, 'status' => $status, 'q' => $q, 'labels' => self::$program_labels, 'tones' => self::$program_tones, 'categories' => $this->program_categories));
    }

    public function program_form($id = 0)
    {
        $this->guard_admin('program.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('work_programs', array('id' => $id, 'deleted_at' => NULL))->row_array() : NULL;
        if ($id && !$item) show_404();
        if ($item && in_array($item['status'], array('selesai', 'dibatalkan'), TRUE)) $this->back('admin/program/' . $id, '', 'Program yang sudah selesai/dibatalkan tidak dapat diubah.');
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('title' => $this->str('title', 160), 'category' => $this->choice('category', $this->program_categories, 'lainnya'), 'description' => $this->text('description', 5000),
                'period_start' => $this->date_or_null('period_start'), 'period_end' => $this->date_or_null('period_end'), 'target' => $this->str('target', 255),
                'indicator' => $this->str('indicator', 255), 'pic' => $this->str('pic', 120), 'budget' => $this->money('budget'), 'realization' => $this->money('realization'),
                'obstacles' => $this->text('obstacles', 3000), 'updated_at' => date('Y-m-d H:i:s'));
            if (mb_strlen($data['title']) < 3 || !$data['period_start'] || !$data['period_end']) $error = 'Nama program dan periode (mulai & selesai) wajib diisi.';
            elseif ($data['period_end'] < $data['period_start']) $error = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('work_programs', $data);
                else {$this->db->insert('work_programs', $data + array('status' => 'draft', 'created_by' => $this->admin_id)); $id = (int) $this->db->insert_id();}
                $this->audit($item ? 'update' : 'create', 'program:' . $id);
                $this->back('admin/program/' . $id, 'Program kerja disimpan.');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $this->render('program_form', array('item' => $item, 'error' => $error, 'categories' => $this->program_categories));
    }

    public function program_detail($id = 0)
    {
        $this->guard_admin('program.read');
        $id = (int) $id;
        $item = $this->db->get_where('work_programs', array('id' => $id, 'deleted_at' => NULL))->row_array();
        if (!$item) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 30);
            try {
                $this->atomic(function () use ($id, $act) {
                    $p = $this->lock('work_programs', $id);
                    $locked = in_array($p['status'], array('selesai', 'dibatalkan'), TRUE);
                    if ($act === 'status') {
                        $to = $this->choice('to', self::$program_labels);
                        if (!in_array($to, $this->program_flow[$p['status']] ?? array(), TRUE)) throw new RuntimeException('Perubahan status tidak diizinkan.');
                        $this->need(in_array($to, array('aktif', 'selesai', 'dibatalkan'), TRUE) || ($p['status'] === 'diajukan' && $to === 'draft') ? 'program.approve' : 'program.write');
                        $update = array('status' => $to, 'updated_at' => date('Y-m-d H:i:s'));
                        if ($to === 'aktif' && !$p['approved_at']) {$update['approved_by'] = $this->admin_id; $update['approved_at'] = date('Y-m-d H:i:s');}
                        if ($to === 'selesai') {
                            $update['evaluation'] = $this->text('evaluation', 3000);
                            if (mb_strlen($update['evaluation']) < 5) throw new RuntimeException('Isi evaluasi singkat sebelum menandai program selesai.');
                        }
                        $this->db->where('id', $id)->update('work_programs', $update);
                        $this->audit('status', 'program:' . $id, $p['status'] . '->' . $to);
                        return;
                    }
                    $this->need('program.write');
                    if ($locked) throw new RuntimeException('Program sudah ditutup.');
                    if ($act === 'milestone_add') {
                        $title = $this->str('m_title', 160);
                        if (mb_strlen($title) < 3) throw new RuntimeException('Nama milestone wajib diisi.');
                        $this->db->insert('program_milestones', array('program_id' => $id, 'title' => $title, 'due_date' => $this->date_or_null('m_due'), 'weight' => max(1, min(100, (int) $this->input->post('m_weight')))));
                    } elseif ($act === 'milestone_toggle') {
                        $mid = (int) $this->input->post('milestone_id');
                        $m = $this->db->get_where('program_milestones', array('id' => $mid, 'program_id' => $id))->row_array();
                        if (!$m) throw new RuntimeException('Milestone tidak ditemukan.');
                        $this->db->where('id', $mid)->update('program_milestones', array('is_done' => $m['is_done'] ? 0 : 1, 'done_at' => $m['is_done'] ? NULL : date('Y-m-d H:i:s')));
                    } elseif ($act === 'link_agenda') {
                        $post = (int) $this->input->post('post_id');
                        if (!$this->db->get_where('posts', array('id' => $post, 'type' => 'agenda'))->row_array()) throw new RuntimeException('Agenda tidak ditemukan.');
                        $this->db->query('INSERT IGNORE INTO program_agendas (program_id,post_id) VALUES (?,?)', array($id, $post));
                    } elseif ($act === 'unlink_agenda') {
                        $this->db->where('program_id', $id)->where('post_id', (int) $this->input->post('post_id'))->delete('program_agendas');
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->recalc_progress($id);
                    $this->audit($act, 'program:' . $id);
                });
                $this->back('admin/program/' . $id, 'Program kerja diperbarui.');
            } catch (RuntimeException $e) {
                $this->back('admin/program/' . $id, '', $e->getMessage());
            }
        }
        $milestones = $this->db->order_by('due_date IS NULL', '', FALSE)->order_by('due_date')->order_by('id')->get_where('program_milestones', array('program_id' => $id))->result_array();
        $agendas = $this->db->select('p.id,p.title,p.event_date,p.status')->from('program_agendas pa')->join('posts p', 'p.id=pa.post_id')->where('pa.program_id', $id)->order_by('p.event_date')->get()->result_array();
        $agenda_options = array();
        foreach ($this->db->select('id,title,event_date')->where('type', 'agenda')->order_by('event_date', 'DESC')->limit(60)->get('posts')->result_array() as $a) $agenda_options[(int) $a['id']] = ops_date($a['event_date']) . ' — ' . $a['title'];
        $notes = $this->db->select('id,title,note_date,status')->where('program_id', $id)->order_by('note_date', 'DESC')->get('activity_notes')->result_array();
        $purchases = $this->db->select('id,title,status,estimated_total,actual_total')->where('program_id', $id)->order_by('id', 'DESC')->get('purchase_requests')->result_array();
        $spent = 0;
        foreach ($purchases as $p) if (in_array($p['status'], array('received', 'closed', 'partially_received'), TRUE)) $spent += (float) ($p['actual_total'] ?? $p['estimated_total']);
        $creator = $item['approved_by'] ? $this->db->select('username')->get_where('admins', array('id' => (int) $item['approved_by']))->row_array() : NULL;
        $this->render('program_detail', array('item' => $item, 'milestones' => $milestones, 'agendas' => $agendas, 'agenda_options' => $agenda_options, 'notes' => $notes,
            'purchases' => $purchases, 'spent' => $spent, 'approver' => $creator['username'] ?? '', 'labels' => self::$program_labels, 'tones' => self::$program_tones,
            'categories' => $this->program_categories, 'next' => $this->program_flow[$item['status']] ?? array()));
    }

    private function recalc_progress($id)
    {
        $row = $this->db->query('SELECT COALESCE(SUM(weight),0) total, COALESCE(SUM(CASE WHEN is_done=1 THEN weight ELSE 0 END),0) done FROM program_milestones WHERE program_id=?', array((int) $id))->row_array();
        $progress = (int) $row['total'] > 0 ? (int) round(100 * $row['done'] / $row['total']) : 0;
        $this->db->where('id', (int) $id)->update('work_programs', array('progress' => $progress, 'updated_at' => date('Y-m-d H:i:s')));
    }

    /* ===================== 08 NOTULEN ===================== */

    public function notulen_index()
    {
        $this->guard_admin('notulen.read');
        $status = $this->choice('status', self::$note_labels, '', 'get');
        $q = $this->str('q', 80, 'get');
        $this->db->select('n.*,p.title agenda_title,w.title program_title,(SELECT COUNT(*) FROM action_items ai WHERE ai.note_id=n.id AND ai.status IN (\'open\',\'in_progress\')) open_actions', FALSE)
            ->from('activity_notes n')->join('posts p', 'p.id=n.agenda_id', 'left')->join('work_programs w', 'w.id=n.program_id', 'left');
        if ($status) $this->db->where('n.status', $status);
        if ($q !== '') $this->db->group_start()->like('n.title', $q)->or_like('n.summary', $q)->or_like('n.decisions', $q)->group_end();
        $items = $this->db->order_by('n.note_date', 'DESC')->limit(200)->get()->result_array();
        $overdue = $this->db->select('ai.*,n.title note_title')->from('action_items ai')->join('activity_notes n', 'n.id=ai.note_id')
            ->where_in('ai.status', array('open', 'in_progress'))->where('ai.due_date <', date('Y-m-d'))->order_by('ai.due_date')->limit(50)->get()->result_array();
        $this->render('notulen_index', array('items' => $items, 'overdue' => $overdue, 'status' => $status, 'q' => $q, 'labels' => self::$note_labels, 'tones' => self::$note_tones));
    }

    public function notulen_form($id = 0)
    {
        $this->guard_admin('notulen.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('activity_notes', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        if ($item && $item['status'] === 'disahkan') $this->back('admin/notulen/' . $id, '', 'Notulen yang sudah disahkan dikunci. Gunakan "Buka Revisi" untuk mengubahnya.');
        $error = '';
        $agenda_id = (int) $this->input->get('agenda');
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('title' => $this->str('title', 160), 'note_date' => $this->date_or_null('note_date'), 'agenda_id' => (int) $this->input->post('agenda_id') ?: NULL,
                'program_id' => (int) $this->input->post('program_id') ?: NULL, 'location' => $this->str('location', 160), 'participants' => $this->str('participants', 1000),
                'participant_count' => max(0, (int) $this->input->post('participant_count')), 'summary' => $this->text('summary', 8000), 'decisions' => $this->text('decisions', 8000),
                'obstacles' => $this->text('obstacles', 3000), 'updated_at' => date('Y-m-d H:i:s'));
            $data['status'] = $this->input->post('finalize') ? 'final' : 'draft';
            if (mb_strlen($data['title']) < 3 || !$data['note_date']) $error = 'Judul kegiatan dan tanggal wajib diisi.';
            elseif ($data['agenda_id'] && !$this->db->get_where('posts', array('id' => $data['agenda_id'], 'type' => 'agenda'))->row_array()) $error = 'Agenda tidak valid.';
            elseif ($data['program_id'] && !$this->db->get_where('work_programs', array('id' => $data['program_id']))->row_array()) $error = 'Program tidak valid.';
            if (!$error) {
                $this->atomic(function () use (&$id, $item, $data) {
                    if ($id) {
                        $current = $this->lock('activity_notes', $id);
                        if ($current['status'] === 'disahkan') throw new RuntimeException('Notulen sudah disahkan.');
                        // Versi sebelumnya disimpan utuh; revisi tidak menimpa tanpa histori.
                        $this->db->insert('activity_note_versions', array('note_id' => $id, 'version' => (int) $current['version'], 'snapshot_json' => json_encode($current, JSON_UNESCAPED_UNICODE), 'changed_by' => $this->admin_id));
                        $this->db->where('id', $id)->update('activity_notes', $data + array('version' => (int) $current['version'] + 1));
                    } else {
                        $this->db->insert('activity_notes', $data + array('created_by' => $this->admin_id));
                        $id = (int) $this->db->insert_id();
                    }
                });
                $this->audit($item ? 'update' : 'create', 'notulen:' . $id, $data['status']);
                $this->back('admin/notulen/' . $id, 'Notulen disimpan' . ($data['status'] === 'final' ? ' dan diajukan untuk pengesahan.' : ' sebagai draf.'));
            }
            $item = array_merge($item ?: array(), $data);
        } elseif (!$item && $agenda_id) {
            $agenda = $this->db->get_where('posts', array('id' => $agenda_id, 'type' => 'agenda'))->row_array();
            if ($agenda) $item = array('title' => 'Notulen: ' . $agenda['title'], 'note_date' => $agenda['event_date'], 'agenda_id' => $agenda_id, 'location' => $agenda['event_location']);
        }
        $agendas = array();
        foreach ($this->db->select('id,title,event_date')->where('type', 'agenda')->order_by('event_date', 'DESC')->limit(80)->get('posts')->result_array() as $a) $agendas[(int) $a['id']] = ops_date($a['event_date']) . ' — ' . $a['title'];
        $programs = array();
        foreach ($this->db->select('id,title')->where('deleted_at IS NULL', NULL, FALSE)->where_in('status', array('aktif', 'diajukan', 'draft', 'ditunda'))->order_by('title')->get('work_programs')->result_array() as $p) $programs[(int) $p['id']] = $p['title'];
        $this->render('notulen_form', array('item' => $item, 'error' => $error, 'agendas' => $agendas, 'programs' => $programs));
    }

    public function notulen_detail($id = 0)
    {
        $this->guard_admin('notulen.read');
        $id = (int) $id;
        $item = $this->db->select('n.*,p.title agenda_title,w.title program_title,a.username approver,c.username creator')->from('activity_notes n')
            ->join('posts p', 'p.id=n.agenda_id', 'left')->join('work_programs w', 'w.id=n.program_id', 'left')
            ->join('admins a', 'a.id=n.approved_by', 'left')->join('admins c', 'c.id=n.created_by', 'left')->where('n.id', $id)->get()->row_array();
        if (!$item) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 30);
            try {
                $this->atomic(function () use ($id, $act) {
                    $n = $this->lock('activity_notes', $id);
                    if ($act === 'approve') {
                        $this->need('notulen.approve');
                        if ($n['status'] !== 'final') throw new RuntimeException('Hanya notulen berstatus "Menunggu Pengesahan" yang dapat disahkan.');
                        $this->db->where('id', $id)->update('activity_notes', array('status' => 'disahkan', 'approved_by' => $this->admin_id, 'approved_at' => date('Y-m-d H:i:s')));
                    } elseif ($act === 'reopen') {
                        $this->need('notulen.approve');
                        if ($n['status'] !== 'disahkan') throw new RuntimeException('Notulen belum disahkan.');
                        $this->db->where('id', $id)->update('activity_notes', array('status' => 'draft', 'approved_by' => NULL, 'approved_at' => NULL));
                    } elseif ($act === 'action_add') {
                        $this->need('notulen.write');
                        $title = $this->str('a_title', 190);
                        if (mb_strlen($title) < 3) throw new RuntimeException('Uraian tindak lanjut wajib diisi.');
                        $this->db->insert('action_items', array('note_id' => $id, 'title' => $title, 'pic' => $this->str('a_pic', 120), 'due_date' => $this->date_or_null('a_due')));
                    } elseif ($act === 'action_update') {
                        $this->need('notulen.write');
                        $aid = (int) $this->input->post('action_id');
                        if (!$this->db->get_where('action_items', array('id' => $aid, 'note_id' => $id))->row_array()) throw new RuntimeException('Tindak lanjut tidak ditemukan.');
                        $this->db->where('id', $aid)->update('action_items', array('status' => $this->choice('a_status', self::$action_labels, 'open'), 'progress_note' => $this->str('a_note', 255), 'updated_at' => date('Y-m-d H:i:s')));
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->audit($act, 'notulen:' . $id);
                });
                $this->back('admin/notulen/' . $id, 'Notulen diperbarui.');
            } catch (RuntimeException $e) {
                $this->back('admin/notulen/' . $id, '', $e->getMessage());
            }
        }
        $actions = $this->db->order_by('due_date IS NULL', '', FALSE)->order_by('due_date')->get_where('action_items', array('note_id' => $id))->result_array();
        $versions = $this->db->select('v.version,v.created_at,a.username')->from('activity_note_versions v')->join('admins a', 'a.id=v.changed_by', 'left')->where('v.note_id', $id)->order_by('v.version', 'DESC')->get()->result_array();
        $this->render('notulen_detail', array('item' => $item, 'actions' => $actions, 'versions' => $versions, 'labels' => self::$note_labels, 'tones' => self::$note_tones,
            'action_labels' => self::$action_labels, 'action_tones' => self::$action_tones));
    }

    /* ===================== 09 RONDA ===================== */

    public function ronda_index()
    {
        $this->guard_admin('ronda.read');
        $from = $this->str('dari', 10, 'get');
        if (!ops_valid_date($from)) $from = date('Y-m-d', strtotime('monday this week'));
        $to = date('Y-m-d', strtotime($from . ' +13 days'));
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('ronda.write');
            $act = $this->str('act', 30);
            try {
                if ($act === 'add') {
                    $date = $this->date_or_null('patrol_date');
                    $shift = $this->choice('shift', self::$shifts);
                    $citizen = (int) $this->input->post('citizen_id');
                    $name = $this->str('officer_name', 140);
                    if ($citizen) {
                        $c = $this->db->get_where('citizens', array('id' => $citizen, 'is_active' => 1))->row_array();
                        if (!$c) throw new RuntimeException('Warga tidak ditemukan.');
                        if ($name === '') $name = $c['full_name'] ?: $c['display_name'];
                    }
                    if (!$date || !$shift || mb_strlen($name) < 2) throw new RuntimeException('Tanggal, shift, dan nama petugas wajib diisi.');
                    $this->atomic(function () use ($date, $shift, $name, $citizen) {
                        $clash = $this->db->query('SELECT id,shift FROM patrol_schedules WHERE patrol_date=? AND (officer_name=? OR (citizen_id IS NOT NULL AND citizen_id=?)) FOR UPDATE', array($date, $name, $citizen ?: -1))->result_array();
                        foreach ($clash as $cl) if ($cl['shift'] === $shift || $cl['shift'] === 'penuh' || $shift === 'penuh') throw new RuntimeException('Jadwal bentrok: ' . $name . ' sudah terjadwal pada ' . self::$shifts[$cl['shift']] . ' di tanggal yang sama.');
                        $this->db->insert('patrol_schedules', array('patrol_date' => $date, 'shift' => $shift, 'post_location' => $this->str('post_location', 120) ?: 'Pos Ronda',
                            'officer_name' => $name, 'citizen_id' => $citizen ?: NULL, 'created_by' => $this->admin_id));
                        $this->audit('create', 'patrol:' . $this->db->insert_id(), $date . ' ' . $shift);
                    });
                    $this->back('admin/ronda?dari=' . $from, 'Jadwal ronda ditambahkan.');
                } elseif ($act === 'attendance') {
                    $sid = (int) $this->input->post('schedule_id');
                    $status = $this->choice('attendance', self::$attendance);
                    $note = $this->str('attendance_note', 255);
                    if (!$status || mb_strlen($note) < 3) throw new RuntimeException('Pilih status kehadiran dan isi alasan perubahan manual (minimal 3 karakter).');
                    $sub = $this->str('substitute_name', 140);
                    if ($status === 'diganti' && $sub === '') throw new RuntimeException('Isi nama petugas pengganti.');
                    $this->atomic(function () use ($sid, $status, $note, $sub) {
                        $s = $this->lock('patrol_schedules', $sid);
                        if (!$s) throw new RuntimeException('Jadwal tidak ditemukan.');
                        $this->db->where('id', $sid)->update('patrol_schedules', array('attendance' => $status, 'attendance_note' => $note, 'substitute_name' => $sub,
                            'marked_by_admin' => $this->admin_id, 'updated_at' => date('Y-m-d H:i:s')));
                        $this->audit('attendance_manual', 'patrol:' . $sid, $s['attendance'] . '->' . $status . ' alasan: ' . $note);
                    });
                    $this->back('admin/ronda?dari=' . $from, 'Kehadiran diperbarui (tercatat di audit).');
                } elseif ($act === 'delete') {
                    $sid = (int) $this->input->post('schedule_id');
                    $s = $this->db->get_where('patrol_schedules', array('id' => $sid))->row_array();
                    if (!$s || $s['attendance'] !== 'scheduled' || $s['patrol_date'] < date('Y-m-d')) throw new RuntimeException('Hanya jadwal mendatang yang belum ada kehadirannya yang dapat dihapus.');
                    $this->db->where('id', $sid)->delete('patrol_schedules');
                    $this->audit('delete', 'patrol:' . $sid, $s['patrol_date'] . ' ' . $s['officer_name']);
                    $this->back('admin/ronda?dari=' . $from, 'Jadwal dihapus.');
                }
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
        $items = $this->db->where('patrol_date >=', $from)->where('patrol_date <=', $to)->order_by('patrol_date')->order_by("FIELD(shift,'penuh','shift_1','shift_2')", '', FALSE)->get('patrol_schedules')->result_array();
        $recap = $this->db->query("SELECT attendance, COUNT(*) n FROM patrol_schedules WHERE patrol_date BETWEEN ? AND ? GROUP BY attendance", array(date('Y-m-01'), date('Y-m-t')))->result_array();
        $citizens = array();
        foreach ($this->db->select('id,display_name,full_name')->where('is_active', 1)->order_by('display_name')->get('citizens')->result_array() as $c) $citizens[(int) $c['id']] = $c['full_name'] ?: $c['display_name'];
        $incidents = $this->db->order_by('incident_at', 'DESC')->limit(10)->get('incident_reports')->result_array();
        $this->render('ronda_index', array('items' => $items, 'from' => $from, 'to' => $to, 'recap' => array_column($recap, 'n', 'attendance'), 'citizens' => $citizens, 'incidents' => $incidents,
            'error' => $error, 'shifts' => self::$shifts, 'attendance' => self::$attendance, 'attendance_tones' => self::$attendance_tones, 'incident_categories' => $this->incident_categories));
    }

    public function ronda_kejadian()
    {
        $this->guard_admin('ronda.write');
        $this->post_only();
        $at = $this->str('incident_at', 16);
        $at = str_replace('T', ' ', $at);
        $desc = $this->text('description', 3000);
        if (!DateTime::createFromFormat('Y-m-d H:i', $at) || mb_strlen($desc) < 5) $this->back('admin/ronda', '', 'Waktu kejadian dan uraian wajib diisi.');
        $this->db->insert('incident_reports', array('incident_at' => $at . ':00', 'location' => $this->str('location', 160), 'category' => $this->choice('category', $this->incident_categories, 'lainnya'),
            'description' => $desc, 'action_taken' => $this->text('action_taken', 3000), 'schedule_id' => (int) $this->input->post('schedule_id') ?: NULL, 'reported_by_admin' => $this->admin_id));
        $this->audit('create', 'incident:' . $this->db->insert_id());
        $this->back('admin/ronda', 'Laporan kejadian dicatat.');
    }

    /** Warga melihat jadwal ronda dan melakukan check-in/out untuk jadwal miliknya sendiri. */
    public function ronda_warga()
    {
        $this->guard_citizen();
        if ($this->input->method(TRUE) === 'POST') {
            $sid = (int) $this->input->post('schedule_id');
            $act = $this->choice('act', array('checkin' => 1, 'checkout' => 1));
            try {
                $this->atomic(function () use ($sid, $act) {
                    $s = $this->lock('patrol_schedules', $sid);
                    // Kepemilikan & tanggal divalidasi di server: parameter URL saja tidak cukup untuk absen.
                    if (!$s || (int) $s['citizen_id'] !== $this->citizen_id) throw new RuntimeException('Jadwal ini bukan milik Anda.');
                    $start = strtotime($s['patrol_date'] . ' ' . $this->shift_start[$s['shift']] . ($s['shift'] === 'shift_2' ? ' +1 day' : ''));
                    $now = time();
                    if ($act === 'checkin') {
                        if ($s['check_in']) throw new RuntimeException('Anda sudah check-in.');
                        if ($now < $start - 3600 || $now > $start + 6 * 3600) throw new RuntimeException('Check-in hanya dapat dilakukan mulai 1 jam sebelum hingga 6 jam setelah shift dimulai.');
                        $this->db->where('id', $sid)->update('patrol_schedules', array('check_in' => date('Y-m-d H:i:s'), 'attendance' => $now > $start + 900 ? 'terlambat' : 'hadir', 'updated_at' => date('Y-m-d H:i:s')));
                    } else {
                        if (!$s['check_in'] || $s['check_out']) throw new RuntimeException('Check-out membutuhkan check-in terlebih dahulu.');
                        $this->db->where('id', $sid)->update('patrol_schedules', array('check_out' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
                    }
                });
                $this->audit_citizen($act, 'patrol:' . $sid);
                $this->back('warga/ronda', $act === 'checkin' ? 'Check-in tercatat. Selamat bertugas!' : 'Check-out tercatat. Terima kasih.');
            } catch (RuntimeException $e) {
                $this->back('warga/ronda', '', $e->getMessage());
            }
        }
        $mine = $this->db->where('citizen_id', $this->citizen_id)->where('patrol_date >=', date('Y-m-d', strtotime('-1 day')))->order_by('patrol_date')->limit(20)->get('patrol_schedules')->result_array();
        $all = $this->db->select('patrol_date,shift,post_location,officer_name')->where('patrol_date >=', date('Y-m-d'))->where('patrol_date <=', date('Y-m-d', strtotime('+7 days')))
            ->order_by('patrol_date')->order_by('shift')->get('patrol_schedules')->result_array();
        $this->render_citizen('ronda', 'Jadwal Ronda', array('mine' => $mine, 'all' => $all, 'shifts' => self::$shifts, 'attendance' => self::$attendance, 'attendance_tones' => self::$attendance_tones));
    }
}
