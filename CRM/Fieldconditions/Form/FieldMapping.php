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
    CRM_Fieldconditions_BAO_Fieldconditions::createFieldMapping($values['type'], $values['name']);
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
