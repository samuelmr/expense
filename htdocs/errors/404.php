<?php
  require_once('html_library.php');
  require_once('include_library.php');
  echo header_common('404 Not Found');
?>
<h1>Not Found</h1>
<p>The requested URL
 <?php echo htmlentities($_SERVER['REQUEST_URI']) ?>
 was not found on this server.</p>

<h2>How to find what you were looking for</h2>
<p>The following steps may help you find the document you were searching:</p>
<ol>
<li><strong>Check the address in the location bar</strong><br />
The address might be misspelled.  <strong><em>Note:</em></strong> all documents should have either the extension <tt><strong>.html</strong></tt> or <tt><strong>.php</strong></tt> -- there are no <tt>.htm</tt> or <tt>.php3</tt> pages here.</li>
<!--
<li><strong>Use our text search</strong><br /></li>
-->
</ol>

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

  $err_code = 404;
  include_once("$CONFIG[error_basedir]/feedback_form.php");

  echo footer_common();

?>
