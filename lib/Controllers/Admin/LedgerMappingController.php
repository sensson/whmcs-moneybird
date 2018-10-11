<?php

namespace WHMCS\Module\Addon\Moneybird\Controllers\Admin;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \WHMCS\Module\Addon\Moneybird\Controllers\Controller;
use \WHMCS\Module\Addon\Moneybird\Models\Links\LedgerLink;

class LedgerMappingController extends Controller {
  protected $template = 'admin/ledger_mapping.tpl';

  public function get($smarty, $vars) {

    // This connects to Moneybird
    $connection = new \Picqer\Financials\Moneybird\Connection();
    $connection->setAccessToken($vars['AccessToken']);
    $connection->setAdministrationId($vars['AdministrationId']);
    $moneybird = new \Picqer\Financials\Moneybird\Moneybird($connection);

    // Get a list of revenue ledgers. The most important values are:
    // id - The id at Moneybird
    // name - The description
    $ledgers = array();
    foreach ($moneybird->ledgerAccount()->getAll() as $ledger) {
      if ($ledger->account_type == 'revenue') {
        $ledgers[$ledger->id] = $ledger->name;
      }
    }
    $smarty->assign('ledgers', $ledgers);

    // Get a list of product groups. The most important values are:
    // id - The id within WHMCS
    // name - The description
    $product_groups = array();
    foreach (Capsule::table('tblproductgroups')->select()->get() as $product_group) {
      $product_groups[$product_group->id] = $product_group->name;
    }

    $smarty->assign('product_groups', $product_groups);

    // Get the current defaults from the the Moneybird ledger table
    // whmcs_product_group_id - The product group id in WHMCS
    // moneybird_ledger_id - The ledger ID in Moneybird
    $selected_ledgers = array();
    foreach (LedgerLink::all() as $ledger_link) {
      $selected_ledgers[$ledger_link->whmcs_product_group_id] = $ledger_link->moneybird_ledger_id;
    }
    $smarty->assign('selected_ledgers', $selected_ledgers);

    return parent::get($smarty, $vars);
  }

  public function post($smarty, $vars) {
    // Process ledger links and add them to the ledger_link table
    foreach ($_POST as $product_group => $moneybird_ledger_id) {
      // Only process objects prefixed with group_
      if (preg_match('/^group_/', $product_group)) {
        $product_group_id = str_replace('group_', '', $product_group);
        $ledger_link = LedgerLink::updateOrCreate(
          ['whmcs_product_group_id' => $product_group_id],
          ['moneybird_ledger_id' => $moneybird_ledger_id]
        );
      }
    }

    // Return the settings page
    return self::get($smarty, $vars);
  }
}
