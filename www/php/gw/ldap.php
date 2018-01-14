<?php

require_once('gw/agate.php');
require_once('gw/memcd.php');

class LDAPGate extends AGate {

  protected $_ldap;

  protected $_prefix;

  protected $_suffix;

  protected $_host;

  protected $_port;

  public $_link;

  protected $_filter;

  protected $_cname;

  protected $_cache;

  function __construct(
    $host,
    $port,
    $bind_rdn,
    $bind_password,
    $prefix = FALSE,
    $suffix = FALSE,
    $filter = FALSE
  ) {
    global $conf;

    $filter = $filter ? $filter
      : '(|(objectClass=trueUnit)(objectClass=trueService)(objectClass=trueUser)(objectClass=trueObject))';

    if ($filter[0] != '(')
      $filter = '('. $filter. ')';
    $prefix = $prefix ? $prefix : 'dc';
    $suffix = $suffix ? $suffix : ($prefix . '=' . $conf['cname']);

    parent::__construct();
    $this->_log = new Log(get_class());
    $this->_cache = new MemCd($conf['ip'], getservbyname('mem-global', 'tcp'));

    $this->_host = $host;
    $this->_port = $port;
    $this->_filter = $filter;
    $this->_prefix = $prefix;
    $this->_suffix = $suffix;

    $this->_cname = $suffix . ',dc=truths,dc=world';

    /* connect */
    $this->_link = ldap_connect($host, $port);
    ldap_set_option($this->_link, LDAP_OPT_PROTOCOL_VERSION, 3);

    /* login */
    if (!ldap_bind($this->_link, $bind_rdn, $bind_password)) {
      throw new Exception("LDAP login failed");
    }
  }

  public function name2dn($name) {
    return $this->_prefix . '=' . implode(',' . $this->_prefix . '=',
        explode('.', $name));
  }

  public function add_suffix($name) {
    return $name . ',' . $this->_suffix;
  }

  public function add_cname($name) {
    return $name . ',' . $this->_cname;
  }

  /* dc=che,dc=users,dc=xxx -> dc=users,dc=xxx */
  public function get_unit($name) {
    $this->_log->Debug('get unit: ' . $name);
    return substr(strstr($name, ','), 1);
  }

  /* add class filter w/ object classes and
   * true field names */
  public function prepare_filter($filter) {
    if ($filter) {
      if ($filter[0] != '(')
        $filter = '('. $filter. ')';
      $filter = '(&'. $this->_filter 
        . preg_replace('/((^|\()([a-zA-Z]+))/i', '${2}true${3}', $filter). ')';
      $filter = preg_replace('/((^|\()truetype\s*=([a-zA-Z]))/', '${2}ObjectClass=truetype${3}', $filter);
      return $filter;
    }
    return $this->_filter;
  }

  /* schemas are stored with a prefix in name, like
   * {21}cn=xxxx,cn=schema,cn=config
   * we need to list the whole unit in order to find
   * matching name */
  protected function name2config_name($name) {
    $name = $this->add_suffix($name);
    $unit = $this->get_unit($name);
    $this->_log->Debug('list unit: ' . $unit);

    $list = ldap_list($this->_link, $unit, $this->_filter, ['dn']);
    if ($list) {
      $list = ldap_get_entries($this->_link, $list);
    }

    if ($list) {
      foreach ($list as $key => $val) {
        $dn       = strtolower($val['dn']);
        $chk_name = strtolower('}' . substr($name, 3));
        if (strpos($dn, $chk_name) !== FALSE) {
          return $dn;
        }
      }
    }
    return $name;
  }

  public function auth($login, $passwd) {
    global $conf;
    $temp_gate = new LDAPGate(
      $conf['ldap_1']['ip'],
      $conf['ldap_1']['port'],
      $conf['ldap_1']['login'],
      $conf['ldap_1']['password']
    );

    $bind_rdn = $this->add_suffix($this->add_cname($this->_prefix . '=' . $login
      . ',dc=users'));
    $this->_log->Debug("challengers dn: " . $bind_rdn);
    return $this->_cache->cache('auth:' . $bind_rdn,
      function () use ($temp_gate, $bind_rdn, $passwd) {
        return ldap_bind($temp_gate->_link, $bind_rdn, $passwd);
      });
  }

