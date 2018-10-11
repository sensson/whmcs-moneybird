<?php

namespace WHMCS\Module\Addon\Moneybird\Controllers;

abstract class ActionController extends Controller {
  protected $actions = array();
  protected $default_action = 'list';

  public function getActionName($name) {
    $current_action = $name;

    if (!array_key_exists($name, $this->actions)) {
      $current_action = $this->default_action;
    }

    return $current_action;
  }

  public function getActionController($name) {
    $current_action = $this->getActionName($name);
    return new $this->actions[$current_action]['class']();
  }

  public function getActions() {
    return $this->actions;
  }

  public function get($smarty, $vars) {
    $controller = $this->getActionController($_GET['action']);
    return $controller->get($smarty, $vars);
  }

  public function post($smarty, $vars) {
    $controller = $this->getActionController($_GET['action']);
    return $controller->post($smarty, $vars);
  }
}
