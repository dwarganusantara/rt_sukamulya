<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Antrean notifikasi email (outbox) yang aman di-retry.
 * - enqueue(): dipanggil SETELAH transaksi berhasil; INSERT IGNORE dengan dedupe_key unik,
 *   sehingga event yang sama tidak menghasilkan dua email walau aksi diulang.
 * - process(): kirim via SMTP; gagal → dicoba lagi dengan jeda bertambah, maksimal max_attempts.
 * Isi email tidak memuat NIK/nomor KK atau data sensitif lain; hanya status layanan + tautan portal.
 */
class Notifier
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Ops_model');
        $this->CI->Ops_model->ensure();
        $this->CI->config->load('notifications', TRUE, TRUE);
    }

    private $db_cfg = NULL;

    /**
     * Konfigurasi SMTP: environment/config server lebih diutamakan; bila kosong memakai pengaturan dari
     * panel Antrean Email (tabel settings, password terenkripsi).
     */
    public function cfg($key)
    {
        $file = $this->CI->config->item($key, 'notifications');
        if (in_array($key, array('max_attempts'), TRUE)) return $file;
        if ($this->source() === 'server') return $file;
        if ($this->db_cfg === NULL) {
            $this->db_cfg = array();
            foreach ($this->CI->db->like('setting_key', 'smtp_', 'after')->get('settings')->result_array() as $r) $this->db_cfg[$r['setting_key']] = $r['setting_value'];
        }
        $map = array('smtp_host' => 'smtp_host', 'smtp_port' => 'smtp_port', 'smtp_user' => 'smtp_user', 'smtp_pass' => 'smtp_pass_enc', 'smtp_crypto' => 'smtp_crypto', 'from_email' => 'smtp_from_email', 'from_name' => 'smtp_from_name');
        $v = (string) ($this->db_cfg[$map[$key] ?? ''] ?? '');
        if ($key === 'smtp_pass' && $v !== '') {$this->CI->load->library('encryption'); $v = (string) $this->CI->encryption->decrypt($v);}
        if ($key === 'smtp_port') return (int) ($v ?: 587);
        if ($key === 'smtp_crypto') return in_array($v, array('tls', 'ssl', ''), TRUE) ? $v : 'tls';
        if ($key === 'from_name' && $v === '') return $file;
        return $v;
    }

    /** 'server' bila SMTP diatur lewat environment/config server, 'panel' bila lewat Antrean Email. */
    public function source()
    {
        return (string) $this->CI->config->item('smtp_host', 'notifications') !== '' ? 'server' : 'panel';
    }

    public function ready()
    {
        return $this->cfg('smtp_host') !== '' && $this->cfg('from_email') !== '';
    }

    /** Simpan konfigurasi SMTP dari panel. Password kosong = tidak diubah. */
    public function save_panel_config(array $c)
    {
        $rows = array('smtp_host' => $c['host'], 'smtp_port' => (string) (int) $c['port'], 'smtp_user' => $c['user'], 'smtp_crypto' => $c['crypto'], 'smtp_from_email' => $c['from_email'], 'smtp_from_name' => $c['from_name']);
        if ($c['pass'] !== '') {$this->CI->load->library('encryption'); $rows['smtp_pass_enc'] = $this->CI->encryption->encrypt($c['pass']);}
        if (!empty($c['clear_pass'])) $rows['smtp_pass_enc'] = '';
        foreach ($rows as $k => $v) $this->CI->db->replace('settings', array('setting_key' => $k, 'setting_value' => $v));
        $this->db_cfg = NULL;
    }

    /** Kirim satu email langsung. @return array(bool ok, string error) */
    public function send_one($to, $subject, $body)
    {
        if (!$this->ready()) return array(FALSE, 'SMTP belum dikonfigurasi.');
        $this->CI->load->library('email');
        $this->CI->email->clear();
        $this->CI->email->initialize(array('protocol' => 'smtp', 'smtp_host' => $this->cfg('smtp_host'), 'smtp_port' => $this->cfg('smtp_port'), 'smtp_user' => $this->cfg('smtp_user'),
            'smtp_pass' => $this->cfg('smtp_pass'), 'smtp_crypto' => $this->cfg('smtp_crypto'), 'smtp_timeout' => 15, 'mailtype' => 'text', 'charset' => 'utf-8', 'newline' => "\r\n", 'crlf' => "\r\n"));
        $this->CI->email->from($this->cfg('from_email'), $this->cfg('from_name'));
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($body);
        if (@$this->CI->email->send(FALSE)) return array(TRUE, '');
        // Pesan error disanitasi: jangan simpan kredensial SMTP.
        $err = mb_substr(trim(strip_tags((string) $this->CI->email->print_debugger(array('headers')))), 0, 300);
        return array(FALSE, preg_replace('/(AUTH|PASS|password)\S*\s+\S+/i', '$1 [disembunyikan]', $err) ?: 'Gagal mengirim');
    }

    /** Warga aktif yang mengisi email valid dan menyetujui notifikasi email. */
    public function consenting_citizens()
    {
        $rows = $this->CI->db->select('id,email')->where(array('is_active' => 1, 'notify_email' => 1))->where("email IS NOT NULL AND email <> ''", NULL, FALSE)->get('citizens')->result_array();
        return array_values(array_filter($rows, function ($r) {return (bool) filter_var($r['email'], FILTER_VALIDATE_EMAIL);}));
    }

    /** Antrekan notifikasi ke semua warga yang menyetujui. @return int jumlah email masuk antrean */
    public function enqueue_broadcast($subject, $body, $batch)
    {
        $n = 0;
        foreach ($this->consenting_citizens() as $c) if ($this->enqueue_citizen($c['id'], $subject, $body, 'broadcast:' . $batch . ':' . $c['id'])) $n++;
        return $n;
    }

    public function enabled()
    {
        $row = $this->CI->db->get_where('settings', array('setting_key' => 'notify_email'))->row_array();
        return $row && $row['setting_value'] === '1';
    }

    /** @return bool TRUE bila masuk antrean (bukan berarti sudah terkirim). */
    public function enqueue_citizen($citizen_id, $subject, $body, $dedupe_key)
    {
        if (!$citizen_id || !$this->enabled() || !$this->CI->db->table_exists('notification_outbox')) return FALSE;
        $c = $this->CI->db->get_where('citizens', array('id' => (int) $citizen_id, 'is_active' => 1))->row_array();
        if (!$c || empty($c['email']) || !filter_var($c['email'], FILTER_VALIDATE_EMAIL) || empty($c['notify_email'])) return FALSE;
        $body = trim($body) . "\n\nBuka portal warga: " . site_url('warga') . "\n\nPesan otomatis. Anda menerima email ini karena mengaktifkan notifikasi email di profil. Matikan kapan saja di Edit Profil.";
        $this->CI->db->query('INSERT IGNORE INTO notification_outbox (citizen_id, channel, recipient, subject, body, dedupe_key, status, created_at) VALUES (?,?,?,?,?,?,?,NOW())',
            array((int) $citizen_id, 'email', $c['email'], mb_substr($subject, 0, 190), $body, mb_substr($dedupe_key, 0, 120), 'pending'));
        return $this->CI->db->affected_rows() > 0;
    }

    /** Kirim antrean yang jatuh tempo. @return array [sent, failed, waiting(smtp belum siap)] */
    public function process($limit = 20)
    {
        $stat = array('sent' => 0, 'failed' => 0, 'waiting' => 0, 'blocked' => 0);
        if (!$this->CI->db->table_exists('notification_outbox')) return $stat;
        $rows = $this->CI->db->where('status', 'pending')->group_start()->where('next_attempt_at IS NULL', NULL, FALSE)->or_where('next_attempt_at <=', date('Y-m-d H:i:s'))->group_end()
            ->order_by('id')->limit((int) $limit)->get('notification_outbox')->result_array();
        if (!$rows) return $stat;
        $this->CI->load->library('Feature_gate');
        $prefixes=array('kk'=>'family_card','mutation'=>'resident_mutation','complaint'=>'complaints','reminder'=>'dues_arrears');
        $eligible=array();
        foreach($rows as $row){
            $code=$prefixes[strtok($row['dedupe_key'],':')]??NULL;
            if($code && $this->CI->feature_gate->commercial($code)!=='ALLOW'){
                $this->CI->db->where(array('id'=>$row['id'],'status'=>'pending'))->update('notification_outbox',array('last_error'=>'Ditunda: lisensi fitur belum aktif.','next_attempt_at'=>date('Y-m-d H:i:s',time()+900)));
                $stat['blocked']++; continue;
            }
            $eligible[]=$row;
        }
        $rows=$eligible;
        if(!$rows)return $stat;
        if (!$this->ready()) {$stat['waiting'] = count($rows); return $stat;}
        $max = (int) $this->cfg('max_attempts') ?: 5;
        foreach ($rows as $r) {
            $code=$prefixes[strtok($r['dedupe_key'],':')]??NULL;
            if($code){
                $this->CI->load->model('Subscription_model');
                $this->CI->Subscription_model->refresh_entitlements();
                if($this->CI->feature_gate->commercial($code)!=='ALLOW'){
                    $this->CI->db->where(array('id'=>$r['id'],'status'=>'pending'))->update('notification_outbox',array('last_error'=>'Ditunda: lisensi fitur belum aktif.','next_attempt_at'=>date('Y-m-d H:i:s',time()+900)));
                    $stat['blocked']++;continue;
                }
            }
            // Klaim baris agar dua proses paralel tidak mengirim email yang sama.
            $this->CI->db->where('id', $r['id'])->where('status', 'pending')->update('notification_outbox', array('status' => 'sending'));
            if ($this->CI->db->affected_rows() !== 1) continue;
            list($ok, $err) = $this->send_one($r['recipient'], $r['subject'], $r['body']);
            $attempts = (int) $r['attempts'] + 1;
            if ($ok) {
                $this->CI->db->where('id', $r['id'])->update('notification_outbox', array('status' => 'sent', 'attempts' => $attempts, 'sent_at' => date('Y-m-d H:i:s'), 'last_error' => NULL));
                $stat['sent']++;
            } else {
                $final = $attempts >= $max;
                $this->CI->db->where('id', $r['id'])->update('notification_outbox', array('status' => $final ? 'failed' : 'pending', 'attempts' => $attempts, 'last_error' => $err ?: 'Gagal mengirim',
                    'next_attempt_at' => $final ? NULL : date('Y-m-d H:i:s', time() + 60 * (2 ** $attempts))));
                $stat['failed']++;
            }
        }
        return $stat;
    }
}
