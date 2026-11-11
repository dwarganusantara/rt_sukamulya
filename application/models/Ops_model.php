<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skema & helper data untuk modul RT/RW Digital tambahan.
 * Semua perubahan bersifat additive: CREATE TABLE IF NOT EXISTS dan ADD COLUMN
 * yang dicek lebih dulu. Tidak ada DROP/DELETE terhadap data existing.
 * Isi yang sama tersedia di rt_digital_16_upgrade.sql untuk dijalankan manual.
 */
class Ops_model extends CI_Model
{
    const SCHEMA_VERSION = 'rt16-5-platform-owner';

    public function ensure()
    {
        static $done = FALSE;
        if ($done) return;
        $done = TRUE;
        $row = $this->db->get_where('settings', array('setting_key' => 'ops_schema_version'))->row_array();
        if ($row && $row['setting_value'] === self::SCHEMA_VERSION && $this->db->table_exists('backup_runs')) return;
        $this->migrate();
        $this->db->replace('settings', array('setting_key' => 'ops_schema_version', 'setting_value' => self::SCHEMA_VERSION));
    }

    public function migrate()
    {
        $opt = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $tables = array(
            'resident_mutations' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, mutation_type VARCHAR(30) NOT NULL, family_id INT UNSIGNED NULL, member_id INT UNSIGNED NULL,
                citizen_id INT UNSIGNED NULL, created_by_admin INT UNSIGNED NULL, effective_date DATE NULL, old_values_json TEXT NULL, proposed_values_json TEXT NOT NULL,
                reason VARCHAR(1000) NOT NULL DEFAULT '', status VARCHAR(30) NOT NULL DEFAULT 'submitted', reviewed_by INT UNSIGNED NULL, review_note VARCHAR(1000) NULL,
                reviewed_at DATETIME NULL, applied_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), KEY idx_mut_status (status), KEY idx_mut_family (family_id), KEY idx_mut_citizen (citizen_id)",
            'complaints' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ticket_no VARCHAR(30) NULL, citizen_id INT UNSIGNED NULL, category VARCHAR(40) NOT NULL, title VARCHAR(160) NOT NULL,
                location VARCHAR(190) NOT NULL DEFAULT '', description TEXT NOT NULL, attachment VARCHAR(120) NULL, priority VARCHAR(10) NOT NULL DEFAULT 'normal',
                status VARCHAR(30) NOT NULL DEFAULT 'submitted', assigned_to INT UNSIGNED NULL, resolution_note TEXT NULL, resolved_at DATETIME NULL, closed_at DATETIME NULL,
                rating TINYINT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), UNIQUE KEY uq_ticket (ticket_no), KEY idx_cmp_status (status), KEY idx_cmp_citizen (citizen_id), KEY idx_cmp_assigned (assigned_to)",
            'complaint_updates' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, complaint_id INT UNSIGNED NOT NULL, actor_type VARCHAR(10) NOT NULL, actor_id INT UNSIGNED NOT NULL,
                status_from VARCHAR(30) NULL, status_to VARCHAR(30) NULL, message TEXT NULL, is_internal TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), KEY idx_cu_complaint (complaint_id)",
            'work_programs' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(160) NOT NULL, category VARCHAR(60) NOT NULL DEFAULT '', description TEXT NULL,
                period_start DATE NULL, period_end DATE NULL, target VARCHAR(255) NOT NULL DEFAULT '', indicator VARCHAR(255) NOT NULL DEFAULT '', pic VARCHAR(120) NOT NULL DEFAULT '',
                budget DECIMAL(15,2) NOT NULL DEFAULT 0, realization DECIMAL(15,2) NOT NULL DEFAULT 0, status VARCHAR(20) NOT NULL DEFAULT 'draft', progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
                obstacles TEXT NULL, evaluation TEXT NULL, approved_by INT UNSIGNED NULL, approved_at DATETIME NULL, created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, deleted_at DATETIME NULL, PRIMARY KEY (id), KEY idx_wp_status (status)",
            'program_milestones' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, program_id INT UNSIGNED NOT NULL, title VARCHAR(160) NOT NULL, due_date DATE NULL,
                weight TINYINT UNSIGNED NOT NULL DEFAULT 1, is_done TINYINT(1) NOT NULL DEFAULT 0, done_at DATETIME NULL, note VARCHAR(255) NOT NULL DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_pm_program (program_id)",
            'program_agendas' => "program_id INT UNSIGNED NOT NULL, post_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (program_id, post_id)",
            'activity_notes' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(160) NOT NULL, note_date DATE NOT NULL, agenda_id INT UNSIGNED NULL, program_id INT UNSIGNED NULL,
                location VARCHAR(160) NOT NULL DEFAULT '', participants VARCHAR(1000) NOT NULL DEFAULT '', participant_count INT UNSIGNED NOT NULL DEFAULT 0, summary TEXT NULL,
                decisions TEXT NULL, obstacles TEXT NULL, status VARCHAR(20) NOT NULL DEFAULT 'draft', version INT UNSIGNED NOT NULL DEFAULT 1, approved_by INT UNSIGNED NULL,
                approved_at DATETIME NULL, created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), KEY idx_an_agenda (agenda_id), KEY idx_an_program (program_id)",
            'activity_note_versions' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, note_id INT UNSIGNED NOT NULL, version INT UNSIGNED NOT NULL, snapshot_json MEDIUMTEXT NOT NULL,
                changed_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_note_version (note_id, version)",
            'action_items' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, note_id INT UNSIGNED NOT NULL, title VARCHAR(190) NOT NULL, pic VARCHAR(120) NOT NULL DEFAULT '', due_date DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'open', progress_note VARCHAR(255) NOT NULL DEFAULT '', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), KEY idx_ai_note (note_id), KEY idx_ai_status_due (status, due_date)",
            'patrol_schedules' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, patrol_date DATE NOT NULL, shift VARCHAR(20) NOT NULL, post_location VARCHAR(120) NOT NULL DEFAULT 'Pos Ronda',
                officer_name VARCHAR(140) NOT NULL, citizen_id INT UNSIGNED NULL, attendance VARCHAR(20) NOT NULL DEFAULT 'scheduled', check_in DATETIME NULL, check_out DATETIME NULL,
                substitute_name VARCHAR(140) NOT NULL DEFAULT '', attendance_note VARCHAR(255) NOT NULL DEFAULT '', marked_by_admin INT UNSIGNED NULL, created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), UNIQUE KEY uq_patrol_officer (patrol_date, shift, officer_name), KEY idx_patrol_citizen (citizen_id)",
            'incident_reports' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, schedule_id INT UNSIGNED NULL, incident_at DATETIME NOT NULL, location VARCHAR(160) NOT NULL DEFAULT '',
                category VARCHAR(40) NOT NULL DEFAULT 'lainnya', description TEXT NOT NULL, action_taken TEXT NULL, reported_by_admin INT UNSIGNED NULL, reported_by_citizen INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_ir_date (incident_at)",
            'assets' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, code VARCHAR(40) NOT NULL, name VARCHAR(160) NOT NULL, category VARCHAR(60) NOT NULL DEFAULT '', kind VARCHAR(20) NOT NULL DEFAULT 'aset',
                quantity INT NOT NULL DEFAULT 0, qty_available INT NOT NULL DEFAULT 0, unit VARCHAR(30) NOT NULL DEFAULT 'unit', condition_status VARCHAR(20) NOT NULL DEFAULT 'baik',
                location VARCHAR(160) NOT NULL DEFAULT '', source VARCHAR(120) NOT NULL DEFAULT '', pic VARCHAR(120) NOT NULL DEFAULT '', value DECIMAL(15,2) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'aktif', note VARCHAR(500) NOT NULL DEFAULT '', created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL, PRIMARY KEY (id), UNIQUE KEY uq_asset_code (code), KEY idx_asset_status (status)",
            'stock_movements' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, asset_id INT UNSIGNED NOT NULL, movement VARCHAR(20) NOT NULL, qty INT NOT NULL, ref_type VARCHAR(30) NOT NULL DEFAULT '',
                ref_id INT UNSIGNED NULL, note VARCHAR(255) NOT NULL DEFAULT '', actor_id INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), KEY idx_sm_asset (asset_id)",
            'asset_loans' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, asset_id INT UNSIGNED NOT NULL, borrower_name VARCHAR(140) NOT NULL, citizen_id INT UNSIGNED NULL, qty INT NOT NULL DEFAULT 1,
                purpose VARCHAR(255) NOT NULL DEFAULT '', loan_date DATE NOT NULL, due_date DATE NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'requested', approved_by INT UNSIGNED NULL,
                approved_at DATETIME NULL, borrowed_at DATETIME NULL, returned_at DATETIME NULL, qty_returned_good INT NOT NULL DEFAULT 0, qty_damaged INT NOT NULL DEFAULT 0, qty_lost INT NOT NULL DEFAULT 0,
                note VARCHAR(500) NOT NULL DEFAULT '', created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), KEY idx_loan_asset (asset_id), KEY idx_loan_status (status)",
            'important_contacts' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(140) NOT NULL, category VARCHAR(40) NOT NULL DEFAULT 'umum', phone VARCHAR(30) NOT NULL,
                address VARCHAR(255) NOT NULL DEFAULT '', service_hours VARCHAR(100) NOT NULL DEFAULT '', note VARCHAR(255) NOT NULL DEFAULT '', is_public TINYINT(1) NOT NULL DEFAULT 1,
                is_active TINYINT(1) NOT NULL DEFAULT 1, is_emergency TINYINT(1) NOT NULL DEFAULT 0, verified_at DATE NULL, sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, PRIMARY KEY (id), KEY idx_contact_public (is_public, is_active)",
            'purchase_requests' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(160) NOT NULL, reason TEXT NULL, vendor VARCHAR(140) NOT NULL DEFAULT '', program_id INT UNSIGNED NULL,
                estimated_total DECIMAL(15,2) NOT NULL DEFAULT 0, actual_total DECIMAL(15,2) NULL, actual_note VARCHAR(255) NOT NULL DEFAULT '', is_donation TINYINT(1) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'draft', requested_by INT UNSIGNED NULL, approved_by INT UNSIGNED NULL, approved_at DATETIME NULL, review_note VARCHAR(500) NULL,
                proof VARCHAR(120) NULL, transaction_id INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
                PRIMARY KEY (id), UNIQUE KEY uq_purchase_tx (transaction_id), KEY idx_pr_status (status)",
            'purchase_items' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, purchase_id INT UNSIGNED NOT NULL, name VARCHAR(160) NOT NULL, qty INT NOT NULL DEFAULT 1, unit VARCHAR(30) NOT NULL DEFAULT 'unit',
                est_price DECIMAL(15,2) NOT NULL DEFAULT 0, actual_price DECIMAL(15,2) NULL, qty_received INT NOT NULL DEFAULT 0, to_inventory TINYINT(1) NOT NULL DEFAULT 0, asset_id INT UNSIGNED NULL,
                PRIMARY KEY (id), KEY idx_pi_purchase (purchase_id)",
            'goods_receipts' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, purchase_id INT UNSIGNED NOT NULL, receipt_date DATE NOT NULL, items_json TEXT NOT NULL, condition_note VARCHAR(500) NOT NULL DEFAULT '',
                received_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_gr_purchase (purchase_id)",
            'aid_programs' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(160) NOT NULL, period VARCHAR(40) NOT NULL DEFAULT '', aid_type VARCHAR(60) NOT NULL DEFAULT '',
                unit_value DECIMAL(15,2) NOT NULL DEFAULT 0, source VARCHAR(120) NOT NULL DEFAULT '', criteria TEXT NULL, quota INT UNSIGNED NOT NULL DEFAULT 0, status VARCHAR(20) NOT NULL DEFAULT 'draft',
                created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, PRIMARY KEY (id)",
            'aid_recipients' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, program_id INT UNSIGNED NOT NULL, recipient_key VARCHAR(60) NOT NULL, citizen_id INT UNSIGNED NULL, family_id INT UNSIGNED NULL,
                recipient_name VARCHAR(140) NOT NULL, address VARCHAR(255) NOT NULL DEFAULT '', proposal_note VARCHAR(500) NOT NULL DEFAULT '', status VARCHAR(20) NOT NULL DEFAULT 'usulan',
                verified_by INT UNSIGNED NULL, verified_at DATETIME NULL, approved_by INT UNSIGNED NULL, approved_at DATETIME NULL, decision_note VARCHAR(500) NOT NULL DEFAULT '',
                amount DECIMAL(15,2) NOT NULL DEFAULT 0, distributed_at DATETIME NULL, distributed_by INT UNSIGNED NULL, proof VARCHAR(120) NULL, created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_aid_recipient (program_id, recipient_key), KEY idx_aid_status (status)",
            'documents' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, category VARCHAR(60) NOT NULL DEFAULT 'umum', doc_number VARCHAR(100) NOT NULL DEFAULT '', title VARCHAR(190) NOT NULL,
                period VARCHAR(40) NOT NULL DEFAULT '', doc_date DATE NULL, module VARCHAR(30) NOT NULL DEFAULT 'umum', confidentiality VARCHAR(20) NOT NULL DEFAULT 'internal',
                retention_until DATE NULL, current_version INT UNSIGNED NOT NULL DEFAULT 1, uploaded_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL, deleted_at DATETIME NULL, deleted_by INT UNSIGNED NULL, PRIMARY KEY (id), KEY idx_doc_cat (category), KEY idx_doc_deleted (deleted_at)",
            'document_versions' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, document_id INT UNSIGNED NOT NULL, version INT UNSIGNED NOT NULL, stored_name VARCHAR(120) NOT NULL,
                original_name VARCHAR(190) NOT NULL, mime VARCHAR(100) NOT NULL, size_bytes INT UNSIGNED NOT NULL, checksum CHAR(64) NOT NULL, note VARCHAR(255) NOT NULL DEFAULT '',
                uploaded_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_doc_version (document_id, version)",
            'handover_sessions' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(160) NOT NULL, period_label VARCHAR(60) NOT NULL DEFAULT '', outgoing VARCHAR(255) NOT NULL DEFAULT '',
                incoming VARCHAR(255) NOT NULL DEFAULT '', snapshot_json MEDIUMTEXT NULL, snapshot_hash CHAR(64) NULL, checklist_json MEDIUMTEXT NULL, exceptions TEXT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft', finalized_by INT UNSIGNED NULL, finalized_at DATETIME NULL, created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, PRIMARY KEY (id)",
            'handover_signoffs' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, session_id INT UNSIGNED NOT NULL, admin_id INT UNSIGNED NOT NULL, side VARCHAR(10) NOT NULL, role_label VARCHAR(60) NOT NULL DEFAULT '',
                note VARCHAR(255) NOT NULL DEFAULT '', signed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_signoff (session_id, admin_id)",
            'backup_runs' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, kind VARCHAR(20) NOT NULL DEFAULT 'database', status VARCHAR(20) NOT NULL, file_name VARCHAR(160) NULL, size_bytes BIGINT UNSIGNED NULL,
                checksum CHAR(64) NULL, message VARCHAR(500) NOT NULL DEFAULT '', triggered_by INT UNSIGNED NULL, verified_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), KEY idx_backup_created (created_at)",
            'settings_history' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, snapshot_json MEDIUMTEXT NOT NULL, note VARCHAR(255) NOT NULL DEFAULT '', changed_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)",
            'cash_reconciliations' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, recon_date DATE NOT NULL, bank_balance DECIMAL(15,2) NOT NULL, system_balance DECIMAL(15,2) NOT NULL,
                difference DECIMAL(15,2) NOT NULL, note VARCHAR(500) NOT NULL DEFAULT '', created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)",
            'due_reminders' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, due_id INT UNSIGNED NOT NULL, citizen_id INT UNSIGNED NOT NULL, message VARCHAR(500) NOT NULL, sent_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_rem_citizen (citizen_id, created_at)",
            // rt16-2: field tambahan warga (Modul 06) & antrean notifikasi email (Bagian 11).
            'custom_field_definitions' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, label VARCHAR(80) NOT NULL, field_type VARCHAR(20) NOT NULL DEFAULT 'text', options TEXT NULL,
                help_text VARCHAR(160) NOT NULL DEFAULT '', is_required TINYINT(1) NOT NULL DEFAULT 0, citizen_editable TINYINT(1) NOT NULL DEFAULT 1, is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 0, created_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, PRIMARY KEY (id)",
            'citizen_field_values' => "citizen_id INT UNSIGNED NOT NULL, field_id INT UNSIGNED NOT NULL, value VARCHAR(255) NOT NULL DEFAULT '', updated_at DATETIME NULL,
                PRIMARY KEY (citizen_id, field_id), KEY idx_cfv_field (field_id)",
            'notification_outbox' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, citizen_id INT UNSIGNED NULL, channel VARCHAR(20) NOT NULL DEFAULT 'email', recipient VARCHAR(190) NOT NULL,
                subject VARCHAR(190) NOT NULL, body TEXT NOT NULL, dedupe_key VARCHAR(120) NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'pending', attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                last_error VARCHAR(300) NULL, next_attempt_at DATETIME NULL, sent_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), UNIQUE KEY uq_outbox_dedupe (dedupe_key), KEY idx_outbox_status (status, next_attempt_at)",
            // rt16-3: Manajemen Fitur & Menu (katalog fitur, status per tenant, audit perubahan).
            'app_features' => "id INT UNSIGNED NOT NULL AUTO_INCREMENT, code VARCHAR(80) NOT NULL, name VARCHAR(150) NOT NULL, category VARCHAR(80) NOT NULL,
                parent_id INT UNSIGNED NULL, route_key VARCHAR(150) NULL, icon VARCHAR(80) NULL, sort_order INT NOT NULL DEFAULT 0, is_core TINYINT(1) NOT NULL DEFAULT 0,
                is_implemented TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_feature_code (code),
                KEY idx_feature_parent (parent_id), CONSTRAINT fk_feature_parent FOREIGN KEY (parent_id) REFERENCES app_features(id)",
            'tenant_features' => "tenant_id INT UNSIGNED NOT NULL, feature_id INT UNSIGNED NOT NULL, enabled TINYINT(1) NOT NULL DEFAULT 0,
                visible_to_residents TINYINT(1) NOT NULL DEFAULT 1, visible_to_staff TINYINT(1) NOT NULL DEFAULT 1, updated_by INT UNSIGNED NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (tenant_id, feature_id),
                KEY idx_tf_feature (feature_id), CONSTRAINT fk_tenant_feature FOREIGN KEY (feature_id) REFERENCES app_features(id)",
            'tenant_feature_audit' => "id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id INT UNSIGNED NOT NULL, feature_id INT UNSIGNED NOT NULL, actor_user_id INT UNSIGNED NOT NULL,
                old_value LONGTEXT NULL, new_value LONGTEXT NOT NULL, reason VARCHAR(500) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), KEY idx_tenant_time (tenant_id, created_at), KEY idx_tfa_feature (feature_id)",
        );
        foreach ($tables as $name => $columns) {
            if (!$this->db->table_exists($name)) $this->db->query('CREATE TABLE IF NOT EXISTS `' . $name . '` (' . $columns . ')' . $opt);
        }

        $columns = array(
            'activity_logs' => array('ip_address' => 'VARCHAR(45) NULL', 'details' => 'VARCHAR(500) NULL'),
            'admins' => array('is_active' => 'TINYINT(1) NOT NULL DEFAULT 1', 'full_name' => 'VARCHAR(140) NULL', 'created_at' => 'DATETIME NULL'),
            'kk_members' => array('status' => "VARCHAR(20) NOT NULL DEFAULT 'aktif'", 'status_changed_at' => 'DATETIME NULL', 'domicile_status' => 'VARCHAR(20) NULL', 'resident_since' => 'DATE NULL'),
            'citizens' => array('notify_email' => 'TINYINT(1) NOT NULL DEFAULT 0'),
            'complaints' => array('is_anonymous' => 'TINYINT(1) NOT NULL DEFAULT 0'),
        );
        foreach ($columns as $table => $fields) {
            if (!$this->db->table_exists($table)) continue;
            foreach ($fields as $field => $definition) {
                if (!$this->db->field_exists($field, $table)) $this->db->query('ALTER TABLE `' . $table . '` ADD COLUMN `' . $field . '` ' . $definition);
            }
        }
        // Retire the former pengurus Super Admin role; server ownership is separate.
        $role = $this->db->query("SHOW COLUMNS FROM `admins` LIKE 'role'")->row_array();
        if($role){$this->db->where('role','super_admin')->update('admins',array('role'=>'admin'));}
        if ($role && strpos($role['Type'], 'super_admin') !== FALSE && strpos($role['Type'], 'enum(') === 0) {
            $this->db->query("ALTER TABLE `admins` MODIFY `role` ENUM('admin','operator','bendahara','ketua','sekretaris','keamanan','inventaris','sosial','auditor') NOT NULL DEFAULT 'admin'");
        }
        $this->db->data_cache = array();
        $this->sync_features();
    }

    /**
     * Sinkronkan katalog fitur (config/features.php) ke app_features + tenant_features.
     * Pemasangan pertama: semua fitur yang sudah dipakai diaktifkan (perilaku lama tidak berubah).
     * Kode baru setelahnya: default OFF (BR-05). Status yang sudah ada tidak pernah ditimpa.
     */
    public function sync_features()
    {
        if (!$this->db->table_exists('app_features') || !$this->db->table_exists('tenant_features')) return;
        $this->config->load('features', TRUE);
        $catalog = (array) $this->config->item('feature_catalog', 'features');
        $tenant = (int) ($this->config->item('feature_tenant_id', 'features') ?: 1);
        $first_install = !$this->db->where('tenant_id', $tenant)->count_all_results('tenant_features');
        $order = 0;
        foreach ($catalog as $code => $f) {
            $order += 10;
            $row = array('name' => $f['name'], 'category' => $f['category'], 'route_key' => $f['admin'][0] ?? NULL, 'icon' => $f['icon'] ?? NULL, 'sort_order' => $order, 'is_core' => 0, 'is_implemented' => 1);
            $exists = $this->db->select('id')->get_where('app_features', array('code' => $code))->row_array();
            if ($exists) $this->db->where('id', (int) $exists['id'])->update('app_features', $row);
            else $this->db->insert('app_features', $row + array('code' => $code));
        }
        $ids = array();
        foreach ($this->db->select('id,code')->get('app_features')->result_array() as $r) $ids[$r['code']] = (int) $r['id'];
        foreach ($catalog as $code => $f) {
            $parent = !empty($f['parent']) && isset($ids[$f['parent']]) ? $ids[$f['parent']] : NULL;
            $this->db->where('id', $ids[$code])->update('app_features', array('parent_id' => $parent));
            $has_resident = !empty($f['warga']);
            $this->db->query('INSERT IGNORE INTO tenant_features (tenant_id, feature_id, enabled, visible_to_residents, visible_to_staff) VALUES (?,?,?,?,1)',
                array($tenant, $ids[$code], $first_install ? 1 : 0, $has_resident ? 1 : 0));
        }
    }

    /** Daftar akun pengurus aktif untuk pilihan PIC. */
    public function staff_options($roles = NULL)
    {
        $this->db->select('id,username,role' . ($this->db->field_exists('full_name', 'admins') ? ',full_name' : ''));
        if ($this->db->field_exists('is_active', 'admins')) $this->db->where('is_active', 1);
        if ($roles) $this->db->where_in('role', $roles);
        $out = array();
        foreach ($this->db->order_by('username')->get('admins')->result_array() as $row) $out[(int) $row['id']] = ($row['full_name'] ?? '') ?: $row['username'];
        return $out;
    }

    /** Keluarga terverifikasi beserta anggota (dengan id & status) untuk modul mutasi/bansos. */
    public function family_by_owner($citizen_id)
    {
        $family = $this->db->get_where('kk_families', array('owner_citizen_id' => (int) $citizen_id))->row_array();
        return $family ? $this->family_detail($family) : NULL;
    }

    public function family_by_id($family_id)
    {
        $family = $this->db->get_where('kk_families', array('id' => (int) $family_id))->row_array();
        return $family ? $this->family_detail($family) : NULL;
    }

    private function family_detail($family)
    {
        $family['fields'] = json_decode($family['fields_json'], TRUE) ?: array();
        $family['members'] = array();
        foreach ($this->db->order_by('id')->get_where('kk_members', array('family_id' => (int) $family['id']))->result_array() as $row) {
            $row['fields'] = json_decode($row['fields_json'], TRUE) ?: array();
            $row['status'] = $row['status'] ?? 'aktif';
            $family['members'][] = $row;
        }
        return $family;
    }

    /** Ringkasan monitoring untuk dashboard & serah terima. Semua angka dari data tersimpan. */
    public function monitoring()
    {
        $this->ensure();
        $count = function ($table, $where = array(), $in = array()) {
            if (!$this->db->table_exists($table)) return 0;
            foreach ($where as $k => $v) $this->db->where($k, $v);
            foreach ($in as $k => $v) $this->db->where_in($k, $v);
            return (int) $this->db->count_all_results($table);
        };
        $today = date('Y-m-d');
        $arrears = $this->db->query("SELECT COUNT(*) n, COALESCE(SUM(amount_due-amount_paid),0) total FROM dues WHERE amount_paid < amount_due AND period < ?", array(date('Y-m')))->row_array();
        $balance = $this->db->query("SELECT COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE -amount END),0) total FROM transactions WHERE status='verified'")->row_array();
        $members = $this->db->table_exists('kk_members') ? (int) $this->db->where('status', 'aktif')->count_all_results('kk_members') : 0;
        return array(
            'kk_active' => $count('kk_families'),
            'members_active' => $members,
            'mutations_pending' => $count('resident_mutations', array(), array('status' => array('submitted', 'in_review'))),
            'complaints_open' => $count('complaints', array(), array('status' => array('submitted', 'triaged', 'assigned', 'in_progress', 'waiting_resident', 'reopened'))),
            'arrears_count' => (int) $arrears['n'],
            'arrears_total' => (float) $arrears['total'],
            'cash_balance' => (float) $balance['total'],
            'actions_overdue' => $this->db->table_exists('action_items') ? (int) $this->db->where_in('status', array('open', 'in_progress'))->where('due_date <', $today)->count_all_results('action_items') : 0,
            'loans_overdue' => $this->db->table_exists('asset_loans') ? (int) $this->db->where('status', 'borrowed')->where('due_date <', $today)->count_all_results('asset_loans') : 0,
            'purchases_pending' => $count('purchase_requests', array('status' => 'submitted')),
            'programs_active' => $count('work_programs', array('status' => 'aktif')),
            'patrol_today' => $count('patrol_schedules', array('patrol_date' => $today)),
            'last_backup' => $this->db->table_exists('backup_runs') ? $this->db->where('status', 'success')->order_by('id', 'DESC')->limit(1)->get('backup_runs')->row_array() : NULL,
        );
    }
}
