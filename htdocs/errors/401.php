<?php
  require_once('html_library.php');
  require_once('include_library.php');
  echo header_common('401 Authorization Required');
?>
<h1>Authorization Required</h1>
<p>This server could not verify that you
are authorized to access the document
requested.  Either you supplied the wrong
credentials (e.g., bad password), or your
browser doesn't understand how to supply
the credentials required.</p>

<?php
  if ($_SESSION['userid']) {
    include_once("$CONFIG[error_basedir]/logout.php");
  }
?>

<!-- <?php echo htmlentities("$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI] $_SERVER[SERVER_PROTOCOL]") ?> -->

<h2>Help us serve you better</h2>
<?php
  if (!preg_match("/$_SERVER[HTTP_HOST]/", $_SERVER['HTTP_REFERER'])) { 
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $scheme = htmlentities($referer['scheme']);
    $host = htmlentities($referer['host']);
    $path = htmlentities($referer['path']);
    $uri = htmlentities($_SERVER['REQUEST_URI']);
    if ($host) {
      echo "
<p>It seems that you followed a link from <a href=\"$scheme://$host\">$host</a>.</p>
<p>Please, tell <a href=\"mailto:webmaster@$host\">their webmaster</a> that
the document <a href=\"$scheme://$host$path\">$referer[path]</a> contains a 
link to a page $uri which requires authorization.</p>
";
    }
  }

  $err_code = 401;
  include_once("$CONFIG[error_basedir]/feedback_form.php");
  echo footer_common();

?>
