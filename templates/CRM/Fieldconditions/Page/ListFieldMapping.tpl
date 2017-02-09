<h3>Field conditionals</h3>

<div style="padding: 1em;"><a href="/civicrm/admin/fieldconditions/add-map?reset=1" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new field dependency filter</span></a></div>

<table>
<tr>
  <th>ID</th><th>Source field</th><th>Destination field</th><th></th>
</tr>
{foreach from=$field_maps item=row}
  <tr>
    <td>{$row.id}</td>
    <td>{$row.source_group_title} : {$row.source_field_label}</td>
    <td>{$row.dest_group_title} : {$row.dest_field_label}</td>
    <td><a href="{crmURL p='civicrm/admin/fieldconditions/filter-values' q="reset=1&map_id=`$row.id`"}">{ts}Values{/ts}</a> | <a href="#{$row.id}">{ts}Delete{/ts}</a></td>
  </tr>
{/foreach}
</table>
