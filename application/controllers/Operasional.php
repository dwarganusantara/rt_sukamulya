<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 10 Aset & Inventaris, 11 Kontak Penting, 12 Belanja & Penerimaan Barang.
 * Stok dikunci FOR UPDATE di setiap perubahan sehingga tidak bisa negatif walau
 * ada permintaan bersamaan. Posting kas belanja hanya sekali (transaction_id unik).
 */
class Operasional extends Ops_Controller
{
    private $asset_categories = array('peralatan' => 'Peralatan', 'elektronik' => 'Elektronik', 'furnitur' => 'Furnitur', 'kebersihan' => 'Kebersihan', 'keamanan' => 'Keamanan', 'acara' => 'Perlengkapan Acara', 'atk' => 'ATK', 'lainnya' => 'Lainnya');
    private $kinds = array('aset' => 'Aset (dapat dipinjam)', 'habis_pakai' => 'Barang habis pakai');
    private $conditions = array('baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang');
    public static $loan_labels = array('requested' => 'Diajukan', 'approved' => 'Disetujui', 'borrowed' => 'Dipinjam', 'returned' => 'Dikembalikan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan');
    public static $loan_tones = array('requested' => 'pending', 'approved' => 'info', 'borrowed' => 'purple', 'returned' => 'ok', 'rejected' => 'bad', 'cancelled' => 'muted');
    private $movement_labels = array('in' => 'Masuk', 'out' => 'Keluar / dipakai', 'loan_out' => 'Dipinjam', 'loan_return' => 'Kembali', 'adjust' => 'Penyesuaian', 'lost' => 'Hilang', 'damaged' => 'Rusak', 'purchase' => 'Dari belanja');
    private $contact_categories = array('darurat' => 'Darurat', 'kesehatan' => 'Kesehatan', 'keamanan' => 'Keamanan', 'pemerintahan' => 'Pemerintahan', 'utilitas' => 'Listrik/Air/Gas', 'kebersihan' => 'Kebersihan', 'pengurus' => 'Pengurus RT', 'umum' => 'Umum');
    public static $purchase_labels = array('draft' => 'Draf', 'submitted' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'ordered' => 'Dipesan',
        'partially_received' => 'Diterima Sebagian', 'received' => 'Diterima', 'closed' => 'Ditutup', 'cancelled' => 'Dibatalkan');
    public static $purchase_tones = array('draft' => 'muted', 'submitted' => 'pending', 'approved' => 'info', 'rejected' => 'bad', 'ordered' => 'purple', 'partially_received' => 'pending', 'received' => 'ok', 'closed' => 'muted', 'cancelled' => 'muted');

    /* ===================== 10 INVENTARIS ===================== */

