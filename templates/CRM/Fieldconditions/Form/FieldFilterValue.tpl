{if $action eq 8}
  <h3>{ts}Delete{/ts}</h3>

  <p>{ts}Are you sure you want to delete this combination?{/ts}</p>

  <ul>
    {foreach from=$confirm_delete_values item=label}
      <li>{$label}</li>
    {/foreach}
  </ul>
{/if}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
