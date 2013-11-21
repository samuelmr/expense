<?php

  # ini_set('include_path', '.:./include:../include:../../include');
  chdir(dirname(__FILE__));
  require_once('database.php');
  require_once('Coicop.php');
  require_once('Expense.php');
  $cc = new Coicop();
  $demo_userid = 9;
  $demo = new Expense($demo_userid);
  $start_time = strtotime('2000-01-01 00:00:00');

  $typelimits = array('01.1' => array('min' => 0.5, 'max' => 3500, '*' => 50),
                      '01.2' => array('min' => 0.5, 'max' => 300, '*' => 5),
                      '02.1' => array('min' => 0, 'max' => 500, '*' => 6),
                      '02.2' => array('min' => 0, 'max' => 230, '*' => 20),
                      '03.1' => array('min' => 5, 'max' => 1000, '*' => 5),
                      '03.2' => array('min' => 0, 'max' => 200, '*' => 0.5),
                      '04.1' => array('min' => 200, 'max' => 1500, '*' => 1),
                      '04.2' => array('min' => 300, 'max' => 5500, '*' => 1),
                      '04.3' => array('min' => 10, 'max' => 20, '*' => 0.1),
                      '04.4' => array('min' => 10, 'max' => 180, '*' => 1),
                      '04.5' => array('min' => 30, 'max' => 1000, '*' => 1),
                      '05.1' => array('min' => 5, 'max' => 600, '*' => 0.3),
                      '05.2' => array('min' => 5, 'max' => 130, '*' => 0.2),
                      '05.3' => array('min' => 50, 'max' => 260, '*' => 0.1),
                      '05.4' => array('min' => 5, 'max' => 120, '*' => 2),
                      '05.5' => array('min' => 0.5, 'max' => 170, '*' => 4),
                      '05.6' => array('min' => 0.55, 'max' => 270, '*' => 20),
                      '06.1' => array('min' => 0.5, 'max' => 570, '*' => 2),
                      '06.2' => array('min' => 20, 'max' => 400, '*' => 0.5),
                      '06.3' => array('min' => 10, 'max' => 70, '*' => 1),
                      '07.1' => array('min' => 40, 'max' => 2300, '*' => 2),
                      '07.2' => array('min' => 15, 'max' => 1800, '*' => 12),
                      '07.3' => array('min' => 2, 'max' => 670, '*' => 4),
                      '08.1' => array('min' => 0.5, 'max' => 30, '*' => 20),
                      '08.2' => array('min' => 25, 'max' => 80, '*' => 0.5),
                      '08.3' => array('min' => 5, 'max' => 750, '*' => 1),
                      '09.1' => array('min' => 15, 'max' => 610, '*' => 0.3),
                      '09.2' => array('min' => 5, 'max' => 240, '*' => 1),
                      '09.3' => array('min' => 5, 'max' => 600, '*' => 5),
                      '09.4' => array('min' => 5, 'max' => 900, '*' => 2),
                      '09.5' => array('min' => 5, 'max' => 580, '*' => 2),
                      '09.6' => array('min' => 50, 'max' => 450, '*' => 0.5),
                      '10.1' => array('min' => 0, 'max' => 10, '*' => 0.08),
                      '10.2' => array('min' => 0, 'max' => 10, '*' => 0.08),
                      '10.3' => array('min' => 0, 'max' => 10, '*' => 0.08),
                      '10.4' => array('min' => 0, 'max' => 7, '*' => 0.08),
                      '10.5' => array('min' => 0, 'max' => 45, '*' => 0.08),
                      '11.1' => array('min' => 3, 'max' => 1100, '*' => 5),
                      '11.2' => array('min' => 20, 'max' => 160, '*' => 1),
                      '12.1' => array('min' => 0.5, 'max' => 570, '*' => 50),
                      '12.3' => array('min' => 0.5, 'max' => 120, '*' => 20),
                      '12.4' => array('min' => 0.5, 'max' => 220, '*' => 1),
                      '12.5' => array('min' => 15, 'max' => 660, '*' => 1),
                      '12.6' => array('min' => 0.5, 'max' => 10, '*' => 1),
                      '12.7' => array('min' => 0.5, 'max' => 50, '*' => 1),
                      '12.9' => array('min' => 0.5, 'max' => 1200, '*' => 2));

  $totals = array();
  $stat_userid = 121;
  $stat = new Expense($stat_userid);
  $statquery = Array();
  $statquery['from'] = $start_time;
  $statquery['to'] = time();
  $years = ($statquery['to'] - $statquery['from']) / 365 / 24 / 60 / 60;
  for ($i=0; $i<count($cc->cats); $i++) {
    $cat =& $cc->cats[$i];
    $statquery['type'] = $cat->id;
    $totals[$cat->id] = $stat->getTotal($statquery) / $years;
    # $subs =& $cat->getSubs();
    # for ($j=0; $j<count($subs); $j++) {
    #   $sub =& $subs[$j];
    # }
  }
                      
  $demo->cleanAllProducts();
  $lipsum = file('../include/lipsum.txt');

  $query = Array();

  for ($i=0; $i<count($cc->cats); $i++) {
    $cat =& $cc->cats[$i];
    $statquery['type'] = $cat->id;
    $subs =& $cat->getSubs();
    for ($j=0; $j<count($subs); $j++) {
      $sub =& $subs[$j];
      $limits = $typelimits[$sub->id];
      if (!$limits['max']) {
        trigger_error("No max limit set for ".$sub->id."\n", E_USER_WARNING);
        continue;
      }
      $prodcost = $limits['max'] / $limits['*'];
      # trigger_error($cc->getSubName($sub->id, 'fi'), E_USER_NOTICE);
      for ($y=date('Y', $start_time); $y<=date('Y'); $y++) {
        $total = 0;
        $statquery['from'] = mktime(0, 0, 0, 1, 1, $y);
        $statquery['to'] = mktime(0, 0, -1, 1, 1, $y+1);
        $index = $stat->getTotal($statquery)/$totals[$cat->id];
        $cut = rand(65, 100)/100;
        $top = $cut * $index * $limits['max'];
        if ($y == date('Y')) {
          # $total += (12 - date('m'))/12 * $limits['max'];
          $top = (date('m')+1)/12 * $top;
        }
        # trigger_error("$y index: $index, top: $top", E_USER_NOTICE);
        while ($total <= $top) {
          # for ($m=1; $m<=12; $m++) {
          $m = rand(1, 12);
            $cost = rand(($limits['min'] * 100), ($prodcost * 100))/100;
            if ($cost >= ($prodcost * 0.4)) {
             continue;
            }
            $from = mktime(0, 0, 0, $m, 1, $y);
            $to = mktime(0, 0, 0, $m+1, 1, $y)-1;
            $time = rand($from, $to);
            if ($time > time()) {
              continue;
            }
            $date = date('d.m.Y', $time);
            $total += $cost;
            $type = $sub->id;
            $words = rand(2, 6);
            $prod = '';
            for ($n=0; $n<$words; $n++) {
              $prod .= trim($lipsum[rand(0, count($lipsum) - 1)]).
                       (($n < $words) ? " " : "");
            }
            $values = array('date' => $date,
                            'cost' => $cost,
                            'type' => $type,
                            'prod' => $prod);
            if ($demo->addProduct($values)) {
              # $template = "%s %4s %' 6.2f %s\n";
              # printf($template, $date, $type, $cost, $prod);
            }
          # }
        }
      }
    }
  }
?>
