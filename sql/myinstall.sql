CREATE TABLE `civicrm_fieldcondition_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Field condition map',
  `map_type` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `settings` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `civicrm_fieldcondition_valuefilter_1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Generic row ID',
  `probleme` int(10) unsigned NOT NULL,
  `etiologie` int(10) unsigned NOT NULL,
  `symptome` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
