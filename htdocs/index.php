<?php

  require_once('protect.php');
  require_once('include_library.php');
  require_once('form_library.php');
  require_once('Coicop.php');
  require_once('Expense.php');
  require_once('model.php');
  require_once('view.php');
  require_once('config.php');
  # require_once('stats.php');

  # ini_set('error_reporting', 1024);
  ini_set('display_errors', 'off');
  ini_set('html_errors', 'off');
  ini_set('log_errors', 'on');
  ini_set('error_log', '/www/seuranta/logs/kulutus.seuranta.org-error.log');
  error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
  if (function_exists('my_error_handler')) {
    set_error_handler('my_error_handler');
  }

  $dtd = '<!DOCTYPE html';

  $scripts = <<<EOS
  <script type="text/javascript" src="view.js">
  </script>

EOS;

  $QUERY = getQuery($_REQUEST);
  $views = array('summary' => 'summary', 'details' => 'details',
                 'benchmarkimages' => 'benchmark', 'plot' => 'plot');
  if (isset($_COOKIE['tab']) && in_array($QUERY['view'], $views)) {
    if (isset($_REQUEST['init'])) {
      $QUERY['view'] = 'summary';
    }
    elseif (($_COOKIE['tab'] == 'summary') && ($QUERY['view'] == 'details') &&
        isset($_REQUEST['cat']) && $_REQUEST['cat']) {
      $QUERY['prevview'] = $_COOKIE['tab'];
    }
    else {
      $QUERY['prevview'] = $QUERY['view'];
      $QUERY['view'] = $views[$_COOKIE['tab']];
    }
  }
  $CONFIG = getConfig($_SESSION, $QUERY);
  $LOCALE = getLocale($_SESSION, $QUERY, $CONFIG);
  $CONFIG['db_debug'] = FALSE;
  if ($CONFIG['read_only']) {
    $QUERY['nolinks'] = TRUE;
  }

  $headers = <<<EOH
  <meta name="viewport" content="width=device-width" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="HandheldFriendly" content="True" />
  <meta name="MobileOptimized" content="width" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <!-- link rel="microsummary" type="application/x.microsummary+xml" href="./ms.xml" / -->
  <link rel="stylesheet" type="text/css" href="errors.css" />
  <link rel="stylesheet" type="text/css" href="common.css" />
  <link rel="stylesheet" media="screen" type="text/css" href="screen.css" id="screenstyle" />
  <link rel="stylesheet" media="print" type="text/css" href="print.css" title="Print style" />
  <link rel="stylesheet" media="handheld" type="text/css" href="handheld.css" title="Mobile style" />
  <style type="text/css">
   #piesvg {
    width: $CONFIG[pie_w]px;
    height: $CONFIG[pie_h]px;
   }
  </style>
