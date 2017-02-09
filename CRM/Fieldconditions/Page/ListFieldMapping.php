<?php

class CRM_Fieldconditions_Page_ListFieldMapping extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Field conditionals'));

    $maps = [];

    $dao = CRM_Core_DAO::executeQuery('SELECT m.*,
        fsrc.label as source_field_label, gsrc.title as source_group_title,
        fdst.label as dest_field_label, gdst.title as dest_group_title
      FROM civicrm_fieldcondition_map m
      INNER JOIN civicrm_custom_field fsrc ON (fsrc.id = m.source_field_id)
      INNER JOIN civicrm_custom_group gsrc ON (gsrc.id = fsrc.custom_group_id)
      INNER JOIN civicrm_custom_field fdst ON (fdst.id = m.dest_field_id)
      INNER JOIN civicrm_custom_group gdst ON (gdst.id = fdst.custom_group_id)');

    while ($dao->fetch()) {
      $maps[] = [
        'id' => $dao->id,
        'source_field_id' => $dao->source_field_id,
        'source_field_label' => $dao->source_field_label,
        'source_group_title' => $dao->source_group_title,
        'dest_field_id' => $dao->dest_field_id,
        'dest_field_label' => $dao->dest_field_label,
        'dest_group_title' => $dao->dest_group_title,
      ];
    }

    $this->assign('field_maps', $maps);

    parent::run();
  }

}
