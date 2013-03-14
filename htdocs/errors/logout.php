<?php
  
  include_once('include_library.php');

  $message = $_REQUEST['message'] ? $_REQUEST['message'] : $message;
  $message = $message ? $message : 'You have now logged out';
  $back = $_REQUEST['back'] ? $_REQUEST['back'] : $back;
  $back = $back ? $back : 'Try to go back to the previous page and refresh.';

  if (preg_match('/url=([^\&\;\=]+)/', $_SERVER['REQUEST_URI'], $match)) {
    $url = $match[1];
  }
  if (!$_SERVER['REMOTE_USER']) {
    echo "<h2>".htmlentities($message)."</h2>\n";
  }
  if (($url) && !preg_match('/logout/', $url)) {
    echo "<p>You may try to access the resource at <a href=\"".
         htmlentities($url)."\">".htmlentities($url)."</a>".
         " using different username and password.</p>";
  }
  else {
    echo "<p>".back(NULL, $back)."</p>\n";
  }
?>
