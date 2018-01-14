<?php

class Log {

  private $origin;

  private $stderr;

  private $logfile;

  function __construct($origin) {
    $this->origin = $origin;
    #$this->stderr = fopen('php://stderr', 'a');
    /* TODO: Add rotate */
    #$file = '/var/log/truth/stdout.log';
    #$this->logfile = fopen($file, 'a');
    #if (!$this->logfile) {
    #  throw new Exception("unable to open log file: " . $file);
    #}
  }

  private function _log($level, $message) {
    if ($level === 'Debug') {
      syslog(LOG_DEBUG, $message);
    }
    elseif ($level == 'Error') {
      syslog(LOG_ERR, $message);
    }
    elseif ($level == 'Warning') {
      syslog(LOG_WARNING, $message);
    }
    elseif ($level == 'Info') {
      syslog(LOG_INFO, $message);
    }
    elseif ($level == 'Dump') {
      syslog(LOG_DEBUG, $message);
    }
    else {
      throw new Exception("Not exist level of message");
    }
  }


  public function Debug($message) {
    $this->_log(__FUNCTION__, '['. __FUNCTION__ . " ($this->origin) ]: " . $message);
  }

  public function Error($message) {
    $this->_log(__FUNCTION__, '['. __FUNCTION__ . " ($this->origin) ]: " . $message);
  }

  public function Warning($message) {
    $this->_log(__FUNCTION__, '['. __FUNCTION__ . " ($this->origin) ]: " . $message);
  }

  public function Info($message) {
    $this->_log(__FUNCTION__, '['. __FUNCTION__ . " ($this->origin) ]: " . $message);
  }

  public function Dump($var) {
    $this->Debug('DUMP : ' . var_export($var, TRUE));
  }
}
