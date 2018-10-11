<?php

namespace WHMCS\Module\Addon\Moneybird\Core;

abstract class Pages {
  protected $default = 'home';
  protected $pages = array();

  public function getPageName($name) {
    $active_page = $name;

    if (!array_key_exists($name, $this->pages)) {
      $active_page = $this->default;
    }

    return $active_page;
  }

  public function getController($name) {
    $active_page = $this->getPageName($name);
    return new $this->pages[$active_page]['class']();
  }

  public function getPages() {
    return $this->pages;
  }

  public function get($name, $smarty, $vars) {
    $smarty->assign('pages', $this->pages);
    $smarty->assign('active_page', self::getPageName($name));

    $controller = $this->getController($name);
    return $controller->get($smarty, $vars);
  }

  public function post($name, $smarty, $vars) {
    $smarty->assign('pages', $this->pages);
    $smarty->assign('active_page', self::getPageName($name));

    $controller = $this->getController($name);
    return $controller->post($smarty, $vars);
  }
}
