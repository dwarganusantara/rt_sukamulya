<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 05 (pelengkap) — aging tunggakan, pengingat in-app, dan rekonsiliasi kas.
 * Tagihan, pembayaran, verifikasi, dan posting kas tetap ditangani menu Iuran/Kas existing.
 */
class Keuangan extends Ops_Controller
{
    private $buckets = array('0_30' => '1–30 hari', '31_60' => '31–60 hari', '61_90' => '61–90 hari', '90_plus' => '> 90 hari');

    /** Jatuh tempo = akhir bulan periode tagihan. */
    private function arrears()
    {
        $rows = $this->db->select('d.*,c.display_name,c.full_name')->from('dues d')->join('citizens c', 'c.id=d.citizen_id')
            ->where('d.amount_paid < d.amount_due', NULL, FALSE)->where('d.period <', date('Y-m'))->order_by('d.period')->get()->result_array();
        $today = new DateTime('today');
        foreach ($rows as &$r) {
            $due = new DateTime(date('Y-m-t', strtotime($r['period'] . '-01')));
            $days = (int) $due->diff($today)->format('%a');
            $r['days'] = $days;
            $r['remaining'] = (float) $r['amount_due'] - (float) $r['amount_paid'];
            $r['bucket'] = $days <= 30 ? '0_30' : ($days <= 60 ? '31_60' : ($days <= 90 ? '61_90' : '90_plus'));
        }
        return $rows;
    }

