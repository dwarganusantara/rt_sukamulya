<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Langganan extends Ops_Controller
{
    private $management_scope='platform';

    private function management_url($editor=FALSE)
    {
        return $editor?'platform/paket':'platform/langganan';
    }

    private function record_change($action,$actor,$tenant,$before,$after,$write)
    {
        $after=(array)$after;$after['_actor_scope']=$this->management_scope;
        return $this->Subscription_model->change($action,$actor,$tenant,$before,$after,$write);
    }
    private function boot()
    {
        $this->load->model('Subscription_model');
        $this->Subscription_model->ensure();
        $this->load->library('Feature_gate');
        $this->load->library('Platform_owner');
        $this->load->library('Menu_access');
    }

    /** Katalog komersial: semua menu sidebar (kecuali inti) + fitur katalog di luar sidebar. */
    private function commercial_catalog()
    {
        return $this->menu_access->commercial_catalog();
    }

    public function status()
    {
        $this->guard_admin(); $this->boot();
        if ($this->input->method(TRUE)!=='GET') show_error('Metode tidak diizinkan.',405);
        $code=(string)$this->input->get('feature');
        $catalog=$this->feature_gate->catalog();
        $sub=$this->Subscription_model->current($this->feature_gate->tenant());
        $state=!$sub?'NONE':($sub['status']==='SUSPENDED'?'SUSPENDED':($sub['ends_at']<=date('Y-m-d H:i:s')?'EXPIRED':($sub['starts_at']>date('Y-m-d H:i:s')?'FUTURE':$sub['status'])));
        // Mode menu sidebar: parameter berisi path menu (semua menu sidebar melalui proses langganan).
        $items=$this->menu_access->items();
        if (!isset($catalog[$code]) && isset($items[$code])) {
            if (!$this->menu_access->allowed($this->role,$code)) {
                $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array('status'=>'PERMISSION_DENIED','message'=>'Anda tidak memiliki hak akses untuk fitur ini.')));
                return;
            }
            $m=$this->feature_gate->match($code);
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'=>$this->menu_access->commercial($code),'name'=>$items[$code][2],
                'operational'=>$m?$this->feature_gate->operational($m[0],'admin'):TRUE,'license_state'=>$state,
            )));
            return;
        }
        if (!isset($catalog[$code])) show_404();
        // PERMISSION_DENIED dibedakan dari SUBSCRIPTION_REQUIRED: peran tanpa izin tidak ditawari langganan.
        if (!$this->feature_gate->subscription_role_ok($code,$this->role)) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array('status'=>'PERMISSION_DENIED','message'=>'Anda tidak memiliki hak akses untuk fitur ini.')));
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'=>$this->feature_gate->commercial($code),'name'=>$catalog[$code]['name'],
            'operational'=>$this->feature_gate->menu_visible(($catalog[$code]['admin'][0] ?? '')),
            'license_state'=>$state,
        )));
    }

    public function index()
    {
        $this->guard_admin(); $this->boot();
        if ($this->input->method(TRUE)!=='GET') show_error('Halaman langganan tenant bersifat baca saja.',405);
        $tenant=$this->feature_gate->tenant();
        $data=array('plans'=>$this->Subscription_model->plans(),'subscription'=>$this->Subscription_model->current($tenant),'catalog'=>$this->commercial_catalog(),'commercial'=>$this->Subscription_model->catalog());
        $data['site_settings']=$this->Content_model->settings();
        $data['basic_plan']=NULL;
        foreach($data['plans'] as $key=>$plan)if($plan['is_basic']){if(!$data['basic_plan'])$data['basic_plan']=$plan;unset($data['plans'][$key]);}
        $data['plans']=array_values($data['plans']);
        $data['can_manage_features']=$this->can('fitur.manage');
        $data['can_manage_subscriptions']=$this->platform_owner->valid_session();
        $data['package_editor_url']='platform/paket';
        usort($data['plans'],function($a,$b){return (int)$a['sort_order']<=>(int)$b['sort_order'] ?: ((float)$a['price']<=>(float)$b['price'] ?: (int)$a['id']<=>(int)$b['id']);});
        $tab=(string)$this->input->get('tab');
        $data['tab']=in_array($tab,array('paket','fitur','riwayat'),TRUE)?$tab:'paket';
        $data['current_plan']=$data['subscription'] ? $this->db->get_where('subscription_plans',array('id'=>$data['subscription']['plan_id']))->row_array() : NULL;
        $data['history']=$this->db->select('s.*,p.name AS plan_name')->from('tenant_subscriptions s')->join('subscription_plans p','p.id=s.plan_id')->where('s.tenant_id',$tenant)->order_by('s.id','DESC')->get()->result_array();
        // Semua menu sidebar ditampilkan dan melalui proses Gratis / Berlangganan / Nonaktif (urutan sama dengan sidebar).
        $data['feature_rows']=array();
        foreach($this->menu_access->items() as $path=>$it){
            $code=$this->menu_access->commercial_code($path);
            $status=$this->menu_access->commercial($path);
            $m=$this->feature_gate->match($path);
            $operational=$m?$this->feature_gate->operational($m[0],'admin'):TRUE;
            $data['feature_rows'][]=array('code'=>$code?:'core:'.$path,'path'=>$path,'route'=>$path,'name'=>$it[2],'icon'=>$it[1],
                'category'=>$this->menu_access->category_label($it[4]),'type'=>$this->menu_access->commercial_type($path),'status'=>$status,
                'operational'=>$operational,'enabled'=>$status==='ALLOW'&&$operational,'core'=>$code===NULL);
        }
        $this->load->view('admin/header',$data); $this->load->view('subscription/mine',$data); $this->load->view('admin/footer');
    }

    public function login()
    {
        $this->boot(); $error='';
        if ($this->input->method(TRUE)==='POST') {
            $username=substr(trim((string)$this->input->post('username')),0,100);
            $error=$this->platform_owner->sign_in($username,(string)$this->input->post('password'));
            if ($error==='') {
                $return=(string)$this->session->userdata('platform_return_to');$this->session->unset_userdata('platform_return_to');
                redirect(preg_match('#^platform/paket(?:\?edit=\d+)?$#',$return)?$return:'platform/langganan');
            }
        }
        $this->load->view('subscription/platform_login',array('error'=>$error));
    }

    private function platform_guard()
    {
        $this->boot();
        if (!$this->platform_owner->valid_session()) {
            if($this->router->method==='packages')$this->session->set_userdata('platform_return_to','platform/paket'.((int)$this->input->get('edit')?'?edit='.(int)$this->input->get('edit'):''));
            redirect('platform/login');
        }
        $this->output->set_header('Cache-Control: no-store, private');
        $this->management_scope='platform_owner';
        return 0;
    }

    public function logout()
    {
        $this->platform_guard();
        if ($this->input->method(TRUE)!=='POST') show_error('Metode tidak diizinkan.',405);
        $this->session->unset_userdata(array('platform_admin_id','platform_authenticated_at','platform_owner_name','platform_owner_fingerprint'));
        $this->session->sess_regenerate(TRUE); redirect('platform/login');
    }

    public function platform()
    {
        $actor=$this->platform_guard(); $error=''; $notice='';
        $catalog=$this->commercial_catalog(); $tenant=$this->feature_gate->tenant();
        if ($this->input->method(TRUE)==='POST') {
            try {
                $action=(string)$this->input->post('action');
                $code=(string)$this->input->post('feature_code');
                if ($action==='catalog') {
                    $type=(string)$this->input->post('commercial_type');
                    if (!isset($catalog[$code]) || !in_array($type,array('FREE','PREMIUM','DISABLED'),TRUE)) throw new RuntimeException('Fitur atau kategori tidak valid.');
                    $row=array('feature_code'=>$code,'commercial_type'=>$type,'description'=>mb_substr(trim((string)$this->input->post('description')),0,500));
                    $before=$this->db->get_where('subscription_catalog',array('feature_code'=>$code))->row_array();
                    $this->record_change('catalog',$actor,NULL,$before,$row,function()use($row){$this->db->replace('subscription_catalog',$row);});
                } elseif ($action==='plan') {
                    $name=mb_substr(trim((string)$this->input->post('name')),0,120);
                    $days=filter_var($this->input->post('duration_days'),FILTER_VALIDATE_INT);
                    $price=(string)$this->input->post('price');
                    $features=(array)$this->input->post('features');
                    if (!$name || $days===FALSE || $days<0 || $days>3650 || ($days===0&&(float)$price!==0.0) || !preg_match('/^\d{1,12}(\.\d{1,2})?$/',$price) || !$features || array_diff($features,array_keys($catalog))) throw new RuntimeException('Nama, harga, durasi, dan fitur paket wajib valid. Durasi 0 hanya untuk paket Gratis.');
                    $row=array('name'=>$name,'price'=>$price,'duration_days'=>$days,'is_active'=>$this->input->post('is_active')?1:0);
                    $this->record_change('plan_create',$actor,NULL,NULL,$row+array('features'=>$features),function()use($row,$features){
                        $this->db->insert('subscription_plans',$row); $id=$this->db->insert_id();
                        foreach(array_unique($features) as $f) $this->db->insert('subscription_plan_features',array('plan_id'=>$id,'feature_code'=>$f));
                    });
                } elseif ($action==='plan_toggle') {
                    $id=(int)$this->input->post('plan_id'); $before=$this->db->get_where('subscription_plans',array('id'=>$id))->row_array();
                    if (!$before) throw new RuntimeException('Paket tidak ditemukan.');
                    $active=$before['is_active']?0:1;
                    $this->record_change('plan_toggle',$actor,NULL,$before,array('is_active'=>$active),function()use($id,$active){$this->db->where('id',$id)->update('subscription_plans',array('is_active'=>$active));});
                } elseif (in_array($action,array('subscription','override'),TRUE)) {
                    // Single-RT installation: ignore no tenant input; reject forged scope explicitly.
                    if ((int)$this->input->post('tenant_id')!==$tenant) throw new RuntimeException('Tenant tidak sesuai instalasi.');
                    $start=$this->date_time($this->input->post('starts_at')); $end=$this->date_time($this->input->post('ends_at'));
                    $reason=mb_substr(trim((string)$this->input->post('reason')),0,500);
                    if (!$start || !$end || $end<=$start || !$reason) throw new RuntimeException('Tanggal mulai/akhir dan alasan wajib valid.');
                    $row=array('tenant_id'=>$tenant,'starts_at'=>$start,'ends_at'=>$end,'reason'=>$reason,'created_by'=>$actor);
                    if ($action==='subscription') {
                        $plan=(int)$this->input->post('plan_id'); $status=(string)$this->input->post('status');
                        $offer=$this->db->get_where('subscription_plans',array('id'=>$plan,'is_active'=>1))->row_array();
                        if (!$offer || ((float)$offer['price']===0.0&&(int)$offer['duration_days']===0) || !in_array($status,array('ACTIVE','TRIAL','EXPIRED','SUSPENDED'),TRUE)) throw new RuntimeException('Pilih paket berjangka aktif dan status. Paket Gratis tanpa batas waktu tersedia otomatis.');
                        $row+=array('plan_id'=>$plan,'status'=>$status); $table='tenant_subscriptions'; $before=$this->Subscription_model->current($tenant);
                    } else {
                        $mode=(string)$this->input->post('access_mode');
                        if (!isset($catalog[$code]) || !in_array($mode,array('GRANT','DENY'),TRUE)) throw new RuntimeException('Fitur/akses override tidak valid.');
                        $row+=array('feature_code'=>$code,'access_mode'=>$mode); $table='subscription_overrides'; $before=NULL;
                    }
                    $this->record_change($action,$actor,$tenant,$before,$row,function()use($table,$row){$this->db->insert($table,$row);});
                } else throw new RuntimeException('Aksi tidak dikenal.');
                $this->session->set_flashdata('subscription_notice','Perubahan tersimpan dan tercatat pada audit.');
                redirect($this->management_url());
            } catch (RuntimeException $e) { $error=$e->getMessage(); }
        }
        $this->load->view('subscription/platform',array('is_local'=>FALSE,'editor_route'=>$this->management_url(TRUE),'manager_route'=>$this->management_url(),'error'=>$error,'notice'=>$this->session->flashdata('subscription_notice'),'tenant'=>$tenant,'catalog'=>$catalog,'commercial'=>$this->Subscription_model->catalog(),'plans'=>$this->Subscription_model->plans(FALSE),'current'=>$this->Subscription_model->current($tenant),'overrides'=>$this->db->where('tenant_id',$tenant)->order_by('id','DESC')->limit(50)->get('subscription_overrides')->result_array(),'audit'=>$this->db->order_by('id','DESC')->limit(100)->get('subscription_audit_logs')->result_array(),'settings'=>$this->Content_model->settings()));
    }

    private function date_time($value)
    {
        $value=(string)$value;
        $d=DateTime::createFromFormat('!Y-m-d\TH:i',$value);
        return $d && $d->format('Y-m-d\TH:i')===$value ? $d->format('Y-m-d H:i:s') : NULL;
    }

    /** Editable offers, isolated from RT administrators and existing licensed revisions. */
    public function packages()
    {
        $actor=$this->platform_guard();$error='';$catalog=$this->commercial_catalog();
        $edit=(int)$this->input->get('edit');
        if($this->input->method(TRUE)==='POST'){
            try{
                if($this->input->post('action')==='presentation'){
                    $names=array('subscription_basic_name'=>120,'subscription_page_title'=>150,'subscription_page_subtitle'=>300);
                    $values=array();$before=$this->Content_model->settings();
                    foreach($names as $key=>$max){$value=trim((string)$this->input->post($key));if($value===''||mb_strlen($value)>$max)throw new RuntimeException('Judul dan nama paket Gratis wajib diisi sesuai batas karakter.');$values[$key]=$value;}
                    $old=array_intersect_key($before,$values);
                    $this->record_change('package_presentation',$actor,NULL,$old,$values,function()use($values){
                        foreach($values as $key=>$value)$this->db->replace('settings',array('setting_key'=>$key,'setting_value'=>$value));
                        $basic=$this->db->get_where('subscription_plans',array('price'=>0,'duration_days'=>0,'is_active'=>1),1)->row_array();
                        if($basic){$this->db->where('id',$basic['id'])->update('subscription_plans',array('name'=>$values['subscription_basic_name']));$this->db->set('version','version+1',FALSE)->where('plan_id',$basic['id'])->update('subscription_plan_details');}
                    });
                }else{
                    $edit=(int)$this->input->post('plan_id');$version=(int)$this->input->post('version');
                    $name=trim((string)$this->input->post('name'));$price=(string)$this->input->post('price');$days=filter_var($this->input->post('duration_days'),FILTER_VALIDATE_INT);
                    $description=trim((string)$this->input->post('description'));$theme=(string)$this->input->post('theme');$order=filter_var($this->input->post('sort_order'),FILTER_VALIDATE_INT);
                    $features=$this->input->post('features')?:array();$benefits=preg_split('/\R/u',trim((string)$this->input->post('benefits')),-1,PREG_SPLIT_NO_EMPTY);$benefits=array_values(array_map('trim',$benefits));
                    if(!$name||mb_strlen($name)>120||!preg_match('/^\d{1,12}(\.\d{1,2})?$/',$price)||$days===FALSE||$days<0||$days>3650||($days===0&&(float)$price!==0.0)||mb_strlen($description)>300||!in_array($theme,array('orange','violet','green'),TRUE)||$order===FALSE||abs($order)>10000||!is_array($features)||!$features||array_diff($features,array_keys($catalog))||count($benefits)>12)throw new RuntimeException('Periksa nama, harga, durasi, urutan, warna, dan fitur. Durasi 0 hanya untuk paket Gratis.');
                    foreach($benefits as $text)if(mb_strlen($text)>160)throw new RuntimeException('Setiap manfaat maksimal 160 karakter, maksimal 12 baris.');
                    $all=$this->Subscription_model->plans(FALSE);$before=NULL;foreach($all as $plan)if((int)$plan['id']===$edit)$before=$plan;
                    if($edit&&!$before)throw new RuntimeException('Paket tidak ditemukan.');
                    $row=array('name'=>$name,'price'=>$price,'duration_days'=>$days,'is_active'=>$this->input->post('is_active')?1:0);
                    $details=array('description'=>$description,'benefits_json'=>json_encode($benefits,JSON_UNESCAPED_UNICODE),'theme'=>$theme,'sort_order'=>$order,'version'=>$version+1);
                    $this->record_change($edit?'plan_edit':'plan_create',$actor,NULL,$before,$row+$details+array('features'=>$features,'previous_plan_id'=>$edit),function()use($edit,$version,$row,$details,$features){
                        $id=$edit;
                        if($edit){
                            $this->db->query('SELECT id FROM subscription_plans WHERE id=? FOR UPDATE',array($edit));
                            $current=$this->db->get_where('subscription_plan_details',array('plan_id'=>$edit))->row_array();
                            if((int)($current['version']??0)!==$version)throw new RuntimeException('Paket sudah diubah. Muat ulang sebelum menyimpan.');
                            if($this->db->get_where('tenant_subscriptions',array('plan_id'=>$edit))->num_rows()){
                                $this->db->where('id',$edit)->update('subscription_plans',array('is_active'=>0));$id=0;
                            }
                        }
                        if(!$id){$this->db->insert('subscription_plans',$row);$id=$this->db->insert_id();}else{$this->db->where('id',$id)->update('subscription_plans',$row);}
                        $this->db->where('plan_id',$id)->delete('subscription_plan_features');foreach(array_unique($features) as $code)$this->db->insert('subscription_plan_features',array('plan_id'=>$id,'feature_code'=>$code));
                        $this->db->replace('subscription_plan_details',$details+array('plan_id'=>$id));
                        if((float)$row['price']===0.0&&(int)$row['duration_days']===0)$this->db->replace('settings',array('setting_key'=>'subscription_basic_name','setting_value'=>$row['name']));
                        return array('plan_id'=>(int)$id);
                    });
                }
                $this->session->set_flashdata('subscription_notice','Detail paket tersimpan. Paket yang sudah dipakai lisensi disimpan sebagai revisi baru.');redirect($this->management_url(TRUE));
            }catch(RuntimeException $e){$error=$e->getMessage();}
        }
        $plans=$this->Subscription_model->plans(FALSE);$selected=NULL;foreach($plans as $p)if((int)$p['id']===$edit)$selected=$p;
        $this->load->view('subscription/plan_editor',array('is_local'=>FALSE,'editor_route'=>$this->management_url(TRUE),'manager_route'=>$this->management_url(),'plans'=>$plans,'selected'=>$selected,'error'=>$error,'notice'=>$this->session->flashdata('subscription_notice'),'catalog'=>$catalog,'settings'=>$this->Content_model->settings(),'posted'=>$error?$this->input->post():array()));
    }

    public function category_update()
    {
        $actor=$this->platform_guard();
        if($this->input->method(TRUE)!=='POST')show_error('Metode tidak diizinkan.',405);
        // Super Admin bebas mengubah status akses setiap fitur sidebar (kecuali menu inti yang tidak ada di katalog komersial).
        $code=$this->input->post('feature_code');$type=$this->input->post('commercial_type');$catalog=$this->commercial_catalog();
        if(!is_string($code)||!isset($catalog[$code])||!in_array($type,array('FREE','PREMIUM','DISABLED'),TRUE))show_error('Pilihan fitur atau kategori tidak valid.',422);
        $before=$this->db->get_where('subscription_catalog',array('feature_code'=>$code))->row_array();
        $row=array('feature_code'=>$code,'commercial_type'=>$type,'description'=>$before['description']??'');
        $this->record_change('catalog',$actor,$this->feature_gate->tenant(),$before,$row,function()use($row){$this->db->replace('subscription_catalog',$row);});
        $this->session->set_flashdata('notice','Status akses '.$catalog[$code]['name'].' diubah menjadi '.array('FREE'=>'Aktif (Gratis)','PREMIUM'=>'Berlangganan','DISABLED'=>'Nonaktif')[$type].'.');
        // Kembali ke halaman asal (Paket & Langganan di panel admin) bila dikirim dari sana.
        $back=(string)$this->input->post('return_tab');
        if(in_array($back,array('paket','fitur'),TRUE))redirect('admin/langganan?tab='.$back.'#sp-row-'.rawurlencode(str_replace(array(':','/'),'-',$code)));
        redirect('platform/langganan');
    }
}
