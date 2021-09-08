<?php
 require_once('locale_fi.php');
 $keys = $LOCALE;
 $target_lang = 'en';
 if (isset($_REQUEST['lang'])) {
   $target_lang = $_REQUEST['lang'];
 }
 include_once('locale_'.$target_lang.'.php');
 # echo "<p>".join("</p><p>", $LOCALE)."</p>\n";
 echo "<pre>\n";
 foreach($keys as $key => $value) {
   echo "\$LOCALE['$key'] = '$LOCALE[$key]';\n";
 }
 echo "/<pre>\n";
?>
