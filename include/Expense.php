<?php

require_once('database.php');
require_once('model.php');

class Expense {

  var $configTable = "expense2_config";
  var $confUserCol = "user_id";
  var $confTitleCol = "title";
  var $confLangCol = "lang";
  var $confProdCol = "product_table";
  var $benchmarkTable = "expense2_benchmark";
  var $bmFromCol = 'from_id';
  var $bmToCol = 'to_id';

  function Expense($user) {
    $this->user = $user;
    $this->config = $this->getConfig();
  }

  function getConfig() {
    $config = array();
    $select = "SELECT ".
              db_escape_string($this->confTitleCol).
              ", ".db_escape_string($this->confLangCol).
              ", ".db_escape_string($this->confProdCol).
              " FROM ".db_escape_string($this->configTable).
              " WHERE ".db_escape_string($this->confUserCol).
              " = '".db_escape_string($this->user)."'";
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $config['title'] = $row[$this->confTitleCol];
      $config['lang'] = $row[$this->confLangCol];
      $config['prodTable'] = $row[$this->confProdCol];
      db_free_result($stmt);
      return $config;
    }
    db_free_result($stmt);
    return false;
  }

  function setConfig($user, $values) {
  }

  function getBenchmarkTargets() {
    $targets = array();
    $select = "SELECT ".db_escape_string($this->bmToCol).
              " FROM ".db_escape_string($this->benchmarkTable).
              " WHERE ".db_escape_string($this->bmFromCol).
              " = '".db_escape_string($this->user)."'".
              " OR ".db_escape_string($this->bmFromCol)." IS NULL".
              " ORDER BY ".db_escape_string($this->bmToCol);
    $stmt = db_query($select);
    while ($row = db_fetch_assoc($stmt)) {
      $subsel = "SELECT ".
                db_escape_string($this->confTitleCol).
                ", ".db_escape_string($this->confLangCol).
                ", ".db_escape_string($this->confProdCol).
                " FROM ".db_escape_string($this->configTable).
                " WHERE ".db_escape_string($this->confUserCol).
                " = '".db_escape_string($row[$this->bmToCol])."'";
      $substmt = db_query($subsel);
      if ($subrow = db_fetch_assoc($substmt)) {
        $config = array();
        $config['title'] = $subrow[$this->confTitleCol];
        $config['lang'] = $subrow[$this->confLangCol];
        $config['prodTable'] = $subrow[$this->confProdCol];
        db_free_result($substmt);
        $targets[] = array('id' => $row[$this->bmToCol], 'config' => $config);
      }
    }
    db_free_result($stmt);
    return $targets;
  }

  function getFirstDate() {
    $select = "SELECT MIN(date) AS first FROM ".$this->config['prodTable'];
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $first = $row['first'];
      if (!$first) {
        $first = date('Y-m-d H:i:s');
      }
    }
    db_free_result($stmt);
    return strtotime($first);
  }

  function getLastDate() {
    $select = "SELECT MAX(date) AS last FROM ".$this->config['prodTable'];
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $last = $row['last'];
      if (!$last) {
        $last = date('Y-m-d H:i:s');
      }
    }
    db_free_result($stmt);
    return strtotime($last);
  }

  function getLastId() {
    $select = "SELECT MAX(id) AS last FROM ".$this->config['prodTable'];
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $last = $row['last'];
    }
    db_free_result($stmt);
    return $last;
  }

  function getTotal($query=NULL) {
    $total = 0;
    $where = attrs2db($query);
    $select = "SELECT SUM(cost) AS total FROM ".$this->config['prodTable'].
              "$where";
    $stmt = db_query($select);
    # echo "<!-- $select: $stmt -->\n";
    if ($row = db_fetch_assoc($stmt)) {
      $total = $row['total'];
    }
    db_free_result($stmt);
    return $total;
  }

  function getAvg($query=NULL) {
    $avg = 0;
    $where = attrs2db($query);
    $select = "SELECT AVG(cost) AS avg FROM ".$this->config['prodTable'].
              "$where";
    $stmt = db_query($select);
    # echo "<!-- $select: $stmt -->\n";
    if ($row = db_fetch_assoc($stmt)) {
      $avg = $row['avg'];
    }
    db_free_result($stmt);
    return $avg;
  }

  function addProduct(&$arr) {
    $values = array('date' => date2db($arr['date']),
                    'cost' => $arr['cost'],
                    'type' => $arr['type'],
                    'prod' => $arr['prod']);
    if (isset($arr['other'])) {
      $values['other'] = $arr['other'];
      $values['currency'] = $arr['currency'];
    }
    return (db_insert($this->config['prodTable'], $values));
  }

  function updateProduct($arr) {
    $like = array('id' => $arr['id']);
    $values = array('date' => date2db($arr['date']),
                    'cost' => $arr['cost'],
                    'type' => $arr['type'],
                    'prod' => $arr['prod'],
                    'other' => $arr['other'],
                    'currency' => $arr['currency']);
    return (db_update($this->config['prodTable'], $values, $like));
  }

  function deleteProduct($arr) {
    $like = array('id' => $arr['id']);
    return (db_delete($this->config['prodTable'], $like));
  }

  function cleanAllProducts() {
    $delete = "TRUNCATE TABLE ".$this->config['prodTable'];
    $stmt = db_query($delete);
  }

  function getMax($query=NULL, $groupBy=NULL) {
    $max = 0;
    $where = attrs2db($query);
    $what = ($query['group'] ? 'MAX(SUM(cost))' : 'MAX(cost)');
    $select = "SELECT $what AS max FROM ".$this->config['prodTable'].
              $where;
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $max = $row['max'];
    }
    db_free_result($stmt);
    return $max;
  }
 
  function getMin($query=NULL, $groupBy=NULL) {
    $min = 0;
    $where = attrs2db($query);
    $what = ($query['group'] ? 'MIN(SUM(cost))' : 'MIN(cost)');
    $select = "SELECT $what AS min FROM ".$this->config['prodTable'].
              $where;
    $stmt = db_query($select);
    if ($row = db_fetch_assoc($stmt)) {
      $min = $row['min'];
    }
    db_free_result($stmt);
    return $min;
  }

  function getProducts($query=NULL) {
    # $products = array();
    $where = attrs2db($query);
    $select = "
      SELECT id, type, date, cost, prod, other, currency
      FROM ".$this->config['prodTable']."
      $where";
    $stmt = db_query($select);
    # while ($row = db_fetch_assoc($stmt)) {
    #   $products[] = $row;
    # }
    db_free_result($stmt);
    $products = $stmt;
    return $products;
  }

  function getSummary($query=NULL) {
    $products = array();
    $where = attrs2db($query);
    $select = "
      SELECT id, type, date, SUM(cost) AS cost
      FROM ".$this->config['prodTable']."
      $where";
    $stmt = db_query($select);
    # echo "<!--\n$select\n-->\n";
    # while ($row = db_fetch_assoc($stmt)) {
    #   $products[] = $row;
    # }
    db_free_result($stmt);
    $products = $stmt;
    return $products;
  }
 
}

?>
