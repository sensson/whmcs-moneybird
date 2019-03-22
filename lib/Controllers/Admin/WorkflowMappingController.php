<?php

namespace WHMCS\Module\Addon\Moneybird\Controllers\Admin;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \WHMCS\Module\Addon\Setting;
use \WHMCS\Module\Addon\Moneybird\Controllers\Controller;
use \WHMCS\Module\Addon\Moneybird\Models\PaymentGateway;
use \WHMCS\Module\Addon\Moneybird\Models\Links\WorkflowLink;

class WorkflowMappingController extends Controller {
  protected $template = 'admin/workflow_mapping.tpl';

  public function get($smarty, $vars) {

    // This connects to Moneybird
    $connection = new \Picqer\Financials\Moneybird\Connection();
    $connection->setAccessToken($vars['AccessToken']);
    $connection->setAdministrationId($vars['AdministrationId']);
    $moneybird = new \Picqer\Financials\Moneybird\Moneybird($connection);

    // Get a list of workflows. The most important values are:
    $moneybird_workflows = array();
    foreach ($moneybird->Workflow()->getAll() as $workflow) {
      if ($workflow->type == 'InvoiceWorkflow') {
        $moneybird_workflows[$workflow->id] = $workflow->name;
      }
    }
    $smarty->assign('moneybird_workflows', $moneybird_workflows);

    // As WHMCS stores settings as individual records we need more queries
    $whmcs_payment_methods = array();
    $gateways = PaymentGateway::filter('visible', 'on')->distinct('gateway')->get();
    foreach ($gateways as $gateway) {
      $name = PaymentGateway::find($gateway->gateway);
      $whmcs_payment_methods[$gateway->gateway] = $name->name;
    }
    $smarty->assign('whmcs_payment_methods', $whmcs_payment_methods);

    // Get the current defaults from the Moneybird workflow table
    $selected_workflows = array();
    foreach (WorkflowLink::all() as $workflow_link) {
      $selected_workflows[$workflow_link->whmcs_payment_method] = $workflow_link->moneybird_workflow_id;
    }
    $smarty->assign('selected_workflows', $selected_workflows);

    return parent::get($smarty, $vars);
  }

  public function post($smarty, $vars) {
    // Process payment methods and add them to the workflow_link table
    foreach ($_POST as $payment_method => $moneybird_workflow_id) {
      // Only process objects prefixed with workflow_
      if (preg_match('/^workflow_/', $payment_method)) {
        $payment_method_name = str_replace('workflow_', '', $payment_method);
        $workflow_link = WorkflowLink::updateOrCreate(
          ['whmcs_payment_method' => $payment_method_name],
          ['moneybird_workflow_id' => $moneybird_workflow_id]
        );
      }
    }

    // Return the settings page
    return self::get($smarty, $vars);
  }
}
