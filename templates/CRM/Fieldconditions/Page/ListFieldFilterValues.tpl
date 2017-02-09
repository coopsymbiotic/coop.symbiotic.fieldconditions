<div style="padding: 1em;"><a href="/civicrm/admin/fieldconditions/filter-values/add?reset=1" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new</span></a></div>

<table>
<tr>
  <th>ID</th><th>Source value</th><th>Destination value</th><th></th>
</tr>
{foreach from=$rows item=row}
  <tr>
    <td>{$row.id}</td>
    <td>{if $row.source_label != $oldlabel}{$row.source_label}{/if}</td>
    <td>{$row.dest_label}</td>
    <td><a href="#{$row.id}">delete</a></td>
  </tr>
  {assign var='oldlabel' value=$row.source_label}
{/foreach}
</table>
