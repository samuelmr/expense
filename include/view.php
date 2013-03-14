<?php

  function selectListCombined(&$lang, &$cc, $name='type',
                              $selected=NULL, $addall=TRUE) {
    $LOCALE = $GLOBALS['LOCALE'];
    $string = "<select id=\"$name\" name=\"$name\">";
    if ($addall) {
      $string .= "<option value=\"\">".
                 htmlentities($LOCALE['all'])."</option>";
    }
    for ($i=0; $i<count($cc->cats); $i++) {
      $cat =& $cc->cats[$i];
      # $label = (($lang == 'fi') ? $cat->nameFi : $cat->nameEn);
      $label = $cc->getCatName($cat->id, $lang);
      $string .= "<optgroup label=\"".htmlentities($label)."\">";
      if ($addall) {
        $string .= "<option value=\"$cat->id\">".
                   htmlentities($LOCALE['all'])."</option>";
      }
      $subs = $cat->getSubs();
      for ($j=0; $j<count($subs); $j++) {
	$sub =& $subs[$j];
        # $text = (($lang == 'fi') ? $sub->nameFi : $sub->nameEn);
        $text = $cc->getSubName($sub->id, $lang);
        $string .= "<option value=\"".htmlentities($sub->id)."\"".
                   (($sub->id == $selected) ? ' selected="selected"' : '').
                   ">".htmlentities($text)."</option>";
      }
      $string .= "</optgroup>";
    }
    $string .= "</select>";
    return $string;
  }

  function history(&$e, $query) {
    $LOCALE = $GLOBALS['LOCALE'];
    echo "  <table id=\"history\">\n   <thead>\n";
    echo "    <tr><th class=\"year\">".
               htmlentities($LOCALE['year'])."</th>";
    $thfmt = "<th class=\"month\" id=\"m%d\" scope=\"col\">%s</th>";
    for ($i=1; $i<=12; $i++) {
      echo sprintf($thfmt, $i, htmlentities($LOCALE['months'][$i]));
    }
    echo "<th class=\"total\" id=\"total\" scope=\"col\">".
       htmlentities($LOCALE['total'])."</th>";
    echo "</tr>\n   </thead>\n   <tbody>\n";

    $start_time = $e->getFirstDate();  

    for ($y=date('Y', $start_time); $y<=date('Y'); $y++) {
      echo "    <tr><th class=\"year\" id=\"y$y\" scope=\"row\">$y</th>";
      for ($m=1; $m<=12; $m++) {
        $query['from'] = mktime(0, 0, 0, $m, 1, $y);
        $query['to'] = mktime(0, 0, 0, $m+1, 1, $y)-1;
        $urlattrs = attrs2url($query);
        $link = "./?$urlattrs";
        $tmp = sprintf('%.2f', $e->getTotal($query));
        $title = htmlentities($LOCALE['months'][$m])." $y";
        echo "<td class=\"month\" headers=\"y$y m$m\">".
           "<a href=\"$link\" title=\"".
           htmlentities($LOCALE['showonly']).
           " $title\">".
           str_replace('.', ',', $tmp)."</a>".
           "</td>";
      }
      $query['from'] = mktime(0, 0, 0, 1, 1, $y);
      $query['to'] = mktime(0, 0, 0, 1, 1, $y+1)-1;
      $urlattrs = attrs2url($query);
      $link = "./?$urlattrs";
      $tmp = sprintf('%.2f', $e->getTotal($query));
      $title = htmlentities($LOCALE['total'])." $y";
      echo "<td class=\"year\" headers=\"y$y total\">".
           "<a href=\"$link\" title=\"$title\">".
           str_replace('.', ',', $tmp)."</a></td>";
      echo "</tr>\n";
    }
    echo "   </tbody>\n  </table>\n";
  }

  function details(&$e, &$cc, &$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $total = $e->getTotal($query);
    // copy; may be modified
    $attrs = $query;
    $attrs['prev'] = $attrs['order'];
    # $th_up = '<img src="/i/up.gif" width="9" height="9" alt="/\\" />';
    # $th_down = '<img src="/i/down.gif" width="9" height="9" alt="\\/" />';
    $total_perc = 0;
    echo "  <table id=\"details\" class=\"sortable\">\n";
    echo "   <thead>\n";
    echo "    <tr>\n";
    $attrs['order'] = 'date';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"date\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['date']).
         # (($query['order'] == 'date') ? $th_up : '').
         # (($query['order'] == 'date desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    $attrs['order'] = 'cost';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"cost\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['cost']).
         # (($query['order'] == 'cost') ? $th_up : '').
         # (($query['order'] == 'cost desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    echo "     <th class=\"perc\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['perc']).
         # "</a>".
         "</th>\n";
    $attrs['order'] = 'type';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"type\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['type']).
         # (($query['order'] == 'type') ? $th_up : '').
         # (($query['order'] == 'type desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    $attrs['order'] = 'prod';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"desc\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['prod']).
         # (($query['order'] == 'prod') ? $th_up : '').
         # (($query['order'] == 'prod desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    # echo "     <th class=\"edit\">".htmlentities($LOCALE['admin'])."</th>\n";
    echo "    </tr>\n";
    echo "   </thead>\n";
    echo "   <tbody>\n";
    $prods = $e->getProducts($query);
    $i = 0;
    while ($row = db_fetch_row($prods)) {
      flush();
      $i++;
      $date = date('d.m.Y', strtotime($row['date']));
      $attrs = $query; // new copy
      $attrs['from'] = strtotime($row['date']);
      $attrs['to'] = strtotime($row['date']);
      $dateurl = attrs2url($attrs);
      $datehint = htmlentities("$LOCALE[showonly] $date");
      $cost = sprintf("%.2f", $row['cost']);
      $prod = $row['prod'];
      $type = $cc->getSubName($row['type'], $query['lang']);
      $oa = $row['other'];
      $oc = $row['currency'];
      $attrs = $query; // new copy
      $attrs['type'] = $row['type'];
      $urlattrs = attrs2url($attrs);
      $hint = htmlentities("$LOCALE[showonly] $type");
      $p = ($total > 0) ? (round($row['cost']/$total, 4)) : 0;
      $total_perc += $p;
      $perc = sprintf("%.02f&nbsp;%%", 100 * $p);
      echo "    <tr>\n";
      echo "     <td class=\"date\">".
           "<a title=\"$datehint\" href=\"?$dateurl\">".
           $date.
           "</a>".
           "</td>\n";
      echo "     <td class=\"cost\"".($oa ? " title=\"$oa $oc\"" : "").">".
           str_replace('.', ',', $cost)."</td>\n";
      echo "     <td class=\"perc\">".str_replace('.', ',', $perc)."</td>\n";
      echo "     <td class=\"type\">".
           "<a href=\"?$urlattrs\" title=\"$hint\">".
           htmlentities($type)."</a></td>\n";
      $attrs = $query; // new copy
      $attrs['prod'] = $prod;
      $urlattrs = attrs2url($attrs);
      $hint = htmlentities("$LOCALE[search]: $prod");
      echo "     <td class=\"desc\">".
           "<a href=\"?$urlattrs\" title=\"$hint\">".
           htmlentities($prod)."</a>";
           # "</td>\n";
      if (!$CONFIG['read_only']) {
        # echo "     <td class=\"edit\">";
        echo "     <span class=\"edit\">";
        echo "<a target=\"insWin\" class=\"modify\"".
             " href=\"./?view=modify&amp;id=$row[id]&amp;type=$row[type]".
             "&amp;cat=".substr($row['type'], 0, 2)."\"".
             " title=\"".htmlentities($LOCALE['modify'])."\">".
             "<img src=\"i/edit.png\" width=\"16\" height=\"16\"".
             " alt=\"".htmlentities($LOCALE['modify'])."\" />".
             "</a>";
        $attrs = $query; // new copy
        $attrs['id'] = $row['id'];
        # $prevview = $attrs['view'];
        $attrs['delete'] = htmlentities($LOCALE['delete']);
        $urlattrs = attrs2url($attrs);
        echo " <a class=\"delete\"".
             " href=\"./?$urlattrs\"".
             " title=\"".htmlentities($LOCALE['delete'])."\">".
             "<img src=\"i/delete.png\" width=\"16\" height=\"16\"".
             " alt=\"".htmlentities($LOCALE['delete'])."\"".
             " onclick=\"if(!confirm('".
             htmlentities($LOCALE['delete_confirm']).
             " (".addslashes(htmlentities($prod)).")".
             "')) { return false }\"".
             " />".
             "</a>";
        echo "</span>";
      }
      echo "</td>\n";
      echo "    </tr>\n";
    }
    db_free_result($prods);
    echo "   </tbody>\n";
    $total = sprintf("%.2f", $e->getTotal($query));
    $total_perc = sprintf("%.02f&nbsp;%%", 100 * $total_perc);
    # echo "   <tfoot>\n";
    echo "   <tbody>\n";
    echo "    <tr class=\"total sortbottom\">\n";
    echo "     <td class=\"total\">".htmlentities($LOCALE['total'])."</td>\n";
    echo "     <td class=\"cost\" id=\"currenttotal\">".
         str_replace('.', ',', $total)."</td>\n";
    echo "     <td class=\"perc\">".str_replace('.', ',', $total_perc).
         "</td>\n";
    echo "     <td class=\"type\" colspan=\"2\">&nbsp;</td>\n";
    # echo "     <td class=\"edit\">&nbsp;</td>\n";
    echo "    </tr>\n";
    # echo "   </tfoot>\n";
    echo "   </tbody>\n";
    echo "  </table>\n";
  }

  function form(&$cc, &$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $fid = 'form';
    echo "  <form action=\"./\" method=\"get\" id=\"$fid\">\n";
    echo "   <fieldset id=\"formfs\">\n    ";
    echo form_input('order', $query['order'], 'hidden');
    echo "\n    ";
    echo form_input('prev', $query['prev'], 'hidden');
    echo "\n";
    echo "    <fieldset id=\"selectfs\">\n     ";
    $type = $query['type'];
    echo form_label('type', NULL, $LOCALE['type'], 'class="header"');
    echo selectListCombined($query['lang'], $cc, 'type', $type)."\n";
    echo "    </fieldset>\n";
/*
    echo "     <fieldset id=\"viewfs\">";
    echo "<label>".
         htmlentities($LOCALE['view']).":</label>";
    echo "<span class=\"bind\">".
         form_radio('view', 'summary', $query['view']).
         form_label('view', 'summary', $LOCALE['summary'], 'class="submit"').
         "</span>";
    echo "<span class=\"bind\">".
         form_radio('view', 'details', $query['view']).
         form_label('view', 'details', $LOCALE['details'], 'class="submit"').
         "</span>";
    echo "<span class=\"bind\">".
         form_radio('view', 'benchmark', $query['view']).
         form_label('view', 'benchmark', $LOCALE['benchmark'], 'class="submit"').
         "</span>";
    # echo "<span class=\"bind\">".
    #      form_radio('view', 'excel', $query['view']).
    #      form_label('view', 'excel', $LOCALE['excel'], 'class="submit"').
    #      "</span>";
    echo "</fieldset>\n";
*/
    echo "    <fieldset id=\"searchfs\">";
    $prod = $query['prod'];
    echo "<label for=\"".make_id('prod', $prod)."\">".
         htmlentities($LOCALE['search']).":</label>";
    echo form_input('prod', $prod, 'text', 10, 'maxlength="30"', TRUE);
    echo "</fieldset>\n";
    echo "    <fieldset id=\"datefs\">";
    $from = date('d.m.Y', $query['from']);
    echo "<label for=\"".make_id('from', $from)."\">".
         htmlentities($LOCALE['date']).":</label>";
    echo form_input('from', $from, 'text', 10, 'maxlength="10"', TRUE);
    $to = date('d.m.Y', $query['to']);
    echo "<label for=\"".make_id('to', $to)."\">&#8211;</label>";
    echo form_input('to', $to, 'text', 10, 'maxlength="10"', TRUE);
    echo "</fieldset>\n";
    echo "    <fieldset id=\"submitfs\">";
    echo form_input('', htmlentities($LOCALE['show']), 'submit');
    echo "</fieldset>\n";
    echo "    <fieldset id=\"linksfs\">\n";
    $attrs = $query;
    # $attrs['lang'] = $CONFIG['lang'];
    $attrs['prevview'] = $attrs['view'];
    $attrs['view'] = 'insert';
    $urlattrs_insert = attrs2url($attrs);
    $text_insert = htmlentities($LOCALE['insert']);
    $text_logout = htmlentities($LOCALE['logout']);
    $logoutmsg = urlencode($LOCALE['logoutmsg']);
    $returnmsg = urlencode($LOCALE['returnmsg']);
    echo <<<EO1
     <ul id="links">
      <li><a id="insertlink" target="insWin" href="./?$urlattrs_insert" class="modify">$text_insert</a></li>

EO1;
    if (!$CONFIG['read_only']) {
      echo <<<EO2
      <li><a id="logoutlink" href="./?view=logout&amp;message=$logoutmsg&amp;back=$returnmsg">$text_logout</a></li>

EO2;
    }
    echo "     </ul>\n";
    echo "    </fieldset>\n";
    echo "   </fieldset>\n";
    # $attrs = $query;
    # $attrs['view'] = $row['benchmark'];
    # $urlattrs = attrs2url($attrs);
    # echo "   <p><a href=\"?$urlattrs\">".htmlentities($LOCALE['benchmark']).
    #      "</a></p>\n";
    echo "  </form>\n";
  }

  function insertform(&$cc, &$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $cost = $query['cost'];
    $currency = $query['currency'];
    $date = $query['date'];
    $other = $query['other'];
    $prod = $query['prod'];
    $rate = (($other > 0) ? $cost/$other : '');
    $type = $query['type'];
    echo "  <form action=\"./\" method=\"post\" id=\"insertform\"".
         " class=\"$query[view]\">\n";
    echo "   <fieldset id=\"insertfs\">\n    ";
    echo form_input('id', $query['id'], 'hidden')."\n    ";
    echo form_input('view', $query['view'], 'hidden')."\n";
    echo "    <fieldset id=\"selectfs\">\n     ";
    echo form_label('type', NULL, $LOCALE['type'], 'class="header"');
    echo selectListCombined($query['lang'], $cc, 'type', $type, FALSE);
    $coicopurl = 'http://www.stat.fi/tk/tt/luokitukset/popup/coicop.pdf';
    echo " <a href=\"$coicopurl\" target=\"statfi\">(?)</a>";
    echo "\n";
    echo "    </fieldset>\n";
    echo "    <fieldset id=\"datefs\">";
    echo form_label('date', $date, $LOCALE['date'], 'class="header"');
    echo form_input('date', $date, 'text', 10, 'maxlength="10" class="date"', TRUE);
    echo "    </fieldset>\n";
    echo "     <fieldset id=\"otherfs\">";
    echo form_label('other', $other, $LOCALE['other'], 'class="header"');
    echo form_input('other', $other, 'text', 10, ' class="money"', TRUE);
    echo form_label('currency', $currency, $LOCALE['currency'], 'class="header"');
    echo form_input('currency', $currency, 'text', 4, 'maxlength="3" class="curr"', TRUE);
    echo form_label('rate', $rate, $LOCALE['rate'], 'class="header"');
    echo form_input('rate', $rate, 'text', 10, ' class="money"', TRUE);
    echo "</fieldset>\n";
    echo "     <fieldset id=\"costfs\">";
    echo form_label('cost', $cost, $LOCALE['cost'], 'class="header"');
    echo form_input('cost', $cost, 'text', 10, ' class="money"', TRUE);
    echo "</fieldset>\n";
    echo "     <fieldset id=\"prodfs\">";
    echo form_label('prod', $prod, $LOCALE['prod'], 'class="header"');
    echo form_input('prod', $prod, 'text', 60, 'maxlength="255"', TRUE);
    echo "</fieldset>\n";
    echo "     <fieldset id=\"submitfs\">";
    echo form_input($query['view'], htmlentities($LOCALE[$query['view']]),
                        'submit');
    echo "</fieldset>\n";
    echo "   </fieldset>\n";
    echo "  </form>\n";
  }

