<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Curl-Probe</title>
<!-- <script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/ajax.js"></script> //-->
<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
<div id="content">
<h1>Curl-Probe</h1>
<div id="header">
<?
if (isset($_SESSION['user'])) {
  print("<hr />\n");
  print("<a href=\"index.php\">Probe overview</a> | 
        <a href=\"admin.php?a=probes\">Manage probes</a> | ");

  if ($_SESSION['db']->isAdminUser($_SESSION['user']->getUserid()) == 1)
    print("<a href=\"admin.php?a=users\">Manage users</a> | ");

  print("<a href=\"admin.php?a=edituser&amp;id=".$_SESSION['user']->getUserid()."\">Signed in as ". htmlspecialchars($_SESSION['user']->getName()) . "</a> | 
  <a href=\"?a=logout\">Logout</a>\n");
}
?>
<hr />
</div>
