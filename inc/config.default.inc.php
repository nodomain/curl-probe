<?php
$dbhost = "localhost";
$dbuser = "curl-probe";
$dbpass = "SECRET";
$dbname = "curl-probe";

// host settings 
$hostname = "hostname";
$contact = "mail@".$hostname;
$mailfrom = "curl-probe <curl-probe@".$hostname.">";
$replyto = "mail@".$hostname;

$maxreplychars = 2048;

$revision = '$Rev: 31 $';
$date     = '$Date: 2009-11-11 23:14:31 +0000 (Wed, 11 Nov 2009) $';

$version  = "2.3." . substr($revision, 6, -2);
$build = substr($date, 7, 10);

$timeformat = "y/m/d H:i T";

// quiet mode settings
$startquietmode = "00:00";
$endquietmode = "00:00";

// jabber settings
$jabberserver = "jabber.org";
$jabberport = "5222";
$jabberuser = "curlprobe";
$jabberpass = "";
$jabberdomain = "jabber.org";
$jabberressource = "vs01";

include_once("config.local.inc.php");
?>