EOH;

  $cc = new Coicop;
  $e = new Expense($_SESSION['userid']);

  if ($QUERY['view'] == 'export') {
   $fn = $LOCALE['expense'].
         '_'.date('Ymd', $QUERY['from']).'-'.date('Ymd', $QUERY['to']).
         '.tsv';
   header("Content-type: text/x-tab-separated; charset=UTF-8");
   header("Content-disposition: attachment; filename=$fn");
   # header('Content-type: text/plain; charset=UTF-8');
   export3($e, $cc, $QUERY);
   exit();
  }

  // only load google stuff if needed?
  if (in_array($QUERY['view'], $views)) {
    $scripts .= <<<EOS
  <script type="text/javascript" src="http://www.google.com/jsapi">
  </script>
  <!-- script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false">
  </script -->
  <script type="text/javascript">
   google.load("visualization", "1", {packages: ["annotatedtimeline"]});
  </script>
  <script type="text/javascript" src="./js/sorttable.js">
  </script>

EOS;
  }

  header('Vary: Accept, Accept-Language');

  $dtdtype = NULL;
  $svgtypes = array();
  $svgtypes = svg_support();
  if ($svgtypes[2]) {
    $dtdtype = 'svg';
  }

  if (!$CONFIG['product_table']) {
    $message = $LOCALE['db_not_found'];
    $QUERY['prevview'] = $QUERY['view'];
    $QUERY['view'] = 'logout';
  }

  $bmtargets = $e->getBenchmarkTargets();
  $QUERY['bmto'] = $QUERY['bmto'] ? $QUERY['bmto'] : $bmtargets[0]['id'];

  $headers .= prevnextlinks($QUERY, $e);
  $headers .= showhidedivs($QUERY);
  echo header_html5($LOCALE['expense'], $headers, $dtdtype, $QUERY['lang']);

  $_SESSION['start'] = isset($_SESSION['start']) ? $_SESSION['start'] :
                       $e->getLastId();
  if (isset($_REQUEST['modify'])) {
    if ($e->updateProduct($QUERY)) {
      trigger_error($LOCALE['update_ok'], E_USER_NOTICE);
    }
    else {
      trigger_error($LOCALE['update_failed'], E_USER_WARNING);
    }
  }
  if (isset($_REQUEST['insert'])) {
    if ($e->addProduct($QUERY)) {
      trigger_error($LOCALE['insert_ok'], E_USER_NOTICE);
    }
    else {
      trigger_error($LOCALE['insert_failed'], E_USER_WARNING);
    }
  }
  if (isset($_REQUEST['delete'])) {
    if ($_REQUEST['id']) {
      $attrs = array('id' => $_REQUEST['id']);
      $result = $e->deleteProduct($attrs);
      if ($result) {
        trigger_error($LOCALE['delete_ok'], E_USER_NOTICE);
      }
      else {
        trigger_error($LOCALE['delete_failed'], E_USER_NOTICE);
      }
      $QUERY['id'] = NULL;
    }
  }

  $nolimits = array();
  if (($e->getTotal($nolimits) == 0) && (!isset($_REQUEST['init'])) &&
      ($QUERY['view'] != 'insert') && ($QUERY['view'] != 'logout')) {
    $query = array('view' => 'insert');
    $urlattrs = attrs2url($query);
    $insert_url = "./?$urlattrs";
    $linktext = htmlentities($LOCALE['insert']);
    echo "  <h1 id=\"h1\">".htmlentities($LOCALE['expense'])."</a></h1>\n";
    printErrors($GLOBALS['ERRORS']);
    echo "  <div class=\"notice\">\n   <p>";
    echo htmlentities($LOCALE['no_products']);
    echo "</p>\n  </div>\n";
    echo <<<EOI
  <iframe src="$insert_url" width="600" height="450">
   <p><a href="$insert_url">$linktext</a></p>
  </iframe>

EOI;
    echo "  <p><a href=\"./?init=&amp;lang=$QUERY[lang]\">&raquo;&raquo; ".
         htmlentities($LOCALE['continue'])."</a></p>\n";
    echo <<<EOS

EOS;
    echo $scripts;
    echo footer_plain('UA-4404005-8');
    exit();
  }

  switch ($QUERY['view']) {
    case 'logout':
      include_once("logout.php");
      include_once("errors/logout.php");
      break;
 /*
   case 'delete':
      $view = $_REQUEST['prevview'];
      $QUERY['view'] = $view;
      $QUERY['id'] = NULL;
      # printErrors($GLOBALS['ERRORS']);
      if (($view == 'insert') || ($view == 'modify')) {
        insertform($cc, $QUERY);
        $query = array();
        $query['order'] = 'date desc';
        $query['lang'] = $QUERY['lang'];
        $query['view'] = $QUERY['view'];
        $query['start'] = $_SESSION['start'];
        details($e, $cc, $query);
        break;
      }
*/
    case 'insert':
      printErrors($GLOBALS['ERRORS']);
      insertform($cc, $QUERY);
      $query = array();
      $query['order'] = 'date desc';
      $query['lang'] = $QUERY['lang'];
      $query['view'] = $QUERY['view'];
      $query['start'] = $_SESSION['start'];
      details($e, $cc, $query);
      break;
    case 'modify':
      if ($_REQUEST['id']) {
        $attrs = array('id' => $QUERY['id']);
        $stmt = $e->getProducts($attrs);
        $row = db_fetch_assoc($stmt);
        $row['date'] = date('d.m.Y', strtotime($row['date']));
        $QUERY = array_merge($QUERY, $row);
      }
      printErrors($GLOBALS['ERRORS']);
      insertform($cc, $QUERY);
      $query = array();
      $query['order'] = 'date desc';
      $query['lang'] = $QUERY['lang'];
      $query['view'] = $QUERY['view'];
      # $query['id'] = $QUERY['id'];
      $query['start'] = $_SESSION['start'];
      details($e, $cc, $query);
      break;
    default:
      # if ($QUERY['order'] == 'date desc') {
      #   $QUERY['order'] = 'cost desc';
      # }
      echo "  <h1 id=\"h1\"><a href=\"./?init=&amp;lang=$QUERY[lang]\"".
           " title=\"".htmlentities($LOCALE['defaults'])."\">".
           htmlentities($LOCALE['expense'])."</a></h1>\n";
      form($cc, $QUERY);
      history($e, $QUERY);
      $b = new Expense($QUERY['bmto']);
      benchmarkhistory($e, $b, $cc, $QUERY);
      printErrors($GLOBALS['ERRORS']);
      links($QUERY);
      echo "  <div id=\"content\">\n";
      $query = $QUERY;
      $query['order'] = 'cost desc';
      summary($e, $cc, $query);
      $query['order'] = 'date desc';
      details($e, $cc, $query);
      $query = $QUERY;
      $query['date'] = NULL;
      $query['order'] = 'type';
      $query['group'] = $level;
      $query['type'] = NULL;
      $query['lang'] = $QUERY['lang'];
      $query['view'] = $QUERY['view'];
      benchmark($e, $b, $cc, $query, $bmtargets);
      # benchmarktable($e, $b, $cc, $query, $QUERY['bmto']);
      # foreach ($bmtargets as $targ) {
      #  $b = new Expense($targ['id']);
      #  benchmarktable($e, $b, $cc, $query, $targ['id']);
      # }
      $query = $QUERY;
      plot($e, $cc, $query);
      echo "  </div>\n";
      require_once('pie.php');
      timeline($e, $cc, $QUERY);
      break;
  }

  echo $scripts;
  echo footer_plain('UA-4404005-8');

?>
