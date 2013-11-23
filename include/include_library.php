<?php

  $CONFIG['html_basedir'] = '/www/seuranta/kulutus/htdocs';
  $CONFIG['php_basedir'] = '/www/seuranta/kulutus/include';
  $CONFIG['error_basedir'] = '/www/seuranta/kulutus/htdocs/errors';

  $INCLUDE = is_array($INCLUDE) ? $INCLUDE : array();
  $INCLUDE['xhtml_ct'] = 'application/xhtml+xml';
  $INCLUDE['html_ct'] = 'text/html';
  $INCLUDE['dtd_transitional'] = 
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'.
    ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  $INCLUDE['dtd_basic'] = 
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"'.
    ' "http://www.w3.org/TR/xhtml1/DTD/xhtml-basic10.dtd">';
  $INCLUDE['dtd_strict'] = 
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'.
    ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  $INCLUDE['dtd_11'] = 
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"'.
    ' "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
  $INCLUDE['dtd_x11_math_svg'] = 
    '<!DOCTYPE html'.
    ' PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"'.
    ' "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">';
  $INCLUDE['dtd_mob11'] = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML'.
    ' Mobile 1.1//EN" "http://www.wapforum.org/DTD/xhtml-mobile11.dtd">';
  $INCLUDE['dtd_mob12'] = '<!DOCTYPE html PUBLIC "-//OMA//DTD XHTML Mobile 1.'.
    '2 //EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">';
  $INCLUDE['dtd_html5'] = '<!DOCTYPE html>';


  function script_addr($max_length=72) {

    $http_host = $_SERVER['HTTP_HOST'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];

    if (strlen($http_host)+strlen($request_uri)>$max_length) {
      $request_uri = $script_name;  
    }

    return($http_host.$request_uri);
  }

  function content_negotiate($accept=NULL) {
    global $INCLUDE;
    $accept = isset($accept) ? $accept : $_SERVER['HTTP_ACCEPT'];
    $types = explode(',', $accept);
    $tmp = array();
    $count = 0;
    foreach ($types as $type) {
      $count += 0.00001;
      if (preg_match('/;\s?q=([\d\.]+)/', $type, $match)) {
	$q = $match[1];
	$type = preg_replace('/;(.*)$/', '', $type);
	$tmp[$type] = $q;
      }
      else {
	$tmp[$type] = 1 - $count;
      }
    }

    if (isset($tmp[$INCLUDE['xhtml_ct']]) &&
        isset($tmp[$INCLUDE['html_ct']]) &&
	($tmp[$INCLUDE['xhtml_ct']] >= $tmp[$INCLUDE['html_ct']])) {
      $ctype = $INCLUDE['xhtml_ct'];
    }
    else {
      $ctype = $INCLUDE['html_ct'];
    }
    return $ctype;
  }

  function doctype($doctype=NULL) {
    global $INCLUDE;
    if ($doctype == 'transitional') {
      $dtd = $INCLUDE['dtd_transitional'];
    }
    elseif ($doctype == 'basic') {
      $dtd = $INCLUDE['dtd_basic'];
    }
    elseif ($doctype == 'strict') {
      $dtd = $INCLUDE['dtd_strict'];
    }
    elseif ($doctype == 'svg') {
      $dtd = $INCLUDE['dtd_x11_math_svg'];
    }
    elseif ($doctype == 'mobi') {
      $dtd = $INCLUDE['dtd_mob11'];
    }
    elseif ($doctype == 'html5') {
      $dtd = $INCLUDE['dtd_html5'];
    }
    else {
      $dtd = $INCLUDE['dtd_11'];
    }
    return $dtd;
  }

  function xml_pi($version, $charset='UTF-8') {
    return "<?xml version=\"1.0\" encoding=\"$charset\"?>";
  }

  function xml_css($url) {
   return "<?xml-stylesheet type=\"text/css\" href=\"$url\"?>";
  }

  function header_plain($title, $headers='', $doctype='',
                        $lang='fi', $charset='iso-8859-1',
                        $bodyattrs=NULL, $headattrs=NULL, $id='seuranta.org') {
    global $INCLUDE;
    global $header_printed;
    if ($header_printed) {
      return;
    }
    if ($bodyattrs) {
      $bodyattrs = ' '.$bodyattrs;
    }
    if ($headattrs) {
      $headattrs = ' '.$headattrs;
    }
    $ctype = content_negotiate();
    $xdl = '';
    if ($ctype == $INCLUDE['xhtml_ct']) {
      # $xdl = xml_pi('1.0', $charset)."\n";
    }
    else {
      $doctype = $doctype ? $doctype : 'strict';
    }
    $dtd = doctype($doctype);
    $title = htmlentities($title);
    header("Content-Type: $ctype; charset=$charset");
    $header_printed = true;
    return <<<HEADMARKER
$xdl$dtd
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$lang">
 <head$headattrs>
  <meta http-equiv="Content-Type" content="$ctype; charset=$charset" />
  <title>$title</title>
$headers </head>
 <body id="$id"$bodyattrs>

HEADMARKER
;
  }

  function footer_plain($ga=NULL) {
    global $footer_printed;
    if ($footer_printed) {
      return;
    }
    $footer_printed = true;
    $gascript = '';

    if ($ga) {
      $gascript = <<<EOS
  <script type="text/javascript">
   var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
   document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
   try {
    var pageTracker = _gat._getTracker("$ga");
    pageTracker._trackPageview();
   } catch(err) {}</script>

EOS;
    }
    return <<<FOOTMARKER
$gascript </body>
</html>

FOOTMARKER
;
  }

  function header_html5($title, $headers='', $doctype='<!DOCTYPE html>',
                        $lang='fi', $charset='iso-8859-1',
                        $bodyattrs=NULL, $headattrs=NULL, $id='seuranta.org') {
    global $INCLUDE;
    global $header_printed;
    if ($header_printed) {
      return;
    }
    if ($bodyattrs) {
      $bodyattrs = ' '.$bodyattrs;
    }
    if ($headattrs) {
      $headattrs = ' '.$headattrs;
    }
    $ctype = content_negotiate();
    $xdl = '';
    if ($ctype == $INCLUDE['xhtml_ct']) {
      # $xdl = xml_pi('1.0', $charset)."\n";
    }
    else {
      $doctype = $doctype ? $doctype : 'html5';
    }
    $dtd = doctype($doctype);
    $title = htmlentities($title);
    header("Content-Type: $ctype; charset=$charset");
    $header_printed = true;
    return <<<HEADMARKER
$xdl$dtd
<html xmlns="http://www.w3.org/1999/xhtml" lang="$lang">
 <head$headattrs>
  <meta charset="$charset" />
  <title>$title</title>
$headers </head>
 <body id="$id"$bodyattrs>

HEADMARKER
;
  }

  function footer_html5($ga=NULL) {
    global $footer_printed;
    if ($footer_printed) {
      return;
    }
    $footer_printed = true;
    $gascript = '';

    if ($ga) {
      $gascript = <<<EOS
  <script type="text/javascript">
   var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
   document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
   try {
    var pageTracker = _gat._getTracker("$ga");
    pageTracker._trackPageview();
   } catch(err) {}</script>

EOS;
    }
    return <<<FOOTMARKER
$gascript </body>
</html>

FOOTMARKER
;
  }

  function redirect($url) {
    header('HTTP/1.1 303 See Other');
    header("Location: $url");
    $title = "Redirecting to $url";
    $url = htmlentities($url);
    $headers = "  <meta http-equiv=\"refresh\" content=\"0;URL=$url\" />\n";
    echo header_plain($title, $headers, 'strict');
    echo <<<EOF
  <h1>Redirecting</h1>
  <p>If your browser is not redirected into another page in a few seconds,
   follow the link below.</p>
  <p><a href="$url">$url</a></p>
<!--
  <p>Also remember to update your bookmarks or notify the webmaster of 
   any web page that contains a link to this address.</p>
-->
EOF;
    echo footer_plain();
  }

  function back($url=NULL, $text='back') {
    $url = isset($url) ? $url : $_SERVER['HTTP_REFERER'];
    $url = htmlentities($url);
    $text = htmlentities($text);
    return "<a href=\"$url\" onclick=\"".
           "if (history &amp;&amp; history.go) { history.go(-1);".
           " return false; }\">$text</a>";
  }

?>
