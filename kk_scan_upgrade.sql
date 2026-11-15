-- Back up first. Additive only: citizens remains the login/account table.
CREATE TABLE IF NOT EXISTS kk_families (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nomor_kk VARCHAR(16) NOT NULL UNIQUE,
 owner_citizen_id INT UNSIGNED NOT NULL UNIQUE,
 fields_json TEXT NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS kk_members (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 family_id INT UNSIGNED NOT NULL,
 nik VARCHAR(16) NOT NULL UNIQUE,
 fields_json TEXT NOT NULL,
 KEY (family_id),
 FOREIGN KEY (family_id) REFERENCES kk_families(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS kk_scan_requests (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 citizen_id INT UNSIGNED NOT NULL,
 status ENUM('pending','approved','revision_requested','rejected') NOT NULL DEFAULT 'pending',
 proposed_data_json MEDIUMTEXT NOT NULL,
 review_notes TEXT NULL,
 reviewed_by INT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY (citizen_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
