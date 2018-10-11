<?php

namespace WHMCS\Module\Addon\Moneybird\Controllers\Admin;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \WHMCS\Module\Addon\Setting;
use \WHMCS\Module\Addon\Moneybird\Controllers\Controller;
use \WHMCS\Module\Addon\Moneybird\Models\Links\TaxLink;

class TaxMappingController extends Controller {
  protected $template = 'admin/tax_mapping.tpl';

  public function get($smarty, $vars) {

    // This connects to Moneybird
    $connection = new \Picqer\Financials\Moneybird\Connection();
    $connection->setAccessToken($vars['AccessToken']);
    $connection->setAdministrationId($vars['AdministrationId']);
    $moneybird = new \Picqer\Financials\Moneybird\Moneybird($connection);

    // Get a list of tax rates. The most important values are:
    // id - The id at Moneybird
    // name - The description
    $moneybird_taxes = array();
    foreach ($moneybird->TaxRate()->getAll() as $rate) {
      if ($rate->tax_rate_type == 'sales_invoice') {
        $moneybird_taxes[$rate->id] = $rate->name;
      }
    }
    $smarty->assign('moneybird_taxes', $moneybird_taxes);

    // Get a list of taxes. The most important values are:
    // id - The id within WHMCS
    // country - The country the tax is assigned to
    // taxrate - The tax percentage
    $whmcs_taxes = array();
    foreach (Capsule::table('tbltax')->select()->get() as $rate) {
      $whmcs_taxes[$rate->id] = $rate->country . ' - ' . $rate->taxrate . '%';
    }
    $smarty->assign('whmcs_taxes', $whmcs_taxes);

    // Get the current defaults from the the Moneybird ledger table
    // whmcs_product_group_id - The product group id in WHMCS
    // moneybird_ledger_id - The ledger ID in Moneybird
    $selected_taxes = array();
    foreach (TaxLink::all() as $tax_link) {
      $selected_taxes[$tax_link->whmcs_tax_id] = $tax_link->moneybird_tax_id;
    }
    $smarty->assign('selected_taxes', $selected_taxes);

    return parent::get($smarty, $vars);
  }

  public function post($smarty, $vars) {
    // Process tax links and add them to the tax_link table
    foreach ($_POST as $tax_option => $moneybird_tax_id) {
      // Only process objects prefixed with tax_
      if (preg_match('/^tax_/', $tax_option)) {
        $tax_option_id = str_replace('tax_', '', $tax_option);
        $tax_link = TaxLink::updateOrCreate(
          ['whmcs_tax_id' => $tax_option_id],
          ['moneybird_tax_id' => $moneybird_tax_id]
        );
      }
    }

    // Return the settings page
    return self::get($smarty, $vars);
  }
}
