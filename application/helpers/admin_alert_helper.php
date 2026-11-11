<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pending work the admin must act on: KK verification and letter requests.
 * Used by the footer popup (first render) and Admin::alerts (polling).
 * Returns NULL for roles that cannot act on either queue (e.g. bendahara).
 */
function admin_alert_data()
{
    $CI =& get_instance();
    $role = $CI->session->userdata('admin_role');
    if (!in_array($role, array('admin', 'operator', 'ketua'), TRUE)) return NULL;
    $time = function ($d) { return $d ? date('d M Y, H:i', strtotime($d)) : ''; };
    $out = array(
        'kk' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/kk/pengajuan?status=pending')),
        'letters' => array('count' => 0, 'latest_id' => 0, 'items' => array(), 'url' => site_url('admin/requests?status=diajukan')),
    );
    if (!isset($CI->feature_gate)) $CI->load->library('Feature_gate');
    if ($CI->feature_gate->for_staff('family_card') && $CI->db->table_exists('kk_scan_requests')) {
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
    if ($CI->feature_gate->for_staff('letters')) {
    $out['letters']['count'] = $CI->db->where('status', 'diajukan')->count_all_results('service_requests');
    $rows = $CI->db->select('s.id,s.subject,s.created_at,c.display_name,c.full_name')->from('service_requests s')
        ->join('citizens c', 'c.id=s.citizen_id')->where('s.status', 'diajukan')->order_by('s.id', 'DESC')->limit(4)->get()->result_array();
    foreach ($rows as $r) {
        $out['letters']['items'][] = array('id' => (int) $r['id'], 'title' => $r['full_name'] ?: $r['display_name'], 'meta' => $r['subject'],
            'time' => $time($r['created_at']), 'url' => site_url('admin/request_detail/' . (int) $r['id']));
    }
    $out['letters']['latest_id'] = (int) ($rows[0]['id'] ?? 0);
    }
    $out['total'] = $out['kk']['count'] + $out['letters']['count'];
    // Changes whenever a new submission arrives (ids only grow), not when one is processed.
    $out['signature'] = 'kk' . $out['kk']['latest_id'] . '-sr' . $out['letters']['latest_id'];
    return $out;
}
