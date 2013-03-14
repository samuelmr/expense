<?php

  function entify($string) {
    /* doc?  why not htmlentities? */ 
    /* used by /samuel/cv/, should leave tags as is and only convert chars */
    # echo "<!-- $string -->\n";
    $from = array('/&/',
                  '/å/', '/ä/', '/ö/',
                  '/Å/', '/Ä/', '/Ö/',
                  );
    $to = array('&amp;',
                '&aring;', '&auml;', '&ouml;',
                '&Aring;', '&Auml;', '&Ouml',
                );
    # echo "<!-- $string -->\n";
    return preg_replace($from, $to, $string);
  }

  function htmlentities_numeric($string){
    $encoded = "";
    for ($n=0;$n<strlen($string);$n++){
      $check = htmlentities($string[$n], ENT_QUOTES);
      if ($string[$n] != $check) {
        $encoded .= "&#".ord($string[$n]).";";
      }
      else {
        $encoded .= $string[$n];
      }
    }
    return $encoded;
  }

  // equal to htmlentities_numeric?
  function numericentities($string, $quote_style=ENT_COMPAT) {
    $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
    foreach ($trans as $key => $value)
      $trans[$key] = '&#'.ord($key).';';
    return strtr($string, $trans);
  } 

  function xhtmlattrs($string) {
    $s_quoted = '/(\w+)="([^"]*)"/e';
    $s_unquoted = '/(\w+)=([a-zA-Z0-9_-]+)/e';
    $r_quoted = 'strtolower(\'$1\')."=\\"$2\\""';
    $r_unquoted = 'strtolower(\'$1\')."=\\"$2\\""'; 
    $search = array($s_quoted, $s_unquoted, '/\\\\"/');
    $replace = array($r_quoted, $r_unquoted, '"');
/*
    echo "<!-- 
REPLACING
$search[0] with $replace[0]
AND
$search[1] with $replace[1]
AND
$search[2] with $replace[2]
-->\n";
    echo "<!--
IN: $string
OUT: ".preg_replace($search, $replace, $string)."
-->
";
*/
    return preg_replace($search, $replace, $string);
  }

  function html2xhtml($htmlstring) {

    global $htmlmatchtags;
    global $htmlemptytags;

    // initialize only once, but only when used!
    if (empty($htmlmatchtags)) {
      $htmltags = array('a', 'address', 'body',
                        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                        'html',
                        'head', 'p', 'table', 'td', 'title', 'tr');
    }
    if (empty($htmlemptytags)) {
      $emptytags = array('area', 'base', 'br', 'hr', 'img', 'link', 'meta');
    }

    $search = array();
    $replace = array();
    for ($i=0; $i<count($htmltags); $i++) {
      $s_alku = "|<$htmltags[$i]([^>]*)>|eis";
      $s_loppu = "|</$htmltags[$i]>|i";
      $r_alku = "\"<\".$htmltags[$i].xhtmlattrs('\$1').\">\"";
      $r_loppu = "</$htmltags[$i]>";
      $search = array($s_alku, $s_loppu);
      $replace = array($r_alku, $r_loppu);
/*
      echo "<!-- 
REPLACING
$s_alku with $r_alku
AND
$s_loppu with $r_loppu
-->\n";
      # echo "<!-- SEARCHING $search[0] -->\n";
*/
      $htmlstring = preg_replace($search, $replace, $htmlstring);
    }

    for ($i=0; $i<count($emptytags); $i++) {
      $search = array("|<$emptytags[$i]([^>]*)>|is");
      $replace = array("<$emptytags[$i]\\1 />");
/*
      echo "<!-- 
REPLACING
$search[0] with $replace[0]
AND
$search[1] with $replace[1]
-->\n";
      echo "<!-- IN: $htmlstring -->\n";
*/
      $htmlstring = preg_replace($search, $replace, $htmlstring);
/*
      echo "<!-- OUT: $htmlstring -->\n";
*/
    }

    return $htmlstring;
  }

 
  $matchingtagspattern = '/(<([\w]+)([^>]*)>)(.*)(<\/\2>)/is';
  $emptytagpattern = '/(<([\w]+)([^>]*)>)(.*)(?!<\/\2>)/i';
  $quotedattributepattern = '/(\w+)="([^"]*)"/';
  $unquotedattributepattern = '/(\w+)=([a-zA-Z0-9_-]*)/';

  function html2xhtml_recursive(&$htmlstring, $previousmatches=array()) {

    echo "<!-- SEARCHING $htmlstring -->\n\n";
    global $matchingtagspattern;
    global $emptytagpattern;
    global $quotedattributepattern;
    global $unquotedattributepattern;

    static $search = array();
    static $replace = array();
    static $matches = array();
 
    preg_match_all ($matchingtagspattern, $htmlstring, $matches);
    for ($i=0; $i<count($matches[0]); $i++) {
      $element = $matches[0][$i];
      if ($element) {
        $tag = strtolower($matches[2][$i]);
        echo "<!-- $i: $tag (".count($matches[0]).") -->\n";
        $attrs = $matches[3][$i];
        $content = $matches[4][$i];
        $matches = html2xhtml($content, $matches);
        $search[] = "/".preg_replace('/\\//', '\\/', $element)."/i";
        $replace[] = "<$tag$attrs>$content</$tag>";
/*
        $n = count($search);
        echo "<!-- 
===================================================================
to be searched: $n

===================================== replace =====================

".$search[$n-1]." 

====================================== with =======================

".$replace[$n-1]." 

===================================================================
-->\n";
        $htmlstring = preg_replace($search,$replace,$htmlstring);
*/
      }
    }

    preg_match_all ($emptytagpattern, $htmlstring, $matches);
    for ($i=0; $i<count($matches); $i++) {
      $element = $matches[0][$i];
      if ($element) {
        $tag = strtolower($matches[2][$i]); 
        $attrs = $matches[3][$i];
        $search[] = "/".preg_replace('/\\//', '\\/', $element)."/";
        $replace[] = "<$tag$attrs />"; 
        $n = count($search);
        echo "<!-- 
===================================================================
to be searched: $n

===================================== replace =====================

".$search[$n-1]." 

====================================== with =======================

".$replace[$n-1]." 

===================================================================
-->\n";
        $htmlstring = preg_replace($search,$replace,$htmlstring);
        preg_replace($search,$replace,$htmlstring);
      }
    }

    # echo "<!--\n";
    # for ($x=0; $x<count($search); $x++) {
    #   echo "$x: $search[$x] - $replace[$x]\n";
    # } 
    # var_dump($search);
    # var_dump($replace);
    # echo "\n-->\n"; 
    preg_replace($search,$replace,$htmlstring);

    return $previousmatches;
  } 

?>