/*
  function addInsertHistory(&$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $h = array($query['id'], $query['date'],
               $query['type'], $query['cost'], $query['prod']);
    $query['history'][] = $h;
  }

  function getInsertHistory(&$sess) {
    return isset($sess['history']) ? $sess['history'] : array();
  }

  function setInsertHistory(&$history) {
    $_SESSION['history'] = $history;
  }

  function inserthistory(&$query, &$cc) {
    $LOCALE = $GLOBALS['LOCALE'];
    echo "  <table>\n"; 
      echo "   <thead>\n".
           "    <tr>".
           "<th>".htmlentities($LOCALE['date'])."</th>".
           "<th>".htmlentities($LOCALE['cost'])."</th>".
           "<th>".htmlentities($LOCALE['type'])."</th>".
           "<th>".htmlentities($LOCALE['prod'])."</th>".
           "</tr>\n".
           "   </thead>\n".
           "   <tbody>\n";
    for ($i=count($query['history'])-1; $i>=0; $i--) {
      list($id, $date, $type, $cost, $prod) = $query['history'][$i];
      $type = $cc->getSubName($type, $query['lang']);
      echo "    <tr>";
      echo "     <td class=\"date\">$date</td>\n";
      echo "     <td class=\"cost\"".($oa ? " title=\"$oa $oc\"" : "").">".
           str_replace('.', ',', $cost)."</td>\n";
      echo "     <td class=\"type\">".htmlentities($type)."</td>\n";
      echo "     <td class=\"desc\">".
           "<a target=\"insWin\" class=\"modify\"".
           " href=\"./?view=modify&amp;id=$id&amp;type=$type".
           "&amp;cat=".substr($type, 0, 2)."\"".
           " title=\"".htmlentities($LOCALE['modify'])."\">".
           htmlentities($prod).
           "</a></td>\n";
      echo "</tr>\n";
    }
    echo "   </tbody>\n";
    echo "  </table>\n";
  }

*/

  function summary(&$e, &$cc, &$query, $level='type') {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $total = $e->getTotal($query);
    $days = (($query['to'] - $query['from'])/ 60 / 60 / 24) + 1;
    $avg = (($days > 0) ? $total/$days : $total);
    # $th_up = '<img src="/i/up.gif" width="9" height="9" alt="/\\" />';
    # $th_down = '<img src="/i/down.gif" width="9" height="9" alt="\\/" />';
    $total_perc = 0;
    echo "  <table id=\"summary\" class=\"sortable\">\n";
    echo "   <thead>\n";
    echo "    <tr>\n";
    // copy; may be modified
    $attrs = $query;
    $attrs['prev'] = $attrs['order'];
    $attrs['order'] = 'type';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"type\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['type']).
         # (($query['order'] == 'type') ? $th_up : '').
         # (($query['order'] == 'type desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    $attrs['order'] = 'cost';
    $urlattrs = attrs2url($attrs);
    echo "     <th class=\"cost\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['cost']).
         # (($query['order'] == 'cost') ? $th_up : '').
         # (($query['order'] == 'cost desc') ? $th_down : '').
         # "</a>".
         "</th>\n";
    echo "     <th class=\"perc\" colspan=\"2\">".
         # "<a href=\"?$urlattrs\">".
         htmlentities($LOCALE['perc']).
         # "</a>".
         "</th>\n";
    echo "    </tr>\n";
    echo "   </thead>\n";
    echo "   <tbody>\n";
    $attrs = $query; // new copy
    $attrs['group'] = $level;
    $prods = $e->getSummary($attrs);
    $maxw = $total;
    $w_unit = ($maxw ? $CONFIG['bar_width']/$maxw : 0);
    $h = $CONFIG['bar_height'];
    while ($row = db_fetch_row($prods)) {
      $cost = sprintf("%.2f", $row['cost']);
      if ($level == 'cat') {
        $type = $cc->getCatName($row['type'], $query['lang']);
      }
      else {
        $type = $cc->getSubName($row['type'], $query['lang']);
      }
      // new copy for each row
      $attrs = $query;
      $attrs['view'] = 'details';
      $attrs['type'] = $row['type'];
      $urlattrs = attrs2url($attrs);
      $p = ($total > 0) ? (round($row['cost']/$total, 4)) : 0;
      $total_perc += $p;
      $perc = sprintf("%.02f&nbsp;%%", 100 * $p);
      $w = ceil($cost * $w_unit);
      $alt = str_repeat('*', (100 * $p));
      if (($w/$maxw) < 0.25) {
        $src = $CONFIG['hor_25'];
      }
      elseif (($w/$maxw) < 0.50) {
        $src = $CONFIG['hor_50'];
      }
      elseif (($w/$maxw) < 0.75) {
        $src = $CONFIG['hor_75'];
      }
      else {
        $src = $CONFIG['hor_100'];
      }
      $img = "<img src=\"$src\" width=\"$w\" height=\"$h\" alt=\"$alt\" />";
      $hint = htmlentities("$LOCALE[showonly] $type");

      echo "    <tr>\n";
      echo "     <td class=\"type\">".
           "<a href=\"./?$urlattrs\" title=\"$hint\">".
           htmlentities($type)."</a></td>\n";
      echo "     <td class=\"cost\">".str_replace('.', ',', $cost)."</td>\n";
      echo "     <td class=\"perc\">".str_replace('.', ',', $perc)."</td>\n";
      echo "     <td class=\"img\">".
             "<a href=\"./?$urlattrs\" title=\"$hint\">$img</a></td>\n";
      echo "    </tr>\n";
    }
    db_free_result($prods);
    echo "   </tbody>\n";
    $total = sprintf("%.2f", $e->getTotal($query));
    $total_perc = sprintf("%.02f&nbsp;%%", 100 * $total_perc);
    $avgnum = str_replace('.', ',', sprintf('%.02f', $avg));
    $avgtext = sprintf($LOCALE['avg'], $avgnum);
    # echo "   <tfoot>\n";
    echo "   <tbody>\n";
    echo "    <tr class=\"total hslice sortbottom\" id=\"totaltr\">\n";
    echo "     <td class=\"total entry-title\">".htmlentities($LOCALE['total'])."</td>\n";
    echo "     <td class=\"cost entry-content\" id=\"summarytotal\" title=\"$avgtext\">".
         str_replace('.', ',', $total)."</td>\n";
    echo "     <td class=\"perc\">".str_replace('.', ',', $total_perc).
         "</td>\n";
    echo "     <td class=\"img\">&nbsp;</td>\n";
    echo "    </tr>\n";
    # echo "   </tfoot>\n";
    echo "   </tbody>\n";
    echo "  </table>\n";
  }

  function benchmarkhistory(&$e, &$b, &$cc, &$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    // copy; may be modified
    $attrs = $query;
    echo "  <table id=\"benchmarkhistory\">\n   <thead>\n";
    echo "    <tr><th class=\"year\">".
               htmlentities($LOCALE['year'])."</th>";
    $thfmt = "<th class=\"month\" id=\"bm%d\" scope=\"col\">%s</th>";
    for ($i=1; $i<=12; $i++) {
      echo sprintf($thfmt, $i, htmlentities($LOCALE['months'][$i]));
    }
    echo "<th class=\"total\" id=\"btotal\" scope=\"col\">".
       htmlentities($LOCALE['total'])."</th>";
    echo "</tr>\n   </thead>\n   <tbody>\n";

    $start_year = date('Y', $e->getFirstDate());
    $end_year = date('Y', $e->getLastDate());
    for ($y=$start_year; $y<=$end_year; $y++) {
      $attrs['from'] = mktime(0, 0, 0, 1, 1, $y);
      $attrs['to'] = mktime(0, 0, 0, 12, 31, $y);
      $urlattrs = attrs2url($attrs);
      $trow = "    <tr><th class=\"year\" id=\"by$y\" scope=\"row\">$y</th>";
      $allmissed = TRUE;
      for ($m=1; $m<=12; $m++) {
        $attrs['from'] = mktime(0, 0, 0, $m, 1, $y);
        $attrs['to'] = mktime(0, 0, 0, $m+1, 1, $y)-1;
        $urlattrs = attrs2url($attrs);
        $link = "./?$urlattrs";
        $title = htmlentities($LOCALE['showonly']." ".
                              $LOCALE['months'][$m])." $y";
        $atot = $e->getTotal($attrs);
        $battrs = $attrs;
        $battrs['prod'] = '';
        $btot = $b->getTotal($battrs);
        $diff = $atot - $btot;
        $plusminus = ($diff < 0) ? 'minus' : 'plus';
        if (($atot == 0) || ($btot == 0)) {
          if ($atot == 0) {
            $title .= " (".sprintf($LOCALE['missing'], $LOCALE['owntot']).")";
          }
          if ($btot == 0) {
            $title .= " (".sprintf($LOCALE['missing'], $LOCALE['avgtot']).")";
          }
          $plusminus = 'miss';
        }
        elseif ($allmissed && ($atot || $btot)) {
          $allmissed = FALSE;
        }
        $tmp = sprintf('%.2f', $diff);
        $trow .= "<td class=\"month $plusminus\" headers=\"by$y bm$m\">".
           "<a href=\"$link\" title=\"$title\">".
           str_replace('.', ',', $tmp)."</a>".
           "</td>";
      }
      $attrs['from'] = mktime(0, 0, 0, 1, 1, $y);
      $attrs['to'] = mktime(0, 0, 0, 1, 1, $y+1)-1;
      $urlattrs = attrs2url($attrs);
      $link = "./?$urlattrs";
      $title = htmlentities($LOCALE['total'])." $y";
      $atot = $e->getTotal($attrs);
      $battrs = $attrs;
      $battrs['prod'] = '';
      $btot = $b->getTotal($battrs);
      $diff = $atot - $btot;
      $plusminus = ($diff < 0) ? 'minus' : 'plus';
      if (($atot == 0) || ($btot == 0)) {
        if ($atot == 0) {
          $title .= " (".sprintf($LOCALE['missing'], $LOCALE['owntot']).")";
        }
        if ($btot == 0) {
          $title .= " (".sprintf($LOCALE['missing'], $LOCALE['avgtot']).")";
        }
        $plusminus = 'miss';
      }
      $tmp = sprintf('%.2f', $diff);
      $trow .= "<td class=\"year $plusminus\" headers=\"by$y btotal\">".
           "<a href=\"$link\" title=\"$title\">".
           str_replace('.', ',', $tmp)."</a></td>";
      $trow .= "</tr>\n";
      if (!$allmissed) {
        echo $trow;
      }
    }
    echo "   </tbody>\n  </table>\n";
  }

  function benchmark(&$e, &$b, &$cc, &$query, $targets=NULL) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];

    $bmconf = $b->getConfig();
    $bmname = $bmconf['title'];

    $total_perc = 0;
    // copy; may be modified
    $attrs = $query;
    $span = date('j.n.Y', $attrs['from'])." - ".date('j.n.Y', $attrs['to']);
    # $urlattrs = attrs2url($attrs);
    $total = $e->getTotal($attrs);
    $battrs = $attrs;
    $battrs['prod'] = '';
    $bmark = $b->getTotal($battrs);
    $diff = $total - $bmark;
    $plusminus = ($diff < 0) ? 'minus' : 'plus';
    if (($total == 0) || ($bmark == 0)) {
      $plusminus = 'miss';
    }
    $cattrs = $attrs;
    $cattrs['bmto'] = NULL;
    echo "  <div id=\"benchmarkimages\">\n";
    echo "   <h2>".htmlentities($bmname)."</h2>\n";
    if ($targets) {
      echo "   <form method=\"get\" action=\"./\">\n";
      echo "    <fieldset id=\"bmtarget\">\n";
      echo "     ".attrs2form($attrs, 'bmform')."\n";
      echo "     <label for=\"bmto\">".htmlentities($LOCALE['benchmark_title']).
           "</label>\n";
      echo "     <select name=\"bmto\" id=\"bmto\">\n";
      foreach ($targets as $targ) {
       echo "      <option value=\"$targ[id]\"".
            ($targ['id'] == $attrs['bmto'] ? ' selected="selected"' : '').
            ">".htmlentities($targ['config']['title'])."</option>\n";
      }
      echo "     </select>\n";
      echo "     ".form_input('', htmlentities($LOCALE['show']), 'submit')."\n";
      echo "    </fieldset>\n";
      echo "   </form>\n";
    }

    if (!$total) {
      echo "   <h3 class=\"error\">".htmlentities(ucfirst(sprintf($LOCALE['missing'], $LOCALE['owntot'])))." $span</h3>\n";
      echo "  </div>\n";
      return;
    }
    if (!$bmark) {
      echo "   <h3 class=\"error\">".htmlentities(ucfirst(sprintf($LOCALE['missing'], $LOCALE['avgtot'])))." $span</h3>\n";
      echo "  </div>\n";
      return;
    }
    echo "   <h3>$span: <span title=\"".htmlentities($LOCALE['owntot'])."\">".
         str_replace('.', ',', sprintf('%.2f', $total)).
         "</span> &minus;  <span title=\"".htmlentities($LOCALE['avgtot'])."\">".
         str_replace('.', ',', sprintf('%.2f', $bmark)).
         "</span> = <span class=\"$plusminus\">".
         str_replace('.', ',', sprintf('%.2f', $diff)).
         " &euro;</span></h3>\n";
    $cats = $cc->getCats();
    $w = 640;
    $h = 27;
    $bh = "10,2,4";
    $max = sprintf('%0.2f', (($bmark > $total) ? $bmark : $total));
    $u = (($max != 0) ? (100/$max) : 0);
    $attrs = $query;
    $attrs['view'] = 'details';
    while (list($catid, $cat) = each($cats)) {
      $attrs['type'] = $cat->id;
      $tota = $e->getTotal($attrs);
      $battrs = $attrs;
      $battrs['prod'] = '';
      $totb = $b->getTotal($battrs);
      $diff = sprintf('%0.2f', $tota - $totb);
      # $diffprc = ($totb ? (sprintf('%d', 100*($tota - $totb)/$totb)) : '0.00');
      $diffprc = ($totb ? (sprintf('%d', 100*$tota/$totb)) : '0.00');
      $plusminus = ($diff < 0) ? '009900' : '990000';
      if (($atot == 0) || ($btot == 0)) {
        $plusminus = '666666';
      }
      # $tota = str_replace('.', ',', $tota);
      # $totb = str_replace('.', ',', $totb);
      # $diff = str_replace('.', ',', $diff);
      $catn = $cc->getCatName($cat->id, $attrs['lang']);
      # $catn = iconv(iconv_get_encoding('input_encoding'), 'UTF-8', $catn);
      $color = str_replace('#', '', $cat->color);
      $sa = sprintf('%0.2f', $u * $tota);
      $sb = sprintf('%0.2f', $u * $totb);
      $tota = sprintf('%0.2f', $tota);
      $totb = sprintf('%0.2f', $totb);
      $la = urlencode($LOCALE['owntot'].": $tota ")."%E2%82%AC".
            urlencode(" ($diffprc %)");
      $lb = urlencode($LOCALE['avgtot']).":%20$totb%20%E2%82%AC";
      $lm = round($max);
      $oleg = str_pad(urlencode($LOCALE['owntot'].": $tota ")."%E2%82%AC".
                      " ($diffprc%20%25)", 45, '+', STR_PAD_RIGHT).".";
      $aleg = str_pad(urlencode($LOCALE['avgtot'].": $totb ")."%E2%82%AC", 30);
      $apad = (($tota/$max) <= 0.75) ? '-1' : 1;
      $bpad = (($totb/$max) <= 0.75) ? '-1' : 1;
      $imgsrc = "http://chart.apis.google.com/chart?chs=${w}x${h}".
                "&amp;cht=bhg".
                "&amp;chbh=$bh".
                "&amp;chco=000099,666666&amp;chts=000000,10".
                "&amp;chf=c,lg,0,$color,1,FFFFFF,0.5|bg,s,FFFFFF".
                // axis label korvattu chm:lla
                # "&amp;chxt=x,x".
                # "&amp;chxs=1,000099,9,$apad|2,666666,9,$bpad".
                # "&amp;chxr=0,0,$max|1,0,$max".
                # "&amp;chxp=0,$tota|1,$totb|2,0".
                # "&amp;chxl=0:|$la|1:|$lb|2:|$diffprc%20%25".
                // title erikseen tekstina
                # "&amp;chtt=".urlencode($catn).
                "&amp;chm=t+$la,000099,0,1,9,0|t+$lb,333333,1,1,9,0".
                # "&amp;chdl=$oleg|$aleg".
                // bugi chartsissa: chm ei toimi, jos kummassakin joukossa
                // on vain yksi datapoint
                # "&amp;chd=t:0,$sa|0,$sb".
                "&amp;chd=t:$sa|$sb".
                "";
      $urlattrs = attrs2url($attrs);
      echo "   <h4><a href=\"./?$urlattrs\">".htmlentities($catn)."</a></h4>\n".
           "   <p title=\"$tota - $totb = $diff\"><a href=\"./?$urlattrs\">".
           "<img src=\"$imgsrc\" class=\"bmark\" width=\"$w\" height=\"$h\"".
           " alt=\"$LOCALE[owntot]: $tota, $LOCALE[avgtot]: $totb\" /></a>".
           "</p>\n";
    }
    echo "  </div>\n";
  }

  function plot($e, $cc, $query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];

    $plot_w = $CONFIG['plot_w'];
    $plot_h = $CONFIG['plot_h'];
    $colors = array();
    $names = array();
    $columns = array();
    echo <<<EOH
  <div id="plot">
  </div>
  <script type="text/javascript">// <![CDATA[
   function initPlot() {
    var div = document.getElementById('plot');
    chart = new google.visualization.AnnotatedTimeLine(div);
    var data = new google.visualization.DataTable();
    data.addColumn('date', 'Date');

EOH;
    if (!$query['type']) {
      $level = 'cat';
      for ($i=0; $i<count($cc->cats); $i++) {
        $cat =& $cc->cats[$i];
        $name = $cc->getCatName($cat->id, $query['lang']);
        $columns[$cat->id] = array($i+1, $name, $cat->color);
      }
    }
    else {
      $level = 'type';
      $type = substr($query[type], 0, 2);
      $cat = $cc->findCat($type);
      $subs = $cat->getSubs();
      for ($i=0; $i<count($subs); $i++) {
        $sub =& $subs[$i];
        $name = $cc->getSubName($sub->id, $query['lang']);
        $columns[$sub->id] = array($i+1, $name);
      }
    }
    while (list($id, $values) = each($columns)) {
      # $label = $id." ".substr($values[1], 0, 4);
      $label = $values[1];
      echo "    data.addColumn('number', '$label');\n";
      if ($values[2]) {
        $colors[] = "'$values[2]'";
      }
    }
    $count = 0;
    $max = 0;
    # $i = $query['from'];
    $f = $e->getFirstDate();
    $i = mktime(0, 0, 0, date('m', $f), 1, date('Y', $f));
    $fd = date('j', $query['from']);
    $fm = date('n', $query['from'])-1;
    $fy = date('Y', $query['from']); 
    $td = date('j', $query['to']);
    $tm = date('n', $query['to'])-1;
    $ty = date('Y', $query['to']); 
    # $last = mktime(0, 0, 0, $m, $d, $y);
    $last = $e->getLastDate();
    while ($i < $last) {
      $attrs = $query; // new copy
      $attrs['from'] = $i;
      $y = date('Y', $i);
      $m = date('n', $i)-1;
      # $d = date('j', $i);
      $d = 15;
      $i = mktime(0, 0, 0, date('m', $i)+1, date('d', $i), date('Y', $i));
      $attrs['to'] = $i;
      $attrs['group'] = $level;
      $prods = $e->getSummary($attrs);
      # $tot = $e->getTotal($attrs);
      // if (db_num_rows($prods) <= 0) {
      //   continue;
      // }
      # echo "    data.addRow();\n";
      # echo "    data.setValue($count, 0, new Date($y, $m, $d));\n";
      # echo " // from ".date('Y-m-d H:i:s', $attrs['from'])." to ".
      #      date('Y-m-d H:i:s', $attrs['to'])." [".attrs2url($attrs)."]\n";
      $tmpdata = array();
      while ($row = db_fetch_row($prods)) {
        $cost = sprintf("%.2f", $row['cost']);
        $max = ($cost > $max) ? $cost : $max;
        $type = ($level == 'cat') ? substr($row['type'], 0, 2) : $row['type'];
        if (is_array($columns[$type])) {
          $cat = $columns[$type][0];
        }
        # echo "    data.setValue($count, $cat, $cost);\n";
        $tmpdata[$type] = array($count, $cat, $cost);
      }
      $values = "    data.addRow([new Date($y, $m, $d)";
      reset($columns);
      while (list($id, $data) = each($columns)) {
        $values .= sprintf(", %.2f", $tmpdata[$id][2]);
      }
      $values .= "]);\n";
      echo $values;
      $count++;
    }
    $cols = join(',', $colors);

    echo <<<EOF
    var opts = {'colors': [$cols],
                // curveType: 'function',
                'dateFormat': 'dd.MM.yyyy',
                'displayAnnotations': false,
                'displayLegendValues': false,
                'displayExactValues': true,
                'fill': 20,
                'legendPosition': 'newRow',
                'min': 0,
                'scaleType': 'maximized',
                'thickness': 3,
                // 'width': $plot_w,
                // 'height': $plot_h,
                // 'vAxis': {'maxValue': $max},
                'wmode': 'transparent',
                'zoomStartTime': new Date($fy, $fm, $fd),
                'zoomEndTime': new Date($ty, $tm, $td)
                }
    chart.draw(data, opts);
   }
