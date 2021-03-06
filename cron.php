#!/package/host/localhost/php-5/bin/php
<?php
include_once('inc/config.default.inc.php');
require_once('class/class.servertest.php');
require_once('class/class.database.php');
require_once('class/class.sendmail.php');
require_once('class/class.shorturl.php');

$currentminute = date("i");
//$currentminute = 5;

$fp = fopen("./tmp/lock.txt", "w+");

if (flock($fp, LOCK_EX | LOCK_NB)) { // do an exclusive lock
  $sTest = new serverTest();
  try {
    $db      = new database($dbuser, $dbpass, $dbhost, $dbname);
    $servers = $db->getAllProbesToCheck();
    foreach ($servers as $server) {
      if ($currentminute % $server['checkinterval'] == 0) {
        print("Probing " . $server['name']);
        $mystatus = false;
        $sTest->setTitle($server['name']);
        $sTest->setServer($server['url']);
        $sTest->setHost($server['host']);
        $sTest->setFindstring($server['findstring']);
        $sTest->setVersion($version);
        $sTest->setHostname($hostname);
	
	try {
          $sTest->test();
          // test again to make sure we really have a problem and avoid accidental mails
          if ($sTest->getStatus() == false) {
            $random = rand(1, 15);
            print(" ... trying again in " . $random . " seconds to ensure we really have a problem\n");
            sleep($random);
            $sTest->test();
          }
        }
        catch (Exception $ex) {
          print("Exception occured for probe '" . $sTest->getTitle() . "': " . $ex->getMessage() . "\n");
        }
        
        if ($sTest->getStatus() == false) {
          print(" ... failed\n");
          // send mail only if status in DB was true -> mail will only be sent once
          $status = $db->getProbeStatus($server['id']);
          if ($status == true) {
            // insert entry into events table
            $db->addProbeEvent('error', $sTest->getResult(), $server['id']);
            // mail it
            $text     = "Service: ".$server['name']."\nURL: ".$server['url']."\nString: ".$server['findstring']."\nState: ERROR\n\nDate/Time: ".date($timeformat). "\n\nAdditional Info: \n\n" .$sTest->getResult();
            $status      = $db->setProbeStatus($server['id'], 0);
            //          print ("Error: Servertest for probe '" . $sTest->getTitle() ."' failed!\n");
            $responsible = $db->getAllResponsible($server['id']);
            if (isset($responsible)) {
              foreach ($responsible as $recipient) {
                // mail it
                if ((strtotime($startquietmode) <= time()) && (time() <= strtotime($endquietmode))) {
                  echo "not sending mail because of quiet mode\n";
                } else {
                  echo "sending mail\n";
                  $mailer = new sendMail($recipient['email'], $mailfrom, $replyto);
                  $mailer->send("PROBLEM: ". $sTest->getTitle() ." is CRITICAL", $text);
                }
              }
            } else {
              throw new Exception("No responsible person found. Please set up at least one person!");
            }
          } else {
            // nothing changed
          }
        } else {
          $status = $db->getProbeStatus($server['id']);
          print(" ... ok\n");
          if ($status == false) {
            // insert entry into events table
            $db->addProbeEvent('ok', '', $server['id']);
            $logtime     = $db->getProbeStatusLogtime($server['id']);
            // mail it
            $text        = "Service: ".$server['name']."\nURL: ".$server['url']."\nString: ".$server['findstring']."\nState: OK\n\nDate/Time: ".date($timeformat). "\nSince:".date($timeformat, strtotime($logtime)) . " (" . round(abs(time() - strtotime($logtime)) / 60, 1) . " minute(s))\n\nAdditional Info: \n\n" .$sTest->getResult();
            $status      = $db->setProbeStatus($server['id'], 1);
            //          print ("Error: Servertest for probe '" . $sTest->getTitle() ."' failed!\n");
            $responsible = $db->getAllResponsible($server['id']);
            if (isset($responsible)) {
              foreach ($responsible as $recipient) {
                // mail it
                if ((strtotime($startquietmode) <= time()) && (time() <= strtotime($endquietmode))) {
                  echo "not sending mail because of quiet mode\n";
                } else {
                  echo "sending mail\n";
                  $mailer = new sendMail($recipient['email'], $mailfrom, $replyto);
                  $mailer->send("RECOVERY: ". $sTest->getTitle() ." is OK", $text);
                }
              }
            } else {
              throw new Exception("No responsible person found. Please set up at least one person!");
            }
          }
        }
      }
    }
  }
  catch (Exception $ex) {
    // send mail
    print("Exception occured for probe '" . $sTest->getTitle() . "': " . $ex->getMessage() . "\n");
  }
  flock($fp, LOCK_UN); // release the lock
} else {
  print("Couldn't get the lock!");
}
fclose($fp);
?>
