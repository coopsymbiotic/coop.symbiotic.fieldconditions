<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Fieldconditions_Form_AddField extends CRM_Core_Form {
  public function buildQuickForm() {
    $map_id = CRM_Utils_Array::value('map_id', $_REQUEST); // FIXME

    $options = [];
    $options[''] = ts('- select -');

    $t = $this->get_fields();
    $options = array_merge($options, $t);

    $this->add('select', 'field_name', ts('Field'), $options, TRUE,
      array('id' => 'field', 'class' => 'crm-select2')
    );

    $this->add('text', 'field_label', ts('Label'), NULL, TRUE);
    $this->add('text', 'db_column_name', ts('DB name'), NULL, TRUE);

    $this->add('hidden', 'map_id', $map_id);

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

  /**
   * Returns a list of all available CiviCRM fields.
   *
   * TODO: remove the fields already in the mapping.
   */
  private function get_fields() {
    $entities = [
      'activity' => ts('Activity'),
      'contact' => ts('Contact'),
      'address' => ts('Address'),
      'contribution' => ts('Contribution'),
      'event' => ts('Event'),
      'case' => ts('Case'),
    ];

    $options = [];

    foreach ($entities as $entity_name => $entity_label) {
      $result = civicrm_api3($entity_name, 'getfields');

      foreach ($result['values'] as $key => $val) {
        $name = $entity_name . '.' . $key;
        $options[$name] = $entity_label . ' > ' . $val['title'];
      }
    }

    return $options;
  }

  public function postProcess() {
    $values = $this->exportValues();

    $parts = explode('.', $values['field']);
    $map_id = intval($values['map_id']); // FIXME use proper validation?

    // TODO: create civicrm_fieldcondition_map_[id]
    // TODO: add a field in it, and respect the input's type (ex: text or int..)

    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition_map WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    if (!isset($settings['fields'])) {
      $settings['fields'] = [];
    }

    $settings['fields'][] = [
      'field_name' => $values['field_name'],
      'field_label' => $values['field_label'],
      'db_column_name' => $values['db_column_name'],
    ];

    CRM_Core_DAO::executeQuery('UPDATE civicrm_fieldcondition_map SET settings = %1 WHERE id = %2', [
      1 => [json_encode($settings), 'String'],
      2 => [$map_id, 'Positive'],
    ]);

    CRM_Core_Session::setStatus(ts('Saved'), '', 'success');

    // FIXME: redirect back to /fields

    parent::postProcess();
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
