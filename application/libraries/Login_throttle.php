<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SECURITY (SEC-02): brute-force protection for the admin and resident login forms.
 *
 * Reuses the existing `platform_login_attempts` table (id, ip_hash CHAR(64), created_at) so no
 * schema change is needed. Each row stores a SHA-256 key that is namespaced per scope, so these
 * counters never collide with the platform-owner limiter that already uses the same table.
 *
 * Only FAILED attempts are recorded. Limits per 15 minutes:
 *  - per scope+username : MAX_PER_ACCOUNT failures (stops password guessing on one account)
 *  - per scope+client IP : MAX_PER_IP failures       (stops spraying many usernames)
 * A successful login clears the counter of that username only, so logging into an attacker-owned
 * account (residents can self-register) never resets the limit for other accounts.
 * If the table is missing (fresh install before migrations) the limiter is skipped, not fatal.
 */
class Login_throttle
{
    const WINDOW = 900;
    const MAX_PER_ACCOUNT = 8;
    const MAX_PER_IP = 30;
    const MESSAGE = 'Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.';

    private $CI;
    private $ready;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->ready = $this->CI->db->table_exists('platform_login_attempts');
    }

    private function key($scope, $type, $value)
    {
        return hash('sha256', 'login|' . $scope . '|' . $type . '|' . $value);
    }

    private function account_key($scope, $username)
    {
        return $this->key($scope, 'user', mb_strtolower(trim((string) $username)));
    }

    private function ip_key($scope)
    {
        return $this->key($scope, 'ip', (string) $this->CI->input->ip_address());
    }

    private function failures($key)
    {
        return (int) $this->CI->db->where('ip_hash', $key)
            ->where('created_at >', date('Y-m-d H:i:s', time() - self::WINDOW))
            ->count_all_results('platform_login_attempts');
    }

    /** TRUE when this scope/username/IP must wait before trying again. */
    public function blocked($scope, $username)
    {
        if (!$this->ready) return FALSE;
        return $this->failures($this->account_key($scope, $username)) >= self::MAX_PER_ACCOUNT
            || $this->failures($this->ip_key($scope)) >= self::MAX_PER_IP;
    }

    public function fail($scope, $username)
    {
        if (!$this->ready) return;
        $this->CI->db->insert_batch('platform_login_attempts', array(
            array('ip_hash' => $this->account_key($scope, $username)),
            array('ip_hash' => $this->ip_key($scope)),
        ));
        // Keep the table small; rows older than a day are irrelevant to every limiter using it.
        if (mt_rand(1, 50) === 1) {
            $this->CI->db->where('created_at <', date('Y-m-d H:i:s', time() - 86400))->delete('platform_login_attempts');
        }
    }

    public function succeed($scope, $username)
    {
        if (!$this->ready) return;
        $this->CI->db->where('ip_hash', $this->account_key($scope, $username))->delete('platform_login_attempts');
    }

    /** Spend the same time as a real check when the username does not exist (limits user enumeration). */
    public static function dummy_verify($password)
    {
        password_verify((string) $password, '$2y$10$ieRKAj5X3obNRZOEPOg6rOBNywgJr6E2XE8f1S4weWOpJMM1b.OCi');
        return FALSE;
    }
}
