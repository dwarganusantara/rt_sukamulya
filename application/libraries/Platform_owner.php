<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Server-owned identity, deliberately absent from admins/roles and account management. */
class Platform_owner
{
    private $CI;
    private $identity;
    private $identities = array();
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('platform_owner',TRUE,TRUE);
        $this->identity=(array)$this->CI->config->item('identity','platform_owner');
        $this->identities[]=$this->identity;
        $this->CI->config->load('central_admin',TRUE,TRUE);
        $central=(array)$this->CI->config->item('identity','central_admin');
        if(!empty($central['enabled']))$this->identities[]=$central;
        $this->select_identity((string)$this->CI->session->userdata('platform_owner_name'));
    }
    public function recognizes($username)
    {
        foreach($this->identities as $identity)if(!empty($identity['enabled']) && hash_equals((string)($identity['username']??''),(string)$username))return TRUE;
        return FALSE;
    }
    private function select_identity($username)
    {
        foreach($this->identities as $identity)if(!empty($identity['enabled']) && hash_equals((string)($identity['username']??''),(string)$username)){$this->identity=$identity;return TRUE;}
        return FALSE;
    }
    public function authenticate($username,$password)
    {
        if(!$this->select_identity($username))return FALSE;
        $hash=(string)($this->identity['password_hash']??'');
        return !empty($this->identity['enabled'])&&hash_equals((string)($this->identity['username']??''),(string)$username)&&$hash!==''&&password_verify((string)$password,$hash);
    }
    public function username(){return (string)($this->identity['username']??'');}
    /** Shared entry point for platform and ordinary login screens; never sets admin_id. */
    public function sign_in($username,$password)
    {
        $ip=hash('sha256',$this->CI->input->ip_address());
        $count=$this->CI->db->where('ip_hash',$ip)->where('created_at >',date('Y-m-d H:i:s',time()-900))->count_all_results('platform_login_attempts');
        if($count>=8)return 'Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.';
        if(!$this->authenticate($username,$password)){
            $this->CI->db->insert('platform_login_attempts',array('ip_hash'=>$ip));
            return 'Username atau password salah.';
        }
        $this->CI->session->sess_regenerate(TRUE);
        $this->CI->session->unset_userdata(array('platform_admin_id','admin_id','admin_role'));
        $this->CI->session->set_userdata(array('platform_owner_name'=>$this->username(),'platform_owner_fingerprint'=>$this->fingerprint(),'platform_authenticated_at'=>time()));
        $this->CI->db->where('ip_hash',$ip)->delete('platform_login_attempts');
        return '';
    }
    public function fingerprint(){return hash('sha256',$this->username().'|'.($this->identity['password_hash']??''));}
    public function valid_session()
    {
        return !empty($this->identity['enabled'])&&$this->CI->session->userdata('platform_owner_name')===$this->username()
            && (int)$this->CI->session->userdata('platform_authenticated_at')>=time()-3600
            && hash_equals($this->fingerprint(),(string)$this->CI->session->userdata('platform_owner_fingerprint'));
    }
}
