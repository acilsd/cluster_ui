<?php

require_once('svcs/micro.php');

class ObjectGet extends MicroService {
  function __construct() {
    parent::__construct();
    $this->set_origin(get_class());
    $this->autoresponse(TRUE);
  }

  /* hide specific LDAP fields we do not want to show to user */
  protected function mute_fields($object) {
    static $to_mute = [
      'dn',
      'ou',
      'dc',
      'arecord',
      'associateddomain',
      'userpassword',
    ];

    if (!is_array($object)) {
      return $object;
    }

    $new_object = [];
    foreach ($object as $field => $val) {
      if (!in_array(strtolower($field), $to_mute)) {
        $new_object[$field] = $val;
      }
    }
    return $new_object;
  }

  /* remove the 'true' prefix from field names */
  protected function untrue_fields($object) {

    if (!is_array($object)) {
      return $object;
    }

    $new_object = [];
    foreach ($object as $field => $val) {
      if (substr($field, 0, 4) === 'true') {
        $new_object[substr($field, 4)] = $val;
      }
      else {
        $new_object[$field] = $val;
      }
    }
    return $new_object;
  }

  function get() {
    $dn = $this->_ldap->name2dn(lcfirst($this->_uri->cur_object));
    $fields = $this->add_objectclass($this->_uri->fields);
    $ldap_filter = FALSE;

    $prepare_val = function ($val) use ($fields) {
      $val = $this->untrue_fields($this->mute_fields($val));
      return $val;
    };

    $ldap_filter = $this->_uri->get_filter_arguments('where')[0];
    $res = NULL;

    switch ($this->_uri->scope) {
      case 'one':
        $res = $prepare_val($this->_ldap->read($dn, FALSE, $ldap_filter));
        break;
      case 'list':
        $res = array_map($prepare_val, $this->_ldap->list($dn, FALSE, $ldap_filter));
        break;
      case 'tree':
        $res = array_map($prepare_val, $this->_ldap->tree($dn, FALSE, $ldap_filter));
        break;
      default:
        $this->_log->Error('unknown scope: ' . $this->_uri->scope);
        $this->fatal('unknown scope', 301);
        break;
    }

    if (empty($res)) {
      $this->fatal(
        'object not found: '
        . ldap_err2str(ldap_errno($this->_ldap->_link)),
        431);
    }

    function query($query) {
      require_once('gw/micro.php');
      $obj_get_gw = new MicroGate('object-get');
      return $obj_get_gw->get($query);
    }

    if ($union_query = $this->_uri->get_filter_arguments('union')) {
      if ($union_data = query($union_query[0])) {
        $this->_log->Debug('union data recieved');
        $res += $union_data['data'];
      }
    }

    if ($join_query = $this->_uri->get_filter_arguments('join')) {
      if ($join_data = query($join_query[0])) {
        $this->_log->Debug('join data recieved');
        $res = $this->join_data($res, $join_data['data'], $join_query[1]);
      }
    }

    if ($fields) {
      if ($this->_uri->scope == 'one') {
        $res = array_intersect_key($res, array_flip($fields));
      } else {
        $res = array_map(function($val) use ($fields) {
          return array_intersect_key($val, array_flip($fields)); 
        }, $res);
      }
    }

    if ($this->_raw_response) {
      $this->_response = $res;
    }
    else {
      $this->_response['data'] = $res;
    }

    $this->set_okay();
  }

  function add_objectclass($fields) {
    if ($fields) {
      $fields[] = 'objectclass';
    }
    return $fields;
  }

  protected function reindex_array($array, $index_key) {
    $index = [];
    foreach ($array as $key => $val) {
      $index[$key] = $val[$index_key];
    }
    asort($index);
    $res = [];
    foreach ($index as $key => $val) {
      $res[$key] = $array[$key];
    }
    return $res;
  }

  protected function to_array($obj) {
    return is_array($obj) ? $obj : [ $obj ];
  }

  protected function sum_objects($obj1, $obj2) {
    $res = $obj1;
    unset($obj2['type']);
    foreach ($obj2 as $key => $val) {
      if (isset($obj1[$key])) {
        $res[$key] = $this->to_array($obj1[$key]) + $this->to_array($obj2[$key]);
      } else {
        $res[$key] = $val;
      }
    }

    return $res;
  }

  protected function join_data($data1, $data2, $filter) {
    $parts = explode('=', $filter);
    $arg1 = $parts[0];
    $arg2 = $parts[1];

    $this->_log->Debug('joining on: '. $arg1 . ' and '. $arg2);

    $data1 = $this->reindex_array($data1, $arg1);
    $data2 = $this->reindex_array($data2, $arg2);

    $data = [];
    while (($obj1 = current($data1)) && ($obj2 = current($data2))) {
      $key1 = key($data1);
      $key2 = key($data2);

      if ($obj1[$arg1] == $obj2[$arg2]) {
        $data[$key1] = $this->sum_objects($obj1, $obj2);
        next($data2);
      } elseif (($obj1[$arg1] < $obj2[$arg2])) {
        next($data1);
      } elseif (($obj1[$arg1] > $obj2[$arg2])) {
        next($data2);
      }
    }

    return $data;
  }

  function run() {
    $this->get();
  }
}
