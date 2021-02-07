<?php

use CRM_Fieldconditions_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Fieldconditions_Form_AddField extends CRM_Core_Form {

  protected $map_id;

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('Field Condition: Add Field'));

    $map_id = CRM_Utils_Request::retrieveValue('map_id', 'Positive', $this);

    $options = [];
    $options[''] = ts('- select -');

    $t = $this->get_fields();
    $options = array_merge($options, $t);

    $this->add('select', 'field_name', ts('Field'), $options, TRUE,
      array('id' => 'field', 'class' => 'crm-select2')
    );

    // FIXME: Why? Was maybe meant to have clean SQL tables, but not worth it?
    // $this->add('text', 'field_label', ts('Label'), NULL, TRUE);
    // $this->add('text', 'db_column_name', ts('DB name'), NULL, TRUE);

    $this->add('hidden', 'map_id', $map_id);

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

  /**
   * Returns a list of all available CiviCRM fields.
   *
   * @todo Remove the fields already in the mapping.
   * @todo Use api4 instead, but it does not support: case fields, mutlti-record custom fields.
   */
  private function get_fields() {
    $entities = [
      'Activity' => ts('Activity'),
      'Contact' => ts('Contact'),
      'Address' => ts('Address'),
      'Contribution' => ts('Contribution'),
      'Event' => ts('Event'),
      'Case' => ts('Case'),
    ];

    $options = [];

    foreach ($entities as $entity_name => $entity_label) {
      $fields = civicrm_api3($entity_name, 'getfields')['values'];

      foreach ($fields as $key => $val) {
        $name = $entity_name . '.' . $val['name'];
        $options[$name] = $entity_label . ' > ' . $val['title'];
      }
    }

    return $options;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $map_id = $values['map_id'];

    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    if (!isset($settings['fields'])) {
      $settings['fields'] = [];
    }

    $colname = $values['field_name'];
    $colname = mb_strtolower($colname);
    $colname = preg_replace('/[^_a-z0-9]/', '_', $colname);

    $settings['fields'][] = [
      'field_name' => $values['field_name'],
      'column_name' => $colname,
    ];

    CRM_Core_DAO::executeQuery('UPDATE civicrm_fieldcondition SET settings = %1 WHERE id = %2', [
      1 => [json_encode($settings), 'String'],
      2 => [$map_id, 'Positive'],
    ]);

    $tableName = 'civicrm_fieldcondition_' . $map_id;

    // Add the column
    // @todo Use the correct type of the original field?
    $parts = explode('.', $values['field_name']);
    $entity_name = array_shift($parts);
    $field_name = implode('.', $parts);

    $field = civicrm_api3($entity_name, 'getfield', [
      'name' => $field_name,
      'action' => 'get',
    ])['values'];

    $sqlType = 'text';

    // Custom Fields have data_type, but core fields (ex: Address.county usually do not)
    if (!empty($field['data_type'])) {
      $sqlType = CRM_Core_BAO_CustomValueTable::fieldToSQLType($field['data_type'], $field['text_length'] ?? NULL);
    }
    elseif (!empty($field['type'])) {
      $data_type = CRM_Utils_Type::typeToString($field['type']);
      $sqlType = CRM_Core_BAO_CustomValueTable::fieldToSQLType($data_type);
    }

    CRM_Core_DAO::executeQuery("ALTER TABLE $tableName ADD `$colname` $sqlType DEFAULT NULL");

    CRM_Core_Session::setStatus(ts('Saved'), '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/fieldconditions/fields', "map_id=$map_id&reset=1"));
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
