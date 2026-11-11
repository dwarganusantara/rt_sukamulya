ALTER TABLE `service_requests` MODIFY `type` varchar(40) NOT NULL;
CREATE TABLE IF NOT EXISTS `service_request_details` (
  `request_id` int unsigned NOT NULL,
  `applicant_name` varchar(140) NOT NULL,
  `address` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `letter_number` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
