<?php

class CRM_Fieldconditions_Page_ListFieldFilterValues extends CRM_Core_Page {

  public function run() {
    // FIXME: use CRM_Utils_Request?
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);

    CRM_Utils_System::setTitle(ts('Field filter values'));

    CRM_Core_Resources::singleton()->addStyleFile('coop.symbiotic.fieldconditions', 'fieldconditions.admin.css');

    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getMapSettings($map_id);
    $values = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($map_id);

    $this->assign('map_id', $map_id);
    $this->assign('values', $values);
    $this->assign('settings', $settings);

    parent::run();
  }

}
