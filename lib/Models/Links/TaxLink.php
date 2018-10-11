<?php

namespace WHMCS\Module\Addon\Moneybird\Models\Links;

class TaxLink extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'mod_moneybird_tax_links';
  public $primaryKey = 'whmcs_tax_id';
  protected $fillable = ['whmcs_tax_id', 'moneybird_tax_id'];
}
