<?php

/* abstract microservice that transfers our api
 * calls like get() into particular backend (ldap). */

class AGate {

  protected $_log;

  function __construct() {
  }

  private function _not_impl() {
    throw new Exception('method is not supported by this gateway.');
  }

  /* public functions that each gateway
   * must implement: */

  public function get($url) {
    $this->_not_impl();
  }

  public function post($url, $params) {
    $this->_not_impl();
  }
}