  public function read($name, $attributes = FALSE, $filter = FALSE) {
    $skip_cache = $filter ? TRUE : FALSE;
    $name = ($this->_suffix == 'cn=config')
      ? $this->name2config_name($name)
      : $this->add_suffix($name);

    if ($attributes) {
      foreach ($attributes as $key => $val) {
        $attributes[$key] = strtolower($val);
      }
    }

    $this->_log->Dump($attributes);
    $link      = $this->_link;
    $filter    = $this->prepare_filter($filter);
    $this->_log->Debug('filter: '. $filter);
    $read_func = function () use ($link, $name, $filter) {
      $res = ldap_read($link, $name, $filter);
      if ($res) {
        $res = ldap_get_entries($link, $res);
      }
      return $res;
    };

    $res = $skip_cache
      ? $read_func()
      : $this->_cache->cache('read:' . $name, $read_func);
    $this->_log->Debug('LDAP read ' . $name . (($res) ? ' OKAY' : ' FAILED'));
    $res = $this->cleanup_fields($res);

    if ($attributes) {
      $new_res = [];
      foreach ($attributes as $attr) {
        if (isset($res[$attr])) {
          $new_res[$attr] = $res[$attr];
        }
      }
      $res = $new_res;
    }
    return $res ? $res : FALSE;
  }

  public function tree($name, $attributes = FALSE, $filter = FALSE) {
    $list = $this->list($name, $attributes, $filter);

    $desc = $list;
    if ($filter)
      $desc = $this->list($name, [ 'dn' ], FALSE);

    if ($desc) {
      foreach ($desc as $object => $val) {
        $res = $this->tree($this->name2dn($object), $attributes, $filter);
        if ($res)
          $list += $res;
      }
    }

    return $list;
  }

  public function list($name, $attributes = FALSE, $filter = FALSE) {

    $skip_cache = $filter ? TRUE : FALSE;
    if ($attributes) {
      foreach ($attributes as $key => $val) {
        $attributes[$key] = strtolower($val);
      }
    }

    $name   = $this->add_suffix($name);
    $link   = $this->_link;
    $filter = $this->prepare_filter($filter);
    $this->_log->Debug('filter: '. $filter);
    $list_func = function () use ($link, $name, $filter) {

      $sr = @ldap_list($link, $name, $filter);
      $res = [];
      if (!$sr) {
        return FALSE;
      }
      $data = ldap_get_entries($this->_link, $sr);
      for ($i = 0; $i < $data['count']; $i++) {
        /* associatedDomain contains full object name */
        $res[$data[$i]['associateddomain'][0]] = $data[$i];
      }
      return $res;
    };

    $res = $skip_cache
      ? $list_func()
      : $this->_cache->cache('list:' . $name, $list_func);
    $this->_log->Debug('LDAP list ' . $name . (($res) ? ' OKAY' : ' FAILED'));
    if (!$res) {
      $this->_log->Debug('LDAP filter: '. $filter);
    }
    $res = $this->cleanup_fields($res);
    return $res;
  }

  public function search($name, $filter) {

    $name = $this->add_suffix($name);
    $sr = ldap_search($this->_link, $name, $filter);

    $this->_log->Debug('LDAP search for ' . $filter . ' in ' . $name . (($sr)
        ? ' OKAY' : ' FAILED'));
    $res = [];
    if ($sr) {
      $data = ldap_get_entries($this->_link, $sr);
      for ($i = 0; $i < $data['count']; $i++) {
        /* associatedDomain contains full object name */
        $res[$data[$i]['associateddomain'][0]] = $data[$i];
      }
    }
    else {
      return FALSE;
    }

    $res = $this->cleanup_fields($res);
    $true_res = [];
    foreach ($res as $object => $val) {
      $true_res[$object] = $val;
    }
    return $true_res;
  }

