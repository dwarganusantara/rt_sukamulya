<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aktivasi akun warga dari undangan admin (warga/aktivasi/{token}).
 *
 * - Token 64 hex, sekali pakai, kedaluwarsa; yang tersimpan hanya SHA-256-nya.
 * - Token saja tidak cukup: warga wajib mencocokkan NIK + tanggal lahir salah satu anggota aktif (≥17 th)
 *   yang tercatat di KK tersebut. NIK/KK saja tidak pernah dipakai sebagai autentikasi.
 * - Maksimal 5 percobaan gagal per token (lalu token dicabut) + pembatasan per IP.
 * - Pesan kesalahan generik: tidak membocorkan apakah NIK terdaftar.
 * - Password dibuat sendiri oleh warga; tidak ada password bawaan.
 */
class Aktivasi extends CI_Controller
{
    const MAX_ATTEMPTS = 5;
    const GENERIC = 'Data yang Anda masukkan tidak cocok dengan data KK pada undangan ini.';

    public function index($token = '')
    {
        $this->output->set_header('Cache-Control: no-store, private')->set_header('Referrer-Policy: no-referrer');
        $this->db->db_debug = FALSE;
        if (!preg_match('/^[a-f0-9]{64}$/', (string) $token) || !$this->db->table_exists('account_invitations')) show_404();
        $hash = hash('sha256', $token);
        $data = array('settings' => $this->Content_model->settings(), 'menu' => $this->Content_model->pages(TRUE),
            'error' => '', 'invalid' => FALSE, 'token' => $token);
        if (!$this->usable($this->invitation($hash))) {
            $data['invalid'] = TRUE;
            $this->load->view('citizen/activate', $data);
            return;
        }
        if ($this->input->method(TRUE) === 'POST') {
            $this->load->library('Login_throttle');
            $ip_key = 'ip:' . $this->input->ip_address();
            if ($this->login_throttle->blocked('aktivasi', $ip_key)) {
                $data['error'] = Login_throttle::MESSAGE;
            } else {
                try {
                    $citizen = $this->activate($hash);
                    $this->login_throttle->succeed('aktivasi', $ip_key);
                    $this->session->sess_regenerate(TRUE);
                    $this->session->set_userdata(array('citizen_id' => $citizen['id'], 'citizen_name' => $citizen['name']));
                    $this->session->set_flashdata('notice', 'Akun berhasil diaktifkan dan sudah tertaut ke data Kartu Keluarga Anda.');
                    redirect('warga');
                } catch (DomainException $e) {
                    // Gagal verifikasi identitas: dihitung sebagai percobaan.
                    $this->login_throttle->fail('aktivasi', $ip_key);
                    $data['error'] = $e->getMessage();
                    if (!$this->usable($this->invitation($hash))) $data['invalid'] = TRUE;
                } catch (RuntimeException $e) {
                    $data['error'] = $e->getMessage();
                }
            }
        }
        $this->load->view('citizen/activate', $data);
    }

    private function invitation($hash)
    {
        return $this->db->select('i.*, f.owner_citizen_id')->from('account_invitations i')->join('kk_families f', 'f.id=i.family_id')
            ->where('i.token_hash', $hash)->get()->row_array();
    }

    private function usable($inv)
    {
        return $inv && $inv['used_at'] === NULL && $inv['revoked_at'] === NULL && strtotime($inv['expires_at']) > time()
            && (int) $inv['attempts'] < self::MAX_ATTEMPTS && $inv['owner_citizen_id'] === NULL;
    }

