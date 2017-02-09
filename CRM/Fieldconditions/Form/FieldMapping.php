<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Fieldconditions_Form_FieldMapping extends CRM_Core_Form {

  public function buildQuickForm() {

    $result = civicrm_api3('custom_field', 'get', [
      'option.limit' => 0,
      'api.CustomGroup.get' => [],
    ]);

    $options = [];
    $options[''] = ts('- select -');

    foreach ($result['values'] as $key => $val) {
      $group_title = $val['api.CustomGroup.get']['values'][0]['title'];
      $options[$key] = $group_title . ' : ' . $val['label'] . (empty($val['is_active']) ? ' ' . ts('(disabled)', ['domain' => 	'coop.symbiotic.fieldconditions']) : '');
    }

    $this->add('select', 'source_field_id', ts('Source field', ['domain' =>  'coop.symbiotic.fieldconditions']), $options, TRUE,
      array('id' => 'source_field_id', 'class' => 'crm-select2')
    );

    $this->add('select', 'dest_field_id', ts('Destination field', ['domain' =>  'coop.symbiotic.fieldconditions']), $options, TRUE,
      array('id' => 'dest_field_id', 'class' => 'crm-select2')
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_fieldcondition_map (source_field_id, dest_field_id) VALUES (%1, %2)', [
      1 => [$values['source_field_id'], 'Positive'],
      2 => [$values['dest_field_id'], 'Positive'],
    ]);

    CRM_Core_Session::setStatus(ts('Saved'), '', 'success');

    parent::postProcess();

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/fieldconditions', "reset=1"));
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