    public function tunggakan()
    {
        $this->guard_admin('iuran.report');
        $rows = $this->arrears();
        $bucket = $this->choice('umur', $this->buckets, '', 'get');
        if ($this->input->get('export') === 'csv') {
            $this->audit('export', 'arrears', count($rows) . ' baris');
            $safe = function ($v) {$v = (string) $v; return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;};
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="tunggakan-iuran-' . date('Ymd') . '.csv"');
            header('X-Content-Type-Options: nosniff');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array('Warga', 'Periode', 'Kategori', 'Tagihan', 'Dibayar', 'Sisa', 'Hari lewat', 'Umur'), ';');
            foreach ($rows as $r) fputcsv($out, array($safe($r['full_name'] ?: $r['display_name']), $r['period'], $safe($r['category']), (int) $r['amount_due'], (int) $r['amount_paid'], (int) $r['remaining'], $r['days'], $this->buckets[$r['bucket']]), ';');
            fclose($out);
            exit;
        }
        $summary = array_fill_keys(array_keys($this->buckets), array('count' => 0, 'total' => 0));
        $per_citizen = array();
        foreach ($rows as $r) {
            $summary[$r['bucket']]['count']++;
            $summary[$r['bucket']]['total'] += $r['remaining'];
            $c = (int) $r['citizen_id'];
            if (!isset($per_citizen[$c])) $per_citizen[$c] = array('name' => $r['full_name'] ?: $r['display_name'], 'count' => 0, 'total' => 0, 'oldest' => $r['days']);
            $per_citizen[$c]['count']++;
            $per_citizen[$c]['total'] += $r['remaining'];
            $per_citizen[$c]['oldest'] = max($per_citizen[$c]['oldest'], $r['days']);
        }
        uasort($per_citizen, function ($a, $b) {return $b['total'] <=> $a['total'];});
        // Ringkasan per kelompok umur: jumlah warga unik, dan distribusi warga menurut keterlambatan terlamanya.
        $order = array_keys($this->buckets);
        $bucket_citizens = array_fill_keys($order, array()); $worst = array();
        foreach ($rows as $r) {
            $c = (int) $r['citizen_id']; $bucket_citizens[$r['bucket']][$c] = TRUE;
            if (!isset($worst[$c]) || array_search($r['bucket'], $order) > array_search($worst[$c], $order)) $worst[$c] = $r['bucket'];
        }
        $distribution = array_fill_keys($order, 0); foreach ($worst as $b) $distribution[$b]++;
        foreach ($order as $k) $summary[$k]['citizens'] = count($bucket_citizens[$k]);
        $periods = array_values(array_unique(array_column($rows, 'period'))); rsort($periods);
        $period = (string) $this->input->get('periode', TRUE);
        if (!in_array($period, $periods, TRUE)) $period = '';
        if ($bucket) $rows = array_values(array_filter($rows, function ($r) use ($bucket) {return $r['bucket'] === $bucket;}));
        if ($period !== '') $rows = array_values(array_filter($rows, function ($r) use ($period) {return $r['period'] === $period;}));
        $last = array();
        foreach ($this->db->select('due_id,MAX(created_at) at', FALSE)->group_by('due_id')->get('due_reminders')->result_array() as $x) $last[(int) $x['due_id']] = $x['at'];
        $kk = array();
        $cids = array_unique(array_map('intval', array_column($rows, 'citizen_id')));
        if ($cids && $this->db->table_exists('kk_families')) foreach ($this->db->select('owner_citizen_id,nomor_kk')->where_in('owner_citizen_id', $cids)->get('kk_families')->result_array() as $f) $kk[(int) $f['owner_citizen_id']] = $f['nomor_kk'];
        $this->render('tunggakan', array('rows' => $rows, 'summary' => $summary, 'per_citizen' => $per_citizen, 'buckets' => $this->buckets, 'bucket' => $bucket, 'last' => $last,
            'distribution' => $distribution, 'debtors' => count($worst), 'periods' => $periods, 'period' => $period, 'kk' => $kk));
    }

    /** Pengingat manual in-app (muncul sebagai pemberitahuan di portal warga). Maks. 1x per 3 hari per tagihan. */
    public function ingatkan()
    {
        $this->guard_admin('iuran.remind');
        $this->post_only();
        // Tombol "Kirim Pengingat" per baris mengirim 'one'; tombol massal memakai centang due_ids[].
        $one = (int) $this->input->post('one');
        $ids = $one ? array($one) : array_slice(array_map('intval', (array) $this->input->post('due_ids')), 0, 200);
        $sent = 0;
        $skipped = 0;
        foreach ($ids as $id) {
            $due = $this->db->where('id', $id)->where('amount_paid < amount_due', NULL, FALSE)->get('dues')->row_array();
            if (!$due) continue;
            $recent = $this->db->where('due_id', $id)->where('created_at >=', date('Y-m-d H:i:s', strtotime('-3 days')))->count_all_results('due_reminders');
            if ($recent) {$skipped++; continue;}
            $remaining = (float) $due['amount_due'] - (float) $due['amount_paid'];
            $message = 'Tagihan ' . $due['category'] . ' periode ' . $due['period'] . ' masih tersisa ' . ops_money($remaining) . '. Abaikan bila sudah membayar dan menunggu verifikasi.';
            $this->db->insert('due_reminders', array('due_id' => $id, 'citizen_id' => (int) $due['citizen_id'], 'sent_by' => $this->admin_id, 'message' => $message));
            $reminder_id = (int) $this->db->insert_id();
            $this->load->library('Notifier');
            $this->notifier->enqueue_citizen((int) $due['citizen_id'], 'Pengingat iuran ' . $due['period'], $message, 'reminder:' . $reminder_id);
            $sent++;
        }
        $this->audit('remind', 'dues', $sent . ' terkirim, ' . $skipped . ' dilewati');
        $this->back('admin/tunggakan', $sent . ' pengingat dikirim ke portal warga.' . ($skipped ? ' ' . $skipped . ' dilewati (sudah diingatkan < 3 hari).' : ''));
    }

    public function rekonsiliasi()
    {
        $this->guard_admin('kas.report');
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('kas.reconcile');
            $date = $this->date_or_null('recon_date');
            $bank = $this->input->post('bank_balance');
            if (!$date || $date > date('Y-m-d') || !is_numeric($bank)) $error = 'Isi tanggal (tidak di masa depan) dan saldo rekening/kas fisik yang valid.';
            else {
                $system = $this->system_balance($date);
                $this->db->insert('cash_reconciliations', array('recon_date' => $date, 'bank_balance' => (float) $bank, 'system_balance' => $system, 'difference' => (float) $bank - $system,
                    'note' => $this->str('note', 500), 'created_by' => $this->admin_id));
                $this->audit('reconcile', 'cash:' . $date, 'selisih ' . ((float) $bank - $system));
                $this->back('admin/rekonsiliasi', 'Rekonsiliasi dicatat. Selisih: ' . ops_money((float) $bank - $system) . '.');
            }
        }
        // Pemeriksaan integritas antara Iuran dan Kas.
        $checks = array(
            'Pembayaran iuran terverifikasi tanpa jurnal kas' => $this->db->query("SELECT p.id,p.amount,p.reviewed_at FROM due_payments p WHERE p.status='verified' AND p.transaction_id IS NULL")->result_array(),
            'Nilai jurnal kas berbeda dari pembayaran iuran' => $this->db->query("SELECT p.id,p.amount,t.amount tx_amount FROM due_payments p JOIN transactions t ON t.id=p.transaction_id WHERE p.status='verified' AND ROUND(t.amount,2)<>ROUND(p.amount,2)")->result_array(),
            'Jurnal kas dipakai lebih dari satu pembayaran (posting ganda)' => $this->db->query("SELECT transaction_id id, COUNT(*) n FROM due_payments WHERE transaction_id IS NOT NULL GROUP BY transaction_id HAVING COUNT(*)>1")->result_array(),
            'Tagihan dengan nilai terbayar ≠ total pembayaran terverifikasi' => $this->db->query("SELECT d.id,d.amount_paid,COALESCE(SUM(p.amount),0) verified FROM dues d LEFT JOIN due_payments p ON p.due_id=d.id AND p.status='verified' GROUP BY d.id HAVING ROUND(d.amount_paid,2)<>ROUND(verified,2)")->result_array(),
        );
        $history = $this->db->select('r.*,a.username')->from('cash_reconciliations r')->join('admins a', 'a.id=r.created_by', 'left')->order_by('r.recon_date', 'DESC')->order_by('r.id', 'DESC')->limit(30)->get()->result_array();
        $this->render('rekonsiliasi', array('system' => $this->system_balance(date('Y-m-d')), 'checks' => $checks, 'history' => $history, 'error' => $error));
    }

    /** Saldo dari jurnal kas terverifikasi s.d. tanggal (transaksi pembalik ikut menetralkan). */
    private function system_balance($date)
    {
        $r = $this->db->query("SELECT COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE -amount END),0) s FROM transactions WHERE status='verified' AND tx_date<=?", array($date))->row_array();
        return (float) $r['s'];
    }
}
