-- Hak Akses Menu per peran (Akun & Hak Akses → Detail Hak Akses).
-- Non-destruktif: tabel baru saja. Tanpa baris = menu mengikuti RBAC rt_access (aktif).
-- Baris allowed=0 hanya MEMATIKAN menu untuk peran; tidak pernah menambah izin di luar RBAC.
-- Tabel juga dibuat otomatis oleh libraries/Menu_access.php::ensure().
-- Rollback: DROP TABLE role_menu_access; (seluruh peran kembali ke bawaan RBAC).
CREATE TABLE IF NOT EXISTS role_menu_access (
  tenant_id INT UNSIGNED NOT NULL,
  role VARCHAR(40) NOT NULL,
  menu_path VARCHAR(120) NOT NULL,
  allowed TINYINT(1) NOT NULL DEFAULT 1,
  updated_by INT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id, role, menu_path),
  KEY idx_role_menu_role (tenant_id, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
