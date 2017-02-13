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

    $dao = CRM_Core_DAO::executeQuery('SELECT map.*,
        fsrc.option_group_id as source_option_group_id,
        fdst.option_group_id as dest_option_group_id
      FROM civicrm_fieldcondition_map map
      LEFT JOIN civicrm_custom_field fsrc ON (fsrc.id = map.source_field_id)
      LEFT JOIN civicrm_custom_field fdst ON (fdst.id = map.dest_field_id)
      WHERE map.id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      CRM_Core_Error::fatal('map_id not found');
    }

    $source_result = civicrm_api3('option_value', 'get', [
      'option_group_id' => $dao->source_option_group_id,
      'option.limit' => 0,
    ]);

    $dest_result = civicrm_api3('option_value', 'get', [
      'option_group_id' => $dao->dest_option_group_id,
      'option.limit' => 0,
    ]);

    $source_options = [];
    foreach ($source_result['values'] as $key => $val) {
      $source_options[$val['value']] = $val['label'];
    }

    $dest_options = [];
    foreach ($dest_result['values'] as $key => $val) {
      $dest_options[$val['value']] = $val['label'];
    }

    $this->add('hidden', 'map_id', $map_id);

    // NB: singular
    $this->add('select', 'source_option', 'Source', $source_options, TRUE,
      array('id' => 'source_option', 'class' => 'crm-select2')
    );

    // NB: plural
    $this->add('select', 'dest_options', 'Destination', $dest_options, TRUE,
      array('id' => 'dest_options', 'multiple' => 'multiple', 'class' => 'crm-select2')
    );

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
    $source_option = $values['source_option'];

    foreach ($values['dest_options'] as $dest) {
      CRM_Core_DAO::executeQuery('INSERT INTO civicrm_fieldcondition_valuefilter (fieldcondition_map_id, source_value, dest_value) VALUES (%1, %2, %3)', [
        1 => [$map_id, 'Positive'],
        2 => [$source_option, 'Positive'],
        3 => [$dest, 'Positive'],
      ]);
    }

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
