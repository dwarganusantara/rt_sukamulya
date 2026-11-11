<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 06 — field tambahan profil warga (non-sensitif).
 * Pengurus mendefinisikan field bertipe (teks, angka, tanggal, pilihan, centang); nilai disimpan per akun warga.
 * Label yang mengarah ke data sensitif (NIK, agama, kesehatan, penghasilan, dsb.) ditolak.
 */
class Custom_fields
{
    public static $types = array('text' => 'Teks singkat', 'number' => 'Angka', 'date' => 'Tanggal', 'select' => 'Pilihan', 'checkbox' => 'Centang (ya/tidak)');
    /** Kata kunci data pribadi sensitif / identitas resmi yang tidak boleh dijadikan field tambahan. */
    private $blocked = array('nik', 'ktp', 'kartu keluarga', 'no kk', 'nomor kk', 'agama', 'kepercayaan', 'suku', 'ras', 'etnis', 'penyakit', 'kesehatan', 'medis', 'disabilitas',
        'difabel', 'hamil', 'penghasilan', 'gaji', 'pendapatan', 'rekening', 'kartu kredit', 'password', 'kata sandi', 'pin', 'ibu kandung', 'politik', 'partai', 'orientasi', 'seksual',
        'biometrik', 'sidik jari', 'kriminal', 'catatan polisi', 'npwp', 'bpjs', 'paspor');
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Ops_model');
        $this->CI->Ops_model->ensure();
    }

    public function definitions($active_only = TRUE)
    {
        if (!$this->CI->db->table_exists('custom_field_definitions')) return array();
        if ($active_only) $this->CI->db->where('is_active', 1);
        $rows = $this->CI->db->order_by('sort_order')->order_by('id')->get('custom_field_definitions')->result_array();
        foreach ($rows as &$r) $r['option_list'] = $r['options'] === '' ? array() : array_values(array_filter(array_map('trim', explode("\n", (string) $r['options'])), 'strlen'));
        return $rows;
    }

    public function values($citizen_id)
    {
        if (!$this->CI->db->table_exists('citizen_field_values')) return array();
        $out = array();
        foreach ($this->CI->db->get_where('citizen_field_values', array('citizen_id' => (int) $citizen_id))->result_array() as $r) $out[(int) $r['field_id']] = $r['value'];
        return $out;
    }

    /** '' bila label aman; selain itu pesan error. */
    public function label_error($label)
    {
        $l = ' ' . mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', (string) $label)) . ' ';
        foreach ($this->blocked as $word) if (strpos($l, ' ' . $word . ' ') !== FALSE) return 'Label "' . $label . '" mengarah ke data sensitif (' . $word . '). Field tambahan hanya untuk data non-sensitif.';
        return '';
    }

    /**
     * Validasi input $_POST['cf'] terhadap definisi.
     * @param bool $citizen TRUE = dari portal warga (field yang disembunyikan dari warga diabaikan)
     * @return array [values(field_id => string), error]
     */
    public function collect($citizen = TRUE)
    {
        $input = $this->CI->input->post('cf', TRUE);
        $input = is_array($input) ? $input : array();
        $values = array();
        foreach ($this->definitions() as $d) {
            if ($citizen && !(int) $d['citizen_editable']) continue;
            $id = (int) $d['id'];
            $v = isset($input[$id]) && is_scalar($input[$id]) ? trim((string) $input[$id]) : '';
            $v = mb_substr(preg_replace('/\s+/u', ' ', strip_tags($v)), 0, 255);
            if ($d['field_type'] === 'checkbox') $v = $v !== '' ? '1' : '0';
            elseif ($v !== '' && $d['field_type'] === 'number' && !preg_match('/^-?\d{1,12}([.,]\d{1,4})?$/', $v)) return array(array(), $d['label'] . ' harus berupa angka.');
            elseif ($v !== '' && $d['field_type'] === 'date' && !ops_valid_date_cf($v)) return array(array(), $d['label'] . ' harus berupa tanggal yang valid.');
            elseif ($v !== '' && $d['field_type'] === 'select' && !in_array($v, $d['option_list'], TRUE)) return array(array(), 'Pilihan ' . $d['label'] . ' tidak valid.');
            if ((int) $d['is_required'] && ($v === '' || ($d['field_type'] === 'checkbox' && $v === '0'))) return array(array(), $d['label'] . ' wajib diisi.');
            $values[$id] = $v;
        }
        return array($values, '');
    }

    public function save($citizen_id, $values)
    {
        foreach ($values as $field_id => $value) {
            $this->CI->db->query('INSERT INTO citizen_field_values (citizen_id, field_id, value, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE value=VALUES(value), updated_at=NOW()',
                array((int) $citizen_id, (int) $field_id, (string) $value));
        }
    }

    /**
     * HTML field mengikuti markup form existing.
     * $style: 'pe' (edit profil warga) atau 'acf' (form akun warga admin).
     */
    public function render($values, $style = 'pe', $citizen = TRUE)
    {
        $html = '';
        foreach ($this->definitions() as $d) {
            if ($citizen && !(int) $d['citizen_editable']) continue;
            $id = (int) $d['id'];
            $name = 'cf[' . $id . ']';
            $v = (string) ($values[$id] ?? '');
            $label = html_escape($d['label']) . ((int) $d['is_required'] ? ' <b style="color:#d6453d" aria-hidden="true">*</b>' : ($style === 'acf' ? ' <em>(opsional)</em>' : ''));
            $req = (int) $d['is_required'] ? ' required' : '';
            $help = $d['help_text'] !== '' ? '<small>' . html_escape($d['help_text']) . '</small>' : '';
            if ($d['field_type'] === 'checkbox') {
                $html .= '<label><strong>' . $label . '</strong><span style="display:flex;gap:8px;align-items:center;min-height:42px"><input type="checkbox" name="' . $name . '" value="1"' . ($v === '1' ? ' checked' : '') . $req . ' style="width:18px;height:18px"> Ya</span>' . $help . '</label>';
                continue;
            }
            if ($d['field_type'] === 'select') {
                $control = '<select name="' . $name . '"' . $req . '><option value="">— Pilih —</option>';
                foreach ($d['option_list'] as $o) $control .= '<option value="' . html_escape($o) . '"' . ($o === $v ? ' selected' : '') . '>' . html_escape($o) . '</option>';
                $control .= '</select>';
            } else {
                $type = $d['field_type'] === 'number' ? 'text" inputmode="decimal' : ($d['field_type'] === 'date' ? 'date' : 'text');
                $control = '<input type="' . $type . '" name="' . $name . '" maxlength="255" value="' . html_escape($v) . '"' . $req . '>';
            }
            $html .= '<label><strong>' . $label . '</strong><span class="' . $style . '-control"><i>＋</i>' . $control . '</span>' . $help . '</label>';
        }
        return $html;
    }
}

function ops_valid_date_cf($value)
{
    $d = DateTime::createFromFormat('!Y-m-d', (string) $value);
    return $d && $d->format('Y-m-d') === $value;
}
