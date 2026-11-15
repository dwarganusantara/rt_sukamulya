<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catat aksi penting ke activity_logs. Ringkasan disanitasi: deretan 16 digit
 * (NIK/nomor KK) dimasking dan kata kunci rahasia tidak pernah disimpan.
 */
function rt_audit($action, $subject, $details = '', $admin_id = NULL)
{
    $CI =& get_instance();
    $admin_id = $admin_id === NULL ? (int) $CI->session->userdata('admin_id') : (int) $admin_id;
    $row = array('admin_id' => $admin_id, 'action' => mb_substr((string) $action, 0, 80), 'subject' => mb_substr(rt_audit_sanitize($subject), 0, 100));
    static $extended = NULL;
    if ($extended === NULL) $extended = $CI->db->field_exists('ip_address', 'activity_logs');
    if ($extended) {
        $row['ip_address'] = mb_substr((string) $CI->input->ip_address(), 0, 45);
        $row['details'] = $details === '' ? NULL : mb_substr(rt_audit_sanitize($details), 0, 500);
    }
    $CI->db->insert('activity_logs', $row);
}

function rt_audit_sanitize($text)
{
    $text = (string) $text;
    $text = preg_replace('/\b(\d{4})\d{8}(\d{4})\b/', '$1********$2', $text);
    $text = preg_replace('/(password|token|secret|hash)\s*[:=]\s*\S+/i', '$1=[disembunyikan]', $text);
    return $text;
}

/** Masking NIK / nomor KK untuk tampilan daftar. */
function rt_mask_id($value)
{
    $value = (string) $value;
    return strlen($value) >= 8 ? substr($value, 0, 4) . str_repeat('•', max(0, strlen($value) - 8)) . substr($value, -4) : ($value === '' ? '—' : '••••');
}
