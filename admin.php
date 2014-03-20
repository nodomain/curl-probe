<?
// include general functions
include_once('inc/functions.inc.php');

// check login
if (isset($_SESSION['user'])) {
  try {
    include_once('inc/head.inc.php');
    include_once('inc/admin.inc.php');

    if (isset($_GET['a'])) {
      switch ($_GET['a']) {
        case 'logout':
          session_destroy();
          header("Location: index.php");	  
	break;
        case 'probes':
          listProbes();
	  break;
        case 'pause':
	  pauseProbe();
	  break;
	case 'unpause':
	  unpauseProbe();
          break;
	case 'editprobe':
	  editProbe();
          break;
	case 'deleteprobe':
	  deleteProbe();
          break;
	case 'addprobe':
	  addProbe();
          break;
	case 'addresp':
	  addProbeResponsible();
          break;
	case 'deleteresp':
	  deleteProbeResponsible();
          break;
        case 'users':
	  listUsers();
	  break;
	case 'edituser':
	  editUser();
	  break;
	case 'deleteuser':
	  deleteUser();
	  break;
	case 'adduser':
	  addUser();
	  break;
	default:
	  listProbes();
      }
    }
        
    include_once('inc/foot.inc.php');
  } catch (Exception $ex) {
    print("<h2 class=\"badnews\">Error</h2>\n");
    include_once('inc/head.inc.php');
    print("<hr />");
    print($ex->getMessage()."\n");
    print("<hr />");  
    include_once('inc/foot.inc.php');
  }
}

