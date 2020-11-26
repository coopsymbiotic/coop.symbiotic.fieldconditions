<?php

class CRM_Fieldconditions_Page_Ajax_FieldFilterValues extends CRM_Core_Page {

  public function run() {
    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive');
    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getSettings($map_id);
    $params = [];

    // Normally a request should have all possible values
    // for a given mapping.
    //
    // Ex: if the mapping has fields A, B and C, then the query might send:
    // A=1, B=2, C=null.
    foreach ($settings['fields'] as $key => $val) {
      $column_name = $val['column_name'];
      if ($v = CRM_Utils_Request::retrieveValue($column_name, 'String')) {
        $params[$column_name] = $v;
      }
    }

    if (empty($params)) {
      // Value was deselected, return all possible values
      $rows = [];

      foreach ($settings['fields'] as $field) {
        $options = civicrm_api3($field['entity_name'], 'getoptions', [
          'field' => $field['entity_field'],
        ])['values'];

        foreach ($options as $key => $label) {
          $rows[] = [
            $field['column_name'] => [
              'label' => $label,
              'value' => $key,
            ],
          ];
        }
      }
    }
    else {
      $rows = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($map_id, $params);
    }

    echo json_encode($rows);
    CRM_Utils_System::civiExit();
  }

}
