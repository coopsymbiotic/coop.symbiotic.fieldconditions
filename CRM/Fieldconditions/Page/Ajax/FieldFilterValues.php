<?php

class CRM_Fieldconditions_Page_Ajax_FieldFilterValues extends CRM_Core_Page {

  public function run() {
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);
    $source_value = CRM_Utils_Array::value('source_value', $_REQUEST);
    $dest_value = CRM_Utils_Array::value('dest_value', $_REQUEST);

    $rows = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterValues($map_id, $source_value, $dest_value);

    echo json_encode($rows);
    CRM_Utils_System::civiExit();
  }

}
