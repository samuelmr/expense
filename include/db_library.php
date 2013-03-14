<?php

  function db_connect($host, $login, $passwd, $db) {
    $conn = @mysql_connect($hostname,
                          $login,
                          $passwd);

    if ($conn) {
      mysql_select_db($db);
    }
    else {
      echo "<h1>DB CONNECTION FAILED</h1>\n";
      exit();
    }
    return $conn;
  }

  function db_error($conn=NULL) {
    return mysql_error($conn);
  }

  function db_escape($str) {
    return mysql_real_escape_string($str);
  }

  function db_escape_string($str) {
    return mysql_real_escape_string($str);
  }

  function db_free_result(&$stmt) {
    return mysql_free_result($stmt);
  }

  function db_select($tables, &$columns, $like=NULL, $opr='AND', $misc=NULL) {

    /*
     *  example:
     *  $table = 'my_tbl';
     *  $columns = array('id', 'name');
     *  $like = array('name' => 'foo%', 'size' => 12);
     *  $opr = 'OR';
     *  $misc = array('GROUP BY id', 'ORDER BY name');
     *  
     *  Make sure to use arrays for 2nd, 3rd and 4th arg,
     *  there is no error handling: if args are of wrong
     *  type, they are simply ignored!
     *  
     */

    $select = "SELECT	";
    if (is_array($columns)) {
      $select .= implode(',', $columns);
    }
    else {
      $select .= $columns;
    }
    $select .= "\nFROM	$tables\nWHERE	1=1";
    if (is_array($like)) {
      while(list($key, $value) = each($like)) {
        $select .= "\n$opr $key LIKE '$value'";
      }
      reset($like);
    }
    if (is_array($misc)) {
      $select .= join ("\n", $misc);
    }
    elseif (is_string($misc)) {
      $select .= "\n$misc";
    }
    $stmt = db_query($select);
    return $stmt;
  }

  function db_query(&$sql) {
    global $CONFIG;
    $stmt = mysql_query($sql);
    if (isset($CONFIG['db_debug']) && $CONFIG['db_debug']) {
      echo "<!--\n$sql\n$stmt\n-->\n";
    }
    if (!$stmt) {
      trigger_error("$sql: ".mysql_error($GLOBALS['conn'])."\n", E_USER_WARNING);
    }
    return $stmt;
  }

  function db_num_rows(&$stmt) {
    return mysql_num_rows($stmt);
  }

  function db_affected_rows(&$conn) {
   return mysql_affected_rows($conn);
  }

  function db_fetch_assoc(&$stmt) {
    return mysql_fetch_assoc($stmt);
  }

  function db_fetch_row(&$stmt, $type=MYSQL_ASSOC) {
    /* values for $type: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH */
    return mysql_fetch_array($stmt, $type);
  }

  function db_insert($table, &$values) {

    /* $values is an associative array, keys are column names */

    if (is_array($values)) {
      $insert = "
        INSERT INTO $table
          (`".
          join('`, `', array_keys($values)).
          "`)
        VALUES
          (";
      # $insert .= join("', '", array_values($values));
      $v = array_values($values);
      for ($i = 0; $i<count($v); $i++) {
        $insert .= isset($v[$i]) ? "'".db_escape_string($v[$i])."'" : 'NULL';
        if (count($v) > ($i + 1)) {
          $insert .= ", ";
        }
      }
      $insert .= ")";
      $stmt = db_query($insert);
      return $stmt;
    }
  }

  function db_insert_id() {
    return mysql_insert_id();
  }

  function db_replace($table, $values) {

    /* $values is an associative array, keys are column names */

    $replace = "
      REPLACE INTO $table
        (`".
        join('`, `', array_keys($values)).
        "`)
      VALUES
        (";
    # $replace .= join("', '", array_values($values));
    $v = array_values($values);
    for ($i = 0; $i<count($v); $i++) {
      $replace .= isset($v[$i]) ? "'".db_escape_string($v[$i])."'" : 'NULL';
      if (count($v) > ($i+1)) { 
        $replace .= ", ";
      }
    }
    $replace .= ")";

    $stmt = db_query($replace);
    return $stmt;

  }

  function db_update($table, &$values, &$like, $opr='AND') {

    /* $values and $like are both associative arrays, keys are column names */

    $update = "UPDATE $table SET ";
    $keys = array_keys($values);
    for ($i=0; $i<count($keys); $i++) {
      $key = $keys[$i];
      if (isset($values[$key])) {
        $update .= "$key = '".db_escape_string($values[$key])."'";
      }
      else {
	$update .= "$key = NULL";
      }
      if (isset($keys[$i+1])) {
        $update .= ", ";
      }
    }
    $update .= " WHERE 1=1";
    # $keys = array_keys($like);
    # for ($i=0; $i<count($keys); $i++) {
    #   $key = $keys[$i]; 
    #   $update .= "$key LIKE '$like[$key]'";
    #   if ($keys[$i+1]) {
    #     $update .= " $opr ";
    #  }
    # }
    if (is_array($like)) {
      while(list($key, $value) = each($like)) {
        $update .= "\n$opr $key LIKE '$value'";
      }
      reset($like);
    }

    $stmt = db_query($update);
    return $stmt;

  }

  function db_delete($table, &$like, $opr='AND') {

    /* $like is an associative array, keys are column names */

    $delete = "DELETE FROM $table WHERE ".
    $keys = array_keys($like);
    for ($i=0; $i<count($keys); $i++) {
      $key = $keys[$i]; 
      $delete .= "$key LIKE '$like[$key]'";
      if ($keys[$i+1]) {
        $delete .= " $opr ";
      }
    }

    $stmt = db_query($delete);
    return $stmt;
  }

  function db_date($time=NULL) {
    if ($time) {
      $datestring = date('Y-m-d H:i:s', $time);
    }
    else {
      $datestring = date('Y-m-d H:i:s');
    }
    return "'$datestring'";
  }



  function db_get_array($table, $name, $value, $where=NULL, $order=NULL) {
    global $CONFIG;
    $select = "SELECT $name AS name, $value AS value FROM $table";
    if ($where) {
      $select .= " WHERE $where";
    }
    if ($order) {
      $select .= " ORDER BY $order";
    }
    $results = array();
    $stmt = db_query($select);
    if (isset($CONFIG['db_debug']) && $CONFIG['db_debug']) {
      echo "<!-- $select: $stmt -->\n";
    }
    while ($row = db_fetch_row($stmt)) {
      $results[$row['name']] = $row['value'];
    }
    return $results;
  }

  /* obsolete */

  function db_get_rows($table, $opts=NULL) {

    /* slow - stores all results in a big array */

    $results = array();

    $select = "SELECT * FROM $table $opts";
    $stmt = db_query($select);
    while ($row = db_fetch_row($stmt)) {
      $results[] = $row;
    }
    return $results;
  }

?>
