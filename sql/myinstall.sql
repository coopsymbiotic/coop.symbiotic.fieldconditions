CREATE TABLE `civicrm_fieldcondition_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Field condition map',
  `source_field_id` int(10) unsigned NOT NULL COMMENT 'Source field',
  `dest_field_id` int(10) unsigned NOT NULL COMMENT 'Destination field',
  CONSTRAINT `FK_civicrm_fieldcondition_map_source_field_id` FOREIGN KEY (`source_field_id`) REFERENCES `civicrm_custom_field` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_fieldcondition_map_dest_field_id` FOREIGN KEY (`dest_field_id`) REFERENCES `civicrm_custom_field` (`id`) ON DELETE CASCADE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `civicrm_fieldcondition_valuefilter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Generic row ID',
  `fieldcondition_map_id` int(10) unsigned NOT NULL COMMENT 'Field condition map',
  `source_value` int(10) unsigned NOT NULL COMMENT 'Source field value',
  `dest_value` int(10) unsigned NOT NULL COMMENT 'Possible destination field value',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
