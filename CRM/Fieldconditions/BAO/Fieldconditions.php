<?php

class CRM_Fieldconditions_BAO_Fieldconditions {

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
    ];
  }

  /**
   * Return all possible values. Used in admin forms.
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
    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    if (empty($settings['fields'])) {
      throw new Exception('No fields in this mapping?');
    }

    $map_id = (int) $map_id;
    $table = 'civicrm_fieldcondition_' . $map_id;

    // This is used by AJAX queries
    // FIXME: we should probably validate if the 'key' is valid.
    foreach ($params as $key => $val) {
      if (is_array($val) && !empty($val)) {
        $where .= ' AND ' . $key . ' IN (' . implode(',', $val) . ')'; // FIXME FIXME FIXME
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
          'label' => self::translate($field['field_name'], $dao->{$db_column_name}),
          'value' => $dao->{$db_column_name},
        ];
      }

      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * FIXME
   */
  static function translate($field_name, $value) {
    static $cache = [];

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

}
