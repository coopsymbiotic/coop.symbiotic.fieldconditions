<?php

require_once 'fieldconditions.civix.php';
use CRM_Fieldconditions_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function fieldconditions_civicrm_config(&$config) {
  _fieldconditions_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function fieldconditions_civicrm_xmlMenu(&$files) {
  _fieldconditions_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function fieldconditions_civicrm_install() {
  _fieldconditions_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function fieldconditions_civicrm_postInstall() {
  _fieldconditions_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function fieldconditions_civicrm_uninstall() {
  _fieldconditions_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function fieldconditions_civicrm_enable() {
  _fieldconditions_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function fieldconditions_civicrm_disable() {
  _fieldconditions_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function fieldconditions_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _fieldconditions_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function fieldconditions_civicrm_managed(&$entities) {
  _fieldconditions_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function fieldconditions_civicrm_caseTypes(&$caseTypes) {
  _fieldconditions_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function fieldconditions_civicrm_angularModules(&$angularModules) {
  _fieldconditions_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function fieldconditions_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _fieldconditions_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function fieldconditions_civicrm_navigationMenu(&$menu) {
  _fieldconditions_civix_insert_navigation_menu($menu, 'Administer/Customize Data and Screens', [
    'label' => E::ts('Field Conditions'),
    'name' => 'fieldconditoins_settings',
    'url' => 'civicrm/admin/fieldconditions',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _fieldconditions_civix_navigationMenu($menu);
}

/**
 *
 */
function fieldconditions_civicrm_buildForm($formName, &$form) {
  $allSettings = CRM_Fieldconditions_BAO_Fieldconditions::getAllSettings();
  $maps = [];

  // Find out if this form has fieldconditions
  foreach ($allSettings as $map_id => $settings) {
    $matches = FALSE;
    $hash_id = $map_id;

    foreach ($form->_elementIndex as $key => $val) {
      foreach ($settings['fields'] as $k2 => $field) {
        // This is a horrible hack, but the Multiple Contact Update (Batch update) form names things differently.
        if ($formName == 'CRM_Contact_Form_Task_Batch' && substr($key, 0, 6) == 'field[') {
          $fix = [
            'country_id' => 'country',
            'county_id' => 'county',
            'state_province_id' => 'state_province',
          ];

          if (isset($fix[$field['entity_field']])) {
            $field['entity_field'] = $fix[$field['entity_field']];
          }
        }

        // There is a bit of twisted logic in here to handle multiple address records.
        // Addresses use as "hash_id" such as "1-2", where 1 is the map_id, 2 is the address ID.
        // We switch back and forth because a same form can have address and non-address fields, semi-randomly.
        // Field conditions between address and non-address fields is somewhat unpredictable/untested.
        if ($key == $field['entity_field']) {
          $hash_id = $map_id;
          if (empty($maps[$hash_id])) {
            $maps[$hash_id] = $settings;
          }
          $maps[$hash_id]['fields'][$k2]['qf_field'] = $key;
        }
        elseif (preg_match('/^address\[(\d+)\]\[' . $field['entity_field'] . '(_[^\]]*)?\]$/', $key, $found)) {
          // Ex: address fields on Edit Contact
          $hash_id = $map_id . '-' . $found[1];
          if (empty($maps[$hash_id])) {
            $maps[$hash_id] = $settings;
          }
          $maps[$hash_id]['fields'][$k2]['qf_field'] = 'address_' . $found[1] . '_' . $field['entity_field'] . (!empty($found[2]) ? $found[2] : '');
        }
        elseif ($formName == 'CRM_Contact_Form_Task_Batch' && preg_match('/^field\[(\d+)\]\[' . $field['entity_field'] . '(-[^\]]*)?\]$/', $key, $found)) {
          // Ex: multiple contact update
          $hash_id = $map_id . '-' . $found[1];
          if (empty($maps[$hash_id])) {
            $maps[$hash_id] = $settings;
          }
          $maps[$hash_id]['fields'][$k2]['qf_field'] = 'field_' . $found[1] . '_' . $field['entity_field'] . (!empty($found[2]) ? $found[2] : '');
        }
        elseif ($formName == 'CRM_Contact_Form_Task_Batch' && preg_match('/^field\[(\d+)\]\[address_' . $field['entity_field'] . '(-[^\]]*)?\]$/', $key, $found)) {
          // Ex: multiple contact update, custom address fields
          $hash_id = $map_id . '-' . $found[1];
          if (empty($maps[$hash_id])) {
            $maps[$hash_id] = $settings;
          }
          $maps[$hash_id]['fields'][$k2]['qf_field'] = 'field_' . $found[1] . '_address_' . $field['entity_field'] . (!empty($found[2]) ? $found[2] : '');
        }
        elseif (preg_match('/' . $field['entity_field'] . '_/', $key)) {
          // @todo This currently only matches against custom fields
          // and should probably use strpos or something more efficient.
          // custom_xx_
          $hash_id = $map_id;
          if (empty($maps[$hash_id])) {
            $maps[$hash_id] = $settings;
          }
          $maps[$hash_id]['fields'][$k2]['qf_field'] = $key;
        }
      }
    }
  }

  if (!empty($maps)) {
    if (empty(CRM_Utils_Request::retrieveValue('snippet', 'String'))) {
      Civi::resources()->addVars('fieldconditions', [
        'maps' => $maps,
      ]);

      Civi::resources()->addScriptFile('coop.symbiotic.fieldconditions', 'fieldconditions.js');
    }
    else {
      // Ex: loading a new address
      // We have to do a workaround using alterContent because Address.tpl does not
      // invoke any relevant crmRegion where we could inject JS.
      global $fieldconditions_maps;
      $fieldconditions_maps = $maps;
    }
  }
}

function fieldconditions_civicrm_pageRun(&$page) {
  $pageName = get_class($page);

  // To support Contact inline edit
  if ($pageName == 'CRM_Contact_Page_View_Summary') {
    Civi::resources()->addScriptFile('coop.symbiotic.fieldconditions', 'fieldconditions.js');
  }
}

function fieldconditions_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  $tpls = [
    'CRM/Contact/Form/Inline/Address.tpl',
    'CRM/Contact/Form/Inline/CustomData.tpl',
    'CRM/Contact/Form/Contact.tpl',
  ];

  if (in_array($tplName, $tpls) && !empty(CRM_Utils_Request::retrieveValue('snippet', 'String'))) {
    global $fieldconditions_maps;

    if (!empty($fieldconditions_maps)) {
      foreach ($fieldconditions_maps as $hash_id => $vars) {
        $content .= '
          <script>
            CRM.$(function($) {
              if (typeof CRM.vars.fieldconditions == "undefined") {
                CRM.vars.fieldconditions = {};
                CRM.vars.fieldconditions.maps = {};
              }

              CRM.vars.fieldconditions.maps["' . $hash_id . '"] = $.parseJSON(\'' . json_encode($vars) . '\');
              CRM.fieldconditionsEnable();
            });
          </script>
        ';
      }

      // Only call this function once (if there are multiple fieldconditions)
      $content .= '
        <script>
          CRM.$(function($) {
            CRM.fieldconditionsEnable();
          });
        </script>
      ';
    }
  }
}
