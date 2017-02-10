CiviCRM field conditions
========================

Every had a custom field whose possible values depended on the values of another field?

For example,

* if "Field A" has options "1,2,3,4",
* and "Field B" has options "x1, x2, x3, y1, y2, y3, z1, z2, z3"
* but x* can only be selected if FieldA = 1, y* can only be selected if FieldA  = 2, etc?

This extension may help.

BIG WARNING
===========

This code was not initially meant for public release. It is a subset of a quick hack done for a client project.

It is not meant to be used by end-users at this point, since it is missing a general solution for the javascript bit (see example JS below).

Example JS
==========

This is a rather terrible quick and dirty example. It can be improved in many ways. Provided here only to give a rough idea.

```
  CRM.myExample = function(map_id, dest_field_id, value, extract_element, origin_element) {
    var $select = $('.crm-case-customdata-row-' + dest_field_id + ' select');

    // Only update the list if no selection was already done.
    // Also protect against a change event triggered by another list being programmatically updated.
    if (CRM.myChangeInProgress || $select.val()) {
      return;
    }

    $('.crm-case-customdata-row-' + dest_field_id).append('<div class="crm-loading-element" style="float: right; height: 16px;"></div>');

    CRM.myChangeInProgress = true;

    var params = {};
    params['map_id'] = map_id;

    // If "other", do not send the value, so that we get all options possible.
    if (value != 9999 && value != 99999) {
      params[origin_element] = value;
    }

    $.ajax(CRM.url('civicrm/fieldconditions/ajax/field-filter-values'), {
      data: params,
      dataType: 'json',
      success: function(data) {
        $select.html('');

        CRM.status(data.length + ' items');

        $.each(data, function(index, element) {
          $select.append($('<option></option>')
            .attr('value', element[extract_element + '_value'])
            .html(element[extract_element + '_label']));
        });

        $select.change();
        $('.crm-case-customdata-row-' + dest_field_id + ' > .crm-loading-element').remove();
        CRM.ddmpesChangeInProgress = false;
      },
      error: function(data) {
        CRM.alert("Network communication error. Please try again.");
        $('.crm-case-customdata-row-' + dest_field_id + ' > .crm-loading-element').remove();
        CRM.myChangeInProgress = false;
      },
    });
  };

  CRM.myExample(1, 2, $(this).val(), 'dest', 'source');
```

Support
=======

Please post bug reports in the issue tracker of this project on github:  
https://github.com/mlutfy/coop.symbiotic.fieldconditions/issues

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

If you appreciate this module, please consider donating to the CiviCRM project:  
http://civicrm.org/contribute

While we do our best to provide volunteer support for this extension, please
consider financially contributing to support or development of this extension
if you can.

Commercial support via Coop SymbioTIC:  
<https://www.symbiotic.coop>

License
=======

(C) 2017 Mathieu Lutfy <mathieu@symbiotic.coop>  
(C) 2017 Coop SymbioTIC <mathieu@symbiotic.coop>

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.
