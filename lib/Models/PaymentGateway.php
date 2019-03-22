<?php

namespace WHMCS\Module\Addon\Moneybird\Models;

// This is a very basic implementation of a tblpaymentgateways model
class PaymentGateway extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'tblpaymentgateways';
  public $primaryKey = null;

  /**
   * Find a payment method in WHMCS
   *
   * @param string $name the name of the payment method
   * @return object|null the payment gateway
   */
  public static function find($name) {
    $settings = (new PaymentGateway)->where('gateway', $name)->get();
    $object = new PaymentGateway();

    foreach ($settings as $setting) {
      $name = $setting->setting;
      $value = $setting->value;
      $object->$name = $value;
    }

    return $object;
  }

  /**
   * Filter the list of payment methods by setting
   *
   * @param string $setting the name of the setting
   * @param string $setting the name of the value
   * @return QueryBuild
   */
  public function filter($setting, $value) {
    $gateways = (new PaymentGateway)->newQuery();
    $gateways->where('setting', $setting);
    $gateways->where('value', $value);
    return $gateways;
  }
}
