CREATE TABLE `civicrm_fieldcondition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Field condition map',
  `type` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COMMENT 'Map name for admins' COLLATE utf8_unicode_ci DEFAULT NULL,
  `settings` text DEFAULT '' COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
