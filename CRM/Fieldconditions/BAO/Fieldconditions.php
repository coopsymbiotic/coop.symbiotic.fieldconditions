<?php

class CRM_Fieldconditions_BAO_Fieldconditions {

  /**
   * FIXME
   */
  static function getFieldFilterValues($map_id, $source_value, $dest_value) {
    $rows = [];
    $where = 'WHERE vf.fieldcondition_map_id = %1';

    $params = [
      1 => [$map_id, 'Positive'],
    ];

    if (!empty($source_value)) {
      $where .= ' AND ov_src.value IN (' . CRM_Ddmpes_Utils::convertRequestStringArrayToSQL($source_value) . ')';
    }
    elseif (!empty($dest_value)) {
      $where .= ' AND ov_dst.value IN (' . CRM_Ddmpes_Utils::convertRequestStringArrayToSQL($dest_value) . ')';
    }

    $dao = CRM_Core_DAO::executeQuery('SELECT vf.id as id, map.source_field_id, map.dest_field_id,
           ov_src.label as source_label, ov_src.value as source_value,
           ov_dst.label as dest_label, ov_dst.value as dest_value
      FROM civicrm_fieldcondition_valuefilter vf
      LEFT JOIN civicrm_fieldcondition_map map ON (vf.fieldcondition_map_id = map.id)
      LEFT JOIN civicrm_custom_field fsrc ON (fsrc.id = map.source_field_id)
      LEFT JOIN civicrm_custom_field fdst ON (fdst.id = map.dest_field_id)
      LEFT JOIN civicrm_option_value ov_src ON (ov_src.value = vf.source_value AND ov_src.option_group_id = fsrc.option_group_id)
      LEFT JOIN civicrm_option_value ov_dst ON (ov_dst.value = vf.dest_value AND ov_dst.option_group_id = fdst.option_group_id)
      ' . $where . '
     ORDER BY vf.id ASC', $params);

    while ($dao->fetch()) {
      $rows[] = [
        'id' => $dao->id,
        'source_label' => $dao->source_label,
        'source_value' => $dao->source_value,
        'dest_label' => $dao->dest_label,
        'dest_value' => $dao->dest_value,
      ];
    }

    return $rows;
  }

}
