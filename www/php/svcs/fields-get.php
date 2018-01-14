<?php

require_once("svcs/micro.php");

class FieldsGet extends MicroService {

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

  public function get($field) {
    $field = 'cn=true' . $field . ',cn=schema';
    $res = $this->_ldap->read($field, ['olcattributetypes']);
    if ($res !== FALSE) {
      $this->set_okay();
      return $this->_response['data'] = $res;
    }
    $this->set_fail('field not found');
    $this->_log->Info('done get field: ' . $field . ' FAILED');
    return $this->_response['data'] = FALSE;
  }


}
