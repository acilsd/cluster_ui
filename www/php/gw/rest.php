<?php

require_once('gw/agate.php');

class RESTGate extends AGate {

  protected $_data_string;

  function __construct() {
    parent::__construct();
    $this->_log = new Log(get_class());
    $this->_data_string = FALSE;
  }

  public function get($url, $params = []) {
    /*$context = stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' => isset($params['header']) ? $params['header'] : []
      ]
    ]);*/

    $fh = fopen($url, "r");
    if (!$fh) {
      $this->_log->Error("connect to " . $url . " failed");
      return FALSE;
    }

    $content = stream_get_contents($fh);
    fclose($fh);

    if (!$content) {
      $this->_log->Error("request to " . $url . " failed");
      return FALSE;
    }

    if ($data = json_decode($content, TRUE)) {
      return $data;
    }

    // raw content
    return $content;
  }

  public function post($url, $params) {
    $new_data = [];
    $new_data['data'] = isset($params['data']) ? $params['data'] : [];
    $this->_data_string = json_encode($new_data);

    $this->_log->Debug('starting post query on ' . $url);
    $ch = curl_init($url);
    if (FALSE === $ch) {
      $this->fatal('curl initialization failed');
    }

    $header = [
      'Content-Type: application/json',
      'Content-Length: ' . strlen($this->_data_string),
      'Expect: 100-continue',
      'Connection: Keep-Alive',
    ];

    if (isset($params['header'])) {
      foreach ($params['header'] as $key => $value) {
        $header[] = "$key: $value";
      }
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_data_string);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $output = curl_exec($ch);

    /* curl failed */
    if (FALSE === $output) {
      $this->_log->Error('curl error: ' . curl_error($ch) . ' no: '
        . curl_errno($ch));
      return FALSE;
    }

    /* response is bad json */
    if (!($data = json_decode($output, TRUE))) {
      $this->_log->Error("json decode from POST to " . $url
        . " failed. contents:");
      $this->_log->DUMP($output);
      return FALSE;
    }

    return $data;
  }

  public function get_data_string() {
    return $this->_data_string;
  }

}

