<?php
// include general functions
include_once('inc/functions.inc.php');

// check login
if (isset($_SESSION['user'])) {
  try {
    // spawn servertest object
    $sTest = new serverTest();
    include_once('inc/head.inc.php');
    print("<h2>Probe overview (realtime data)</h2>");
    print("<hr />");
    print("<table summary=\"overview\">");
    print("<tr><th style=\"width: 200px;\">Probe</th><th style=\"width: 50px;\">Status</th><th style=\"width: 80px;\">Time (s)</th><th>Additional information (max. " . $maxreplychars . " chars)</th></tr>");

    $servers = $_SESSION['db']->getAllProbes();
    foreach ($servers as $server) {
      // only show active probes
      if ($server['check'] == true) {
        // users may only see their own probes
        if ($_SESSION['user']->isUser()) {     
          if ($_SESSION['user']->ownsProbe($server['id'])) {
            print("<tr>");
            $sTest->setTitle($server['name']);
            $sTest->setServer($server['url']);
            $sTest->setFindstring($server['findstring']);
            $sTest->setVersion($version);
            $sTest->setHostname($hostname);
            try {    
              $sTest->test();
              if ($sTest->getStatus() == true) {
                $message = "<img src=\"img/good.png\" alt=\"ok\"/></td><td>" . $sTest->getBenchmark()->timeElapsed();
              } else {
                $message = "<img src=\"img/bad.png\" alt=\"failed\"/></td><td>" . $sTest->getBenchmark()->timeElapsed() .  "</td><td>" . htmlspecialchars(substr($sTest->getResult(), 0, $maxreplychars));
              }          
              print("<td><strong>" . $sTest->getTitle() . "</strong></td><td>" . $message . "</td>\n");
            } catch (Exception $ex) {
              print("<td><strong><span class=\"badnews\">". $sTest->getTitle(). ": ". $ex->getMessage() ."</span></strong></td>");
            }
            print("</tr>");
          }   
        } else {
          print("<tr>");
          $sTest->setTitle($server['name']);
          $sTest->setServer($server['url']);
          $sTest->setFindstring($server['findstring']);
          $sTest->setVersion($version);
          $sTest->setHostname($hostname);
          try {    
            $sTest->test();
            if ($sTest->getStatus() == true) {
              $message = "<img src=\"img/good.png\" alt=\"ok\"/></td><td>" . $sTest->getBenchmark()->timeElapsed();
            } else {
              $message = "<img src=\"img/bad.png\" alt=\"failed\"/></td><td>" . $sTest->getBenchmark()->timeElapsed() .  "</td><td>" . htmlspecialchars(substr($sTest->getResult(), 0, $maxreplychars));
            }          
            print("<td><strong>" . $sTest->getTitle() . "</strong></td><td>" . $message . "</td>\n");
          } catch (Exception $ex) {
            print("<td><strong><span class=\"badnews\">". $sTest->getTitle(). ": ". $ex->getMessage() ."</span></strong></td>");
          }
          print("</tr>");
        }
      }
    }
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
