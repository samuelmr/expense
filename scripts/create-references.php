<?php

 ini_set('include_path', '/www/seuranta/kulutus/include');
 require_once('json-stat.php');
 
 $DEBUG = 0;
 error_reporting(E_ALL);

 require_once('Expense.php');
 require_once('Coicop.php');

 $DB_CONFIG['db_debug'] = $DEBUG;

 $cc = new Coicop();

 $lastvaluesurl = 'http://pxnet2.stat.fi/PXWeb/sq/f9e857be-ee6f-4430-ae4e-34cb1ac93e0b';

/*
 // http://pxnet2.stat.fi/PXWeb/pxweb/fi/StatFin/StatFin__tul__ktutk/statfin_ktutk_pxt_001.px/table/tableViewLayout1/?rxid=b26cdefd-29c7-4721-b05a-fec60145ee6f
 $index2000url = 'http://pxnet2.stat.fi/PXWeb/sq/c0db653c-b549-49d8-ba9d-c79b9ae5c2aa';
 $index2005url = 'http://pxnet2.stat.fi/PXWeb/sq/772f1db4-1db4-4b9b-b0b4-d3ea792c6776';
 # $index2010url = 'http://pxnet2.stat.fi/PXWeb/sq/36cb1acc-00dc-4e43-a0b6-a420de52012d';
 $index2010url = 'http://pxnet2.stat.fi/PXWeb/sq/5627568e-eb4d-4d71-b972-9de5cadeaa6a';
 $index2015url = 'http://pxnet2.stat.fi/PXWeb/sq/222b2737-c9c2-4cba-8983-a0f508b6d0ca';

 $indexes = array();
 $remember = array();
 getIndexes(2001, $index2000url);
 getIndexes(2005, $index2005url);
 getIndexes(2010, $index2010url);
 getIndexes(2015, $index2015url);

*/

/*
 Indeksit:
 - StatFi => Hinnat ja kustannukset => Kuluttajahintaindeksi => Kuukausitiedot =>
   11xq -- Kuluttajahintaindeksit pääryhmittäin (2000=100, 2005=100, 2010=100, 2015=100), kuukausitiedot, 2000M01-2019M09
 - http://pxnet2.stat.fi/PXWeb/pxweb/fi/StatFin/StatFin__hin__khi__kk/statfin_khi_pxt_11xq.px/
 - kaikki tiedot, kuukaudet, indeksisarjat ja hyödykkeet
#  - indeksisarja 2000=100, kuukaudet 2000M01 - 2004M12 => index2000url
#  - indeksisarja 2005=100, kuukaudet 2005M01 - 2009M12 => index2005url
#  - indeksisarja 2010=100, kuukaudet 2010M01 - 2004M12 => index2010url
#  - indeksisarja 2015=100, kuukaudet 2015M01 - 2004M12 => index2015url
#  - Sarkaineroteltu (otsikollinen)
 - Taulukkonäkymä 1
 - Jatka
 - Tallenna poiminta
 - Kiinteä aloitusaika, johon lisätään päivitetyt jaksot
 - JSON-stat-tiedosto
 - http://pxnet2.stat.fi/PXWeb/sq/956384ea-8ac6-485f-9eb0-65b4da5aa2e3
*/

 $index2000Url = 'http://pxnet2.stat.fi/PXWeb/sq/956384ea-8ac6-485f-9eb0-65b4da5aa2e3';
 $index2005Url = 'http://pxnet2.stat.fi/PXWeb/sq/d2b511c2-f662-4e2b-9090-736c93960a4e';
 $index2010Url = 'http://pxnet2.stat.fi/PXWeb/sq/43f0c885-e7ce-4528-9c02-e72935e3a661';
 $index2015Url = 'http://pxnet2.stat.fi/PXWeb/sq/ae3cfa79-72a6-439a-ad25-f5acc0c85b66';
 
 $jsonstat = array();
 $jsonstat["2000"] = JSONstat($index2000Url);
 $jsonstat["2005"] = JSONstat($index2005Url);
 $jsonstat["2010"] = JSONstat($index2010Url);
 $jsonstat["2015"] = JSONstat($index2015Url);

 # echo json_encode($jsonstat);
 # exit();
 
 getBase($lastvaluesurl);
 getBudgets();