/*-----------------------------------------------------------*/
function listProbes() {
  print("<a href=\"?a=addprobe\">New Probe</a><br />");
  $servers = $_SESSION['db']->getAllProbes();
  print("<table summary=\"servers\">\n");
  print("<tr><th>Name</th><th>URL</th><th>Check interval</th><th>Active</th><th>Edit</th><th>Delete</th></tr>");
  foreach ($servers as $server) {
    // if user is not an admin only list users probes
    if ($_SESSION['user']->isUser()) {
      if ($_SESSION['user']->ownsProbe($server['id'])) {
        // print probe
        if ($server['check'] == true) {
          $check = "<a href=\"?a=pause&amp;id=".$server['id']."\" title=\"probe running -> pause probe\"><img src=\"img/play.png\" alt=\"probe running\" /></a>";
        } else {
          $check = "<a href=\"?a=unpause&amp;id=".$server['id']."\" title=\"probe paused -> unpause probe\"><img src=\"img/pause.png\" alt=\"probe paused\" /></a>";
        }
        print("<tr>");
        print("<td>". $server['name'] ."</td>
               <td><a href=\"". $server['url'] ."\" target=\"_blank\" title=\"test URL...\">".$server['url']."</a></td>
         <td>". $server['checkinterval']."</td>
         <td>". $check ."</td>
         <td><a href=\"?a=editprobe&amp;id=".$server['id']."\" title=\"edit probe\"><img src=\"img/edit.png\" alt=\"edit\" /></a></td>
         <td><a href=\"?a=deleteprobe&amp;id=".$server['id']."\" title=\"delete probe\"><img src=\"img/delete.png\" alt=\"delete\" /></a></td>
        ");
        print("</tr>");
      }
    } else {
    // print all
    if ($server['check'] == true) {
      $check = "<a href=\"?a=pause&amp;id=".$server['id']."\" title=\"probe running -> pause probe\"><img src=\"img/play.png\" alt=\"probe running\" /></a>";
    } else {
      $check = "<a href=\"?a=unpause&amp;id=".$server['id']."\" title=\"probe paused -> unpause probe\"><img src=\"img/pause.png\" alt=\"probe paused\" /></a>";
    }
    print("<tr>");
    print("<td>". $server['name'] ."</td>
           <td><a href=\"". $server['url'] ."\" target=\"_blank\" title=\"test URL...\">".$server['url']."</a></td>
     <td>". $server['checkinterval']."</td>
     <td>". $check ."</td>
     <td><a href=\"?a=editprobe&amp;id=".$server['id']."\" title=\"edit probe\"><img src=\"img/edit.png\" alt=\"edit\" /></a></td>
     <td><a href=\"?a=deleteprobe&amp;id=".$server['id']."\" title=\"delete probe\"><img src=\"img/delete.png\" alt=\"delete\" /></a></td>
    ");
    print("</tr>");
    }

    
  }
  print("</table>");

}


function editProbe() {
  if (!isset($_POST['submit'])) {
    if (isset($_GET['id'])) {
      $_SESSION['probeid'] = $_GET['id'];
    }

    // users may only edit their own probes
    if ($_SESSION['user']->isUser()) {     
      if (!$_SESSION['user']->ownsProbe($_SESSION['probeid'])) {
        print("You may only edit your own probes!");
        return;
      }   
    }

    $probe = $_SESSION['db']->getProbeData($_SESSION['probeid']);
    print("<h2>Probe settings</h2>");
    print("<form action=\"admin.php?a=editprobe&amp;id=".$_SESSION['probeid']."\" method=\"post\">\n");
    print("<label for=\"name\">Name</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"name\" id=\"name\" value=\"".$probe['name']."\" /><br />\n");
    print("<label for=\"url\">URL</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"url\" id=\"url\" value=\"".$probe['url']."\" /><br />\n");
    print("<label for=\"host\">Optional: Host</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"host\" id=\"host\" value=\"".$probe['host']."\" /><br />\n");
    print("<label for=\"findstring\">Look for</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"findstring\" id=\"findstring\" value=\"".$probe['findstring']."\" /><br />\n");
    print("<label for=\"checkinterval\">Check interval (minutes)</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"checkinterval\" id=\"checkinterval\" value=\"".$probe['checkinterval']."\" /><br />\n");
    print("<label for=\"check\">Check</label><br />\n");
    if ($probe['check'] == 1) {
      $checked = "checked = \"checked\"";
    }
    print("<input type=\"checkbox\" name=\"check\" id=\"check\" value=\"check\" ".$checked." /><br />\n");
    print("<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Save settings\" /><br />\n");
    print("</form>\n");
    print("<br /><hr />");
    print("<h2>Responsible for this probe</h2>");
    print("<p>Every user in this list will be informed by e-mail if an error occurs.</p>");
    $resp = $_SESSION['db']->getAllResponsible($_SESSION['probeid']);

    if (isset ($resp)) {
      print("<table summary=\"userlist\">");
      print("<tr><th>Name</th><th>E-mail address</th><th>Twitter</th><th>Remove</th></tr>");
      foreach ($resp as $current) {
        print("<tr><td>".$current['realname']."</td>
                   <td><a href=\"mailto:".$current['email']."\">".$current['email']."</a></td>
                   <td><a href=\"http://twitter.com/".$current['twitteruser']."\" target=\"_blank\">".$current['twitteruser']."</a></td>");
        
        if (count($resp) > 1) {
          if ($_SESSION['user']->isUser() and $_SESSION['user']->getUserId() == $current['user_id']) {
    		    print("<td>own user must not be deleted</td>");
          } else {
    		    print("<td><a href=\"?a=deleteresp&amp;rid=".$current['resp_id']."\" title=\"remove\"><img src=\"img/delete.png\" alt=\"delete\" /></a></td>");
          }
        } else { 
          print("<td>last user must not be deleted</td>");
        }

  		  print("</tr>");  
      }
      
      print("</table>");
    } else {
      print("No responsible person found. Please set up at least one person!<br /><br />\n");
    }
      $users = $_SESSION['db']->getAllUserData();
      print("Add users<br />\n");
      print("<form action=\"admin.php?a=addresp&amp;id=".$_SESSION['probeid']."\" method=\"post\">\n");
      print("<select name=\"user\">");
      foreach ($users as $user) {
        print("<option value=\"".$user['id']."\">".htmlspecialchars($user['realname'])." (".$user['email'].")</option>");
      }
      print("</select>");
      print("<input type=\"submit\" name=\"submit2\" id=\"submit2\" value=\"Add\" /><br />\n");
      
      print("</form><br />");
  } else {
    $result = $_SESSION['db']->setProbeData($_SESSION['probeid'], trim($_POST['name']), trim($_POST['url']), trim($_POST['host']), trim($_POST['findstring']), trim($_POST['check']), trim($_POST['checkinterval']));
    if ($result) {
      listProbes();
    }
  }
}


function deleteProbe() {
  // users may only edit their own probes
  if ($_SESSION['user']->isUser()) {     
    if (!$_SESSION['user']->ownsProbe($_GET['id'])) {
      print("You may only edit your own probes!");
      return;
    }   
  }

  $result = $_SESSION['db']->deleteProbe($_GET['id']);
  if ($result) {
    listProbes();
  }	  
}

function deleteProbeResponsible() {
  $result = $_SESSION['db']->deleteProbeResponsible($_GET['rid']);
  if ($result) {
    editProbe();
  }
}

function addProbeResponsible() {
  $result = $_SESSION['db']->addProbeResponsible($_GET['id'], $_POST['user']);
  //if ($result) {
    editProbe();
  //}
}

function addProbe() {
  if (!isset($_POST['submit'])) {
    print("<h2>Add new probe</h2>");
    print("<form action=\"admin.php?a=addprobe\" method=\"post\">\n");
    print("<label for=\"name\">Name</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"name\" id=\"name\" /><br />\n");
    print("<label for=\"url\">URL</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"url\" id=\"url\" /><br />\n");
    print("<label for=\"host\">Optional: Host</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"host\" id=\"host\" /><br />\n");
    print("<label for=\"findstring\">Look for</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"findstring\" id=\"findstring\" /><br />\n");
    print("<label for=\"checkinterval\">Check interval (minutes)</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"checkinterval\" id=\"checkinterval\" value=\"5\" /><br />\n");
    print("<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Save settings\" /><br />\n");
    print("</form>\n");
  } else {
    $result = $_SESSION['db']->addProbe(trim($_POST['name']), trim($_POST['url']), trim($_POST['host']), trim($_POST['findstring']), trim($_POST['checkinterval']), trim($_POST['check']));
    if ($result) {
      $_SESSION['probeid'] = $result;
      // add current user as responsible
      $_SESSION['db']->addProbeResponsible($_SESSION['probeid'], $_SESSION['user']->getUserid());
      $_SESSION['db']->unpauseProbe($_SESSION['probeid']);
      listProbes();
    } else {
      print("Probe already exists!\n");
    }
  }
}

function pauseProbe() {
  // users may only edit their own probes
  if ($_SESSION['user']->isUser()) {     
    if (!$_SESSION['user']->ownsProbe($_GET['id'])) {
      print("You may only edit your own probes!");
      return;
    }   
  }

  if ($_SESSION['db']->pauseProbe($_GET['id'])) {
    listProbes();
  }
}

function unpauseProbe() {
  // users may only edit their own probes
  if ($_SESSION['user']->isUser()) {     
    if (!$_SESSION['user']->ownsProbe($_GET['id'])) {
      print("You may only edit your own probes!");
      return;
    }   
  }

  if ($_SESSION['db']->unpauseProbe($_GET['id'])) {
    listProbes();
  }
}

function listUsers() {
  if ($_SESSION['db']->isAdminUser($_SESSION['user']->getUserid()) == 1) {
    print("<a href=\"?a=adduser\">New User</a><br />");
    $users = $_SESSION['db']->getAllUserData();
    print("<table summary=\"users\">\n");
    print("<tr><th>Login</th><th>Realname</th><th>E-Mail</th><th>Twitter</th><th>Jabber</th><th>Admin</th><th>Edit</th><th>Delete</th></tr>");
    foreach ($users as $user) {
      print("<tr>");

      print("<td>". htmlspecialchars($user['login']) ."</td>
             <td>". htmlspecialchars($user['realname']) ."</td>
       <td><a href=\"mailto:". $user['email'] ."\">".$user['email']."</a></td>
       <td><a href=\"http://twitter.com/". $user['twitteruser'] ."\" target=\"_blank\">".$user['twitteruser']."</a></td>
       <td>".$user['jabberuser']."</td>
       <td>".$user['admin']."</td>
       <td><a href=\"?a=edituser&amp;id=".$user['id']."\" title=\"edit user\"><img src=\"img/edit.png\" alt=\"edit\" /></a></td>");
       if ($_SESSION['user']->getUserid() <> $user['id']) {
         print("<td><a href=\"?a=deleteuser&amp;id=".$user['id']."\" title=\"delete user\"><img src=\"img/delete.png\" alt=\"delete\" /></a></td>");
       }
      print("</tr>");
    }
    print("</table>");
  } else {
    print("Admin access only!");    
  }
}

function editUser() {
  if (!isset($_POST['submit'])) {
    if (isset($_GET['id'])) {
      $_SESSION['userid'] = $_GET['id'];
    }

    $user = $_SESSION['db']->getUserData($_SESSION['userid']);

    // users may only edit their own profiles
    if ($_SESSION['user']->isUser()) { 
      if ($_SESSION['user']->getUserid() != $user['id']) {
        print("You may only edit your own profile!");
        return;
      }   
    }

    print("<h2>User settings</h2>");
    print("<form action=\"admin.php?a=edituser&amp;id=".$_SESSION['userid']."\" method=\"post\">\n");
    print("<label for=\"login\">Login</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"login\" id=\"login\" value=\"".htmlspecialchars($user['login'])."\" /><br />\n");

    print("<label for=\"realname\">Real name</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"realname\" id=\"realname\" value=\"".htmlspecialchars($user['realname'])."\" /><br />\n");

    print("<label for=\"email\">E-Mail</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"email\" id=\"email\" value=\"".$user['email']."\" /><br />\n");

    print("<label for=\"twitteruser\">Twitter user</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"twitteruser\" id=\"twitteruser\" value=\"".$user['twitteruser']."\" /><br />\n");

    print("<label for=\"twitterpass\">Twitter password</label><br />\n");
    print("<input type=\"password\" size=\"50\" name=\"twitterpass\" id=\"twitterpass\" value=\"".$user['twitterpass']."\" /><br />\n");

    print("<label for=\"jabberuser\">Jabber account</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"jabberuser\" id=\"jabberuser\" value=\"".$user['jabberuser']."\" /><br />\n");

    if ($_SESSION['db']->isAdminUser($_SESSION['user']->getUserid()) == 1) {
      print("<label for=\"admin\">Admin</label><br />\n");
      print("<input type=\"checkbox\" name=\"admin\" id=\"admin\" value=\"".$user['admin']."\"");

      if ($user['admin'] == 1)
        print(" checked=\"checked\" ");

      print("/><br />\n");    
    } else {
      print("<input type=\"hidden\" name=\"admin\" id=\"admin\" value=\"".$user['admin']."\"");
    }

    print("<label for=\"password\">Password (leave empty for unchanged password)</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"password\" id=\"password\" value=\"\" /><br /><br />\n");

    print("<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Save settings\" /><br />\n");
    print("</form>\n");
  } else {
    

    if (isset($_POST['admin'])) {
      $isAdmin = $_POST['admin'];
    } else {
      $isAdmin = 0;
    }

    $result = $_SESSION['db']->setUserData($_SESSION['userid'], trim($_POST['login']), trim($_POST['realname']), trim($_POST['email']), trim($_POST['password']), trim($_POST['twitteruser']), trim($_POST['twitterpass']), $isAdmin, trim($_POST['jabberuser']));
    if ($result) {
      listUsers();
    }
  }
}

function deleteUser() {
  if (!$_SESSION['user']->isUser()) { 
    $result = $_SESSION['db']->deleteUser($_GET['id']);
    if ($result) {
      listUsers();
    }	  
  }
}

function addUser() {
  if (!isset($_POST['submit'])) {
    print("<h2>Add new user</h2>");
    print("<form action=\"admin.php?a=adduser\" method=\"post\">\n");
    print("<label for=\"login\">Login</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"login\" id=\"login\" /><br />\n");
    print("<label for=\"realname\">Real name</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"realname\" id=\"realname\" /><br />\n");
    print("<label for=\"email\">E-Mail</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"email\" id=\"email\" /><br />\n");
    print("<label for=\"password\">Password</label><br />\n");
    print("<input type=\"text\" size=\"50\" name=\"password\" id=\"password\" value=\"initinit\" /><br />\n");
    print("<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Save settings\" /><br />\n");
    print("</form>\n");
  } else {
    $result = $_SESSION['db']->addUser(trim($_POST['login']), trim($_POST['realname']), trim($_POST['email']), trim($_POST['password']));
    if ($result) {
      listUsers();
    } else {
      print("User already exists!\n");
    }
  }
}

?>
