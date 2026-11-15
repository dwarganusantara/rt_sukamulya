<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * "Pengajuan Anda sudah diproses admin" popup for residents.
 * A decision is shown once per (request, status): if admin later changes the
 * status again (e.g. revision -> approved) the resident is told again.
 */

const CITIZEN_NOTICE_DAYS = 14; // older decisions are history, not news

function citizen_notice_ready()
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('citizen_notice_reads')) {
        $CI->db->query('CREATE TABLE IF NOT EXISTS `citizen_notice_reads` (
            `citizen_id` INT UNSIGNED NOT NULL, `kind` VARCHAR(10) NOT NULL, `ref_id` INT UNSIGNED NOT NULL,
            `status` VARCHAR(30) NOT NULL, `seen_at` DATETIME NOT NULL,
            PRIMARY KEY (`citizen_id`,`kind`,`ref_id`,`status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $CI->db->data_cache = array(); // refresh CI's cached table list
    }
    return TRUE;
}

/** Unseen admin decisions for one resident, newest first. */
function citizen_notice_items($citizen_id)
{
    $CI =& get_instance();
    $citizen_id = (int) $citizen_id;
    if (!$citizen_id) return array();
    citizen_notice_ready();
    $since = date('Y-m-d H:i:s', strtotime('-' . CITIZEN_NOTICE_DAYS . ' days'));
    $time = function ($d) { return $d ? date('d M Y, H:i', strtotime($d)) : ''; };
    $items = array();

    if ($CI->db->table_exists('kk_scan_requests')) {
        $rows = $CI->db->query("SELECT r.id,r.status,r.review_notes,r.reviewed_at FROM kk_scan_requests r
            WHERE r.citizen_id=? AND r.status IN ('approved','rejected','revision_requested') AND r.reviewed_at>=?
            AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=r.citizen_id AND n.kind='kk' AND n.ref_id=r.id AND n.status COLLATE utf8mb4_unicode_ci = r.status COLLATE utf8mb4_unicode_ci)
            ORDER BY r.reviewed_at DESC LIMIT 10", array($citizen_id, $since));
        $rows = $rows ? $rows->result_array() : array();
        $map = array(
            'approved' => array('approved', 'Pengajuan Disetujui!', 'Data Kartu Keluarga Anda telah diverifikasi dan di-ACC oleh Admin RT. Data keluarga kini tampil di akun Anda.', 'Lihat Data KK', 'warga/keluarga'),
            'rejected' => array('rejected', 'Pengajuan Ditolak', 'Pengajuan data Kartu Keluarga Anda tidak dapat disetujui oleh Admin RT. Baca alasannya di bawah ini.', 'Lihat Detail', 'warga/keluarga#riwayat'),
            'revision_requested' => array('revision', 'Perlu Perbaikan Data', 'Admin RT meminta Anda memperbaiki data Kartu Keluarga lalu mengajukannya kembali.', 'Perbaiki Sekarang', 'warga/keluarga/scan?manual=1'),
        );
        foreach ($rows as $r) {
            $m = $map[$r['status']];
            $items[] = array('kind' => 'kk', 'id' => (int) $r['id'], 'status' => $r['status'], 'tone' => $m[0], 'title' => $m[1], 'message' => $m[2],
                'type' => 'Verifikasi Data KK', 'code' => 'KK-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT), 'note' => (string) $r['review_notes'],
                'time' => $time($r['reviewed_at']), 'sort' => $r['reviewed_at'], 'action' => $m[3], 'url' => site_url($m[4]));
        }
    }

    $rows = $CI->db->query("SELECT s.id,s.subject,s.status,s.admin_note,s.updated_at FROM service_requests s
        WHERE s.citizen_id=? AND s.status IN ('diproses','disetujui','ditolak','selesai') AND s.updated_at>=?
        AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=s.citizen_id AND n.kind='sr' AND n.ref_id=s.id AND n.status COLLATE utf8mb4_unicode_ci = s.status COLLATE utf8mb4_unicode_ci)
        ORDER BY s.updated_at DESC LIMIT 10", array($citizen_id, $since));
    $rows = $rows ? $rows->result_array() : array();
    $map = array(
        'diproses' => array('revision', 'Pengajuan Sedang Diproses', 'Pengajuan Anda sudah diterima dan sedang diperiksa oleh pengurus RT.', 'Lihat Pengajuan'),
        'disetujui' => array('approved', 'Surat Anda Disetujui!', 'Pengajuan surat Anda telah di-ACC oleh Admin RT dan siap digunakan.', 'Lihat Surat'),
        'selesai' => array('approved', 'Pengajuan Selesai!', 'Pengajuan Anda telah selesai diproses oleh Admin RT.', 'Lihat Pengajuan'),
        'ditolak' => array('rejected', 'Pengajuan Surat Ditolak', 'Pengajuan surat Anda tidak dapat disetujui oleh Admin RT. Baca alasannya di bawah ini.', 'Lihat Detail'),
    );
    foreach ($rows as $r) {
        $m = $map[$r['status']];
        $items[] = array('kind' => 'sr', 'id' => (int) $r['id'], 'status' => $r['status'], 'tone' => $m[0], 'title' => $m[1], 'message' => $m[2],
            'type' => $r['subject'], 'code' => 'PGJ-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT), 'note' => (string) $r['admin_note'],
            'time' => $time($r['updated_at']), 'sort' => $r['updated_at'], 'action' => $m[3], 'url' => site_url('warga/pengajuan/' . (int) $r['id']));
    }
    // Modul 02: keputusan mutasi data keluarga.
    if ($CI->db->table_exists('resident_mutations')) {
        $rows = $CI->db->query("SELECT m.id,m.status,m.review_note,m.reviewed_at FROM resident_mutations m
            WHERE m.citizen_id=? AND m.status IN ('approved','rejected','revision_requested') AND m.reviewed_at>=?
            AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=m.citizen_id AND n.kind='mt' AND n.ref_id=m.id AND n.status COLLATE utf8mb4_unicode_ci = m.status COLLATE utf8mb4_unicode_ci)
            ORDER BY m.reviewed_at DESC LIMIT 10", array($citizen_id, $since));
        $map = array(
            'approved' => array('approved', 'Mutasi Disetujui!', 'Perubahan data keluarga Anda telah disetujui dan diterapkan oleh pengurus RT.', 'Lihat Data Keluarga'),
            'rejected' => array('rejected', 'Mutasi Ditolak', 'Pengajuan mutasi data keluarga Anda tidak dapat disetujui. Baca alasannya di bawah ini.', 'Lihat Detail'),
            'revision_requested' => array('revision', 'Mutasi Perlu Perbaikan', 'Pengurus RT meminta Anda memperbaiki pengajuan mutasi lalu mengirimkannya kembali.', 'Perbaiki Sekarang'),
        );
        foreach (($rows ? $rows->result_array() : array()) as $r) {
            $m = $map[$r['status']];
            $items[] = array('kind' => 'mt', 'id' => (int) $r['id'], 'status' => $r['status'], 'tone' => $m[0], 'title' => $m[1], 'message' => $m[2],
                'type' => 'Mutasi Data Keluarga', 'code' => 'MUT-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT), 'note' => (string) $r['review_note'],
                'time' => $time($r['reviewed_at']), 'sort' => $r['reviewed_at'], 'action' => $m[3], 'url' => site_url('warga/mutasi/' . (int) $r['id']));
        }
    }

    // Modul 03: tiket pengaduan yang butuh perhatian warga.
    if ($CI->db->table_exists('complaints')) {
        $rows = $CI->db->query("SELECT c.id,c.ticket_no,c.status,c.resolution_note,c.updated_at FROM complaints c
            WHERE c.citizen_id=? AND c.status IN ('waiting_resident','resolved','rejected') AND c.updated_at>=?
            AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=c.citizen_id AND n.kind='cp' AND n.ref_id=c.id AND n.status COLLATE utf8mb4_unicode_ci = c.status COLLATE utf8mb4_unicode_ci)
            ORDER BY c.updated_at DESC LIMIT 10", array($citizen_id, $since));
        $map = array(
            'resolved' => array('approved', 'Pengaduan Selesai Ditangani', 'Pengurus RT menandai pengaduan Anda selesai. Konfirmasi bila sudah tuntas atau buka kembali bila belum.', 'Konfirmasi'),
            'waiting_resident' => array('revision', 'Pengurus Menunggu Tanggapan', 'Pengurus RT membutuhkan informasi tambahan untuk pengaduan Anda.', 'Beri Tanggapan'),
            'rejected' => array('rejected', 'Pengaduan Ditolak', 'Pengaduan Anda tidak dapat ditindaklanjuti. Baca alasannya pada detail tiket.', 'Lihat Detail'),
        );
        foreach (($rows ? $rows->result_array() : array()) as $r) {
            $m = $map[$r['status']];
            $items[] = array('kind' => 'cp', 'id' => (int) $r['id'], 'status' => $r['status'], 'tone' => $m[0], 'title' => $m[1], 'message' => $m[2],
                'type' => 'Pengaduan & Aspirasi', 'code' => (string) $r['ticket_no'], 'note' => (string) $r['resolution_note'],
                'time' => $time($r['updated_at']), 'sort' => $r['updated_at'], 'action' => $m[3], 'url' => site_url('warga/pengaduan/' . (int) $r['id']));
        }
    }

    // Modul 03 (lanjutan): tindak lanjut / tanggapan petugas pada tiket pengaduan.
    // Hanya tanggapan publik (is_internal=0) dari petugas. Perubahan status yang sudah dikabarkan di atas
    // (menunggu warga / selesai / ditolak) tidak diulang. Satu item per tiket = tanggapan terbaru; penanda
    // dibaca memakai status "u<ID tanggapan>", jadi tanggapan berikutnya memunculkan pop-up lagi.
    if ($CI->db->table_exists('complaints') && $CI->db->table_exists('complaint_updates')) {
        $rows = $CI->db->query("SELECT t.* FROM (
                SELECT c.id, c.ticket_no, c.title, c.status, MAX(u.id) AS last_id, COUNT(u.id) AS n
                  FROM complaints c
                  JOIN complaint_updates u ON u.complaint_id=c.id AND u.actor_type='admin' AND u.is_internal=0 AND u.created_at>=?
                       AND (u.status_to IS NULL OR u.status_to NOT IN ('waiting_resident','resolved','rejected'))
                 WHERE c.citizen_id=?
                 GROUP BY c.id, c.ticket_no, c.title, c.status) t
            WHERE NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=? AND n.kind='cf' AND n.ref_id=t.id
                  AND n.status COLLATE utf8mb4_unicode_ci = CONCAT('u', t.last_id) COLLATE utf8mb4_unicode_ci)
            ORDER BY t.last_id DESC LIMIT 5", array($since, $citizen_id, $citizen_id));
        $rows = $rows ? $rows->result_array() : array();
        $cf_labels = array('submitted' => 'Diterima', 'triaged' => 'Ditelaah', 'assigned' => 'Ditugaskan', 'in_progress' => 'Diproses', 'reopened' => 'Dibuka Kembali',
            'closed' => 'Ditutup', 'waiting_resident' => 'Menunggu Tanggapan Anda', 'resolved' => 'Selesai Ditangani', 'rejected' => 'Ditolak');
        $latest = array();
        if ($rows) foreach ($CI->db->select('id,message,created_at')->where_in('id', array_map(function ($r) {return (int) $r['last_id'];}, $rows))->get('complaint_updates')->result_array() as $u) $latest[(int) $u['id']] = $u;
        foreach ($rows as $r) {
            $u = $latest[(int) $r['last_id']] ?? array('message' => '', 'created_at' => NULL);
            $items[] = array('kind' => 'cf', 'id' => (int) $r['id'], 'status' => 'u' . (int) $r['last_id'], 'status_label' => ($cf_labels[$r['status']] ?? 'Ditindaklanjuti'), 'tone' => 'approved', 'kicker' => 'Tindak Lanjut Pengaduan',
                'title' => 'Ada Tanggapan dari Pengurus', 'message' => 'Pengurus RT menindaklanjuti pengaduan "' . $r['title'] . '".' . ((int) $r['n'] > 1 ? ' Ada ' . (int) $r['n'] . ' tanggapan baru.' : ''),
                'type' => 'Pengaduan & Aspirasi', 'code' => (string) $r['ticket_no'], 'note' => (string) $u['message'],
                'time' => $time($u['created_at']), 'sort' => (string) $u['created_at'], 'action' => 'Lihat Tindak Lanjut', 'url' => site_url('warga/pengaduan/' . (int) $r['id']), 'labels' => array('type' => 'Jenis', 'code' => 'No. Tiket', 'time' => 'Ditanggapi'));
        }
    }

    // Modul 13: tahapan bantuan sosial — diusulkan, lolos verifikasi (layak), disetujui, tidak layak, atau ditolak.
    // Diusulkan & layak hanya untuk program yang masih berjalan; keputusan akhir tetap dikabarkan walau program sudah ditutup.
    if ($CI->db->table_exists('aid_recipients') && $CI->db->table_exists('aid_programs')) {
        $when = "(CASE r.status WHEN 'usulan' THEN r.created_at WHEN 'layak' THEN r.verified_at WHEN 'tidak_layak' THEN r.verified_at ELSE r.approved_at END)";
        $rows = $CI->db->query("SELECT r.id,r.status,r.decision_note,r.amount,p.name,p.aid_type,p.period,p.unit_value, $when decided_at
            FROM aid_recipients r JOIN aid_programs p ON p.id=r.program_id
            WHERE r.citizen_id=?
            AND (r.status IN ('disetujui','tidak_layak','ditolak') OR (r.status IN ('usulan','layak') AND p.status<>'selesai'))
            AND $when>=?
            AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=r.citizen_id AND n.kind='bs' AND n.ref_id=r.id AND n.status COLLATE utf8mb4_unicode_ci = r.status COLLATE utf8mb4_unicode_ci)
            ORDER BY decided_at DESC LIMIT 5", array($citizen_id, $since));
        foreach (($rows ? $rows->result_array() : array()) as $r) {
            $detail = trim($r['aid_type'] . ($r['period'] !== '' ? ' · ' . ucfirst($r['period']) : ''));
            $prog = '"' . $r['name'] . '"';
            // Nominal = "Nilai per Penerima" program saat ini (data program bisa diubah admin setelah warga diusulkan).
            $value = (float) $r['unit_value'] > 0 ? (float) $r['unit_value'] : (float) $r['amount'];
            $rupiah = $value > 0 ? 'Rp ' . number_format($value, 0, ',', '.') : '';
            $amount = $rupiah !== '' ? ' senilai ' . $rupiah : '';
            $kick = '';
            if ($r['status'] === 'usulan') {
                $m = array('approved', 'Anda Diusulkan Menerima Bantuan', 'Anda diusulkan sebagai calon penerima bantuan ' . $prog . $amount . '. Pengurus RT akan memverifikasi kelayakan terlebih dahulu; hasilnya akan dikabarkan di sini.');
                $kick = 'Kabar dari Admin RT';
            } elseif ($r['status'] === 'layak') {
                $m = array('approved', 'Lolos Verifikasi Bantuan', 'Anda dinyatakan layak sebagai calon penerima bantuan ' . $prog . $amount . '. Saat ini menunggu persetujuan akhir dari pengurus RT.');
                $kick = 'Kabar baik dari Admin RT';
            } elseif ($r['status'] === 'disetujui') {
                $m = array('approved', 'Bantuan Sosial Disetujui!', 'Selamat, Anda ditetapkan sebagai penerima bantuan ' . $prog . $amount . '. Pengurus RT akan menginformasikan jadwal penyalurannya.');
            } elseif ($r['status'] === 'tidak_layak') {
                $m = array('rejected', 'Belum Memenuhi Kriteria', 'Setelah diverifikasi, Anda belum memenuhi kriteria penerima bantuan ' . $prog . '. Baca alasannya di bawah ini. Hubungi pengurus RT bila ada data yang perlu diperbarui.');
            } else {
                $m = array('rejected', 'Usulan Bantuan Ditolak', 'Usulan Anda sebagai penerima bantuan ' . $prog . ' tidak disetujui oleh pengurus RT. Baca alasannya di bawah ini.');
            }
            // Catatan verifikasi (kriteria kelayakan) tidak ditampilkan saat diusulkan/layak; alasan tetap tampil saat tidak layak/ditolak/disetujui.
            $note = in_array($r['status'], array('usulan', 'layak'), TRUE) ? '' : (string) $r['decision_note'];
            $items[] = array('kind' => 'bs', 'id' => (int) $r['id'], 'status' => $r['status'], 'tone' => $m[0], 'kicker' => $kick, 'title' => $m[1], 'message' => $m[2],
                'program' => (string) $r['name'], 'amount' => in_array($r['status'], array('tidak_layak', 'ditolak'), TRUE) ? '' : $rupiah,
                'type' => 'Bantuan Sosial' . ($detail !== '' ? ' — ' . $detail : ''), 'code' => 'BNS-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT), 'note' => $note,
                'time' => $time($r['decided_at']), 'sort' => $r['decided_at'], 'action' => 'Mengerti', 'url' => site_url('warga'));
        }
    }

    // Jadwal ronda baru untuk warga ini (jadwal yang dihubungkan ke akunnya, belum lewat). Muncul sekali per jadwal.
    if ($CI->db->table_exists('patrol_schedules')) {
        if (!isset($CI->feature_gate)) $CI->load->library('Feature_gate');
        if ($CI->feature_gate->link_ok('warga/ronda', 'warga')) {
            $shifts = array('shift_1' => 'Shift 1 (22.00–01.00)', 'shift_2' => 'Shift 2 (01.00–04.00)', 'penuh' => 'Semalam (22.00–04.00)');
            $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
            $rows = $CI->db->query("SELECT s.id,s.patrol_date,s.shift,s.post_location,s.created_at FROM patrol_schedules s
                WHERE s.citizen_id=? AND s.patrol_date>=CURDATE() AND s.created_at>=?
                AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=s.citizen_id AND n.kind='rd' AND n.ref_id=s.id AND n.status='jadwal')
                ORDER BY s.patrol_date ASC, s.id ASC LIMIT 5", array($citizen_id, $since));
            foreach (($rows ? $rows->result_array() : array()) as $r) {
                $ts = strtotime($r['patrol_date']);
                $when = $hari[(int) date('w', $ts)] . ', ' . date('j M Y', $ts);
                $shift = $shifts[$r['shift']] ?? $r['shift'];
                $items[] = array('kind' => 'rd', 'id' => (int) $r['id'], 'status' => 'jadwal', 'status_label' => 'Terjadwal', 'tone' => 'approved', 'kicker' => 'Jadwal Ronda dari Pengurus RT',
                    'title' => 'Anda Dijadwalkan Ronda', 'message' => 'Anda dijadwalkan ronda pada ' . $when . ', ' . $shift . ' di ' . ($r['post_location'] ?: 'Pos Ronda') . '. Jangan lupa check-in saat bertugas.',
                    'type' => 'Jadwal Ronda — ' . $shift, 'code' => 'RND-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT), 'note' => '',
                    'time' => $time($r['created_at']), 'sort' => $r['created_at'], 'action' => 'Lihat Jadwal Ronda', 'url' => site_url('warga/ronda'), 'labels' => array('type' => 'Jadwal', 'code' => 'No. Jadwal', 'time' => 'Dijadwalkan'));
            }
        }
    }

    // Pengumuman baru dari pengurus (berstatus terbit). Muncul sekali per warga per pengumuman.
    if ($CI->db->table_exists('posts')) {
        if (!isset($CI->feature_gate)) $CI->load->library('Feature_gate');
        if ($CI->feature_gate->link_ok('pengumuman', 'warga')) {
            $rows = $CI->db->query("SELECT p.id,p.title,p.body,p.created_at FROM posts p
                WHERE p.type='pengumuman' AND p.status='published' AND p.created_at>=?
                AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=? AND n.kind='an' AND n.ref_id=p.id AND n.status='pub')
                ORDER BY p.created_at DESC LIMIT 3", array($since, $citizen_id));
            foreach (($rows ? $rows->result_array() : array()) as $r) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $r['body']), ENT_QUOTES, 'UTF-8')));
                if (mb_strlen($text) > 180) $text = mb_substr($text, 0, 177) . '…';
                $items[] = array('kind' => 'an', 'id' => (int) $r['id'], 'status' => 'pub', 'status_label' => 'Pengumuman Baru', 'tone' => 'approved', 'kicker' => 'Pengumuman dari Pengurus RT',
                    'title' => (string) $r['title'], 'message' => $text, 'type' => 'Pengumuman', 'code' => 'PNG-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT), 'note' => '',
                    'time' => $time($r['created_at']), 'sort' => $r['created_at'], 'action' => 'Baca Pengumuman', 'url' => site_url('pengumuman/' . (int) $r['id']), 'labels' => array('type' => 'Jenis', 'code' => 'No. Pengumuman', 'time' => 'Diterbitkan'));
            }
        }

        // Agenda yang baru diterbitkan. Hanya agenda hari ini/mendatang dan muncul sekali per warga.
        if ($CI->feature_gate->link_ok('agenda', 'warga')) {
            $rows = $CI->db->query("SELECT p.id,p.title,p.body,p.event_date,p.event_time,p.event_location,p.created_at FROM posts p
                WHERE p.type='agenda' AND p.status='published' AND p.event_date>=? AND p.created_at>=?
                AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=? AND n.kind='ag' AND n.ref_id=p.id AND n.status='pub')
                ORDER BY p.created_at DESC LIMIT 3", array(date('Y-m-d'), $since, $citizen_id));
            foreach (($rows ? $rows->result_array() : array()) as $r) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $r['body']), ENT_QUOTES, 'UTF-8')));
                if (mb_strlen($text) > 120) $text = mb_substr($text, 0, 117) . '…';
                $schedule = date('d M Y', strtotime($r['event_date']));
                if (!empty($r['event_time'])) $schedule .= ', pukul ' . substr($r['event_time'], 0, 5) . ' WIB';
                if (trim((string) $r['event_location']) !== '') $schedule .= ' di ' . trim((string) $r['event_location']);
                $message = 'Akan dilaksanakan pada ' . $schedule . '.' . ($text !== '' ? ' ' . $text : '');
                $items[] = array('kind' => 'ag', 'id' => (int) $r['id'], 'status' => 'pub', 'status_label' => 'Agenda Baru', 'tone' => 'approved', 'kicker' => 'Agenda dari Pengurus RT',
                    'title' => (string) $r['title'], 'message' => $message, 'type' => 'Agenda Kegiatan', 'code' => 'AGD-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT), 'note' => '',
                    'time' => $time($r['created_at']), 'sort' => $r['created_at'], 'action' => 'Lihat Agenda', 'url' => site_url('agenda/' . (int) $r['id']));
            }
        }
    }

    // Modul 05: pengingat iuran yang dikirim bendahara.
    if ($CI->db->table_exists('due_reminders')) {
        $rows = $CI->db->query("SELECT r.id,r.message,r.created_at FROM due_reminders r
            WHERE r.citizen_id=? AND r.created_at>=?
            AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=r.citizen_id AND n.kind='rm' AND n.ref_id=r.id AND n.status='sent')
            ORDER BY r.created_at DESC LIMIT 5", array($citizen_id, $since));
        foreach (($rows ? $rows->result_array() : array()) as $r) {
            $items[] = array('kind' => 'rm', 'id' => (int) $r['id'], 'status' => 'sent', 'tone' => 'revision', 'title' => 'Pengingat Iuran', 'message' => (string) $r['message'],
                'type' => 'Iuran Warga', 'code' => 'REM-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT), 'note' => '', 'time' => $time($r['created_at']), 'sort' => $r['created_at'],
                'action' => 'Lihat Tagihan', 'url' => site_url('warga/iuran'));
        }
    }

    usort($items, function ($a, $b) { return strcmp($b['sort'], $a['sort']); });
    foreach ($items as &$it) unset($it['sort']);
    return $items;
}

/** Records that the resident saw these decisions; ignores anything not theirs. */
function citizen_notice_mark_seen($citizen_id, $items)
{
    $CI =& get_instance();
    $citizen_id = (int) $citizen_id;
    if (!$citizen_id || !is_array($items)) return 0;
    citizen_notice_ready();
    $valid = array();
    foreach (citizen_notice_items($citizen_id) as $it) $valid[$it['kind'] . ':' . $it['id'] . ':' . $it['status']] = $it;
    $done = 0;
    foreach (array_slice($items, 0, 50) as $item) {
        if (!is_array($item)) continue;
        $key = (string) ($item['kind'] ?? '') . ':' . (int) ($item['id'] ?? 0) . ':' . (string) ($item['status'] ?? '');
        if (!isset($valid[$key])) continue;
        $it = $valid[$key];
        $CI->db->query('INSERT IGNORE INTO citizen_notice_reads (citizen_id,kind,ref_id,status,seen_at) VALUES (?,?,?,?,?)',
            array($citizen_id, $it['kind'], $it['id'], $it['status'], date('Y-m-d H:i:s')));
        $done++;
    }
    return $done;
}
