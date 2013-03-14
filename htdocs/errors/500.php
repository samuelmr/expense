<?php
  require_once('html_library.php');
  require_once('include_library.php');
  echo header_common('500 Internal Server Error');
  $server_admin = 'webmaster@puoli.net';
?>
<h1>Internal Server Error</h1>
<p>The server encountered an internal error or
misconfiguration and was unable to complete
your request.</p>
<p>Please contact the server administrator,
<?php echo "<a href=\"mailto:$server_admin\">$server_admin</a>" ?>
 and inform us of the time the error occurred,
and anything you might have done that may have
triggered the error.</p>
<!-- <p>More information about this error may be available 
in the server error log.</p> -->

<!-- <?php echo htmlentities("$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI] $_SERVER[SERVER_PROTOCOL]") ?> -->

<h2>Help us serve you better</h2>
<?php
  if (!preg_match("/$_SERVER[HTTP_HOST]/", $_SERVER['HTTP_REFERER'])) { 
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $scheme = htmlentities($referer['scheme']);
    $host = htmlentities($referer['host']);
    $path = htmlentities($referer['path']);
    if ($host) {
      echo "
<p>It seems that you followed a link from <a href=\"$scheme://$host\">$host</a>.</p>
<p>Please, tell <a href=\"mailto:webmaster@$host\">their webmaster</a> that
the document <a href=\"$scheme://$host$path\">$referer[path]</a> contains a broken 
link.</p>
";
    }
  }

  $err_code = 500;
  include_once("$CONFIG[error_basedir]/feedback_form.php");

  echo footer_common();

?>
