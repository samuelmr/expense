<?php

 ini_set('include_path', '/www/seuranta/kulutus/include');
 require_once('json-stat.php');
 
 $DEBUG = 0;
 error_reporting(E_ALL);

 require_once('Expense.php');
 require_once('Coicop.php');

 $DB_CONFIG['db_debug'] = $DEBUG;

 $cc = new Coicop();

 # $lastvaluesurl = 'https://statfin.stat.fi/PXWeb/sq/8712b451-2929-4a0a-a645-5e257877c542';
 $lastvaluesurl = 'https://statfin.stat.fi:443/PxWeb/sq/dfaf9e15-70de-4216-bc58-02323e9e23e9';
 # $newestvaluesurl = 'https://pxdata.stat.fi:443/PxWeb/sq/b596762a-208b-4d44-97fb-5ac0b3ad01d0';
 $newestvaluesurl = 'https://pxdata.stat.fi:443/PxWeb/sq/8335445e-1088-4eca-87ed-59b2dd15252f';
 # $index2000Url = 'https://pxnet2.stat.fi/PXWeb/sq/956384ea-8ac6-485f-9eb0-65b4da5aa2e3';
 # $index2000Url = 'https://statfin.stat.fi/PXWeb/sq/956384ea-8ac6-485f-9eb0-65b4da5aa2e3';
 # $index2000Url = 'https://statfin.stat.fi/PxWeb/sq/a061918c-de2c-44c4-bdd4-8fb16e0a6823';
 $index2000Url = 'https://statfin.stat.fi/PxWeb/sq/6b18daba-6171-49d8-ae01-102b49e27f1c';
 $index2005Url = 'https://statfin.stat.fi/PXWeb/sq/d2b511c2-f662-4e2b-9090-736c93960a4e';
 $index2010Url = 'https://statfin.stat.fi/PXWeb/sq/43f0c885-e7ce-4528-9c02-e72935e3a661';
 $index2015Url = 'https://statfin.stat.fi/PXWeb/sq/ae3cfa79-72a6-439a-ad25-f5acc0c85b66';


 $jsonstat = array();
 $jsonstat["2000"] = JSONstat($index2000Url);
 $jsonstat["2005"] = JSONstat($index2005Url);
 $jsonstat["2010"] = JSONstat($index2010Url);
 $jsonstat["2015"] = JSONstat($index2015Url);

 # echo json_encode($jsonstat);
 # exit();
 
 $types = array("Kaikki kotitaloudet",
  "Yhden hengen talous, alle 65 v",
  "Lapseton pari, alle 65 v",
  "Yksinhuoltajatalous",
  "Kahden huoltajan lapsiperhe",
  "Vanhustalous",
  "Muut kotitaloudet"
);
$reverseTypes = array(
  "Yhden hengen talous, alle 65 v" => 1,
  "Lapseton pari, alle 65 v" => 2,
  "Yhden huoltajan taloudet" => 3,
  "Kahden huoltajan lapsiperhe" => 4,
  "Yli 64-vuotiaiden kotitaloudet" => 5,
  "Muut kotitaloudet" => 6,
  "Kaikki kotitaloudet" => 0
);

 $ehandles = initHandles($lastvaluesurl);
 getBase($lastvaluesurl);
 getNewBase($newestvaluesurl);
 getBudgets();

 function initHandles($lastvaluesurl) {
  global $types;
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
  return $ehandles;
 }

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
  $value = getValue($jsonstat[$iy], $query);
  # echo "[$y-$m: $type - $varname] => $value\n";
  return $value;
 }

 function getYearIndex($y, $type) {
  $total = 0;
  for ($m=1; $m<=12; $m++) {
   $total += getIndex($y, $m, $type);
  }
  return $total/12;
 }

 function getBase($lastvaluesurl) {
  global $ehandles, $types, $indexes, $DB_CONFIG, $DEBUG;

  $years = array(2001, 2006, 2012, 2016);
  // $response = getpx($params);
  $response = http_get($lastvaluesurl);
  // echo "$lastvaluesurl\n$response\n";
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
   # echo $row;
   $csv = str_getcsv($row, "\t");
   # var_dump($csv);
   if (count($csv) < 2) {
    continue;
   }
   elseif (preg_match('/^A(\d{2})(\d+)\s+(.*?)$/', $csv[0], $match)) {
    $cat = $match[1];
    $sub = $match[2];
    $desc = mb_convert_encoding($match[3], 'UTF-8', 'ISO-8859-1');
    # $desc = $match[3];
    // tietoliikenne on hassusti numeroitu!
    if ($cat == 8) {
     $sub -= 10;
    }
    // koulutus on hassusti numeroitu!
    elseif (($cat == 10) && ($sub == 12)) {
     $sub = 2;
    }
    elseif (($cat == 10) && ($sub == 13)) {
     $sub = 5;
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

 function getNewBase($url) {
  global $ehandles, $types, $reverseTypes, $DEBUG;

  $response = http_get($url);
  echo "$url\n$response\n";  
  if ($DEBUG) {
   echo "\n$response\n";
  }
  $rows = explode("\n", $response);
  foreach ($rows as $row) {
   # echo $row;
   $csv = str_getcsv($row, "\t");
   # var_dump($csv);
   if (count($csv) < 2) {
    continue;
   }
   if ($csv[0] == 'Vuosi') {
    continue;
   }
   if (!isset($reverseTypes[$csv[2]])) {
    echo "No type for $csv[2]!\n";
    continue;
   }
   elseif (preg_match('/^(\d{2})\.?(\d*)\s+(.*?)$/', $csv[1], $match)) {
    $cat = $match[1];
    $sub = $match[2];
    if (($cat == 2) && ($sub == 2)) {
     $sub = 1; // alkoholin valmistuspalvelut uusi alakategoria
    }
    elseif (($cat == 2) && ($sub == 3)) {
     $sub = 2; // tupakka vanhalle paikalle
    }
    elseif (($cat == 4) && ($sub == 2)) {
     $sub = 1; // laskennalliset vuokrat vuokra-asumiseen
    }
    elseif (($cat == 4) && ($sub == 6)) {
     $sub = 2; // omistusasuminen vanhalle paikalle
    }
    elseif (($cat == 6) && ($sub == 4)) {
     $sub = 2; // muut terveyspalvelut avohoitopalveluihin
    }
    elseif (($cat == 7) && ($sub == 4)) {
     $sub = 3; // tavaroiden kuljetuspalvelut kuljetuspalveluihin
    }
    elseif (($cat == 8) && ($sub == 1)) {
     $cat = "09"; // informaatio- ja viestintätekniset laitteet
     $sub = 1;
    }
    elseif (($cat == 8) && ($sub == 2)) {
     $cat = "09"; // ohjelmistot pl- pelit
     $sub = 1;
    }
    elseif (($cat == 9) && ($sub == 1)) {
     $sub = 2; // vapaa-ajan kestokulutustavarat
    }
    elseif (($cat == 9) && ($sub == 2)) {
     $sub = 3; // muut vapaa-ajan tuotteet
    }
    elseif (($cat == 9) && ($sub == 6)) {
     $sub = 4; // kulttuuri- ja vapaa-ajan palvelut yhdessä
    }
    elseif (($cat == 9) && ($sub == 7)) {
     $sub = 5; // kirjat ja lehdet
    }
    elseif (($cat == 9) && ($sub == 8)) {
     $sub = 6; // valmismatkat
    }
    elseif (($cat == 12) && ($sub == 1)) {
     $sub = 5; // vakuutukset
    }
    elseif (($cat == 12) && ($sub == 2)) {
     $sub = 6; // rahoituspalvelut
    }
    elseif (($cat == 13) && ($sub == 1)) {
     $cat = 12; // henkilökohtainen hygienia
    }
    elseif (($cat == 13) && ($sub == 2)) {
      $cat = 12; // henkilökohtaiset tavarat
      $sub = 3;
    }
    elseif (($cat == 13) && ($sub == 3)) {
     $cat = 12; // sosiaaliturva
     $sub = 4;
    }
    elseif (($cat == 13) && ($sub == 9)) {
     $cat = 12; // muut palvelut
     $sub = 7;
    }
    elseif ($cat == 99) {
     $cat = 12; // kulutusmenojen ulkopuoliset erät
     $sub = 9;
    }
    $desc = mb_convert_encoding($match[3], 'UTF-8', 'ISO-8859-1');
    $y = $csv[0];
    $t = $types[$reverseTypes[$csv[2]]];
    $value = $csv[3];
    if ($y && $t && is_numeric($value)) {
      insertIndexed($ehandles[$t], "$cat.$sub", "$desc [$t]", $value/12, $y);
    }
    else {
     # echo "Can't process row '$row': [$y, $t, $value]\n";
    }
   }
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
     if ($DEBUG > 2) {
      echo "$type, $desc, $value, $y, $a, $s\n";
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
     if ($DEBUG > 2) {
      echo "$type, $desc, $value, $y, $a, $s\n";
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
     if ($DEBUG > 2) {
      echo "$type, $desc, $value, $y, $a, $s\n";
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
   $yend = 2015;
  }
  elseif ($y == 2016) {
   // kulutustutkimus 2016
   $baseindex = getYearIndex($y, $type);
   $ystart = 2016;
   $yend = 2021;
  }
  elseif ($y == 2022) {
   // kulutustutkimus 2022
   $baseindex = getYearIndex($y, $type);
   $ystart = 2022;
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
  if ($DEBUG) {
    # echo "Using index $y from $ystart to $yend\n";
  }
  for ($iy = $ystart; $iy <= $yend; $iy++) {
   for ($im=1; $im<=12; $im++) {
    if (($iy == date('Y')) && ($im >= date('n'))) {
     return FALSE;
    }
    $currentindex = getIndex($iy, $im, $type);
    if ($baseindex && $currentindex) {
     $indexed = ($value)/$baseindex*$currentindex;
     if ($DEBUG > 1) {
      echo "[".$handle->user."] $iy $im $type: $value => $indexed ($baseindex, $currentindex)\n";
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
     # print_r($values);
     if ($handle->addProduct($values)) {
      # var_dump($values);
     }
    }
    else {
     echo "Either baseindex ($baseindex) or currentindex ($currentindex) is missing for $desc at $iy-$im\n";
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
      $keyname = mb_convert_encoding('VIESTINTÄ', 'ISO-8859-1', 'UTF-8');
     }
     elseif ($catid == "9") {
      $keyname = 'KULTTUURI JA VAPAA-AIKA';
     }
     elseif ($catid == "10") {
      $keyname = 'KOULUTUS';
     }
     elseif ($catid == "11") {
      $keyname = 'RAVINTOLAT JA HOTELLIT';
     }
     elseif ($catid == "12") {
      $keyname = 'MUUT TAVARAT JA PALVELUT';
     }
     else {
      $keyname = mb_convert_encoding($cc->getSubName($subid, 'fi'), 'ISO-8859-1', 'UTF-8');
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
                  'product_table' => "expense2_user$i",
                  'force_query' => "",
                  'read_only' => 1);
  # print_r($config);
  if (db_replace($config_table, $config)) {
   ++$ok;
  }
  else {
    echo "Couldn't update config for user $i\n";
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
