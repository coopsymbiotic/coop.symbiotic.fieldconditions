<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Fieldconditions_Form_FieldFilterValue extends CRM_Core_Form {

  public function setDefaultValues() {
    $defaults = [];

    $source_value = CRM_Utils_Array::value('source_value', $_REQUEST);

    if ($source_value) {
      $defaults['source_option'] = intval($source_value);
    }

    return $defaults;
  }

  public function buildQuickForm() {
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST);

    $this->add('hidden', 'map_id', $map_id);

    $dao = CRM_Core_DAO::executeQuery('SELECT *
      FROM civicrm_fieldcondition_map map
      WHERE map.id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      CRM_Core_Error::fatal('map_id not found');
    }

    $map_settings = json_decode($dao->settings);

    foreach ($map_settings->fields as $field) {
      $t = explode('.', $field->field_name);

      $result = civicrm_api3('CustomField', 'getoptions', [
        'field' => $t[1],
        'option.limit' => 0,
      ]);

      $options = ['' => ts('- select -')] + $result['values'];

      $this->add('select', $field->db_column_name, $field->field_label, $options, TRUE,
        array('id' => $field->db_column_name, 'class' => 'crm-select2')
      );
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    $map_id = $values['map_id'];

    // FIXME: code duplication with buildForm.
    $dao = CRM_Core_DAO::executeQuery('SELECT *
      FROM civicrm_fieldcondition_map map
      WHERE map.id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      CRM_Core_Error::fatal('map_id not found');
    }

    $map_settings = json_decode($dao->settings);

    $params = [];
    $sql_fields = [];
    $sql_placeholders = [];

    foreach ($map_settings->fields as $key => $field) {
      $sql_fields[] = $field->db_column_name;
      $params[$key] = [$values[$field->db_column_name], 'Positive'];
      $sql_placeholders[] = '%' . $key;
    }

    $sql = 'INSERT INTO civicrm_fieldcondition_valuefilter_' . $map_id . ' (' . implode(',', $sql_fields) . ')
      VALUES (' . implode(',', $sql_placeholders) . ')';

    CRM_Core_DAO::executeQuery($sql, $params);

    CRM_Core_Session::setStatus(ts('Saved'), '', 'success');
    parent::postProcess();

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/fieldconditions/filter-values', ['map_id' => $map_id]));
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