    public function aset_index()
    {
        $this->guard_admin('aset.read');
        $q = $this->str('q', 80, 'get');
        $kind = $this->choice('jenis', $this->kinds, '', 'get');
        $this->db->where('status !=', 'dihapus');
        if ($kind) $this->db->where('kind', $kind);
        if ($q !== '') $this->db->group_start()->like('code', $q)->or_like('name', $q)->or_like('location', $q)->group_end();
        $items = $this->db->order_by('name')->get('assets')->result_array();
        $stats = $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(CASE WHEN kind='aset' THEN quantity-qty_available ELSE 0 END),0) out_qty,
            SUM(condition_status IN ('rusak_ringan','rusak_berat')) damaged, SUM(status='diusulkan_hapus') proposed FROM assets WHERE status!='dihapus'")->row_array();
        $loans = $this->db->select('l.*,a.name asset_name,a.unit')->from('asset_loans l')->join('assets a', 'a.id=l.asset_id')
            ->where_in('l.status', array('requested', 'approved', 'borrowed'))->order_by('l.due_date')->get()->result_array();
        $this->render('aset_index', array('items' => $items, 'stats' => $stats, 'loans' => $loans, 'q' => $q, 'kind' => $kind, 'kinds' => $this->kinds,
            'categories' => $this->asset_categories, 'conditions' => $this->conditions, 'loan_labels' => self::$loan_labels, 'loan_tones' => self::$loan_tones));
    }

    public function aset_form($id = 0)
    {
        $this->guard_admin('aset.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('assets', array('id' => $id))->row_array() : NULL;
        if ($id && (!$item || $item['status'] === 'dihapus')) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('code' => strtoupper($this->str('code', 40)), 'name' => $this->str('name', 160), 'category' => $this->choice('category', $this->asset_categories, 'lainnya'),
                'unit' => $this->str('unit', 30) ?: 'unit', 'condition_status' => $this->choice('condition_status', $this->conditions, 'baik'), 'location' => $this->str('location', 160),
                'source' => $this->str('source', 120), 'pic' => $this->str('pic', 120), 'value' => $this->input->post('value') !== '' ? $this->money('value') : NULL, 'note' => $this->str('note', 500), 'updated_at' => date('Y-m-d H:i:s'));
            if (!$id) $data['kind'] = $this->choice('kind', $this->kinds, 'aset');
            $qty = (int) $this->input->post('quantity');
            if (!preg_match('/^[A-Z0-9._-]{2,40}$/', $data['code']) || mb_strlen($data['name']) < 2) $error = 'Kode (huruf/angka, tanpa spasi) dan nama barang wajib diisi.';
            elseif (!$id && ($qty < 0 || $qty > 100000)) $error = 'Jumlah awal tidak valid.';
            else {
                $dup = $this->db->get_where('assets', array('code' => $data['code']))->row_array();
                if ($dup && (int) $dup['id'] !== $id) $error = 'Kode barang sudah dipakai.';
            }
            if (!$error) {
                $this->atomic(function () use (&$id, $data, $qty) {
                    if ($id) {$this->db->where('id', $id)->update('assets', $data); return;}
                    $this->db->insert('assets', $data + array('quantity' => $qty, 'qty_available' => $qty, 'created_by' => $this->admin_id));
                    $id = (int) $this->db->insert_id();
                    if ($qty > 0) $this->movement($id, 'in', $qty, 'manual', NULL, 'Stok awal');
                });
                $this->audit($item ? 'update' : 'create', 'asset:' . $id, $data['code']);
                $this->back('admin/inventaris/' . $id, 'Data barang disimpan.');
            }
            $item = array_merge($item ?: array(), $data, array('quantity' => $qty));
        }
        $this->render('aset_form', array('item' => $item, 'error' => $error, 'kinds' => $this->kinds, 'categories' => $this->asset_categories, 'conditions' => $this->conditions));
    }

    public function aset_detail($id = 0)
    {
        $this->guard_admin('aset.read');
        $id = (int) $id;
        $item = $this->db->get_where('assets', array('id' => $id))->row_array();
        if (!$item) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 30);
            try {
                $this->atomic(function () use ($id, $act) {
                    $a = $this->lock('assets', $id);
                    if ($a['status'] === 'dihapus') throw new RuntimeException('Barang sudah dihapus dari daftar.');
                    if ($act === 'stock') {
                        $this->need('aset.write');
                        $type = $this->choice('movement', array('in' => 1, 'out' => 1, 'damaged' => 1, 'lost' => 1, 'adjust' => 1));
                        $qty = (int) $this->input->post('qty');
                        $note = $this->str('note', 255);
                        if (!$type || $qty === 0 || abs($qty) > 100000 || mb_strlen($note) < 3) throw new RuntimeException('Pilih jenis, isi jumlah, dan alasan (min. 3 karakter).');
                        if ($type === 'out' && $a['kind'] !== 'habis_pakai') throw new RuntimeException('Pengeluaran pemakaian hanya untuk barang habis pakai. Untuk aset gunakan peminjaman.');
                        $delta_total = $type === 'in' ? abs($qty) : ($type === 'adjust' ? $qty : -abs($qty));
                        $delta_avail = $type === 'damaged' ? -abs($qty) : $delta_total;
                        if ($type === 'damaged') $delta_total = 0;
                        if ($a['qty_available'] + $delta_avail < 0 || $a['quantity'] + $delta_total < 0) throw new RuntimeException('Stok tidak mencukupi. Tersedia: ' . (int) $a['qty_available'] . ' ' . $a['unit'] . '.');
                        $this->db->where('id', $id)->update('assets', array('quantity' => $a['quantity'] + $delta_total, 'qty_available' => $a['qty_available'] + $delta_avail, 'updated_at' => date('Y-m-d H:i:s')));
                        $this->movement($id, $type, $type === 'adjust' ? $qty : abs($qty), 'manual', NULL, $note);
                    } elseif ($act === 'propose_delete') {
                        $this->need('aset.write');
                        if ((int) $this->db->where('asset_id', $id)->where_in('status', array('approved', 'borrowed'))->count_all_results('asset_loans')) throw new RuntimeException('Masih ada peminjaman aktif untuk barang ini.');
                        $note = $this->str('note', 255);
                        if (mb_strlen($note) < 5) throw new RuntimeException('Tuliskan alasan penghapusan.');
                        $this->db->where('id', $id)->update('assets', array('status' => 'diusulkan_hapus', 'note' => mb_substr('Usulan hapus: ' . $note . ' | ' . $a['note'], 0, 500)));
                    } elseif ($act === 'approve_delete' || $act === 'cancel_delete') {
                        $this->need('aset.approve');
                        if ($a['status'] !== 'diusulkan_hapus') throw new RuntimeException('Tidak ada usulan penghapusan.');
                        $this->db->where('id', $id)->update('assets', array('status' => $act === 'approve_delete' ? 'dihapus' : 'aktif'));
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->audit($act, 'asset:' . $id);
                });
                $this->back('admin/inventaris/' . $id, 'Data barang diperbarui.');
            } catch (RuntimeException $e) {
                $this->back('admin/inventaris/' . $id, '', $e->getMessage());
            }
        }
        $movements = $this->db->select('m.*,a.username')->from('stock_movements m')->join('admins a', 'a.id=m.actor_id', 'left')->where('m.asset_id', $id)->order_by('m.id', 'DESC')->limit(50)->get()->result_array();
        $loans = $this->db->order_by('id', 'DESC')->limit(30)->get_where('asset_loans', array('asset_id' => $id))->result_array();
        $this->render('aset_detail', array('item' => $item, 'movements' => $movements, 'loans' => $loans, 'kinds' => $this->kinds, 'categories' => $this->asset_categories,
            'conditions' => $this->conditions, 'movement_labels' => $this->movement_labels, 'loan_labels' => self::$loan_labels, 'loan_tones' => self::$loan_tones));
    }

    public function pinjam()
    {
        $this->guard_admin('aset.read');
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('aset.write');
            $act = $this->str('act', 20);
            try {
                if ($act === 'create') {
                    $asset_id = (int) $this->input->post('asset_id');
                    $qty = (int) $this->input->post('qty');
                    $loan = $this->date_or_null('loan_date');
                    $due = $this->date_or_null('due_date');
                    $name = $this->str('borrower_name', 140);
                    $asset = $this->db->get_where('assets', array('id' => $asset_id, 'kind' => 'aset', 'status' => 'aktif'))->row_array();
                    if (!$asset) throw new RuntimeException('Pilih aset yang dapat dipinjam.');
                    if ($qty < 1 || !$loan || !$due || $due < $loan || mb_strlen($name) < 2) throw new RuntimeException('Lengkapi peminjam, jumlah, tanggal pinjam dan tanggal kembali (tidak sebelum tanggal pinjam).');
                    if ($qty > (int) $asset['qty_available']) throw new RuntimeException('Jumlah melebihi stok tersedia (' . (int) $asset['qty_available'] . ').');
                    $borrower = (int) $this->input->post('citizen_id');
                    if ($borrower && !$this->db->get_where('citizens', array('id' => $borrower, 'is_active' => 1))->row_array()) throw new RuntimeException('Akun warga peminjam tidak ditemukan.');
                    $this->db->insert('asset_loans', array('asset_id' => $asset_id, 'borrower_name' => $name, 'citizen_id' => $borrower ?: NULL, 'qty' => $qty,
                        'purpose' => $this->str('purpose', 255), 'loan_date' => $loan, 'due_date' => $due, 'status' => 'requested', 'created_by' => $this->admin_id));
                    $this->audit('create', 'loan:' . $this->db->insert_id());
                    $this->back('admin/inventaris/pinjam', 'Permohonan pinjam dicatat. Setujui untuk mengunci stok.');
                }
                $lid = (int) $this->input->post('loan_id');
                $this->atomic(function () use ($lid, $act) {
                    $l = $this->lock('asset_loans', $lid);
                    if (!$l) throw new RuntimeException('Peminjaman tidak ditemukan.');
                    $a = $this->lock('assets', $l['asset_id']);
                    $set = array('updated_at' => date('Y-m-d H:i:s'));
                    if ($act === 'approve') {
                        $this->need('aset.approve');
                        if ($l['status'] !== 'requested') throw new RuntimeException('Permohonan sudah diproses.');
                        // Stok dikunci saat disetujui sehingga dua persetujuan bersamaan tidak membuat stok minus.
                        if ((int) $a['qty_available'] < (int) $l['qty']) throw new RuntimeException('Stok tersedia tidak cukup (' . (int) $a['qty_available'] . ').');
                        $this->db->where('id', $a['id'])->update('assets', array('qty_available' => $a['qty_available'] - $l['qty']));
                        $this->movement($a['id'], 'loan_out', $l['qty'], 'loan', $lid, 'Disetujui untuk ' . $l['borrower_name']);
                        $set += array('status' => 'approved', 'approved_by' => $this->admin_id, 'approved_at' => date('Y-m-d H:i:s'));
                    } elseif ($act === 'reject' || $act === 'cancel') {
                        // Menolak permohonan adalah keputusan persetujuan; membatalkan cukup hak tulis.
                        if ($act === 'reject') $this->need('aset.approve');
                        if (!in_array($l['status'], array('requested', 'approved'), TRUE)) throw new RuntimeException('Peminjaman tidak dapat dibatalkan pada status ini.');
                        if ($l['status'] === 'approved') {
                            $this->db->where('id', $a['id'])->update('assets', array('qty_available' => $a['qty_available'] + $l['qty']));
                            $this->movement($a['id'], 'loan_return', $l['qty'], 'loan', $lid, 'Batal sebelum diserahkan');
                        }
                        $set['status'] = $act === 'reject' ? 'rejected' : 'cancelled';
                    } elseif ($act === 'handover') {
                        if ($l['status'] !== 'approved') throw new RuntimeException('Serah terima hanya untuk peminjaman yang disetujui.');
                        $set += array('status' => 'borrowed', 'borrowed_at' => date('Y-m-d H:i:s'));
                    } elseif ($act === 'return') {
                        if ($l['status'] !== 'borrowed') throw new RuntimeException('Barang belum diserahkan.');
                        $good = max(0, (int) $this->input->post('qty_good'));
                        $damaged = max(0, (int) $this->input->post('qty_damaged'));
                        $lost = max(0, (int) $this->input->post('qty_lost'));
                        if ($good + $damaged + $lost !== (int) $l['qty']) throw new RuntimeException('Jumlah baik + rusak + hilang harus sama dengan jumlah dipinjam (' . (int) $l['qty'] . ').');
                        $this->db->where('id', $a['id'])->update('assets', array('qty_available' => $a['qty_available'] + $good, 'quantity' => $a['quantity'] - $lost, 'updated_at' => date('Y-m-d H:i:s')));
                        if ($good) $this->movement($a['id'], 'loan_return', $good, 'loan', $lid, 'Kembali kondisi baik');
                        if ($damaged) $this->movement($a['id'], 'damaged', $damaged, 'loan', $lid, 'Kembali dalam kondisi rusak');
                        if ($lost) $this->movement($a['id'], 'lost', $lost, 'loan', $lid, 'Hilang saat dipinjam');
                        $set += array('status' => 'returned', 'returned_at' => date('Y-m-d H:i:s'), 'qty_returned_good' => $good, 'qty_damaged' => $damaged, 'qty_lost' => $lost, 'note' => $this->str('note', 500));
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->db->where('id', $lid)->update('asset_loans', $set);
                    $this->audit('loan_' . $act, 'loan:' . $lid);
                });
                $this->back('admin/inventaris/pinjam', 'Peminjaman diperbarui.');
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
        $status = $this->choice('status', self::$loan_labels + array('active' => 'Aktif', 'all' => 'Semua'), 'active', 'get');
        $this->db->select('l.*,a.name asset_name,a.code,a.unit')->from('asset_loans l')->join('assets a', 'a.id=l.asset_id');
        if ($status === 'active') $this->db->where_in('l.status', array('requested', 'approved', 'borrowed'));
        elseif ($status !== 'all') $this->db->where('l.status', $status);
        $loans = $this->db->order_by("FIELD(l.status,'requested','approved','borrowed')", 'DESC', FALSE)->order_by('l.due_date')->limit(200)->get()->result_array();
        $assets = array();
        foreach ($this->db->where('kind', 'aset')->where('status', 'aktif')->where('qty_available >', 0)->order_by('name')->get('assets')->result_array() as $a) $assets[(int) $a['id']] = $a['name'] . ' (' . $a['code'] . ') — tersedia ' . (int) $a['qty_available'] . ' ' . $a['unit'];
        $citizens = array();
        foreach ($this->db->select('id,display_name')->where('is_active', 1)->order_by('display_name')->get('citizens')->result_array() as $c) $citizens[(int) $c['id']] = $c['display_name'];
        $this->render('aset_pinjam', array('loans' => $loans, 'assets' => $assets, 'citizens' => $citizens, 'status' => $status, 'error' => $error, 'labels' => self::$loan_labels, 'tones' => self::$loan_tones));
    }

    private function movement($asset_id, $type, $qty, $ref_type, $ref_id, $note)
    {
        $this->db->insert('stock_movements', array('asset_id' => (int) $asset_id, 'movement' => $type, 'qty' => (int) $qty, 'ref_type' => $ref_type, 'ref_id' => $ref_id, 'note' => mb_substr($note, 0, 255), 'actor_id' => $this->admin_id ?: NULL));
    }

    /* ===================== 11 KONTAK PENTING ===================== */

    public function kontak($id = 0)
    {
        $this->guard_admin('kontak.read');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('important_contacts', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $this->need('kontak.write');
            $act = $this->str('act', 20);
            if ($act === 'verify' || $act === 'toggle') {
                $cid = (int) $this->input->post('contact_id');
                $c = $this->db->get_where('important_contacts', array('id' => $cid))->row_array();
                if (!$c) show_404();
                $this->db->where('id', $cid)->update('important_contacts', $act === 'verify' ? array('verified_at' => date('Y-m-d')) : array('is_active' => $c['is_active'] ? 0 : 1));
                $this->audit('contact_' . $act, 'contact:' . $cid);
                $this->back('admin/kontak', $act === 'verify' ? 'Kontak ditandai sudah diperiksa hari ini.' : 'Status kontak diubah.');
            }
            $data = array('name' => $this->str('name', 140), 'category' => $this->choice('category', $this->contact_categories, 'umum'), 'phone' => preg_replace('/[^0-9+() -]/', '', $this->str('phone', 30)),
                'address' => $this->str('address', 255), 'service_hours' => $this->str('service_hours', 100), 'note' => $this->str('note', 255), 'is_public' => $this->input->post('is_public') ? 1 : 0,
                'is_emergency' => $this->input->post('is_emergency') ? 1 : 0, 'is_active' => $this->input->post('is_active') ? 1 : 0, 'sort_order' => (int) $this->input->post('sort_order'), 'updated_at' => date('Y-m-d H:i:s'));
            if ($this->input->post('verified_now')) $data['verified_at'] = date('Y-m-d');
            if (mb_strlen($data['name']) < 2 || strlen(preg_replace('/\D/', '', $data['phone'])) < 3) $error = 'Nama layanan dan nomor telepon wajib diisi.';
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('important_contacts', $data);
                else {$this->db->insert('important_contacts', $data); $id = (int) $this->db->insert_id();}
                $this->audit($item ? 'update' : 'create', 'contact:' . $id);
                $this->back('admin/kontak', 'Kontak penting disimpan.');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $items = $this->db->order_by('is_emergency', 'DESC')->order_by('sort_order')->order_by('name')->get('important_contacts')->result_array();
        $this->render('kontak', array('items' => $items, 'item' => $item, 'error' => $error, 'categories' => $this->contact_categories));
    }

    public function kontak_warga()
    {
        $this->guard_citizen();
        $items = $this->db->where('is_public', 1)->where('is_active', 1)->order_by('is_emergency', 'DESC')->order_by('sort_order')->order_by('name')->get('important_contacts')->result_array();
        $this->render_citizen('kontak', 'Kontak Penting', array('items' => $items, 'categories' => $this->contact_categories));
    }

    /* ===================== 12 BELANJA ===================== */

    public function belanja_index()
    {
        $this->guard_admin('belanja.read');
        $status = $this->choice('status', self::$purchase_labels, '', 'get');
        $counts = array();
        foreach ($this->db->select('status,COUNT(*) n')->group_by('status')->get('purchase_requests')->result_array() as $r) $counts[$r['status']] = (int) $r['n'];
        $this->db->select('p.*,a.username requester,w.title program_title')->from('purchase_requests p')->join('admins a', 'a.id=p.requested_by', 'left')->join('work_programs w', 'w.id=p.program_id', 'left');
        if ($status) $this->db->where('p.status', $status);
        $items = $this->db->order_by("FIELD(p.status,'submitted','approved','ordered','partially_received','received','draft')", 'DESC', FALSE)->order_by('p.id', 'DESC')->limit(200)->get()->result_array();
        $spent = $this->db->query("SELECT COALESCE(SUM(t.amount),0) s FROM purchase_requests p JOIN transactions t ON t.id=p.transaction_id WHERE t.status='verified' AND t.period=?", array(date('Y-m')))->row_array();
        $this->render('belanja_index', array('items' => $items, 'counts' => $counts, 'status' => $status, 'spent' => (float) $spent['s'], 'labels' => self::$purchase_labels, 'tones' => self::$purchase_tones));
    }

    public function belanja_form($id = 0)
    {
        $this->guard_admin('belanja.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('purchase_requests', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        if ($item && !in_array($item['status'], array('draft', 'rejected'), TRUE)) $this->back('admin/belanja/' . $id, '', 'Permintaan yang sudah diajukan tidak dapat diubah.');
        if ($item && (int) $item['requested_by'] !== $this->admin_id && !in_array($this->role,array('admin'),TRUE)) $this->back('admin/belanja/' . $id, '', 'Hanya pemohon yang dapat mengubah permintaan ini.');
        $error = '';
        $items = $id ? $this->db->get_where('purchase_items', array('purchase_id' => $id))->result_array() : array();
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('title' => $this->str('title', 160), 'reason' => $this->text('reason', 3000), 'vendor' => $this->str('vendor', 140),
                'program_id' => (int) $this->input->post('program_id') ?: NULL, 'is_donation' => $this->input->post('is_donation') ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s'));
            $rows = array();
            $names = (array) $this->input->post('item_name', TRUE);
            foreach ($names as $i => $name) {
                $name = mb_substr(trim((string) $name), 0, 160);
                if ($name === '') continue;
                $qty = (int) ($this->input->post('item_qty')[$i] ?? 0);
                $price = (float) preg_replace('/[^0-9]/', '', (string) ($this->input->post('item_price')[$i] ?? '0'));
                if ($qty < 1 || $qty > 100000 || $price < 0 || $price > 999999999999) {$error = 'Jumlah dan harga item "' . $name . '" tidak valid.'; break;}
                $rows[] = array('name' => $name, 'qty' => $qty, 'unit' => mb_substr(trim((string) ($this->input->post('item_unit', TRUE)[$i] ?? 'unit')), 0, 30) ?: 'unit', 'est_price' => $price,
                    'to_inventory' => !empty($this->input->post('item_inventory')[$i]) ? 1 : 0);
            }
            if (!$error && (mb_strlen($data['title']) < 3 || !$rows)) $error = 'Isi judul permintaan dan minimal satu item.';
            if (!$error && $data['program_id'] && !$this->db->get_where('work_programs', array('id' => $data['program_id']))->row_array()) $error = 'Program tidak valid.';
            if (!$error) {
                $data['estimated_total'] = array_sum(array_map(function ($r) {return $r['qty'] * $r['est_price'];}, $rows));
                $data['status'] = $this->input->post('submit_now') ? 'submitted' : 'draft';
                $this->atomic(function () use (&$id, $data, $rows) {
                    if ($id) {
                        $this->db->where('id', $id)->update('purchase_requests', $data + array('review_note' => NULL));
                        $this->db->where('purchase_id', $id)->delete('purchase_items');
                    } else {
                        $this->db->insert('purchase_requests', $data + array('requested_by' => $this->admin_id));
                        $id = (int) $this->db->insert_id();
                    }
                    foreach ($rows as $r) $this->db->insert('purchase_items', $r + array('purchase_id' => $id));
                });
                $this->audit($item ? 'update' : 'create', 'purchase:' . $id, $data['status']);
                $this->back('admin/belanja/' . $id, $data['status'] === 'submitted' ? 'Permintaan belanja diajukan untuk persetujuan.' : 'Draf permintaan belanja disimpan.');
            }
            $item = array_merge($item ?: array(), $data);
            $items = $rows;
        }
        $programs = array();
        foreach ($this->db->select('id,title')->where('deleted_at IS NULL', NULL, FALSE)->where_in('status', array('aktif', 'diajukan', 'draft'))->order_by('title')->get('work_programs')->result_array() as $p) $programs[(int) $p['id']] = $p['title'];
        $this->render('belanja_form', array('item' => $item, 'items' => $items, 'programs' => $programs, 'error' => $error));
    }

    public function belanja_detail($id = 0)
    {
        $this->guard_admin('belanja.read');
        $id = (int) $id;
        $item = $this->db->get_where('purchase_requests', array('id' => $id))->row_array();
        if (!$item) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 20);
            $this->load->library('Private_storage');
            $upload = NULL;
            try {
                if ($act === 'post') {
                    $upload_error = '';
                    $upload = $this->private_storage->store('proof', 'purchases', $upload_error, array('jpg', 'png', 'webp', 'pdf'));
                    if ($upload_error) throw new RuntimeException($upload_error);
                }
                $notice = $this->atomic(function () use ($id, $act, $upload) {
                    $p = $this->lock('purchase_requests', $id);
                    $set = array('updated_at' => date('Y-m-d H:i:s'));
                    $note = $this->str('note', 500);
                    switch ($act) {
                        case 'submit':
                            $this->need('belanja.write');
                            if (!in_array($p['status'], array('draft', 'rejected'), TRUE)) throw new RuntimeException('Permintaan sudah diajukan.');
                            $set['status'] = 'submitted';
                            break;
                        case 'approve':
                        case 'reject':
                            $this->need('belanja.approve');
                            if ($p['status'] !== 'submitted') throw new RuntimeException('Hanya permintaan berstatus menunggu persetujuan yang dapat diputuskan.');
                            // Pemisahan tugas: pemohon tidak menyetujui permintaannya sendiri.
                            if ((int) $p['requested_by'] === $this->admin_id) throw new RuntimeException('Pemohon tidak boleh memutuskan permintaannya sendiri. Minta pengurus lain yang berwenang.');
                            if ($act === 'reject' && mb_strlen($note) < 3) throw new RuntimeException('Tuliskan alasan penolakan.');
                            $set += array('status' => $act === 'approve' ? 'approved' : 'rejected', 'approved_by' => $this->admin_id, 'approved_at' => date('Y-m-d H:i:s'), 'review_note' => $note ?: NULL);
                            break;
                        case 'order':
                            $this->need('belanja.write');
                            if ($p['status'] !== 'approved') throw new RuntimeException('Pemesanan hanya untuk permintaan yang disetujui.');
                            $set['status'] = 'ordered';
                            if ($this->str('vendor', 140) !== '') $set['vendor'] = $this->str('vendor', 140);
                            break;
                        case 'receive':
                            $this->need('belanja.receive');
                            if (!in_array($p['status'], array('ordered', 'partially_received'), TRUE)) throw new RuntimeException('Penerimaan hanya untuk barang yang sudah dipesan.');
                            $received = array();
                            $complete = TRUE;
                            foreach ($this->db->query('SELECT * FROM purchase_items WHERE purchase_id=? FOR UPDATE', array($id))->result_array() as $it) {
                                $qty = max(0, (int) ($this->input->post('recv')[$it['id']] ?? 0));
                                $remaining = (int) $it['qty'] - (int) $it['qty_received'];
                                if ($qty > $remaining) throw new RuntimeException('Jumlah diterima untuk "' . $it['name'] . '" melebihi sisa pesanan (' . $remaining . ').');
                                if ($qty > 0) {
                                    $upd = array('qty_received' => (int) $it['qty_received'] + $qty);
                                    $price = (string) ($this->input->post('price')[$it['id']] ?? '');
                                    if ($price !== '') $upd['actual_price'] = (float) preg_replace('/[^0-9]/', '', $price);
                                    if ($it['to_inventory']) $upd['asset_id'] = $this->receive_to_inventory($it, $qty, $id);
                                    $this->db->where('id', $it['id'])->update('purchase_items', $upd);
                                    $received[] = array('item_id' => (int) $it['id'], 'name' => $it['name'], 'qty' => $qty);
                                }
                                if ((int) $it['qty_received'] + $qty < (int) $it['qty']) $complete = FALSE;
                            }
                            if (!$received) throw new RuntimeException('Isi jumlah barang yang diterima.');
                            $this->db->insert('goods_receipts', array('purchase_id' => $id, 'receipt_date' => date('Y-m-d'), 'items_json' => json_encode($received, JSON_UNESCAPED_UNICODE),
                                'condition_note' => $note, 'received_by' => $this->admin_id));
                            $set['status'] = $complete ? 'received' : 'partially_received';
                            break;
                        case 'post':
                            $this->need('belanja.post');
                            if ($p['is_donation']) throw new RuntimeException('Barang donasi tidak diposting sebagai pengeluaran kas.');
                            if (!in_array($p['status'], array('ordered', 'partially_received', 'received'), TRUE)) throw new RuntimeException('Pembayaran belanja hanya dapat diposting setelah pemesanan.');
                            if ($p['transaction_id']) throw new RuntimeException('Pengeluaran belanja ini sudah diposting ke kas (transaksi #' . (int) $p['transaction_id'] . ').');
                            $amount = $this->money('actual_total');
                            if ($amount <= 0) throw new RuntimeException('Isi nilai realisasi pembayaran.');
                            $diff_note = $this->str('actual_note', 255);
                            if (abs($amount - (float) $p['estimated_total']) > 0.009 && mb_strlen($diff_note) < 3) throw new RuntimeException('Nilai realisasi berbeda dari estimasi. Tuliskan alasannya.');
                            $this->db->insert('transactions', array('tx_date' => date('Y-m-d'), 'type' => 'keluar', 'category' => 'Belanja RT', 'description' => mb_substr('Belanja BLJ-' . str_pad($id, 5, '0', STR_PAD_LEFT) . ': ' . $p['title'], 0, 255),
                                'amount' => $amount, 'period' => date('Y-m'), 'counterparty' => mb_substr($p['vendor'], 0, 140), 'proof' => '', 'status' => 'verified',
                                'created_by' => $this->admin_id, 'verified_by' => $this->admin_id, 'verified_at' => date('Y-m-d H:i:s')));
                            $set += array('transaction_id' => (int) $this->db->insert_id(), 'actual_total' => $amount, 'actual_note' => $diff_note);
                            if ($upload) $set['proof'] = $upload['stored_name'];
                            break;
                        case 'close':
                            $this->need('belanja.write');
                            if ($p['status'] !== 'received') throw new RuntimeException('Tutup permintaan setelah semua barang diterima.');
                            if (!$p['is_donation'] && !$p['transaction_id']) throw new RuntimeException('Posting pembayaran ke kas terlebih dahulu.');
                            $set['status'] = 'closed';
                            break;
                        case 'cancel':
                            $this->need('belanja.write');
                            if (!in_array($p['status'], array('draft', 'submitted', 'approved', 'ordered'), TRUE) || $p['transaction_id']) throw new RuntimeException('Permintaan tidak dapat dibatalkan karena sudah ada penerimaan atau posting kas.');
                            $set['status'] = 'cancelled';
                            $set['review_note'] = $note ?: $p['review_note'];
                            break;
                        default:
                            throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->db->where('id', $id)->update('purchase_requests', $set);
                    $this->audit('purchase_' . $act, 'purchase:' . $id, isset($set['status']) ? $p['status'] . '->' . $set['status'] : '');
                    return 'Permintaan belanja diperbarui.';
                });
                $this->back('admin/belanja/' . $id, $notice);
            } catch (RuntimeException $e) {
                if ($upload) $this->private_storage->delete('purchases', $upload['stored_name']);
                $this->back('admin/belanja/' . $id, '', $e->getMessage());
            }
        }
        $item = $this->db->select('p.*,a.username requester,b.username approver,w.title program_title')->from('purchase_requests p')->join('admins a', 'a.id=p.requested_by', 'left')
            ->join('admins b', 'b.id=p.approved_by', 'left')->join('work_programs w', 'w.id=p.program_id', 'left')->where('p.id', $id)->get()->row_array();
        $rows = $this->db->get_where('purchase_items', array('purchase_id' => $id))->result_array();
        $receipts = $this->db->select('g.*,a.username')->from('goods_receipts g')->join('admins a', 'a.id=g.received_by', 'left')->where('g.purchase_id', $id)->order_by('g.id')->get()->result_array();
        $this->render('belanja_detail', array('item' => $item, 'rows' => $rows, 'receipts' => $receipts, 'labels' => self::$purchase_labels, 'tones' => self::$purchase_tones));
    }

    public function belanja_bukti($id = 0)
    {
        $this->guard_admin('belanja.read');
        $item = $this->db->get_where('purchase_requests', array('id' => (int) $id))->row_array();
        if (!$item || !$item['proof']) show_404();
        $this->audit('download', 'purchase_proof:' . (int) $id);
        $this->load->library('Private_storage');
        $this->private_storage->send('purchases', $item['proof'], 'bukti-belanja-' . (int) $id);
    }

    /** Barang yang ditandai masuk inventaris: tambah stok aset bernama sama atau buat aset baru. */
    private function receive_to_inventory($it, $qty, $purchase_id)
    {
        $asset = $it['asset_id'] ? $this->db->query('SELECT * FROM assets WHERE id=? FOR UPDATE', array((int) $it['asset_id']))->row_array() : NULL;
        if (!$asset || $asset['status'] === 'dihapus') {
            $code = 'BLJ' . str_pad($purchase_id, 4, '0', STR_PAD_LEFT) . '-' . (int) $it['id'];
            // Kode unik: bila aset lama dengan kode ini sudah dihapus, beri akhiran agar tidak bentrok uq_asset_code.
            for ($n = 2, $base = $code; $this->db->where('code', $code)->count_all_results('assets') > 0; $n++) $code = $base . '-' . $n;
            $this->db->insert('assets', array('code' => $code, 'name' => $it['name'], 'category' => 'lainnya', 'kind' => 'aset', 'quantity' => $qty, 'qty_available' => $qty,
                'unit' => $it['unit'], 'source' => 'Belanja BLJ-' . str_pad($purchase_id, 5, '0', STR_PAD_LEFT), 'created_by' => $this->admin_id));
            $asset_id = (int) $this->db->insert_id();
        } else {
            $asset_id = (int) $asset['id'];
            $this->db->where('id', $asset_id)->update('assets', array('quantity' => $asset['quantity'] + $qty, 'qty_available' => $asset['qty_available'] + $qty, 'updated_at' => date('Y-m-d H:i:s')));
        }
        $this->movement($asset_id, 'purchase', $qty, 'purchase', $purchase_id, 'Penerimaan barang belanja');
        return $asset_id;
    }
}
