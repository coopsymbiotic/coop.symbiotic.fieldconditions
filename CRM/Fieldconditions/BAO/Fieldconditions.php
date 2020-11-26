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
      'label' => $meta['label'],
      'entity_field' => $fieldName,
      'entity_name' => $entityName,
    ];
  }

  /**
   * FIXME
   */
  static function getFieldFilterValues($map_id, $source_value, $dest_value) {
    $rows = [];

    if (!empty($source_value)) {
      return self::getFieldFilterDestValues($map_id, $source_value);
    }
    elseif (!empty($dest_value)) {
      return self::getFieldFilterSourceValues($map_id, $dest_value);
    }
    elseif (isset($source_value)) {
      return self::getAllDestValues($map_id);
    }
    elseif (isset($dest_value)) {
      return self::getAllSourceValues($map_id);
    }

    return $rows;
  }

  /**
   * Given a $dest_value, return possible 'source values'.
   */
  static function getFieldFilterSourceValues($map_id, $dest_value) {
    $rows = [];
    $where = 'WHERE vf.fieldcondition_map_id = %1';

    $params = [
      1 => [$map_id, 'Positive'],
    ];

    $where .= ' AND ov_dst.value IN (' . CRM_Ddmpes_Utils::convertRequestStringArrayToSQL($dest_value) . ')';

    $sql = 'SELECT ov_src.label as source_label, ov_src.value as source_value
      FROM civicrm_fieldcondition_valuefilter vf
      LEFT JOIN civicrm_fieldcondition_map map ON (vf.fieldcondition_map_id = map.id)
      LEFT JOIN civicrm_custom_field fsrc ON (fsrc.id = map.source_field_id)
      LEFT JOIN civicrm_custom_field fdst ON (fdst.id = map.dest_field_id)
      LEFT JOIN civicrm_option_value ov_src ON (ov_src.value = vf.source_value AND ov_src.option_group_id = fsrc.option_group_id)
      LEFT JOIN civicrm_option_value ov_dst ON (ov_dst.value = vf.dest_value AND ov_dst.option_group_id = fdst.option_group_id)
      ' . $where . '
     GROUP BY ov_src.value
     ORDER BY vf.id ASC';

    $dao = CRM_Core_DAO::executeQuery($sql, $params);

    while ($dao->fetch()) {
      $rows[] = [
        'source_label' => $dao->source_label,
        'source_value' => $dao->source_value,
      ];
    }

    return $rows;
  }

  /**
   * Given a $source_value, return possible 'dest values'.
   */
  static function getFieldFilterDestValues($map_id, $source_value) {
    $rows = [];
    $where = 'WHERE vf.fieldcondition_map_id = %1';

    $params = [
      1 => [$map_id, 'Positive'],
    ];

    $where .= ' AND ov_src.value IN (' . CRM_Ddmpes_Utils::convertRequestStringArrayToSQL($source_value) . ')';

    $sql = 'SELECT ov_dst.label as dest_label, ov_dst.value as dest_value
      FROM civicrm_fieldcondition_valuefilter vf
      LEFT JOIN civicrm_fieldcondition_map map ON (vf.fieldcondition_map_id = map.id)
      LEFT JOIN civicrm_custom_field fsrc ON (fsrc.id = map.source_field_id)
      LEFT JOIN civicrm_custom_field fdst ON (fdst.id = map.dest_field_id)
      LEFT JOIN civicrm_option_value ov_src ON (ov_src.value = vf.source_value AND ov_src.option_group_id = fsrc.option_group_id)
      LEFT JOIN civicrm_option_value ov_dst ON (ov_dst.value = vf.dest_value AND ov_dst.option_group_id = fdst.option_group_id)
      ' . $where . '
     GROUP BY ov_dst.value
     ORDER BY vf.id ASC';

    $dao = CRM_Core_DAO::executeQuery($sql, $params);

    while ($dao->fetch()) {
      $rows[] = [
        'dest_label' => $dao->dest_label,
        'dest_value' => $dao->dest_value,
      ];
    }

    return $rows;
  }

  /**
   * Given a map_id, return all possible source values.
   *
   * This is used mostly when a field selection is cleared, so we need
   * to restore the full list of options.
   */
  static function getAllSourceValues($map_id) {
    $rows = [];

    $params = [
      1 => [$map_id, 'Positive'],
    ];

    $dao = CRM_Core_DAO::executeQuery('SELECT map.source_field_id, map.dest_field_id,
           ov_src.label as source_label, ov_src.value as source_value
      FROM civicrm_fieldcondition_map map
      LEFT JOIN civicrm_custom_field fsrc ON (fsrc.id = map.source_field_id)
      LEFT JOIN civicrm_option_value ov_src ON (ov_src.option_group_id = fsrc.option_group_id)
      WHERE map.id = %1
     ORDER BY ov_src.id ASC', $params);

    while ($dao->fetch()) {
      $rows[] = [
        'id' => $dao->id,
        'source_label' => $dao->source_label,
        'source_value' => $dao->source_value,
      ];
    }

    return $rows;
  }

  /**
   * Given a map_id, return all possible destination values.
   *
   * This is used mostly when a field selection is cleared, so we need
   * to restore the full list of options.
   */
  static function getAllDestValues($map_id) {
    $rows = [];

    $params = [
      1 => [$map_id, 'Positive'],
    ];

    $dao = CRM_Core_DAO::executeQuery('SELECT map.dest_field_id,
           ov_dest.label as dest_label, ov_dest.value as dest_value
      FROM civicrm_fieldcondition_map map
      LEFT JOIN civicrm_custom_field fdest ON (fdest.id = map.dest_field_id)
      LEFT JOIN civicrm_option_value ov_dest ON (ov_dest.option_group_id = fdest.option_group_id)
      WHERE map.id = %1
     ORDER BY ov_dest.id ASC', $params);

    while ($dao->fetch()) {
      $rows[] = [
        'id' => $dao->id,
        'dest_label' => $dao->dest_label,
        'dest_value' => $dao->dest_value,
      ];
    }

    return $rows;
  }

  /**
   * Return all possible values. Used in admin forms.
   */
  static function getFieldFilterAllValues($map_id, $params = []) {
    $rows = [];
    $where = 'WHERE 1=1';

    // Extract field definitions
    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);

    if (empty($settings['fields'])) {
      CRM_Core_Error::fatal('No fields in this mapping?');
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
   * FIXME
   */
  static function getMapSettings($map_id) {
    $settings = CRM_Core_DAO::singleValueQuery('SELECT settings FROM civicrm_fieldcondition WHERE id = %1', [
      1 => [$map_id, 'Positive'],
    ]);

    $settings = json_decode($settings, TRUE);
    return $settings;
  }

}
