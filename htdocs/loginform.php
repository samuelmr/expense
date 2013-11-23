<?php

  require_once('include_library.php');
  require_once('config.php');

  $QUERY = getQuery($_REQUEST);
  $CONFIG = getConfig($_SESSION, $QUERY);
  $locale = getLocale($_SESSION, $QUERY, $CONFIG);

  $headers = <<<EOS
  <link rel="stylesheet" type="text/css" href="/errors.css" />
  <link rel="stylesheet" title="Login form style" type="text/css" href="/login.css" />
  <script type="text/javascript" src="login.js">
  </script>

EOS
  ;
  if (isset($_REQUEST['message']) && !isset($message)) {
    $message = $_REQUEST['message'];
  }
  $message = isset($message) ? $message : $locale['login_message'];
  $title = isset($title) ? $title : $locale['login_title'];
  echo header_plain($title, $headers, 'strict', $QUERY['lang']);
  echo "  <h1>".htmlentities($title)."</h1>\n";
  # echo "<pre>\n";
  # var_dump($LOCALE);
  # echo "</pre>\n";

  $goto = isset($goto) ? $goto : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  if (count($GLOBALS['ERRORS']) > 0) {
    for ($i=0; $i<count($GLOBALS['ERRORS']); $i++) {
      list($str, $class, $file, $line) = $GLOBALS['ERRORS'][$i];
      if ($class != 'notice') {
        echo "  <div class=\"$class\">".htmlentities($str)."</div>\n";
      }
    }
  }

  echo "  <div".(isset($_REQUEST['message']) ? ' class="warning"' : '').">".
       htmlentities($message)."</div>\n";

?>
  <h2><?php echo htmlentities($go); ?></h2>
  <form action="<?php echo htmlentities($goto); ?>" method="post" id="loginform">
  <table>
   <caption><?php echo htmlentities($locale['login_prompt']); ?></caption>
   <tr>
    <td class="auth">
     <p>
      <label for="login_username" class="block"><?php echo htmlentities(ucfirst($locale['login_username'])); ?></label>
      <input type="text" name="login_username" id="login_username" size="12" maxlength="20" />
     </p>
    </td>
    <td class="auth">
     <p>
      <label for="login_password" class="block"><?php echo htmlentities(ucfirst($locale['login_password'])); ?></label>
      <input type="password" name="login_password" id="login_password" size="12" maxlength="20" />
     </p>
    </td>
    <td>
     <p>
      <input type="submit" value="<?php echo htmlentities($locale['login_go']); ?>" />
     </p>
    </td>
   </tr>
  </table>
  </form>
  <script type="text/javascript">
  // <![CDATA[<!--
    if (loginFocus) {
      loginFocus();
    }
    else if (document &&
             document.forms &&
             document.forms['loginform'] &&
             document.forms['loginform'].login &&
             document.forms['loginform'].login.focus) {
      document.forms['loginform'].login.focus();
    }
  // -->]]>
  </script>

  <h2><?php echo htmlentities($locale['login_register']); ?></h2>
  <form id="register" action="register.php" method="post">
   <table>
    <caption><?php echo htmlentities($locale['login_choose']); ?></caption>
    <tr>
     <td class="auth">
      <p>
       <label for="username" class="block"><?php echo htmlentities(ucfirst($locale['login_username'])); ?></label>
       <input type="text" name="username" id="username" size="12" maxlength="20" />
      </p>
     </td>
     <td class="auth">
      <p>
       <label for="password" class="block"><?php echo htmlentities(ucfirst($locale['login_password'])); ?></label>
       <input type="password" name="password" id="password" size="12" maxlength="20" />
      </p>
     </td>
     <td>
      <p>
       <input type="submit" value="<?php echo htmlentities($locale['login_register']); ?>" />
      </p>
     </td>
    </tr>
   </table>
  </form>

  <h2><?php echo htmlentities($locale['login_try']); ?></h2>
  <p><a href="/?login_username=demo&amp;login_password=demo&amp;lang=fi"><?php echo htmlentities($locale['login_go']); ?></a></p>

<?php
  echo footer_plain('UA-4404005-8');
?>
