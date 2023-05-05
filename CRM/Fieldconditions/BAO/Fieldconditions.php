<?php

use CRM_Fieldconditions_ExtensionUtil as E;

class CRM_Fieldconditions_BAO_Fieldconditions {

  /**
   * Creates a new Field Mapping, the first step to setup a new fieldcondition.
   * It creates the base SQL table, which will store the list of fields in the condition.
   */
  static public function createFieldMapping($type, $name) {
    // @todo generate an entity?
    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_fieldcondition (type, name) VALUES (%1, %2)', [
      1 => [$type, 'String'],
      2 => [$name, 'String'],
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

    $map_id = CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_fieldcondition WHERE name = %1', [
      1 => [$name, 'String'],
    ]);

    return $map_id;
  }

  /**
   * Adds a new field in a field mapping.
   */
  static public function addFieldToMapping($map_id, $field_name) {
    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    if (!isset($settings['fields'])) {
      $settings['fields'] = [];
    }

    $colname = $field_name;
    $colname = mb_strtolower($colname);
    $colname = preg_replace('/[^_a-z0-9]/', '_', $colname);

    $settings['fields'][] = [
      'field_name' => $field_name,
      'column_name' => $colname,
    ];

    CRM_Core_DAO::executeQuery('UPDATE civicrm_fieldcondition SET settings = %1 WHERE id = %2', [
      1 => [json_encode($settings), 'String'],
      2 => [$map_id, 'Positive'],
    ]);

    $tableName = 'civicrm_fieldcondition_' . $map_id;

    // Add the column
    // @todo Use the correct type of the original field?
    $parts = explode('.', $field_name);
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
  }

  /**
   * Adds a tuple (combo of values) in a fieldcondition 'filter'.
   */
  static public function addFieldFilterValue($map_id, $values) {
    $dao = CRM_Core_DAO::executeQuery('SELECT *
      FROM civicrm_fieldcondition map
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
      $sql_fields[] = $field->column_name;
      $params[$key] = [$values[$field->column_name], 'String']; // @todo not always a string
      $sql_placeholders[] = '%' . $key;
    }

    $sql = 'INSERT INTO civicrm_fieldcondition_' . $map_id . ' (' . implode(',', $sql_fields) . ')
      VALUES (' . implode(',', $sql_placeholders) . ')';

    CRM_Core_DAO::executeQuery($sql, $params);
  }

  /**
   * Given a name such as Contact.custom_123, it returns more useful meta from getfields.
   */
  static public function getFieldMeta($fieldName) {
    $parts = explode('.', $fieldName);
    $entityName = array_shift($parts);
    $fieldName = implode('.', $parts);

    $meta = civicrm_api3($entityName, 'getfield', [
      'name' => $fieldName,
      'action' => 'get',
    ])['values'];

    return [
      'label' => $meta['label'] ?? $meta['title'],
      'entity_field' => $fieldName,
      'entity_name' => $entityName,
      'html_type' => $meta['html_type'],
      'serialize' => $meta['serialize'] ?? false,
    ];
  }

  /**
   * Main function to lookup possible matches (or to return all possible matches).
   * Used by both the admin forms and by the end-user lookups.
   */
  static function getFieldFilterAllValues($map_id, $params = []) {
    $rows = [];
    $where = 'WHERE 1=1';

    // Non-numeric map_id when there are fieldconditiosn on addresses
    if (!is_numeric($map_id)) {
      $parts = explode('-', $map_id);
      $map_id = $parts[0];
    }

    // Extract field definitions
    $settings = CRM_Fieldconditions_BAO_Fieldconditions::getSettings($map_id);

    if (empty($settings['fields'])) {
      throw new Exception('No fields in this mapping?');
    }

    // Lookup possible matches from the DB
    $map_id = (int) $map_id;
    $table = 'civicrm_fieldcondition_' . $map_id;

    // This is used by AJAX queries
    // FIXME: we should probably validate if the 'key' is valid.
    $param_counter = 0;

    foreach ($params as $key => $val) {
      // Ignore the field if it is a multi-select field, which is equivalent to
      // looking up all possible combinations as if said field had not been selected.
      // We only check this if there is more than one selection. Otherwise FieldA will allow invalid selections.
      // @todo This only works if: FieldA is single, FieldB is multi, and no fieldC. This conditions should
      // probably either do some OR statements for multiselect fields, or some fancier logic.
      $param_counter++;

      if (count($params) > 1 && $param_counter > 1) {
        if (self::getFieldPropertyFromSettings($settings, 'column_name', $key, 'serialize')) {
          continue;
        }
      }

      if (is_array($val) && !empty($val)) {
        $key = CRM_Utils_Type::escape($key, 'MysqlColumnNameOrAlias');
        $values = CRM_Utils_Type::validate(implode(',', $val), 'CommaSeparatedIntegers');
        $where .= " AND $key IN ($values)";
      }
      else {
        $val = intval($val); // FIXME
        if ($val) {
          $where .= ' AND ' . $key . ' = ' . $val;
        }
      }
    }

    $sql = 'SELECT *
      FROM ' . $table . ' vf ' . $where . '
     ORDER BY vf.id ASC';

    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => [$map_id, 'Positive'],
    ]);

    while ($dao->fetch()) {
      $row = [];
      $row['id'] = $dao->id;

      foreach ($settings['fields'] as $field) {
        $db_column_name = $field['column_name'];
        $row[$db_column_name] = [
          'label' => self::translate($field, $dao->{$db_column_name}),
          'value' => $dao->{$db_column_name},
        ];
      }

      $rows[] = $row;
    }

    // Very specific hack for: If FieldA is multiselect and we want to allow multiple selections of FieldA
    // This will add all possible options for FieldA.
    $keys = array_keys($params);

    if (!empty($keys)) {
      if (self::getFieldPropertyFromSettings($settings, 'column_name', $keys[0], 'serialize')) {
        $sql = 'SELECT DISTINCT(' . $keys[0] . ') FROM ' . $table . ' vf ORDER BY vf.id ASC';
        $dao = CRM_Core_DAO::executeQuery($sql);

        while ($dao->fetch()) {
          $row = [];
          $row['id'] = $dao->id;

          foreach ($settings['fields'] as $field) {
            $db_column_name = $field['column_name'];
            $row[$db_column_name] = [
              'label' => self::translate($field, $dao->{$db_column_name}),
              'value' => $dao->{$db_column_name},
            ];
          }

          $rows[] = $row;
        }
      }
    }

    return $rows;
  }

  /**
   * Returns a field property from the settings.
   * If we keyed settings by field_name, we could remove this function.
   */
  static function getFieldPropertyFromSettings($settings, $match_prop, $prop_name, $return_prop) {
    if (empty($settings['fields'])) {
      return false;
    }

    foreach ($settings['fields'] as $key => $val) {
      if ($val[$match_prop] == $prop_name) {
        return $val[$return_prop];
      }
    }

    return false;
  }

  /**
   * Returns the human-readable value for a field.
   * @todo There must be a core function for this?
   */
  static function translate($field, $value) {
    static $cache = [];
    $field_name = $field['field_name'];

    // @todo Cache?
    if ($field['html_type'] == 'Autocomplete-Select') {
      $result = civicrm_api3('Contact', 'get', [
        'id' => $value,
        'return' => 'display_name',
        'sequential' => 1,
      ]);

      if (!empty($result['values'][0])) {
        return $result['values'][0]['display_name'];
      }
    }

    if (!isset($cache[$field_name])) {
      $parts = explode('.', $field_name);

      try {
        $options = civicrm_api3($parts[0], 'getoptions', [
          'field' => $parts[1],
        ]);
        $cache[$field_name] = $options['values'];
      }
      catch (Exception $e) {
        // Ignore, assume no options, but populate the cache
        // to avoid checking every time.
        $cache[$field_name] = [];
      }
    }

    if (isset($cache[$field_name]) && !empty($cache[$field_name][$value])) {
      return $cache[$field_name][$value];
    }

    // Assume this field doesn't have any options.
    // Ex: text field.
    return $value;
  }

  /**
   * Returns the various settings of a given fieldcondition (they used to be called 'maps').
   */
  public static function getSettings($map_id) {
    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    foreach ($settings['fields'] as &$field) {
      $meta = CRM_Fieldconditions_BAO_Fieldconditions::getFieldMeta($field['field_name']);
      $field['entity'] = $meta['entity_name']; // @todo deprecate
      $field['entity_name'] = $meta['entity_name'];
      $field['entity_field'] = $meta['entity_field'];
      $field['field_label'] = $meta['label'];
      $field['html_type'] = $meta['html_type'];
      $field['serialize'] = $meta['serialize'] ?? false;
    }

    return $settings;
  }

  /**
   *
   */
  public static function getAllSettings() {
    $all = [];
    $dao = CRM_Core_DAO::executeQuery('SELECT id FROM civicrm_fieldcondition');

    while ($dao->fetch()) {
      $all[$dao->id] = self::getSettings($dao->id);
    }

    return $all;
  }

  /**
   * Copied from core CRM/Contact/Page/AJAX.php
   * Used in the settings to add possible value combinations.
   */
  public static function getContactRef($custom_field_id) {
    $name = '';
    $cfID = $custom_field_id;

    // check that this is a valid, active custom field of Contact Reference type
    $params = ['id' => $cfID];
    $returnProperties = ['filter', 'data_type', 'is_active'];
    $cf = [];
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $params, $cf, $returnProperties);
    if (!$cf['id'] || !$cf['is_active'] || $cf['data_type'] != 'ContactReference') {
      throw new Exception('Not a ContactReference');
    }

    if (!empty($cf['filter'])) {
      $filterParams = [];
      parse_str($cf['filter'], $filterParams);

      $action = $filterParams['action'] ?? NULL;
      if (!empty($action) && !in_array($action, ['get', 'lookup'])) {
        throw new Exception('Action is not get or lookup');
      }

      if (!empty($filterParams['group'])) {
        $filterParams['group'] = explode(',', $filterParams['group']);
      }
    }

    $list = array_keys(CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'contact_reference_options'
    ), '1');

    $return = array_unique(array_merge(['sort_name'], $list));
    $limit = 0;

    $params = ['offset' => 0, 'rowCount' => $limit, 'version' => 3];
    foreach ($return as $fld) {
      $params["return.{$fld}"] = 1;
    }

    if (!empty($action)) {
      $excludeGet = [
        'reset',
        'key',
        'className',
        'fnName',
        'json',
        'reset',
        'context',
        'timestamp',
        'limit',
        'id',
        's',
        'q',
        'action',
      ];

      foreach ($_GET as $param => $val) {
        if (empty($val) ||
          in_array($param, $excludeGet) ||
          strpos($param, 'return.') !== FALSE ||
          strpos($param, 'api.') !== FALSE
        ) {
          continue;
        }
        $params[$param] = $val;
      }
    }

    if ($name) {
      $params['sort_name'] = $name;
    }

    $params['sort'] = 'sort_name';

    // tell api to skip permission chk. dgg
    $params['check_permissions'] = 0;

    // add filter variable to params
    if (!empty($filterParams)) {
      $params = array_merge($params, $filterParams);
    }

    $contact = civicrm_api3('Contact', 'Get', $params);

    $contactList = [];
    foreach ($contact['values'] as $value) {
      $view = [];
      foreach ($return as $fld) {
        if (!empty($value[$fld])) {
          $view[] = $value[$fld];
        }
      }
      $contactList[$value['id']] = implode(' :: ', $view);
    }

    return $contactList;
  }

}
