<?php

require_once("svcs/micro.php");

class TypesGet extends MicroService {

  protected $_uri;

  function __construct() {
    global $conf;

    parent::__construct();

    $this->autoresponse(TRUE);
    $this->set_origin(get_class());
    $this->_ldap = new LDAPGate(
      $conf['ldap_2']['ip'],
      $conf['ldap_2']['port'],
      $conf['ldap_2']['login'],
      $conf['ldap_2']['password'],
      $conf['ldap_2']['prefix'],
      $conf['ldap_2']['suffix'],
      $conf['ldap_2']['filter']
    );

  }

  function run() {
    $arg = $this->_uri->fields[0];
    $this->_log->Debug('requested get on arg ' . $arg);
    $this->get($arg);
  }

  public function get($type) {
    /* TODO: fix brocken search.
     * schemas add a numeric prefix in cn=config */
    $type = 'cn=trueType' . $type . ',cn=schema';
    $res = $this->_ldap->read($type, ['olcobjectclasses']);
    if ($res) {
      $this->set_okay();
      return $this->_response['data'] = $res;
    }
    $this->fatal('type not found', 421);
    return FALSE;
  }
}
