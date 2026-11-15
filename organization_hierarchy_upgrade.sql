ALTER TABLE `officials`
  ADD COLUMN `parent_id` int(10) unsigned DEFAULT NULL AFTER `photo`,
  ADD COLUMN `icon` varchar(20) NOT NULL DEFAULT '👤' AFTER `description`,
  ADD KEY `idx_official_parent` (`parent_id`);
