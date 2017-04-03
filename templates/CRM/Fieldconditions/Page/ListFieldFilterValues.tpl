<h3>Filter values</h3>

<table class="crm-fieldconditions-filtervalues">
<tr>
  <th>ID</th>

  {foreach from=$settings.fields item=field}
    <th>{$field.field_label}</th>
  {/foreach}
  <th></th>
</tr>
{foreach from=$values item=row}
  <tr>
    <td>{$row.id}</td>
    {foreach from=$settings.fields item=field}
      <td>{$row[$field.db_column_name].label}</td>
    {/foreach}
    <td>
      <a href="#{$row.id}">{ts}Delete{/ts}</a>
    </td>
  </tr>
{/foreach}
</table>

{literal}
  <script>
/*
    CRM.$(".crm-fieldconditions-filtervalues > tbody").sortable({
      // connectWith: "#fieldconditions-filter-values",
      // placeholder: "ui-state-highlight"
      nested: true
    });
    CRM.$("#fieldconditions-filter-values").disableSelection();
*/
  </script>
{/literal}

<div style="padding: 1em;"><a href="/civicrm/admin/fieldconditions/filter-values/edit?reset=1&map_id={$map_id}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new</span></a></div>
