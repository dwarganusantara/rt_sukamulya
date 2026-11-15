<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Read-only aggregates; never return NIK, names, or other residents' requests. */
class Dashboard_summary_model extends CI_Model
{
 public function for_citizen($citizen_id)
 {
  $citizen_id=(int)$citizen_id;
  if($citizen_id<1)throw new InvalidArgumentException('Akun warga diperlukan.');
  $out=array('residents'=>NULL,'heads'=>NULL,'processing'=>NULL);
  if($this->feature_gate->link_ok('warga/keluarga','warga') && $this->db->table_exists('kk_members')){
   $active=$this->db->field_exists('status','kk_members')?" WHERE status='aktif'":'';
   $out['residents']=(int)$this->db->query('SELECT COUNT(*) AS total FROM kk_members'.$active)->row()->total;
   // Verified master members only; moved/deceased heads are excluded.
   $condition=$active?" AND ":" WHERE ";
   $out['heads']=(int)$this->db->query("SELECT COUNT(DISTINCT family_id) AS total FROM kk_members".$active.$condition."JSON_UNQUOTE(JSON_EXTRACT(CASE WHEN JSON_VALID(fields_json) THEN fields_json ELSE '{}' END, '$.hubungan_keluarga'))='Kepala Keluarga'")->row()->total;
  }
  if($this->feature_gate->link_ok('warga/riwayat','warga')){
   $out['processing']=(int)$this->db->where('citizen_id',$citizen_id)->where_in('status',array('diajukan','diproses'))->count_all_results('service_requests');
  }
  return $out;
 }
}