//]]></script>

EOF;

  }

  function export(&$e, &$cc, &$query, $level='type') {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $sep = "\t";
    $eol = "\n";

    $month = 60*60*24*365.25/12;
    $dur = $query['to'] - $query['from'];
    $diff = abs($dur - $month);
    $factor = ($diff > 60*60*24*5) ? $dur/$month : 1;

    $query['group'] = $level;
    $query['order'] = 'type';
    $query['prev'] = 'date';
    $prods = $e->getSummary($query);
    while ($row = db_fetch_row($prods)) {
        $cost = sprintf("%.2f", $row['cost']/$factor);
        $type = ($level == 'cat') ? substr($row['type'], 0, 2) : $row['type'];
        $name = utf8_encode($cc->getSubName($type, $query['lang']));
        echo $cost.$sep.$type.$sep.$name.$eol;
    }

  }

  function export2(&$e, &$cc, &$query, $level='type') {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $sep = "\t";
    $eol = "\n";

    echo utf8_encode($LOCALE['year']).$sep;
    for ($i=1; $i<=12; $i++) {
      echo utf8_encode($LOCALE['months'][$i]).$sep;
    }
    echo utf8_encode($LOCALE['total']).$eol;

    $attrs = $query;
    $start_year = date('Y', $e->getFirstDate());
    $end_year = date('Y', $e->getLastDate());
    for ($y=$start_year; $y<=$end_year; $y++) {
      $attrs['from'] = mktime(0, 0, 0, 1, 1, $y);
      $attrs['to'] = mktime(0, 0, 0, 12, 31, $y);
      echo $y.$sep;
      for ($m=1; $m<=12; $m++) {
        $attrs['from'] = mktime(0, 0, 0, $m, 1, $y);
        $attrs['to'] = mktime(0, 0, 0, $m+1, 1, $y)-1;
        $atot = $e->getTotal($attrs);
        echo str_replace('.', ',', sprintf('%.2f', $atot)).$sep;
      }
      $attrs['from'] = mktime(0, 0, 0, 1, 1, $y);
      $attrs['to'] = mktime(0, 0, 0, 1, 1, $y+1)-1;
      $atot = $e->getTotal($attrs);
      echo str_replace('.', ',', sprintf('%.2f', $atot)).$eol;
    }
  }

  function export3(&$e, &$cc, &$query, $level='type') {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $sep = "\t";
    $eol = "\n";

    $f = $e->getFirstDate();
    # var_dump($query);
    # $f = $query['from'];
    # $i = mktime(0, 0, 0, date('m', $f), 1, date('Y', $f));
    $last = $e->getLastDate();
    # $last = $query['to'];
    while ($i < $last) {
      $attrs = $query; // new copy
      $attrs['from'] = $i;
      $y = date('Y', $i);
      $m = date('n', $i);
      $i = mktime(0, 0, 0, date('m', $i)+1, date('d', $i), date('Y', $i));
      $attrs['to'] = $i;
      $attrs['group'] = $level;
      $attrs['order'] = 'type';
      $attrs['prev'] = 'date';
      $prods = $e->getSummary($attrs);
      while ($row = db_fetch_row($prods)) {
        # var_dump($attrs);
        $cost = sprintf("%.2f", $row['cost']);
        $type = ($level == 'cat') ? substr($row['type'], 0, 2) : $row['type'];
        $name = utf8_encode($cc->getSubName($type, $query['lang']));
        echo $y.$sep.$m.$sep.$cost.$sep.$type.$sep.$name.$eol;
      }
    }
  }

  function timeline(&$e, &$cc, $query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];

    $daysecs = 60 * 60 * 24;

    if (($query['to'] - $query['from']) > ($CONFIG['maxdays'] * $daysecs)) {
      printf("<!-- ".htmlentities($LOCALE['timeline_max'])." -->\n",
             floor(($query['to'] - $query['from']) / $daysecs) + 1,
	     $CONFIG['maxdays']);

      return;
    }
    elseif ((floor($query['to'] - $query['from']) + $daysecs) <
            ($CONFIG['mindays'] * $daysecs)) {
      printf("<!-- ".htmlentities($LOCALE['timeline_min'])." -->\n",
             floor(($query['to'] - $query['from']) / $daysecs) + 1,
	     $CONFIG['mindays']);
      return;
    }

    $wcount = ($query['to'] - $query['from'])/$daysecs; 
    $w = $CONFIG['timeline_width'];

    $days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
    $sum = array();

    $attrs = $query;
    $attrs['group'] = 'date'; 
    $attrs['order'] = 'cost';
    $prods = $e->getSummary($attrs);
    $h_unit = 0;
    $maxh = 0;
    $minh = 0;
    while ($row = db_fetch_row($prods)) {
      $date = $row['date'];
      $cost = $row['cost'];
      if ($cost > $maxh) {
        $maxh = $cost;
      }
      if ($cost < $minh) {
        $minh = $cost;
      }
      $sum[$date] = $cost;
    }
    db_free_result($prods);
    $h_diff = $maxh - $minh;
    $h_unit = ($h_diff ? $CONFIG['timeline_height']/$h_diff : 0);
    if ($minh < 0) {
      $minus = abs($minh) * $h_unit;
    }
    $minus_h = 0;
    $empty_h = 0;

    echo "  <table id=\"timeline\">\n";
    echo "   <tbody>\n";
    echo "    <tr>\n";

    $i = $query['from'];
    $d = date('d', $query['to']) + 1;
    $m = date('m', $query['to']);
    $y = date('Y', $query['to']); 
    $last = mktime(0, 0, 0, $m, $d, $y);
    while ($i < $last) {
      $sdate = date('Y-m-d', $i);
      $empty_src = "../i/.gif";
      if (array_key_exists($sdate, $sum) && $h_diff) {
        $h = abs($sum[$sdate]) * $h_unit;
        $cost = sprintf('%.2f&nbsp;&euro;', $sum[$sdate]);
        if (($sum[$sdate]/$h_diff) < 0.25) {
  	  $src = $CONFIG['ver_25'];
        }
        elseif (($sum[$sdate]/$h_diff) < 0.50) {
  	  $src = $CONFIG['ver_50'];
        }
        elseif (($sum[$sdate]/$h_diff) < 0.75) {
  	 $src = $CONFIG['ver_75'];
        }
        else {
  	  $src = $CONFIG['ver_100'];
        }
        $class = "img";
      }
      elseif (array_key_exists($sdate, $sum)) {
        $class = "img";
      }
      else {
        $h = 0;
        $cost = '0&nbsp;&euro;';
        $src = "../i/.gif";
        $class = "empty";
      }

      if (($minh < 0) && ($sum[$sdate] >= 0)) {
        $minus_h = 0;
        $empty_h = $minus;
        $minus_src = "../i/.gif";
      }
      elseif ($minh < 0) {
        $h = 0;
        $minus_h = abs($sum[$sdate]) * $h_unit;
        $empty_h = $minus - $minus_h;
        $minus_src = $src;
        $src = "../i/.gif";
      }

      $date = date('d.m.y', $i);
      $alt = str_replace('.', ',', $cost);

      $attrs = $query;
      $attrs['from'] = $i;
      $attrs['to'] = $i;
      $urlattrs = attrs2url($attrs);

      $h = round($h);
      $minus_h = round($minus_h);
      $empty_h = round($empty_h);
      if ($h) {
        $img = "<img src=\"$src\" alt=\"$alt\" title=\"$date: $alt\"".
               " class=\"timeline\"".
               " width=\"$w\" height=\"$h\" />";
      }
      else {
        $img = "<img src=\"$src\" alt=\"$alt\" title=\"$date: $alt\"".
               " class=\"timeline\"".
               " width=\"$w\" height=\"1\" />";
      }  
      if ($minus_h) {
        $minus_img = "<img src=\"$minus_src\" alt=\"$alt\"".
               " title=\"$date: $alt\" class=\"timeline\"".
               " width=\"$w\" height=\"$minus_h\" />";
      }
      if ($empty_h) {
        $empty_img = "<img src=\"$empty_src\" alt=\" \"".
               " class=\"timeline\"".
               " width=\"$w\" height=\"$empty_h\" />";
      }
      $wd = date('w', $i);
      $hint = htmlentities("$LOCALE[showonly] ".$LOCALE[$days[$wd]]." $date");
      echo "    <td class=\"img\" title=\"$date: $alt\">".
             "<a href=\"./?$urlattrs\" title=\"$hint\">".
             $img.
             "<small class=\"$class\"".
             " title=\"".htmlentities($LOCALE[$days[$wd]])." $date\"".
             ">".
             strtoupper($LOCALE[$days[$wd]][0]).
             "</small>".
             ($minus_h ? $minus_img : '').
             ($empty_h ? $empty_img : '').
             "</a></td>\n";
      $i = mktime(0, 0, 0, date('m', $i), date('d', $i) + 1, date('Y', $i));
    }
    echo "    </tr>\n";
    echo "   </tbody>\n";
    echo "  </table>\n"; 
  }

  function links(&$query) {
    $LOCALE = $GLOBALS['LOCALE'];
    $CONFIG = $GLOBALS['CONFIG'];
    $attrs = $query;
    $attrs['lang'] = $CONFIG['lang'];
    $attrs['previous_view'] = $attrs['view'];
    # $attrs['view'] = 'settings';
    # $urlattrs_settings = attrs2url($attrs);
    # $text_settings = htmlentities($LOCALE['settings']);
    # $attrs['view'] = 'excel';
    # $urlattrs_excel = attrs2url($attrs);
    # $text_excel = htmlentities($LOCALE['excel']);
    $attrs['view'] = 'insert';
    $urlattrs_insert = attrs2url($attrs);
    $text_benchmark = htmlentities($LOCALE['benchmark']);
    $text_summary = htmlentities($LOCALE['summary']);
    $text_details = htmlentities($LOCALE['details']);
    $text_plot = htmlentities($LOCALE['plot']);
    $text_insert = htmlentities($LOCALE['insert']);
    $text_logout = htmlentities($LOCALE['logout']);
    $logoutmsg = urlencode($LOCALE['logoutmsg']);
    $returnmsg = urlencode($LOCALE['returnmsg']);
    echo <<<EOL
  <ul id="tabs">
   <li id="summarylink"><a href="#summary">$text_summary</a></li>
   <li id="detailslink"><a href="#details">$text_details</a></li>
   <li id="plotlink"><a href="#plot">$text_plot</a></li>
   <li id="benchmarkimageslink"><a href="#benchmarkimages">$text_benchmark</a></li>
  </ul>
<!--
  <ul id="links">
   <li><a target="insWin" href="./?$urlattrs_insert" class="modify">$text_insert</a></li>
   <li><a href="./logout.php?message=$logoutmsg&amp;back=$returnmsg">$text_logout</a></li>
  </ul>
-->

EOL;
  }

  function prevnextlinks($query, $e=NULL) {
    $LOCALE = $GLOBALS['LOCALE'];
    $diff = ($query['to'] - $query['from']) + 24 * 60 * 60;
    $query['from'] -= $diff;
    $query['to'] -= $diff;
    $urlattrs_prev = attrs2url($query);
    $query['from'] += 2 * $diff;
    $query['to'] += 2 * $diff;
    $urlattrs_next = attrs2url($query);
    $days = round($diff/24/60/60);

    $urlattrs_home = "init=1&amp;lang=$query[lang]";
    $hometitle = htmlentities($LOCALE['defaults']);
    if ($days != 1) {
      $prevtitle = htmlentities("$LOCALE[prev] $days $LOCALE[days]");
      $nexttitle = htmlentities("$LOCALE[next] $days $LOCALE[days]");
    }
    else {
      $prevtitle = htmlentities("$LOCALE[prev_s] $LOCALE[day]");
      $nexttitle = htmlentities("$LOCALE[next_s] $LOCALE[day]");
    }
    $links = <<<EOL

  <link rel="home" href="./?$urlattrs_home" title="$hometitle" />
  <link rel="prev" href="./?$urlattrs_prev" title="$prevtitle" />
  <link rel="next" href="./?$urlattrs_next" title="$nexttitle" />

EOL;

    if ($e) {
      $query['from'] = $e->getFirstDate();
      $query['to'] = $query['from'] + $diff;
      $urlattrs_start = attrs2url($query);
      if ($days != 1) {
        $starttitle = htmlentities("$LOCALE[first] $days $LOCALE[days]");
      }
      else {
        $starttitle = htmlentities("$LOCALE[first_s] $LOCALE[day]");
      }
      $query['to'] = $e->getLastDate();
      $query['from'] = $query['to'] - $diff;
      $urlattrs_end = attrs2url($query);
      if ($days != 1) {
         $endtitle = htmlentities("$LOCALE[last] $days $LOCALE[days]");
      }
      else {
        $endtitle = htmlentities("$LOCALE[last_s] $LOCALE[day]");
      }
#      $links .= <<<EOM
#   <link rel="first" href="./?$urlattrs_start" title="$starttitle" />
#   <link rel="last" href="./?$urlattrs_end" title="$endtitle" />
# 
# EOM;
    }

    return $links;
  }

  function showhidedivs($query) {
    $divs = array('summary' => 'summary',
                  'details' => 'details',
                  'plot' => 'plot',
                  'benchmark' => 'benchmarkimages');
    if (!isset($divs[$query['view']])) {
     return false;
    }
    $tab = $divs[$query['view']];
    $styles = '';
    foreach ($divs as $view => $div) {
     $display = ($query['view'] == $view) ? 'block' : 'none';
     $styles .= "#$div{display:$display}";
    }
    if ($query['view'] == 'benchmark') {
     $styles .= "#history{display:none}#benchmarkhistory{display:block}";
    }
    else {
     $styles .= "#history{display:block}#benchmarkhistory{display:none}";
    }
    return <<<EOS
  <script type="text/javascript">
   var ss = document.getElementById('screenstyle');
   var st = document.createElement('style');
   st.type = 'text/css';
   st.id = 'dynamicstyle';
   if (st.styleSheet) {
    st.styleSheet.cssText = '$styles';
   }
   else {
    st.innerHTML = '$styles';
   }
   ss.parentNode.appendChild(st);
   document.cookie = 'tab=' + escape('$tab') + ';path=/'; 
  </script>

EOS;
  }

  function attrs2url($array) {
    $url = '';
    $count = 0;
    $array['from'] = isset($array['from']) ? date('d.m.Y', $array['from']) : NULL;
    $array['to'] = isset($array['to']) ? date('d.m.Y', $array['to']) : NULL;
    if (isset($array['type'])) {
      $array['cat'] = (isset($array['cat']) ? $array['cat'] :
                       substr($array['type'], 0, 2));
    }
    while (list($key, $value) = each($array)) {
      ++$count;
      $url .= urlencode($key)."=".urlencode($value);
      if ($count < count($array)) {
        $url .= "&amp;";
      }
    }
    return $url;
  }

  function attrs2form($array, $form=NULL) {
    $inputs = '';
    $count = 0;
    $array['from'] = isset($array['from']) ? date('d.m.Y', $array['from']) : NULL;
    $array['to'] = isset($array['to']) ? date('d.m.Y', $array['to']) : NULL;
    if (isset($array['type'])) {
      $array['cat'] = (isset($array['cat']) ? $array['cat'] :
                       substr($array['type'], 0, 2));
    }
    while (list($key, $value) = each($array)) {
      $inputs .= form_input($key, $value, 'hidden', NULL, NULL, NULL, $form);
    }
    return $inputs;
  }

  function sheet($cc, $lang) {
    $lang = fi;
    for ($i=0; $i<count($cc->cats); $i++) {
      $cat =& $cc->cats[$i];
      $label = (($lang == 'fi') ? $cat->nameFi : $cat->nameEn);
      echo '<h2 style="margin: 0px; font-size: 1em;">'.$cat->id." ".
           htmlentities($label)."</h2>\n";    
      $subs = $cat->getSubs();
      for ($j=0; $j<count($subs); $j++) {
        $sub =& $subs[$j];
        $text = (($lang == 'fi') ? $sub->nameFi : $sub->nameEn);
        echo '<p style="margin: 0px; font-size: 80%;">'.$sub->id." ".
             htmlentities($text)."</p>\n";
      }
    }
  }

  function printErrors($errors) {
    if (count($errors) > 0) {
      for ($i=0; $i<count($errors); $i++) {
        list($str, $class, $file, $line) = $errors[$i];
        echo "  <div class=\"$class\">".htmlentities($str)."</div>\n";
        echo "<!-- $file, $line -->\n";
      }
    }
  }

  function svg_support() {

    // default view options
    $object = false;
    $embed = false;
    $inline = false;
    $image = false;

    if (preg_match("/image\/svg/", $_SERVER['HTTP_ACCEPT'])) {
      $object = false; // could also be true?
      $inline = true;
    }
    elseif (preg_match('/Lynx/', $_SERVER['HTTP_USER_AGENT'])) {
    }
    elseif (preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT'])) {
      $object = true;
    }
    elseif (preg_match('/Chrome/', $_SERVER['HTTP_USER_AGENT'])) {
      $inline = true;
    }
    elseif (preg_match('/Safari/', $_SERVER['HTTP_USER_AGENT'])) {
      $object = true;
    }
    elseif (preg_match('#Firefox/1.5#', $_SERVER['HTTP_USER_AGENT'])) {
      $object = true;
      $inline = false; // text rendering fails!
    }
    elseif (preg_match('/Gecko/', $_SERVER['HTTP_USER_AGENT'])) {
      $object = true;
      $inline = false; // graph rendering fails!
    }
    elseif (preg_match('/Mac/', $_SERVER['HTTP_USER_AGENT'])) {
      $object = true;
    }
    $retvalues = array($object, $embed, $inline, $image);
    return $retvalues;
  }

?>