    /** @return array id, name */
    private function activate($hash)
    {
        $nik = preg_replace('/\D/', '', (string) $this->input->post('nik'));
        $birth = (string) $this->input->post('tanggal_lahir', TRUE);
        $username = trim((string) $this->input->post('username', TRUE));
        $password = (string) $this->input->post('password');
        // Validasi bentuk dulu (tidak dihitung sebagai percobaan).
        if (strlen($nik) !== 16) throw new RuntimeException('NIK harus 16 digit angka.');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) throw new RuntimeException('Tanggal lahir wajib diisi.');
        if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username)) throw new RuntimeException('Username 3–80 karakter: huruf, angka, titik, garis bawah, atau strip.');
        if (strlen($password) < 8) throw new RuntimeException('Password minimal 8 karakter.');
        if ($password !== (string) $this->input->post('password_confirmation')) throw new RuntimeException('Konfirmasi password tidak sama.');
        if (!$this->input->post('consent')) throw new RuntimeException('Centang pernyataan bahwa Anda adalah anggota keluarga yang bersangkutan.');

        $this->db->trans_begin();
        try {
            $inv = $this->db->query('SELECT * FROM account_invitations WHERE token_hash=? FOR UPDATE', array($hash))->row_array();
            $family = $inv ? $this->db->query('SELECT * FROM kk_families WHERE id=? FOR UPDATE', array((int) $inv['family_id']))->row_array() : NULL;
            if (!$inv || !$family || !$this->usable($inv + array('owner_citizen_id' => $family['owner_citizen_id']))) throw new RuntimeException('Tautan aktivasi tidak berlaku lagi. Minta tautan baru ke pengurus RT.');

            $member = NULL;
            foreach ($this->db->get_where('kk_members', array('family_id' => (int) $family['id'], 'nik' => $nik))->result_array() as $m) {
                $f = json_decode($m['fields_json'], TRUE) ?: array();
                $age = !empty($f['tanggal_lahir']) ? (new DateTime($f['tanggal_lahir']))->diff(new DateTime('today'))->y : -1;
                if (($m['status'] ?: 'aktif') === 'aktif' && ($f['tanggal_lahir'] ?? '') === $birth && $age >= 17) $member = $m + array('f' => $f);
            }
            if (!$member) {
                $n = (int) $inv['attempts'] + 1;
                $this->db->where('id', (int) $inv['id'])->update('account_invitations', array('attempts' => $n) + ($n >= self::MAX_ATTEMPTS ? array('revoked_at' => date('Y-m-d H:i:s')) : array()));
                $this->db->trans_commit();
                $this->load->helper('rt_audit');
                rt_audit('account_activate_fail', 'family:' . (int) $family['id'], 'percobaan ' . $n, 0);
                throw new DomainException(self::GENERIC . ($n >= self::MAX_ATTEMPTS ? ' Tautan dinonaktifkan karena terlalu banyak percobaan; minta tautan baru ke pengurus RT.' : ' Sisa percobaan: ' . (self::MAX_ATTEMPTS - $n) . '.'));
            }
            if ($this->db->get_where('citizens', array('username' => $username))->row_array()) throw new RuntimeException('Username sudah digunakan. Silakan pilih username lain.');

            $name = mb_substr(trim((string) ($member['f']['nama_lengkap'] ?? '')), 0, 140) ?: $username;
            $fam = json_decode($family['fields_json'], TRUE) ?: array();
            $this->db->insert('citizens', array('username' => $username, 'display_name' => $name, 'full_name' => $name,
                'gender' => ($member['f']['jenis_kelamin'] ?? '') === 'L' ? 'male' : 'female',
                'address' => mb_substr((string) ($fam['alamat'] ?? ''), 0, 255) ?: NULL,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'is_active' => 1));
            $cid = (int) $this->db->insert_id();
            $now = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $family['id'])->update('kk_families', array('owner_citizen_id' => $cid));
            $this->db->where('id', (int) $inv['id'])->update('account_invitations', array('used_at' => $now, 'citizen_id' => $cid, 'member_id' => (int) $member['id']));
            $this->db->where(array('family_id' => (int) $family['id'], 'used_at' => NULL, 'revoked_at' => NULL))->update('account_invitations', array('revoked_at' => $now));
            // Akun ini terbukti milik anggota tersebut (NIK + tanggal lahir cocok). Satu anggota hanya boleh punya satu akun.
            if ($this->db->table_exists('citizen_resident_links')) {
                $debug = $this->db->db_debug; $this->db->db_debug = FALSE;
                $linked = $this->db->insert('citizen_resident_links', array('citizen_id' => $cid, 'member_id' => (int) $member['id'], 'verified_at' => $now, 'verification_source' => 'aktivasi_undangan'));
                $err = $this->db->error(); $this->db->db_debug = $debug;
                if (!$linked) throw new RuntimeException((int) $err['code'] === 1062 ? 'Anggota keluarga ini sudah memiliki akun. Hubungi pengurus RT bila lupa username.' : 'Akun belum berhasil dibuat. Silakan coba lagi.');
            }
            if (!$this->db->trans_status() || !$cid) throw new RuntimeException('Akun belum berhasil dibuat. Silakan coba lagi.');
            $this->db->trans_commit();
        } catch (DomainException $e) {
            throw $e; // percobaan gagal sudah di-commit di atas
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            throw $e instanceof RuntimeException ? $e : new RuntimeException('Akun belum berhasil dibuat. Silakan coba lagi.');
        }
        $this->load->helper('rt_audit');
        rt_audit('account_activate', 'family:' . (int) $family['id'], 'warga#' . $cid . ' anggota#' . (int) $member['id'], 0);
        return array('id' => $cid, 'name' => $name);
    }
}
