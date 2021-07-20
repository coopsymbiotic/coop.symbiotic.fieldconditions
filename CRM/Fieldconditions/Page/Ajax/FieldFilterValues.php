<?php

class CRM_Fieldconditions_Page_Ajax_FieldFilterValues extends CRM_Core_Page {

  public function run() {
    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'String');

    // Non-numeric map_id when there are fieldconditiosn on addresses
    if (!is_numeric($map_id)) {
      $parts = explode('-', $map_id);
      $map_id = $parts[0];
    }

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

    $rows = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($map_id, $params);

    if (empty($params)) {
      // This lets the frontend know that we are displaying all options
      // For situations where we want to unselect the only available option.
      foreach ($rows as &$row) {
        $row['column_name']['all'] = 1;
      }
    }

    echo json_encode($rows);
    CRM_Utils_System::civiExit();
  }

}
