<?php
  require_once('html_library.php');
  require_once('include_library.php');
  echo header_common('400 Bad Request');
?>
<h1>Bad Request</h1>
<p>Your browser sent a request that this server could not understand.</p>
<p>Request header field is missing colon separator.</p>

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

  $err_code = 400;
  include_once("$CONFIG[error_basedir]/feedback_form.php");

  echo footer_common();
?>
