<h3>Filter values</h3>

<ol id="fieldconditions-filter-values">
  {foreach from=$rows item=row}
    <li><span>{$row.source_label}</span> <a href="/civicrm/admin/fieldconditions/filter-values/edit?reset=1&map_id={$map_id}&source_value={$row.source_value}">add</a>
      <ol style="padding-left: 3em;">
        {foreach from=$row.values item=val}
          <li>{$val.id} : {$val.dest_label} <a href="#{$row.id}">delete</a></li>
        {/foreach}
      </ol>
    </li>
  {/foreach}
</ol>

{literal}
  <script>
    CRM.$("#fieldconditions-filter-values").sortable({
      // connectWith: "#fieldconditions-filter-values",
      // placeholder: "ui-state-highlight"
      nested: true
    });
    CRM.$("#fieldconditions-filter-values").disableSelection();
  </script>
{/literal}

<div style="padding: 1em;"><a href="/civicrm/admin/fieldconditions/filter-values/edit?reset=1&map_id={$map_id}" class="button action-item"><span><span class="icon ui-icon-circle-plus"></span> Add new</span></a></div>
