<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_Page_ListFieldMapping extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Field conditions'));

    if (CRM_Utils_Request::retrieveValue('delete', 'Integer', NULL, FALSE, 'POST') && CRM_Utils_Request::retrieveValue('confirmed', 'Integer', NULL, FALSE, 'POST')) {
      // @todo Move to BAO
      $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive');
      CRM_Core_DAO::executeQuery('DELETE FROM civicrm_fieldcondition WHERE id = %1', [
        1 => [$map_id, 'Positive'],
      ]);
      CRM_Core_DAO::executeQuery('DROP TABLE civicrm_fieldcondition_' . $map_id);
      CRM_Core_Session::setStatus(E::ts('The field condition has been deleted'), '', 'success');
      $url = CRM_Utils_System::url('civicrm/admin/fieldconditions', 'reset=1');
      CRM_Utils_System::redirect($url);
    }

    $fieldConditions = (array) \Civi\Api4\FieldCondition::get(FALSE)->execute();
    $this->assign('field_maps', $fieldConditions);

    parent::run();
  }

}
