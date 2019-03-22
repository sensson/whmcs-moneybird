<?php

namespace WHMCS\Module\Addon\Moneybird\Models\Links;

class WorkflowLink extends \WHMCS\Module\Addon\Moneybird\Models\BaseModel {
  public $table = 'mod_moneybird_workflow_links';
  public $primaryKey = 'whmcs_payment_method';
  public $incrementing = false;
  protected $fillable = ['whmcs_payment_method', 'moneybird_workflow_id'];
}
