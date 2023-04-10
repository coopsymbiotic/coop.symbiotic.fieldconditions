<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Advimport_Generic extends CRM_Advimport_Helper_PHPExcel {

  function getDataFromFile($file, $delimiter = '', $encoding = 'UTF-8') {
    [$headers, $data] = parent::getDataFromFile($file, $delimiter, $encoding);

    // Add the fieldcondition_id as a column and a value for each row
    // because later on, we will not have access to the helperDefinition
    array_unshift($headers, 'fieldcondition_id');
    foreach ($data as &$d) {
      $d['fieldcondition_id'] = $this->helperDefinition['fieldcondition_id'];
    }

    return [$headers, $data];
  }

  /**
   * Available fields.
   */
  public function getMapping(&$form) {
    $helperDefinition = $this->getHelperDefinition();

    if (!$helperDefinition) {
      return [];
    }

    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getSettings($helperDefinition['fieldcondition_id']);
    $mapping = [];

    foreach ($settings['fields'] as $field) {
      $mapping[$field['column_name']] = [
        'label' => $field['field_label'],
        'field' => $field['column_name'],
      ];
    }

    // Hide the fieldcondition_id field
    if ($form->elementExists('fieldcondition_id')) {
      $form->removeElement('fieldcondition_id');
    }

    return $mapping;
  }

  function processItem($params) {
    if (empty($params['fieldcondition_id'])) {
      throw new Exception('Missing fieldcondition_id');
    }

    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getSettings($params['fieldcondition_id']);

    // Check if the OptionValues already exist
    foreach ($settings['fields'] as $field) {
      $this->createOptionValue($field['entity_name'], $field['entity_field'], $params[$field['column_name']]);
    }

    // @todo Check if the combo already exists
    $values = [];

    foreach ($settings['fields'] as $field) {
      $values[$field['column_name']] = $this->getOptionValue($field['entity_name'], $field['entity_field'], $params[$field['column_name']]);
    }

    // Check if the combination already exists
    $test = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($params['fieldcondition_id'], $values);

    if (empty($test)) {
      CRM_Fieldconditions_BAO_Fieldconditions::addFieldFilterValue($params['fieldcondition_id'], $values);
    }
  }

  /**
   *
   */
  private function getOptionValue($entityName, $entityField, $value) {
    $result = civicrm_api3($entityName, 'getoptions', [
      'field' => $entityField,
      'option.limit' => 0,
    ]);

    if (!empty($result['values'])) {
      $pos = array_search($value, $result['values']);

      if ($pos !== NULL) {
        return $pos;
      }
    }

    throw new Exception('OptionValue not found for label = ' . $value);
  }

  /**
   *
   */
  private function createOptionValue($entityName, $entityField, $value) {
    // Check if it already exists
    $result = civicrm_api3($entityName, 'getoptions', [
      'field' => $entityField,
      'option.limit' => 0,
    ]);

    if (!empty($result['values'])) {
      $pos = array_search($value, $result['values']);

      if ($pos !== FALSE) {
        return $pos;
      }
    }

    // Get the OptionGroup ID
    if (preg_match('/^custom_(\d+)$/', $entityField, $matches)) {
      $custom_field_id = $matches[1];

      $customField = \Civi\Api4\CustomField::get(FALSE)
        ->addSelect('option_group_id')
        ->addWhere('id', '=', $custom_field_id)
        ->execute()
        ->single();

      // Assuming integer values, get the MAX previous value
      $last = \Civi\Api4\OptionValue::get(FALSE)
        ->addSelect('value')
        ->addWhere('option_group_id', '=', $customField['option_group_id'])
        ->addOrderBy('id', 'DESC')
        ->setLimit(1)
        ->execute()
        ->first();

      $next = $last['value'] + 1;

      // Create the Option Value
      \Civi\Api4\OptionValue::create(FALSE)
        ->addValue('option_group_id', $customField['option_group_id'])
        ->addValue('label', $value)
        ->addValue('value', $next)
        ->addValue('weight', $next)
        ->execute();

      return $next;
    }
    else {
      throw new Exception('Expected custom_XX');
    }
  }

}
