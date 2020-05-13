{if !$hasAuthorised}
  <p><strong>Please add the required details and Authorize MYOB extension to Sync data with your MYOB account.</strong></p>
{/if}
{if !$hasSelectedCompany}
  <p><strong>Please select the company.</strong></p>
{/if}

{if $errors and $errors|@count > 0}
  <div class="messages error no-popup crm-not-you-message">
    <p>Please correct the following errors.</p>
    <ul>
    {foreach from=$errors item=error}
        <li>{$error}</li>
    {/foreach}
    </ul>
  </div>
{/if}

<table class="form-layout">
  {foreach from=$elementNames item=element}
    {assign var="elementName" value=$element.name}
    <tr>
      <td class="label">{$form.$elementName.label}</td>
      <td>
        {if $elementName != 'myob_password'}
          {$form.$elementName.html}
        {else}
          <input size="50" value="{$form.$elementName.value}" type="password" name="myob_password" id="myob_password" class="crm-form-text">
        {/if}<br />
        <span class="description">{ $element.description }</span>
        {if $elementName == 'myob_selected_company_id'}
          <a href="{crmURL p='civicrm/myob/fetchcompanies'}">Refresh List</a>
        {/if}
        {if $elementName == 'myob_selected_taxcode_id' || $elementName == 'myob_selected_freighttaxcode_id'}
          <a href="{crmURL p='civicrm/myob/fetchtaxcodes'}">Refresh List</a>
        {/if}
        {if $elementName == 'myob_selected_account_id'}
          <a href="{crmURL p='civicrm/myob/fetchaccounts'}">Refresh List</a>
        {/if}
      </td>
    </tr>
  {/foreach}
</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>