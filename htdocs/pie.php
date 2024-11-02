<?php

  if (! $_SESSION['userid']) {
    include_once('protect.php');
  }
  require_once('database.php');
  require_once('html_library.php');
  require_once('include_library.php');
  require_once('Coicop.php');
  require_once('Expense.php');
  require_once('model.php');
  require_once('view.php');
  require_once('config.php');

  $query = (isset($QUERY) ? $QUERY : getQuery($_REQUEST));
  # $config = (isset($CONFIG) ? $CONFIG : getConfig($_SESSION, $query));
  $config = getConfig($_SESSION, $query);
  $locale = (isset($LOCALE) ? $LOCALE : getLocale($_SESSION, $query, $config));
  if ($config['force_query']) {
    $query['prod'] = $config['force_query'];
  }

  $cc = new Coicop;
  $cats = $cc->getCats();
  $cat_count = count($cats);
  $e = new Expense($_SESSION['userid']);

  $urlattrs = attrs2url($query);
  # $charset = 'iso-8859-1';
  $charset = 'UTF-8';
  $script = preg_replace('/\/[^\/]*$/', '', $_SERVER['REQUEST_URI']);
  $ssl = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : NULL;
  $mainscript = 'http'.($ssl ? 's' : '').'://'.
                $_SERVER['HTTP_HOST'].$script;

  $src = "$mainscript/pie.php?$urlattrs&amp;userid=$_SESSION[userid]";
  $svg_ctype = 'image/svg+xml';
  // Shockwave Flash Object
  # $svg_classid = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';
  // Microsoft DirectAnimation Structured Graphics
  # $svg_classid = 'clsid:369303C2-D7AC-11D0-89D5-00A0C90833E6"
  // SVG Behavior Factory Class
  # $svg_classid = 'clsid:78156a80-c6a1-4bbf-8e6a-3cd390eeb4e2';
  // SVGRenderer
  # $svg_classid = 'clsid:abd2f8ea-f1d9-4704-bdc3-a07741f067a2';
  // Adobe SVG Viewer
  $svg_classid = 'clsid:377B5106-3B4E-4A2D-8520-8767590CAC86';
  # $svgplugin = 'http://download.adobe.com/pub/adobe/magic/svgviewer/win/3.x/3.01/en/SVGView.exe';
  // Corel SVG Viewer
  # $svg_classid = 'clsid:25FDBBF6-8AC8-4126-A5F6-7C65EEA86793';

  $svgplugin = 'http://www.adobe.com/svg/viewer/install/auto/';
  $svg_stuff = '';

  $attrs = $query;
  $attrs['type'] = NULL;
  $attrs['group'] = NULL;
  $attrs['order'] = 'type';
  $total = $e->getTotal($attrs);

  // measures for the pie
  $radius = $config['pie_h'] / 2.5;
  $origo_x = ($radius * 1.25);
  $origo_y = $config['pie_h'] / 2;

  // measures for the legend texts
  $line_height = ($cat_count ? round((2 * $radius) / $cat_count) : 14);
  if ($line_height > ($radius / 2.5)) {
    $line_height = ($radius / 2.5);
  }
  $font_size = round(0.88 * $line_height);
  $legend_x = round($radius * 2.5) + ($line_height / 2);
  $legend_y = round($radius * 0.25);

  $rx = $radius;
  $ry = $radius;
  $done_deg = 270;
  $lastcolor = '#FFFFFF';
  for ($i=0; $i<$cat_count; $i++) {
    $cat =& $cats[$i];
    $color = $cat->color;
    $name = (($query['lang'] == 'fi') ? $cat->nameFi : $cat->nameEn);
    $attrs['type'] = $cat->id;
    $cost = $e->getTotal($attrs);
    $urlattrs = attrs2url($attrs);
    $percent = ($total > 0) ? ($cost / $total) : 0;
    # $title = sprintf("%s: %.02f (%.02f %%)",
    #                  $name, round($cost, 2), (100 * $percent));
    $title = "$name: ".number_format(floatval($cost), 2, ',', ' ')." € (".number_format(100 * $percent, 2, ',', ' ')." %)";
    # $title = htmlentities_numeric($title);
    # $hint = htmlentities_numeric($locale['showonly']." ".$name);
    $hint = $locale['showonly']." ".$name;

    $text_x = ($legend_x+$line_height+4);
    $text_y = ($legend_y+$line_height-2);

    $m_x = isset($a_x) ? $a_x : ($origo_x);
    $m_y = isset($a_y) ? $a_y : ($origo_y - $ry);

    if (round($percent, 2) < 1) {
      $curr_deg = 360 * $percent;
      $stroke = "#000000";
    }
    else {
      $curr_deg = 359.9;
      $stroke = "$color";
    }
    $a_x = round($origo_x + $rx * cos(deg2rad($curr_deg + $done_deg)), 2);
    $a_y = round($origo_y + $rx * sin(deg2rad($curr_deg + $done_deg)), 2);

    $xrot = ($m_y <= $origo_y) ? -1 : 1;
    $large = ($cost > $total/2) ? 1 : 0;
    $sweep = 1;

    $svg_stuff .= <<<EO1
     <g class="class" onclick="window.top.location.href='$mainscript?$urlattrs'; return false;">
      <g class="legend">
       <a xlink:href="$mainscript?$urlattrs" tooltip="enabled" xlink:title="$hint">
        <rect x="$legend_x" y="$legend_y" width="$line_height" height="$line_height" fill="$color" stroke="#000000" stroke-width="1"  />
        <text x="$text_x" y="$text_y" font-size="${font_size}px">
         $title
        </text>
        <hint>$hint</hint>
       </a>
      </g>

EO1;
    if ($cost > 0) {
      $svg_stuff .= <<<EO2
      <g class="slice" hint="$hint" tooltip="enabled">
       <a xlink:href="$mainscript?$urlattrs" xlink:title="$title">
        <path id="slice$i" fill="$color" stroke="$stroke" stroke-width="1" fill-rule="evenodd" d="M$m_x,$m_y A$rx,$ry $xrot $large $sweep $a_x,$a_y L$origo_x,$origo_y Z" />
       </a>
      </g>

EO2;
      $lastcolor = $color;
    }
    $svg_stuff .= "     </g>\n";
    $done_deg += $curr_deg;
    $legend_y += $line_height;
  }
  if (($a_x != ($origo_x)) || ($a_y != ($origo_y - $ry))) {
    $m_x = $a_x;
    $m_y = $a_y;
    $a_x = $origo_x;
    $a_y = $origo_y - $ry;
    $xrot = ($m_y <= $origo_y) ? -1 : 1;
    $large = 0;
    $sweep = 1;
    $color = $lastcolor;
    $svg_stuff .= <<<EO3
      <g class="slice">
       <a xlink:href="$mainscript?$urlattrs" xlink:title="$title">
        <path id="slice$i" fill="$color" stroke="$color" stroke-width="1" fill-rule="evenodd" d="M$m_x,$m_y A$rx,$ry $xrot $large $sweep $a_x,$a_y L$origo_x,$origo_y Z" />
       </a>
      </g>

EO3;
  }
  $svg_contents = <<<EOS
