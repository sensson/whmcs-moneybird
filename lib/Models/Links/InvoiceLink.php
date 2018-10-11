<?php

namespace WHMCS\Module\Addon\Moneybird\Models\Links;

class InvoiceLink extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'mod_moneybird_invoice_links';
  public $primaryKey = 'whmcs_invoice_id';
  protected $fillable = ['whmcs_invoice_id', 'moneybird_invoice_id'];
}
