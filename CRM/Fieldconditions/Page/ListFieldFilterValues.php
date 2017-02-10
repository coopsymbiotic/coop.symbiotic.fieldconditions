<?php

class CRM_Fieldconditions_Page_ListFieldFilterValues extends CRM_Core_Page {

  public function run() {
    // FIXME: use CRM_Utils_Request?
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);
    $source_value = CRM_Utils_Array::value('source_val', $_REQUEST);
    $dest_value = CRM_Utils_Array::value('dest_val', $_REQUEST);

    $values = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterValues($map_id, $source_value, $dest_value);

    // Re-sort by 'source_id' so that it is easier to display
    $rows = [];

    foreach ($values as $key => $val) {
      $source_value = $val['source_value'];

      if (!isset($rows[$source_value])) {
        $rows[$source_value] = [
          'source_label' => $val['source_label'],
          'source_value' => $val['source_value'],
          'values' => [],
        ];
      }

      $rows[$source_value]['values'][] = $val;
    }

    CRM_Utils_System::setTitle(ts('Field filter values'));

    CRM_Core_Resources::singleton()->addStyleFile('coop.symbiotic.fieldconditions', 'fieldconditions.admin.css');

    $this->assign('map_id', $map_id);
    $this->assign('rows', $rows);

    parent::run();
  }

}
