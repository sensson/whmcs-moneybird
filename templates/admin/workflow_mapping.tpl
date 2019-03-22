<h2 align="left">Workflow mapping</h2>

<p>This is used to map your payment methods to workflows in Moneybird.</p>

<form method="post" action="{$modulelink}&page={$active_page}">

  <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
      {foreach from=$whmcs_payment_methods key=name item=payment_method}
        {* Set the tax_option variable *}
        {assign var="workflow_option" value="workflow_{$name}"}
        <tr>
          <td class="fieldlabel" width="20%">{$payment_method}</td>
          <td class="fieldarea">{html_options name=$workflow_option options=$moneybird_workflows selected=$selected_workflows[$name] class='form-control select-inline'}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  <div class="btn-container">
    <input type="submit" id="saveChanges" value="Save Changes" class="btn btn-primary">
  </div>
</form>
