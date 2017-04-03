<h3>Fieldcondition (valuefilter) fields</h3>

<table>
<tr>
  <th>{ts}Field{/ts}</th><th>{ts}Label{/ts}</th><th>{ts}DB{/ts}</th><th></th>
</tr>
{foreach from=$fields item=row}
  <tr>
    <td>{$row.field_name}</td>
    <td>{$row.field_label}</td>
    <td>{$row.db_column_name}</td>
    <td>
      <a href="#{$row.id}">{ts}Delete{/ts}</a>
    </td>
  </tr>
{/foreach}
</table>

<div style="padding: 1em;"><a href="/civicrm/admin/fieldconditions/fields/add?reset=1&map_id={$map_id}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new field</span></a></div>
