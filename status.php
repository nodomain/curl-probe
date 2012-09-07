<?php
// include general functions
include_once('inc/functions.inc.php');

// check login
if (isset($_SESSION['user'])) {
  try {
    // spawn servertest object
    include_once('inc/head.inc.php');
    print("<h2>Probe status</h2>");
    print("<hr />");
    print("<table summary=\"overview\">");
    $status = $_SESSION['db']->getProbeResponse($_GET['id']);
    print("<tr><td>Status</td><td>".$status['type']."</td></tr>");
    print("<tr><td>Message</td><td>".$status['text']."</td></tr>");
    print("<tr><td>Timestamp</td><td>".$status['timestamp']."</td></tr>");
    print("</table>");
    include_once('inc/foot.inc.php');
  } catch (Exception $ex) {
    include_once('inc/head.inc.php');
    print("<h2><span class=\"badnews\">Error</h2><hr />");
    print($ex->getMessage()."\n");
    include_once('inc/foot.inc.php');
  }
}
?>
