<?php

require_once('svcs/micro.php');

class RestGet extends MicroService {
  function __construct() {
    parent::__construct();
    $this->set_origin(get_class());
    $this->autoresponse(TRUE);
  }

  function run() {
    $this->set_okay();
    $this->auth();

    $res = $this->forward('object-get', 'get');

    if (is_array($res)) {
      if ($res['status'] == 'okay') {
        // each forward will override previous response but keep unchanged fields
        $this->_response = $res + $this->_response;
      } else {
        $this->set_message($res['message']);
        $this->fail('call to object service failed');
      }
    } else {
      // raw response
      $this->_raw_response = TRUE;
      $this->_response = $res;
    }
  }
}
