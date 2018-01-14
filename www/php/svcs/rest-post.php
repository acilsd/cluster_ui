<?php

require_once('svcs/micro.php');

class RestPost extends MicroService {

  function __construct() {
    parent::__construct();
    $this->set_origin(get_class());
    $this->autoresponse(TRUE);
  }

  function run() {
    $this->auth();
    $res = $this->forward('object-post', 'post');

    if (is_array($res)) {
      // each forward will override previous response but keep unchanged fields
      $this->_response = $res + $this->_response;
    } else {
      // raw response
      $this->_raw_response = TRUE;
      $this->_response = $res;
    }
  }
}