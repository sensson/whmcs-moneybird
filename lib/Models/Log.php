<?php

namespace WHMCS\Module\Addon\Moneybird\Models;

class Log extends BaseModel {
  public $table = 'mod_moneybird_log';
  public $primaryKey = 'id';
  public $timestamps = true;
}
