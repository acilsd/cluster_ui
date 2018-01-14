<?php

require_once("gw/agate.php");

abstract class ContainerGW extends AGate {

  /**
   * @param string $name
   * @param string $image
   * @param string[] $volume_binds
   * @param string[] $port_binds
   * @param string $proto
   * @param string[] $dns
   * @param string[] $env
   *
   * @return int
   */
  public abstract function create($name, $image, $volume_binds, $port_binds, $proto, $dns, $env = []);

  /**
   * @param int $id
   *
   * @return static
   * */
  public abstract function remove($id);

  /**
   * @param int $id
   *
   * @return static
   */
  public abstract function start($id);

  /**
   * @param int $id
   *
   * @return static
   * */
  public abstract function stop($id);

  /**
   * @return string[]
   */
  public abstract function list();

  /**
   * @param string $container
   *
   * @return bool
   */
  public abstract function is_running(string $container);
}