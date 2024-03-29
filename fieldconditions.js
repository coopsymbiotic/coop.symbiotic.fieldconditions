(function($, _, ts){

  CRM.fieldconditionsChangeInProgress = 0;

  // see crmLoad at the bottom of the file

  /**
   *
   */
  CRM.fieldconditionsEnable = function() {
    $.each(CRM.vars.fieldconditions.maps, function(map_id, settings) {
      $.each(settings.fields, function(index, field) {
        $('#' + field.qf_field).on('change', function(event) {
          CRM.fieldconditionsFieldLookup(field, map_id, settings);
        });

        // If there are already values in the field, trigger a refresh of the available options
        // @todo use hook_civicrm_fieldOptions instead?
        if ($('#' + field.qf_field).val()) {
          CRM.fieldconditionsFieldLookup(field, map_id, settings);
        }
      });
    });
  };

  /**
   * When the user one of the fields, lookup possible options.
   */
  CRM.fieldconditionsFieldLookup = function(selected_field, map_id, settings) {
    // Only update the list if no selection was already done.
    // Also protect against a change event triggered by another list being programmatically updated.
    if (CRM.fieldconditionsChangeInProgress > 0) {
      return;
    }

    $('#' + selected_field.qf_field).parent().append('<div class="crm-loading-element" style="float: right; height: 16px;"></div>');
    CRM.fieldconditionsChangeInProgress++;

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
        var allowed_values = {};

        // Checking for displaying_all avoid re-selecting an option if there was only one choice available
        // For example: only one country is enabled, and we want to be able to leave the field empty.
        var displaying_all = 0;

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

            if ('all' in option) {
              displaying_all = 1;
            }
          });
        });

        $.each(allowed_values, function(qf_name, values) {
          CRM.fieldconditionsUpdateWidget(qf_name, values, selected_field, displaying_all);
        });

        $('#' + selected_field.qf_field).parent().find('.crm-loading-element').remove();
        CRM.fieldconditionsChangeInProgress--;
      },
      error: function(data) {
        CRM.alert(ts('Error while checking possible values'));
        $('#' + selected_field.qf_field).parent().find('.crm-loading-element').remove();
        CRM.fieldconditionsChangeInProgress--;
      },
    });
  };

  CRM.fieldconditionsUpdateWidget = function(qf_name, values, source_field, displaying_all) {
    var $select = $('#' + qf_name);
    var select_original_value = $select.val();

    // Do not update a widget that already has a value
    // It should already be filtered to allow only valid options
    //
    // However, we still update the field that was selected, because
    // if {A,B,C} was previously possible, but now that "A" was selected,
    // with the values of other fields, maybe {B,C} are not valid options anymore.
    // [UPDATE] Returning here causes problems if we still want to filter the allowed options
    // and not allow selecting an invalid value afterwards.
    if ($select.val() && qf_name != source_field.qf_field) {
      // return;
    }

    // If it's an autocomplete select, hide it, since it's difficult to control
    if ($select.hasClass('crm-ajax-select')) {
      if (values.length == 0) {
        $select.hide();
        $select.siblings('.select2-container').show();
        return;
      }
      else {
        // @todo Do not re-create an element if it already exists
        var $parent = $select.parent();
        var id = $select.attr('id');
        $select.remove();

        $select = $('<select>', {id: id, name: id, 'class': 'crm-form-select'});
        $select.appendTo($parent);
        $select.siblings('.select2-container').hide();

        // @todo cleanup - enable event update. we have the qf_field but not the 'field' definition
        $.each(CRM.vars.fieldconditions.maps, function(map_id, settings) {
          $.each(settings.fields, function(index, field) {
            if (qf_name == field.qf_field) {
              $('#' + field.qf_field).on('change', function(event) {
                CRM.fieldconditionsFieldLookup(field, map_id, settings);
              });
            }
          });
        });
      }
    }

    $select.html('');
    $select.append('<option value=""></option>');

    $.each(values, function(index, element) {
      // Rather odd, probably something we can fix in the PHP that generates the values
      if (index != "null" && index != '') {
        $select.append($('<option></option>')
          .attr('value', index)
          .html(element));
      }
    });

    // If there was only one value, in a field other than the current field, then select that value.
    // Checking for displaying_all avoid re-selecting an option if there was only one choice available
    // For example: only one country is enabled, and we want to be able to leave the field empty.
    if (source_field.qf_field != qf_name && Object.keys(values).length == 1 && !displaying_all) {
      for (var property in values) {
        if (values.hasOwnProperty(property)) {
          $select.val(property);
        }
      }
    }
    else if (select_original_value) {
      // Restore the value that was there before, if still valid
      $select.val(select_original_value);
    }

    $select.change();
  };

  /**
   * Called by "Add new address" on New Contact, for example
   */
  $(document).on('crmLoad', function(e) {
    if (typeof CRM.vars.fieldconditions == 'undefined' || typeof CRM.vars.fieldconditions.maps == 'undefined') {
      return;
    }

    CRM.fieldconditionsEnable();
  });


})(CRM.$, CRM._, CRM.ts('coop.symbiotic.fieldconditions'));
