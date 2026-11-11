<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
    public function index()
    {
        $data = array('settings' => $this->Content_model->settings(),
            'menu' => $this->Content_model->pages(TRUE),
            'announcements' => $this->db->where('type', 'pengumuman')->where('status', 'published')->order_by('created_at', 'DESC')->limit(3)->get('posts')->result_array(),
            'events' => $this->db->where('type', 'agenda')->where('status', 'published')->where('event_date >=', date('Y-m-d'))->order_by('event_date')->limit(3)->get('posts')->result_array(),
            'quick_links' => $this->db->where('is_active', 1)->order_by('sort_order')->order_by('id')->get('quick_links')->result_array(),
            'balance' => $this->db->select("COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE -amount END),0) AS balance", FALSE)->where('status', 'verified')->get('transactions')->row()->balance);
        $this->load->view('site/home', $data);
    }

    public function page($slug = '')
    {
        $page = $this->Content_model->published_page($slug);
        if (!$page) show_404();
        $this->load->view('site/page', array('page' => $page,
            'settings' => $this->Content_model->settings(),
            'menu' => $this->Content_model->pages(TRUE)));
    }

    private function shared()
    {
        return array('settings' => $this->Content_model->settings(), 'menu' => $this->Content_model->pages(TRUE));
    }

    public function listing($type = '')
    {
        if (!in_array($type, array('pengumuman', 'agenda', 'galeri'), TRUE)) show_404();
        $data = $this->shared();
        $data['type'] = $type;
        if ($type === 'pengumuman') {
            $data['query'] = mb_substr(trim((string) $this->input->get('q', TRUE)), 0, 80);
            $data['category'] = strtolower((string) $this->input->get('kategori', TRUE));
            if (!in_array($data['category'], array('','kegiatan','informasi','iuran','lainnya'), TRUE)) $data['category'] = '';
            $this->db->where('type', $type)->where('status', 'published');
            if ($data['query'] !== '') {
                $this->db->group_start()->like('title', $data['query'])->or_like('body', $data['query'])->group_end();
            }
            $items = $this->db->order_by('created_at', 'DESC')->get('posts')->result_array();
            foreach ($items as &$item) $item['category'] = $this->announcement_category($item);
            unset($item);
            if ($data['category'] !== '') $items = array_values(array_filter($items, function($item) use ($data) { return $item['category'] === $data['category']; }));
            $data['items'] = $items;
            $this->load->view('site/announcements', $data);
            return;
        }
        if ($type === 'agenda') {
            $data['query'] = mb_substr(trim((string) $this->input->get('q', TRUE)), 0, 80);
            $data['category'] = strtolower((string) $this->input->get('kategori', TRUE));
            if (!in_array($data['category'], array('','kegiatan','kerja-bakti','lomba','sosial','lainnya'), TRUE)) $data['category'] = '';
            $this->db->where('type','agenda')->where('status','published');
            if ($data['query'] !== '') $this->db->group_start()->like('title',$data['query'])->or_like('body',$data['query'])->or_like('event_location',$data['query'])->group_end();
            $items=$this->db->order_by('event_date','DESC')->order_by('event_time','ASC')->get('posts')->result_array();
            foreach($items as &$item) $item['category']=$this->agenda_category($item);
            unset($item);
            if ($data['category'] !== '') $items=array_values(array_filter($items,function($item)use($data){return $item['category']===$data['category'];}));
            $data['items']=$items;
            $this->load->view('site/agendas',$data);
            return;
        }
        if ($type === 'galeri') {
            $data['query']=mb_substr(trim((string)$this->input->get('q',TRUE)),0,80);
            $data['category']=strtolower((string)$this->input->get('kategori',TRUE));
            if(!in_array($data['category'],array('','kerja-bakti','lomba','sosial','keagamaan','rutin','lainnya'),TRUE)) $data['category']='';
            $this->db->where('type','galeri')->where('status','published')->where('image IS NOT NULL',NULL,FALSE);
            if($data['query']!=='') $this->db->group_start()->like('title',$data['query'])->or_like('body',$data['query'])->group_end();
            $items=$this->db->order_by('created_at','DESC')->get('posts')->result_array();
            foreach($items as &$item) $item['category']=$this->gallery_category($item);
            unset($item);
            if($data['category']!=='') $items=array_values(array_filter($items,function($item)use($data){return $item['category']===$data['category'];}));
            $data['items']=$items;
            $this->load->view('site/gallery',$data);
            return;
        }
        $data['items'] = $this->db->where('type', $type)->where('status', 'published')
            ->order_by($type === 'agenda' ? 'event_date' : 'created_at', $type === 'agenda' ? 'ASC' : 'DESC')->get('posts')->result_array();
        $this->load->view('site/listing', $data);
    }

    private function announcement_category($item)
    {
        $text = mb_strtolower(($item['title'] ?? '').' '.($item['body'] ?? ''));
        if (preg_match('/iuran|pembayaran|tagihan|kas\b|keuangan/u', $text)) return 'iuran';
        if (preg_match('/kegiatan|lomba|rapat|kerja bakti|posyandu|acara|undangan/u', $text)) return 'kegiatan';
        if (preg_match('/informasi|jadwal|pemeliharaan|gangguan|layanan|pemberitahuan/u', $text)) return 'informasi';
        return 'lainnya';
    }

    private function agenda_category($item)
    {
        $text=mb_strtolower(($item['title']??'').' '.($item['body']??''));
        if (preg_match('/kerja bakti|gotong royong|bersih lingkungan/u',$text)) return 'kerja-bakti';
        if (preg_match('/lomba|turnamen|kompetisi|pertandingan/u',$text)) return 'lomba';
        if (preg_match('/posyandu|sosial|santunan|donor|kesehatan/u',$text)) return 'sosial';
        if (preg_match('/rapat|pertemuan|kegiatan|acara|senam/u',$text)) return 'kegiatan';
        return 'lainnya';
    }

    private function gallery_category($item)
    {
        $text=mb_strtolower(($item['title']??'').' '.($item['body']??''));
        if(preg_match('/kerja bakti|gotong royong|bersih lingkungan/u',$text)) return 'kerja-bakti';
        if(preg_match('/lomba|turnamen|kompetisi|pertandingan/u',$text)) return 'lomba';
        if(preg_match('/pengajian|keagamaan|maulid|isra|idul|kajian/u',$text)) return 'keagamaan';
        if(preg_match('/posyandu|sosial|santunan|donor|kesehatan/u',$text)) return 'sosial';
        if(preg_match('/rutin|rapat|senam|pertemuan/u',$text)) return 'rutin';
        return 'lainnya';
    }

    public function announcement($id = 0)
    {
        $item = $this->db->get_where('posts', array('id'=>(int)$id, 'type'=>'pengumuman', 'status'=>'published'))->row_array();
        if (!$item) show_404();
        $data = $this->shared();
        $data['item'] = $item;
        $data['category'] = $this->announcement_category($item);
        $data['related'] = $this->db->where('type','pengumuman')->where('status','published')->where('id !=',(int)$id)->order_by('created_at','DESC')->limit(3)->get('posts')->result_array();
        $this->load->view('site/announcement_detail', $data);
    }

    public function agenda($id = 0)
    {
        $item=$this->db->get_where('posts',array('id'=>(int)$id,'type'=>'agenda','status'=>'published'))->row_array();
        if(!$item) show_404();
        $data=$this->shared();
        $data['item']=$item;
        $data['category']=$this->agenda_category($item);
        $data['related']=$this->db->where('type','agenda')->where('status','published')->where('id !=',(int)$id)->order_by('event_date','DESC')->limit(3)->get('posts')->result_array();
        $this->load->view('site/agenda_detail',$data);
    }

    public function structure()
    {
        $data = $this->shared();
        $data['items'] = $this->db->where('is_active', 1)->order_by('sort_order')->get('officials')->result_array();
        $this->load->view('site/structure', $data);
    }

    public function profile()
    {
        $data = $this->shared();
        $data['items'] = $this->db->where('is_active', 1)->order_by('sort_order')->limit(6)->get('officials')->result_array();
        $this->load->view('site/profile', $data);
    }

    public function information()
    {
        $data = $this->shared();
        $data['pages'] = $this->Content_model->pages(TRUE);
        $this->load->view('site/information', $data);
    }

    public function contact()
    {
        $this->load->view('site/contact', $this->shared());
    }

    public function search()
    {
        $data = $this->shared();
        $query = trim((string) $this->input->get('q', TRUE));
        $data['query'] = mb_substr($query, 0, 80);
        $data['pages'] = array();
        $data['posts'] = array();
        if (mb_strlen($data['query']) >= 2) {
            $data['pages'] = $this->db->where('is_published', 1)->group_start()
                ->like('title', $data['query'])->or_like('body', $data['query'])->group_end()
                ->limit(30)->get('pages')->result_array();
            $data['posts'] = $this->db->where('status', 'published')->group_start()
                ->like('title', $data['query'])->or_like('body', $data['query'])->group_end()
                ->limit(30)->get('posts')->result_array();
            $data['posts']=array_values(array_filter($data['posts'],function($post){$code=array('pengumuman'=>'announcements','agenda'=>'events','galeri'=>'gallery')[$post['type']]??NULL;return $code && $this->feature_gate->for_residents($code);}));
        }
        $this->load->view('site/search', $data);
    }

    public function finance()
    {
        $data = $this->shared();
        $period = (string) $this->input->get('periode', TRUE);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) $period = date('Y-m');
        $data['period'] = $period;
        $data['opening'] = $this->db->select("COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE -amount END),0) AS total", FALSE)
            ->where('status', 'verified')->where('period <', $period)->get('transactions')->row()->total;
        $data['items'] = $this->db->where('status', 'verified')->where('period', $period)
            ->order_by('tx_date')->order_by('id')->get('transactions')->result_array();
        $start = date('Y-m',strtotime($period.'-01 -5 months'));
        $trendRows = $this->db->select("period,SUM(CASE WHEN type='masuk' THEN amount ELSE 0 END) AS income,SUM(CASE WHEN type='keluar' THEN amount ELSE 0 END) AS expense",FALSE)
            ->where('status','verified')->where('period >=',$start)->where('period <=',$period)
            ->group_by('period')->get('transactions')->result_array();
        $trendMap = array();foreach($trendRows as $row)$trendMap[$row['period']]=$row;
        $data['trend'] = array();for($i=5;$i>=0;$i--){$month=date('Y-m',strtotime($period.'-01 -'.$i.' months'));$data['trend'][]=array('period'=>$month,'income'=>(float)($trendMap[$month]['income']??0),'expense'=>(float)($trendMap[$month]['expense']??0));}
        $this->load->view('site/finance', $data);
    }

    public function proof($id = 0)
    {
        $item = $this->db->get_where('transactions', array('id' => (int) $id))->row_array();
        if (!$item || ($item['status'] !== 'verified' && !in_array($this->session->userdata('admin_role'), array('admin','bendahara','ketua'), TRUE))) show_404();
        $path = FCPATH . 'uploads/proofs/' . basename($item['proof']);
        if (!is_file($path)) show_404();
        $mime = mime_content_type($path);
        if (!in_array($mime, array('image/jpeg', 'image/png', 'image/webp', 'application/pdf'), TRUE)) show_404();
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="bukti-' . (int) $id . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }
}
