<?php

  require_once('db_config.php');
  require_once('db_library_mysqli.php');

  if (!isset($conn) || !$conn) {
    $conn = db_connect($CONFIG['db_hostname'],
                       $CONFIG['db_login'],
                       $CONFIG['db_passwd'],
                       $CONFIG['db_database']);
  }

?>
