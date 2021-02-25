{crmScope key='coop.symbiotic.fieldconditions'}
<table>
<tr>
  <th>{ts}ID{/ts}</th>
  <th>{ts}Type{/ts}</th>
  <th>{ts}Name{/ts}</th>
  <th></th>
</tr>
{foreach from=$field_maps item=row}
  <tr>
    <td>{$row.id}</td>
    <td>{$row.type}</td>
    <td>{$row.name}</td>
    <td>
      <a href="{crmURL p='civicrm/admin/fieldconditions/fields' q="reset=1&map_id=`$row.id`"}" class="button">{ts}Fields{/ts}</a>
      <a href="{crmURL p='civicrm/admin/fieldconditions/filter-values' q="reset=1&map_id=`$row.id`"}" class="button">{ts}Values{/ts}</a>
      <a href="{crmURL p='civicrm/admin/fieldconditions' q="map_id=`$row.id`&delete=1"}" data-civiurl="civicrm/admin/fieldconditions" data-civiparams="map_id={$row.id}&delete=1" class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</span></a>

    </td>
  </tr>
{/foreach}
</table>

<div style="padding: 1em; height: 50px;"><a href="{crmURL p='civicrm/admin/fieldconditions/add-map' q='reset=1'}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span>{ts}New{/ts}</span></a></div>

{literal}
<script type="text/javascript">
  CRM.$(function($) {
    $(".button.delete").click(function(e) {
      e.preventDefault();
      // This is not the correct CiviCRM way, but could not find a good example
      var dest_url = CRM.url($(this).data('civiurl'));
      var params = $(this).data('civiparams') + '&confirmed=1';

      CRM.confirm({
        width: 400,
        message: {/literal}"{ts escape='js'}Are you sure you want to delete?{/ts}"{literal}
      }).on('crmConfirm:yes', function() {
        $.post(dest_url, params).done(function(result) {window.location = CRM.url("civicrm/admin/fieldconditions", {reset: 1});});
      });
    });
  });
</script>
{/literal}

{/crmScope}
