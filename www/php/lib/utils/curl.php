<?php

class Curl {

  private $ch;

  function __construct($opt) {
    $this->ch = curl_init();
    foreach ($opt as $key => $value) {
      curl_setopt($this->ch, $key, $value);
    }

    if (!isset($opt[CURLOPT_RETURNTRANSFER])) {
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    }
  }

  function __destruct() {
    curl_close($this->ch);
  }

  /** @return mixed */
  public function exec() {
    $res = curl_exec($this->ch);
    if (!$res) {
      print_r(curl_error($this->ch) . PHP_EOL);
    }

    return $res;
  }

  public function error() {
    return curl_error($this->ch);
  }

  public function errno() {
    return curl_errno($this->ch);
  }
}