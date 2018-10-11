<h2 align="left">Tax mapping</h2>

<p>This is used to map your tax rates to tax rates in Moneybird. Consult your
  accountant if you are unsure.</p>

<form method="post" action="{$modulelink}&page={$active_page}">

  <h3>Generic</h3>
  <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
      <tr>
        <td class="fieldlabel" width="20%">Local tax rate</td>
        <td class="fieldarea">{html_options name=tax_9991 options=$moneybird_taxes selected=$selected_taxes[9991] class='form-control select-inline'}</td>
      </tr>
      <tr>
        <td class="fieldlabel" width="20%">EU tax rate</td>
        <td class="fieldarea">
          {html_options name=tax_9992 options=$moneybird_taxes selected=$selected_taxes[9992] class='form-control select-inline'}
          This rate will be set if the customer is from Europe and tax exempt.
        </td>
      </tr>
      <tr>
        <td class="fieldlabel" width="20%">No tax rate</td>
        <td class="fieldarea">
          {html_options name=tax_9993 options=$moneybird_taxes selected=$selected_taxes[9993] class='form-control select-inline'}
          This rate will be set if the customer is not from Europe and tax exempt.
        </td>
      </tr>
    </tbody>
  </table>

  <h3>Tax rules</h3>
  <p>You may have your own tax rules. VAT MOSS is one of the regulations that requires it.</p>
  <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
      {foreach from=$whmcs_taxes key=tax_id item=tax}
        {* Set the tax_option variable *}
        {assign var="tax_option" value="tax_{$tax_id}"}
        <tr>
          <td class="fieldlabel" width="20%">{$tax}</td>
          <td class="fieldarea">{html_options name=$tax_option options=$moneybird_taxes selected=$selected_taxes[$tax_id] class='form-control select-inline'}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  <div class="btn-container">
    <input type="submit" id="saveChanges" value="Save Changes" class="btn btn-primary">
  </div>
</form>
