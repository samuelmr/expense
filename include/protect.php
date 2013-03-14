<?php

  session_start();
  require_once('database.php');
  require_once('include_library.php');
  # require_once('debug.php');
  # error_reporting(E_ALL);
  set_error_handler('login_error_handler');

  $loginform = "$CONFIG[html_basedir]/loginform.php";

  define('DB_DATA_NOT_FOUND', 'User information not found!');

  $LOGIN['urlvars'] = array('login_username', 'login_password', '__utm\w');
  $LOGIN['regex'] = '/^('.join('|', $LOGIN['urlvars']).')$/';

  $goto = get_goto();

  if (isset($_REQUEST['login_username']) &&
      isset($_REQUEST['login_password'])) {
    $username = trim($_REQUEST['login_username']);
    $password = trim($_REQUEST['login_password']);
    if ($username && $password) {
      login($username, $password);
    }
  }

  if (!isset($_SESSION['userid'])) {
    header("X-Account-Management-Status: none");
    include_once($loginform);
    exit();
  }

  $ams_vars = "id=\"$_SESSION[userid]\""; // should be username
  if (isset($_SESSION['username'])) {
   $ams_vars .= "; name=\"$_SESSION[username]\""; // should be real name
  }
  header("X-Account-Management-Status: active; $ams_vars");

  function login($username, $password) {

    global $ERRORS;
    global $goto;
    global $loginform;
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : NULL;
    $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : NULL;

    $select = "
      SELECT   user_id, username, level
      FROM     user_auth
      WHERE    (valid_from IS NULL OR valid_from <= NOW())
      AND      (valid_to IS NULL OR valid_to >= NOW())";
    if (isset($CONFIG['required_user_id'])) {
      $select .= "
        AND    user_id = '$CONFIG[required_user_id]'";
    }
    if (isset($CONFIG['required_accesslevel'])) {
      $select .= "
        AND    level >= '$CONFIG[required_accesslevel]'";
    }
    $select .= "
      AND    username = '".db_escape_string($username)."'
      AND    password = SHA1('".db_escape_string($password)."')";
    $stmt = db_query($select);
    if (!$stmt) {
      trigger_error(db_error()."\n", E_USER_ERROR);
    }
    $row = db_fetch_assoc($stmt);
    if ($row['user_id']) {
      logout();
      $_SESSION = array();
      $_SESSION['userid'] = $row['user_id'];
      $_SESSION['username'] = $username;
      $_SESSION['accesslevel'] = $row['level'];
      $_SESSION['PHP_AUTH_USER'] = $row['username'];
      db_free_result($stmt);
      session_write_close();
      session_start();
      redirect($goto);
      exit();
    }
    else {
      if ($username || $password) {
        trigger_error(DB_DATA_NOT_FOUND, E_USER_ERROR);
      }
      include_once($loginform);
      # echo "<!--\n$select\n-->\n";
      db_free_result($stmt);
      exit();
    }
  }

  function logout() {
    session_destroy();
    session_start();
    session_regenerate_id();
  }

  function get_goto() {
    global $LOGIN;
    $goto_vars = array();
    while(list($key, $value) = each($_REQUEST)) {
      if ((!preg_match($LOGIN['regex'], $key)) &&
          ($key != session_name())) {
        if (is_array($value)) {
          for ($i=0; $i<count($value); $i++) {
            $goto_vars[] = urlencode($key)."=".urlencode($value[$i]);
          }
        }
        else {
          $goto_vars[] = urlencode($key)."=".urlencode($value);
        }
      }
    }
    $goto = "http".(isset($_SERVER['HTTPS']) ? 's' : '')."://".
            $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
            (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '').
            (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '').
            ((count($goto_vars) > 0) ? "?" : "").
            join('&', $goto_vars);
    return $goto;
  }

  function login_error_handler($errno, $errmsg, $file, $line, $vars) {
    if ($errno >= E_USER_ERROR) {
      global $ERRORS;
      if (!is_array($ERRORS)) {
        $ERRORS = array();
      }
      switch($errno) {
        case E_USER_ERROR:
          $class = 'error';
          break;
        case E_USER_WARNING:
          $class = 'warning';
          break;
        default:
          $class = 'notice';
          break;
      }
      $ERRORS[] = array($errmsg, $class, $file, $line);
      return TRUE;
    }
    return FALSE;
  }

?>
