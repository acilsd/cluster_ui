<?php

require_once('gw/rest.php');

/* a gw to a get/post microservice */

class MicroGate extends RESTGate {

  protected $service;

  protected $port;

  function __construct($service = '') {
    parent::__construct();

    $this->_log = new Log(get_class());
    $this->port = -1;
    $this->set_service($service);
    return $this;
  }

  function set_service($service) {
    $this->service = 'truths-' . $service;
  }

  public function prepare_url($url_path) {
    global $conf;

    if ($this->port === -1) {
      $this->port = getservbyname($this->service, 'tcp');
      if ($this->port === FALSE) {
        $message = 'getservbyname for ' . $this->service . ' failed';
        $this->_log->Error($message);
        throw new Exception($message);
      }
    }

    $domain = parse_url($url_path, PHP_URL_HOST);
    if ($domain == "") {
      $domain = 'http://' . $conf['cname'] . '.truths.world';
      return $domain . ':' . $this->port . '/' . ltrim($url_path, '/');
    }

    return 'http://' . $domain . ':' . $this->port . parse_url($url_path, PHP_URL_PATH);
  }

  public function get($url_path, $params = []) {
    $url = $this->prepare_url($url_path);
    $this->_log->Debug('calling REST GET on ' . $url);

    $rest_get = new ReflectionMethod('RESTGate', 'get');
    return $rest_get->invokeArgs($this, [$url, $params]);
  }

  public function post($url_path, $params) {
    $url = $this->prepare_url($url_path);
    $this->_log->Debug('calling REST POST on ' . $url);

    $rest_post = new ReflectionMethod('RESTGate', 'post');
    return $rest_post->invokeArgs($this, [$url, $params]);
  }
}