<svg id="piesvg" version="1.2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><!-- width="100%" height="100%" viewBox="0 0 $config[pie_w] $config[pie_h]" preserveAspectRatio="xMidYMid" -->
 <defs>
  <style type="text/css">
   svg, g {
    overflow: hidden;
   }
   g.class:hover g.slice, g.class:hover g.legend a rect {
    fill-opacity: 0.75;
    stroke: #666666;
   }
   g.class:hover g.legend a text {
    fill: #666666;
   }
   text {
    font-family: Helvetica,Arial,Verdana,sans-serif;
    font-size: 11px;
    font-style: normal;
   }
  </style>
 </defs>
 <title>$locale[pie]</title>
$svg_stuff
</svg>

EOS
;

  $standalone = false;
  list($object, $embed, $inline, $image) = svg_support();
  if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
    $standalone = true;
    $object = false;
  }
  $svg_headers = "";
  if ($standalone) {
    $svg_headers .= "<?xml version=\"1.0\" encoding=\"$charset\"?>\n";

    // XXX ugly!
    # $svg_contents = preg_replace('/svg:/', '', $svg_contents);
    # $svg_contents = preg_replace('/xmlns:svg/', 'xmlns', $svg_contents);

    $svg_file = $svg_headers.$svg_contents;
    $svg_size = strlen($svg_file);
    header("Content-Length: $svg_size");
    header("Content-Type: $svg_ctype; charset=$charset");
    header("Accept-Ranges: bytes");
    echo $svg_file;
    # echo "<!--\n";
    # var_dump($config);
    # echo "-->\n";
  }
  else {
    echo "  <div id=\"pie\">\n";
    if ($object) {
      echo "   <object data=\"$src\"".
           " type=\"$svg_ctype\"".
           " width=\"$config[pie_w]\" height=\"$config[pie_h]\">\n";
    }
    if ($embed) {
      echo "    <embed src=\"$src\" type=\"$svg_ctype\"".
           " width=\"$config[pie_w]\" height=\"$config[pie_h]\"".
           " pluginspage=\"$svgplugin\" />\n";
    }
    if ($image) {
      echo "   <img src=\"$src\"".
           " width=\"$config[pie_w]\" height=\"$config[pie_h]\" />\n";
    }
    if ($inline) {
/*
      echo <<<EOS
   <object id="AdobeSVG" classid="clsid:78156a80-c6a1-4bbf-8e6a-3cd390eeb4e2"> </object>
   <?import namespace="svg" urn="http://www.w3.org/2000/svg" implementation="#AdobeSVG"?>

EOS;
*/
      echo $svg_contents;
    }
    if ($object) {
      if (!$embed && !$inline && !$image) {
        // object must have content (WAI)
        echo "    $locale[pie]\n";
        echo "    <p class=\"warning\">".
             htmlspecialchars($locale['svg_required']).
             "</p>\n";
        echo "    <p><a href=\"$svgplugin\">$svgplugin</a></p>\n";
      }
      echo "   </object>\n";
    }
    echo "  </div>\n";
  }

?>
