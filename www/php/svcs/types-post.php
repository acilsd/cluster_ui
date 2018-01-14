<?php

require_once("svcs/micro.php");

class TypesPost extends MicroService {

  protected $_post_data;

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

    $data = $this->get_post_data();
    $this->post($data);
  }

  public function post($data) {

    $data = $data['data'];
    $name = 'trueType' . $data['name'];

    $this->_log->Debug('start add type: ' . $name);
    $dn = $this->_ldap->name2dn($name . '.schema');

    /* new schema object */
    $object['objectClass'] = ['olcSchemaConfig'];
    $object['cn'] = $name;

    /* new attributes */
    foreach ($data['fields'] as $field => $type) {
      /* TODO: check field existance */
      /* TODO: create missing fields */
    }

    $new_data = [];
    foreach ($data['fields'] as $fname => $type) {
      $new_data['fields']['true' . $fname] = $type;
    }

    /* new objectclass */
    $object['olcObjectClasses']
      = "( " . $this->_ldap->genocid()
      . " NAME '" . $name
      . "' SUP 'trueObject' STRUCTURAL MAY ( " . implode(' $ ',
        array_keys($new_data['fields'])) . ' ) )';

    $res = $this->_ldap->add($dn, $object);
    $this->_log->Info('ldap schema for ' . $name . (($res) ? ' OKAY'
        : ' FAILED'));

    if (!$res) {
      $this->fatal(
        'adding new type object class to schema failed: '
        . ldap_err2str(ldap_errno($this->_ldap->_link)),
        524);
    }
    else {
      $this->set_okay();
    }
    return $this->_response['data'] = $res;

  }


}
