<?php

// phpcs:disable
use CRM_Fieldconditions_ExtensionUtil as E;
// phpcs:enable

class CRM_Fieldconditions_BAO_FieldCondition extends CRM_Fieldconditions_DAO_FieldCondition {

  /**
   * Create a new FieldCondition based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Fieldconditions_DAO_FieldCondition|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Fieldconditions_DAO_FieldCondition';
    $entityName = 'FieldCondition';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  */

}
