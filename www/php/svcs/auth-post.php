<?php

require_once('svcs/micro.php');

class AuthPost extends MicroService {

  function __construct() {
    global $conf;

    parent::__construct();

    $this->autoresponse(TRUE);
    $this->set_origin(get_class());
  }

  function run() {
    $data = $this->get_post_data();
    $this->post($data['data']);
  }

  /* find or generate a token */
  protected function token($login) {
    global $conf;

    /* add tokens directory to the login directory if not yet */
    $tokens = $this->_ldap->add_cname('dc=tokens,dc=' . $login . ',dc=users');
    if (!$this->_ldap->read($tokens)) {
      if (!$this->_ldap->add($tokens,
        [
          'objectclass' => ['trueUnit'],
          'ou' => 'tokens',
          'dc' => 'tokens',
          'associatedDomain' => 'tokens.' . $login . '.users.' . $conf['cname']
            . '.truths.world',
          'aRecord' => $conf['ip'],
        ])
      ) {
        $this->fatal('failed to add tokens unit for user ' . $login);
      }
    }

    /* find existing valid token */
    if ($list = $this->_ldap->list($tokens)) {
      foreach ($list as $item => $value) {
        if ($value['status'] === 'valid') {
          $this->_log->Debug("found valid token");
          return $value['token'];
        }
      }
    }

    /* add a new token */
    $token = uniqid('', TRUE);
    $hash = substr($token, 0, 5);
    $token_dn = 'dc=' . $hash . ',' . $tokens;
    if (!$this->_ldap->add($token_dn,
      [
        'objectclass' => ['trueToken'],
        'token' => $token,
        'status' => 'valid',
        'dc' => $hash,
        'associatedDomain' => $hash . '.tokens.' . $login . '.users.'
          . $conf['cname'] . '.truths.world',
        'aRecord' => $conf['ip'],

      ])
    ) {
      $this->fatal('failed to create a new token for user ' . $login);
    }
    return $token;
  }

  function post($data) {
    $login = $data['login'];
    $passwd = $data['passwd'];

    if ($login == "" || $passwd == "") {
      $this->fatal('both login and passwd fields expected', 571);
    }

    if ($this->_ldap->auth($login, $passwd)) {
      $this->_response['auth']['token'] = $this->token($login);
      $this->set_okay();
    }
    else {
      $this->fail('authentication failed for ' . $login, 402);
    }
  }
}