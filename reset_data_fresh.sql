-- ============================================================================
-- RESET DATA "FRESH" UNTUK HOSTING — RT/RW Digital
-- PERINGATAN: menghapus data warga, KK, layanan, keuangan, operasional, dan log
-- secara PERMANEN. Jalankan HANYA pada salinan database untuk hosting, setelah backup.
--
-- DIPERTAHANKAN: admins, platform_admins (akun admin), officials (Pengurus RT),
--   important_contacts (Kontak Penting), posts (Pengumuman/Agenda/Galeri), pages,
--   quick_links, letter_types, settings (kecuali favorit per-warga), menu/fitur/hak akses,
--   paket langganan, tema & tampilan.
-- Superadmin tersimpan di application/config (platform_owner/central_admin), tidak tersentuh.
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- Akun warga
TRUNCATE TABLE citizen_resident_links;
TRUNCATE TABLE citizen_field_values;
TRUNCATE TABLE citizen_notice_reads;
TRUNCATE TABLE account_invitations;
TRUNCATE TABLE citizens;

-- KK, penduduk, rumah & domisili
TRUNCATE TABLE resident_mutations;
TRUNCATE TABLE kk_scan_requests;
TRUNCATE TABLE kk_members;
TRUNCATE TABLE kk_families;
TRUNCATE TABLE rt_family_residences;
TRUNCATE TABLE rt_houses;

-- Layanan surat & pengaduan
TRUNCATE TABLE service_request_documents;
TRUNCATE TABLE service_request_details;
TRUNCATE TABLE service_requests;
TRUNCATE TABLE complaint_updates;
TRUNCATE TABLE complaints;

-- Keuangan
TRUNCATE TABLE due_reminders;
TRUNCATE TABLE due_payments;
TRUNCATE TABLE dues;
TRUNCATE TABLE billing_campaigns;
TRUNCATE TABLE cash_reconciliations;
TRUNCATE TABLE transactions;

-- Bantuan sosial
TRUNCATE TABLE aid_recipients;
TRUNCATE TABLE aid_programs;

-- Chat & notifikasi
TRUNCATE TABLE chat_messages;
TRUNCATE TABLE chat_conversations;
TRUNCATE TABLE chat_presence;
TRUNCATE TABLE notification_outbox;

-- Operasional RT (Kontak Penting dipertahankan)
TRUNCATE TABLE program_agendas;
TRUNCATE TABLE program_milestones;
TRUNCATE TABLE work_programs;
TRUNCATE TABLE patrol_schedules;
TRUNCATE TABLE incident_reports;
TRUNCATE TABLE purchase_items;
TRUNCATE TABLE goods_receipts;
TRUNCATE TABLE purchase_requests;
TRUNCATE TABLE stock_movements;
TRUNCATE TABLE asset_loans;
TRUNCATE TABLE assets;
TRUNCATE TABLE activity_note_versions;
TRUNCATE TABLE activity_notes;
TRUNCATE TABLE action_items;
TRUNCATE TABLE document_versions;
TRUNCATE TABLE documents;
TRUNCATE TABLE handover_signoffs;
TRUNCATE TABLE handover_sessions;

-- Log & riwayat
TRUNCATE TABLE activity_logs;
TRUNCATE TABLE platform_login_attempts;
TRUNCATE TABLE backup_runs;
TRUNCATE TABLE settings_history;
TRUNCATE TABLE subscription_audit_logs;
TRUNCATE TABLE tenant_feature_audit;

-- Setting per-warga (favorit dashboard warga)
DELETE FROM settings WHERE setting_key LIKE 'dashboard\_favorites\_%';

SET FOREIGN_KEY_CHECKS = 1;
