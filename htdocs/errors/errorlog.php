<?php

  require_once('database.php');
  require_once('db_library.php');
  require_once('include_library.php');

  session_start();

  $remote_user = $_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] :
                 $_SESSION['userid'];
  $date = db_date();

  echo header_common('Thank you for your feedback');
  if ($_REQUEST['request_uri']) {
    $insert = "INSERT INTO http_error_log (
               date, http_request, request_uri, query_string,
               http_post_vars, http_cookie_vars, 
               remote_user, remote_addr, remote_host,
               user_agent, user_comment, user_email, error_code
               ) values (
               $date,".
               "'".mysql_real_escape_string($_REQUEST['http_request'])."',".
               "'".mysql_real_escape_string($_REQUEST['request_uri'])."',".
               "'".mysql_real_escape_string($_REQUEST['query_string'])."',".
               "'".mysql_real_escape_string($_REQUEST['http_post_vars'])."',".
               "'".mysql_real_escape_string($_REQUEST['http_cookie_vars'])."',".
               "'".mysql_real_escape_string($remote_user)."', ".
               "'".mysql_real_escape_string($_REQUEST['remote_addr'])."',".
               "'".mysql_real_escape_string($_REQUEST['remote_host'])."',".
               "'".mysql_real_escape_string($_REQUEST['user_agent'])."',". 
               "'".mysql_real_escape_string($_REQUEST['user_comment'])."',".
               "'".mysql_real_escape_string($_REQUEST['user_email'])."',".
               "'".mysql_real_escape_string($_REQUEST['error_code'])."'".
               ")";
    $stmt = mysql_query($insert);
    echo "<!-- $insert: $stmt -->\n";
    if ($stmt) {
?>
<h1>Thank You!</h1>
<p>Your feedback is being processed.</p>
<?php
    }
    else {
      echo "<pre>\n";
      echo "$insert\n";
      echo mysql_error()."\n";  
      echo "</pre>\n";
    }
  }
  else {
  }

  $webmaster = 'webmaster@puoli.net';
  $subject = "Error $_REQUEST[error_code] at $_REQUEST[request_uri]";
  $message = "
DATE AND TIME:	  $date,
HTTP_REQUEST:     '$_REQUEST[http_request]',
REQUEST_URI:      '$_REQUEST[request_uri]',
QUERY_STRING:     '$_REQUEST[query_string]',
HTTP_POST_VARS:   '$_REQUEST[http_post_vars]',
HTTP_COOKIE_VARS: '$_REQUEST[http_cookie_vars]', 
REMOTE_USER:      '$remote_user',
REMOTE_ADDR:      '$_REQUEST[remote_addr]',
REMOTE_HOST:      '$_REQUEST[remote_host]',
USER_AGENT:       '$_REQUEST[user_agent]',
USER_COMMENT:
$_REQUEST[user_comment]
";
  if ($_REQUEST[user_email]) {
    $headers = "From: $_REQUEST[user_email]\r\n";
?>
<p>Our <a href="mailto:webmaster@puoli.net">Webmaster</a> will contact you
if further information is required.</p>
<?php
  }
  mail($webmaster, $subject, $message, $headers); 
  echo footer_common();

?>
