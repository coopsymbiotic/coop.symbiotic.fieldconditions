<?php

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Fieldconditions_Form_FieldFilterValue extends CRM_Core_Form {
  protected $map_id = NULL;
  protected $id = NULL;

  public function preProcess() {
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);

    if ($this->_action & CRM_Core_Action::DELETE) {
      if (!CRM_Core_Permission::check('administer CiviCRM')) {
        CRM_Core_Error::fatal(ts('You do not have permission to access this page.'));
      }
    }
  }

  public function setDefaultValues() {
    $defaults = [];

    $source_value = CRM_Utils_Array::value('source_value', $_REQUEST);

    if ($source_value) {
      $defaults['source_option'] = intval($source_value);
    }

    return $defaults;
  }

  public function buildQuickForm() {
    $this->map_id = CRM_Utils_Request::retrieve('map_id', 'Positive', $this);
    $this->add('hidden', 'map_id', $this->map_id);

    if ($this->_action & CRM_Core_Action::DELETE || (isset($this->_submitValues['action']) && $this->_submitValues['action'] & CRM_Core_Action::DELETE)) {
      $this->assign('action', 'delete');
      $this->id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      $this->add('hidden', 'id', $this->id);
      $this->add('hidden', 'action', $this->_action);

      // Fetch the specific filter value, then fetch its human-readable labels
      $labels = [];
      $val = CRM_Fieldconditions_BAO_Fieldconditions::getFieldFilterAllValues($this->map_id, ['id' => $this->id])[0];

      foreach ($val as $k => $v) {
        if ($k != 'id') {
          $labels[] = $v['label'];
        }
      }

      $this->assign('confirm_delete_values', $labels);

      $button = ts('Delete');
      if ($this->_action & CRM_Core_Action::RENEW) {
        $button = ts('Restore');
      }

      $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => $button,
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      ));
      return;
    }

    $dao = CRM_Core_DAO::executeQuery('SELECT *
      FROM civicrm_fieldcondition
      WHERE id = %1', [
      1 => [$this->map_id, 'Positive'],
    ]);

    if (!$dao->fetch()) {
      throw new Exception('map_id not found');
    }

    $settings = json_decode($dao->settings, TRUE);

    foreach ($settings['fields'] as $field) {
      $meta = CRM_Fieldconditions_BAO_Fieldconditions::getFieldMeta($field['field_name']);

      $result = civicrm_api3($meta['entity_name'], 'getoptions', [
        'field' => $meta['entity_field'],
        'option.limit' => 0,
      ]);

      $options = ['' => ts('- select -')] + $result['values'];

      $this->add('select', $field['column_name'], $meta['label'], $options, TRUE,
        array('id' => $field['column_name'], 'class' => 'crm-select2')
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

    if (isset($values['action']) && $values['action'] & CRM_Core_Action::DELETE) {
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_fieldcondition_" . intval($map_id) . " WHERE id = %1", [
        1 => [$values['id'], 'Positive'],
      ]);

      CRM_Core_Session::setStatus(ts('The item has been deleted'), '', 'success');

      $url = CRM_Utils_System::url('civicrm/admin/fieldconditions/filter-values', "map_id=$map_id");
      CRM_Utils_System::redirect($url);
    }

    CRM_Fieldconditions_BAO_Fieldconditions::addFieldFilterValue($map_id, $values);
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
