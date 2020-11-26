<?php

use CRM_Fieldconditions_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Fieldconditions_Form_FieldMapping extends CRM_Core_Form {

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('New Field Condition'));

    $types = [
      '' => E::ts('- select -'),
      'filter' => E::ts('Filter - limit options of one field based on the other field'),
    ];

    $this->add('select', 'type', E::ts('Type'), $types, TRUE);
    $this->add('text', 'name', E::ts('Name'), NULL, TRUE);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

/*
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
*/

    # $settings = json_encode($settings);

    // @todo generate an entity?
    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_fieldcondition (type, name) VALUES (%1, %2)', [
      1 => [$values['type'], 'String'],
      2 => [$values['name'], 'String'],
    ]);

    $id = CRM_Core_DAO::singleValueQuery('SELECT max(id) as id FROM civicrm_fieldcondition');

    // Create a database table for the new mapping
    $tableName = 'civicrm_fieldcondition_' . $id;

    $sql = "CREATE TABLE $tableName (
        id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ID',
        PRIMARY KEY (id)
      )
      ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";

    CRM_Core_DAO::executeQuery($sql);

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
