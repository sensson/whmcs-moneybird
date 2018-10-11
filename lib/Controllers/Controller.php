<?php

namespace WHMCS\Module\Addon\Moneybird\Controllers;

abstract class Controller {
  protected $template = 'base.tpl';

  public function get($smarty, $vars) {
    $smarty->assign('modulelink', $vars['modulelink']);
    $smarty->assign('language', $vars['_lang']);
    return $smarty->fetch($this->template);
  }

  public function post($smarty, $vars) {
    return $this->get($smarty, $vars);
  }
}
