<div id="footer">
<?
if (isset($_SESSION['user'])) {
   print("<hr />\n");
   print("<a href=\"index.php\">Probe overview</a> | 
          <a href=\"admin.php?a=probes\">Manage probes</a> | 
	  <a href=\"admin.php?a=users\">Manage users</a> |
	  <a href=\"admin.php?a=edituser&amp;id=".$_SESSION['user']->getUserid()."\">Signed in as ". htmlspecialchars($_SESSION['user']->getName()) . "</a> | 
	  <a href=\"?a=logout\">Logout</a>\n");
}
?>
<hr />
<a href="mailto:<?=$contact?>">contact</a> | version: <?=$version?> build: <?=$build?> | curl probe is free software - see <a href="http://code.google.com/p/curl-probe/" target="_blank">Google Code</a> for details
<div id="message"></div>
<hr />
</div>
</div>
</body>
</html>