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

$version = "3.1";

$timeformat = "y/m/d H:i T";

// quiet mode settings
$startquietmode = "00:00";
$endquietmode = "00:00";

include_once("config.local.inc.php");
?>
