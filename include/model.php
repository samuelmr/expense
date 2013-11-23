<?php

  function attrs2db($array) {
    $conf = $GLOBALS['CONFIG'];
    if (!isset($conf['qsepr'])) {
     $conf['qsepr'] = '|';
    }
    $sql = '';
    $where = array();
    if (isset($array['id']) && $array['id']) {
      $where[] = "id = '".db_escape_string($array['id'])."'";
    }
    if (isset($array['start']) && $array['start']) {
      $where[] = "id > '".db_escape_string($array['start'])."'";
    }
    if (isset($array['type']) && (strlen($array['type']) == 4)) {
      $where[] = "type = '".db_escape_string($array['type'])."'";
    }
    elseif (isset($array['type']) && $array['type']) {
      $where[] = "type LIKE '".db_escape_string($array['type'])."%'";
    }
    if (isset($array['from']) && $array['from']) {
      $where[] = "date >= '".date('Y-m-d', $array['from'])."'";
    }
    if (isset($array['to']) && $array['to']) {
      $where[] = "date <= '".date('Y-m-d', $array['to'])."'";
    }
    if (isset($conf['force_query']) && $conf['force_query']) {
      $where[] = "(prod LIKE '%".db_escape_string($conf['force_query'])."%')";
    }
    if (isset($array['prod']) && $array['prod']) {
      $items = explode($conf['qsepr'], db_escape_string($array['prod']));
      $where[] = "(prod LIKE '%".join("%' OR prod LIKE '%", $items)."%')";
      # $where[] = "MATCH(prod) AGAINST '".db_escape_string($array['prod'])."'";
    }
    if (count($where) > 0) {
      $sql .= "\nWHERE ".join(' AND ', $where);
    }
    $array['group'] = isset($array['group']) ? $array['group'] : NULL; 
    switch ($array['group']) {
      case 'date':
        $sql .= "\nGROUP BY date";
        break;
      case 'cat':
        $sql .= "\nGROUP BY SUBSTRING_INDEX(type, '.', 1)";
        break;
      case 'type':
        $sql .= "\nGROUP BY type";
        break;
      default:
        break;
    }
    $sql .= "\n";
    $array['order'] = isset($array['order']) ? $array['order'] : NULL; 
    switch ($array['order']) {
      case 'cost':
        $sql .= "\nORDER BY cost";
        break;
      case 'cost desc':
        $sql .= "\nORDER BY cost DESC";
        break;
      case 'date':
        $sql .= "\nORDER BY date, id";
        break;
      case 'date desc':
        $sql .= "\nORDER BY date DESC, id DESC";
        break;
      case 'type':
        $sql .= "\nORDER BY type";
        break;
      case 'type desc':
        $sql .= "\nORDER BY type DESC";
        break;
      case 'prod desc':
        $sql .= "\nORDER BY prod DESC";
        break;
      default:
        $sql .= "\nORDER BY prod";
        break;
    }
    if (isset($array['max']) && ($array['max'] > 0)) {
      $sql .= "\nLIMIT 0,$array[max]";
    }
    return $sql;
  }

  function date2time($date) {
    $time = false;
    $date_re = '/(\d{1,2})\D(\d{1,2})\D(\d{2,4})/';
    if (preg_match($date_re, $date, $match)) {
      $d = sprintf('%02d', $match[1]);
      $m = sprintf('%02d', $match[2]);
      $y = $match[3];
      if ($y<98) {
        $y+= 2000;
      }
      elseif ($y<100) {
        $y+= 1900;
      }
      $time = strtotime("$y-$m-$d");
    }
    return $time;
  }

  function date2db($datestr) {
    if ($time = date2time($datestr)) {
      return date('Y-m-d', $time);
    }
    return false;
  }

?>
