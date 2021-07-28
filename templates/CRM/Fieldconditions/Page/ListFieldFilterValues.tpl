{crmScope key='coop.symbiotic.fieldconditions'}

{* @todo Use a DataTable instead? *}
<table class="crm-fieldconditions-filtervalues">
<thead>
<tr>
  <th>{ts}ID{/ts}</th>

  {foreach from=$settings.fields item=field}
    <th>{$field.field_label}</th>
  {/foreach}
  <th></th>
</tr>
</thead>
{foreach from=$values item=row}
  <tr>
    <td>{$row.id}</td>
    {foreach from=$settings.fields item=field}
      <td>{$row[$field.column_name].label}</td>
    {/foreach}
    <td>
      <a href="{crmURL p='civicrm/admin/fieldconditions/filter-values/edit' q="action=delete&reset=1&map_id=`$map_id`&id=`$row.id`"}">{ts}Delete{/ts}</a>
    </td>
  </tr>
{/foreach}
</table>

  <div style="padding: 1em;"><a href="{crmURL p='civicrm/admin/fieldconditions/filter-values/edit' q="reset=1&map_id=`$map_id`"}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new field filter value</span></a></div>
{/crmScope}
