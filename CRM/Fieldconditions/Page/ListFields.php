<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Page_ListFields extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Field Condition (filter) Fields'));

    $fields = [];
    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive');
    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getSettings($map_id);

    if (!empty($settings['fields'])) {
      $this->assign('fields', $settings['fields']);
    }

    $this->assign('map_id', $map_id);

    parent::run();
  }

}
