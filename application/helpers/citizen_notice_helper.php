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
        WHERE s.citizen_id=? AND s.status IN ('disetujui','ditolak','selesai') AND s.updated_at>=?
        AND NOT EXISTS (SELECT 1 FROM citizen_notice_reads n WHERE n.citizen_id=s.citizen_id AND n.kind='sr' AND n.ref_id=s.id AND n.status COLLATE utf8mb4_unicode_ci = s.status COLLATE utf8mb4_unicode_ci)
        ORDER BY s.updated_at DESC LIMIT 10", array($citizen_id, $since));
    $rows = $rows ? $rows->result_array() : array();
    $map = array(
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
