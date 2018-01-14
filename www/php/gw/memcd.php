<?php

require_once('gw/agate.php');

class MemCd extends AGate {

  protected $_mcd;

  protected $_log;

  function __construct(
    $host,
    $port
  ) {
    global $conf;

    parent::__construct();
    $this->_log = new Log(get_class());

    $this->_mcd = new Memcached();

    if (!$this->_mcd->addServer($host, $port)) {
      throw new Exception('Unable to connect to memcached on '
        . $host . ':' . $port);
    }

  }

  function get($url) {
    $url = strtolower($url);
    return $this->_mcd->get($url);
  }

  function post($url, $data) {
    $url = strtolower($url);
    return $this->_mcd->set($url, $data);
  }

  function del($url) {
    $url = strtolower($url);
    $this->_log->Debug('deleting cache: ' . $url);
    return $this->_mcd->delete($url);
  }

  function cache($url, $function) {
    $url = strtolower($url);

    if (($res = $this->get($url)) === FALSE) {
      $this->_log->Debug('cache miss: ' . $url);
      $res = $function();

      if ($this->post($url, $res) === FALSE) {
        $this->_log->Debug('cache not stored: error ' . $this->_mcd->getResultCode());
      }
    }
    else {
      $this->_log->Debug('cache hit: ' . $url);
    }
    return $res;
  }
}
