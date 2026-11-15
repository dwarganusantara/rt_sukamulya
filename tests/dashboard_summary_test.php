<?php
if(PHP_SAPI!=='cli')exit;
define('BASEPATH',__DIR__);
class CI_Model{}
require __DIR__.'/../application/models/Dashboard_summary_model.php';
class SummaryGate{public $allowed=TRUE;function link_ok($route,$role){return $this->allowed;}}
class SummaryDb{
 public $records=array(array('citizen_id'=>1,'status'=>'diajukan'),array('citizen_id'=>1,'status'=>'diproses'),array('citizen_id'=>1,'status'=>'disetujui'),array('citizen_id'=>1,'status'=>'draft'),array('citizen_id'=>2,'status'=>'diproses'));
 private $filters=array();function table_exists($t){return FALSE;}function where($k,$v){$this->filters[$k]=array($v);return $this;}function where_in($k,$v){$this->filters[$k]=$v;return $this;}function count_all_results($t){$rows=$this->records;foreach($this->filters as $k=>$v)$rows=array_filter($rows,function($r)use($k,$v){return in_array($r[$k],$v,TRUE);});$this->filters=array();return count($rows);}
}
function summary_check($condition,$label){if(!$condition)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
$m=new Dashboard_summary_model();$m->db=new SummaryDb();$m->feature_gate=new SummaryGate();
$r=$m->for_citizen(1);summary_check($r['processing']===2,'Only own submitted/processing letters counted; drafts, completed, and other residents excluded');
summary_check($m->for_citizen(2)['processing']===1,'Different citizen receives their own count');
summary_check($r['residents']===NULL&&$r['heads']===NULL,'Missing verified member module is unavailable, not a fabricated zero');
$m->feature_gate->allowed=FALSE;$r=$m->for_citizen(1);summary_check($r['processing']===NULL&&$r['residents']===NULL,'Disabled features do not expose aggregates');
try{$m->for_citizen(0);throw new RuntimeException('Guest accepted');}catch(InvalidArgumentException $e){echo 'PASS Guest cannot request summary'.PHP_EOL;}
