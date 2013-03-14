<?php

  session_start();
  require_once('database.php');
  require_once('html_library.php');
  require_once('view.php');
  require_once('include_library.php');
  $user_table = 'user_auth';
  $config_table = 'expense2_config';

  require_once('config.php');
  $QUERY = getQuery($_REQUEST);
  $CONFIG = getConfig($_SESSION, $QUERY);
  $LOCALE = getLocale($_SESSION, $QUERY, $CONFIG);
  $lang = $_SESSION['lang'];

  # ini_set('error_reporting', 1024);
  ini_set('display_startup_errors', 'off');
  ini_set('display_errors', 'off');
  ini_set('html_errors', 'off');
  ini_set('log_errors', 'on');
  ini_set('error_log', '/www/seuranta/logs/kulutus.seuranta.org-error.log');
  set_error_handler('my_error_handler');
  # error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
  error_reporting(E_ALL);

  $headers = <<<EOS
  <link rel="stylesheet" type="text/css" href="/errors.css" />
  <link rel="stylesheet" title="Login form style" type="text/css" href="/login.css" />

EOS
  ;
  $title = $LOCALE['expense'];

  $ok = 0;
  $u = (isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '');
  $p = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '');

  if ($u && $p) {
    $select = "SELECT id FROM $user_table WHERE username = '".
              db_escape_string($u)."'";
    $stmt = db_query($select);
    $rows = db_num_rows($stmt);
    db_free_result($stmt);
    if ($rows > 0) {
      $message = $LOCALE['user_exists'];
    }
    else {
      $select = "SHOW TABLE STATUS LIKE '$user_table'";
      $res = db_query($select);
      $row = db_fetch_assoc($res);
      $i = $row['Auto_increment'];
      db_free_result($res);
  
      $values = array('user_id' => $i,
                      'username' => $u,
                      'password' => sha1($p),
                      'valid_from' => date('Y-m-d H:i:s'),
                      'valid' => 'y',
                      'level' => 20);
      if (db_insert($user_table, $values)) {
        ++$ok;
      }
      else {
        echo header_plain($title, $headers, 'strict', $lang);
        echo "  <h1>".htmlentities($title)."</h1>\n";
        trigger_error($LOCALE['insert_failed'], E_USER_ERROR);
        printErrors($GLOBALS['ERRORS']);
        echo footer_plain('UA-4404005-8');
        exit();
      }
  
      $create_sql = <<<EOS
CREATE TABLE `expense`.`expense2_user$i` (
`id` bigint( 2 ) NOT NULL AUTO_INCREMENT ,
`date` date NOT NULL default '0000-00-00',
`cost` float NOT NULL default '0',
`type` varchar( 4 ) NOT NULL default '',
`prod` varchar( 255 ) NOT NULL default '',
`other` float default NULL ,
`currency` char( 3 ) default NULL ,
PRIMARY KEY ( `id` ) ,
KEY `type` ( `type` ) ,
KEY `date` ( `date` ) ,
FULLTEXT KEY `prod` ( `prod` )
) ENGINE = MYISAM DEFAULT CHARSET = latin1;

EOS;
  
      if (db_query($create_sql)) {
        ++$ok;
      }
      else {
        echo header_plain($title, $headers, 'strict', $lang);
        echo "  <h1>".htmlentities($title)."</h1>\n";
        trigger_error($LOCALE['insert_failed'], E_USER_ERROR);
        printErrors($GLOBALS['ERRORS']);
        echo footer_plain('UA-4404005-8');
        exit();
      }
  
      $config = array('user_id' => $i,
                      'title' => 'Kulutusseuranta',
                      'lang' => $lang,
                      'product_table' => "expense2_user$i");
      if (db_insert($config_table, $config)) {
        ++$ok;
      }
      else {
        echo header_plain($title, $headers, 'strict', $lang);
        echo "  <h1>".htmlentities($title)."</h1>\n";
        trigger_error($LOCALE['insert_failed'], E_USER_ERROR);
        printErrors($GLOBALS['ERRORS']);
        echo footer_plain('UA-4404005-8');
        exit();
      }
  
      if ($ok == 3) {
        $_SESSION['userid'] = $i;
      }
    }
  }

  $goto = isset($goto) ? $goto : 'http://'.$_SERVER['HTTP_HOST']."/";
  if (isset($message)) {
    $goto .= "?message=".urlencode($message);
  }
  redirect($goto);

?>
