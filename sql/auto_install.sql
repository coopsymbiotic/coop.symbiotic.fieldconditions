-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_fieldcondition`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_fieldcondition
-- *
-- * Field Condition map
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fieldcondition` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FieldCondition map ID',
  `type` varchar(32) COMMENT 'Type of fieldcondition (ex: filter)',
  `name` varchar(255) COMMENT 'Map name visible to admins',
  `settings` text COMMENT 'JSON blob with map settings',
  PRIMARY KEY (`id`)
)
ENGINE=InnoDB;
