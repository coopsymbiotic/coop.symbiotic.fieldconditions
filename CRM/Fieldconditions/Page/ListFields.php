<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Page_ListFields extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Field Condition (filter) Fields'));

    $fields = [];
    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive');

    $dao = CRM_Core_DAO::executeQuery('SELECT id, type, settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      CRM_Core_Error::fatal('Invalid map_id');
    }

    if ($dao->settings) {
      $settings = json_decode($dao->settings, TRUE);

      foreach ($settings['fields'] as &$field) {
        $meta = CRM_Fieldconditions_BAO_Fieldconditions::getFieldMeta($field['field_name']);

        $field['entity'] = $meta['entity_name'];
        $field['field_label'] = $meta['label'];
      }

      if (!empty($settings['fields'])) {
        $this->assign('fields', $settings['fields']);
      }
    }

    $this->assign('map_id', $map_id);

    parent::run();
  }

}
