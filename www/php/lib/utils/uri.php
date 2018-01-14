<?php

class Uri {

  public $scope;

  public $filter;

  function __construct() {

    $this->log = new Log(get_class());
    $this->url = rtrim((isset($_SERVER['HTTPS']) ? "https" : "http")
      . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '/') . '/';

    $this->cur_object = parse_url($this->url, PHP_URL_HOST);
    $this->url_path = explode('/', parse_url($this->url, PHP_URL_PATH));
    $this->url_args = [];
    $this->filters = [];
    $this->fields = FALSE;
    $this->scope = ($_SERVER['REQUEST_METHOD'] == 'GET')
      ? $this->scope = 'one'
      : $this->scope = 'add';

    /* parse url path for scope and filters */
    foreach ($this->url_path as $segment) {
      if ($segment == "") {
        continue;
      } elseif ($segment[0] == ':') {
        if (strstr($segment, '=') === FALSE) {
          $this->fields[] = strtolower(substr($segment, 1));
        } else {
          $filt_arg = explode('=', $segment);
          $this->filters[substr($filt_arg[0], 1)] = explode(',', base64_decode($filt_arg[1]));
        }
      } else {
        /* a scope */
        $this->scope = $segment;
      }
    }

    $this->log->Debug(
      'request type ' . $_SERVER['REQUEST_METHOD'] .
      ', cur_object: ' . $this->cur_object . ', scope: ' . $this->scope .
      ', filters: ' . var_export($this->filters, true));

  }

  public function get_filter_arguments($filter) {
    if (isset ($this->filters[$filter])) {
      return $this->filters[$filter];
    } else 
      return false;
  }
}
