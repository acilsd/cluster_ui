<?php

require_once('svcs/micro.php');

class ObjectPost extends MicroService {

  protected $_types_get_gw;

  protected $_types_post_gw;

  protected $_fields_get_gw;

  protected $_fields_post_gw;

  function __construct() {
    parent::__construct();
    $this->set_origin(get_class());
    $this->autoresponse(TRUE);
    $this->_types_get_gw = new MicroGate('types-get');
    $this->_types_post_gw = new MicroGate('types-post');
    $this->_fields_get_gw = new MicroGate('fields-get');
    $this->_fields_post_gw = new MicroGate('fields-post');
  }

  /**
   * @param string $type
   *
   * @return bool
   */
  private function check_type(string $type) {
    $res = $this->_types_get_gw->get(':' . $type);
    if (!$res) {
      $this->fatal('unable to check existence of type '
        . $type, 521);
    }
    return $res['code'] == 200;
  }

  /**
   * @param string $field
   *
   * @return bool
   */
  private function check_field(string $field) {
    $res = $this->_fields_get_gw->get(':' . $field);
    if (!$res) {
      $this->fatal('unable to check existence of field '
        . $field, 511);
    }
    return $res['code'] == 200;
  }

  /**
   * @param array $object
   *
   * @return void
   */
  private function create_type(array $object) {
    $type_fields = [];

    /* first need to check existence and create fields if needed */
    foreach ($object as $field => $value) {
      /* type is build in */
      if ($field == 'type') {
        continue;
      }

      if (!$this->check_field($field)) {
        $this->_log->Info('need to create field ' . $field);
        $type_fields[$field] = $this->create_field($field, $value)['type'];
      }
    }

    $res = $this->_types_post_gw->post('add', [
      'data' => [
        'name' => $object['type'],
        'fields' => $type_fields,
      ],
    ]);

    if (!$res) {
      $this->fatal('rest gw for types post failed on '
        . $object['type'], 522);
    }

    if ($res['code'] != 200) {
      $this->_log->Error('create type failed, request:');
      $this->_log->Dump($this->_types_post_gw->get_data_string());
      $this->_log->Error('response:');
      $this->_log->Dump($res);

      $this->set_message('unable to create type ' . $object['type']);
      $this->set_message(['response' => $res]);
      $this->fatal('failing', 523);
    }
  }

  /**
   * @param string $field
   * @param $value
   *
   * @return array
   */
  private function create_field(string $field, $value) {
    $request = [
      'name' => $field,
      'type' => gettype($value),
    ];

    $res = $this->_fields_post_gw->post('add', ['data' => $request]);
    if (!$res || $res['code'] != 200) {
      $this->_log->Error('add field failed, request:');
      $this->_log->Dump($this->_fields_post_gw->get_data_string());
      $this->_log->Error('response:');
      $this->_log->Dump($res);
      $this->set_message('unable to add new field type ' . $field
        . '(' . $request['type'] . ')');
      $this->set_message(['response' => $res]);
      $this->fail('failing', 512);
    }
    return $request;
  }

  private function add_object(string $fqdn, array $object) {
    $this->_log->Debug('Adding object: ' . $fqdn);

    $dn = $this->_ldap->name2dn($fqdn);
    $object = $this->_ldap->type2objectclass($object);
    $object = $this->true_fields($object);
    $object = $this->add_muted_fields($fqdn, $object);

    if (!$this->_ldap->add($dn, $object)) {
      $this->fatal(
        'adding new object failed: '
        . ldap_err2str(ldap_errno($this->_ldap->_link)), 532);

    }
  }

  /**
   * @param string $fqdn
   * @param array $mod_data
   *
   * @return void
   */
  private function modify_object(string $fqdn, array $mod_data) {
    $this->_log->Debug('modifying object: ' . $fqdn);
    $dn = $this->_ldap->name2dn($fqdn);
    $mod_data = $this->true_fields($mod_data);

    if (!$this->_ldap->mod($dn, $mod_data)) {
      $this->fatal(
        'modification failed, see data. '
        . ldap_err2str(ldap_errno($this->_ldap->_link)), 533);
    }
  }

  /**
   * @param string $fqdn
   *
   * @return void
   */
  private function delete_object($fqdn) {
    $this->_log->Debug('Deleting object: ' . $fqdn);
    if (!$this->_ldap->del($this->_ldap->name2dn($fqdn))) {
      $this->fatal(
        'delete failed, see data. '
        . ldap_err2str(ldap_errno($this->_ldap->_link)), 534);
    }
  }

  /* add some hidden fields */
  private function add_muted_fields($name, $object) {
    global $conf;

    /* needed mostly for powerdns */
    $object['dc'] = explode('.', $name)[0];
    /* TODO: hosts manager */
    $object['arecord'] = $conf['ip'];
    $object['associateddomain'] = $name;
    return $object;
  }

  /* add 'true' prefix to field names */
  protected function true_fields($object) {
    static $ignore = [
      'type',
      'uid',
      'homedirectory',
      'loginshell',
      'givenname',
      'sn',
      'cn',
      'gidnumber',
      'uidnumber',
      'objectclass',
    ];

    $new_object = [];
    foreach ($object as $field => $val) {
      if (in_array(strtolower($field), $ignore)) {
        $new_object[$field] = $val;
      }
      else {
        $new_object['true' . $field] = $val;
      }
    }
    return $new_object;
  }

  public function post() {
    $this->set_okay();

    $this->_log->Debug('starting object post');
    if (!$this->_uri->scope) {
      $this->fail('empty scope');
      return FALSE;
    }

    $handler = NULL;

    switch ($this->_uri->scope) {
      case 'add':
        $handler = function ($fqdn, $object) {
          if (sizeof($object) == 0) {
            $this->fatal('empty objects not allowed', 331);
          }
          
          if (!isset($object['type'])) {
            $this->fatal('object type not set for '. $fqdn);
          }

          if (!$this->check_type($object['type'])) {
            $this->create_type($object);
          }

          $this->add_object($fqdn, $object);
        };
        break;
      case 'mod':
        $handler = function ($fqdn, $mod_data) {
          $this->modify_object($fqdn, $mod_data);
        };
        break;
      case 'del':
        $handler = function ($index, $fqdn) {
          $this->delete_object($fqdn);
        };
        break;
      default:
        $this->fatal('Unknown scope ' . $this->_uri->scope);
    }

    foreach ($this->get_post_data()['data'] as $fqdn => $object) {
      $handler($fqdn, $object);
    }

    return $this->_response['data'] = ($this->_response['status'] == 'okay');
  }

  public function run() {
    return $this->post();
  }
}
