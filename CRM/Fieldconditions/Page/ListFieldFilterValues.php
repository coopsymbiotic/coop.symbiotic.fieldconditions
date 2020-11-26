<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Page_ListFieldFilterValues extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Field filter values'));
    CRM_Core_Resources::singleton()->addStyleFile('coop.symbiotic.fieldconditions', 'fieldconditions.admin.css');

    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive');
    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getMapSettings($map_id);
    $values = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($map_id);

    foreach ($settings['fields'] as &$field) {
      $meta = CRM_Fieldconditions_BAO_Fieldconditions::getFieldMeta($field['field_name']);
      $field['label'] = $meta['label'];
    }

    $this->assign('map_id', $map_id);
    $this->assign('values', $values);
    $this->assign('settings', $settings);

    parent::run();
  }

}
