<?php

require_once("svcs/micro.php");
require_once("gw/container-gw.php");
require_once("lib/utils/utils.php");

class Shepherd extends MicroService {

  /** @var ContainerGW */
  protected $_container_gw;

  /** @param array $container_engine */
  function __construct($container_engine) {
    parent::__construct();

    $this->_response = 60;

    require_once("gw/" . class2file($container_engine['class']));
    $this->_container_gw = new $container_engine['class'](...
      array_values($container_engine['args']));
  }

  function __destruct() {
    $this->_log->Debug("Sleeping for $this->_response seconds");
    parent::__destruct(); // TODO: Change the autogenerated stub
  }

  private function restart_ldap() {
    global $conf;

    $this->_log->Info("Restarting ldap");

    $this->_container_gw->start($this->_container_gw->create(
      $conf['cname'] . '-slapd-test',
      $conf['ldap_1']['image'],
      [
        "/var/data/${conf['cname']}/ldap:/var/lib/ldap",
        "/var/data/${conf['cname']}/ldap-conf:/etc/ldap/slapd.d",
      ],
      $conf['ldap_1']['port'] . ':389',
      'tcp',
      [$conf['ip']],
      [
        'LDAP_ADMIN_PASSWORD' => $conf['ldap_1']['password'],
        'LDAP_CONFIG_PASSWORD' => $conf['ldap_1']['password'],
        'LDAP_READONLY_USER' => 'true',
        'LDAP_READONLY_USER_USERNAME' => 'manager',
        'LDAP_READONLY_USER_PASSWORD' => $conf['ldap_1']['mpassword'],
        'LDAP_BACKEND' => 'hdb',
        'LDAP_TLS' => 'false',
        'LDAP_TLS_VERIFY_CLIENT' => 'false',
        'LDAP_ORGANISATION' => 'Truth',
        'LDAP_DOMAIN' => $conf['cname'],
      ]
    ));
  }

  private function get_services() {
    global $conf;
    return $this->_ldap->list(
      "dc=services,dc=${conf['cname']},dc=truths,dc=world"
    );
  }

  private function get_service_container(array $service) {
    global $conf;
    return $conf['cname'] . '-' . substr(
        $service['cn'],
        strpos($service['cn'], '-') + 1
      );
  }

  private function service_is_running(array $service) {
    return $this->_container_gw->is_running(
      $this->get_service_container($service)
    );
  }

  private function run_service(array $service) {
    global $conf;

    $container_name = $this->get_service_container($service);
    $this->_log->Debug("starting container ${container_name} ...");

    try {
      $this->_container_gw->start($this->_container_gw->create(
        $container_name,
        $service['dockerimage'],
        $service['dockervolume'],
        $service['dockerport'],
        $service['ipserviceprotocol'],
        [$conf['ip']]
      ));
    } catch (Exception $e) {
      $this->_log->Error("[${service['cn']}] service start failed. "
        . $e->getMessage());
    }
  }

  public function run() {
    global $conf;

    if (!$this->_ldap) {
      $this->restart_ldap();
      $this->_response = 10;
      return;
    }

    $services = $this->get_services();

    foreach ($services as $service) {
      $this->_log->Debug("Checking service : " . json_encode($service));

      if (!$this->service_is_running($service)) {
        $this->_log->Debug("Service ${service['cn']} is not running");

        $this->run_service($service);
        $this->_response = 10;
      }
    }
  }
}
