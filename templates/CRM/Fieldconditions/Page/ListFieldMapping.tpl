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
      <a href="{crmURL p='civicrm/admin/fieldconditions/fields' q="reset=1&map_id=`$row.id`"}">{ts}Fields{/ts}</a> |
      <a href="{crmURL p='civicrm/admin/fieldconditions/filter-values' q="reset=1&map_id=`$row.id`"}">{ts}Values{/ts}</a> |
      <a href="#{$row.id}">{ts}Delete{/ts}</a>
    </td>
  </tr>
{/foreach}
</table>

<div style="padding: 1em;"><a href="{crmURL p='civicrm/admin/fieldconditions/add-map' q='reset=1'}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span>{ts}New{/ts}</span></a></div>
{/crmScope}
