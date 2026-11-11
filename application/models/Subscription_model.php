<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Commercial entitlement is separate from operational feature switches and RT roles. */
class Subscription_model extends CI_Model
{
    private $catalog_cache = NULL;
    private $decision_cache = array();
    private $ready = FALSE;
    /** Long-running workers must not keep entitlement decisions from an earlier send. */
    public function refresh_entitlements()
    {
        $this->catalog_cache=NULL;
        $this->decision_cache=array();
    }
    public function ensure()
    {
        if ($this->ready) return;
        if ($this->db->table_exists('subscription_plan_details')) { $this->ready=TRUE; return; }
        $sql = file_get_contents(FCPATH.'subscription_upgrade.sql');
        foreach (explode(';', $sql) as $statement) if (trim($statement)) $this->db->query($statement);
        $this->ready=TRUE;
    }

    public function catalog()
    {
        $this->ensure();
        if ($this->catalog_cache===NULL) $this->catalog_cache=array_column($this->db->get('subscription_catalog')->result_array(), NULL, 'feature_code');
        return $this->catalog_cache;
    }

    public function decision($code, $tenant)
    {
        $key=$tenant.':'.$code;
        if (isset($this->decision_cache[$key])) return $this->decision_cache[$key];
        return $this->decision_cache[$key]=$this->evaluate($code,$tenant);
    }

    private function evaluate($code,$tenant)
    {
        $catalog = $this->catalog();
        // Existing catalog entries are FREE until explicitly classified by the platform.
        $feature = $catalog[$code] ?? array('commercial_type'=>'FREE');
        if ($feature['commercial_type']==='DISABLED') return 'FEATURE_DISABLED';
        if ($feature['commercial_type']==='FREE') return 'ALLOW';
        $now = date('Y-m-d H:i:s');
        $override = $this->db->where(array('tenant_id'=>(int)$tenant,'feature_code'=>$code))
            ->where('starts_at <=',$now)->where('ends_at >',$now)->order_by('id','DESC')->get('subscription_overrides')->row_array();
        if ($override) return $override['access_mode']==='GRANT' ? 'ALLOW' : 'SUBSCRIPTION_REQUIRED';
        $sub = $this->current($tenant);
        if (!$sub || !in_array($sub['status'],array('ACTIVE','TRIAL'),TRUE) || $sub['starts_at']>$now || $sub['ends_at']<=$now) return 'SUBSCRIPTION_REQUIRED';
        return $this->db->get_where('subscription_plan_features',array('plan_id'=>$sub['plan_id'],'feature_code'=>$code))->num_rows() ? 'ALLOW' : 'SUBSCRIPTION_REQUIRED';
    }

    public function current($tenant)
    {
        return $this->db->where('tenant_id',(int)$tenant)->order_by('id','DESC')->get('tenant_subscriptions')->row_array();
    }

    public function plans($active = TRUE)
    {
        $this->ensure();
        if ($active) $this->db->where('is_active',1);
        $plans = $this->db->order_by('id','DESC')->get('subscription_plans')->result_array();
        foreach ($plans as &$p) {
            $p['features'] = array_column($this->db->get_where('subscription_plan_features',array('plan_id'=>$p['id']))->result_array(),'feature_code');
            $details=$this->db->get_where('subscription_plan_details',array('plan_id'=>$p['id']))->row_array();
            $p+= $details ?: array('description'=>'','benefits_json'=>'[]','theme'=>'orange','sort_order'=>0,'version'=>0);
            $p['benefits']=json_decode($p['benefits_json'],TRUE)?:array();
            $p['is_basic']=(float)$p['price']===0.0&&(int)$p['duration_days']===0;
        }
        return $plans;
    }

    public function change($action, $actor, $tenant, $before, $after, $write)
    {
        $this->db->trans_begin();
        try {
            $result=$write();
            if(is_array($result))$after=array_merge((array)$after,$result);
            $this->db->insert('subscription_audit_logs',array('actor_id'=>$actor,'tenant_id'=>$tenant,'action'=>$action,'before_json'=>json_encode($before),'after_json'=>json_encode($after)));
            if (!$this->db->trans_status()) throw new RuntimeException('Perubahan gagal disimpan.');
            $this->db->trans_commit();
            $this->catalog_cache=NULL; $this->decision_cache=array();
        } catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
    }
}
