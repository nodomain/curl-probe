curl-probe
==========

PHP tool to check HTTP URLs for errors and notify by mail

I needed a litte tool that checks HTTP URLs for a defined string on a regular base. If an error occurs, predefined persons will be notified by email.

Since nothing like that existed I hacked it on my own.


The code is quite old so please don't bug me for the bad progamming style. It can surely be improved and that's why I publish it here :-)
I also recently added Jabber/XMPP support which means that you can be notified at your jabber account. Just set up an own account for curl-probe, configure it in config.local.inc.php and add this account to your roster. After entering your account in the user profile section you are going to receive every notification via Jabber.

Find more here (german only): http://nodomain.cc/2009/04/01/curl-probe.html