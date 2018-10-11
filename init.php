<?php

// Require any libraries needed for the module to function.
require __DIR__ . '/vendor/autoload.php';

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Get the Moneybird settings
 *
 * @param none
 * @return array
 */
function getMoneybirdSettings() {
  $settings = Capsule::table('tbladdonmodules');
  $settings = $settings->select('setting', 'value');
  $settings = $settings->where('module', '=', 'moneybird');

  $module_settings = array();
  foreach ($settings->get() as $setting) {
    $module_settings[$setting->setting] = $setting->value;
  }

  return $module_settings;
}

/**
 * Create a Moneybird connection
 *
 * @param none
 * @return object \Picqer\Financials\Moneybird\Moneybird
 */
function createMoneybirdConnection() {
  $module_settings = getMoneybirdSettings();

  // Set up the connection with MoneyBird
  $connection = new \Picqer\Financials\Moneybird\Connection();
  $connection->setAccessToken($module_settings['AccessToken']);
  $connection->setAdministrationId($module_settings['AdministrationId']);
  return new \Picqer\Financials\Moneybird\Moneybird($connection);
}
