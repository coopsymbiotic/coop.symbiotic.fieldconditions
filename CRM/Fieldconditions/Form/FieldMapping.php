<?php

use CRM_Fieldconditions_ExtensionUtil as E;

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
      $options[$key] = $group_title . ' : ' . $val['label'] . (empty($val['is_active']) ? ' ' . E::ts('(disabled)') : '');
    }

    $this->add('select', 'source_field_id', E::ts('Source field'), $options, TRUE,
      array('id' => 'source_field_id', 'class' => 'crm-select2')
    );

    $this->add('select', 'dest_field_id', E::ts('Destination field'), $options, TRUE,
      array('id' => 'dest_field_id', 'class' => 'crm-select2')
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

    // Eventually we may support other types
    $values['map_type'] = 'filter';

    $settings = [];
    $settings['fields'] = [];

    $settings['fields'][] = [
      'field_id' => $values['source_field_id'],
      // 'field_label' => $values['field_label'],
      // 'db_column_name' => $values['db_column_name'],
    ];

    $settings['fields'][] = [
      'field_id' => $values['dest_field_id'],
      // 'field_label' => $values['field_label'],
      // 'db_column_name' => $values['db_column_name'],
    ];

    $settings = json_encode($settings);

    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_fieldcondition_map (map_type, settings) VALUES (%1, %2)', [
      1 => [$values['map_type'], 'String'],
      2 => [$settings, 'String'],
    ]);

    CRM_Core_Session::setStatus(ts('Saved'), '', 'success');
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
