<?php

function foreach2($arg1, $lambda) {
  if (is_array($arg1)) {
    foreach ($arg1 as $key) {
      $lambda($key);
    }
  }
  else {
    $lambda($arg1);
  }
}

function class2file($class) {
  $f = TRUE;
  $res = '';
  foreach (str_split($class) as $c) {
    if (ctype_upper($c) && !$f) {
      $res .= '-';
      $f = TRUE;
    }
    else {
      $f = FALSE;
    }
    $res .= lcfirst($c);
  }

  return $res . '.php';
}

function fatal($message) {
  throw new Exception ('fatal error: ' . $message);
}
