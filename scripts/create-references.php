<?php

 ini_set('include_path', '/www/seuranta/kulutus/include');
 $DEBUG = 0;
 error_reporting(E_ALL);

 require_once('Expense.php');
 require_once('Coicop.php');

 $DB_CONFIG['db_debug'] = $DEBUG;

 $cc = new Coicop();

 $lastvaluesurl = 'http://pxnet2.stat.fi/PXWeb/sq/0a9fc3a1-aa9b-4876-82fb-8a82842f48ff';
 $index2000url = 'http://pxnet2.stat.fi/PXWeb/sq/f49ae819-1da8-440d-a5cf-1d55f53c8167';
 $index2005url = 'http://pxnet2.stat.fi/PXWeb/sq/9c703425-c001-4f35-b1c0-9790dddba899';
 $index2010url = 'http://pxnet2.stat.fi/PXWeb/sq/7db59173-cb74-4b1b-8830-db4ff0b0a849';

 $indexes = array();
 $remember = array();
 getIndexes(2001, $index2000url);
 getIndexes(2005, $index2005url);
 getIndexes(2010, $index2010url);

 var_dump($indexes["2014-01-09.4"]);
 print_r($indexes);
 exit();
 
 getBase($lastvaluesurl);
 getBudgets();

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
    $ehandles[$t]->addBenchmarkTarget($t, $datasource_name, $lastvaluesurl);
   }
   else {
    trigger_error("addUser failed ($u, $p, $t)", E_USER_ERROR);
   }
  }

  $years = array(2001, 2006, 2012);
  // $response = getpx($params);
  $response = file_get_contents($lastvaluesurl);
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
   else {
    trigger_error("Row doesn't match regex: $csv[0]", E_USER_WARNING);
   }
   if (!isset($cat)) {
    continue;
   }
   $key = 0;
   foreach ($types as $j => $t) {
    foreach ($years as $i => $y) {
     $key++;
     $value = $csv[$key];
     if (!is_numeric($value)) {
      trigger_error("No numeric value for year $y, type $t, key $key ($value)\n", E_USER_WARNING);
      continue;
     }
     # $base[$t][$y]["$cat.$sub"] = $value;
     # echo "$t $y $cat.$sub: $value ($desc)\n";
     insertIndexed($ehandles[$t], "$cat.$sub", $desc, $value/12, $y);
    }
   }
   # var_dump($csv);
  }
 }

 function getBudgets() {
  global $indexes, $DB_CONFIG, $DEBUG;
  $file1 = dirname(dirname(__FILE__)).'/include/minimibudjetit2010.txt';
  $file2 = dirname(dirname(__FILE__)).'/include/minimibudjetit2013.txt';

  $datasource_name = 'Kuluttajatutkimuskeskuksen minimibudjetit';
  $url = 'http://www.kuluttajatutkimuskeskus.fi/files/5842/Tutkimuksia_ja_selvityksia_3_2014_Viitebudjettien_paivitys_Lehtinen_Aalto_korj_04082014_v2.pdf';

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
                    'asuva lapseton pariskunta',
                    'asuva 10 ja 14 v lasten yksinhuoltajaperhe',
                    'asuva 4 ja 10 v lasten perhe',
                    'asuva 14 ja 16 v lasten perhe',
                    'asuva 10, 15 ja 17 v lasten perhe');

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
     $ehandle->addBenchmarkTarget($t, $datasource_name, $url);
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
     if (($k == 5) || ($k == 7) || ($k == 8)) {
      // lisatty vasta 2013
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
    foreach ($statuses as $s) {
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
  global $indexes, $DEBUG;
  $itype = $type;
  if ($type == "10.13") {
   // yliopistokoulutukselle ei ole indeksiarvoa,
   // kaytetaan tasoltaan erittelematonta
   $itype = "10.5";
  }
  elseif (($type == "12.7") || ($type == "12.9")) {
   // erittelemattomille ja ulkopuolisille ei ole indeksiarvoa
   $itype = "12.7";
  }
  if ($y == 2001) {
   // kulutustutkimus 2001
   if (isset($indexes["2001-$itype"])) {
    $baseindex = $indexes["2001-$itype"];
   }
   else {
    trigger_error("no base index for key 2001-$itype", E_USER_WARNING);
    # $baseindex = $indexes['2001'];
    $baseindex = 100;
   }
   $ystart = 2000;
   $yend = 2005;
  }
  elseif ($y == 2006) {
   // kulutustutkimus 2006
   $baseindexkey = "2006-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    trigger_error("no base index for key $baseindexkey", E_USER_WARNING);
    $baseindex = 100;
    # $baseindex = $indexes['2006'];
   }
   $ystart = 2006;
   $yend = 2011;
  }
  elseif ($y == 2012) {
   // kulutustutkimus 2012
   $baseindexkey = "2012-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    trigger_error("no base index for key $baseindexkey", E_USER_WARNING);
    $baseindex = 100;
    # $baseindex = $indexes['2012'];
   }
   $ystart = 2012;
   $yend = date('Y');
  }
  elseif ($y == 2009) {
   // kohtuullisen kulutuksen minimibudjetit
   $baseindexkey = "2009-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    trigger_error("no base index for key $baseindexkey", E_USER_WARNING);
    # echo "no base index for key $baseindexkey\n";
    $baseindex = 100;
   }
   $ystart = 2005;
   $yend = 2012;
  }
  elseif ($y == 2013) {
   // kohtuullisen kulutuksen paivitetyt minimibudjetit
   $baseindexkey = "2013-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    trigger_error("no base index for key $baseindexkey", E_USER_WARNING);
    # echo "no base index for key $baseindexkey\n";
    $baseindex = 100;
   }
   $ystart = 2013;
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
    $key1 = sprintf('%d-%02d-%s', $iy, $im, $itype);
    $key2 = sprintf('%d-%02d', $iy, $im);
    $key3 = sprintf('%d-%s', $iy, $itype);
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
     # echo "no current index for keys '$key1', '$key2' and '$key3'\n";
     $currentindex = 100;
    }
    if ($baseindex && $currentindex) {
     $indexed = ($value)/$baseindex*$currentindex;
     if ($DEBUG > 1) {
      echo "[".$handle->user."] $iy $im $type: $value => $indexed ($baseindex, $currentindex)\n";
     }
     # $fmt = '%s (%.2f / %.2f * %.2f)';
     # $prodstr = sprintf($fmt, $desc, ($value), $baseindex, $currentindex);
     # $prodstr = $desc." (".printnr($value)." / ".
     #            printnr($baseindex)." * ".printnr($currentindex).")";
     $prodstr = $desc;
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

 function printnr($nr) {
  return str_replace('.', ',', sprintf('%.2f', $nr));
 }

 function getIndexes($year, $url) {
  global $cc, $indexes, $remember;

  $response = file_get_contents($url);
  $rows = explode("\n", $response);

  // Kuluttajahintaindeksi 2000=100 on eri muodossa
  if ($year == 2001) {
   // datassa kategorioita ei ole numeerisina - numeroidaan jarjestyksen mukaan
   $cats = array(
    '01.1', '01.2',
    '02.1', '02.2',
    '03.1', '03.2',
   );
   # $cat_index = 0;
   $tempindex = array();
   foreach ($rows as $row) {
    $csv = str_getcsv($row, "\t");
    if (is_numeric($csv[0])) {
     for ($m=1; $m<=13; $m++) {
      $tempkey = ($m<13) ? sprintf("%04d-%02d-%s", $csv[0], $m, $csv[1]) : "$csv[0]-$csv[1]";
      $tempindex[$tempkey] = $csv[$m+2];
      # echo "tempindex[$tempkey] = ".$csv[$m+2]."\n";
     }
    }
   }
   # print_r($tempindex);
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
     if (preg_match('/^(\d{2})\.(\d+)\s+(.*?)$/', $csv[0], $match)) {
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
     $y = $year;
     $m = 1;
     for ($i=2; $i<count($csv); $i++) {
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
   return;
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
   return $row['user_id'];
  }
  else {
   trigger_error("No user found with $select", E_USER_WARNING);
  }
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
  $config = array('user_id' => $i,
                  'title' => $desc,
                  'lang' => $lang,
                  'product_table' => "expense2_user$i");
  if (db_insert($config_table, $config)) {
   ++$ok;
  }
  if ($ok == 3) {
   return($i);
  }
  return FALSE;
 }

?>
