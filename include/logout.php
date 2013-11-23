<?php
  # session_name($_REQUEST['session_name']);
  session_start();
  # session_unset(); 
  session_destroy();
  session_start();
  $_SESSION = array();
  session_write_close();
?>
