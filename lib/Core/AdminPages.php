<?php

namespace WHMCS\Module\Addon\Moneybird\Core;

class AdminPages extends Pages {
  protected $default = 'home';
  protected $pages = array(
    // Every page identifier needs a translated string
    'home' => array(
      'class' => '\\WHMCS\\Module\\Addon\\Moneybird\\Controllers\\Admin\\HomeController',
      'description' => 'Home',
    ),
    'ledger_mapping' => array(
      'class' => '\\WHMCS\\Module\\Addon\\Moneybird\\Controllers\\Admin\\LedgerMappingController',
      'description' => 'Manage ledgers',
      'type' => 'page',
    ),
    'tax_mapping' => array(
      'class' => '\\WHMCS\\Module\\Addon\\Moneybird\\Controllers\\Admin\\TaxMappingController',
      'description' => 'Manage taxes',
      'type' => 'page',
    ),
  );
}
