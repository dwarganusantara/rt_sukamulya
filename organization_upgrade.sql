ALTER TABLE `officials`
  ADD COLUMN `description` varchar(500) NOT NULL DEFAULT '' AFTER `position`;
