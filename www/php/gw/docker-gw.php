<?php

require_once("gw/container-gw.php");
require_once("lib/utils/curl.php");

class DockerGW extends ContainerGW {

  /* @var string */
  protected $_api_ver;

  protected $_socket_path;

  public function __construct(string $api_ver, string $socket_path) {
    $this->_api_ver = $api_ver;
    $this->_socket_path = $socket_path;
  }

  public function list() {
    $result = json_decode((new Curl([
      CURLOPT_URL => "http:/v$this->_api_ver/containers/json?all=true",
      CURLOPT_UNIX_SOCKET_PATH => $this->_socket_path,
    ]))->exec());


    if (!$result) {
      return [];
    }

    $containers = [];
    foreach ($result as $c) {
      if ($c->State == 'running') {
        $containers[] = trim(!empty($c->Names) ? $c->Names[0] : $c->Id, '/');
      }
    }

    return $containers;
  }

  public function create($name, $image, $volume_binds, $port_binds, $proto, $dns, $env = []) {
    $this->stop($name)->remove($name);

    if (!is_array($port_binds)) {
      $port_binds = [$port_binds];
    }

    foreach ($env as $key => $value) {
      $env[$key] = $key . '=' . $value;
    }

    $exposed_ports = array_reduce($port_binds, function ($acc, $bind) use (&$proto) {
      $ports = explode(':', $bind);
      $acc[$ports[1] . "/" . $proto] = new stdClass();
      return $acc;
    }, []);

    $port_bindings = array_reduce($port_binds, function ($acc, $bind) use (&$proto) {
      $ports = explode(':', $bind);
      $acc[$ports[1] . "/" . $proto] = [["HostPort" => $ports[0]]];
      return $acc;
    }, []);

    $body = [
      "Image" => $image,
      "Env" => array_values($env),
      "ExposedPorts" => $exposed_ports,
      "HostConfig" => [
        "Binds" => $volume_binds,
        "PortBindings" => $port_bindings,
        "Dns" => $dns,
      ],
    ];

    $res = json_decode((new Curl([
      CURLOPT_URL => "http:/v$this->_api_ver/containers/create?name=$name",
      CURLOPT_UNIX_SOCKET_PATH => $this->_socket_path,
      CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => json_encode($body),
    ]))->exec());

    if (!$res) {
      throw new Exception("Failed to create container. Unknown error.");
    }

    if (!isset($res->Id)) {
      throw new Exception("Failed to create container, reason : \"$res->message\"");
    }

    return $res->Id;
  }

  public function start($id) {
    (new Curl([
      CURLOPT_URL => "http:/v$this->_api_ver/containers/$id/start",
      CURLOPT_UNIX_SOCKET_PATH => $this->_socket_path,
      CURLOPT_CUSTOMREQUEST => "POST",
    ])
    )->exec();
    return $this;
  }

  public function stop($id) {
    (new Curl([
      CURLOPT_URL => "http:/v$this->_api_ver/containers/$id/stop",
      CURLOPT_UNIX_SOCKET_PATH => $this->_socket_path,
      CURLOPT_CUSTOMREQUEST => "POST",
    ]))->exec();
    return $this;
  }

  public function remove($id) {
    (new Curl([
      CURLOPT_URL => "http:/v$this->_api_ver/containers/$id",
      CURLOPT_UNIX_SOCKET_PATH => $this->_socket_path,
      CURLOPT_CUSTOMREQUEST => "DELETE",
    ]))->exec();
    return $this;
  }

  public function is_running(string $container) {
    static $containers = NULL;
    if (is_null($containers)) {
      $containers = $this->list();
    }
    return in_array($container, $containers);
  }
}