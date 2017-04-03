<?php

class CRM_Fieldconditions_Page_ListFields extends CRM_Core_Page {

  public function run() {
    // FIXME: use CRM_Utils_Request?
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);

    CRM_Utils_System::setTitle(ts('Fieldcondition (valuefilter) fields'));

    $fields = [];

    $dao = CRM_Core_DAO::executeQuery('SELECT id, map_type, settings FROM civicrm_fieldcondition_map WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      CRM_Core_Error::fatal('Invalid map_id');
    }

    if ($dao->settings) {
      $settings = json_decode($dao->settings, TRUE);

      if (!empty($settings['fields'])) {
        $this->assign('fields', $settings['fields']);
      }
    }

    $this->assign('map_id', $map_id);

    parent::run();
  }

}
