<?php

require_once('svcs/micro.php');

class AuthGet extends MicroService {

  function __construct() {
    global $conf;

    parent::__construct();
    $this->set_origin(get_class());
    $this->autoresponse(TRUE);
  }

  public function get($token) {
    $tokens = $this->_ldap->add_cname('dc=users');
    if (!$tokens = $this->_ldap->search($tokens, 'token=' . $token)) {
      $this->fatal('token not found', 401);
    }

    foreach ($tokens as $num => $value) {
      if ($value['status'] === 'valid') {
        return $this->set_okay();
      }
    }

    $this->fail('token is invalid');
  }

  public function run() {
    $this->get($this->_uri->fields[0]);
  }
}
