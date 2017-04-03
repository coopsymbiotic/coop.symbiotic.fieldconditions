<?php

class CRM_Fieldconditions_Page_Ajax_FieldFilterValues extends CRM_Core_Page {

  public function run() {
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);

    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getMapSettings($map_id);
    $params = [];

    // Normally a request should have all possible values
    // for a given mapping.
    //
    // Ex: if the mapping has fields A, B and C, then the query might send:
    // A=1, B=2, C=null.
    foreach ($settings['fields'] as $key => $val) {
      $db_column_name = $val['db_column_name'];
      $params[$db_column_name] = CRM_Utils_Array::value($db_column_name, $_REQUEST);
    }

    $rows = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($map_id, $params);

    echo json_encode($rows);
    CRM_Utils_System::civiExit();
  }

}
