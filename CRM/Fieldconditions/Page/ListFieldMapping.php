<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Page_ListFieldMapping extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Field conditions'));

    $maps = [];

    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_fieldcondition m');

    while ($dao->fetch()) {
      $settings = json_decode($dao->settings);
      $tmp = [];

      foreach ($settings->fields as $field) {
        $tmp[] = $field->field_label;
      }

      $maps[] = [
        'id' => $dao->id,
        'type' => $dao->type,
        'name' => $dao->name,
        'settings' => ts('Fields:') . ' ' . implode(', ', $tmp),
      ];
    }

    $this->assign('field_maps', $maps);

    parent::run();
  }

}
