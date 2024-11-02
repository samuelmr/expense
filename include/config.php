<?php

  require_once('database.php');
  require_once('model.php');
  global $ERRORS;
  global $ERRORCONFIG;
  $ERRORCONFIG = array('max_errors' => 20);
  global $DB_CONFIG;
  $DB_CONFIG = array('debug' => false);

  function getQuery(&$req) {
    $lang = (isset($req['lang']) ? $req['lang'] : NULL);
    $from = (isset($req['from']) ? date2time($req['from']) : NULL);
    if (!$from) {
      // need a better detection?
      if (date('d') == 31) {
        $from = mktime(0, 0, 0, date('m'), 1, date('Y'));
      }
      else {
        $from = mktime(0, 0, 0, date('m')-1, date('d')+1, date('Y'));
      }
      /*
      $from = mktime(0, 0, 0, date('m'), date('d')-6, date('Y'));
      */
    }
    $to = (isset($req['to']) ? date2time($req['to']) : NULL);
    if (!$to) {
      $to = strtotime(date('Y-m-d'));
    }
    if (date('H:m:s', $to) == '00:00:00') {
      $to = strtotime('+1 day', $to) - 1; // start of date to end of date
    }
    $date = (isset($req['date']) ? $req['date'] : date('d.m.Y'));

    $other = NULL;
    $currency = NULL;

    if (isset($req['other']) && isset($req['currency']) &&
        $req['other']>0) {
      $other = $req['other'];
      $currency = $req['currency'];
    }

    $view = 'summary';
    if (isset($req['view'])) {
      $vs = array('insert', 'modify', 'delete', 'settings', 'details', 'summary', 'benchmark', 'export', 'json', 'logout');
      $view = (in_array($req['view'], $vs) ? $req['view'] : $view);
    }
 
    $order = isset($req['order']) ? $req['order'] : 'cost desc';
    $prev = isset($req['prev']) ? $req['prev'] : 'date desc';
 
    if (($order == $prev) && !strstr($order, 'desc')) {
      $order .= ' desc';
    }

    return array('from' => $from,
                 'to' => $to,
                 'id' => (isset($req['id']) ? $req['id'] : NULL),
                 'date' => $date,
                 'type' => (isset($req['type']) ? $req['type'] : NULL),
                 'prod' => (isset($req['prod']) ? $req['prod'] : NULL),
                 'cost' => (isset($req['cost']) ? $req['cost'] : NULL),
                 'other' => $other,
                 'currency' => $currency,
                 'group' => (isset($req['group']) ? $req['group'] : NULL),
                 'view' => $view,
                 'bmto' => (isset($req['bmto']) ? $req['bmto'] : NULL),
                 'lang' => $lang,
                 'order' => $order,
                 // possible cause of faults...
                 'prev' => $prev);
  }

  function getConfig(&$sess, &$query) {
    $conf = array();
    if (isset($sess['expense_config']) && !isset($_REQUEST['init'])) {
      return $sess['expense_config'];
    }
    elseif (isset($sess['userid']) && $sess['userid']) {
      $select = "
        SELECT    *
        FROM      expense2_config
        WHERE     user_id = '".htmlentities($sess['userid'])."'";
      $stmt = db_query($select);
      if (!$stmt) {
        echo "<pre>$select\n$stmt\n".db_error()."\n</pre>\n";
      }
      $conf = db_fetch_assoc($stmt);
      db_free_result($stmt);
      $lang = isset($sess['lang']) ? $sess['lang'] : NULL;
      // query will override session
      $lang = isset($query['lang']) ? $query['lang'] : $lang;
      // read from config only if not already found
      $lang = $lang ? $lang : $conf['lang'];
      $conf['timeline_width'] = 6;
      $conf['timeline_height'] = 168;
      # $conf['pie_w'] = ($lang == 'fi') ? 585 : 700;
      $conf['pie_w'] = 800;
      $conf['pie_h'] = 200;
      $conf['plot_w'] = 700;
      $conf['plot_h'] = 200;
      $conf['pie_order'] = 'c.id';
      $conf['maxdays'] = 366;
      $conf['mindays'] = 2;
      $conf['bar_width'] = 400;
      $conf['bar_height'] = 12;
      $imgdir = "/i";
      $conf['hor_100'] = "$imgdir/horz.gif";
      $conf['hor_75'] = "$imgdir/horz.gif";
      $conf['hor_50'] = "$imgdir/horz.gif";
      $conf['hor_25'] = "$imgdir/horz.gif";
      $conf['ver_100'] = "$imgdir/vert.gif";
      $conf['ver_75'] = "$imgdir/vert.gif";
      $conf['ver_50'] = "$imgdir/vert.gif";
      $conf['ver_25'] = "$imgdir/vert.gif";
      $conf['qorsepr'] = '|';
      $conf['qandsepr'] = '&';
      $sess['expense_config'] = $conf;
    }
    return $conf;
  }

  function getLocale(&$sess, &$query, &$conf) {
    $host = $_SERVER['HTTP_HOST'];
    $def = 'en';
    if (stristr($host, 'kulutus') || stristr($host, 'talous')) {
     $def = 'fi';
    }
    $LOCALE = isset($GLOBALS['LOCALE']) ? $GLOBALS['LOCALE'] : array();
    $lang = isset($conf['lang']) ? $conf['lang'] : NULL;
    $lang = isset($sess['lang']) ? $sess['lang'] : $lang;
    $lang = isset($query['lang']) ? $query['lang'] : $lang;
    if (isset($sess['expense_locale']) && !$lang) {
      return $sess['expense_locale'];
    }
    if (!$lang && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
     $acc = preg_replace('/(;.*$)/', '', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
     $a = explode(',', $acc);
     # $lang = (array_search('en', $a) < array_search('fi', $a) ? 'en' : 'fi');
     foreach ($a as $l) {
      if (@include_once("locale_$lang.php")) {
       $lang = $l;
       break;
      }
     }
    }
    else {
     // try to include from include_path
     @include_once("locale_$lang.php");
    }
    if (!is_array($LOCALE) || (count($LOCALE) < 1)) {
      $lang = $def;
      include_once("locale_$lang.php");
    }
    $conf['lang'] = $lang;
    $query['lang'] = $lang;
    $sess['lang'] = $lang;
    # trigger_error('set language to '.$lang, E_USER_NOTICE);
    return $LOCALE;
  }

  function my_error_handler($errno, $str, $file, $line) {
    $ERRORS = &$GLOBALS['ERRORS'];
    $CONFIG = &$GLOBALS['ERRORCONFIG'];
    switch ($errno) {
      case E_NOTICE:
        $class = 'notice';
        break;
      case E_USER_NOTICE:
        $class = 'notice';
        break;
      case E_WARNING:
        $class = 'warning';
        break;
      case E_USER_WARNING:
        $class = 'warning';
        break;
      case E_USER_ERROR:
        $class = 'error';
        break;
      default:
        $class = 'warning';
        break;
    }
    if (isset($ERRORS) && is_array($ERRORS) && count($ERRORS) < $CONFIG['max_errors']) {
      $ERRORS[] = array($str, $class, $file, $line);
    }
  }

?>
