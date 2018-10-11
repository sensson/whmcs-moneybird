<?php

namespace WHMCS\Module\Addon\Moneybird\Models\Links;

class CustomerLink extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'mod_moneybird_customer_links';
  public $primaryKey = 'whmcs_customer_id';
  protected $fillable = ['whmcs_customer_id', 'moneybird_customer_id'];
}