/*
function getIndexJson($url) {
  $response = http_get($url);
  $all = json_decode($response);
  $ind = array();
  $i = 0;
  foreach ($all->dataset->dimension->Indeksisarja->category->index as $iskey => $isindex) {
   $i++;
   foreach ($all->dataset->dimension->Kuukausi->category->index as $kkkey => $kkindex) {
    $i++;
    foreach ($all->dataset->dimension->Hyödyke->category->index as $hykey => $hyindex) {
     $i++;
     if (isset($all->dataset->value[$n])) {
      $value = $all->dataset->value[$n];
      if ($value !== null) {
       echo "$iskey-$kkkey-$hykey $n ($isindex, $kkindex, $hyindex) = ".$value."\n";
      }
     }
    }
   }
  }
  return $ind;
 }
*/


/*
 function getpx($params) {
  global $DEBUG;
  $url = 'http://193.166.171.75/Dialog/Saveshow.asp';
  if ($DEBUG) {
   # trigger_error("$url?$params", E_USER_NOTICE);
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  if ($DEBUG > 1) {
   trigger_error($response, E_USER_NOTICE);
  }
  return $response;
 }

 function getIndex($params) {
  global $DEBUG;
  $response = getpx($params);
  if ($DEBUG) {
   echo "\n$response\n";
  }
  $index = array();
  $rows = explode("\n", $response);
  for ($i=0; $i<count($rows); $i++) {
   $values = explode("\t", $rows[$i]);
   if ($values[0] != '"Pisteluku"') {
    continue;
   }
   $key = preg_replace('/^"(\d+)\s.*$/', '\\1', $values[1]);
   $index[$key] = sprintf('%f', trim($values[2]));
  }
  return $index;
 }
*/

 function getIndex($y, $m, $type) {
  global $jsonstat, $DEBUG;
  $varname = 'pisteluku';
  # echo "Getting index for $y $m $type\n";
  if ($type == "10.13") {
   // yliopistokoulutukselle ei ole indeksiarvoa,
   // kaytetaan tasoltaan erittelematonta
   $type = "10.5";
  }
  elseif (($type == "12.8") || ($type == "12.9")) {
   // erittelemattomille ja ulkopuolisille ei ole indeksiarvoa
   $type = "12.7";
  }
  if ($y < 2005) {
   // KHI 2000=100, vain pääryhmät saatavilla
   $is = getSeries($y);
   if (preg_match('/(05|06|08|09|10|11|12)\./', $type, $match)) {
    # echo "Converted $type to ".$match[1]."!\n";
    $type = $match[1];
   }
   $query = array('Indeksisarja' => '0_2000', 'Kuukausi' => sprintf('%dM%02d', $y, $m), 'Hyödyke' => $type, 'Tiedot' => $varname);
   return getValue($jsonstat["2000"], $query);
  }
  elseif ($y < 2010) {
   $type = str_replace('.', '', $type);
   $iy = 2005;
  }
  elseif ($y < 2015) {
   $iy = 2010;
  }
  else {
   $iy = 2015;
   if ($type == '04.2') {
    $type = '04.1'; // omistusasuminen poistettu
   }
   $type = str_replace('.', '', $type);
   $varname = 'indeksipisteluku';
  }
  $query = array('Kuukausi' => sprintf('%dM%02d', $y, $m), 'Hyödyke' => $type, 'Tiedot' => $varname);
  return getValue($jsonstat[$iy], $query);
 }

 function getYearIndex($y, $type) {
  $total = 0;
  for ($m=1; $m<=12; $m++) {
   $total += getIndex($y, $m, $type);
  }
  return $total/12;
 }

 function getBase($lastvaluesurl) {
  global $indexes, $DB_CONFIG, $DEBUG;
  $types = array("Kaikki kotitaloudet",
                 "Yhden hengen talous, alle 65 v",
                 "Lapseton pari, alle 65 v",
                 "Yksinhuoltajatalous",
                 "Kahden huoltajan lapsiperhe",
                 "Vanhustalous",
                 "Muut kotitaloudet");
  $ehandles = array();
  $datasource_name = 'Tilastokeskuksen kulutustutkimus';
  foreach ($types as $i => $t) {
   $u = "bm$i";
   $p = $u;
   if ($uid = addUser($u, $p, $t)) {
    $ehandles[$t] = new Expense($uid);
    $ehandles[$t]->addBenchmarkTarget($t, $datasource_name, $lastvaluesurl, $u);
   }
   else {
    trigger_error("addUser failed ($u, $p, $t)", E_USER_ERROR);
   }
  }

  $years = array(2001, 2006, 2012);
  // $response = getpx($params);
  $response = http_get($lastvaluesurl);
  if ($DEBUG) {
   echo "\n$response\n";
  }
  $rows = explode("\n", $response);

  if (count($rows) > 1) {
   foreach ($ehandles as $ehandle) {
    $ehandle->cleanAllProducts();
   }
  }

  $base = array();
  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   # var_dump($csv);
   if (count($csv) < 2) {
    continue;
   }
   elseif (preg_match('/^A(\d{2})(\d+)\s+(.*?)$/', $csv[0], $match)) {
    $cat = $match[1];
    $sub = $match[2];
    $desc = utf8_encode($match[3]);
    // tietoliikenne on hassusti numeroitu!
    if ($cat == 8) {
     $sub -= 10;
    }
    // koulutus on hassusti numeroitu!
    elseif (($cat == 10) && ($sub == 12)) {
     $sub = 2;
    }
    elseif (($cat == 10) && ($sub == 14)) {
     $sub = 5;
    }
    // muut on hassusti numeroitu!
    elseif (($cat == 12) && ($sub == 2)) {
     $sub = 3;
    }
    elseif (($cat == 12) && ($sub == 3)) {
     $sub = 4;
    }
    elseif (($cat == 12) && ($sub == 4)) {
     $sub = 5;
    }
    elseif (($cat == 12) && ($sub == 4)) {
     $sub = 5;
    }
    elseif (($cat == 12) && ($sub == 5)) {
     $sub = 6;
    }
    elseif (($cat == 12) && ($sub == 6)) {
     $sub = 7;
    }
    elseif (($cat == 12) && ($sub == 7)) {
     $sub = 9;
    }
   }
   elseif ($csv[0] == 'Kulutusmenot') {
    continue;
   }
   else {
    trigger_error("Row doesn't match regex: $csv[0]", E_USER_WARNING);
   }
   if (!isset($cat)) {
    continue;
   }
   $key = 1;
   foreach ($years as $y) {
    foreach ($types as $j => $t) {
     $key++;
     $value = $csv[$key];
     if ($value == '..') {
      // alle 5 havaintoa tai tieto ei saatavilla
      continue;
     }
     elseif (!is_numeric($value)) {
      trigger_error("No numeric value for year $y, type $t, key $key ($value)\n", E_USER_WARNING);
      continue;
     }
     # $base[$t][$y]["$cat.$sub"] = $value;
     # echo "$t $y $cat.$sub: $value ($desc)\n";
     insertIndexed($ehandles[$t], "$cat.$sub", $desc." [$t]", $value/12, $y);
    }
   }
   # var_dump($csv);
  }
 }

 function getBudgets() {
  global $indexes, $DB_CONFIG, $DEBUG;
  $file1 = dirname(dirname(__FILE__)).'/include/minimibudjetit2010.txt';
  $file2 = dirname(dirname(__FILE__)).'/include/minimibudjetit2013.txt';
  $file3 = dirname(dirname(__FILE__)).'/include/minimibudjetit2015.txt';
  $file4 = dirname(dirname(__FILE__)).'/include/minimibudjetit2018.txt';

  $datasource_name = 'Kuluttajatutkimuskeskuksen minimibudjetit';
  $url = 'http://hdl.handle.net/10138/152407';

  $cleared = FALSE;

  $areas = array('Helsingissä',
                 'Pääkaupunkiseudulla',
                 'yli 100 000 asukkaan kaupungissa',
                 '60 000 - 99 999 asukkaan kaupungissa',
                 '20 000 - 59 999 asukkaan kaupungissa',
                 'alle 20 000 asukkaan kaupungissa');
  $statuses = array('yksin asuva alle 45-vuotias nainen',
                    'yksin asuva alle 45-vuotias mies',
                    'yksin asuva yli 65-vuotias nainen',
                    'yksin asuva yli 65-vuotias mies',
                    'asuva lapseton pari', // 2018
                    'asuva pari, joista toinen eläkkeellä',
                    'asuva pari, molemmat eläkekellä', // 2018
                    'asuva 3 v lapsen yksinhuoltajaperhe', // 2018
                    'asuva 10 ja 14 v lasten yksinhuoltajaperhe', // 2018
                    'asuva 2 ja 6 v lasten perhe', // 2013
                    'asuva 4 ja 10 v lasten perhe',
                    'asuva 14 ja 16 v lasten perhe', // 2013
                    'asuva 10, 15 ja 17 v lasten perhe' // 2013
  );

  $rows = file($file1);
  $base = array();
  $y = 2009;

  $key = 1;
  foreach ($areas as $a) {
   foreach ($statuses as $s) {
    $u = "mb$key";
    $p = $u;
    $t = "$a $s - minimibudjetti";
    if ($uid = addUser($u, $p, $t)) {
     $ehandle = new Expense($uid);
     if (!$cleared) {
      $ehandle->clearBenchmarkTargets($datasource_name);
      $cleared = TRUE;
     }
     $ehandle->addBenchmarkTarget($t, $datasource_name, $url, $u);
    }
    else {
     trigger_error("addUser failed ($u, $p, $t)", E_USER_ERROR);
    }
    $ehandles[$t] = $ehandle;
    $ehandle->cleanAllProducts();
    $key++;
   }
  }

  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   if (count($csv) < 2) {
    continue;
   }
   $type = $csv[0];
   $desc = $csv[1];
   if (!$desc || !$type) {
    continue;
   }

   $key = 1;
   foreach ($areas as $a) {
    foreach ($statuses as $k => $s) {
     if (($k == 4) || ($k == 6) || ($k == 7) || ($k == 8) ||
         ($k == 9) || ($k == 11) || ($k ==12)) {
      // lisatty vasta 2013 tai 2018
      continue;
     }
     $t = "$a $s - minimibudjetti";
     $key++;
     $value = str_replace(',', '.', $csv[$key]);
     # echo "$type, $desc, $value, $y\n";
     if (!is_numeric($value) || ($value == 0)) {
      continue;
     }
     insertIndexed($ehandles[$t], $type, $desc, $value, $y);
    }
   }
   # var_dump($csv);
  }

  $rows = file($file2);
  $base = array();
  $y = 2013;
  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   if (count($csv) < 2) {
    continue;
   }
   $type = $csv[0];
   $desc = $csv[1];
   if (!$desc || !$type) {
    continue;
   }

   $key = 1;
   foreach ($areas as $a) {
    foreach ($statuses as $k => $s) {
     if (($k == 4) || ($k == 6) || ($k == 7) || ($k == 9)) {
      // lisatty vasta 2018
      continue;
     }
     $t = "$a $s - minimibudjetti";
     $key++;
     $value = str_replace(',', '.', $csv[$key]);
     if ($DEBUG >= 2) {
      echo "$type, $desc, $value, $y\n";
     }
     if (!is_numeric($value) || ($value == 0)) {
      continue;
     }
     insertIndexed($ehandles[$t], $type, $desc, $value, $y);
    }
   }
   # var_dump($csv);
  }

  $rows = file($file3);
  $base = array();
  $y = 2015;
  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   if (count($csv) < 2) {
    continue;
   }
   $type = $csv[0];
   $desc = $csv[1];
   if (!$desc || !$type) {
    continue;
   }

   $key = 1;
   foreach ($areas as $a) {
    foreach ($statuses as $k => $s) {
     if (($k == 4) || ($k == 6) || ($k == 7) || ($k == 8)) {
      // lisatty vasta 2018
      continue;
     }
     $t = "$a $s - minimibudjetti";
     $key++;
     $value = str_replace(',', '.', $csv[$key]);
     if ($DEBUG >= 2) {
      echo "$type, $desc, $value, $y\n";
     }
     if (!is_numeric($value) || ($value == 0)) {
      continue;
     }
     insertIndexed($ehandles[$t], $type, $desc, $value, $y);
    }
   }
   # var_dump($csv);
  }

  $rows = file($file4);
  $base = array();
  $y = 2018;
  foreach ($rows as $row) {
   # echo $row;
   $csv = str_getcsv($row, "\t");
   # print_r($csv);

   if (count($csv) < 2) {
    continue;
   }
   $type = $csv[0];
   $desc = $csv[1];
   if (!$desc || !$type) {
    continue;
   }

   $key = 1;
   foreach ($areas as $a) {
    foreach ($statuses as $k => $s) {
     $t = "$a $s - minimibudjetti";
     $key++;
     $value = str_replace(',', '.', $csv[$key]);
     if ($DEBUG >= 2) {
      echo "$type, $desc, $value, $y\n";
     }
     if (!is_numeric($value) || ($value == 0)) {
      continue;
     }
     insertIndexed($ehandles[$t], $type, $desc, $value, $y);
    }
   }
   # var_dump($csv);
  }

 }

 function insertIndexed(&$handle, $type, $desc, $value, $y) {
  global $jsonstat, $DEBUG;
  if ($y == 2001) {
   // kulutustutkimus 2001
   $baseindex = getYearIndex($y, $type);
   $ystart = 2000;
   $yend = 2005;
  }
  elseif ($y == 2006) {
   // kulutustutkimus 2006
   $baseindex = getYearIndex($y, $type);
   $ystart = 2006;
   $yend = 2011;
  }
  elseif ($y == 2012) {
   // kulutustutkimus 2012
   $baseindex = getYearIndex($y, $type);
   $ystart = 2012;
   $yend = date('Y');
  }
  elseif ($y == 2009) {
   // kohtuullisen kulutuksen minimibudjetit
   $baseindex = getYearIndex($y, $type);
   $ystart = 2005;
   $yend = 2012;
  }
  elseif ($y == 2013) {
   // kohtuullisen kulutuksen paivitetyt minimibudjetit
   $baseindex = getYearIndex($y, $type);
   $ystart = 2013;
   $yend = 2014;
  }
  elseif ($y == 2015) {
   // kohtuullisen kulutuksen 2015 tasoon paivitetyt minimibudjetit
   $baseindex = getYearIndex($y, $type);
   $ystart = 2015;
   $yend = 2016;
  }
  elseif ($y == 2018) {
   // kohtuullisen kulutuksen 2018 minimibudjetit
   $baseindex = getYearIndex($y, $type);
   $ystart = 2017;
   $yend = date('Y');
  }
  else {
   trigger_error("Unknown year $y!", E_USER_ERROR);
  }
  for ($iy = $ystart; $iy <= $yend; $iy++) {
   for ($im=1; $im<=12; $im++) {
    if (($iy == date('Y')) && ($im >= date('n'))) {
     return FALSE;
    }
    $currentindex = getIndex($iy, $im, $type);
/*
    $key1 = sprintf('%d-%02d-%s', $iy, $im, $type);
    $key2 = sprintf('%d-%02d', $iy, $im);
    $key3 = sprintf('%d-%s', $iy, $type);
    if (isset($indexes[$key1])) {
     $currentindex = $indexes[$key1];
    }
    elseif (isset($indexes[$key2])) {
     $currentindex = $indexes[$key2];
    }
    elseif (isset($indexes[$key3])) {
     $currentindex = $indexes[$key3];
    }
    else {
     echo "no current index for keys '$key1', '$key2' and '$key3'\n";
     $currentindex = 100;
    }
*/
    if ($baseindex && $currentindex) {
     $indexed = ($value)/$baseindex*$currentindex;
     if ($DEBUG > 1) {
      # echo "[".$handle->user."] $iy $im $type: $value => $indexed ($baseindex, $currentindex)\n";
     }
     # $fmt = '%s (%.2f / %.2f * %.2f)';
     # $prodstr = sprintf($fmt, $desc, ($value), $baseindex, $currentindex);
     $prodstr = $desc." (".printnr($value)." / ".
                printnr($baseindex)." * ".printnr($currentindex).")";
     # $prodstr = $desc;
     $date = date('d.m.Y', mktime(0, 0, 0, $im+1, 0, $iy));
     $values = array('date' => $date,
                     'cost' => $indexed,
                     'other' => $value,
                     'currency' => sprintf('I%02d', ($iy-2000)),
                     'type' => "$type",
                     'prod' => $prodstr);
     if ($handle->addProduct($values)) {
      # var_dump($values);
     }
    }
   }
  }
 }

 function getSeries($y) {
  if ($y < 2006) {
   return '0_2000';
  }
  if ($y < 2011) {
   return '0_2005';
  }
  if ($y < 2016) {
   return '0_2010';
  }
  return '0_2015';
 }

 function printnr($nr) {
  return str_replace('.', ',', sprintf('%.2f', $nr));
 }

 function getIndexes($year, $url) {
  global $cc, $jsonstat, $remember;

  $response = http_get($url);
  $rows = explode("\n", $response);
  # echo $url;
  # echo $response;

  // Kuluttajahintaindeksi 2000=100 on eri muodossa
  if ($year == 2001) {
   $tempindex = array();
   foreach ($rows as $row) {
    $csv = str_getcsv($row, "\t");
    # echo $row;
    # print_r($csv);
    if (is_numeric($csv[0])) {
     for ($m=1; $m<=13; $m++) {
      $tempkey = ($m<13) ? sprintf("%04d-%02d-%s", $csv[0], $m, $csv[1]) : "$csv[0]-$csv[1]";
      $tempindex[$tempkey] = $csv[$m+2];
      # echo "tempindex[$tempkey] = ".$csv[$m+2]."\n";
     }
    }
   }
   # print_r($tempindex);
   # exit();
   # return;
   for ($i=0; $i<count($cc->cats); $i++) {
    $catid = $cc->cats[$i]->id;
    # echo $catid.": ".$cc->getCatName($catid, 'fi')."\n";
    for ($j=0; $j<count($cc->cats[$i]->subs); $j++) {
     $subid = $cc->cats[$i]->subs[$j]->id;
     # echo $subid.": ".$cc->getSubName($subid, 'fi')."\n";
     if ($subid == "04.4") {
      $keyname = 'Vesi ja muut asumispalvelut';
     }
     elseif ($catid == "05") {
      $keyname = 'KALUSTEET, KOTITALOUSKONEET JA YLEINEN KODINHOITO';
     }
     elseif ($catid == "06") {
      $keyname = 'TERVEYS';
     }
     elseif ($subid == "07.1") {
      $keyname = 'Ajoneuvon hankinta';
     }
     elseif ($catid == "8") {
      $keyname = utf8_decode('VIESTINTÄ');
     }
     elseif ($catid == "9") {
      $keyname = utf8_decode('KULTTUURI JA VAPAA-AIKA');
     }
     elseif ($catid == "10") {
      $keyname = utf8_decode('KOULUTUS');
     }
     elseif ($catid == "11") {
      $keyname = utf8_decode('RAVINTOLAT JA HOTELLIT');
     }
     elseif ($catid == "12") {
      $keyname = utf8_decode('MUUT TAVARAT JA PALVELUT');
     }
     else {
      $keyname = utf8_decode($cc->getSubName($subid, 'fi'));
     }
     for ($y=2000; $y<=2004; $y++) {
      $indexes[$y] = $tempindex["$y-KULUTTAJAHINTAINDEKSI"];
      for ($m=1; $m<=13; $m++) {
       $ym = ($m < 13) ? sprintf("%04d-%02d", $y, $m) : $y;
       $indexes[$ym] = $tempindex["$ym-KULUTTAJAHINTAINDEKSI"];
       if ($i == 13) {
       }
       if (isset($tempindex["$ym-$keyname"])) {
        $indexes["$ym-$subid"] = $tempindex["$ym-$keyname"];
       }
       elseif ($i <= 13) {
        trigger_error("No temp index for $subid ($catid) $ym-$keyname", E_USER_WARNING);
       }
      }
     }
    }
   }
   # print_r($indexes);
   return;
  }
  elseif ($year == 2005) {
   foreach ($rows as $row) {
    # echo "$row\n\n";
    $csv = str_getcsv($row, "\t");
    # print_r($csv);
    if (count($csv) > 1) {
     # echo count($csv)."\n";
     if (preg_match('/^(\d{2})\.(\d+)\s+(.*?)$/', $csv[1], $match)) {
      $cat = $match[1];
      $sub = $match[2];
      $desc = utf8_encode($match[3]);
      # echo "$cat.$sub : $desc\n";
     }
     elseif ($csv[0] == '0 KOKONAISINDEKSI') {
      $cat = 0;
      $sub = 0;
      $desc = "Kokonaisindeksi";
     }
     else {
      # trigger_error("Row not matching: $row", E_USER_WARNING);
      continue;
     }
     $y = $csv[0];
     $m = 1;
     for ($i=3; $i<count($csv); $i++) {
      if (!is_numeric($csv[$i])) {
       echo "$i: $csv[$i] is not numeric ($y-$m)\n";
       break;
      }
      if ($m > 13) {
       $m = 1;
       $y++;
      }
      $key = ($cat && $sub) ? sprintf('%d-%02d-%s', $y, $m, "$cat.$sub") : "$y-$m";
      if ($m == 13) {
       $key = ($cat && $sub) ? sprintf('%d-%s', $y, "$cat.$sub") : $y;
       if (($year == 2005) && ($y == 2010)) {
        $remember["$cat.$sub"] = $csv[$i];
       }
      }
      if (($year == 2010) && isset($remember["$cat.$sub"])) {
       $indexes[$key] = $remember["$cat.$sub"]/100 * $csv[$i];
      }
      else {
       $indexes[$key] = $csv[$i];
      }
      # echo "indexes"."[$key] = $indexes[$key]\n";
      $m++;
     }
    }
   }
   # var_dump($indexes);
   # print_r($indexes);
   # exit;
   # return;
  }
  else {
   foreach ($rows as $row) {
    # echo "$row\n\n";
    $csv = str_getcsv($row, "\t");
    # print_r($csv);
    $y = $csv[0];
    if (count($csv) < 2) {
     continue;
    }
    if (preg_match('/^(\d{2})\.(\d+)\s+(.*?)$/', $csv[1], $match)) {
     $cat = $match[1];
     $sub = $match[2];
     $desc = $match[3];
     # echo "$cat.$sub : $desc\n";
    }
    elseif ($csv[1] == '0 KULUTTAJAHINTAINDEKSI') {
     $cat = 0;
     $sub = 0;
     $desc = "Kokonaisindeksi";
    }
    else {
     # echo "Row doesn't match: $csv[1]";
     continue;
    }
    // 2015 indeksin omistusasuminen on 4.6, ennen oli 4.2
    if (($cat == 4) && ($sub == 6)) {
     $sub = 2;
    }
    $key = ($cat && $sub) ? sprintf('%d-%s', $y, "$cat.$sub") : $y;
    if (is_numeric($csv[14])) {
     $indexes[$key] = $csv[14];
    }
    for ($m=1; $m<=12; $m++) {
     $key = ($cat && $sub) ? sprintf('%d-%02d-%s', $y, $m, "$cat.$sub") : "$y-$m";
     if (is_numeric($csv[$m+1])) {
      $indexes[$key] = $csv[$m+1];
     }
    }
   }
   # print_r($indexes);
   # exit;
  }
 }

 function addUser($u, $p, $desc) {
  $user_table = 'user_auth';
  $config_table = 'expense2_config';
  $lang = 'fi';
  $ok = 0;
  $select = "SELECT user_id FROM $user_table where username = '$u'";
  $res = db_query($select);
  if ($row = db_fetch_assoc($res)) {
   # var_dump($row);
   $i = $row['user_id'];
    $ok = 2;
  }
  else {
   trigger_error("No user found with $select", E_USER_WARNING);
   $select = "SHOW TABLE STATUS LIKE '$user_table'";
   $res = db_query($select);
   $row = db_fetch_assoc($res);
   $uid = $row['Auto_increment'];
   $values = array('user_id' => $uid,
                   'username' => $u,
                   'password' => sha1($p),
                   'valid_from' => date('Y-m-d H:i:s'),
                   'valid' => 'y',
                   'level' => 20);
   if ($stmt = db_insert($user_table, $values)) {
    ++$ok;
   }
   else {
    trigger_error("user insert failed", E_USER_ERROR);
   }
   $i = db_insert_id($stmt);
   $create_sql = <<<EOS
CREATE TABLE `expense`.`expense2_user$i` (
`id` bigint( 2 ) NOT NULL AUTO_INCREMENT ,
`date` date NOT NULL default '0000-00-00',
`cost` float NOT NULL default '0',
`type` varchar(4) NOT NULL default '',
`prod` varchar(255) NOT NULL default '',
`other` float default NULL ,
`currency` char(3) default NULL ,
PRIMARY KEY (`id`) ,
KEY `type` (`type`) ,
KEY `date` (`date`)
) ENGINE = MYISAM DEFAULT CHARSET = latin1;

EOS;
   if (db_query($create_sql)) {
    ++$ok;
   }
  }
  $config = array('user_id' => $i,
                  'title' => $desc,
                  'lang' => $lang,
                  'product_table' => "expense2_user$i");
  # print_r($config);
  if (db_replace($config_table, $config)) {
   ++$ok;
  }
  if ($ok == 3) {
   return($i);
  }
  return FALSE;
 }

 function http_get($url) {
  $curl_handle=curl_init();
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl_handle, CURLOPT_USERAGENT, 'kulutus.seuranta.org');
  $contents = curl_exec($curl_handle);
  curl_close($curl_handle);
  return $contents;
 }

?>
