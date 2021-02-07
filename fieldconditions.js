(function($, _, ts){

  CRM.fieldconditionsChangeInProgress = false;

  $(document).on('crmLoad', function(e) {
    if (typeof CRM.vars.fieldconditions == 'undefined' || typeof CRM.vars.fieldconditions.maps == 'undefined') {
      return;
    }

    $.each(CRM.vars.fieldconditions.maps, function(map_id, settings) {
      $.each(settings.fields, function(index, field) {
        $('#' + field.qf_field).on('change', function(event) {
          CRM.fieldconditionsFieldLookup(field, map_id, settings);
        });
      });
    });

  });

  /**
   * When the user one of the fields, lookup possible options.
   */
  CRM.fieldconditionsFieldLookup = function(selected_field, map_id, settings) {
    // Only update the list if no selection was already done.
    // Also protect against a change event triggered by another list being programmatically updated.
    if (CRM.fieldconditionsChangeInProgress) {
      return;
    }

    $('#' + selected_field.qf_field).parent().append('<div class="crm-loading-element" style="float: right; height: 16px;"></div>');
    CRM.fieldconditionsChangeInProgress = true;

    var field_to_qf = {};
    var params = {};
    params['map_id'] = map_id;

    // Fetch the values of all fields in this mapping
    $.each(settings.fields, function(index, field) {
      params[field.column_name] = $('#' + field.qf_field).val();
      field_to_qf[field.column_name] = field.qf_field;
    });

    $.ajax(CRM.url('civicrm/fieldconditions/ajax/field-filter-values'), {
      data: params,
      dataType: 'json',
      success: function(data) {
        // CRM.status(data.length + ' items');

        var allowed_values = {};

        $.each(data, function(index, element) {
          // Each 'element' has a list of column_name.{label,value}
          $.each(element, function(column_name, option) {
            // Maybe should be filtered from the results?
            // It is the id of the row of allowed values.
            if (column_name == 'id') {
              return;
            }

            var qf = field_to_qf[column_name];

            if (typeof allowed_values[qf] == 'undefined') {
              allowed_values[qf] = {};
            }

            allowed_values[qf][option.value] = option.label;
          });
        });

        $.each(allowed_values, function(qf_name, values) {
          CRM.fieldconditionsUpdateWidget(qf_name, values);
        });

        $('#' + selected_field.qf_field).parent().find('.crm-loading-element').remove();
        CRM.fieldconditionsChangeInProgress = false;
      },
      error: function(data) {
        CRM.alert(ts('Error while checking possible values'));
        $('#' + selected_field.qf_field).parent().find('.crm-loading-element').remove();
        CRM.fieldconditionsChangeInProgress = false;
      },
    });
  };

  CRM.fieldconditionsUpdateWidget = function(qf_name, values) {
    var $select = $('#' + qf_name);

    // Do not update a widget that already has a value
    // It should already be filtered to allow only valid options
    if ($select.val()) {
      return;
    }

    $select.html('');
    $select.append('<option value=""></option>');

    $.each(values, function(index, element) {
      $select.append($('<option></option>')
        .attr('value', index)
        .html(element));
    });

    if (Object.keys(values).length == 1) {
      for (var property in values) {
        if (values.hasOwnProperty(property)) {
          $select.val(property);
        }
      }
    }

    $select.change();
  };

})(CRM.$, CRM._, CRM.ts('coop.symbiotic.fieldconditions'));
