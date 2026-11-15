<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pending work the admin must act on: KK, letter requests, complaints, and due payments.
 * Used by the footer popup (first render) and Admin::alerts (polling).
 * Returns NULL for roles that cannot act on any queue.
 */
function admin_alert_data()
{
    $CI =& get_instance();
    $role = $CI->session->userdata('admin_role');
    $core_roles = array('admin', 'operator', 'ketua');
    $CI->config->load('rt_access', TRUE);
    $permissions = (array) $CI->config->item('rt_permissions', 'rt_access');
    $can_complaints = in_array($role, (array) ($permissions['pengaduan.handle'] ?? array()), TRUE);
    $can_dues = in_array($role, array('admin', 'bendahara', 'ketua'), TRUE);
    if (!in_array($role, $core_roles, TRUE) && !$can_complaints && !$can_dues) return NULL;
    $time = function ($d) { return $d ? date('d M Y, H:i', strtotime($d)) : ''; };
    $out = array(
        'kk' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/kk/pengajuan?status=pending')),
        'letters' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/requests?status=diajukan')),
        'complaints' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/pengaduan?status=submitted')),
        'dues' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/dues#pembayaran')),
    );
    if (!isset($CI->feature_gate)) $CI->load->library('Feature_gate');
    if (in_array($role, $core_roles, TRUE) && $CI->feature_gate->for_staff('family_card') && $CI->db->table_exists('kk_scan_requests')) {
        $out['kk']['count'] = $CI->db->where('status', 'pending')->count_all_results('kk_scan_requests');
        $rows = $CI->db->select('r.id,r.created_at,r.proposed_data_json,c.display_name,c.full_name')->from('kk_scan_requests r')
            ->join('citizens c', 'c.id=r.citizen_id')->where('r.status', 'pending')->order_by('r.id', 'DESC')->limit(4)->get()->result_array();
        foreach ($rows as $r) {
            $data = json_decode($r['proposed_data_json'], TRUE) ?: array();
            $out['kk']['items'][] = array('id' => (int) $r['id'], 'title' => $r['full_name'] ?: $r['display_name'],
                'meta' => 'KK-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT) . ' · ' . count($data['anggota'] ?? array()) . ' anggota', 'time' => $time($r['created_at']),
                'url' => site_url('admin/kk/pengajuan?status=pending&lihat=' . (int) $r['id']));
        }
        $out['kk']['latest_id'] = (int) ($rows[0]['id'] ?? 0);
    }
    if (in_array($role, $core_roles, TRUE) && $CI->feature_gate->for_staff('letters')) {
    $out['letters']['count'] = $CI->db->where('status', 'diajukan')->count_all_results('service_requests');
    $rows = $CI->db->select('s.id,s.subject,s.created_at,c.display_name,c.full_name')->from('service_requests s')
        ->join('citizens c', 'c.id=s.citizen_id')->where('s.status', 'diajukan')->order_by('s.id', 'DESC')->limit(4)->get()->result_array();
    foreach ($rows as $r) {
        $out['letters']['items'][] = array('id' => (int) $r['id'], 'title' => $r['full_name'] ?: $r['display_name'], 'meta' => $r['subject'],
            'time' => $time($r['created_at']), 'url' => site_url('admin/request_detail/' . (int) $r['id']));
    }
    $out['letters']['latest_id'] = (int) ($rows[0]['id'] ?? 0);
    }
    if ($can_complaints && $CI->feature_gate->for_staff('complaints') && $CI->db->table_exists('complaints')) {
        $out['complaints']['count'] = $CI->db->where('status', 'submitted')->count_all_results('complaints');
        $fields = 'p.id,p.ticket_no,p.title,p.category,p.created_at,c.display_name,c.full_name';
        if ($CI->db->field_exists('is_anonymous', 'complaints')) $fields .= ',p.is_anonymous';
        $rows = $CI->db->select($fields)->from('complaints p')->join('citizens c', 'c.id=p.citizen_id', 'left')
            ->where('p.status', 'submitted')->order_by('p.id', 'DESC')->limit(4)->get()->result_array();
        foreach ($rows as $r) {
            $reporter = !empty($r['is_anonymous']) ? 'Identitas pelapor disembunyikan' : ($r['full_name'] ?: ($r['display_name'] ?: 'Warga'));
            $out['complaints']['items'][] = array('id' => (int) $r['id'], 'title' => (string) $r['title'],
                'meta' => ($r['ticket_no'] ?: 'ADU-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT)) . ' Â· ' . $reporter,
                'time' => $time($r['created_at']), 'url' => site_url('admin/pengaduan/' . (int) $r['id']));
        }
        $out['complaints']['latest_id'] = (int) ($rows[0]['id'] ?? 0);
    }
    if ($can_dues && $CI->feature_gate->for_staff('dues') && $CI->db->table_exists('due_payments')) {
        $out['dues']['count'] = $CI->db->where('status', 'pending')->count_all_results('due_payments');
        $rows = $CI->db->select('p.id,p.amount,p.method,p.created_at,d.category,d.period,c.display_name,c.full_name')->from('due_payments p')
            ->join('dues d', 'd.id=p.due_id')->join('citizens c', 'c.id=p.citizen_id')
            ->where('p.status', 'pending')->order_by('p.id', 'DESC')->limit(4)->get()->result_array();
        foreach ($rows as $r) {
            $method = $r['method'] === 'tunai' ? 'Tunai' : 'Transfer';
            $out['dues']['items'][] = array('id' => (int) $r['id'], 'title' => $r['full_name'] ?: $r['display_name'],
                'meta' => 'Rp ' . number_format((float) $r['amount'], 0, ',', '.') . ' · ' . $r['category'] . ' ' . $r['period'] . ' · ' . $method,
                'time' => $time($r['created_at']), 'url' => site_url('admin/dues#pembayaran'));
        }
        $out['dues']['latest_id'] = (int) ($rows[0]['id'] ?? 0);
    }
    $out['total'] = $out['kk']['count'] + $out['letters']['count'] + $out['complaints']['count'] + $out['dues']['count'];
    // Changes whenever a new submission arrives (ids only grow), not when one is processed.
    $out['signature'] = 'kk' . $out['kk']['latest_id'] . '-sr' . $out['letters']['latest_id'] . '-cp' . $out['complaints']['latest_id'] . '-du' . $out['dues']['latest_id'];
    return $out;
}
