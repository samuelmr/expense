<?php

 $DEBUG = 0;
 error_reporting(E_ALL);
 chdir(dirname(__FILE__));
 ini_set('include_path', '../include');

 require_once('Expense.php');
 require_once('Coicop.php');

 $DB_CONFIG['db_debug'] = $DEBUG;

 $cc = new Coicop();

 $baseparams = 'Valdavarden1=45&Valdavarden2=1&Valdavarden3=2&var1=Kulutusmenot+kotitaloutta+kohti&var2=Hinta&var3=Vuosi&values1=3&values1=263&values1=285&values1=300&values1=311&values1=342&values1=352&values1=360&values1=387&values1=391&values1=403&values1=422&values1=443&values1=451&values1=484&values1=501&values1=513&values1=540&values1=554&values1=571&values1=576&values1=589&values1=611&values1=629&values1=631&values1=635&values1=641&values1=674&values1=687&values1=719&values1=750&values1=770&values1=777&values1=779&values1=781&values1=784&values1=802&values1=806&values1=834&values1=850&values1=860&values1=871&values1=874&values1=880&values1=884&values2=1&values3=5&values3=6&context1=&context2=&context3=&Valdavarden4=7&var4=Kotitaloustyyppi&values4=1&values4=2&values4=3&values4=4&values4=5&values4=6&values4=7&context4=&matrix=120_ktutk_tau_102_fi&root=..%2FDatabase%2FStatFin%2Ftul%2Fktutk%2F&classdir=..%2FDatabase%2FStatFin%2F&classdir2=&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D120_ktutk_tau_102_fi%26ti%3DKotitalouksien%2Bkulutusmenot%2Bkotitaloutta%2Bkohti%2Bkotitaloustyypin%2Bmukaan%2B1985%252D2006%26path%3D%252E%252E%252FDatabase%252FStatFin%252Ftul%252Fktutk%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kotitalouksien+kulutusmenot+kotitaloutta+kohti+kotitaloustyypin+mukaan+1985-2006&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=&hasAggregno=1&description=Kotitalouksien+kulutusmenot+kotitaloutta+kohti+kotitaloustyypin+mukaan+1985-2006&descriptiondefault=0&stubceller=45&headceller=14&pxkonv=prnmt&sel=+++Jatka+++';

 $indexes = array();
 $remember = array();
 getIndexes(2001);
 getIndexes(2005);
 getIndexes(2010);
 # var_dump($indexes);

 getBase($baseparams);
 getBudgets();

 function getpx($params) {
  global $DEBUG;
  $url = 'http://193.166.171.75/Dialog/Saveshow.asp';
  if ($DEBUG) {
   trigger_error("$url?$params", E_USER_NOTICE);
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  if ($DEBUG) {
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

 function getBase($params) {
  global $indexes, $DB_CONFIG, $DEBUG;
  $types = array("Kaikki kotitaloudet",
                 "Yhden hengen talous, alle 65 v",
                 "Lapseton pari, alle 65 v",
                 "Yksinhuoltajatalous",
                 "Kahden huoltajan lapsiperhe",
                 "Vanhustalous",
                 "Muut kotitaloudet");
  $ehandles = array();
  foreach ($types as $i => $t) {
   $u = "bm$i";
   $p = $u;
   if ($uid = addUser($u, $p, $t)) {
    $ehandles[$t] = new Expense($uid);
   }
   else {
    trigger_error("addUser failed ($u, $p, $t)", E_USER_ERROR);
   }
  }

  foreach ($ehandles as $ehandle) {
   $ehandle->cleanAllProducts();
  }

  $years = array(2001, 2006);
  $response = getpx($params);
  if ($DEBUG) {
   echo "\n$response\n";
  }
  # var_dump($response);
  $rows = explode("\n", $response);
  $base = array();
  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   if (count($csv) < 2) {
    continue;
   }
   elseif (preg_match('/^A(\d{2})(\d+)\s+(.*?)$/', $csv[1], $match)) {
    $cat = $match[1];
    $sub = $match[2];
    $desc = $match[3];
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
   if (!$cat) {
    continue;
   }
   $key = 1;
   foreach ($years as $i => $y) {
    foreach ($types as $j => $t) {
     $key++;
     $value = $csv[$key];
     if (!is_numeric($value)) {
      continue;
     }
     insertIndexed(&$ehandles[$t], "$cat.$sub", $desc, $value/12, $y);
    }
   }
  }
 }

 function getBudgets() {
  global $indexes, $DB_CONFIG, $DEBUG;
  $file = dirname(dirname(__FILE__)).'/include/minimibudjetit.txt';
  $areas = array('Helsingiss'.chr(228),
                 'P'.chr(228).chr(228).'kaupunkiseudulla',
                 'yli 100 000 asukkaan kaupungissa',
                 '60 000 - 99 999 asukkaan kaupungissa',
                 '20 000 - 59 999 asukkaan kaupungissa',
                 'alle 20 000 asukkaan kaupungissa');
  $statuses = array('yksin asuva alle 45-vuotias nainen',
                    'yksin asuva alle 45-vuotias mies',
                    'yksin asuva yli 65-vuotias nainen',
                    'yksin asuva yli 65-vuotias mies',
                    'asuva lapseton pariskunta',
                    'asuva lapsiperhe');
                     
  $rows = file($file);
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
    }
    else {
     trigger_error("addUser failed ($u, $p, $t)", E_USER_ERROR);
    }
/*
    $uid = 153 + $key;
    $ehandle = new Expense($uid);
*/

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
    foreach ($statuses as $s) {
     $t = "$a $s - minimibudjetti";
     $key++;
     $value = str_replace(',', '.', $csv[$key]);
     # echo "$type, $desc, $value, $y\n";
     if (!is_numeric($value) || ($value == 0)) {
      continue;
     }
     insertIndexed(&$ehandles[$t], $type, $desc, $value, $y);
    }
   }
   # var_dump($csv);
  }
 }

 function insertIndexed($handle, $type, $desc, $value, $y) {
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
   $baseindex = $indexes['2001'];
   $ystart = 2000;
   $yend = 2004;
  }
  elseif ($y == 2006) {
   $baseindexkey = "2006-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    # echo "no base index for key $baseindexkey\n";
    $baseindex = 100;
   }
   $ystart = 2005;
   $yend = date('Y');
  }
  elseif ($y == 2009) {
   $baseindexkey = "2009-$itype";
   if (isset($indexes[$baseindexkey])) {
    $baseindex = $indexes[$baseindexkey];
   }
   else {
    # echo "no base index for key $baseindexkey\n";
    $baseindex = 100;
   }
   $ystart = 2005;
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
     if ($DEBUG) {
      echo "$iy $im $type: $indexed ($baseindex, $currentindex)";
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
                     'currency' => sprintf('I%02d', ($y-2000)),
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

 function getIndexes($year) {
  global $indexes, $remember;
  $indexparams = array('2001' => 'Valdavarden1=6&Valdavarden2=13&Valdavarden3=1&var1=Vuosi&var2=Kuukausi&var3=Tiedot&values1=1&values1=2&values1=3&values1=4&values1=5&values1=6&values2=1&values2=2&values2=3&values2=4&values2=5&values2=6&values2=7&values2=8&values2=9&values2=10&values2=11&values2=12&values2=13&values3=1&context1=&context2=&context3=&matrix=020_khi_tau_102_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&classdir2=&noofvar=3&elim=NNN&numberstub=2&lang=3&varparm=ma%3D020_khi_tau_102_fi%26ti%3DKuluttajahintaindeksi%2B2000%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2000%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=Vuosi&hasAggregno=1&description=Kuluttajahintaindeksi+2000%3D100&descriptiondefault=1&stubceller=78&headceller=1&pxkonv=prnmt&sel=+++Jatka+++',
                       '2005' => 'Valdavarden1=43&Valdavarden2=1&Valdavarden3=6&var1=Hy%F6dykeryhm%E4&var2=Tiedot&var3=Vuosi&values1=3&values1=269&values1=295&values1=325&values1=337&values1=411&values1=430&values1=440&values1=465&values1=480&values1=500&values1=514&values1=554&values1=569&values1=599&values1=620&values1=636&values1=663&values1=693&values1=713&values1=719&values1=733&values1=780&values1=809&values1=817&values1=822&values1=829&values1=885&values1=899&values1=954&values1=997&values1=1034&values1=1049&values1=1050&values1=1055&values1=1061&values1=1109&values1=1120&values1=1168&values1=1189&values1=1197&values1=1211&values1=1217&values2=1&values3=1&values3=2&values3=3&values3=4&values3=5&values3=6&context1=&context2=&context3=&Valdavarden4=13&var4=Kuukausi&values4=1&values4=2&values4=3&values4=4&values4=5&values4=6&values4=7&values4=8&values4=9&values4=10&values4=11&values4=12&values4=13&context4=&matrix=010_khi_tau_101_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&classdir2=&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D010_khi_tau_101_fi%26ti%3DKuluttajahintaindeksi%2B2005%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2005%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=Vuosi&hasAggregno=1&description=Kuluttajahintaindeksi+2005%3D100&descriptiondefault=1&stubceller=43&headceller=78&pxkonv=prnmt&sel=+++Jatka+++',
                       '2010' => 'Valdavarden1=42&Valdavarden2=1&Valdavarden3=4&var1=Hy%F6dykeryhm%E4&var2=Tiedot&var3=vuosi&values1=3&values1=130&values1=147&values1=167&values1=176&values1=211&values1=222&values1=229&values1=244&values1=254&values1=269&values1=280&values1=302&values1=313&values1=334&values1=348&values1=359&values1=375&values1=394&values1=408&values1=413&values1=425&values1=454&values1=473&values1=479&values1=483&values1=492&values1=525&values1=535&values1=566&values1=593&values1=617&values1=625&values1=629&values1=634&values1=657&values1=666&values1=691&values1=704&values1=710&values1=720&values1=724&values2=1&values3=1&values3=2&values3=3&values3=4&context1=&context2=&context3=&Valdavarden4=13&var4=kuukausi_j&values4=1&values4=2&values4=3&values4=4&values4=5&values4=6&values4=7&values4=8&values4=9&values4=10&values4=11&values4=12&values4=13&context4=&matrix=008_khi_tau_109_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&classdir2=&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D008_khi_tau_109_fi%26ti%3DKuluttajahintaindeksi%2B2010%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2010%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=vuosi&hasAggregno=1&description=Kuluttajahintaindeksi+2010%3D100&descriptiondefault=1&stubceller=42&headceller=52&pxkonv=prnmt&sel=+++Jatka+++');

  $params = $indexparams["$year"];
  $months = array('Vuosikeskiarvo' => '',
                  'Tammikuu' => '-01',
                  'Helmikuu' => '-02',
                  'Maaliskuu' => '-03',
                  'Huhtikuu' => '-04',
                  'Toukokuu' => '-05',
                  'Kesäkuu' => '-06',
                  'Heinäkuu' => '-07',
                  'Elokuu' => '-08',
                  'Syyskuu' => '-09',
                  'Lokakuu' => '-10',
                  'Marraskuu' => '-11',
                  'Joulukuu' => '-12');
  $response = getpx($params);
  # var_dump($response);  
  $rows = explode("\n", $response);
  foreach ($rows as $row) {
   $csv = str_getcsv($row, "\t");
   if (count($csv) == 3) {
    // Kuluttajahintaindeksi 2000 = 100
    $key = $csv[0].$months[utf8_encode($csv[1])];
    $indexes[$key] = $csv[2];
    # echo "indexes"."[$key] = $csv[2] ($key)\n";
    # echo "indexes"."[$key] = $indexes[$key]\n";
    # var_dump($csv);
   }
   elseif (count($csv) > 1) {
    # echo count($csv)."\n";
    if (preg_match('/^(\d{2})\.(\d+)\s+(.*?)$/', $csv[1], $match)) {
     $cat = $match[1];
     $sub = $match[2];
     $desc = $match[3];
     # echo "$cat . $sub : $desc\n";
    }
    $y = ($year == 2005) ? 2005 : 2010;
    $m = 1;
    for ($i=2; $i<count($csv); $i++) {
     if (!is_numeric($csv[$i])) {
      # echo "$i: $csv[$i] is not numeric ($y-$m)\n";
      break;
     }
     if ($m > 13) {
      $m = 1;
      $y++;
     }
     $key = sprintf('%d-%02d-%s', $y, $m, "$cat.$sub");
     if ($m == 13) {
      $key = sprintf('%d-%s', $y, "$cat.$sub");
      if (($year == 2005) && ($y == 2010)) {
       $remember["$cat.$sub"] = $csv[$i];
      }
     }
     if ($year == 2010) {
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
   trigger_error("$select failed");
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
