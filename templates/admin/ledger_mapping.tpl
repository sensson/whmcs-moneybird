<h2 align="left">Ledger mapping</h2>

<p>Ledgers are called categories in Moneybird.</p>

<p>Map your product groups to revenue ledgers in Moneybird. This is
  recommended as it ensures all invoices are mapped to the right ledger. If
  it finds unmapped products it will leave the ledger empty when
  importing invoices into Moneybird.</p>

<form method="post" action="{$modulelink}&page={$active_page}">
  <h3 align="left">Internal ledgers</h3>

  <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
      <tr>
        <td class="fieldlabel" width="20%">Default</td>
        <td class="fieldarea">
          {html_options name=group_0 options=$ledgers selected=$selected_ledgers[0] class='form-control select-inline'}
          This is the default mapping if no other ledger can be found
        </td>
      </tr>
      <tr>
        <td class="fieldlabel" width="20%">Discounts</td>
        <td class="fieldarea">
          {html_options name=group_99998 options=$ledgers selected=$selected_ledgers[99998] class='form-control select-inline'}
          This can be used to map discounts to its own ledger
        </td>
      </tr>
      <tr>
        <td class="fieldlabel" width="20%">Domains</td>
        <td class="fieldarea">
          {html_options name=group_99999 options=$ledgers selected=$selected_ledgers[99999] class='form-control select-inline'}
          This mapping is used for domains
        </td>
      </tr>
    </tbody>
  </table>

  <h3 align="left">Product groups</h3>

  <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
      {foreach from=$product_groups key=group_id item=group_name}
        {* Set the ledger_name variable *}
        {assign var="group_option" value="group_{$group_id}"}
        <tr>
          <td class="fieldlabel" width="20%">{$group_name}</td>
          <td class="fieldarea">{html_options name=$group_option options=$ledgers selected=$selected_ledgers[$group_id] class='form-control select-inline'}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  <div class="btn-container">
    <input type="submit" id="saveChanges" value="Save Changes" class="btn btn-primary">
  </div>
</form>
