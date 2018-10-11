<?php

namespace WHMCS\Module\Addon\Moneybird\Models\Links;

class LedgerLink extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'mod_moneybird_ledger_links';
  public $primaryKey = 'whmcs_product_group_id';
  protected $fillable = ['whmcs_product_group_id', 'moneybird_ledger_id'];
}
