<?php

require_once("svcs/micro.php");

class FieldsPost extends MicroService {

  protected $_post_data;

  function __construct() {
    global $conf;

    /* init microservice */
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
    switch ($this->_uri->scope) {
      case 'add':
        $this->add($data);
        break;
      case 'del':
        $this->del($this->filter);
        break;
      case 'mod':
        $this->never_impl();
    }
  }

  public function del($field) {

    $name = 'true' . $field;
    $dn = $this->_ldap->name2dn($name . '.schema');

    $entry = $this->_ldap->read($dn);
    $real_dn = $entry['dn'];

    $res = $this->_ldap->del($real_dn, $attributes);

    if (!$res) {
      $this->fatal(
        'deleting field object class failed: '
        . ldap_err2str(ldap_errno($this->_ldap->_link)),
        542);
    }
    else {
      $this->set_okay();
    }
    return $this->_response['data'] = $res;
  }

  /* add an object to ldap cn=schema,cn=config that
   * adds an new attribute to schema */
  public function add($data) {

    $field = $data['data']['name'];
    $name = 'true' . $field;
    $type = $data['data']['type'];

    $this->_log->Debug('start add field: ' . $field . ' type: ' . $type);
    $field_info = 'EQUALITY ';
    switch ($type) {
      /* we store integers and strings in LDAP */
      case 'integer':
        $field_info .= 'integerMatch ORDERING integerOrderingMatch '
          . 'SYNTAX 1.3.6.1.4.1.1466.115.121.1.27 ';
        break;
      default:
        $field_info .= 'caseExactMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 ';
    }

    /* this is a basic set of attributes to add a new
     * objectclass in schema describing a field */
    $attributes = [
      'objectClass' => ['olcSchemaConfig'],
      'olcAttributeTypes' => [
        '( ' . $this->_ldap->genid()
        . " NAME '" . $name . "' DESC '' "
        . $field_info . " )",
      ],
      'cn' => $name . '-schema',
    ];
    $dn = $this->_ldap->name2dn($name . '.schema');
    $res = $this->_ldap->add($dn, $attributes);
    $this->_log->Info('done add field: ' . $field . (($res) ? ' OKAY'
        : ' FAILED'));
    if (!$res) {
      $this->fatal(
        'adding new field object class to schema failed: '
        . ldap_err2str(ldap_errno($this->_ldap->_link)),
        541);
    }
    else {
      $this->set_okay();
    }
    return $this->_response['data'] = $res;
  }


}
