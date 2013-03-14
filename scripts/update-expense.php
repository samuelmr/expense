<?php

  session_name('TEMP_PHPSESSID');
  $DEBUG = 0;
  $cookiefile = '/www/seuranta/kulutus/tmp/curl-cookie.txt';
  ini_set('include_path', '/www/seuranta/kulutus/include');

  error_reporting(E_ALL);

  # $_SESSION['userid'] = 80;
  # require_once('config.php');
  # require_once('include_library.php');
  # require_once('form_library.php');
  require_once('Expense.php');
  require_once('Coicop.php');

  $prodstr = 'Keskimääräinen kuluttajahintaindeksillä korjattu kulutus'.
             ' tilastokeskuksen mukaan';
  $e = new Expense(80);
  $cc = new Coicop();

  $results = '';

  $date = date('d.m.Y', mktime(0, 0, 0, date('m'), 0, date('Y')));
  if ($DEBUG) {
   trigger_error($date, E_USER_NOTICE);
  }

  $index = getpx($date);
  if (!isset($index['01']) || !$index['01']) {
    trigger_error("Tilastokeskuksen tietoja ei löytynyt ($date).\n", E_USER_ERROR);
    exit(0);
  }
  
  $from = date('Y-m-d\TH:i:s', strtotime($date));
  $to = date('Y-m-d\TH:i:s', strtotime($date)+(24*60*60));
  $orig_from = '2010-01-01T00:00:00';
  $orig_to = '2010-12-31T23:59:59';
  $last_from = date('Y-m-d\TH:i:s', mktime(0, 0, 0, date('m', strtotime($date))-1, 1, date('Y', strtotime($date))));
  $last_to = date('Y-m-d\TH:i:s', mktime(0, 0, -1, date('m', strtotime($date)), 1, date('Y', strtotime($date))));
  if ($DEBUG) {
   trigger_error("Compare to: $last_from - $last_to", E_USER_NOTICE);
  }
  $lastdate = date('d.m.Y', mktime(0, 0, 0, date('m', strtotime($date)), 0, date('Y', strtotime($date))));
  $format = "%02d %-50s % 6.2f %s: % 6.2f (%s: % 6.2f) %s\n";
  for ($i=0; $i<count($cc->cats); $i++) {
    $cat =& $cc->cats[$i];
    $name = $cc->getCatName($cat->id, 'fi');
    if (!$index[$cat->id]) {
      $results .= "Ei pistelukua kategorialle $name.\n";
      continue;
    }
    $query['type'] = $cat->id;
    $query['from'] = strtotime($from);
    $query['to'] = strtotime($to);
    $currentvalue = $e->getTotal($query);
    if ($currentvalue) {
      $fmt = "Kategorian %02d %s tiedot ovat jo kannassa (%s): %6.2f.\n";
      $results .= sprintf($fmt, $cat->id, $name, $date, $currentvalue);
      continue;
    }
    $query['from'] = strtotime($orig_from);
    $query['to'] = strtotime($orig_to);
    $oldvalue = $e->getTotal($query)/12;
    if (!$oldvalue) {
      $fmt = "Kategorian %02d %s aiempia (%s) tietoja ei ole!\n";
      $results .= sprintf($fmt, $cat->id, $name, $date);
      continue;
    }
    $query['from'] = strtotime($last_from);
    $query['to'] = strtotime($last_to);
    $lastvalue = sprintf('%.2f', $e->getTotal($query));
    $newvalue = sprintf('%.2f', $oldvalue * $index[$cat->id] / 100);
    if ($lastvalue) {
      $diff = sprintf('%+.2f', (($newvalue-$lastvalue)/$lastvalue)*100).' %';
    }
    $values = array('date' => $date,
                    'cost' => $newvalue,
                    'other' => NULL,
                    'currency' => NULL,
                    'type' => $cat->id,
                    'prod' => $prodstr);
    if ($e->addProduct($values)) {
      $results .= sprintf($format, $cat->id, $name, $index[$cat->id],
                          $date, $newvalue, $lastdate, $lastvalue, $diff);
      # $results .= "$date [".$cat->id.": ".$index[$cat->id]."] $name:".
      #             " $newvalue ($lastdate: $lastvalue) $diff\n";
    }
  }
  if (!$results) {
    $results .= "Kulutusseurantatietojen päivitys ei onnistunut.\n";
    exit(-1);
  }
  echo $results;
  exit(0);

  function getpx($date) {
    global $DEBUG;
    global $cookiefile;
    # include_once('curl.php');
    # $url = 'http://pxweb2.stat.fi/Dialog/Saveshow.asp';
    $url = 'http://193.166.171.75/Dialog/Saveshow.asp';
    $time = strtotime($date);
    $year = date('Y', $time) - 2009;
    $month = date('n', $time);
    # $params = "var1=Vuosi&Valdavarden1=1&var2=Hy%F6dykeryhm%E4&Valdavarden2=12&var3=Tieto&Valdavarden3=1&values1=$year&values2=2&values2=3&values2=4&values2=5&values2=6&values2=7&values2=8&values2=9&values2=10&values2=11&values2=12&values2=13&values3=1&context1=&context2=&context3=&var4=Kuukausi&Valdavarden4=1&values4=$month&context4=&matrix=010_khi_tau_101_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&noofvar=4&elim=NNNN&numberstub=3&lang=3&varparm=ma%3D010_khi_tau_101_fi%26ti%3DKuluttajahintaindeksi%2B2005%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2005%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=&hasAggregno=1&stubceller=12&headceller=1&priceeuro=13.51&pxkonv=asp1&sel=+++Jatka+++";
    # $params = "var1=Vuosi&Valdavarden1=1&var2=Hy%F6dykeryhm%E4&Valdavarden2=12&var3=Tiedot&Valdavarden3=1&values1=$year&values2=2&values2=294&values2=336&values2=429&values2=513&values2=662&values2=718&values2=808&values2=828&values2=1049&values2=1060&values2=1119&values3=1&context1=&context2=&context3=&var4=Kuukausi&Valdavarden4=1&values4=$month&context4=&matrix=010_khi_tau_101_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&noofvar=4&elim=NNNN&numberstub=3&lang=3&varparm=ma%3D010_khi_tau_101_fi%26ti%3DKuluttajahintaindeksi%2B2005%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2005%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=&hasAggregno=1&stubceller=12&headceller=1&priceeuro=13.51&pxkonv=asp1&sel=+++Jatka+++";
    # $params = "var1=Hy%F6dykeryhm%E4&Valdavarden1=12&var2=Tiedot&Valdavarden2=1&var3=Vuosi&Valdavarden3=1&values1=2&values1=294&values1=336&values1=429&values1=513&values1=662&values1=718&values1=808&values1=828&values1=1049&values1=1060&values1=1119&values2=1&values3=$year&context1=&context2=&context3=&var4=Kuukausi&Valdavarden4=1&values4=$month&context4=&matrix=010_khi_tau_101_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D010_khi_tau_101_fi%26ti%3DKuluttajahintaindeksi%2B2005%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2005%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=Vuosi&hasAggregno=1&stubceller=12&headceller=1&priceeuro=13.51&pxkonv=prnmt&sel=+++Jatka+++";
    $params = "var1=Hy%F6dykeryhm%E4&Valdavarden1=12&var2=Tiedot&Valdavarden2=1&var3=Vuosi&Valdavarden3=1&values1=2&values1=293&values1=333&values1=423&values1=505&values1=658&values1=713&values1=804&values1=829&values1=1031&values1=1042&values1=1100&values2=1&values3=$year&context1=&context2=&context3=&var4=Kuukausi&Valdavarden4=1&values4=$month&context4=&matrix=008_khi_tau_109_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D008_khi_tau_109_fi%26ti%3DKuluttajahintaindeksi%2B2010%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2010%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=Vuosi&hasAggregno=1&stubceller=12&headceller=1&priceeuro=13.51&pxkonv=prnmt&sel=+++Jatka+++";
    $params = "Valdavarden1=12&Valdavarden2=1&Valdavarden3=1&var1=Hy%F6dykeryhm%E4&var2=Tiedot&var3=vuosi&values1=2&values1=146&values1=175&values1=221&values1=279&values1=374&values1=412&values1=472&values1=491&values1=624&values1=633&values1=665&values2=1&values3=$year&context1=&context2=&context3=&Valdavarden4=1&var4=kuukausi_j&values4=$month&context4=&matrix=008_khi_tau_109_fi&root=..%2FDatabase%2FStatFin%2Fhin%2Fkhi%2F&classdir=..%2FDatabase%2FStatFin%2F&classdir2=&noofvar=4&elim=NNNN&numberstub=2&lang=3&varparm=ma%3D008_khi_tau_109_fi%26ti%3DKuluttajahintaindeksi%2B2010%253D100%26path%3D%252E%252E%252FDatabase%252FStatFin%252Fhin%252Fkhi%252F%26xu%3D%26yp%3D%26lang%3D3&ti=Kuluttajahintaindeksi+2010%3D100&infofile=&mapname=&multilang=fi&mainlang=fi&timevalvar=vuosi&hasAggregno=1&description=Kuluttajahintaindeksi+2010%3D100&descriptiondefault=1&stubceller=12&headceller=1&pxkonv=prnmt&sel=+++Jatka+++";
    if ($DEBUG) {
     trigger_error("$url?$params", E_USER_NOTICE);
    }
    # $cc = new cURL(TRUE, $cookiefile);
    # $response = $cc->post($url, $params);

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
    $index = array();
    # $tr_re = '/<td class="stub3" >(\d+)[^>]+>\s*<td nowrap>([\d,]+)/ims';
    # $data = array();
    # while(preg_match($tr_re, $response, $data)) {
    #   $index[$data[1]] = $data[2];
    #   $response = str_replace($data[0], $empty, $response);
    # }
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

?>
