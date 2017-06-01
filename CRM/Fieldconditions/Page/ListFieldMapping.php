<?php

class CRM_Fieldconditions_Page_ListFieldMapping extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Field conditionals'));

    $maps = [];

    $dao = CRM_Core_DAO::executeQuery('SELECT m.* FROM civicrm_fieldcondition_map m');

    while ($dao->fetch()) {
      $settings = json_decode($dao->settings);
      $tmp = [];

      foreach ($settings->fields as $field) {
        $tmp[] = $field->field_label;
      }

      $maps[] = [
        'id' => $dao->id,
        'map_type' => $dao->map_type,
        'settings' => ts('Fields:') . ' ' . implode(', ', $tmp),
      ];
    }

    $this->assign('field_maps', $maps);

    parent::run();
  }

}