  public function add($name, $data) {

    $name = $this->add_suffix($name);
    $res = ldap_add($this->_link, $name, $data);
    $this->_log->Info('add object to LDAP: ' . $name . (($res) ? ' OKAY'
        : ' FAILED'));
    if (!$res) {
      $this->_log->Debug('failed query:');
      $this->_log->Dump($data);
    }
    $this->_cache->del('list:' . $this->get_unit($name));
    return $res;
  }

  protected function del_recurs($name) {
    $sr = @ldap_list($this->_link, $name, '(ObjectClass=*)', ['dn']);
    if ($sr) {
      $info = ldap_get_entries($this->_link, $sr);
      $this->_log->Debug('found ' . $info['count'] . ' subentries for ' . $name);
      for ($i = 0; $i < $info['count']; $i++) {
        $this->del_recurs($info[$i]['dn']);
      }
    }
    $this->_log->Debug('deleting dn: ' . $name);
    $this->_cache->del('read:' . $name);
    $this->_cache->del('list:' . $this->get_unit($name));
    return (ldap_delete($this->_link, $name));
  }

  public function del($name) {

    $name = $this->add_suffix($name);
    $res = $this->del_recurs($name);
    $this->_log->Info('del object from LDAP: ' . $name . (($res) ? ' OKAY'
        : ' FAILED'));
    return $res;
  }

  public function mod($name, $data) {

    $name = $this->add_suffix($name);
    unset($this->data['type']);
    $this->_cache->del('read:' . $name);
    $this->_cache->del('list:' . $this->get_unit($name));
    $res = ldap_modify($this->_link, $name, $data);
    $this->_log->Info('modify object from LDAP: ' . $name . (($res) ? ' OKAY'
        : ' FAILED'));
    return $res;
  }

  public function type2objectclass($object) {

    $object['objectclass'] = [];
    $object['objectclass'][0] = 'trueType' . $object['type'];
    unset($object['type']);
    return $object;
  }

  public function objectclass2type($object) {

    /* one of objectclasses is the type */
    for ($i = 0; $i <= 1; $i++) {
      /* it starts with 'true' */
      if (strpos($object['objectclass'][$i], 'trueType') === 0) {
        return substr($object['objectclass'][$i], 8);
      }
      /* built in ocs, like users, groups */
      if (strpos($object['objectclass'][$i], 'true') === 0) {
        return substr($object['objectclass'][$i], 4);
      }
    }
    return 'undefined';
  }

  public function cleanup_fields($objects) {

    /* LDAP returns an array
     * array(2) {
     *   ["count"]=>   // number of entries
     *   int(1)
     *   [0]=>         // first entry
     *   array(28) {
     *     ["cn"]=>    // entry field data
     *     array(2) {
     *       ["count"]=>
     *       int(1)
     *       [0]=>
     *       string(5) "insci"
     *     }
     *     [0]=>       // entry field name
     *     string(2) "cn"
     *
     */

    if (!is_array($objects)) {
      return $objects;
    }

    $new_objects = [];
    foreach ($objects as $name => $object) {

      /* drop 'count', etc */
      if (!is_array($object)) {
        continue;
      }

      $new_objects[$name]['type'] = $this->objectclass2type($object);

      /* each object is an LDAP object */
      foreach ($object as $field => $data) {

        /* skip entry field names */
        if (is_integer($field)) {
          continue;
        }

        /* skip objectclasses */
        if ($field == 'objectclass') {
          continue;
        }

        /* unpack entry */
        if (is_array($data)) {
          if ($data['count'] == 1) {
            $new_objects[$name][$field] = $data[0];
          }
          else {
            for ($i = 0; $i < $data['count']; $i++) {
              $new_objects[$name][$field][] = $data[$i];
            }
          }
        }
      }
    }

    return @$objects['count'] == 1 ? $new_objects[0] : $new_objects;
  }

  public function genid() {
    $random = '';
    for ($i = 1; $i < 5; $i++) {
      $random .= ('.' . rand(1, 256));
    }
    return '1.1' . $random;
  }

  public function genocid() {
    $random = '';
    for ($i = 1; $i < 5; $i++) {
      $random .= ('.' . rand(1, 256));
    }
    return '1.1.1.1.1' . $random;
  }

}
