<?php

require_once("/var/www/html/lib/utils/utils.php");

$conf_dir = '/conf';
$svcs = '/var/www/html/svcs/';

foreach (scandir($conf_dir) as $file) {
  if (!is_dir($file)) {
    require_once("$conf_dir/$file");
  }
}

require_once("$svcs/" . class2file($conf['class']));

(new $conf['class'](...array_values(isset($conf['args']) ? $conf['args'] : [])))->run();