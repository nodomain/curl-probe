#!/home/nodomain/bin/php
<?php
include_once('inc/config.default.inc.php');
require_once('class/class.servertest.php');
require_once('class/class.database.php');
require_once('class/class.sendmail.php');
require_once('class/class.twitter.php');
require_once('class/class.shorturl.php');
require_once('lib/XMPPHP/XMPP.php');

$currentminute = date("i");
//$currentminute = 5;

$sTest = new serverTest();
try {
  $db = new database($dbuser, $dbpass, $dbhost, $dbname);
  $servers = $db->getAllProbesToCheck();
  foreach ($servers as $server) {
    if ($currentminute % $server['checkinterval'] == 0) {
      print("Probing ".$server['name']);
      $mystatus = false;
      $sTest->setTitle($server['name']);
      $sTest->setServer($server['url']);
      $sTest->setFindstring($server['findstring']);
      $sTest->setVersion($version);
      $sTest->setHostname($hostname);
      $sTest->test();

      // test again to make sure we really have a problem and avoid accidental mails
      if ($sTest->getStatus() == false) {      
        $random = rand(1, 10);
        print (" ... trying again in ".$random." seconds\n");
        sleep($random);
        $sTest->test();
      }
      
      if ($sTest->getStatus() == false) {      
        print (" ... failed\n");
        // send mail only if status in DB was true -> mail will only be sent once
        $status = $db->getProbeStatus($server['id']);
	if ($status == true) {
	  // insert entry into events table
	  $db->addProbeEvent('error', $sTest->getResult(), $server['id']);
	  // mail it
	  $pretext = "Hello,\nan error occured while probing \"" . $server['name']. "\" \nwith URL \"". $server['url']."\"\nfor \"". $server['findstring']."\". \n\nThe server replied as follows:\n\n";
          $posttext = "\n\nRegards,\nyour faithful curl-probe agent\nat ".$hostname;
          $status = $db->setProbeStatus($server['id'], 0);      
//          print ("Error: Servertest for probe '" . $sTest->getTitle() ."' failed!\n");
          $responsible = $db->getAllResponsible($server['id']);
          if (isset($responsible)) {
            foreach ($responsible as $recipient) {
	      // tweet it
	      if ($recipient['twitteruser'] != '') {
	        $gd = new shorturl($server['url']);
		$shortUrl = $gd->getShortURL();
	        $twitter = new Twitter($recipient['twitteruser'], $recipient['twitterpass']);
                $twitterStatus = $twitter->send(date($timeformat) .": ".$server['name']. " (".$shortUrl.") has an error");	
              }
              // jabber it
              if ($recipient['jabberuser'] != '') {
                $conn = new XMPPHP_XMPP($jabberserver, $jabberport, $jabberuser, $jabberpass, $jabberressource, $jabberdomain, $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);
                try {
                  $conn->connect();
                  $conn->processUntil('session_start');
                  $conn->presence();
                  $conn->message($recipient['jabberuser'], date($timeformat) .": ".$server['name']. " (".$shortUrl.") has an error. The server replied as follows: ".$sTest->getResult());
                  $conn->disconnect();
                } catch(XMPPHP_Exception $e) {
                  print("XMPPHP Error: ". $e->getMessage());
                }
              }

              // mail it
              if ((strtotime($startquietmode) <= time()) && (time() <= strtotime($endquietmode))) {
                echo "not sending mail because of quiet mode\n";
              } else {
                echo "sending mail\n";
                $mailer = new sendMail($recipient['email'], $mailfrom, $replyto);
                $mailer->send($sTest->getTitle() . " - error", $pretext . $sTest->getResult() . $posttext);
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
        print (" ... ok\n");
	if ($status == false) {
	  // insert entry into events table
	  $db->addProbeEvent('ok', '', $server['id']);
	  $logtime = $db->getProbeStatusLogtime($server['id']);
	  // mail it
	  $pretext = "Hello,\nthe probe \"" . $server['name']. "\" with URL \"". $server['url']."\", probing for \"". $server['findstring']."\", has recovered.";
	  $pretext = $pretext."\nIt had been on error since ".date($timeformat, strtotime($logtime))." (".round(abs(time() - strtotime($logtime)) / 60,1)." minute(s)).";
          $posttext = "\n\nRegards,\nyour faithful curl-probe agent\nat ".$hostname;
          $status = $db->setProbeStatus($server['id'], 1);
//          print ("Error: Servertest for probe '" . $sTest->getTitle() ."' failed!\n");
          $responsible = $db->getAllResponsible($server['id']);
          if (isset($responsible)) {
            foreach ($responsible as $recipient) {
      	      // tweet it
              if ($recipient['twitteruser'] != '') {
                $gd = new shorturl($server['url']);
                $shortUrl = $gd->getShortURL();      
                $twitter = new Twitter($recipient['twitteruser'], $recipient['twitterpass']);
                $twitterStatus = $twitter->send(date($timeformat) .": ".$server['name']. " (".$shortUrl.") has recovered. It had been on error since ".date($timeformat, strtotime($logtime))." (".(round(abs(time() - strtotime($logtime)) / 60,1))." min).");
              }

              // jabber it
              if ($recipient['jabberuser'] != '') {
                $conn = new XMPPHP_XMPP($jabberserver, $jabberport, $jabberuser, $jabberpass, $jabberressource, $jabberdomain, $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);
                try {
                  $conn->connect();
                  $conn->processUntil('session_start');
                  $conn->presence();
                  $conn->message($recipient['jabberuser'], date($timeformat) .": ".$server['name']. " (".$shortUrl.") has recovered. It had been on error since ".date($timeformat, strtotime($logtime))." (".(round(abs(time() - strtotime($logtime)) / 60,1))." minute(s)).");
                  $conn->disconnect();
                } catch(XMPPHP_Exception $e) {
                  print("XMPPHP Error: ". $e->getMessage());
                }
              }

              // mail it
              if ((strtotime($startquietmode) <= time()) && (time() <= strtotime($endquietmode))) {
                echo "not sending mail because of quiet mode\n";
              } else {
                echo "sending mail\n";
                $mailer = new sendMail($recipient['email'], $mailfrom, $replyto);
                $mailer->send($sTest->getTitle() . " - recovered", $pretext . $posttext);
              }
            }
          } else {
            throw new Exception("No responsible person found. Please set up at least one person!");
          }
        }
      }
    }
  }
} catch (Exception $ex) {
  // send mail    
  print("Exception occured for probe '" . $sTest->getTitle() . "': " . $ex->getMessage()."\n");
}
?>
