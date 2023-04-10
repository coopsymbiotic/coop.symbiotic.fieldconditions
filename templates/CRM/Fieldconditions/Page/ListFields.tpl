{crmScope extensionKey="coop.symbiotic.fieldconditions"}
<table>
<tr>
  <th>{ts}Entity{/ts}</th>
  <th>{ts}Field{/ts}</th>
  <th>{ts}Database Column Name{/ts}</th>
  <th></th>
</tr>
{foreach from=$fields item=row}
  <tr>
    <td>{$row.entity}</td>
    <td>{$row.field_label}</td>
    <td>{$row.column_name}</td>
    <td>
      <a href="{crmURL p='civicrm/admin/fieldconditions/fields/delete' q="map_id=`$map_id`&field=`$row.column_name`"}" data-civiurl="civicrm/admin/fieldconditions" data-civiparams="map_id={$row.id}&delete=1" class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash" aria-hidden="true"></i> {ts}Delete{/ts}</span></a>
    </td>
  </tr>
{/foreach}
</table>
<div style="padding: 1em;"><a href="{crmURL p='civicrm/admin/fieldconditions/fields/add' q="reset=1&map_id=`$map_id`"}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new field</span></a></div>
{/crmScope}
