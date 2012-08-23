<?php
include('config.default.inc.php');
require_once('class/class.database.php');
require_once('class/class.user.php');
require_once('class/class.servertest.php');
require_once('class/class.sendmail.php');

// create session
session_start();
//session_destroy();

try {
  $_SESSION['db'] = new database($dbuser, $dbpass, $dbhost, $dbname);
  if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(); // Security: http://phpsec.org/projects/guide/4.html
    $_SESSION['initiated'] = true;
  }

  if (!isset($_SESSION['user'])) {
    if (!isset($_POST['login']) && !isset($_POST['password'])) {
      // display login form
      include_once('inc/head.inc.php');
      include_once('inc/loginform.inc.php');
      include_once('inc/foot.inc.php');
    } else {
      // login
      $_SESSION['user'] = new user($_POST['login'], $_POST['password']);
      //print_r($_SESSION);
    }
  } else {
    if (isset($_GET['a'])) {
      switch ($_GET['a']) {
        case 'logout':
          session_destroy();
          header("Location: index.php");
        break;
      }
    }
  }
} 
catch (Exception $ex) {
    include_once('inc/head.inc.php');
    print("<h2><span class=\"badnews\">Error</span></h2><hr />");
    print($ex->getMessage()."\n");
    print("<br /><a href=\"".$_SERVER['HTTP_REFERER']."\" title=\"back...\">Go back...</a>");
    include_once('inc/foot.inc.php');
}

?>