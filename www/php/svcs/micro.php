<?php
require_once("lib/utils/uri.php");
require_once("lib/utils/log.php");
require_once("gw/ldap.php");
require_once("gw/micro.php");

abstract class MicroService {

  protected $_log;

  protected $_response;

  /**
   * @var \LDAPGate|bool
   */
  protected $_ldap;

  protected $_raw_response;

  protected $_autoresponse;

  protected $_auth_token;

  function __construct() {
    global $conf;

    if (php_sapi_name() !== "cli") {
      $this->set_origin($conf['class']);

      $this->_raw_response = FALSE;
      $this->_autoresponse = FALSE;
      $this->_response['status'] = 'unknown';
      $this->_response['id'] = microtime();
      $this->_response['code'] = 299;
      $this->_response['message'] = [];

      $this->_uri = new Uri();
    }
    else {
      $this->_raw_response = TRUE;
      $this->_autoresponse = TRUE;
    }

    $this->_log = new Log($conf['class']);

    try {
      $this->_ldap = new LDAPGate(
        $conf['ldap_1']['ip'],
        $conf['ldap_1']['port'],
        $conf['ldap_1']['login'],
        $conf['ldap_1']['password']
      );
    } catch (Exception $e) {
      $this->_ldap = FALSE;
      $this->_log->Error($e->getMessage());
    }

  }

  function __destruct() {
    if ($this->_autoresponse) {
      $this->_raw_response
        ? $this->send_raw()
        : $this->send_response();
    }
  }

  protected function auth() {
    $auth_str = $_SERVER['HTTP_TRUTH_AUTH'];

    if (!$auth_str) {
      $this->fatal('no auth header', 403);
    }

    if (!$dec = base64_decode($auth_str)) {
      $this->fatal('failed to decode auth header', 404);
    }

    if (!$auth = json_decode($dec, TRUE)) {
      $this->fatal('bad json in auth header', 405);
    }

    if (isset($auth['token'])) {
      $auth_get_gw = new MicroGate();
      $auth_get_gw->set_service('auth-get');
      if (!$res = $auth_get_gw->get(':' . $auth['token'])) {
        $this->fatal('failed to query auth get gw');
      }

      if ($res['status'] !== 'okay') {
        $this->fatal('invalid token');
      }

      $this->_auth_token = $auth['token'];
      return TRUE;
    }
    else {
      if (isset($auth['login']) && isset($auth['passwd'])) {
        $auth_post_gw = new MicroGate();
        $auth_post_gw->set_service('auth-post');
        if (!$res = $auth_post_gw->post('',
          ['data' => ["login" => $auth['login'], "passwd" => $auth['passwd']]])
        ) {
          $this->fail('failed to query auth post gw');
        }

        if ($res['status'] !== 'okay') {
          $this->fail('login fail', 407);
        }

        $this->_auth_token = $res['auth']['token'];
        $this->_response['auth']['token'] = $res['auth']['token'];
        return TRUE;
      }
    }

    $this->fail('either token or login and passwd required', 406);
  }

  protected function get_post_data() {
    /* POST data is a json in case of modify or add,
     * or empty in case of delete. */
    $input = file_get_contents("php://input");
    $this->_log->Debug('POST data size: ' . sizeof($input));

    if ($input) {
      if ($data = json_decode($input, TRUE)) {
        if (isset($data['data'])) {
          return $data;
        }
        else {
          $this->fatal('bad request - no data field');
        }
      }
      else {
        $this->fatal('JSON decode fail');
      }
    }
    $this->fatal('bad POST body');
  }

  protected function autoresponse($value) {
    $this->_autoresponse = $value;
  }

  protected function set_origin($origin) {
    if (!$this->_raw_response) {
      $this->_response['origin'] = $origin;
    }
  }

  protected function set_status($status) {
    if (!$this->_raw_response) {
      $this->_response['status'] = $status;
    }
  }

  protected function set_message($message) {
    if (!$this->_raw_response) {
      $this->_response['message'][] = $message;
    }
  }

  protected function set_code($code) {
    if (!$this->_raw_response) {
      $this->_response['code'] = $code;
    }
    if ($code > 300) {
      $this->set_status('fail');
    }
  }

  protected function set_okay($message = FALSE) {
    /* all set_ fns already check for raw message */
    if ($this->code() < 200 || $this->code() > 298) {
      $this->set_code(200);
    }
    $this->set_status('okay');
    if ($message) {
      $this->set_message($message);
    }
  }

  protected function set_fail($message = FALSE) {
    $this->_response['status'] = 'fail';
    $this->_response['code'] = 301;
    if ($message) {
      $this->set_message($message);
    }
  }

  protected function send_response($message = FALSE) {
    if ($message) {
      $this->set_message($message);
    }

    if (
      is_array($this->_response['message']) &&
      count($this->_response['message']) == 1
    ) {
      $this->_response['message'] = $this->_response['message'][0];
    }

    echo $res = json_encode($this->_response, JSON_PRETTY_PRINT);
  }

  protected function send_raw() {
    echo $this->_response;
  }

  protected function fail($message = FALSE, $code = 400) {
    if ($this->code() < 400) {
      $this->set_code($code);
    }
    $this->set_fail();
    if ($message) {
      $this->set_message('Fail: ' . $message);
    }
  }

  public function code() {
    return $this->_response['code'];
  }

  protected function fatal($message = FALSE, $code = 501) {
    if ($this->code() < $code) {
      $this->set_code($code);
    }
    $this->set_fail();
    $this->send_response(' Fail: ' . $message);
    $this->_autoresponse = FALSE;
    die();
  }

  protected function not_imp($message = FALSE) {
    if ($this->code() < 501) {
      $this->set_code(601);
    }
    $this->set_fail();
    $this->send_response(' Not implemented: ' . $message);
    $this->_autoresponse = FALSE;
    die();
  }

  protected function never_imp($message = FALSE) {
    if ($this->code() < 601) {
      $this->set_code(701);
    }
    $this->set_fail();
    $this->send_response(' For future version: ' . $message);
    $this->_autoresponse = FALSE;
    die();
  }

  /**
   * @param string $service
   * @param string $method
   *
   * @return mixed
   */
  function forward($service, $method) {
    $gw = new MicroGate($service);
    if (php_sapi_name() === "cli") {
      // TODO: cli forwarding
      $this->fatal('Cli forwarding not implemented');
    }

    $params = [
      'header' => [
        'truth-auth' => base64_encode(json_encode(['token' => $this->_auth_token])),
      ],
    ];

    if ($method === 'post') {
      $params['data'] = $this->get_post_data()['data'];
    }

    return $gw->$method($this->_uri->url, $params);
  }
}
